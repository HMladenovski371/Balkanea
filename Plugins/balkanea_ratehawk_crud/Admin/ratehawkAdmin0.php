<?php
/**
 * Ratehawk Crud Admin Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ratehawk_Admin_Page {
    
    private $page_slug = 'ratehawk-crud-admin';
    
    public function __construct() {
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
        
        // Here you would implement your actual import logic
        $result = $this->import_country_data($country_code);
        
        if ($result) {
            add_settings_error('ratehawk_messages', 'ratehawk_message', "Country {$country_code} imported successfully!", 'success');
        } else {
            add_settings_error('ratehawk_messages', 'ratehawk_message', "Failed to import country {$country_code}", 'error');
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
        
        // Here you would implement your actual update logic
        if ($update_type === 'all') {
            $result = $this->update_all_countries();
            $message = $result ? 'All countries updated successfully!' : 'Failed to update all countries';
        } else {
            $result = $this->update_specific_country($country_code);
            $message = $result ? "Country {$country_code} updated successfully!" : "Failed to update country {$country_code}";
        }
        
        $type = $result ? 'success' : 'error';
        add_settings_error('ratehawk_messages', 'ratehawk_message', $message, $type);
    }
    
    /**
     * Import country data (placeholder - implement your actual logic)
     */
    private function import_country_data($country_code) {
        // Implement your actual import logic here
        // This could be an API call, database operation, etc.
        
        // Simulate success for demonstration
        sleep(1); // Simulate processing time
        return true;
    }
    
    /**
     * Update all countries (placeholder - implement your actual logic)
     */
    private function update_all_countries() {
        // Implement your actual update logic for all countries
        
        // Simulate success for demonstration
        sleep(2); // Simulate processing time
        return true;
    }
    
    /**
     * Update specific country (placeholder - implement your actual logic)
     */
    private function update_specific_country($country_code) {
        // Implement your actual update logic for specific country
        
        // Simulate success for demonstration
        sleep(1); // Simulate processing time
        return true;
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap ratehawk-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors('ratehawk_messages'); ?>
            
            <div class="ratehawk-sections">
                <!-- Import Section -->
                <div class="ratehawk-section">
                    <h2>Import New Country</h2>
                    <form method="post" class="ratehawk-form">
                        <?php wp_nonce_field('ratehawk_actions', 'ratehawk_nonce'); ?>
                        <div class="form-group">
                            <label for="country_code">Country Code:</label>
                            <input type="text" id="country_code" name="country_code" 
                                   placeholder="e.g., US, GB, DE" required
                                   pattern="[A-Za-z]{2}" 
                                   title="Please enter a valid 2-letter country code">
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