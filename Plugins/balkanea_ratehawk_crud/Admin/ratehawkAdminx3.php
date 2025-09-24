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
    private $processes = array();
    private $plugin_path;
    private $crud_data_path;
    
    public function __construct() {
        // Set paths
        $this->plugin_path = WP_PLUGIN_DIR . '/balkanea_ratehawk_crud/Includes';
        $this->crud_data_path = ABSPATH . '../CRUD_Data/extracts';
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_ratehawk_check_status', array($this, 'ajax_check_status'));
        add_action('wp_ajax_ratehawk_stop_script', array($this, 'ajax_stop_script'));
        add_action('wp_ajax_ratehawk_cleanup_status', array($this, 'ajax_cleanup_status'));
        
        // Initialize script status from WordPress options
        $this->script_status = get_option('ratehawk_script_status', array());
        
        // Clean up old status entries on admin page load
        add_action('admin_init', array($this, 'cleanup_old_status_entries'));
    }
    
    /**
     * Clean up status entries older than 1 hour
     */
    public function cleanup_old_status_entries() {
        $current_status = get_option('ratehawk_script_status', array());
        $changed = false;
        $one_hour_ago = time() - 3600;
        
        foreach ($current_status as $key => $process) {
            if (isset($process['end_time']) && $process['end_time'] < $one_hour_ago) {
                unset($current_status[$key]);
                $changed = true;
            } elseif (!isset($process['end_time']) && $process['start_time'] < $one_hour_ago) {
                // If a process has been running for more than 1 hour without update, mark it as stale
                $current_status[$key]['status'] = 'stale';
                $current_status[$key]['end_time'] = time();
                $changed = true;
            }
        }
        
        if ($changed) {
            update_option('ratehawk_script_status', $current_status);
        }
    }
    
    /**
     * Add admin menu item
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
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_' . $this->page_slug) {
            return;
        }
        
        // Fix the CSS path
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
            'nonce' => wp_create_nonce('ratehawk_ajax_nonce')
        ));
    }
    
    /**
     * Handle form submissions from the admin page
     */
    public function handle_form_submissions() {
        if (!isset($_POST['ratehawk_nonce']) || !wp_verify_nonce($_POST['ratehawk_nonce'], 'ratehawk_actions')) {
            return;
        }
        
        if (isset($_POST['import_country'])) {
            $this->handle_import_country();
        }
        
        if (isset($_POST['update_hotels_submit'])) {
            $this->handle_update_hotels();
        }
    }
    
    /**
     * Handle import country
     */
    private function handle_import_country() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }
        
        $country_code = strtoupper(sanitize_text_field($_POST['country_code']));
        
        if (empty($country_code) || strlen($country_code) !== 2) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 'Valid 2-letter country code is required', 'error');
            return;
        }
        
        // Check if extract file exists
        $extract_file = $this->crud_data_path . '/extracted_' . $country_code . '_region.jsonl';
        
        if (!file_exists($extract_file)) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', "Extract file for {$country_code} not found. Please run the 'Update' process for this country first.", 'error');
            return;
        }
        
        // Run the import script
        $import_result = $this->run_import_script($country_code);
        
        if ($import_result) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', "Country {$country_code} import started successfully!", 'success');
        } else {
            add_settings_error('ratehawk_messages', 'ratehawk_message', "Failed to start import for country {$country_code}", 'error');
        }
    }
    
    /**
     * Run import script for a country
     */
    private function run_import_script($country_code) {
        $script_path = $this->plugin_path . '/crone_job_import.php';
        $command = '/usr/bin/php ' . escapeshellarg($script_path) . ' ' . escapeshellarg($country_code) . ' > /dev/null 2>&1 & echo $!';
        
        $pid = (int) trim(shell_exec($command));
        
        if ($pid > 0) {
            $process_key = 'import_' . $country_code . '_' . time();
            $this->script_status[$process_key] = array(
                'pid' => $pid,
                'country' => $country_code,
                'type' => 'import',
                'start_time' => time(),
                'status' => 'running',
                'step' => 'importing'
            );
            
            update_option('ratehawk_script_status', $this->script_status);
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle the "Update Hotels" form submission
     */
    private function handle_update_hotels() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }
        
        $update_type = sanitize_text_field($_POST['update_type']);
        $result = false;
        
        if ($update_type === 'all_countries') {
            // Get selected countries from Balkanea plugin settings
            $balkanea_options = get_option('balkanea_settings');
            $countries = isset($balkanea_options['selected_countries']) ? $balkanea_options['selected_countries'] : [];
            
            if (empty($countries)) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', 'No countries are selected in Balkanea Plugin settings. Please select countries first.', 'error');
                return;
            }
            
            $country_codes_string = implode(',', $countries);
            $result = $this->run_update_script($country_codes_string, 'all_countries');
            $message = "Update for all selected countries (" . esc_html($country_codes_string) . ") has been started!";

        } elseif ($update_type === 'specific_hotels') {
            $hotel_ids_raw = sanitize_textarea_field($_POST['hotel_ids']);
            $hotel_ids = array_filter(array_map('trim', explode(',', $hotel_ids_raw)));
            
            if (empty($hotel_ids)) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', 'Please enter at least one hotel ID.', 'error');
                return;
            }
            
            $hotel_ids_string = implode(',', $hotel_ids);
            $result = $this->run_update_script($hotel_ids_string, 'specific_hotels');
            $message = "Update for specific hotels (" . esc_html($hotel_ids_string) . ") has been started!";
        }
        
        if ($result) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', $message, 'success');
        } else {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 'Failed to start the update script. Check file permissions and server logs.', 'error');
        }
    }
    
    /**
     * Run the main download_incremetal_dump.sh script
     */
  private function run_update_script($params, $type) {
    $script_path = $this->plugin_path . '/download_incremental_dump.sh';

    if ($type !== 'all_countries') {
        add_settings_error('ratehawk_messages', 'ratehawk_message', 'Specific hotel updates are not supported with the new script.', 'error');
        return false;
    }

    // Build command
    $command = escapeshellcmd($script_path) . ' ' . escapeshellarg($params) . ' > /dev/null 2>&1 & echo $!';
    $pid = (int) trim(shell_exec($command));

    if ($pid > 0) {
        $process_key = 'update_allcountries_' . time();
        $this->script_status[$process_key] = [
            'pid' => $pid,
            'params' => $params,
             'country' => $params, 
            'type' => 'update',
            'start_time' => time(),
            'status' => 'running',
            'step' => 'downloading'
        ];
        update_option('ratehawk_script_status', $this->script_status);
        $this->schedule_status_check($process_key, $pid);
        return true;
    }

    return false;
}
    
    /**
     * Schedule a status check for a process
     */
    private function schedule_status_check($process_key, $pid) {
        // Schedule a single event to check the status after 30 seconds
        if (!wp_next_scheduled('ratehawk_check_process_status', array($process_key, $pid))) {
            wp_schedule_single_event(time() + 30, 'ratehawk_check_process_status', array($process_key, $pid));
        }
        
        // Add action to handle the scheduled check
        add_action('ratehawk_check_process_status', array($this, 'check_process_status'), 10, 2);
    }
    
    /**
     * Check the status of a process and update if needed
     */
    public function check_process_status($process_key, $pid) {
        $status = get_option('ratehawk_script_status', array());
        
        if (!isset($status[$process_key])) {
            return;
        }
        
        // Check if the process is still running
        $is_running = false;
        if (function_exists('posix_getpgid')) {
            $is_running = posix_getpgid($pid) !== false;
        } else {
            $output = array();
            exec("ps -p " . intval($pid), $output);
            $is_running = count($output) > 1;
        }
        
        if (!$is_running) {
            // Process has finished, update status
            $status[$process_key]['status'] = 'completed';
            $status[$process_key]['end_time'] = time();
            
            // If this was an update process, check if we need to start import processes
            if ($status[$process_key]['type'] === 'update' && $status[$process_key]['step'] === 'extracting') {
                $this->start_import_processes($status[$process_key]['params']);
            }
        } else {
            // Process is still running, schedule another check
            if (!wp_next_scheduled('ratehawk_check_process_status', array($process_key, $pid))) {
                wp_schedule_single_event(time() + 30, 'ratehawk_check_process_status', array($process_key, $pid));
            }
        }
        
        update_option('ratehawk_script_status', $status);
    }
    
    /**
     * Start import processes for countries after update completes
     */
    private function start_import_processes($country_codes) {
        $countries = explode(',', $country_codes);
        
        foreach ($countries as $country_code) {
            $country_code = trim($country_code);
            $extract_file = $this->crud_data_path . '/extracted_' . $country_code . '_region.jsonl';
            
            if (file_exists($extract_file)) {
                $this->run_import_script($country_code);
            }
        }
    }

    /**
     * AJAX handler to check script status
     */
    public function ajax_check_status() {
        check_ajax_referer('ratehawk_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $status = get_option('ratehawk_script_status', array());
        $response = array();
        
        foreach ($status as $key => $script) {
            if ($script['status'] === 'running') {
                // Check if the process with the given PID is still active
                $is_running = false;
                if (function_exists('posix_getpgid') && $script['pid']) {
                    $is_running = posix_getpgid($script['pid']) !== false;
                } else {
                    $output = array();
                    exec("ps -p " . intval($script['pid']), $output);
                    $is_running = count($output) > 1;
                }
                
                if (!$is_running) {
                    $status[$key]['status'] = 'completed';
                    $status[$key]['end_time'] = time();
                    
                    // If this was an update process that completed, start import processes
                    if ($status[$key]['type'] === 'update' && $status[$key]['step'] === 'extracting') {
                        $this->start_import_processes($status[$key]['params']);
                    }
                }
            }
            $response[] = $status[$key];
        }
        
        update_option('ratehawk_script_status', $status);
        wp_send_json_success($response);
    }
    
    /**
     * AJAX handler to stop a script
     */
    public function ajax_stop_script() {
        check_ajax_referer('ratehawk_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $pid = isset($_POST['pid']) ? (int) $_POST['pid'] : 0;
        
        if ($pid > 0) {
            $killed = false;
            if (function_exists('posix_kill')) {
                $killed = posix_kill($pid, SIGTERM);
            } else {
                exec("kill " . $pid, $output, $result);
                $killed = ($result === 0);
            }
            
            // Find the script in the status array by its PID and update it
            $status = get_option('ratehawk_script_status', array());
            $process_key_to_update = null;
            foreach ($status as $key => $script) {
                if (isset($script['pid']) && $script['pid'] == $pid) {
                    $process_key_to_update = $key;
                    break;
                }
            }
            
            if ($process_key_to_update && isset($status[$process_key_to_update])) {
                $status[$process_key_to_update]['status'] = $killed ? 'stopped' : 'failed_to_stop';
                $status[$process_key_to_update]['end_time'] = time();
                update_option('ratehawk_script_status', $status);
            }
            
            if ($killed) {
                wp_send_json_success(array('message' => 'Script stop signal sent successfully.'));
            } else {
                wp_send_json_error(array('message' => 'Failed to stop script. It may have already finished.'));
            }
        }
        
        wp_send_json_error(array('message' => 'Invalid process ID provided.'));
    }
    
    /**
     * AJAX handler to cleanup old status entries
     */
    public function ajax_cleanup_status() {
        check_ajax_referer('ratehawk_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $this->cleanup_old_status_entries();
        wp_send_json_success(array('message' => 'Status cleanup completed.'));
    }
    
    /**
     * Render the admin page HTML
     */
    public function render_admin_page() {
        ?>
        <div class="wrap ratehawk-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors('ratehawk_messages'); ?>
            
            <!-- Status Monitor Section -->
            <div class="ratehawk-section">
                <h2>Script Status Monitor</h2>
                <div id="ratehawk-status-monitor">
                    <div class="status-header">
                        <span>Active Scripts</span>
                        <button id="refresh-status" class="button button-secondary">Refresh Status</button>
                        <button id="cleanup-status" class="button button-secondary">Clean Up Old Status</button>
                    </div>
                    <div id="script-status-list" class="status-list">
                        <div class="status-item">
                            <span class="status-text">Loading status...</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="ratehawk-sections">
                <!-- Import Section -->
                <div class="ratehawk-section">
                    <h2>Import New Country</h2>
                    <form method="post" class="ratehawk-form" id="import-form">
                        <?php wp_nonce_field('ratehawk_actions', 'ratehawk_nonce'); ?>
                         <p><strong>Note:</strong> This section is for manually starting an import on an <em>already extracted</em> file. The main workflow is now handled through the 'Update Hotels' section.</p>
                        <div class="form-group">
                            <label for="country_code">Country Code:</label>
                            <input type="text" id="country_code" name="country_code" 
                                   placeholder="e.g., MK, GR" required
                                   pattern="[A-Za-z]{2}" 
                                   title="Please enter a valid 2-letter country code">
                        </div>
                        <button type="submit" name="import_country" class="button button-primary" id="import-button">
                            Start Manual Import
                        </button>
                    </form>
                </div>
                
                <!-- Update Section -->
                <div class="ratehawk-section">
                    <h2>Update Hotels</h2>
                    <form method="post" class="ratehawk-form">
                        <?php wp_nonce_field('ratehawk_actions', 'ratehawk_nonce'); ?>
                        
                        <div class="form-group">
                            <label>Update Type:</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="update_type" value="all_countries" checked 
                                           onchange="toggleUpdateFields()">
                                    Update All Countries (from Balkanea Settings)
                                </label>
                                <label>
                                    <input type="radio" name="update_type" value="specific_hotels" 
                                           onchange="toggleUpdateFields()">
                                    Update Specific Hotels by ID
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" id="all_countries_info">
                            <?php
                                $balkanea_options = get_option('balkanea_settings');
                                $countries = isset($balkanea_options['selected_countries']) ? $balkanea_options['selected_countries'] : [];
                                if (!empty($countries)) {
                                    echo '<p>This will start the update process for the following countries: <strong>' . esc_html(implode(', ', $countries)) . '</strong></p>';
                                } else {
                                    echo '<p style="color: red; border: 1px solid red; padding: 10px;">Warning: No countries are selected in the Balkanea Plugin settings. Go to <a href="/wp-admin/admin.php?page=balkanea-plugin">Balkanea Settings</a> to select countries.</p>';
                                }
                            ?>
                        </div>
                        
                        <div class="form-group" id="specific_hotels_group" style="display: none;">
                            <label for="hotel_ids">Hotel IDs (separated by comma):</label>
                            <textarea id="hotel_ids" name="hotel_ids" rows="4" class="large-text" placeholder="e.g., 12345, 67890, 54321"></textarea>
                        </div>
                        
                        <button type="submit" name="update_hotels_submit" class="button button-primary">
                            Start Update
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Modal for script execution -->
            <div id="ratehawk-modal" class="ratehawk-modal" style="display: none;">
                <div class="ratehawk-modal-content">
                    <h3 id="modal-title">Processing</h3>
                    <div class="modal-body">
                        <div class="spinner"></div>
                        <p id="modal-message">Please wait while the script is running...</p>
                        <div id="modal-details"></div>
                    </div>
                    <div class="modal-footer">
                        <button id="modal-stop" class="button button-secondary">Stop Script</button>
                        <button id="modal-close" class="button button-primary" style="display: none;">Close</button>
                    </div>
                </div>
            </div>
            
            <script>
            function toggleUpdateFields() {
                const allCountriesInfo = document.getElementById('all_countries_info');
                const specificGroup = document.getElementById('specific_hotels_group');
                const specificRadio = document.querySelector('input[name="update_type"][value="specific_hotels"]');
                
                if (specificRadio.checked) {
                    specificGroup.style.display = 'block';
                    allCountriesInfo.style.display = 'none';
                } else {
                    specificGroup.style.display = 'none';
                    allCountriesInfo.style.display = 'block';
                }
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                toggleUpdateFields();
            });
            </script>
        </div>
        <?php
    }
}

// Initialize the admin page
new Ratehawk_Admin_Page();