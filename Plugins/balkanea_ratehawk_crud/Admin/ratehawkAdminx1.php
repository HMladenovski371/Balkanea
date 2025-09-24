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
        
        // Initialize script status
        $this->script_status = get_option('ratehawk_script_status', array());
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
   /* public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_' . $this->page_slug) {
            return;
        }
        
        wp_enqueue_style(
            'ratehawk-admin-style',
            plugin_dir_url(__FILE__) . '../../assets/css/admin-style.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_script(
            'ratehawk-admin-script',
            plugin_dir_url(__FILE__) . '../../assets/js/admin-script.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('ratehawk-admin-script', 'ratehawk_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ratehawk_ajax_nonce')
        ));
    }*/
    
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
     * Handle form submissions
     */
    public function handle_form_submissions() {
        if (!isset($_POST['ratehawk_nonce']) || !wp_verify_nonce($_POST['ratehawk_nonce'], 'ratehawk_actions')) {
            return;
        }
        
        if (isset($_POST['import_country'])) {
            $this->handle_import_country();
        }
        
        if (isset($_POST['update_countries'])) {
            $this->handle_update_countries();
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
            // File doesn't exist, run the extract script first
            $extract_result = $this->run_extract_script($country_code);
            
            if (!$extract_result) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', "Failed to extract data for country {$country_code}", 'error');
                return;
            }
            
            // Wait a moment for the file to be created
            sleep(2);
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
     * Run extract script for a country
     */
    private function run_extract_script($country_code) {
        $script_path = $this->plugin_path . '/extract_multiple_countries.php';
        $command = '/usr/bin/php ' . escapeshellarg($script_path) . ' ' . escapeshellarg($country_code) . ' > /dev/null 2>&1 & echo $!';
        
        $pid = shell_exec($command);
        $pid = (int) trim($pid);
        
        if ($pid > 0) {
            $this->script_status['extract_' . $country_code] = array(
                'pid' => $pid,
                'country' => $country_code,
                'type' => 'extract',
                'start_time' => time(),
                'status' => 'running'
            );
            
            update_option('ratehawk_script_status', $this->script_status);
            return true;
        }
        
        return false;
    }
    
    /**
     * Run import script for a country
     */
    private function run_import_script($country_code) {
        $script_path = $this->plugin_path . '/crone_job_import.php';
        $command = '/usr/bin/php ' . escapeshellarg($script_path) . ' ' . escapeshellarg($country_code) . ' > /dev/null 2>&1 & echo $!';
        
        $pid = shell_exec($command);
        $pid = (int) trim($pid);
        
        if ($pid > 0) {
            $this->script_status['import_' . $country_code] = array(
                'pid' => $pid,
                'country' => $country_code,
                'type' => 'import',
                'start_time' => time(),
                'status' => 'running'
            );
            
            update_option('ratehawk_script_status', $this->script_status);
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle update countries
     */
    private function handle_update_countries() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }
        
        $update_type = sanitize_text_field($_POST['update_type']);
        $country_code = isset($_POST['specific_country']) ? strtoupper(sanitize_text_field($_POST['specific_country'])) : '';
        
        if ($update_type === 'specific' && (empty($country_code) || strlen($country_code) !== 2)) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 'Valid 2-letter country code is required for specific update', 'error');
            return;
        }
        
        // Here you would implement your actual update logic
        if ($update_type === 'all') {
            $result = $this->update_all_countries();
            $message = $result ? 'All countries update started successfully!' : 'Failed to start update for all countries';
        } else {
            $result = $this->update_specific_country($country_code);
            $message = $result ? "Country {$country_code} update started successfully!" : "Failed to start update for country {$country_code}";
        }
        
        $type = $result ? 'success' : 'error';
        add_settings_error('ratehawk_messages', 'ratehawk_message', $message, $type);
    }
    
    /**
     * Update all countries (placeholder - implement your actual logic)
     */
    private function update_all_countries() {
        // This would need to be implemented based on your specific requirements
        // For now, we'll just return true as a placeholder
        return true;
    }
    
    /**
     * Update specific country (placeholder - implement your actual logic)
     */
    private function update_specific_country($country_code) {
        // This would need to be implemented based on your specific requirements
        // For now, we'll just return true as a placeholder
        return true;
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
                // Check if process is still running
                $is_running = false;
                if (function_exists('posix_getpgid')) {
                    $is_running = posix_getpgid($script['pid']) !== false;
                } else {
                    // Fallback for Windows or systems without posix
                    $output = array();
                    exec("ps -p " . $script['pid'], $output);
                    $is_running = count($output) > 1;
                }
                
                if (!$is_running) {
                    $status[$key]['status'] = 'completed';
                    $status[$key]['end_time'] = time();
                    
                    // Check if the extract file was created for extract scripts
                    if ($script['type'] === 'extract') {
                        $extract_file = $this->crud_data_path . '/extracted_' . $script['country'] . '_region.jsonl';
                        if (!file_exists($extract_file)) {
                            $status[$key]['status'] = 'failed';
                        }
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
        $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        
        if ($pid > 0) {
            // Kill the process
            if (function_exists('posix_kill')) {
                $killed = posix_kill($pid, SIGTERM);
            } else {
                exec("kill " . $pid, $output, $result);
                $killed = ($result === 0);
            }
            
            // Update status
            $status = get_option('ratehawk_script_status', array());
            $key = $type . '_' . $country;
            
            if (isset($status[$key])) {
                $status[$key]['status'] = $killed ? 'stopped' : 'failed';
                $status[$key]['end_time'] = time();
                update_option('ratehawk_script_status', $status);
            }
            
            if ($killed) {
                wp_send_json_success(array('message' => 'Script stopped successfully'));
            } else {
                wp_send_json_error(array('message' => 'Failed to stop script'));
            }
        }
        
        wp_send_json_error(array('message' => 'Invalid process ID'));
    }
    
    /**
     * Render admin page
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
                    </div>
                    <div id="script-status-list" class="status-list">
                        <div class="status-item">
                            <span class="status-text">Loading...</span>
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
                        <div class="form-group">
                            <label for="country_code">Country Code:</label>
                            <input type="text" id="country_code" name="country_code" 
                                   placeholder="e.g., US, GB, DE" required
                                   pattern="[A-Za-z]{2}" 
                                   title="Please enter a valid 2-letter country code">
                        </div>
                        <button type="submit" name="import_country" class="button button-primary" id="import-button">
                            Import New Country
                        </button>
                    </form>
                </div>
                
                <!-- Update Section -->
                <div class="ratehawk-section">
                    <h2>Update Countries</h2>
                    <form method="post" class="ratehawk-form">
                        <?php wp_nonce_field('ratehawk_actions', 'ratehawk_nonce'); ?>
                        
                        <div class="form-group">
                            <label>Update Type:</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="update_type" value="all" checked 
                                           onchange="toggleCountryField()">
                                    Update All Countries
                                </label>
                                <label>
                                    <input type="radio" name="update_type" value="specific" 
                                           onchange="toggleCountryField()">
                                    Update Specific Country
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" id="specific_country_group" style="display: none;">
                            <label for="specific_country">Country Code:</label>
                            <input type="text" id="specific_country" name="specific_country" 
                                   placeholder="e.g., US, GB, DE"
                                   pattern="[A-Za-z]{2}" 
                                   title="Please enter a valid 2-letter country code">
                        </div>
                        
                        <button type="submit" name="update_countries" class="button button-primary">
                            Make Update
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
            function toggleCountryField() {
                const specificGroup = document.getElementById('specific_country_group');
                const specificRadio = document.querySelector('input[name="update_type"][value="specific"]');
                
                if (specificRadio.checked) {
                    specificGroup.style.display = 'block';
                } else {
                    specificGroup.style.display = 'none';
                }
            }
            
            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                toggleCountryField();
            });
            </script>
        </div>
        <?php
    }
}

// Initialize the admin page
new Ratehawk_Admin_Page();