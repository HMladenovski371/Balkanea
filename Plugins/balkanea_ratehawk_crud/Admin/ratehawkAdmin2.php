<?php
/**
 * Ratehawk Crud Admin Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ratehawk_Admin_Page {
    
    private $page_slug = 'ratehawk-crud-admin';
    private $extract_script_path;
    private $import_script_path;
    
    public function __construct() {
        // Set paths to your scripts
        $this->extract_script_path = _DIR_ . '/../Includes/extract_multiple_countries.php';
        $this->import_script_path = _DIR_ . '/../Includes/crone_job_import.php';
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
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
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'toplevel_page_' . $this->page_slug) {
            return;
        }
        
        wp_enqueue_style(
            'ratehawk-admin-style',
            plugin_dir_url(__FILE__) . '../assets/css/admin-style.css',
            array(),
            '1.0.0'
        );
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
        
        $country_code = sanitize_text_field($_POST['country_code']);
        
        if (empty($country_code)) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 'Country code is required', 'error');
            return;
        }
        
        // Validate country code format
        if (!preg_match('/^[A-Za-z]{2}$/', $country_code)) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 'Country code must be exactly 2 letters', 'error');
            return;
        }
        
        // Convert to uppercase
        $country_code = strtoupper($country_code);
        
        // Check if extract file already exists
        $extract_file = ABSPATH . "home/balkanea/CRUD_Data/extract/extract_{$country_code}_region.json";
        
        if (!file_exists($extract_file)) {
            // Run extract script first
            $extract_result = $this->run_extract_script($country_code);
            
            if (!$extract_result['success']) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', 
                    "Extract failed: " . $extract_result['message'], 'error');
                return;
            }
        }
        
        // Run import script
        $import_result = $this->run_import_script($country_code);
        
        if ($import_result['success']) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 
                "Country {$country_code} imported successfully!", 'success');
        } else {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 
                "Import failed: " . $import_result['message'], 'error');
        }
    }
    
    /**
     * Handle update countries
     */
    private function handle_update_countries() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }
        
        $update_type = sanitize_text_field($_POST['update_type']);
        $country_code = isset($_POST['specific_country']) ? sanitize_text_field($_POST['specific_country']) : '';
        
        if ($update_type === 'specific' && empty($country_code)) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 'Country code is required for specific update', 'error');
            return;
        }
        
        // Validate country code format if provided
        if (!empty($country_code) && !preg_match('/^[A-Za-z]{2}$/', $country_code)) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', 'Country code must be exactly 2 letters', 'error');
            return;
        }
        
        if ($update_type === 'all') {
            // Get all existing extract files to determine which countries to update
            $extract_files = glob(ABSPATH . 'home/balkanea/CRUD_Data/extract/extract_*_region.json');
            $country_codes = array();
            
            foreach ($extract_files as $file) {
                if (preg_match('/extract_([A-Z]{2})_region\.json$/', $file, $matches)) {
                    $country_codes[] = $matches[1];
                }
            }
            
            if (empty($country_codes)) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', 
                    'No countries found to update. Import countries first.', 'error');
                return;
            }
            
            $results = array();
            foreach ($country_codes as $code) {
                $results[$code] = $this->run_import_script($code);
            }
            
            $success_count = count(array_filter($results, function($r) { return $r['success']; }));
            $total_count = count($results);
            
            if ($success_count === $total_count) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', 
                    "All {$total_count} countries updated successfully!", 'success');
            } else {
                $error_message = "Updated {$success_count} of {$total_count} countries. Failed: ";
                $failed = array();
                foreach ($results as $code => $result) {
                    if (!$result['success']) {
                        $failed[] = "{$code} ({$result['message']})";
                    }
                }
                add_settings_error('ratehawk_messages', 'ratehawk_message', 
                    $error_message . implode(', ', $failed), 'error');
            }
        } else {
            // Update specific country
            $country_code = strtoupper($country_code);
            
            // Check if extract file exists
            $extract_file = ABSPATH . "home/balkanea/CRUD_Data/extract/extract_{$country_code}_region.json";
            
            if (!file_exists($extract_file)) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', 
                    "Country {$country_code} has not been imported yet. Please import it first.", 'error');
                return;
            }
            
            $result = $this->run_import_script($country_code);
            
            if ($result['success']) {
                add_settings_error('ratehawk_messages', 'ratehawk_message', 
                    "Country {$country_code} updated successfully!", 'success');
            } else {
                add_settings_error('ratehawk_messages', 'ratehawk_message', 
                    "Update failed: " . $result['message'], 'error');
            }
        }
    }
    
    /**
     * Run extract script for a country
     */
    private function run_extract_script($country_code) {
        if (!file_exists($this->extract_script_path)) {
            return array(
                'success' => false,
                'message' => 'Extract script not found at: ' . $this->extract_script_path
            );
        }
        
        // Build the command
        $command = '/usr/local/bin/ea-php82 ' . escapeshellarg($this->extract_script_path) . 
                   ' ' . escapeshellarg($country_code) . ' 2>&1';
        
        // Execute the command
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            return array(
                'success' => false,
                'message' => 'Script execution failed: ' . implode("\n", $output)
            );
        }
        
        // Check if the extract file was created
        $extract_file = ABSPATH . "home/balkanea/CRUD_Data/extract/extract_{$country_code}_region.json";
        
        if (!file_exists($extract_file)) {
            return array(
                'success' => false,
                'message' => 'Extract file was not created: ' . $extract_file
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Extract completed successfully'
        );
    }
    
    /**
     * Run import script for a country
     */
    private function run_import_script($country_code) {
        if (!file_exists($this->import_script_path)) {
            return array(
                'success' => false,
                'message' => 'Import script not found at: ' . $this->import_script_path
            );
        }
        
        // Build the command
        $command = '/usr/local/bin/ea-php82 ' . escapeshellarg($this->import_script_path) . 
                   ' ' . escapeshellarg($country_code) . ' 2>&1';
        
        // Execute the command
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            return array(
                'success' => false,
                'message' => 'Script execution failed: ' . implode("\n", $output)
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Import completed successfully'
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        // Get list of existing countries for reference
        $extract_files = glob(ABSPATH . 'home/balkanea/CRUD_Data/extract/extract_*_region.json');
        $existing_countries = array();
        
        foreach ($extract_files as $file) {
            if (preg_match('/extract_([A-Z]{2})_region\.json$/', $file, $matches)) {
                $existing_countries[] = $matches[1];
            }
        }
        
        sort($existing_countries);
        ?>
        <div class="wrap ratehawk-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors('ratehawk_messages'); ?>
            
            <?php if (!empty($existing_countries)): ?>
            <div class="ratehawk-info-box">
                <h3>Currently Imported Countries</h3>
                <p><?php echo implode(', ', $existing_countries); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="ratehawk-sections">
                <!-- Import Section -->
                <div class="ratehawk-section">
                    <h2>Import New Country</h2>
                    <form method="post" class="ratehawk-form">
                        <?php wp_nonce_field('ratehawk_actions', 'ratehawk_nonce'); ?>
                        <div class="form-group">
                            <label for="country_code">Country Code (2 letters):</label>
                            <input type="text" id="country_code" name="country_code" 
                                   placeholder="e.g., MK, US, GB, DE" required
                                   pattern="[A-Za-z]{2}" 
                                   title="Please enter a valid 2-letter country code">
                            <p class="description">Enter a 2-letter country code (e.g., MK for Macedonia)</p>
                        </div>
                        <button type="submit" name="import_country" class="button button-primary">
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
                                    Update All Countries (<?php echo count($existing_countries); ?> countries)
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
                                   placeholder="e.g., MK, US, GB, DE"
                                   pattern="[A-Za-z]{2}" 
                                   title="Please enter a valid 2-letter country code">
                            <p class="description">Select from existing: <?php echo implode(', ', $existing_countries); ?></p>
                        </div>
                        
                        <button type="submit" name="update_countries" class="button button-primary">
                            Make Update
                        </button>
                    </form>
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