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
        
        // AJAX endpoints
        add_action('wp_ajax_calculate_booking_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_nopriv_calculate_booking_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_submit_booking_request', array($this, 'ajax_submit_booking'));
        add_action('wp_ajax_nopriv_submit_booking_request', array($this, 'ajax_submit_booking'));
        add_action('wp_ajax_get_accommodation_details', array($this, 'ajax_get_accommodation'));
        add_action('wp_ajax_nopriv_get_accommodation_details', array($this, 'ajax_get_accommodation'));
        
        // Shortcode
        add_shortcode('leobo_custom_booking_form', array($this, 'render_booking_form'));
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
        
        // Flatpickr for better date range selection
        wp_enqueue_script(
            'flatpickr',
            'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js',
            array(),
            '4.6.13',
            true
        );
        
        wp_enqueue_style(
            'flatpickr-css',
            'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css',
            array(),
            '4.6.13'
        );
        
        // Booking form JavaScript
        wp_enqueue_script(
            'leobo-booking-form',
            $this->plugin_url . '/assets/js/booking-form.js',
            array('jquery', 'flatpickr'),
            filemtime($this->plugin_path . '/assets/js/booking-form.js'),
            true
        );
        
        // Booking form styles
        wp_enqueue_style(
            'leobo-booking-form-styles',
            $this->plugin_url . '/assets/css/booking-form-styles.css',
            array('flatpickr-css'),
            filemtime($this->plugin_path . '/assets/css/booking-form-styles.css')
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
                'blocked_dates' => $this->get_blocked_dates_js_format()
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
                'blocked_dates' => array()
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
        global $leobo_booking_availability;
        
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
        global $leobo_booking_availability;
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
                    
                    <h4>Step 2: Configure Your Accommodations</h4>
                    <p>Edit the file: <code>/includes/BookingPricing.php</code> to set up your accommodation options and pricing.</p>
                    
                    <h4>Step 3: Set Up Email Notifications</h4>
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
                        <li>Ensure Flatpickr library is loading</li>
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
     * Check if database tables exist
     */
    private function check_database_tables() {
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
}

// Initialize the system
new LeoboCustomBookingSystem();
