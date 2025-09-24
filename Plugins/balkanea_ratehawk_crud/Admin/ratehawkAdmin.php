<?php
/**
 * Ratehawk Crud Admin Page with Script Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ratehawk_Admin_Page {

    private $page_slug = 'ratehawk-crud-admin';
    private $script_status = array();
    private $plugin_path;
    private $crud_data_path;

    public function __construct() {
        $this->plugin_path = WP_PLUGIN_DIR . '/balkanea_ratehawk_crud/Includes';
        $this->crud_data_path = ABSPATH . '../CRUD_Data/extracts';
        $this->script_status = get_option('ratehawk_script_status', array());

        // Admin hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'handle_form_submissions']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // AJAX
        add_action('wp_ajax_ratehawk_check_status', [$this, 'ajax_check_status']);
        add_action('wp_ajax_ratehawk_clear_statuses', [$this, 'ajax_clear_statuses']);
        add_action('wp_ajax_ratehawk_stop_script', [$this, 'ajax_stop_script']);
    }

    /* =========================
       Admin Menu & Scripts
       ========================= */
    public function add_admin_menu() {
        add_menu_page(
            'Rate Hawk CRUD',
            'Rate Hawk CRUD',
            'manage_options',
            $this->page_slug,
            [$this, 'render_admin_page'],
            'dashicons-admin-site',
            30
        );
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_' . $this->page_slug) return;

        wp_enqueue_style(
            'ratehawk-admin-style',
            plugins_url('balkanea_ratehawk_crud/assets/css/admin-style.css'),
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'ratehawk-admin-script',
            plugins_url('balkanea_ratehawk_crud/assets/js/admin-script.js'),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('ratehawk-admin-script', 'ratehawk_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ratehawk_ajax_nonce')
        ]);
    }

    /* =========================
       Handle Form Submissions
       ========================= */
    public function handle_form_submissions() {
        if (!isset($_POST['ratehawk_nonce']) || !wp_verify_nonce($_POST['ratehawk_nonce'], 'ratehawk_actions')) {
            return;
        }

        if (isset($_POST['import_country'])) {
            $this->handle_import_country();
        }

        if (isset($_POST['update_countries'])) {
            $update_type = sanitize_text_field($_POST['update_type']);
            $country_code = $_POST['country_codes'] ?? '';

            $countries = [];
            if ($update_type === 'specific_country') {
                $countries = array_map('trim', explode(',', strtoupper($country_code)));
            }

            $this->run_update_and_import($countries);
        }
    }

    /* =========================
       Manual Import
       ========================= */
    private function handle_import_country() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        $country_code = strtoupper(sanitize_text_field($_POST['country_code']));
        if (empty($country_code) || strlen($country_code) !== 2) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 'Valid 2-letter country code is required', 'error');
            return;
        }

        $extract_file = $this->crud_data_path . "/extracted_{$country_code}_region.jsonl";

        if (!file_exists($extract_file)) {
            $ok = $this->run_extract_script($country_code);
            if (!$ok) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', "Failed to start extract for {$country_code}", 'error');
                return;
            }
            sleep(2);
        }

        $ok = $this->run_import_script($country_code);
        if ($ok) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', "Country {$country_code} import started successfully!", 'success');
        } else {
            add_settings_error('ratehawk_messages', 'ratehawk_message', "Failed to start import for {$country_code}", 'error');
        }
    }

    private function run_extract_script($country_code) {
        $script_path = $this->plugin_path . '/extract_multiple_countries.php';
        if (!file_exists($script_path)) return false;

        $php_bin = '/usr/bin/php';
        if (!is_executable($php_bin)) $php_bin = 'php';

        $cmd = escapeshellcmd($php_bin) . ' ' . escapeshellarg($script_path) . ' ' . escapeshellarg($country_code) . ' > /dev/null 2>&1 & echo $!';
        $pid = (int)trim(shell_exec($cmd));

        if ($pid > 0) {
            $key = 'extract_' . $country_code;
            $this->script_status[$key] = [
                 'key' => $key, 
                'pid' => $pid,
                'country' => $country_code,
                'type' => 'extract',
                'start_time' => time(),
                'status' => 'running',
                'step' => 'extracting'
            ];
            update_option('ratehawk_script_status', $this->script_status);
            return true;
        }
        return false;
    }

    private function run_import_script($country_code) {
        $script_path = $this->plugin_path . '/crone_job_import.php';
        if (!file_exists($script_path)) return false;

        $php_bin = '/usr/bin/php';
        if (!is_executable($php_bin)) $php_bin = 'php';

        $cmd = escapeshellcmd($php_bin) . ' ' . escapeshellarg($script_path) . ' ' . escapeshellarg($country_code) . ' > /dev/null 2>&1 & echo $!';
        $pid = (int)trim(shell_exec($cmd));

        if ($pid > 0) {
            $key = 'import_' . $country_code;
            $this->script_status[$key] = [
                 'key' => $key, 
                'pid' => $pid,
                'country' => $country_code,
                'type' => 'import',
                'start_time' => time(),
                'status' => 'running',
                'step' => 'importing'
            ];
            update_option('ratehawk_script_status', $this->script_status);
            return true;
        }
        return false;
    }

    /* =========================
       Update + Import (multi-country)
       ========================= */
 public function run_update_and_import($countries = []) {
    if (!is_array($countries)) $countries = [];
    if (empty($countries)) $countries = ['all'];

    // Prepare the country argument as comma-separated string
    $country_arg = implode(',', $countries);
    $timestamp = time();
    $running_file = sys_get_temp_dir() . "/ratehawk_{$timestamp}.running";
    file_put_contents($running_file, "waiting");

    $key = 'update_' . md5($country_arg . $timestamp); // unique key per batch
    $this->script_status[$key] = [
        'key' => $key,
       'country' => implode(',', $countries),
        'country_arg' => $country_arg,
        'running_file' => $running_file,
        'status' => 'waiting',
        'start_time' => time(),
        'step' => 'queued',
        'type' => 'update_import'
    ];

    // Start the script in background with all countries as one argument
    $cmd = "/usr/bin/env php " . escapeshellarg($this->plugin_path . "/process_hotels.php") .
           " " . escapeshellarg($country_arg) .
           " > /dev/null 2>&1 & echo $!";

    $pid = (int) trim(shell_exec($cmd));

    if ($pid > 0) {
        $this->script_status[$key]['pid'] = $pid;
        $this->script_status[$key]['status'] = 'running';
        $this->script_status[$key]['step'] = 'downloading_and_importing';
    }

    update_option('ratehawk_script_status', $this->script_status);
    return $this->script_status;
}

    /* =========================
       AJAX: Status
       ========================= */
  public function ajax_check_status() {
    check_ajax_referer('ratehawk_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_die('Unauthorized');

    $status = get_option('ratehawk_script_status', []);
    $response = [];

    foreach ($status as $key => $proc) {
        $running_file = $proc['running_file'] ?? '';
        $is_running = $running_file && file_exists($running_file);

        // Completed if not running
        if (!$is_running && $proc['status'] === 'running') {
            $status[$key]['status'] = 'completed';
            $status[$key]['end_time'] = time();
        }

        // Add the key explicitly for JS
        $proc['key'] = $key;
        $proc['status'] = $status[$key]['status'] ?? $proc['status'];

        $response[] = $proc;
    }

    update_option('ratehawk_script_status', $status);
    wp_send_json_success($response);
}
    /* =========================
       AJAX: Stop Script
       ========================= */
    public function ajax_stop_script() {
        check_ajax_referer('ratehawk_ajax_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        $key = sanitize_text_field($_POST['key'] ?? '');
        $status = get_option('ratehawk_script_status', []);

        if (!$key || !isset($status[$key])) {
            wp_send_json_error(['message' => 'Invalid process key']);
        }

        $proc = $status[$key];
        $pid = (int)($proc['pid'] ?? 0);
        $running_file = $proc['running_file'] ?? '';

        $stopped = false;
        if ($pid > 0) {
            if (function_exists('posix_kill')) {
                $stopped = @posix_kill($pid, SIGTERM);
            } else {
                exec('kill ' . $pid, $o, $r);
                $stopped = ($r === 0);
            }
        }

        if ($running_file && file_exists($running_file)) {
            unlink($running_file);
            $stopped = true;
        }

        $status[$key]['status'] = $stopped ? 'stopped' : 'failed_to_stop';
        $status[$key]['end_time'] = time();
        update_option('ratehawk_script_status', $status);

        if ($stopped) wp_send_json_success(['message' => 'Process stopped.']);
        else wp_send_json_error(['message' => 'Failed to stop process.']);
    }

    /* =========================
       AJAX: Clear Statuses
       ========================= */
    public function ajax_clear_statuses() {
        check_ajax_referer('ratehawk_ajax_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        $status = get_option('ratehawk_script_status', []);
        foreach ($status as $proc) {
            if (!empty($proc['running_file']) && file_exists($proc['running_file'])) {
                unlink($proc['running_file']);
            }
        }

        delete_option('ratehawk_script_status');
        wp_send_json_success(['message' => 'All statuses cleared.']);
    }

    /* =========================
       Admin Page
       ========================= */
    public function render_admin_page() {
        ?>
        <div class="wrap ratehawk-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php settings_errors('ratehawk_messages'); ?>

            <!-- Status Monitor -->
            <div class="ratehawk-section">
                <h2>Script Status Monitor</h2>
               
                <div id="ratehawk-status-monitor">
                    <div class="status-header">
                        <span>Active Scripts</span>
                        <button id="clear-statuses" class="button button-secondary">Clear Status List</button>
                        <button id="refresh-status" class="button button-secondary">Refresh Status</button>
                    </div>
                    <div id="script-status-list" class="status-list">
                        <div class="status-item">
                            <span class="status-text">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ratehawk-sections">
                <!-- Manual Import -->
                <div class="ratehawk-section">
                    <h2>Import New Country (Manual)</h2>
                    <p><strong>Note:</strong> This import does not use the dump script. If the extract file is missing, it will first run <code>extract_multiple_countries.php</code>, then <code>crone_job_import.php</code>.</p>
                    <form method="post" class="ratehawk-form" id="import-form">
                        <?php wp_nonce_field('ratehawk_actions', 'ratehawk_nonce'); ?>
                        <div class="form-group">
                            <label for="country_code">Country Code:</label>
                            <input type="text" id="country_code" name="country_code"
                                   placeholder="e.g., MK, GR" required
                                   pattern="[A-Za-z]{2}"
                                   title="Please enter a valid 2-letter country code"
                                   style="text-transform: uppercase;">
                        </div>
                        <button type="submit" name="import_country" class="button button-primary" id="import-button">
                            Start Manual Import
                        </button>
                    </form>
                </div>

                <!-- Update (Dump + Import) -->
                <div class="ratehawk-section">
                    <h2>Update Hotels (Dump + Import)</h2>
                    <form method="post" class="ratehawk-form" id="update-form">
                        <?php wp_nonce_field('ratehawk_actions', 'ratehawk_nonce'); ?>
                        <div class="form-group">
                            <label>Update Type:</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="update_type" value="all_countries" checked>
                                    Update All Countries
                                </label>
                                <label>
                                    <input type="radio" name="update_type" value="specific_country">
                                    Update Specific Country
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="specific_country_group" style="display:none;">
                            <label for="country_codes">Country Code(s) (comma separated):</label>
                            <input type="text" id="country_codes" name="country_codes"
                                   placeholder="e.g., GR, MK"
                                   pattern="[A-Za-z]{2}(,[A-Za-z]{2})*"
                                   title="Please enter valid 2-letter country codes separated by commas"
                                   style="text-transform: uppercase;">
                        </div>

                        <button type="submit" name="update_countries" class="button button-primary">
                            Start Update + Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($){
                // Toggle specific country input
                function toggleCountryField() {
                    $('#specific_country_group').toggle($('input[name="update_type"]:checked').val() === 'specific_country');
                }
                $('input[name="update_type"]').change(toggleCountryField);
                toggleCountryField();
            });
        </script>
        <?php
    }
}

// Initialize
new Ratehawk_Admin_Page();
