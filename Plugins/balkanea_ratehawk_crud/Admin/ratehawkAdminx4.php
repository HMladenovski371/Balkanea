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
        // Paths
        $this->plugin_path   = WP_PLUGIN_DIR . '/balkanea_ratehawk_crud/Includes';
        $this->crud_data_path = ABSPATH . '../CRUD_Data/extracts';

        // Load status from WP options
        $this->script_status = get_option('ratehawk_script_status', array());

        // Hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // AJAX
        add_action('wp_ajax_ratehawk_check_status', array($this, 'ajax_check_status'));
        add_action('wp_ajax_ratehawk_stop_script', array($this, 'ajax_stop_script'));
    }

    /**
     * Admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Rate Hawk CRUD',
            'Rate Hawk CRUD',
            'manage_options',
            $this->page_slug,
            array($this, 'render_admin_page'),
            'dashicons-admin-site',
            30
        );
    }

    /**
     * Assets
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_' . $this->page_slug) {
            return;
        }

        wp_enqueue_style(
            'ratehawk-admin-style',
            plugins_url('balkanea_ratehawk_crud/assets/css/admin-style.css'),
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'ratehawk-admin-script',
            plugins_url('balkanea_ratehawk_crud/assets/js/admin-script.js'),
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('ratehawk-admin-script', 'ratehawk_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ratehawk_ajax_nonce')
        ));
    }

    /**
     * Handle forms
     */
    public function handle_form_submissions() {
        if (!isset($_POST['ratehawk_nonce']) || !wp_verify_nonce($_POST['ratehawk_nonce'], 'ratehawk_actions')) {
            return;
        }

        if (isset($_POST['import_country'])) {
            $this->handle_import_country(); // MANUAL IMPORT FLOW
        }

        if (isset($_POST['update_countries'])) {
            $update_type  = sanitize_text_field($_POST['update_type']);
            $country_code = isset($_POST['specific_country']) ? strtoupper(sanitize_text_field($_POST['specific_country'])) : '';

            $this->run_update_and_import($update_type === 'specific' ? $country_code : '');
        }
    }

    /* =========================
       MANUAL IMPORT (Extract + Import)
       ========================= */
    private function handle_import_country() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        $country_code = strtoupper(sanitize_text_field($_POST['country_code']));

        if (empty($country_code) || strlen($country_code) !== 2) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 'Valid 2-letter country code is required', 'error');
            return;
        }

        $extract_file = $this->crud_data_path . '/extracted_' . $country_code . '_region.jsonl';

        // If missing, run EXTRACT first (does NOT use dump)
        if (!file_exists($extract_file)) {
            $ok = $this->run_extract_script($country_code);
            if (!$ok) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', "Failed to start extract for {$country_code}", 'error');
                return;
            }
            sleep(2);
        }

        // Then IMPORT
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
        $pid = (int) trim(shell_exec($cmd));

        if ($pid > 0) {
            $key = 'extract_' . $country_code;
            $this->script_status[$key] = [
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
        $pid = (int) trim(shell_exec($cmd));

        if ($pid > 0) {
            $key = 'import_' . $country_code;
            $this->script_status[$key] = [
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
       UPDATE + IMPORT (Dump & Cron)
       ========================= */
   /* private function run_update_and_import($country_code = '') {
        $php_bin = '/usr/bin/php';
        if (!is_executable($php_bin)) $php_bin = 'php';

        // 1️⃣ Start dump.php
       /* $dump_script = $this->plugin_path . '/dump.php';
        if (!file_exists($dump_script)) return false;

        $cmd_dump = escapeshellcmd($php_bin) . ' ' . escapeshellarg($dump_script);
        if (!empty($country_code)) $cmd_dump .= ' ' . escapeshellarg($country_code);
        $cmd_dump .= ' > /dev/null 2>&1 & echo $!';
        $dump_pid = (int) trim(shell_exec($cmd_dump));
        if ($dump_pid <= 0) return false;* /
        
        $sh_script = $this->plugin_path . '/download_incremental_dump.sh';
if (!file_exists($sh_script)) return false;

// Ensure it's executable
if (!is_executable($sh_script)) chmod($sh_script, 0755);

$cmd_sh = escapeshellcmd($sh_script);
if (!empty($country_code)) $cmd_sh .= ' ' . escapeshellarg($country_code);
$cmd_sh .= ' > /dev/null 2>&1 & echo $!';
$dump_pid = (int) trim(shell_exec($cmd_sh));

        $key_dump = empty($country_code) ? 'update_dump_all' : 'update_dump_' . $country_code;
        $this->script_status[$key_dump] = [
            'pid' => $dump_pid,
            'country' => $country_code,
            'type' => 'update_dump',
            'start_time' => time(),
            'status' => 'running',
            'step' => 'downloading'
        ];

        // 2️⃣ Prepare import process as awaiting
        $key_import = empty($country_code) ? 'update_import_all' : 'update_import_' . $country_code;
        $this->script_status[$key_import] = [
            'pid' => 0,
            'country' => $country_code,
            'type' => 'update_import',
            'start_time' => 0,
            'status' => 'awaiting',
            'step' => 'awaiting_import'
        ];

        update_option('ratehawk_script_status', $this->script_status);

        // 3️⃣ Start crone_job_import.php in background
        $import_script = $this->plugin_path . '/crone_job_import.php';
        if (file_exists($import_script)) {
            $cmd_import = escapeshellcmd($php_bin) . ' ' . escapeshellarg($import_script);
            if (!empty($country_code)) $cmd_import .= ' ' . escapeshellarg($country_code);
            $cmd_import .= ' > /dev/null 2>&1 & echo $!';
            $import_pid = (int) trim(shell_exec($cmd_import));

            if ($import_pid > 0) {
                $this->script_status[$key_import]['pid'] = $import_pid;
                $this->script_status[$key_import]['start_time'] = time();
                $this->script_status[$key_import]['status'] = 'running';
                $this->script_status[$key_import]['step'] = 'importing';
                update_option('ratehawk_script_status', $this->script_status);
            }
        }

        return true;
    }*/
    /* =========================
   UPDATE + IMPORT (Dump & Cron)
   ========================= */
/*private function run_update_and_import($country_code = '') {
    // 1️⃣ Start download_incremental_dump.sh
   // $sh_script = $this->plugin_path . '/download_incremental_dump.sh';
    $sh_script='/home/balkanea/public_html/wp-content/plugins/balkanea_ratehawk_crud/Includes/download_incremental_dump.sh';
    if (!file_exists($sh_script)) return false;

    // Ensure it's executable
    if (!is_executable($sh_script)) chmod($sh_script, 0755);

    // Absolute command with optional country code
    $cmd_sh = escapeshellcmd($sh_script);
    if (!empty($country_code)) $cmd_sh .= ' ' . escapeshellarg($country_code);

    // Run in background and capture PID
    $cmd_sh .= ' > /dev/null 2>&1 & echo $!';
    $dump_pid = (int) trim(shell_exec($cmd_sh));

    if ($dump_pid <= 0) return false;

    $key_dump = empty($country_code) ? 'update_dump_all' : 'update_dump_' . $country_code;
    $this->script_status[$key_dump] = [
        'pid' => $dump_pid,
        'country' => $country_code,
        'type' => 'update_dump',
        'start_time' => time(),
        'status' => 'running',
        'step' => 'downloading'
    ];

    // 2️⃣ Prepare import process as awaiting
    $key_import = empty($country_code) ? 'update_import_all' : 'update_import_' . $country_code;
    $this->script_status[$key_import] = [
        'pid' => 0,
        'country' => $country_code,
        'type' => 'update_import',
        'start_time' => 0,
        'status' => 'awaiting',
        'step' => 'awaiting_import'
    ];

    update_option('ratehawk_script_status', $this->script_status);

    // 3️⃣ Start crone_job_import.php in background
    $import_script = $this->plugin_path . '/crone_job_import.php';
    if (file_exists($import_script)) {
        $php_bin = '/usr/bin/php';
        if (!is_executable($php_bin)) $php_bin = 'php';

        $cmd_import = escapeshellcmd($php_bin) . ' ' . escapeshellarg($import_script);
        if (!empty($country_code)) $cmd_import .= ' ' . escapeshellarg($country_code);
        $cmd_import .= ' > /dev/null 2>&1 & echo $!';
        $import_pid = (int) trim(shell_exec($cmd_import));

        if ($import_pid > 0) {
            $this->script_status[$key_import]['pid'] = $import_pid;
            $this->script_status[$key_import]['start_time'] = time();
            $this->script_status[$key_import]['status'] = 'running';
            $this->script_status[$key_import]['step'] = 'importing';
            update_option('ratehawk_script_status', $this->script_status);
        }
    }

    return true;
}*/

private function run_update_and_import($country_codes = '') {
    // Normalize input to array
    if (is_string($country_codes)) {
        $country_codes = array_map('trim', explode(',', $country_codes));
    }

    if (!is_array($country_codes)) $country_codes = [];

    // Path to the automation script
    $script_path = '/home/balkanea/public_html/wp-content/plugins/balkanea_ratehawk_crud/Includes/process_hotels_automate.php';
    if (!file_exists($script_path)) {
        add_settings_error('ratehawk_messages', 'ratehawk_message', "Automation script not found at: $script_path", 'error');
        return false;
    }

    // PHP binary
    $php_bin = '/usr/local/bin/ea-php82';
    if (!is_executable($php_bin)) $php_bin = 'php';

    // Build command with optional country codes
    $cmd = escapeshellcmd($php_bin) . ' ' . escapeshellarg($script_path);
    if (!empty($country_codes)) {
        $cmd .= ' ' . escapeshellarg(implode(',', $country_codes));
    }

    // Run in background and get PID
    $cmd .= ' > /dev/null 2>&1 & echo $!';
    $pid = (int) trim(shell_exec($cmd));

    if ($pid <= 0) {
        add_settings_error('ratehawk_messages', 'ratehawk_message', "Failed to start automation script.", 'error');
        return false;
    }

    // Track status in WP
    $key = empty($country_codes) ? 'update_all' : 'update_' . implode('_', $country_codes);
    $this->script_status[$key] = [
        'pid'        => $pid,
        'country'    => $country_codes,
        'type'       => 'update_import',
        'start_time' => time(),
        'status'     => 'running',
        'step'       => 'downloading_and_importing'
    ];

    update_option('ratehawk_script_status', $this->script_status);

    // Admin message
    add_settings_error('ratehawk_messages', 'ratehawk_message', 
        empty($country_codes) ? "Update + import for all countries started successfully!" : 
                               "Update + import for " . implode(', ', $country_codes) . " started successfully!", 
        'success'
    );

    return true;
}


    /* =========================
       AJAX: Status & Stop
       ========================= */
    public function ajax_check_status() {
        check_ajax_referer('ratehawk_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        $status   = get_option('ratehawk_script_status', array());
        $response = array();

        foreach ($status as $key => $proc) {
            $pid = isset($proc['pid']) ? (int) $proc['pid'] : 0;
            $is_running = false;

            if ($pid > 0) {
                if (function_exists('posix_getpgid')) {
                    $is_running = posix_getpgid($pid) !== false;
                } else {
                    $out = array();
                    exec('ps -p ' . $pid, $out);
                    $is_running = count($out) > 1;
                }
            }

            if (!$is_running && $proc['status'] === 'running') {
                $status[$key]['status'] = 'completed';
                $status[$key]['end_time'] = time();

                if ($proc['type'] === 'extract' && !empty($proc['country'])) {
                    $extract_file = $this->crud_data_path . '/extracted_' . $proc['country'] . '_region.jsonl';
                    if (!file_exists($extract_file)) $status[$key]['status'] = 'failed';
                }
            }

            $response[] = $status[$key];
        }

        update_option('ratehawk_script_status', $status);
        wp_send_json_success($response);
    }

    public function ajax_stop_script() {
        check_ajax_referer('ratehawk_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        $pid = isset($_POST['pid']) ? (int) $_POST['pid'] : 0;
        if ($pid <= 0) wp_send_json_error(array('message' => 'Invalid process ID'));

        $killed = false;
        if (function_exists('posix_kill')) {
            $killed = @posix_kill($pid, SIGTERM);
        } else {
            exec('kill ' . $pid, $o, $r);
            $killed = ($r === 0);
        }

        $status = get_option('ratehawk_script_status', array());
        foreach ($status as $key => $proc) {
            if (isset($proc['pid']) && (int)$proc['pid'] === $pid) {
                $status[$key]['status'] = $killed ? 'stopped' : 'failed_to_stop';
                $status[$key]['end_time'] = time();
                break;
            }
        }

        update_option('ratehawk_script_status', $status);

        if ($killed) {
            wp_send_json_success(array('message' => 'Stop signal sent.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to stop (maybe already finished).'));
        }
    }

    /* =========================
       UI
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
                    <p><strong>Note:</strong> This import DOES NOT use the dump script. If extract file is missing, it will first run <code>extract_multiple_countries.php</code>, then <code>crone_job_import.php</code>.</p>
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

                <!-- Update (Dump) -->
                <div class="ratehawk-section">
                    <h2>Update Hotels (Dump + Import)</h2>
                    <form method="post" class="ratehawk-form">
                        <?php wp_nonce_field('ratehawk_actions', 'ratehawk_nonce'); ?>
                        <div class="form-group">
                            <label>Update Type:</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="update_type" value="all" checked
                                           onchange="document.getElementById('specific_country_group').style.display='none'">
                                    Update All Countries
                                </label>
                                <label>
                                    <input type="radio" name="update_type" value="specific"
                                           onchange="document.getElementById('specific_country_group').style.display='block'">
                                    Update Specific Country
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="specific_country_group" style="display: none;">
                            <label for="specific_country">Country Code:</label>
                            <input type="text" id="specific_country" name="specific_country"
                                   placeholder="e.g., GR"
                                   pattern="[A-Za-z]{2}"
                                   title="Please enter a valid 2-letter country code"
                                   style="text-transform: uppercase;">
                        </div>
                        <button type="submit" name="update_countries" class="button button-primary">
                            Start Update + Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize
new Ratehawk_Admin_Page();
