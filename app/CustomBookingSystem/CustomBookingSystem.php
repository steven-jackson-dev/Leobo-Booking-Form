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
        $this->database = new BookingDatabase();
        $this->email = new BookingEmail();
        
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
        
        // Admin AJAX endpoints
        add_action('wp_ajax_get_submission_details', array($this, 'ajax_get_submission_details'));
        
        // Shortcode
        add_shortcode('leobo_custom_booking_form', array($this, 'render_booking_form'));
        add_shortcode('leobo_test_booking_form', array($this, 'render_test_booking_form'));
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
            '3.9.3', // Enhanced blocked dates debugging
            true
        );
        
        // Test booking form JavaScript (only if test form is present or in admin)
        $load_test_script = false;
        
        // Check if we're in admin area or test form is present
        if (is_admin()) {
            $load_test_script = true;
        } elseif (get_post() && has_shortcode(get_post()->post_content ?? '', 'leobo_test_booking_form')) {
            $load_test_script = true;
        } elseif (isset($_GET['page']) && $_GET['page'] === 'leobo-booking-test-forms') {
            $load_test_script = true;
        }
        
        if ($load_test_script) {
            wp_enqueue_script(
                'leobo-test-booking-form',
                $this->plugin_url . '/assets/js/test-booking-form.js',
                array('jquery', 'leobo-booking-form'),
                '1.0.1', // Updated version for fixes
                true
            );
        }
        
        // Booking form styles
        wp_enqueue_style(
            'leobo-booking-form-styles',
            $this->plugin_url . '/assets/css/booking-form-styles.css',
            array(),
            '3.9.3' // Enhanced blocked dates debugging
        );
        
        // Localize script
        try {
            $frontend_data = $this->pricing->get_frontend_data();
            $blocked_dates = $this->get_blocked_dates_for_frontend();
            
            // Debug: Log what blocked dates are being sent to frontend
            error_log('=== Script Localization ===');
            error_log('Blocked dates being sent to frontend (' . count($blocked_dates) . ' total): ' . print_r($blocked_dates, true));
            
            wp_localize_script('leobo-booking-form', 'leobo_booking_system', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('leobo_booking_system_nonce'),
                'currency_symbol' => 'R',
                'accommodations' => $frontend_data['accommodations'] ?? array(),
                'packages' => $frontend_data['packages'] ?? array(),
                'seasons' => $frontend_data['seasons'] ?? array(),
                'guest_rules' => $frontend_data['guest_rules'] ?? array(),
                'blocked_dates' => $blocked_dates,
                'season_dates' => $this->get_season_dates_for_calendar(),
                'acf_config' => array(
                    'adults_max' => get_field('max_adults', 'option') ?: 6,  // Maximum 6 adults in house
                    'children_max' => get_field('max_children', 'option') ?: 8,
                    'babies_max' => get_field('max_babies', 'option') ?: 4,
                    'minimum_nights_standard' => get_field('default_min_nights', 'option') ?: 2  // Minimum 2 nights
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
                    'adults_max' => 6,  // Maximum 6 adults in house
                    'children_max' => 8,
                    'babies_max' => 4,
                    'minimum_nights_standard' => 2  // Minimum 2 nights
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
     * Render the test booking form with pre-filled data
     * Now uses the same template as live form but with test_mode enabled
     */
    public function render_test_booking_form($atts = array()) {
        $atts = shortcode_atts(array(
            'show_title' => true,
            'show_description' => true,
            'theme' => 'test',
            'test_mode' => true,
            'embedded_admin' => false
        ), $atts);
        
        // Convert string booleans to actual booleans
        $atts['show_title'] = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_description'] = filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN);
        $atts['test_mode'] = filter_var($atts['test_mode'], FILTER_VALIDATE_BOOLEAN);
        $atts['embedded_admin'] = filter_var($atts['embedded_admin'], FILTER_VALIDATE_BOOLEAN);
        
        $args = array(
            'form_id' => $this->form_id,
            'attributes' => $atts
        );
        
        // Use the same template as live form, but with test mode enabled
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
            
            // Skip availability validation since this is an enquiry form, not a booking system
            // Real bookings would need availability validation, but enquiries should always allow price calculation
            
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
        // Add comprehensive debugging
        error_log('=== BOOKING SUBMISSION DEBUG START ===');
        error_log('Raw $_POST data: ' . print_r($_POST, true));
        
        // Temporarily disable nonce check for debugging
        // if (!check_ajax_referer('leobo_booking_system_nonce', 'nonce', false)) {
        //     error_log('NONCE VERIFICATION FAILED');
        //     wp_send_json_error('Security verification failed');
        //     return;
        // }
        
        try {
            // Sanitize input
            $booking_data = $this->sanitize_booking_data($_POST);
            error_log('Sanitized booking data: ' . print_r($booking_data, true));
        
            // Check if this is a test submission
            $is_test = isset($_POST['is_test_submission']) && $_POST['is_test_submission'] == '1';
            
            if ($is_test) {
                // Add test identifier to booking data
                $booking_data['is_test_booking'] = true;
                $booking_data['booking_source'] = 'Test Form Submission';
                
                // Log test submission
                error_log('🧪 Test booking submission received: ' . print_r($booking_data, true));
            }
            
            error_log('=== BOOKING SUBMISSION DEBUG END ===');
            
            // Skip availability validation since this is an enquiry form, not a booking system
            // Real bookings would need availability validation, but enquiries should always be allowed
            
            // Save booking
            $booking_id = $this->database->save_booking($booking_data);
            
            if ($booking_id) {
                // Send notifications (with test identifier in subject if test)
                if ($is_test) {
                    $this->email->send_admin_notification($booking_id, $booking_data, '[TEST]');
                    $this->email->send_user_confirmation($booking_id, $booking_data, '[TEST]');
                } else {
                    $this->email->send_admin_notification($booking_id, $booking_data);
                    $this->email->send_user_confirmation($booking_id, $booking_data);
                }
                
                // Allow custom actions
                do_action('leobo_booking_submitted', $booking_id, $booking_data);
                
                $message = $is_test ? 
                    '🧪 Test booking submitted successfully! Check admin panel for new entry.' : 
                    'Booking request submitted successfully!';
                
                wp_send_json_success(array(
                    'message' => $message,
                    'booking_id' => $booking_id,
                    'is_test' => $is_test
                ));
            } else {
                wp_send_json_error('Failed to save booking request');
            }
            
        } catch (Exception $e) {
            error_log('BOOKING SUBMISSION ERROR: ' . $e->getMessage());
            wp_send_json_error('Server error: ' . $e->getMessage());
        }
    }
    
    /**
     * Sanitize booking form data
     */
    private function sanitize_booking_data($post_data) {
        $data = array(
            'checkin_date' => sanitize_text_field($post_data['checkin_date']),
            'checkout_date' => sanitize_text_field($post_data['checkout_date']),
            'adults' => intval($post_data['adults'] ?? 2),
            'children' => intval($post_data['children'] ?? 0),
            'babies' => intval($post_data['babies'] ?? 0),
            'accommodation' => sanitize_text_field($post_data['accommodation'] ?? ''),
            'helicopter_package' => isset($post_data['helicopter_package']) ? sanitize_text_field($post_data['helicopter_package']) : null,
            'transfer_options' => isset($post_data['transfer']) ? implode(', ', array_map('sanitize_text_field', $post_data['transfer'])) : null,
            'experiences' => isset($post_data['experiences']) ? implode(', ', array_map('sanitize_text_field', $post_data['experiences'])) : null,
            'occasion' => sanitize_text_field($post_data['occasion'] ?? ''),
            'full_name' => sanitize_text_field($post_data['full_name']),
            'email' => sanitize_email($post_data['email']),
            'phone' => sanitize_text_field($post_data['contact_number'] ?? $post_data['phone'] ?? ''),
            'home_address' => sanitize_textarea_field($post_data['home_address'] ?? ''),
            'country' => sanitize_text_field($post_data['country'] ?? ''),
            'how_heard' => sanitize_text_field($post_data['how_heard'] ?? ''),
            'special_requests' => sanitize_textarea_field($post_data['special_requests'] ?? ''),
            'children_interests' => sanitize_textarea_field($post_data['children_interests'] ?? ''),
            'flexible_dates' => isset($post_data['flexible_dates']) ? 1 : 0,
            'calculated_total' => floatval($post_data['calculated_total'] ?? 0)
        );
        
        // Add test submission data if present
        if (isset($post_data['is_test_submission']) && $post_data['is_test_submission'] == '1') {
            $data['is_test_booking'] = true;
            $data['booking_source'] = 'Test Form Submission';
            $data['test_submission_time'] = current_time('mysql');
        }
        
        return $data;
    }
    
    /**
     * Validate date availability
     */
    private function validate_availability($checkin, $checkout) {
        global $wpdb, $leobo_booking_availability;
        
        $start_date = new DateTime($checkin);
        $end_date = new DateTime($checkout);
        $current_date = clone $start_date;
        
        // Check if there's test data active
        $test_data = get_transient('leobo_test_blocked_dates');
        if ($test_data && isset($test_data['api_response']['availability_data'])) {
            // Use test data for validation
            while ($current_date < $end_date) {
                $date_string = $current_date->format('Y-m-d');
                
                // Check test blocked dates
                foreach ($test_data['api_response']['availability_data'] as $room_data) {
                    if (isset($room_data[$date_string]) && $room_data[$date_string]['availability'] == 0) {
                        return false; // Date is blocked in test data
                    }
                }
                
                $current_date->add(new DateInterval('P1D'));
            }
        } else {
            // Use live availability data
            while ($current_date < $end_date) {
                if (!$leobo_booking_availability->is_date_available($current_date->format('Y-m-d'))) {
                    return false;
                }
                $current_date->add(new DateInterval('P1D'));
            }
        }
        
        return true;
    }
    
    /**
     * Get blocked dates for JavaScript including test data if active
     * Note: Last day of consecutive blocked periods is made available for check-in
     */
    private function get_blocked_dates_for_frontend() {
        // Debug: Log what we're checking
        error_log('=== get_blocked_dates_for_frontend() called ===');
        
        // Check if test data is active first
        $test_data = get_transient('leobo_test_blocked_dates');
        
        // Debug: Log test data status
        if ($test_data) {
            error_log('✅ Test data found! Processing test blocked dates...');
            error_log('Test data structure: ' . print_r($test_data, true));
        } else {
            error_log('❌ No test data found, will use live API data');
        }
        
        if ($test_data && isset($test_data['api_response']['availability_data'])) {
            // Extract blocked dates from test API response
            $raw_blocked_dates = array();
            
            error_log('Processing test API response data...');
            foreach ($test_data['api_response']['availability_data'] as $room_index => $room_data) {
                error_log("Processing room data index {$room_index}:");
                
                foreach ($room_data as $date => $availability) {
                    // Skip non-date keys
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        error_log("Skipping non-date key: {$date}");
                        continue;
                    }
                    
                    error_log("Checking date {$date}: availability = " . ($availability['availability'] ?? 'N/A'));
                    
                    // Collect dates where availability is 0
                    if (isset($availability['availability']) && $availability['availability'] == 0) {
                        $raw_blocked_dates[] = $date;
                        error_log("🔴 Date {$date} collected as blocked (availability = 0)");
                    } else {
                        error_log("🟢 Date {$date} available (availability = " . ($availability['availability'] ?? 'N/A') . ")");
                    }
                }
            }
            
            // Sort and deduplicate
            $raw_blocked_dates = array_unique($raw_blocked_dates);
            sort($raw_blocked_dates);
            
            // Process to allow check-in on last day of consecutive blocked periods
            $final_blocked_dates = $this->process_blocked_periods($raw_blocked_dates);
            
            error_log('✅ Final test blocked dates after processing (' . count($final_blocked_dates) . ' total): ' . print_r($final_blocked_dates, true));
            return $final_blocked_dates;
        }
        
        // Fall back to live API data
        error_log('📡 Using live API data...');
        global $leobo_booking_availability;
        $live_blocked_dates = $leobo_booking_availability->get_blocked_dates(1, 'Y-m-d'); // API returns all dates regardless of range
        error_log('Live blocked dates (' . count($live_blocked_dates) . ' total): ' . print_r($live_blocked_dates, true));
        return $live_blocked_dates;
    }
    
    /**
     * Process blocked dates to allow check-in on the last day of consecutive periods
     * (Same logic as in BookingAvailability.php)
     */
    private function process_blocked_periods($blocked_dates, $format = 'Y-m-d') {
        if (empty($blocked_dates)) {
            return array();
        }
        
        $final_blocked = array();
        $consecutive_periods = $this->group_consecutive_dates($blocked_dates);
        
        foreach ($consecutive_periods as $period) {
            // For each consecutive period, block all dates except the last one
            // The last day becomes available for new check-ins (guests check out in morning)
            if (count($period) > 1) {
                // Remove the last date from blocking (allow check-in on checkout day)
                $period_to_block = array_slice($period, 0, -1);
                foreach ($period_to_block as $date) {
                    $final_blocked[] = date($format, strtotime($date));
                }
            }
            // If it's a single day block, we still block it completely
            // (no one can check in or out on the same day)
            else {
                foreach ($period as $date) {
                    $final_blocked[] = date($format, strtotime($date));
                }
            }
        }
        
        return array_unique($final_blocked);
    }
    
    /**
     * Group consecutive dates into periods
     * (Same logic as in BookingAvailability.php)
     */
    private function group_consecutive_dates($dates) {
        if (empty($dates)) {
            return array();
        }
        
        sort($dates);
        $periods = array();
        $current_period = array($dates[0]);
        
        for ($i = 1; $i < count($dates); $i++) {
            $prev_date = new DateTime($dates[$i - 1]);
            $curr_date = new DateTime($dates[$i]);
            $diff = $prev_date->diff($curr_date)->days;
            
            if ($diff === 1) {
                // Consecutive date, add to current period
                $current_period[] = $dates[$i];
            } else {
                // Gap found, start new period
                $periods[] = $current_period;
                $current_period = array($dates[$i]);
            }
        }
        
        // Add the last period
        $periods[] = $current_period;
        
        return $periods;
    }
    
    /**
     * Get blocked dates for JavaScript
     */
    private function get_blocked_dates_js_format() {
        global $wpdb, $leobo_booking_availability;
        return $leobo_booking_availability->get_blocked_dates(1, 'Y-m-d'); // API returns all dates regardless of range
    }
    
    /**
     * Get season dates for calendar color coding
     */
    private function get_season_dates_for_calendar() {
        $seasons_data = array(
            'standard' => array(),
            'peak' => array(),
            'christmas' => array()
        );
        
        // Get Standard Season dates
        $standard_dates = get_field('standard_season_dates', 'option');
        if (!empty($standard_dates)) {
            foreach ($standard_dates as $date_range) {
                if (!empty($date_range['standard_start']) && !empty($date_range['standard_end'])) {
                    $seasons_data['standard'][] = array(
                        'start' => $this->convert_date_format($date_range['standard_start']),
                        'end' => $this->convert_date_format($date_range['standard_end'])
                    );
                }
            }
        }
        
        // Get Peak Season dates
        $peak_dates = get_field('peak_season_dates', 'option');
        if (!empty($peak_dates)) {
            foreach ($peak_dates as $date_range) {
                if (!empty($date_range['peak_start']) && !empty($date_range['peak_end'])) {
                    $seasons_data['peak'][] = array(
                        'start' => $this->convert_date_format($date_range['peak_start']),
                        'end' => $this->convert_date_format($date_range['peak_end'])
                    );
                }
            }
        }
        
        // Get Christmas Season dates
        $christmas_dates = get_field('christmas_dates', 'option');
        if (!empty($christmas_dates)) {
            foreach ($christmas_dates as $date_range) {
                if (!empty($date_range['christmas_start']) && !empty($date_range['christmas_end'])) {
                    $seasons_data['christmas'][] = array(
                        'start' => $this->convert_date_format($date_range['christmas_start']),
                        'end' => $this->convert_date_format($date_range['christmas_end'])
                    );
                }
            }
        }
        
        return $seasons_data;
    }
    
    /**
     * Convert date format from DD/MM/YYYY to YYYY-MM-DD
     */
    private function convert_date_format($date_string) {
        if (empty($date_string)) {
            return '';
        }
        
        // Try to parse the date in DD/MM/YYYY format
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date_string, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "{$year}-{$month}-{$day}";
        }
        
        // If already in YYYY-MM-DD format, return as is
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_string)) {
            return $date_string;
        }
        
        // Try to parse with DateTime as fallback
        try {
            $date = DateTime::createFromFormat('d/m/Y', $date_string);
            if ($date) {
                return $date->format('Y-m-d');
            }
        } catch (Exception $e) {
            error_log('Date conversion error: ' . $e->getMessage());
        }
        
        return '';
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
            'Booking Submissions',           // Page title
            'Submissions',                   // Menu title
            'manage_options',                // Capability
            'leobo-booking-submissions',    // Menu slug
            array($this, 'submissions_page') // Function
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
            'API Testing',                   // Page title
            'API Testing',                   // Menu title
            'manage_options',                // Capability
            'leobo-booking-api-testing',    // Menu slug
            array($this, 'api_testing_page') // Function
        );
        
        add_submenu_page(
            'leobo-booking-system',          // Parent slug
            'Debug Data',                    // Page title
            'Debug Data',                    // Menu title
            'manage_options',                // Capability
            'leobo-booking-debug',          // Menu slug
            array($this, 'debug_page')      // Function
        );
        
        add_submenu_page(
            'leobo-booking-system',          // Parent slug
            'Test Forms',                    // Page title
            'Test Forms',                    // Menu title
            'manage_options',                // Capability
            'leobo-booking-test-forms',     // Menu slug
            array($this, 'test_forms_page') // Function
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
     * Check if database tables exist
     */
    private function check_database_tables() {
        return $this->database->table_exists();
    }
    
    /**
     * Display recent bookings in admin
     */
    private function display_recent_bookings() {
        $bookings = $this->database->get_bookings(10);
        
        if (empty($bookings)) {
            echo '<p>No bookings found. The database table will be created automatically when the first booking is submitted.</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat">';
        echo '<thead><tr>';
        echo '<th>ID</th><th>Guest Name</th><th>Check-in</th><th>Check-out</th><th>Adults</th><th>Children</th><th>Status</th><th>Test</th><th>Date</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($bookings as $booking) {
            $test_badge = $booking->is_test_booking ? '<span style="background: #ff9800; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;">TEST</span>' : '';
            echo '<tr>';
            echo '<td>' . esc_html($booking->id) . '</td>';
            echo '<td>' . esc_html(
                !empty($booking->full_name) ? $booking->full_name : 
                trim(($booking->first_name ?? '') . ' ' . ($booking->last_name ?? ''))
            ) . '</td>';
            echo '<td>' . esc_html($booking->checkin_date) . '</td>';
            echo '<td>' . esc_html($booking->checkout_date) . '</td>';
            echo '<td>' . esc_html($booking->adults) . '</td>';
            echo '<td>' . esc_html($booking->children) . '</td>';
            echo '<td>' . esc_html(ucfirst($booking->status)) . '</td>';
            echo '<td>' . $test_badge . '</td>';
            echo '<td>' . esc_html(date('M j, Y', strtotime($booking->created_at))) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Display recent test submissions
     */
    private function display_test_submissions() {
        $test_submissions = $this->database->get_test_submissions(20);
        
        if (empty($test_submissions)) {
            echo '<p>No test submissions found yet. Database table not found. Please ensure the booking system is properly set up.</p>';
            echo '<p><strong>Note:</strong> The database table will be created automatically when the first booking (test or live) is submitted.</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat">';
        echo '<thead><tr>';
        echo '<th>ID</th><th>Test Date</th><th>Guest Name</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Accommodation</th><th>Total</th><th>Status</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($test_submissions as $submission) {
            echo '<tr>';
            echo '<td><strong>#' . esc_html($submission->id) . '</strong></td>';
            echo '<td>' . esc_html(date('M j, Y H:i', strtotime($submission->created_at))) . '</td>';
            echo '<td>' . esc_html(
                !empty($submission->full_name) ? $submission->full_name : 
                trim(($submission->first_name ?? '') . ' ' . ($submission->last_name ?? ''))
            ) . '</td>';
            echo '<td>' . esc_html($submission->checkin_date) . '</td>';
            echo '<td>' . esc_html($submission->checkout_date) . '</td>';
            echo '<td>' . esc_html($submission->adults . ' adults, ' . $submission->children . ' children') . '</td>';
            echo '<td>' . esc_html($submission->accommodation) . '</td>';
            echo '<td>R ' . esc_html(number_format($submission->total_amount, 2)) . '</td>';
            echo '<td><span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;">' . esc_html(ucfirst($submission->status)) . '</span></td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        
        echo '<p style="margin-top: 15px; color: #666;"><strong>Total Test Submissions:</strong> ' . count($test_submissions) . '</p>';
    }
    
    /**
     * Booking Submissions page - View all booking submissions
     */
    public function submissions_page() {
        // Ensure database table is up to date
        $this->database->create_table();
        
        // Handle bulk actions
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete' && isset($_POST['submission_ids'])) {
            if (wp_verify_nonce($_POST['bulk_action_nonce'], 'bulk_submissions_action')) {
                $deleted_count = 0;
                foreach ($_POST['submission_ids'] as $submission_id) {
                    if ($this->database->delete_booking(intval($submission_id))) {
                        $deleted_count++;
                    }
                }
                echo '<div class="notice notice-success"><p>' . sprintf('%d submission(s) deleted successfully.', $deleted_count) . '</p></div>';
            }
        }
        
        // Handle single delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['submission_id'])) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'delete_submission_' . $_GET['submission_id'])) {
                if ($this->database->delete_booking(intval($_GET['submission_id']))) {
                    echo '<div class="notice notice-success"><p>Submission deleted successfully.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Failed to delete submission.</p></div>';
                }
            }
        }
        
        // Get filter parameters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $test_filter = isset($_GET['test_mode']) ? sanitize_text_field($_GET['test_mode']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // Get submissions with filters
        $submissions = $this->database->get_bookings_with_filters(50, $status_filter, $test_filter, $search);
        $total_submissions = $this->database->get_submissions_count();
        $test_submissions_count = $this->database->get_test_submissions_count();
        $live_submissions_count = $total_submissions - $test_submissions_count;
        
        ?>
        <div class="wrap">
            <h1>📋 Booking Submissions</h1>
            
            <!-- Summary Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card" style="padding: 20px; text-align: center; max-width: none;">
                    <h2 style="margin: 0; color: #2271b1; font-size: 2.5em;"><?php echo $total_submissions; ?></h2>
                    <p style="margin: 5px 0 0 0; color: #646970;">Total Submissions</p>
                </div>
                <div class="card" style="padding: 20px; text-align: center; max-width: none;">
                    <h2 style="margin: 0; color: #00a32a; font-size: 2.5em;"><?php echo $live_submissions_count; ?></h2>
                    <p style="margin: 5px 0 0 0; color: #646970;">Live Bookings</p>
                </div>
                <div class="card" style="padding: 20px; text-align: center; max-width: none;">
                    <h2 style="margin: 0; color: #dba617; font-size: 2.5em;"><?php echo $test_submissions_count; ?></h2>
                    <p style="margin: 5px 0 0 0; color: #646970;">Test Submissions</p>
                </div>
                <div class="card" style="padding: 20px; text-align: center; max-width: none;">
                    <h2 style="margin: 0; color: #d63638; font-size: 2.5em;"><?php echo count(array_filter($submissions, function($s) { return $s->status === 'pending'; })); ?></h2>
                    <p style="margin: 5px 0 0 0; color: #646970;">Pending Review</p>
                </div>
            </div>
            
            <!-- Filters and Search -->
            <div class="card" style="padding: 20px; margin-bottom: 20px; max-width: none;">
                <form method="get" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                    <input type="hidden" name="page" value="leobo-booking-submissions">
                    
                    <div>
                        <label for="status-filter" style="display: block; margin-bottom: 5px; font-weight: 600;">Status:</label>
                        <select name="status" id="status-filter">
                            <option value="">All Status</option>
                            <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
                            <option value="confirmed" <?php selected($status_filter, 'confirmed'); ?>>Confirmed</option>
                            <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="test-filter" style="display: block; margin-bottom: 5px; font-weight: 600;">Type:</label>
                        <select name="test_mode" id="test-filter">
                            <option value="">All Submissions</option>
                            <option value="live" <?php selected($test_filter, 'live'); ?>>Live Bookings Only</option>
                            <option value="test" <?php selected($test_filter, 'test'); ?>>Test Submissions Only</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search-submissions" style="display: block; margin-bottom: 5px; font-weight: 600;">Search:</label>
                        <input type="search" name="s" id="search-submissions" value="<?php echo esc_attr($search); ?>" placeholder="Search by name, email..." style="width: 250px;">
                    </div>
                    
                    <div>
                        <input type="submit" class="button" value="Filter">
                        <a href="<?php echo admin_url('admin.php?page=leobo-booking-submissions'); ?>" class="button">Reset</a>
                    </div>
                </form>
            </div>
            
            <!-- Submissions Table -->
            <div class="card" style="padding: 0; max-width: none;">
                <?php if (empty($submissions)): ?>
                    <div style="padding: 40px; text-align: center; color: #646970;">
                        <h3>No submissions found</h3>
                        <p>No booking submissions match your current filters.</p>
                        <?php if ($total_submissions === 0): ?>
                            <p>The database table will be created automatically when the first booking is submitted.</p>
                            <p><a href="<?php echo admin_url('admin.php?page=leobo-booking-test-forms'); ?>" class="button button-primary">Try Test Form</a></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <form method="post" id="submissions-form">
                        <?php wp_nonce_field('bulk_submissions_action', 'bulk_action_nonce'); ?>
                        
                        <!-- Bulk Actions -->
                        <div style="padding: 15px 20px; border-bottom: 1px solid #c3c4c7; background: #f6f7f7;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <select name="action" id="bulk-action">
                                    <option value="">Bulk Actions</option>
                                    <option value="bulk_delete">Delete</option>
                                </select>
                                <input type="submit" class="button" value="Apply" onclick="return confirm('Are you sure you want to perform this bulk action?')">
                                <span style="margin-left: 20px; color: #646970;">
                                    <?php echo count($submissions); ?> submission(s) shown
                                </span>
                            </div>
                        </div>
                        
                        <!-- Table -->
                        <div style="overflow-x: auto;">
                            <table class="wp-list-table widefat fixed striped" style="margin: 0;">
                                <thead>
                                    <tr>
                                        <td class="manage-column check-column"><input type="checkbox" id="select-all"></td>
                                        <th>ID</th>
                                        <th>Guest Name</th>
                                        <th>Email</th>
                                        <th>Dates</th>
                                        <th>Guests</th>
                                        <th>Status</th>
                                        <th>Type</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr>
                                            <th class="check-column">
                                                <input type="checkbox" name="submission_ids[]" value="<?php echo $submission->id; ?>">
                                            </th>
                                            <td><strong>#<?php echo $submission->id; ?></strong></td>
                                            <td>
                                                <strong><?php 
                                                    // Handle both new (full_name) and legacy (first_name + last_name) data
                                                    if (!empty($submission->full_name)) {
                                                        echo esc_html($submission->full_name);
                                                    } elseif (isset($submission->first_name) || isset($submission->last_name)) {
                                                        echo esc_html(trim(($submission->first_name ?? '') . ' ' . ($submission->last_name ?? '')));
                                                    } else {
                                                        echo 'No name provided';
                                                    }
                                                ?></strong>
                                                <div style="color: #646970; font-size: 12px;">
                                                    <?php echo esc_html($submission->phone ?? $submission->contact_number ?? 'No phone'); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo esc_attr($submission->email); ?>">
                                                    <?php echo esc_html($submission->email); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <strong><?php echo date('M j', strtotime($submission->checkin_date)); ?> - <?php echo date('M j, Y', strtotime($submission->checkout_date)); ?></strong>
                                                <div style="color: #646970; font-size: 12px;">
                                                    <?php 
                                                    $checkin = new DateTime($submission->checkin_date);
                                                    $checkout = new DateTime($submission->checkout_date);
                                                    $nights = $checkin->diff($checkout)->days;
                                                    echo $nights . ' night' . ($nights !== 1 ? 's' : '');
                                                    ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $adults = $submission->adults ?? $submission->guests ?? 0;
                                                $children = $submission->children ?? 0;
                                                $babies = $submission->babies ?? 0;
                                                echo $adults . ' adults';
                                                if ($children > 0) echo ', ' . $children . ' children';
                                                if ($babies > 0) echo ', ' . $babies . ' babies';
                                                ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $submission->status; ?>">
                                                    <?php echo ucfirst($submission->status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (isset($submission->is_test_booking) && $submission->is_test_booking): ?>
                                                    <span class="test-badge">🧪 TEST</span>
                                                <?php else: ?>
                                                    <span class="live-badge">✅ LIVE</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($submission->created_at)); ?>
                                                <div style="color: #646970; font-size: 12px;">
                                                    <?php echo date('H:i', strtotime($submission->created_at)); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#" onclick="showSubmissionDetails(<?php echo $submission->id; ?>); return false;" class="button button-small">View</a>
                                                <a href="<?php echo wp_nonce_url(add_query_arg(['action' => 'delete', 'submission_id' => $submission->id]), 'delete_submission_' . $submission->id); ?>" 
                                                   class="button button-small button-link-delete" 
                                                   onclick="return confirm('Are you sure you want to delete this submission?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Submission Details Modal -->
        <div id="submission-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80%; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 15px;">
                    <h2 style="margin: 0;">Submission Details</h2>
                    <button onclick="closeSubmissionModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                </div>
                <div id="submission-details-content">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
        
        <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .test-badge {
            background: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
        }
        .live-badge {
            background: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
        }
        
        #select-all:checked + label::before,
        input[type="checkbox"]:checked::before {
            content: "✓";
        }
        </style>
        
        <script>
        // Select all checkbox functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="submission_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
        
        // Show submission details
        function showSubmissionDetails(submissionId) {
            // Show modal
            document.getElementById('submission-modal').style.display = 'block';
            document.getElementById('submission-details-content').innerHTML = '<p>Loading...</p>';
            
            // Load content via AJAX
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_submission_details&submission_id=' + submissionId + '&nonce=<?php echo wp_create_nonce("get_submission_details"); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('submission-details-content').innerHTML = data.data.html;
                } else {
                    document.getElementById('submission-details-content').innerHTML = '<p>Error loading submission details.</p>';
                }
            })
            .catch(error => {
                document.getElementById('submission-details-content').innerHTML = '<p>Error loading submission details.</p>';
            });
        }
        
        // Close modal
        function closeSubmissionModal() {
            document.getElementById('submission-modal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('submission-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSubmissionModal();
            }
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for submission details
     */
    public function ajax_get_submission_details() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'get_submission_details')) {
            wp_die('Security check failed');
        }
        
        $submission_id = intval($_POST['submission_id']);
        $submission = $this->database->get_booking_by_id($submission_id);
        
        if (!$submission) {
            wp_send_json_error('Submission not found');
            return;
        }
        
        // Build HTML for submission details
        ob_start();
        ?>
        <div style="line-height: 1.6;">
            <!-- Header Info -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 6px;">
                <div>
                    <h4 style="margin: 0 0 10px 0; color: #2271b1;">Booking Information</h4>
                    <p style="margin: 5px 0;"><strong>ID:</strong> #<?php echo $submission->id; ?></p>
                    <p style="margin: 5px 0;"><strong>Status:</strong> 
                        <span class="status-badge status-<?php echo $submission->status; ?>">
                            <?php echo ucfirst($submission->status); ?>
                        </span>
                    </p>
                    <p style="margin: 5px 0;"><strong>Type:</strong> 
                        <?php if (isset($submission->is_test_booking) && $submission->is_test_booking): ?>
                            <span class="test-badge">🧪 TEST SUBMISSION</span>
                        <?php else: ?>
                            <span class="live-badge">✅ LIVE BOOKING</span>
                        <?php endif; ?>
                    </p>
                    <p style="margin: 5px 0;"><strong>Submitted:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($submission->created_at)); ?></p>
                </div>
                
                <div>
                    <h4 style="margin: 0 0 10px 0; color: #2271b1;">Stay Details</h4>
                    <p style="margin: 5px 0;"><strong>Check-in:</strong> <?php echo date('F j, Y', strtotime($submission->checkin_date)); ?></p>
                    <p style="margin: 5px 0;"><strong>Check-out:</strong> <?php echo date('F j, Y', strtotime($submission->checkout_date)); ?></p>
                    <p style="margin: 5px 0;"><strong>Duration:</strong> 
                        <?php 
                        $checkin = new DateTime($submission->checkin_date);
                        $checkout = new DateTime($submission->checkout_date);
                        $nights = $checkin->diff($checkout)->days;
                        echo $nights . ' night' . ($nights !== 1 ? 's' : '');
                        ?>
                    </p>
                    <p style="margin: 5px 0;"><strong>Guests:</strong> 
                        <?php 
                        $adults = $submission->adults ?? $submission->guests ?? 0;
                        $children = $submission->children ?? 0;
                        $babies = $submission->babies ?? 0;
                        echo $adults . ' adults';
                        if ($children > 0) echo ', ' . $children . ' children';
                        if ($babies > 0) echo ', ' . $babies . ' babies';
                        ?>
                    </p>
                </div>
            </div>
            
            <!-- Guest Information -->
            <div style="margin-bottom: 25px;">
                <h4 style="margin: 0 0 15px 0; color: #2271b1; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Guest Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <p style="margin: 8px 0;"><strong>Name:</strong> <?php 
                            // Handle both new (full_name) and legacy (first_name + last_name) data
                            if (!empty($submission->full_name)) {
                                echo esc_html($submission->full_name);
                            } elseif (isset($submission->first_name) || isset($submission->last_name)) {
                                echo esc_html(trim(($submission->first_name ?? '') . ' ' . ($submission->last_name ?? '')));
                            } else {
                                echo 'No name provided';
                            }
                        ?></p>
                        <p style="margin: 8px 0;"><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($submission->email); ?>"><?php echo esc_html($submission->email); ?></a></p>
                        <p style="margin: 8px 0;"><strong>Phone:</strong> <?php echo esc_html($submission->phone ?? $submission->contact_number ?? 'Not provided'); ?></p>
                        <?php if (isset($submission->home_address) && !empty($submission->home_address)): ?>
                        <p style="margin: 8px 0;"><strong>Address:</strong> <?php echo esc_html($submission->home_address); ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if (isset($submission->country) && !empty($submission->country)): ?>
                            <p style="margin: 8px 0;"><strong>Country:</strong> <?php echo esc_html($submission->country); ?></p>
                        <?php endif; ?>
                        <?php if (isset($submission->how_heard) && !empty($submission->how_heard)): ?>
                            <p style="margin: 8px 0;"><strong>How heard about us:</strong> <?php echo esc_html($submission->how_heard); ?></p>
                        <?php endif; ?>
                        <?php if (isset($submission->flexible_dates) && $submission->flexible_dates): ?>
                            <p style="margin: 8px 0;"><strong>Flexible Dates:</strong> ✅ Yes</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Booking Preferences & Extras -->
            <?php if ((isset($submission->helicopter_package) && !empty($submission->helicopter_package)) || 
                      (isset($submission->transfer_options) && !empty($submission->transfer_options)) || 
                      (isset($submission->experiences) && !empty($submission->experiences)) || 
                      (isset($submission->occasion) && !empty($submission->occasion))): ?>
            <div style="margin-bottom: 25px;">
                <h4 style="margin: 0 0 15px 0; color: #2271b1; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Booking Preferences & Extras</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <?php if (isset($submission->helicopter_package) && !empty($submission->helicopter_package)): ?>
                            <p style="margin: 8px 0;"><strong>Helicopter Package:</strong> <?php echo esc_html($submission->helicopter_package); ?></p>
                        <?php endif; ?>
                        <?php if (isset($submission->transfer_options) && !empty($submission->transfer_options)): ?>
                            <p style="margin: 8px 0;"><strong>Transfer Options:</strong> <?php echo esc_html($submission->transfer_options); ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if (isset($submission->experiences) && !empty($submission->experiences)): ?>
                            <p style="margin: 8px 0;"><strong>Experiences:</strong> <?php echo esc_html($submission->experiences); ?></p>
                        <?php endif; ?>
                        <?php if (isset($submission->occasion) && !empty($submission->occasion)): ?>
                            <p style="margin: 8px 0;"><strong>Occasion:</strong> <?php echo esc_html($submission->occasion); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Special Requests -->
            <?php if (isset($submission->special_requests) && !empty($submission->special_requests)): ?>
                <div style="margin-bottom: 25px;">
                    <h4 style="margin: 0 0 15px 0; color: #2271b1; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Special Requests</h4>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #2271b1;">
                        <?php echo nl2br(esc_html($submission->special_requests)); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Children's Interests -->
            <?php if (isset($submission->children_interests) && !empty($submission->children_interests)): ?>
                <div style="margin-bottom: 25px;">
                    <h4 style="margin: 0 0 15px 0; color: #2271b1; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Children's Interests</h4>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #4CAF50;">
                        <?php echo nl2br(esc_html($submission->children_interests)); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Activities -->
            <?php if (isset($submission->activities) && !empty($submission->activities)): ?>
                <div style="margin-bottom: 25px;">
                    <h4 style="margin: 0 0 15px 0; color: #2271b1; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Selected Activities</h4>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                        <?php echo esc_html($submission->activities); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Raw Data -->
            <div style="margin-top: 30px;">
                <h4 style="margin: 0 0 15px 0; color: #646970; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Raw Submission Data</h4>
                <div style="background: #f1f1f1; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto;">
                    <?php 
                    $submission_array = (array) $submission;
                    foreach ($submission_array as $key => $value) {
                        if ($value !== null && $value !== '') {
                            echo '<strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '<br>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="margin-top: 25px; text-align: right; border-top: 1px solid #ddd; padding-top: 15px;">
                <a href="mailto:<?php echo esc_attr($submission->email); ?>?subject=Re: Your booking inquiry #<?php echo $submission->id; ?>" 
                   class="button button-primary" style="margin-right: 10px;">
                    📧 Send Email
                </a>
                <a href="<?php echo wp_nonce_url(add_query_arg(['action' => 'delete', 'submission_id' => $submission->id], admin_url('admin.php?page=leobo-booking-submissions')), 'delete_submission_' . $submission->id); ?>" 
                   class="button button-link-delete" 
                   onclick="return confirm('Are you sure you want to delete this submission? This action cannot be undone.');">
                    🗑️ Delete Submission
                </a>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        
        wp_send_json_success(['html' => $html]);
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
                    
                    <h3>🧪 Test Form Shortcode</h3>
                    <pre><code>[leobo_test_booking_form]</code></pre>
                    <p>Displays a test booking form with pre-filled data for quick submission testing. Uses the same functions as the main form but with test data pre-populated.</p>
                    
                    <h4>Test Form Features</h4>
                    <ul>
                        <li><strong>Pre-filled Data:</strong> Sample guest information, dates, and preferences</li>
                        <li><strong>Same Functions:</strong> Uses identical backend processing as live form</li>
                        <li><strong>Real Submissions:</strong> Creates actual booking entries in database</li>
                        <li><strong>Test Emails:</strong> Sends emails with [TEST] prefix in subject</li>
                        <li><strong>Random Data:</strong> Button to randomize test data for variety</li>
                        <li><strong>Visual Distinction:</strong> Clearly marked as test form to avoid confusion</li>
                    </ul>
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
                            
                            <!-- Summary Stats -->
                            <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; margin: 15px 0;">
                                <h4 style="margin-top: 0;">📊 API Response Summary</h4>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                    <div>
                                        <strong>Response Time:</strong> <?php echo $test_result['response_time_ms']; ?>ms<br>
                                        <strong>Data Received:</strong> <?php echo $test_result['data_received'] ? 'Yes' : 'No'; ?><br>
                                        <strong>Valid Data:</strong> <?php echo $test_result['has_availability_data'] ? 'Yes' : 'No'; ?><br>
                                        <strong>Tested at:</strong> <?php echo $test_result['timestamp']; ?>
                                    </div>
                                    
                                    <?php 
                                    // Calculate availability stats
                                    $total_dates = 0;
                                    $blocked_dates = 0;
                                    $available_dates = 0;
                                    $provisional_bookings = 0;
                                    $confirmed_bookings = 0;
                                    
                                    if (isset($test_result['response_data']['availability_data'])) {
                                        foreach ($test_result['response_data']['availability_data'] as $room_data) {
                                            foreach ($room_data as $key => $data) {
                                                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key) && is_array($data)) {
                                                    $total_dates++;
                                                    if (isset($data['availability'])) {
                                                        if ($data['availability'] == 0) {
                                                            $blocked_dates++;
                                                        } else {
                                                            $available_dates++;
                                                        }
                                                    }
                                                    if (isset($data['Prov'])) {
                                                        $provisional_bookings += (int)$data['Prov'];
                                                    }
                                                    if (isset($data['Conf'])) {
                                                        $confirmed_bookings += (int)$data['Conf'];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                    
                                    <div>
                                        <strong>Total Dates:</strong> <?php echo $total_dates; ?><br>
                                        <strong style="color: #dc3545;">Blocked Dates:</strong> <?php echo $blocked_dates; ?><br>
                                        <strong style="color: #28a745;">Available Dates:</strong> <?php echo $available_dates; ?><br>
                                        <strong>Room Type:</strong> 
                                        <?php 
                                        $room_type = 'Unknown';
                                        if (isset($test_result['response_data']['availability_data'][0]['Room Type'])) {
                                            $room_type = $test_result['response_data']['availability_data'][0]['Room Type'];
                                        }
                                        echo esc_html($room_type);
                                        ?>
                                    </div>
                                    
                                    <div>
                                        <strong>Total Provisional:</strong> <?php echo $provisional_bookings; ?><br>
                                        <strong>Total Confirmed:</strong> <?php echo $confirmed_bookings; ?><br>
                                        <strong>Coverage Period:</strong> 
                                        <?php 
                                        $date_range = 'N/A';
                                        if (isset($test_result['response_data']['availability_data'])) {
                                            $dates = array();
                                            foreach ($test_result['response_data']['availability_data'] as $room_data) {
                                                foreach ($room_data as $key => $data) {
                                                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)) {
                                                        $dates[] = $key;
                                                    }
                                                }
                                            }
                                            if (!empty($dates)) {
                                                sort($dates);
                                                $date_range = reset($dates) . ' to ' . end($dates);
                                            }
                                        }
                                        echo $date_range;
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detailed Date Breakdown -->
                            <?php if (isset($test_result['response_data']['availability_data']) && !empty($test_result['response_data']['availability_data'])): ?>
                                <div style="margin: 15px 0;">
                                    <h4>📅 Detailed Date Breakdown</h4>
                                    
                                    <?php foreach ($test_result['response_data']['availability_data'] as $room_index => $room_data): ?>
                                        <?php if (isset($room_data['Room Type'])): ?>
                                            <h5 style="margin: 10px 0 5px 0; color: #495057;">
                                                🏨 <?php echo esc_html($room_data['Room Type']); ?>
                                            </h5>
                                        <?php endif; ?>
                                        
                                        <div style="background: #fff; border: 1px solid #dee2e6; border-radius: 4px; margin: 10px 0;">
                                            <table style="width: 100%; border-collapse: collapse;">
                                                <thead style="background: #f8f9fa;">
                                                    <tr>
                                                        <th style="padding: 10px; border-bottom: 1px solid #dee2e6; text-align: left;">Date</th>
                                                        <th style="padding: 10px; border-bottom: 1px solid #dee2e6; text-align: center;">Status</th>
                                                        <th style="padding: 10px; border-bottom: 1px solid #dee2e6; text-align: center;">Availability</th>
                                                        <th style="padding: 10px; border-bottom: 1px solid #dee2e6; text-align: center;">Provisional</th>
                                                        <th style="padding: 10px; border-bottom: 1px solid #dee2e6; text-align: center;">Confirmed</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $date_entries = array();
                                                    foreach ($room_data as $key => $data) {
                                                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key) && is_array($data)) {
                                                            $date_entries[$key] = $data;
                                                        }
                                                    }
                                                    ksort($date_entries); // Sort dates chronologically
                                                    
                                                    foreach ($date_entries as $date => $data): 
                                                        $availability = isset($data['availability']) ? (int)$data['availability'] : 'N/A';
                                                        $provisional = isset($data['Prov']) ? (int)$data['Prov'] : 0;
                                                        $confirmed = isset($data['Conf']) ? (int)$data['Conf'] : 0;
                                                        
                                                        $status_color = '';
                                                        $status_text = '';
                                                        $status_icon = '';
                                                        
                                                        if ($availability === 0) {
                                                            $status_color = '#dc3545';
                                                            $status_text = 'BLOCKED';
                                                            $status_icon = '🔴';
                                                        } elseif ($availability === 1) {
                                                            $status_color = '#28a745';
                                                            $status_text = 'AVAILABLE';
                                                            $status_icon = '🟢';
                                                        } else {
                                                            $status_color = '#6c757d';
                                                            $status_text = 'UNKNOWN';
                                                            $status_icon = '⚫';
                                                        }
                                                        
                                                        $formatted_date = date('D, M j, Y', strtotime($date));
                                                    ?>
                                                        <tr style="border-bottom: 1px solid #f1f1f1;">
                                                            <td style="padding: 8px 10px; font-family: monospace;">
                                                                <strong><?php echo $date; ?></strong><br>
                                                                <small style="color: #6c757d;"><?php echo $formatted_date; ?></small>
                                                            </td>
                                                            <td style="padding: 8px 10px; text-align: center;">
                                                                <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                                                    <?php echo $status_icon; ?> <?php echo $status_text; ?>
                                                                </span>
                                                            </td>
                                                            <td style="padding: 8px 10px; text-align: center; font-family: monospace;">
                                                                <span style="background: <?php echo $availability === 0 ? '#fff5f5' : '#f0fff4'; ?>; 
                                                                           border: 1px solid <?php echo $availability === 0 ? '#fed7d7' : '#c6f6d5'; ?>; 
                                                                           border-radius: 3px; padding: 2px 6px;">
                                                                    <?php echo $availability; ?>
                                                                </span>
                                                            </td>
                                                            <td style="padding: 8px 10px; text-align: center; font-family: monospace;">
                                                                <?php if ($provisional > 0): ?>
                                                                    <span style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px; padding: 2px 6px;">
                                                                        <?php echo $provisional; ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span style="color: #6c757d;">0</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td style="padding: 8px 10px; text-align: center; font-family: monospace;">
                                                                <?php if ($confirmed > 0): ?>
                                                                    <span style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 3px; padding: 2px 6px;">
                                                                        <?php echo $confirmed; ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span style="color: #6c757d;">0</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Raw API Response (Collapsible) -->
                            <div style="margin: 15px 0;">
                                <h4>🔍 Raw API Response</h4>
                                <details style="margin: 10px 0;">
                                    <summary style="cursor: pointer; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
                                        Click to view raw API response data
                                    </summary>
                                    <div style="background: #f1f1f1; padding: 15px; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 4px 4px; max-height: 400px; overflow-y: auto;">
                                        <pre style="margin: 0; white-space: pre-wrap; font-size: 12px;"><?php echo esc_html(print_r($test_result['response_data'], true)); ?></pre>
                                    </div>
                                </details>
                            </div>
                        <?php endif; ?>
                        
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
     * API Testing page - Test blocked date ranges and availability responses
     */
    public function api_testing_page() {
        // Handle blocked dates test
        $test_result = null;
        $blocked_dates_set = false;
        
        if (isset($_POST['test_blocked_dates']) && wp_verify_nonce($_POST['blocked_test_nonce'], 'leobo_blocked_test')) {
            $start_date = sanitize_text_field($_POST['blocked_start_date']);
            $end_date = sanitize_text_field($_POST['blocked_end_date']);
            
            if (!empty($start_date) && !empty($end_date)) {
                // Create test API response with blocked dates
                $test_result = $this->simulate_api_blocked_dates($start_date, $end_date);
                $blocked_dates_set = true;
            }
        }
        
        if (isset($_POST['clear_test_data']) && wp_verify_nonce($_POST['clear_test_nonce'], 'leobo_clear_test')) {
            // Clear any test data
            $deleted = delete_transient('leobo_test_blocked_dates');
            $blocked_dates_set = false;
            
            // Log the clear action for debugging
            error_log('Clear test data button clicked. Transient deleted: ' . ($deleted ? 'Yes' : 'No'));
            
            // Force a page redirect to ensure fresh state
            wp_redirect(add_query_arg('test_cleared', '1', wp_get_referer()));
            exit;
        }
        
        ?>
        <div class="wrap">
            <h1>API Testing & Blocked Dates</h1>
            
            <!-- API Response Structure Info -->
            <div class="card" style="max-width: none; margin-bottom: 20px;">
                <h2>API Response Structure</h2>
                <p>The Pan Hospitality API returns availability data in the following format:</p>
                <div style="background: #f1f1f1; padding: 15px; border-radius: 4px; margin: 10px 0;">
                    <pre style="margin: 0; font-size: 12px;">Array
(
    [availability_data] => Array
        (
            [0] => Array
                (
                    [Room Type] => LPRO - THE OBSERVATORY - OBSERVATORY
                    [2025-08-17] => Array
                        (
                            [availability] => 0  // 0 = Not Available, 1 = Available
                            [Prov] => 1          // Provisional bookings
                            [Conf] => 0          // Confirmed bookings
                        )
                    [2025-08-18] => Array
                        (
                            [availability] => 0
                            [Prov] => 1
                            [Conf] => 0
                        )
                    // ... more dates
                )
        )
)</pre>
                </div>
                <p><strong>Key:</strong></p>
                <ul>
                    <li><code>availability: 0</code> = Date is blocked/unavailable</li>
                    <li><code>availability: 1</code> = Date is available for booking</li>
                    <li><code>Prov</code> = Number of provisional bookings</li>
                    <li><code>Conf</code> = Number of confirmed bookings</li>
                </ul>
            </div>
            
            <!-- Blocked Dates Testing -->
            <div class="card" style="max-width: none; margin-bottom: 20px;">
                <h2>Test Blocked Date Ranges</h2>
                <p>Use this section to simulate API responses with blocked dates for testing the booking form's behavior.</p>
                
                <form method="post" style="margin-bottom: 20px;">
                    <?php wp_nonce_field('leobo_blocked_test', 'blocked_test_nonce'); ?>
                    <?php wp_nonce_field('leobo_clear_test', 'clear_test_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Start Date</th>
                            <td>
                                <input type="date" name="blocked_start_date" value="<?php echo date('Y-m-d'); ?>" required />
                                <p class="description">Start date for the blocked range</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">End Date</th>
                            <td>
                                <input type="date" name="blocked_end_date" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required />
                                <p class="description">End date for the blocked range</p>
                            </td>
                        </tr>
                    </table>
                    <p>
                        <input type="submit" name="test_blocked_dates" class="button button-primary" value="Simulate Blocked Dates" />
                        <input type="submit" name="clear_test_data" class="button button-secondary" value="Clear Test Data" />
                    </p>
                </form>
                
                <?php if ($test_result): ?>
                    <div class="notice notice-success inline">
                        <h4>Test API Response Generated</h4>
                        <p><strong>Blocked Date Range:</strong> <?php echo $test_result['blocked_range']; ?></p>
                        <p><strong>Total Blocked Dates:</strong> <?php echo $test_result['blocked_count']; ?></p>
                        
                        <h4>Simulated API Response:</h4>
                        <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; max-height: 400px; overflow-y: auto;">
                            <pre style="margin: 0; font-size: 11px; white-space: pre-wrap;"><?php echo esc_html(print_r($test_result['api_response'], true)); ?></pre>
                        </div>
                        
                        <div style="margin-top: 15px; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px;">
                            <strong>Testing Instructions:</strong>
                            <ol style="margin: 5px 0;">
                                <li>This simulated data has been stored temporarily</li>
                                <li>Visit your booking form to test how it handles these blocked dates</li>
                                <li>Try selecting dates within the blocked range - they should be unavailable</li>
                                <li>Check that the calendar properly highlights blocked dates</li>
                                <li>Use "Clear Test Data" to remove the simulation</li>
                            </ol>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['test_cleared'])): ?>
                    <div class="notice notice-success inline">
                        <p>✅ Test data has been cleared successfully. The booking form will now use live API data.</p>
                    </div>
                <?php elseif (isset($_POST['clear_test_data'])): ?>
                    <div class="notice notice-info inline">
                        <p>Test data has been cleared. The booking form will now use live API data.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Current Test Status -->
            <div class="card" style="max-width: none; margin-bottom: 20px;">
                <h2>Current Test Status</h2>
                <?php
                $current_test_data = get_transient('leobo_test_blocked_dates');
                if ($current_test_data): ?>
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 15px;">
                        <h4 style="margin-top: 0; color: #856404;">⚠️ Test Mode Active</h4>
                        <p style="margin-bottom: 5px; color: #856404;"><strong>Blocked Range:</strong> <?php echo $current_test_data['blocked_range']; ?></p>
                        <p style="margin-bottom: 5px; color: #856404;"><strong>Blocked Dates Count:</strong> <?php echo $current_test_data['blocked_count']; ?></p>
                        <p style="margin-bottom: 0; color: #856404;"><strong>Created:</strong> <?php echo $current_test_data['created']; ?></p>
                    </div>
                <?php else: ?>
                    <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 15px;">
                        <h4 style="margin-top: 0; color: #155724;">✅ Live Mode</h4>
                        <p style="margin-bottom: 0; color: #155724;">No test data active. The booking form is using live API data.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- API Integration Testing -->
            <div class="card" style="max-width: none;">
                <h2>API Integration Testing</h2>
                <p>Use these tools to test different aspects of the API integration:</p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                    
                    <!-- Test Scenario 1 -->
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px;">
                        <h4 style="margin-top: 0;">Fully Booked Period</h4>
                        <p>Test a period where all dates are unavailable (availability: 0)</p>
                        <button type="button" class="button button-secondary" onclick="testScenario('fully_booked')">Test Scenario</button>
                    </div>
                    
                    <!-- Test Scenario 2 -->
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px;">
                        <h4 style="margin-top: 0;">Partially Available</h4>
                        <p>Test mixed availability with some dates blocked and some available</p>
                        <button type="button" class="button button-secondary" onclick="testScenario('partial')">Test Scenario</button>
                    </div>
                    
                    <!-- Test Scenario 3 -->
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px;">
                        <h4 style="margin-top: 0;">High Demand Dates</h4>
                        <p>Test dates with provisional bookings (Prov > 0)</p>
                        <button type="button" class="button button-secondary" onclick="testScenario('high_demand')">Test Scenario</button>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <script>
        function testScenario(scenario) {
            // This could be expanded to create different test scenarios
            alert('Test scenario "' + scenario + '" would be implemented here.\n\nThis would create specific API response patterns for testing different booking situations.');
        }
        </script>
        <?php
    }
    
    /**
     * Simulate API response with blocked dates
     */
    private function simulate_api_blocked_dates($start_date, $end_date) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end->add($interval));
        
        $api_response = array(
            'availability_data' => array(
                array(
                    'Room Type' => 'LPRO - THE OBSERVATORY - OBSERVATORY'
                )
            )
        );
        
        $blocked_count = 0;
        foreach ($period as $date) {
            $date_string = $date->format('Y-m-d');
            $api_response['availability_data'][0][$date_string] = array(
                'availability' => 0, // Blocked
                'Prov' => 1,
                'Conf' => 0
            );
            $blocked_count++;
        }
        
        // Store test data temporarily
        $test_data = array(
            'api_response' => $api_response,
            'blocked_range' => $start_date . ' to ' . $end_date,
            'blocked_count' => $blocked_count,
            'created' => current_time('Y-m-d H:i:s')
        );
        
        set_transient('leobo_test_blocked_dates', $test_data, HOUR_IN_SECONDS * 2); // Store for 2 hours
        
        return $test_data;
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
                <a href="#acf-export" class="nav-tab" onclick="showDebugTab('acf-export')">ACF Export</a>
                <a href="#pricing-test" class="nav-tab" onclick="showDebugTab('pricing-test')">Pricing Test</a>
                <a href="#season-check" class="nav-tab" onclick="showDebugTab('season-check')">Season Detection</a>
                <a href="#min-nights" class="nav-tab" onclick="showDebugTab('min-nights')">Minimum Nights</a>
            </div>
            
            <!-- ACF Data Tab -->
            <div id="acf-data-tab" class="debug-tab-content">
                <div class="card">
                    <h2>🔧 ACF Configuration Data</h2>
                    
                    <?php 
                    // Check if main pricing fields are configured
                    $standard_configured = !empty(get_field('standard_base_rate', 'option')) && !empty(get_field('standard_season_dates', 'option'));
                    $peak_configured = !empty(get_field('peak_base_rate', 'option')) && !empty(get_field('peak_season_dates', 'option'));
                    $christmas_configured = !empty(get_field('christmas_base_rate', 'option')) && !empty(get_field('christmas_dates', 'option'));
                    
                    $all_configured = $standard_configured && $peak_configured && $christmas_configured;
                    ?>
                    
                    <div style="padding: 15px; background: <?php echo $all_configured ? '#d4edda' : '#f8d7da'; ?>; border: 2px solid <?php echo $all_configured ? '#c3e6cb' : '#f1b0b7'; ?>; border-radius: 8px; margin-bottom: 20px;">
                        <?php if ($all_configured): ?>
                            <h3 style="color: #155724; margin: 0 0 10px 0;">✅ System Fully Configured</h3>
                            <p style="margin: 0; color: #155724;">All rate data is being read from ACF fields in Booking Config.</p>
                        <?php else: ?>
                            <h3 style="color: #721c24; margin: 0 0 10px 0;">⚠️ Configuration Required</h3>
                            <p style="margin: 0 0 10px 0; color: #721c24;"><strong>The system is currently using fallback values.</strong> Please configure the missing ACF fields:</p>
                            <ul style="margin: 0; color: #721c24;">
                                <?php if (!$standard_configured): ?>
                                    <li>Standard Season: rates and/or dates not configured</li>
                                <?php endif; ?>
                                <?php if (!$peak_configured): ?>
                                    <li>Peak Season: rates and/or dates not configured</li>
                                <?php endif; ?>
                                <?php if (!$christmas_configured): ?>
                                    <li>Christmas Season: rates and/or dates not configured</li>
                                <?php endif; ?>
                            </ul>
                            <p style="margin: 10px 0 0 0; color: #721c24;"><strong>→ Go to <a href="<?php echo admin_url('admin.php?page=booking-config'); ?>">Booking Config</a> to set up proper rates.</strong></p>
                        <?php endif; ?>
                    </div>
                    
                    <h3>Standard Season</h3>
                    <?php 
                    $standard_base = get_field('standard_base_rate', 'option');
                    $standard_dates = get_field('standard_season_dates', 'option');
                    $is_configured = !empty($standard_base) && !empty($standard_dates);
                    ?>
                    <div style="padding: 10px; background: <?php echo $is_configured ? '#d4edda' : '#f8d7da'; ?>; border: 1px solid <?php echo $is_configured ? '#c3e6cb' : '#f1b0b7'; ?>; border-radius: 4px; margin-bottom: 10px;">
                        <strong>Configuration Status:</strong> <?php echo $is_configured ? '✅ CONFIGURED (using ACF values)' : '⚠️ NOT CONFIGURED (using fallback values)'; ?>
                    </div>
                    <pre><?php echo esc_html(print_r(array(
                        'dates' => get_field('standard_season_dates', 'option'),
                        'base_rate' => get_field('standard_base_rate', 'option'),
                        'extra_adult' => get_field('standard_extra_adult', 'option'),
                        'extra_child' => get_field('standard_extra_child', 'option')
                    ), true)); ?></pre>
                    
                    <h3>Peak Season</h3>
                    <?php 
                    $peak_base = get_field('peak_base_rate', 'option');
                    $peak_dates = get_field('peak_season_dates', 'option');
                    $is_peak_configured = !empty($peak_base) && !empty($peak_dates);
                    ?>
                    <div style="padding: 10px; background: <?php echo $is_peak_configured ? '#d4edda' : '#f8d7da'; ?>; border: 1px solid <?php echo $is_peak_configured ? '#c3e6cb' : '#f1b0b7'; ?>; border-radius: 4px; margin-bottom: 10px;">
                        <strong>Configuration Status:</strong> <?php echo $is_peak_configured ? '✅ CONFIGURED (using ACF values)' : '⚠️ NOT CONFIGURED (using fallback values)'; ?>
                    </div>
                    <pre><?php echo esc_html(print_r(array(
                        'dates' => get_field('peak_season_dates', 'option'),
                        'base_rate' => get_field('peak_base_rate', 'option'),
                        'extra_adult' => get_field('peak_extra_adult', 'option'),
                        'extra_child' => get_field('peak_extra_child', 'option')
                    ), true)); ?></pre>
                    
                    <h3>Christmas Season</h3>
                    <?php 
                    $christmas_base = get_field('christmas_base_rate', 'option');
                    $christmas_dates = get_field('christmas_dates', 'option');
                    $is_christmas_configured = !empty($christmas_base) && !empty($christmas_dates);
                    ?>
                    <div style="padding: 10px; background: <?php echo $is_christmas_configured ? '#d4edda' : '#f8d7da'; ?>; border: 1px solid <?php echo $is_christmas_configured ? '#c3e6cb' : '#f1b0b7'; ?>; border-radius: 4px; margin-bottom: 10px;">
                        <strong>Configuration Status:</strong> <?php echo $is_christmas_configured ? '✅ CONFIGURED (using ACF values)' : '⚠️ NOT CONFIGURED (using fallback values)'; ?>
                    </div>
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
            
            <!-- ACF Export Tab -->
            <div id="acf-export-tab" class="debug-tab-content" style="display: none;">
                <div class="card">
                    <h2>📦 Complete ACF Data Export</h2>
                    <p>This is the complete export of all ACF fields for booking system analysis. Copy this data to share with developers for debugging purposes.</p>
                    
                    <div style="margin: 15px 0;">
                        <button type="button" onclick="copyACFExport()" class="button button-primary">📋 Copy to Clipboard</button>
                        <span id="copy-feedback" style="margin-left: 10px; color: green; display: none;">✅ Copied!</span>
                    </div>
                    
                    <textarea id="acf-export-data" readonly style="width: 100%; height: 500px; font-family: 'Courier New', monospace; font-size: 12px; background: #f8f9fa; border: 1px solid #ddd; padding: 15px;">
