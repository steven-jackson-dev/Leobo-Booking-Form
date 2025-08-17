<?php
/**
 * Leobo Custom Booking System - Main Class
 * Clean, modular booking system with separated concerns
 * 
 * @package LeoboCustomBookingSystem
 * @version 2.0.0
 * @author Your Development Team
 */

class LeoboCustomBookingSystem {
    
    private $form_id = 'leobo-booking-form';
    private $plugin_path;
    private $plugin_url;
    private $pricing;
    private $database;
    private $email;
    
    public function __construct() {
        global $wpdb, $wp_query, $post;
        $this->plugin_path = dirname(__FILE__);
        $this->plugin_url = get_template_directory_uri() . '/app/CustomBookingSystem';
        
        // Initialize components
        $this->include_dependencies();
        $this->pricing = new LeoboBookingPricing();
        $this->database = new LeoboBookingDatabase();
        $this->email = new LeoboBookingEmail();
        
        // Register hooks
        $this->register_hooks();
    }
    
    /**
     * Include all required files
     */
    private function include_dependencies() {
        require_once $this->plugin_path . '/includes/BookingAvailability.php';
        require_once $this->plugin_path . '/includes/BookingPricing.php';
        require_once $this->plugin_path . '/includes/BookingDatabase.php';
        require_once $this->plugin_path . '/includes/BookingEmail.php';
    }
    
    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // ACF Options page for booking configuration
        add_action('acf/init', array($this, 'add_booking_config_options_page'));
        
        // AJAX endpoints
        add_action('wp_ajax_calculate_booking_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_nopriv_calculate_booking_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_submit_booking_request', array($this, 'ajax_submit_booking'));
        add_action('wp_ajax_nopriv_submit_booking_request', array($this, 'ajax_submit_booking'));
        add_action('wp_ajax_get_accommodation_details', array($this, 'ajax_get_accommodation'));
        add_action('wp_ajax_nopriv_get_accommodation_details', array($this, 'ajax_get_accommodation'));
        
        // Debug AJAX endpoints
        add_action('wp_ajax_debug_season_detection', array($this, 'ajax_debug_season'));
        add_action('wp_ajax_debug_min_nights', array($this, 'ajax_debug_min_nights'));
        
