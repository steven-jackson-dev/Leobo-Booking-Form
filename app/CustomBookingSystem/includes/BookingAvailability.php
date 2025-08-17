<?php
/**
 * Leobo Booking System - Advanced Availability Management
 * Handles Pan Hospitality API integration with caching and optimization
 * 
 * @package LeoboCustomBookingSystem
 * @version 1.0.0
 */

class LeoboBookingAvailability {
    
    private $api_key = 'Leob25secureKEY25';
    private $api_url = 'https://panweb.panhospitality.co.za/Controllers/availfetch.php';
    private $cache_duration = 1800; // 30 minutes
    private $fallback_cache_duration = 86400; // 24 hours for fallback data
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_ajax_refresh_availability_cache', array($this, 'refresh_availability_cache'));
        add_action('wp_ajax_nopriv_refresh_availability_cache', array($this, 'refresh_availability_cache'));
        
        // Schedule cache refresh
        if (!wp_next_scheduled('leobo_refresh_availability_cache')) {
            wp_schedule_event(time(), 'hourly', 'leobo_refresh_availability_cache');
        }
        add_action('leobo_refresh_availability_cache', array($this, 'scheduled_cache_refresh'));
    }
    
    public function init() {
        // Initialize any required settings
    }
    
    /**
     * Get availability data with enhanced caching and fallback
     */
    public function get_availability($start_date, $end_date, $force_refresh = false) {
        // Check for test data first
        $test_data = get_transient('leobo_test_blocked_dates');
        if ($test_data && isset($test_data['api_response'])) {
            return $test_data['api_response'];
        }
        
        $cache_key = 'leobo_availability_' . md5($start_date . $end_date);
        $fallback_key = 'leobo_availability_fallback_' . md5($start_date . $end_date);
        
        if (!$force_refresh) {
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        $data = $this->fetch_availability_from_api($start_date, $end_date);
        
        if ($data && isset($data['availability_data'])) {
            // Store fresh data with short cache
            set_transient($cache_key, $data, $this->cache_duration);
            // Store as fallback with long cache
            set_transient($fallback_key, $data, $this->fallback_cache_duration);
            return $data;
        } else {
            // API failed, try to use fallback data
            $fallback_data = get_transient($fallback_key);
            if ($fallback_data !== false) {
                error_log('Leobo Booking: Using fallback availability data due to API failure');
                return $fallback_data;
            }
        }
        
        return false;
    }
    
    /**
     * Fetch availability from Pan Hospitality API with enhanced error handling
     */
    private function fetch_availability_from_api($start_date, $end_date) {
        // Validate date format
        if (!$this->validate_date_format($start_date) || !$this->validate_date_format($end_date)) {
            error_log('Leobo Booking API Error: Invalid date format provided');
            return false;
        }
        
        $params = array(
            'apiKey' => $this->api_key,
            'startDate' => $start_date,
            'endDate' => $end_date
        );
        
        $query_string = http_build_query($params);
        $endpoint = $this->api_url . '?' . $query_string;
        
        $response = wp_remote_get($endpoint, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'Leobo WordPress Site v1.0'
            ),
            'sslverify' => true
        ));
        
        if (is_wp_error($response)) {
            error_log('Leobo Booking API Error: ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('Leobo Booking API HTTP Error: ' . $response_code . ' - ' . wp_remote_retrieve_response_message($response));
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        if (empty($body)) {
            error_log('Leobo Booking API Error: Empty response body');
            return false;
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Leobo Booking API JSON Error: ' . json_last_error_msg() . ' - Response: ' . substr($body, 0, 500));
            return false;
        }
        
        // Validate API response structure
        if (!isset($data['availability_data']) || !is_array($data['availability_data'])) {
            error_log('Leobo Booking API Error: Invalid response structure - missing availability_data');
            return false;
        }
        
        return $data;
    }
    
    /**
     * Validate date format for API calls
     */
    private function validate_date_format($date) {
        return (bool) preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date);
    }
    
    /**
     * Get blocked dates in various formats
     * Note: Last day of consecutive blocked periods is made available for check-in
     * since guests check in at night and previous guests leave in the morning
     */
    public function get_blocked_dates($months_ahead = 12, $format = 'Y-m-d') {
        // API returns all blocked dates in one call, no need for date range
        $start_date = date('Y/m/d');
        $end_date = date('Y/m/d', strtotime('+7 days')); // Just use a short range as API returns everything
        
        $availability_data = $this->get_availability($start_date, $end_date);
        
        if (!$availability_data || !isset($availability_data['availability_data'])) {
            return array();
        }
        
        $raw_blocked_dates = array();
        
        foreach ($availability_data['availability_data'] as $room_data) {
            foreach ($room_data as $date => $availability) {
                // Skip non-date keys
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    continue;
                }
                
                // Collect dates where availability is 0
                if (isset($availability['availability']) && $availability['availability'] == 0) {
                    $raw_blocked_dates[] = $date;
                }
            }
        }
        
        // Sort the dates and remove duplicates
        $raw_blocked_dates = array_unique($raw_blocked_dates);
        sort($raw_blocked_dates);
        
        // Process to allow check-in on last day of consecutive blocked periods
        $final_blocked_dates = $this->process_blocked_periods($raw_blocked_dates, $format);
        
        return $final_blocked_dates;
    }
    
    /**
     * Process blocked dates to allow check-in on the last day of consecutive periods
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
     * Check if a specific date is available
     */
    public function is_date_available($date) {
        $check_date = date('Y-m-d', strtotime($date));
        $start_date = date('Y/m/d', strtotime('-1 day', strtotime($check_date)));
        $end_date = date('Y/m/d', strtotime('+1 day', strtotime($check_date)));
        
        $availability_data = $this->get_availability($start_date, $end_date);
        
        if (!$availability_data || !isset($availability_data['availability_data'])) {
            return true; // Default to available if API fails
        }
        
        foreach ($availability_data['availability_data'] as $room_data) {
            if (isset($room_data[$check_date])) {
                return $room_data[$check_date]['availability'] == 1;
            }
        }
        
        return true; // Default to available if date not found
    }
    
    /**
     * Get availability for date range with room types
     */
    public function get_detailed_availability($start_date, $end_date) {
        $availability_data = $this->get_availability($start_date, $end_date);
        
        if (!$availability_data || !isset($availability_data['availability_data'])) {
            return array();
        }
        
        $detailed_availability = array();
        
        foreach ($availability_data['availability_data'] as $room_data) {
            $room_type = isset($room_data['Room Type']) ? $room_data['Room Type'] : 'Unknown';
            
            foreach ($room_data as $date => $availability) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    continue;
                }
                
                if (!isset($detailed_availability[$date])) {
                    $detailed_availability[$date] = array();
                }
                
                $detailed_availability[$date][$room_type] = $availability;
            }
        }
        
        return $detailed_availability;
    }
    
    /**
     * AJAX handler for refreshing cache
     */
    public function refresh_availability_cache() {
        check_ajax_referer('leobo_booking_nonce', 'nonce');
        
        // API returns all blocked dates in one call, no need for month calculation
        $start_date = date('Y/m/d');
        $end_date = date('Y/m/d', strtotime('+7 days')); // Short range as API returns everything
        
        $data = $this->get_availability($start_date, $end_date, true); // Force refresh
        
        if ($data) {
            wp_send_json_success(array(
                'message' => 'Availability cache refreshed successfully',
                'blocked_dates' => $this->get_blocked_dates(1) // API returns all dates regardless of range
            ));
        } else {
            wp_send_json_error('Failed to refresh availability cache');
        }
    }
    
    /**
     * Scheduled cache refresh
     */
    public function scheduled_cache_refresh() {
        // API returns all blocked dates in one call, no need for 12-month range
        $start_date = date('Y/m/d');
        $end_date = date('Y/m/d', strtotime('+7 days'));
        
        $this->get_availability($start_date, $end_date, true);
        
        error_log('Leobo: Availability cache refreshed via cron');
    }
    
    /**
     * Get room types from availability data
     */
    public function get_available_room_types($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $start_date = date('Y/m/d', strtotime($date));
        $end_date = date('Y/m/d', strtotime('+1 day', strtotime($date)));
        
        $availability_data = $this->get_availability($start_date, $end_date);
        
        if (!$availability_data || !isset($availability_data['availability_data'])) {
            return array();
        }
        
        $room_types = array();
        
        foreach ($availability_data['availability_data'] as $room_data) {
            if (isset($room_data['Room Type'])) {
                $room_type = $room_data['Room Type'];
                $is_available = false;
                
                if (isset($room_data[$date]) && $room_data[$date]['availability'] == 1) {
                    $is_available = true;
                }
                
                $room_types[] = array(
                    'name' => $room_type,
                    'available' => $is_available
                );
            }
        }
        
        return $room_types;
    }
    
    /**
     * Test API connectivity and return status
     */
    public function test_api_connection() {
        $test_start = date('Y/m/d');
        $test_end = date('Y/m/d', strtotime('+7 days'));
        
        $start_time = microtime(true);
        $data = $this->fetch_availability_from_api($test_start, $test_end);
        $end_time = microtime(true);
        $response_time = round(($end_time - $start_time) * 1000, 2);
        
        $result = array(
            'status' => $data !== false ? 'success' : 'failed',
            'response_time_ms' => $response_time,
            'data_received' => $data !== false,
            'has_availability_data' => $data && isset($data['availability_data']),
            'timestamp' => current_time('mysql'),
            'response_data' => $data // Include the actual response data
        );
        
        // If the API call failed, include error information
        if ($data === false) {
            $result['error_message'] = 'Failed to connect to Pan Hospitality API';
        }
        
        return $result;
    }
    
    /**
     * Clear all availability caches
     */
    public function clear_all_caches() {
        global $wpdb;
        
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%leobo_availability_%'");
        
        return array(
            'status' => 'success',
            'message' => 'All availability caches cleared',
            'timestamp' => current_time('mysql')
        );
    }
}

// Initialize the booking availability system
$leobo_booking_availability = new LeoboBookingAvailability();

/**
 * Helper functions for use in templates and forms
 */

function leobo_get_blocked_dates($months_ahead = 12, $format = 'Y-m-d') {
    global $leobo_booking_availability;
    return $leobo_booking_availability->get_blocked_dates($months_ahead, $format);
}

function leobo_is_date_available($date) {
    global $leobo_booking_availability;
    return $leobo_booking_availability->is_date_available($date);
}

function leobo_get_detailed_availability($start_date, $end_date) {
    global $leobo_booking_availability;
    return $leobo_booking_availability->get_detailed_availability($start_date, $end_date);
}

function leobo_get_available_room_types($date = null) {
    global $leobo_booking_availability;
    return $leobo_booking_availability->get_available_room_types($date);
}

function leobo_test_api_connection() {
    global $leobo_booking_availability;
    return $leobo_booking_availability->test_api_connection();
}

function leobo_clear_availability_cache() {
    global $leobo_booking_availability;
    return $leobo_booking_availability->clear_all_caches();
}