<?php
// Export all ACF booking configuration data
$acf_export = array(
    'export_timestamp' => current_time('Y-m-d H:i:s'),
    'wordpress_version' => get_bloginfo('version'),
    'acf_version' => defined('ACF_VERSION') ? ACF_VERSION : 'Not Available',
    'booking_system_version' => '3.9.0',
    
    // Season Configuration
    'seasons' => array(
        'standard' => array(
            'dates' => get_field('standard_season_dates', 'option'),
            'base_rate' => get_field('standard_base_rate', 'option'),
            'extra_adult' => get_field('standard_extra_adult', 'option'),
            'extra_child' => get_field('standard_extra_child', 'option')
        ),
        'peak' => array(
            'dates' => get_field('peak_season_dates', 'option'),
            'base_rate' => get_field('peak_base_rate', 'option'),
            'extra_adult' => get_field('peak_extra_adult', 'option'),
            'extra_child' => get_field('peak_extra_child', 'option')
        ),
        'christmas' => array(
            'dates' => get_field('christmas_dates', 'option'),
            'base_rate' => get_field('christmas_base_rate', 'option'),
            'extra_adult' => get_field('christmas_extra_adult', 'option'),
            'extra_child' => get_field('christmas_extra_child', 'option'),
            'surcharge' => get_field('christmas_surcharge', 'option')
        )
    ),
    
    // Guest Rules
    'guest_rules' => array(
        'max_adults' => get_field('max_adults', 'option'),
        'max_children' => get_field('max_children', 'option'),
        'max_babies' => get_field('max_babies', 'option'),
        'children_free_age' => get_field('children_free_age', 'option')
    ),
    
    // Minimum Nights Configuration
    'minimum_nights' => array(
        'default_min_nights' => get_field('default_min_nights', 'option'),
        'special_min_nights' => get_field('special_min_nights', 'option'),
        'half_terms' => get_field('half_terms', 'option')
    ),
    
    // Helicopter Packages
    'helicopter_packages' => get_field('helicopter_packages', 'option'),
    'heli_additional_hour' => get_field('heli_additional_hour', 'option'),
    
    // Blocked Dates
    'blocked_dates' => get_field('blocked_dates', 'option'),
    
    // Other Settings
    'other_settings' => array(
        'currency_symbol' => get_field('currency_symbol', 'option'),
        'booking_form_title' => get_field('booking_form_title', 'option'),
        'booking_form_subtitle' => get_field('booking_form_subtitle', 'option'),
        'terms_and_conditions' => get_field('terms_and_conditions', 'option')
    ),
    
    // System Status
    'system_status' => array(
        'acf_fields_configured' => array(
            'standard_season' => !empty(get_field('standard_base_rate', 'option')) && !empty(get_field('standard_season_dates', 'option')),
            'peak_season' => !empty(get_field('peak_base_rate', 'option')) && !empty(get_field('peak_season_dates', 'option')),
            'christmas_season' => !empty(get_field('christmas_base_rate', 'option')) && !empty(get_field('christmas_dates', 'option')),
        ),
        'using_fallback_rates' => empty(get_field('standard_base_rate', 'option')) || empty(get_field('peak_base_rate', 'option'))
    )
);