        // Shortcode
        add_shortcode('leobo_custom_booking_form', array($this, 'render_booking_form'));
    }
    
    /**
     * Add ACF options page for booking configuration
     */
    public function add_booking_config_options_page() {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page(array(
                'page_title' => 'Booking Configuration',
                'menu_title' => 'Booking Config',
                'menu_slug' => 'booking-config',
                'capability' => 'manage_options',
                'icon_url' => 'dashicons-calendar-alt',
                'position' => 31,
                'redirect' => false
            ));
        }
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (!is_page() && !is_single()) {
            return;
        }
        
        // Dependencies
        wp_enqueue_script('jquery');
        
        // Booking form JavaScript (now using custom date picker)
        wp_enqueue_script(
            'leobo-booking-form',
            $this->plugin_url . '/assets/js/booking-form.js',
            array('jquery'),
            '3.2.0', // Updated for pricing fixes and debugging
            true
        );
        
        // Booking form styles
        wp_enqueue_style(
            'leobo-booking-form-styles',
            $this->plugin_url . '/assets/css/booking-form-styles.css',
            array(),
            '3.2.1' // Updated to force cache refresh for checkbox fixes
        );
        
        // Localize script
        try {
            $frontend_data = $this->pricing->get_frontend_data();
            wp_localize_script('leobo-booking-form', 'leobo_booking_system', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('leobo_booking_system_nonce'),
                'currency_symbol' => 'R',
                'accommodations' => $frontend_data['accommodations'] ?? array(),
                'packages' => $frontend_data['packages'] ?? array(),
                'seasons' => $frontend_data['seasons'] ?? array(),
                'guest_rules' => $frontend_data['guest_rules'] ?? array(),
                'blocked_dates' => $this->get_blocked_dates_js_format(),
                'acf_config' => array(
                    'adults_max' => get_field('max_adults', 'option') ?: 12,
                    'children_max' => get_field('max_children', 'option') ?: 8,
                    'babies_max' => get_field('max_babies', 'option') ?: 4,
                    'minimum_nights_standard' => get_field('default_min_nights', 'option') ?: 3
                )
            ));
        } catch (Exception $e) {
            error_log('Leobo Booking Script Localization Error: ' . $e->getMessage());
            // Provide fallback data
            wp_localize_script('leobo-booking-form', 'leobo_booking_system', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('leobo_booking_system_nonce'),
                'currency_symbol' => 'R',
                'accommodations' => array(),
                'packages' => array(),
                'seasons' => array(),
                'guest_rules' => array(),
                'blocked_dates' => array(),
                'acf_config' => array(
                    'adults_max' => 12,
                    'children_max' => 8,
                    'babies_max' => 4,
                    'minimum_nights_standard' => 3
                )
            ));
        }
    }
    
    /**
     * Render the booking form using template
     */
    public function render_booking_form($atts = array()) {
        $atts = shortcode_atts(array(
            'show_title' => true,
            'show_description' => true,
            'theme' => 'default'
        ), $atts);
        
        // Convert string booleans to actual booleans
        $atts['show_title'] = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_description'] = filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN);
        
        $args = array(
            'form_id' => $this->form_id,
            'attributes' => $atts
        );
        
        return $this->get_template('booking-form', $args);
    }
    
    /**
     * AJAX: Calculate booking price
     */
    public function ajax_calculate_price() {
        try {
            check_ajax_referer('leobo_booking_system_nonce', 'nonce');
            
            $checkin = sanitize_text_field($_POST['checkin_date'] ?? '');
            $checkout = sanitize_text_field($_POST['checkout_date'] ?? '');
            
            // Handle both old (guests) and new (adults/children) parameter formats
            $adults = 2; // Default
            $children = 0; // Default
            
            if (isset($_POST['adults']) || isset($_POST['children'])) {
                // New format: separate adults and children
                $adults = intval($_POST['adults'] ?? 2);
                $children = intval($_POST['children'] ?? 0);
            } elseif (isset($_POST['guests'])) {
                // Old format: total guests (assume all adults for backward compatibility)
                $adults = intval($_POST['guests'] ?? 2);
                $children = 0;
            }
            
            $accommodation_id = sanitize_text_field($_POST['accommodation_id'] ?? '');
            $helicopter_package = isset($_POST['helicopter_package']) ? $_POST['helicopter_package'] : null;
            
            // Validate required fields
            if (empty($checkin) || empty($checkout)) {
                wp_send_json_error('Check-in and check-out dates are required');
                return;
            }
            
            // Validate date format
            $checkin_date = DateTime::createFromFormat('Y-m-d', $checkin);
            $checkout_date = DateTime::createFromFormat('Y-m-d', $checkout);
            
            if (!$checkin_date || !$checkout_date) {
                wp_send_json_error('Invalid date format. Please use YYYY-MM-DD format');
                return;
            }
            
            if ($checkin_date >= $checkout_date) {
                wp_send_json_error('Check-out date must be after check-in date');
                return;
            }
            
            $pricing = $this->pricing->calculate_pricing($checkin, $checkout, $adults, $children, $accommodation_id, $helicopter_package);
            
            if (isset($pricing['error'])) {
                wp_send_json_error($pricing['error']);
            } else {
                wp_send_json_success($pricing);
            }
            
        } catch (Exception $e) {
            error_log('Leobo Booking Price Calculation Error: ' . $e->getMessage());
            wp_send_json_error('Sorry, there was an error calculating the price. Please try again or contact support.');
        }
    }
    
    /**
     * AJAX: Get accommodation details
     */
    public function ajax_get_accommodation() {
        check_ajax_referer('leobo_booking_system_nonce', 'nonce');
        
        $accommodation_id = sanitize_text_field($_POST['accommodation_id']);
        $accommodations = $this->pricing->get_accommodations_data();
        
        foreach ($accommodations as $id => $accommodation) {
            if ($id === $accommodation_id) {
                wp_send_json_success($accommodation);
            }
        }
        
        wp_send_json_error('Accommodation not found');
    }
    
    /**
     * AJAX: Submit booking request
     */
    public function ajax_submit_booking() {
        check_ajax_referer('leobo_booking_system_nonce', 'nonce');
        
        // Sanitize input
        $booking_data = $this->sanitize_booking_data($_POST);
        
        // Validate availability
        if (!$this->validate_availability($booking_data['checkin_date'], $booking_data['checkout_date'])) {
            wp_send_json_error('Selected dates are no longer available');
        }
        
        // Save booking
        $booking_id = $this->database->save_booking($booking_data);
        
        if ($booking_id) {
            // Send notifications
            $this->email->send_admin_notification($booking_id, $booking_data);
            $this->email->send_user_confirmation($booking_id, $booking_data);
            
            // Allow custom actions
            do_action('leobo_booking_submitted', $booking_id, $booking_data);
            
            wp_send_json_success(array(
                'message' => 'Booking request submitted successfully!',
                'booking_id' => $booking_id
            ));
        } else {
            wp_send_json_error('Failed to save booking request');
        }
    }
    
    /**
     * Sanitize booking form data
     */
    private function sanitize_booking_data($post_data) {
        return array(
            'checkin_date' => sanitize_text_field($post_data['checkin_date']),
            'checkout_date' => sanitize_text_field($post_data['checkout_date']),
            'adults' => intval($post_data['adults'] ?? 2),
            'children' => intval($post_data['children'] ?? 0),
            'accommodation' => sanitize_text_field($post_data['accommodation'] ?? ''),
            'helicopter_package' => isset($post_data['helicopter_package']) ? $post_data['helicopter_package'] : null,
            'first_name' => sanitize_text_field($post_data['first_name']),
            'last_name' => sanitize_text_field($post_data['last_name']),
            'email' => sanitize_email($post_data['email']),
            'phone' => sanitize_text_field($post_data['phone']),
            'special_requests' => sanitize_textarea_field($post_data['special_requests'] ?? ''),
            'calculated_total' => floatval($post_data['calculated_total'] ?? 0)
        );
    }
    
    /**
     * Validate date availability
     */
    private function validate_availability($checkin, $checkout) {
        global $wpdb, $leobo_booking_availability;
        
        $start_date = new DateTime($checkin);
        $end_date = new DateTime($checkout);
        $current_date = clone $start_date;
        
        while ($current_date < $end_date) {
            if (!$leobo_booking_availability->is_date_available($current_date->format('Y-m-d'))) {
                return false;
            }
            $current_date->add(new DateInterval('P1D'));
        }
        
        return true;
    }
    
    /**
     * Get blocked dates for JavaScript
     */
    private function get_blocked_dates_js_format() {
        global $wpdb, $leobo_booking_availability;
        return $leobo_booking_availability->get_blocked_dates(12, 'Y-m-d');
    }
    
    /**
     * Get template content
     */
    private function get_template($template_name, $args = array()) {
        $template_file = $this->plugin_path . '/templates/' . $template_name . '.php';
        
        if (!file_exists($template_file)) {
            return '<p>Error: Template not found - ' . esc_html($template_name) . '</p>';
        }
        
        ob_start();
        include $template_file;
        return ob_get_clean();
    }
    
    /**
     * Get pricing component (for external access)
     */
    public function get_pricing() {
        return $this->pricing;
    }
    
    /**
     * Get database component (for external access)
     */
    public function get_database() {
        return $this->database;
    }
    
    /**
     * Get email component (for external access)
     */
    public function get_email() {
        return $this->email;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Leobo Booking System',           // Page title
            'Booking System',                 // Menu title
            'manage_options',                 // Capability
            'leobo-booking-system',          // Menu slug
            array($this, 'admin_page'),      // Function
            'dashicons-calendar-alt',        // Icon
            30                               // Position
        );
        
        add_submenu_page(
            'leobo-booking-system',          // Parent slug
            'Documentation',                 // Page title
            'Documentation',                 // Menu title
            'manage_options',                // Capability
            'leobo-booking-docs',           // Menu slug
            array($this, 'documentation_page') // Function
        );
        
        add_submenu_page(
            'leobo-booking-system',          // Parent slug
            'Settings',                      // Page title
            'Settings',                      // Menu title
            'manage_options',                // Capability
            'leobo-booking-settings',       // Menu slug
            array($this, 'settings_page')   // Function
        );
        
        add_submenu_page(
            'leobo-booking-system',          // Parent slug
            'API Status',                    // Page title
            'API Status',                    // Menu title
            'manage_options',                // Capability
            'leobo-booking-api-status',     // Menu slug
            array($this, 'api_status_page') // Function
        );
        
        add_submenu_page(
            'leobo-booking-system',          // Parent slug
            'Debug Data',                    // Page title
            'Debug Data',                    // Menu title
            'manage_options',                // Capability
            'leobo-booking-debug',          // Menu slug
            array($this, 'debug_page')      // Function
        );
    }
    
    /**
     * Admin main page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Leobo Booking System</h1>
            
            <div class="card">
                <h2>System Overview</h2>
                <p>Welcome to the Leobo Custom Booking System. This system provides a complete multi-step booking form for your luxury safari lodge.</p>
                
                <h3>Quick Start</h3>
                <p>To display the booking form on any page or post, use the shortcode:</p>
                <code>[leobo_custom_booking_form]</code>
                
                <h3>Configuration</h3>
                <p>Configure pricing, seasons, and helicopter packages in the <a href="<?php echo admin_url('admin.php?page=booking-config'); ?>" class="button button-secondary">Booking Config</a> page.</p>
                
                <h3>System Status</h3>
                <table class="wp-list-table widefat">
                    <tr>
                        <td><strong>Form Template</strong></td>
                        <td><?php echo file_exists($this->plugin_path . '/templates/booking-form.php') ? '✅ Active' : '❌ Missing'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>JavaScript</strong></td>
                        <td><?php echo file_exists($this->plugin_path . '/assets/js/booking-form.js') ? '✅ Loaded' : '❌ Missing'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Styles</strong></td>
                        <td><?php echo file_exists($this->plugin_path . '/assets/css/booking-form-styles.css') ? '✅ Loaded' : '❌ Missing'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Database Tables</strong></td>
                        <td><?php echo $this->check_database_tables() ? '✅ Ready' : '❌ Need Setup'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>ACF Configuration</strong></td>
                        <td><?php echo function_exists('get_field') ? '✅ ACF Active' : '❌ ACF Required'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Pricing Data</strong></td>
                        <td><?php 
                            $has_pricing = get_field('standard_base_rate', 'option') || get_field('peak_base_rate', 'option');
                            echo $has_pricing ? '✅ Configured' : '⚠️ Needs Configuration'; 
                        ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="card">
                <h2>Recent Bookings</h2>
                <?php $this->display_recent_bookings(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Documentation page
     */
    public function documentation_page() {
        ?>
        <div class="wrap">
            <h1>Leobo Booking System - Documentation</h1>
            
            <div class="nav-tab-wrapper">
                <a href="#setup" class="nav-tab nav-tab-active" onclick="showTab('setup')">Setup Guide</a>
                <a href="#shortcodes" class="nav-tab" onclick="showTab('shortcodes')">Shortcodes</a>
                <a href="#customization" class="nav-tab" onclick="showTab('customization')">Customization</a>
                <a href="#troubleshooting" class="nav-tab" onclick="showTab('troubleshooting')">Troubleshooting</a>
            </div>
            
            <!-- Setup Guide Tab -->
            <div id="setup-tab" class="tab-content">
                <div class="card">
                    <h2>📚 Setup Guide</h2>
                    
                    <h3>1. Basic Installation</h3>
                    <p>The Leobo Booking System is already installed and ready to use. Here's how to get started:</p>
                    
                    <h4>Step 1: Add the Booking Form to a Page</h4>
                    <ol>
                        <li>Edit or create a new page where you want the booking form</li>
                        <li>Add the shortcode: <code>[leobo_custom_booking_form]</code></li>
                        <li>Publish the page</li>
                    </ol>
                    
                    <h4>Step 2: Configure Pricing & Seasons</h4>
                    <ol>
                        <li>Go to <strong>Booking Config</strong> in the WordPress admin menu</li>
                        <li>Configure Standard Season dates and rates</li>
                        <li>Configure Peak Season dates and rates</li>
                        <li>Configure Premium Christmas dates and rates</li>
                        <li>Set up helicopter packages if needed</li>
                        <li>Configure minimum nights rules</li>
                    </ol>
                    
                    <h4>Step 3: Test Your Configuration</h4>
                    <ol>
                        <li>Go to <strong>Booking System → Debug Data</strong></li>
                        <li>Use the pricing test to verify calculations</li>
                        <li>Test season detection for various dates</li>
                        <li>Check minimum nights requirements</li>
                    </ol>
                    
                    <h4>Step 4: Set Up Email Notifications</h4>
                    <p>Configure email settings in: <code>/includes/BookingEmail.php</code></p>
                    
                    <h3>2. Form Features</h3>
                    <ul>
                        <li><strong>Multi-Step Process:</strong> 4 steps for optimal user experience</li>
                        <li><strong>Date Selection:</strong> Advanced calendar with blocked dates</li>
                        <li><strong>Guest Management:</strong> Separate counts for adults, children, babies</li>
                        <li><strong>Add-on Services:</strong> Transfer options, experiences, packages</li>
                        <li><strong>Real-time Pricing:</strong> Dynamic cost calculation</li>
                        <li><strong>Mobile Responsive:</strong> Works on all devices</li>
                    </ul>
                </div>
            </div>
            
            <!-- Shortcodes Tab -->
            <div id="shortcodes-tab" class="tab-content" style="display: none;">
                <div class="card">
                    <h2>🎯 Shortcodes Reference</h2>
                    
                    <h3>Basic Shortcode</h3>
                    <pre><code>[leobo_custom_booking_form]</code></pre>
                    <p>Displays the complete booking form with default settings.</p>
                    
                    <h3>Shortcode Parameters</h3>
                    <table class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Default</th>
                                <th>Description</th>
                                <th>Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>show_title</code></td>
                                <td>true</td>
                                <td>Show or hide the form title</td>
                                <td><code>show_title="false"</code></td>
                            </tr>
                            <tr>
                                <td><code>show_description</code></td>
                                <td>true</td>
                                <td>Show or hide the form description</td>
                                <td><code>show_description="false"</code></td>
                            </tr>
                            <tr>
                                <td><code>theme</code></td>
                                <td>default</td>
                                <td>Theme variant (future use)</td>
                                <td><code>theme="minimal"</code></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h3>Examples</h3>
                    <pre><code>[leobo_custom_booking_form show_title="false"]</code></pre>
                    <p>Displays the form without the title.</p>
                    
                    <pre><code>[leobo_custom_booking_form show_title="false" show_description="false"]</code></pre>
                    <p>Displays a minimal form without title or description.</p>
                </div>
            </div>
            
            <!-- Customization Tab -->
            <div id="customization-tab" class="tab-content" style="display: none;">
                <div class="card">
                    <h2>🎨 Customization Guide</h2>
                    
                    <h3>Styling</h3>
                    <p>The booking form uses custom CSS located at:</p>
                    <pre><code>/assets/css/booking-form-styles.css</code></pre>
                    
                    <h4>CSS Custom Properties</h4>
                    <p>You can customize colors by modifying these CSS variables:</p>
                    <pre><code>:root {
    --leobo-gold: #d4b896;
    --leobo-dark-brown: #3d2f1f;
    --background-dark: #1a1a1a;
    --text-primary: #ffffff;
    --button-primary: var(--leobo-gold);
}</code></pre>
                    
                    <h3>JavaScript Customization</h3>
                    <p>The main JavaScript file is located at:</p>
                    <pre><code>/assets/js/booking-form.js</code></pre>
                    
                    <h3>Template Customization</h3>
                    <p>The form template can be customized at:</p>
                    <pre><code>/templates/booking-form.php</code></pre>
                    
                    <h3>Adding Custom Fields</h3>
                    <ol>
                        <li>Add the field to the template file</li>
                        <li>Update the JavaScript to handle the new field</li>
                        <li>Modify the AJAX handler in CustomBookingSystem.php</li>
                        <li>Update the database schema if needed</li>
                    </ol>
                    
                    <h3>Pricing Customization</h3>
                    <p>Modify pricing logic in:</p>
                    <pre><code>/includes/BookingPricing.php</code></pre>
                    
                    <h3>Email Templates</h3>
                    <p>Customize email templates in:</p>
                    <pre><code>/templates/emails/</code></pre>
                </div>
            </div>
            
            <!-- Troubleshooting Tab -->
            <div id="troubleshooting-tab" class="tab-content" style="display: none;">
                <div class="card">
                    <h2>🔧 Troubleshooting</h2>
                    
                    <h3>Common Issues</h3>
                    
                    <h4>Form Not Displaying</h4>
                    <ul>
                        <li>Check that the shortcode is correct: <code>[leobo_custom_booking_form]</code></li>
                        <li>Verify the template file exists: <code>/templates/booking-form.php</code></li>
                        <li>Check for JavaScript errors in browser console</li>
                    </ul>
                    
                    <h4>Date Picker Not Working</h4>
                    <ul>
                        <li>Ensure Hotel Datepicker library is loading</li>
                        <li>Check for JavaScript conflicts with other plugins</li>
                        <li>Verify the date picker modal is properly initialized</li>
                    </ul>
                    
                    <h4>Form Submission Failing</h4>
                    <ul>
                        <li>Check AJAX URL and nonce values</li>
                        <li>Verify required fields are filled</li>
                        <li>Check WordPress error logs</li>
                        <li>Ensure database tables exist</li>
                    </ul>
                    
                    <h4>Styling Issues</h4>
                    <ul>
                        <li>Check CSS file is loading: <code>/assets/css/booking-form-styles.css</code></li>
                        <li>Look for CSS conflicts with theme</li>
                        <li>Verify browser cache is cleared</li>
                    </ul>
                    
                    <h3>Debug Mode</h3>
                    <p>Enable WordPress debug mode by adding to wp-config.php:</p>
                    <pre><code>define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);</code></pre>
                    
                    <h3>Browser Console</h3>
                    <p>Check browser console (F12) for JavaScript errors and network requests.</p>
                    
                    <h3>Contact Support</h3>
                    <p>If issues persist, check the error logs at:</p>
                    <pre><code>/wp-content/debug.log</code></pre>
                </div>
            </div>
            
            <style>
            .tab-content {
                margin-top: 20px;
            }
            .nav-tab-wrapper {
                margin-bottom: 20px;
            }
            pre {
                background: #f4f4f4;
                padding: 15px;
                border-radius: 4px;
                overflow-x: auto;
            }
            code {
                background: #f4f4f4;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: Monaco, Consolas, monospace;
            }
            .wp-list-table {
                margin-top: 10px;
            }
            </style>
            
            <script>
            function showTab(tabName) {
                // Hide all tabs
                var tabs = document.querySelectorAll('.tab-content');
                tabs.forEach(function(tab) {
                    tab.style.display = 'none';
                });
                
                // Remove active class from all nav tabs
                var navTabs = document.querySelectorAll('.nav-tab');
                navTabs.forEach(function(tab) {
                    tab.classList.remove('nav-tab-active');
                });
                
                // Show selected tab
                document.getElementById(tabName + '-tab').style.display = 'block';
                
                // Add active class to clicked nav tab
                event.target.classList.add('nav-tab-active');
            }
            </script>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Booking System Settings</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('leobo_booking_settings');
                do_settings_sections('leobo_booking_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Currency Symbol</th>
                        <td>
                            <input type="text" name="leobo_currency_symbol" value="<?php echo esc_attr(get_option('leobo_currency_symbol', 'R')); ?>" />
                            <p class="description">Symbol to display for prices (default: R)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Admin Email</th>
                        <td>
                            <input type="email" name="leobo_admin_email" value="<?php echo esc_attr(get_option('leobo_admin_email', get_option('admin_email'))); ?>" class="regular-text" />
                            <p class="description">Email address to receive booking notifications</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Minimum Stay (nights)</th>
                        <td>
                            <input type="number" name="leobo_min_stay" value="<?php echo esc_attr(get_option('leobo_min_stay', '2')); ?>" min="1" />
                            <p class="description">Minimum number of nights for a booking</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Maximum Guests</th>
                        <td>
                            <input type="number" name="leobo_max_guests" value="<?php echo esc_attr(get_option('leobo_max_guests', '12')); ?>" min="1" />
                            <p class="description">Maximum number of guests allowed</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * API Status page - Test API connections and view diagnostics
     */
    public function api_status_page() {
        // Handle API test request
        $test_result = null;
        $cache_cleared = false;
        
        if (isset($_POST['test_api']) && wp_verify_nonce($_POST['api_test_nonce'], 'leobo_api_test')) {
            $test_result = leobo_test_api_connection();
        }
        
        if (isset($_POST['clear_cache']) && wp_verify_nonce($_POST['cache_clear_nonce'], 'leobo_cache_clear')) {
            $cache_result = leobo_clear_availability_cache();
            $cache_cleared = $cache_result['status'] === 'success';
        }
        
        ?>
        <div class="wrap">
            <h1>API Status & Diagnostics</h1>
            
            <!-- API Test Section -->
            <div class="card" style="max-width: none; margin-bottom: 20px;">
                <h2>API Connection Test</h2>
                <form method="post">
                    <?php wp_nonce_field('leobo_api_test', 'api_test_nonce'); ?>
                    <p>Test the connection to the Pan Hospitality API to ensure availability data is being retrieved correctly.</p>
                    <p><input type="submit" name="test_api" class="button button-primary" value="Test API Connection" /></p>
                </form>
                
                <?php if ($test_result): ?>
                    <?php $status_class = $test_result['status'] === 'success' ? 'notice-success' : 'notice-error'; ?>
                    <div class="notice <?php echo $status_class; ?> inline">
                        <p><strong>Test Result:</strong> <?php echo ucfirst($test_result['status']); ?></p>
                        
                        <?php if (isset($test_result['response_data']) && !empty($test_result['response_data'])): ?>
                            <h4>API Response Data:</h4>
                            <div style="background: #f1f1f1; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;">
                                <pre style="margin: 0; white-space: pre-wrap;"><?php echo esc_html(print_r($test_result['response_data'], true)); ?></pre>
                            </div>
                        <?php endif; ?>
                        
                        <ul style="margin-top: 10px;">
                            <li>Response Time: <?php echo $test_result['response_time_ms']; ?>ms</li>
                            <li>Data Received: <?php echo $test_result['data_received'] ? 'Yes' : 'No'; ?></li>
                            <li>Valid Availability Data: <?php echo $test_result['has_availability_data'] ? 'Yes' : 'No'; ?></li>
                            <li>Tested at: <?php echo $test_result['timestamp']; ?></li>
                        </ul>
                        
                        <?php if (isset($test_result['error_message'])): ?>
                            <p><strong>Error:</strong> <?php echo esc_html($test_result['error_message']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Cache Management Section -->
            <div class="card" style="max-width: none; margin-bottom: 20px;">
                <h2>Cache Management</h2>
                <form method="post">
                    <?php wp_nonce_field('leobo_cache_clear', 'cache_clear_nonce'); ?>
                    <p>Clear all cached availability data to force fresh API calls. Use this if you notice stale data.</p>
                    <p><input type="submit" name="clear_cache" class="button button-secondary" value="Clear All Caches" onclick="return confirm('Are you sure you want to clear all availability caches?');" /></p>
                </form>
                
                <?php if ($cache_cleared): ?>
                    <div class="notice notice-success inline">
                        <p>All availability caches have been cleared successfully.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Configuration Status -->
            <div class="card" style="max-width: none;">
                <h2>System Configuration</h2>
                
                <?php
                $config_status = array();
                
                // Check ACF Pro
                $config_status['ACF Pro'] = function_exists('get_field') ? 'Active' : 'Missing';
                
                // Check ACF fields
                $accommodations = get_field('accommodations', 'option');
                $config_status['Accommodations'] = !empty($accommodations) ? count($accommodations) . ' configured' : 'Not configured';
                
                $seasons = get_field('seasons', 'option');
                $config_status['Seasons'] = !empty($seasons) ? count($seasons) . ' configured' : 'Not configured';
                
                $packages = get_field('packages', 'option');
                $config_status['Packages'] = !empty($packages) ? count($packages) . ' configured' : 'Not configured';
                
                // Check database table
                global $wpdb;
                $table_name = $wpdb->prefix . 'leobo_booking_requests';
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
                $config_status['Database Table'] = $table_exists ? 'Created' : 'Missing';
                ?>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Component</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($config_status as $component => $status): ?>
                            <?php
                            $status_class = '';
                            if (strpos($status, 'Missing') !== false || strpos($status, 'Not configured') !== false) {
                                $status_class = 'style="color: #d63638; font-weight: bold;"';
                            } elseif (strpos($status, 'Active') !== false || strpos($status, 'Created') !== false || is_numeric(substr($status, 0, 1))) {
                                $status_class = 'style="color: #00a32a; font-weight: bold;"';
                            }
                            ?>
                            <tr>
                                <td><?php echo $component; ?></td>
                                <td <?php echo $status_class; ?>><?php echo $status; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Debug page - Shows all ACF configuration data
     */
    public function debug_page() {
        ?>
        <div class="wrap">
            <h1>Booking System Debug Data</h1>
            
            <div class="nav-tab-wrapper">
                <a href="#acf-data" class="nav-tab nav-tab-active" onclick="showDebugTab('acf-data')">ACF Configuration</a>
                <a href="#pricing-test" class="nav-tab" onclick="showDebugTab('pricing-test')">Pricing Test</a>
                <a href="#season-check" class="nav-tab" onclick="showDebugTab('season-check')">Season Detection</a>
                <a href="#min-nights" class="nav-tab" onclick="showDebugTab('min-nights')">Minimum Nights</a>
            </div>
            
            <!-- ACF Data Tab -->
            <div id="acf-data-tab" class="debug-tab-content">
                <div class="card">
                    <h2>🔧 ACF Configuration Data</h2>
                    
                    <h3>Standard Season</h3>
                    <pre><?php echo esc_html(print_r(array(
                        'dates' => get_field('standard_season_dates', 'option'),
                        'base_rate' => get_field('standard_base_rate', 'option'),
                        'extra_adult' => get_field('standard_extra_adult', 'option'),
                        'extra_child' => get_field('standard_extra_child', 'option')
                    ), true)); ?></pre>
                    
                    <h3>Peak Season</h3>
                    <pre><?php echo esc_html(print_r(array(
                        'dates' => get_field('peak_season_dates', 'option'),
                        'base_rate' => get_field('peak_base_rate', 'option'),
                        'extra_adult' => get_field('peak_extra_adult', 'option'),
                        'extra_child' => get_field('peak_extra_child', 'option')
                    ), true)); ?></pre>
                    
                    <h3>Christmas Season</h3>
                    <pre><?php echo esc_html(print_r(array(
                        'dates' => get_field('christmas_dates', 'option'),
                        'base_rate' => get_field('christmas_base_rate', 'option'),
                        'extra_adult' => get_field('christmas_extra_adult', 'option'),
                        'extra_child' => get_field('christmas_extra_child', 'option'),
                        'surcharge' => get_field('christmas_surcharge', 'option')
                    ), true)); ?></pre>
                    
                    <h3>Helicopter Packages</h3>
                    <pre><?php echo esc_html(print_r(get_field('helicopter_packages', 'option'), true)); ?></pre>
                    
                    <h3>General Settings</h3>
                    <pre><?php echo esc_html(print_r(array(
                        'max_adults' => get_field('max_adults', 'option'),
                        'max_children' => get_field('max_children', 'option'),
                        'children_free_age' => get_field('children_free_age', 'option'),
                        'default_min_nights' => get_field('default_min_nights', 'option'),
                        'heli_additional_hour' => get_field('heli_additional_hour', 'option')
                    ), true)); ?></pre>
                    
                    <h3>Minimum Nights Rules</h3>
                    <pre><?php echo esc_html(print_r(array(
                        'special_min_nights' => get_field('special_min_nights', 'option'),
                        'half_terms' => get_field('half_terms', 'option')
                    ), true)); ?></pre>
                </div>
            </div>
            
            <!-- Pricing Test Tab -->
            <div id="pricing-test-tab" class="debug-tab-content" style="display: none;">
                <div class="card">
                    <h2>🧮 Pricing Test</h2>
                    
                    <form id="pricing-test-form">
                        <table class="form-table">
                            <tr>
                                <th>Check-in Date</th>
                                <td><input type="date" id="test-checkin" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" /></td>
                            </tr>
                            <tr>
                                <th>Check-out Date</th>
                                <td><input type="date" id="test-checkout" value="<?php echo date('Y-m-d', strtotime('+10 days')); ?>" /></td>
                            </tr>
                            <tr>
                                <th>Adults</th>
                                <td><input type="number" id="test-adults" value="2" min="1" /></td>
                            </tr>
                            <tr>
                                <th>Children</th>
                                <td><input type="number" id="test-children" value="0" min="0" /></td>
                            </tr>
                        </table>
                        
                        <button type="button" class="button button-primary" onclick="testPricing()">Calculate Pricing</button>
                    </form>
                    
                    <div id="pricing-results" style="margin-top: 20px;"></div>
                </div>
            </div>
            
            <!-- Season Check Tab -->
            <div id="season-check-tab" class="debug-tab-content" style="display: none;">
                <div class="card">
                    <h2>📅 Season Detection Test</h2>
                    
                    <form>
                        <table class="form-table">
                            <tr>
                                <th>Test Date</th>
                                <td><input type="date" id="season-test-date" value="<?php echo date('Y-m-d'); ?>" /></td>
                            </tr>
                        </table>
                        
                        <button type="button" class="button button-primary" onclick="testSeason()">Check Season</button>
                    </form>
                    
                    <div id="season-results" style="margin-top: 20px;"></div>
                </div>
            </div>
            
            <!-- Minimum Nights Tab -->
            <div id="min-nights-tab" class="debug-tab-content" style="display: none;">
                <div class="card">
                    <h2>🌙 Minimum Nights Test</h2>
                    
                    <form>
                        <table class="form-table">
                            <tr>
                                <th>Test Date</th>
                                <td><input type="date" id="min-nights-date" value="<?php echo date('Y-m-d'); ?>" /></td>
                            </tr>
                        </table>
                        
                        <button type="button" class="button button-primary" onclick="testMinNights()">Check Minimum Nights</button>
                    </form>
                    
                    <div id="min-nights-results" style="margin-top: 20px;"></div>
                </div>
            </div>
            
            <style>
            .debug-tab-content {
                margin-top: 20px;
            }
            .nav-tab-wrapper {
                margin-bottom: 20px;
            }
            pre {
                background: #f4f4f4;
                padding: 15px;
                border-radius: 4px;
                overflow-x: auto;
                max-height: 400px;
                overflow-y: auto;
            }
            #pricing-results, #season-results, #min-nights-results {
                background: #f9f9f9;
                border: 1px solid #ddd;
                padding: 15px;
                border-radius: 4px;
            }
            </style>
            
            <script>
            function showDebugTab(tabName) {
                // Hide all tabs
                var tabs = document.querySelectorAll('.debug-tab-content');
                tabs.forEach(function(tab) {
                    tab.style.display = 'none';
                });
                
                // Remove active class from all nav tabs
                var navTabs = document.querySelectorAll('.nav-tab');
                navTabs.forEach(function(tab) {
                    tab.classList.remove('nav-tab-active');
                });
                
                // Show selected tab
                document.getElementById(tabName + '-tab').style.display = 'block';
                
                // Add active class to clicked nav tab
                event.target.classList.add('nav-tab-active');
            }
            
            function testPricing() {
                var checkin = document.getElementById('test-checkin').value;
                var checkout = document.getElementById('test-checkout').value;
                var adults = document.getElementById('test-adults').value;
                var children = document.getElementById('test-children').value;
                
                var data = {
                    action: 'calculate_booking_price',
                    nonce: '<?php echo wp_create_nonce('leobo_booking_system_nonce'); ?>',
                    checkin_date: checkin,
                    checkout_date: checkout,
                    adults: adults,
                    children: children
                };
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(data)
                })
                .then(response => response.json())
                .then(result => {
                    document.getElementById('pricing-results').innerHTML = '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
                })
                .catch(error => {
                    document.getElementById('pricing-results').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                });
            }
            
            function testSeason() {
                var testDate = document.getElementById('season-test-date').value;
                
                var data = {
                    action: 'debug_season_detection',
                    nonce: '<?php echo wp_create_nonce('leobo_booking_system_nonce'); ?>',
                    test_date: testDate
                };
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(data)
                })
                .then(response => response.json())
                .then(result => {
                    document.getElementById('season-results').innerHTML = '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
                })
                .catch(error => {
                    document.getElementById('season-results').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                });
            }
            
            function testMinNights() {
                var testDate = document.getElementById('min-nights-date').value;
                
                var data = {
                    action: 'debug_min_nights',
                    nonce: '<?php echo wp_create_nonce('leobo_booking_system_nonce'); ?>',
                    test_date: testDate
                };
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(data)
                })
                .then(response => response.json())
                .then(result => {
                    document.getElementById('min-nights-results').innerHTML = '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
                })
                .catch(error => {
                    document.getElementById('min-nights-results').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                });
            }
            </script>
        </div>
        <?php
    }
    
    /**
     * Check if database tables exist
     */
    private function check_database_tables() {
        global $wpdb;
        global $wpdb;
        $table_name = $wpdb->prefix . 'leobo_bookings';
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    }
    
    /**
     * Display recent bookings
     */
    private function display_recent_bookings() {
        try {
            $recent_bookings = $this->database->get_recent_bookings(5);
            
            if (empty($recent_bookings)) {
                echo '<p>No bookings yet.</p>';
                return;
            }
            
            echo '<table class="wp-list-table widefat">';
            echo '<thead><tr><th>Date</th><th>Guest</th><th>Dates</th><th>Status</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($recent_bookings as $booking) {
                echo '<tr>';
                echo '<td>' . esc_html(date('M j, Y', strtotime($booking->created_at))) . '</td>';
                echo '<td>' . esc_html($booking->first_name . ' ' . $booking->last_name) . '</td>';
                echo '<td>' . esc_html($booking->checkin_date . ' to ' . $booking->checkout_date) . '</td>';
                echo '<td>' . esc_html(ucfirst($booking->status ?? 'pending')) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            
        } catch (Exception $e) {
            echo '<p>Unable to load recent bookings.</p>';
        }
    }
    
    /**
     * AJAX: Debug season detection
     */
    public function ajax_debug_season() {
        check_ajax_referer('leobo_booking_system_nonce', 'nonce');
        
        $test_date = sanitize_text_field($_POST['test_date']);
        $date_obj = DateTime::createFromFormat('Y-m-d', $test_date);
        
        if (!$date_obj) {
            wp_send_json_error('Invalid date format');
            return;
        }
        
        // Get season data and detect season
        $seasons_data = $this->pricing->get_seasons_data();
        $detected_season = $this->detect_season_debug($date_obj, $seasons_data);
        
        wp_send_json_success(array(
            'test_date' => $test_date,
            'detected_season' => $detected_season,
            'seasons_data' => $seasons_data,
            'date_formatted' => $date_obj->format('d/m/Y')
        ));
    }
    
    /**
     * AJAX: Debug minimum nights
     */
    public function ajax_debug_min_nights() {
        check_ajax_referer('leobo_booking_system_nonce', 'nonce');
        
        $test_date = sanitize_text_field($_POST['test_date']);
        $date_obj = DateTime::createFromFormat('Y-m-d', $test_date);
        
        if (!$date_obj) {
            wp_send_json_error('Invalid date format');
            return;
        }
        
        // Get minimum nights rules
        $min_nights_rules = $this->pricing->get_minimum_nights_rules();
        $calculated_min_nights = $this->get_min_nights_debug($date_obj, $min_nights_rules);
        
        wp_send_json_success(array(
            'test_date' => $test_date,
            'calculated_min_nights' => $calculated_min_nights,
            'min_nights_rules' => $min_nights_rules,
            'date_formatted' => $date_obj->format('d/m/Y')
        ));
    }
    
    /**
     * Debug helper: Detect season for a date
     */
    private function detect_season_debug($date, $seasons_data) {
        $date_string = $date->format('d/m/Y');
        
        // Check Christmas dates first (highest priority)
        if (!empty($seasons_data['christmas']['dates'])) {
            foreach ($seasons_data['christmas']['dates'] as $range) {
                if ($this->date_in_range_debug($date_string, $range['christmas_start'], $range['christmas_end'])) {
                    return array(
                        'season' => 'christmas',
                        'matched_range' => $range,
                        'reasoning' => 'Matched Christmas date range'
                    );
                }
            }
        }
        
        // Check peak dates
        if (!empty($seasons_data['peak']['dates'])) {
            foreach ($seasons_data['peak']['dates'] as $range) {
                if ($this->date_in_range_debug($date_string, $range['peak_start'], $range['peak_end'])) {
                    return array(
                        'season' => 'peak',
                        'matched_range' => $range,
                        'reasoning' => 'Matched Peak date range'
                    );
                }
            }
        }
        
        // Check standard dates
        if (!empty($seasons_data['standard']['dates'])) {
            foreach ($seasons_data['standard']['dates'] as $range) {
                if ($this->date_in_range_debug($date_string, $range['standard_start'], $range['standard_end'])) {
                    return array(
                        'season' => 'standard',
                        'matched_range' => $range,
                        'reasoning' => 'Matched Standard date range'
                    );
                }
            }
        }
        
        // Default to standard if no specific season found
        return array(
            'season' => 'standard',
            'matched_range' => null,
            'reasoning' => 'Default fallback to standard season'
        );
    }
    
    /**
     * Debug helper: Get minimum nights for a date
     */
    private function get_min_nights_debug($date, $min_nights_rules) {
        $date_string = $date->format('d/m/Y');
        
        // Check half-terms first
        if (!empty($min_nights_rules['half_terms'])) {
            foreach ($min_nights_rules['half_terms'] as $period) {
                if ($this->date_in_range_debug($date_string, $period['half_term_start'], $period['half_term_end'])) {
                    return array(
                        'min_nights' => $period['half_term_min_nights'],
                        'rule_type' => 'half_term',
                        'matched_period' => $period,
                        'reasoning' => 'Matched half-term period'
                    );
                }
            }
        }
        
        // Check special minimums
        if (!empty($min_nights_rules['special'])) {
            foreach ($min_nights_rules['special'] as $period) {
                if ($this->date_in_range_debug($date_string, $period['special_min_start'], $period['special_min_end'])) {
                    return array(
                        'min_nights' => $period['special_min_value'],
                        'rule_type' => 'special',
                        'matched_period' => $period,
                        'reasoning' => 'Matched special minimum nights period'
                    );
                }
            }
        }
        
        // Return default
        return array(
            'min_nights' => $min_nights_rules['default'],
            'rule_type' => 'default',
            'matched_period' => null,
            'reasoning' => 'Using default minimum nights value'
        );
    }
    
    /**
     * Debug helper: Check if date is within a range
     */
    private function date_in_range_debug($date, $start, $end) {
        if (empty($start) || empty($end)) {
            return false;
        }
        
        $date_obj = DateTime::createFromFormat('d/m/Y', $date);
        $start_obj = DateTime::createFromFormat('d/m/Y', $start);
        $end_obj = DateTime::createFromFormat('d/m/Y', $end);
        
        return $date_obj && $start_obj && $end_obj && 
               $date_obj >= $start_obj && $date_obj <= $end_obj;
    }
}

// Initialize the system
new LeoboCustomBookingSystem();