echo json_encode($acf_export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
                    </textarea>
                    
                    <div style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-left: 4px solid #2196f3;">
                        <strong>Usage Instructions:</strong>
                        <ul style="margin: 5px 0;">
                            <li>Use "Copy to Clipboard" button to copy all data</li>
                            <li>Paste into development tools or share with developers</li>
                            <li>Shows current configuration status and which fields need setup</li>
                            <li>Includes system version information for compatibility checks</li>
                        </ul>
                    </div>
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
            
            function copyACFExport() {
                var exportData = document.getElementById('acf-export-data');
                var feedback = document.getElementById('copy-feedback');
                
                // Select and copy the text
                exportData.select();
                exportData.setSelectionRange(0, 99999); // For mobile devices
                
                try {
                    document.execCommand('copy');
                    
                    // Show feedback
                    feedback.style.display = 'inline';
                    setTimeout(function() {
                        feedback.style.display = 'none';
                    }, 3000);
                    
                } catch (err) {
                    // Fallback for browsers that don't support execCommand
                    feedback.textContent = '❌ Copy failed - please select and copy manually';
                    feedback.style.color = 'red';
                    feedback.style.display = 'inline';
                    
                    setTimeout(function() {
                        feedback.style.display = 'none';
                        feedback.textContent = '✅ Copied!';
                        feedback.style.color = 'green';
                    }, 5000);
                }
            }
            </script>
        </div>
        <?php
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
    
    /**
     * Test Forms page - Live testing interface
     */
    public function test_forms_page() {
        ?>
        <div class="wrap">
            <h1>🧪 Test Forms & Submission Testing</h1>
            
            <!-- Test Form Instructions -->
            <div class="card" style="max-width: none; margin-bottom: 20px;">
                <h2>Quick Testing Guide</h2>
                <p>Use these tools to quickly test the booking system functionality without manually filling forms.</p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #d4b896;">
                        <h3>📝 Test Form Shortcode</h3>
                        <p>Add this shortcode to any page or post:</p>
                        <code style="background: white; padding: 8px; display: block; margin: 10px 0; border-radius: 4px;">[leobo_test_booking_form]</code>
                        <p><strong>Features:</strong> Pre-filled data, randomize button, same functions as live form</p>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #2196f3;">
                        <h3>⚡ Quick Test Links</h3>
                        <p><a href="<?php echo admin_url('admin.php?page=leobo-booking-test-forms&test=inline'); ?>" class="button button-primary">Test Form Below</a></p>
                        <p><a href="<?php echo add_query_arg('test_booking_form', '1', home_url()); ?>" class="button button-secondary" target="_blank">Open Test Page</a></p>
                        <p><strong>Note:</strong> Live form with real submission processing</p>
                    </div>
                </div>
                
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin-top: 20px;">
                    <h4 style="margin-top: 0; color: #856404;">⚠️ Test Form Notes</h4>
                    <ul style="margin: 0; color: #856404;">
                        <li><strong>Real Submissions:</strong> Test forms create actual database entries</li>
                        <li><strong>Test Emails:</strong> Emails sent with [TEST] prefix in subject line</li>
                        <li><strong>Same Functions:</strong> Uses identical processing as live booking form</li>
                        <li><strong>Visual Distinction:</strong> Test forms clearly marked with test styling</li>
                    </ul>
                </div>
            </div>
            
            <!-- Test Submissions Log -->
            <div class="card" style="max-width: none; margin-bottom: 20px;">
                <h2>Recent Test Submissions</h2>
                <?php $this->display_test_submissions(); ?>
            </div>
            
            <?php if (isset($_GET['test']) && $_GET['test'] === 'inline'): ?>
            <!-- Inline Test Form -->
            <div class="card" style="max-width: none;">
                <h2>🧪 Live Test Form</h2>
                <p style="color: #666; margin-bottom: 20px;">This is a live test form running in the admin area. All functionality is identical to the frontend version.</p>
                <div style="background: #1a1a1a; padding: 20px; border-radius: 8px;">
                    <?php 
                    // Render test form but mark it as embedded in admin to prevent duplicate controls
                    $test_atts = array('test_mode' => true, 'embedded_admin' => true);
                    echo $this->render_test_booking_form($test_atts); 
                    ?>
                </div>
            </div>
            <?php else: ?>
            <!-- Instructions when not showing inline form -->
            <div class="card" style="max-width: none;">
                <h2>💡 How to Use Test Forms</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <h3>1. Use Shortcode</h3>
                        <p>Add <code>[leobo_test_booking_form]</code> to any page</p>
                        <p><strong>Benefits:</strong> Full test controls, real-time testing</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <h3>2. Admin Testing</h3>
                        <p><a href="<?php echo admin_url('admin.php?page=leobo-booking-test-forms&test=inline'); ?>" class="button button-primary">Enable Inline Test Form</a></p>
                        <p><strong>Benefits:</strong> Test directly in admin panel</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <h3>3. Separate Test Page</h3>
                        <p><a href="<?php echo add_query_arg('test_booking_form', '1', home_url()); ?>" class="button button-secondary" target="_blank">Open Test Page</a></p>
                        <p><strong>Benefits:</strong> Frontend environment testing</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
        <style>
        .wrap .leobo-booking-form {
            margin: 0;
        }
        .wrap .test-form {
            border: 3px dashed #d4b896;
        }
        .wrap code {
            font-family: Monaco, Consolas, monospace;
            font-size: 13px;
        }
        </style>
        <?php
    }
}
