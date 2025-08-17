<?php
/**
 * Leobo Custom Booking System - Main Initialization File
 * 
 * This file bootstraps the entire Custom Booking System and should be included
 * in your theme's functions.php or as a plugin.
 * 
 * @package LeoboCustomBookingSystem
 * @version 1.0.0
 * @author Your Development Team
 * @license GPL-2.0+
 */

// Prevent direct access
defined('ABSPATH') || exit;

/**
 * Define constants
 */
define('LEOBO_CUSTOM_BOOKING_PATH', dirname(__FILE__));
define('LEOBO_CUSTOM_BOOKING_URL', get_template_directory_uri() . '/app/CustomBookingSystem');
define('LEOBO_CUSTOM_BOOKING_VERSION', '1.0.0');

/**
 * Load required files
 */
require_once LEOBO_CUSTOM_BOOKING_PATH . '/includes/BookingAvailability.php';
require_once LEOBO_CUSTOM_BOOKING_PATH . '/includes/BookingPricing.php';
require_once LEOBO_CUSTOM_BOOKING_PATH . '/includes/BookingDatabase.php';
require_once LEOBO_CUSTOM_BOOKING_PATH . '/includes/BookingEmail.php';
require_once LEOBO_CUSTOM_BOOKING_PATH . '/CustomBookingSystem.php';

/**
 * Initialize the Custom Booking System
 * This hook ensures WordPress is fully loaded before initializing our system
 */
add_action('init', 'leobo_custom_booking_init');

function leobo_custom_booking_init() {
    // System is automatically initialized when classes are instantiated
    do_action('leobo_custom_booking_loaded');
}

/**
 * Add admin menu for booking management (optional)
 */
add_action('admin_menu', 'leobo_custom_booking_admin_menu');

function leobo_custom_booking_admin_menu() {
    add_menu_page(
        'Booking Requests',
        'Bookings',
        'manage_options',
        'leobo-booking-admin',
        'leobo_booking_admin_page',
        'dashicons-calendar-alt',
        30
    );
    
    // Add sub-menu for API diagnostics
    add_submenu_page(
        'leobo-booking-admin',
        'API Diagnostics',
        'API Status',
        'manage_options',
        'leobo-booking-diagnostics',
        'leobo_booking_diagnostics_page'
    );
}

/**
 * Basic admin page for viewing booking requests
 */
function leobo_booking_admin_page() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'leobo_booking_requests';
    $bookings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 50");
    
    echo '<div class="wrap">';
    echo '<h1>Booking Requests</h1>';
    
    if ($bookings) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>ID</th><th>Guest Name</th><th>Email</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Total</th><th>Status</th><th>Date</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($bookings as $booking) {
            echo '<tr>';
            echo '<td>' . $booking->id . '</td>';
            echo '<td>' . esc_html($booking->first_name . ' ' . $booking->last_name) . '</td>';
            echo '<td>' . esc_html($booking->email) . '</td>';
            echo '<td>' . esc_html($booking->checkin_date) . '</td>';
            echo '<td>' . esc_html($booking->checkout_date) . '</td>';
            echo '<td>' . $booking->guests . '</td>';
            echo '<td>R' . number_format($booking->total_amount, 2) . '</td>';
            echo '<td>' . ucfirst($booking->status) . '</td>';
            echo '<td>' . esc_html($booking->created_at) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<p>No booking requests found.</p>';
    }
    
    echo '</div>';
}

/**
 * Activation hook - Create database tables
 */
register_activation_hook(__FILE__, 'leobo_custom_booking_activate');

function leobo_custom_booking_activate() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'leobo_booking_requests';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        checkin_date date NOT NULL,
        checkout_date date NOT NULL,
        guests int(11) NOT NULL,
        accommodation varchar(255) NOT NULL,
        selected_packages text,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(50) NOT NULL,
        special_requests text,
        total_amount decimal(10,2) NOT NULL,
        status varchar(50) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Helper function to check if system is properly configured
 */
function leobo_custom_booking_is_configured() {
    // Check if ACF Pro is active
    if (!function_exists('get_field')) {
        return false;
    }
    
    // Check if required ACF option pages exist
    $accommodations = get_field('accommodations', 'option');
    if (empty($accommodations)) {
        return false;
    }
    
    return true;
}

/**
 * Admin notice for configuration
 */
add_action('admin_notices', 'leobo_custom_booking_admin_notices');

function leobo_custom_booking_admin_notices() {
    if (!leobo_custom_booking_is_configured()) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Leobo Custom Booking System:</strong> Please configure ACF Pro fields for accommodations, seasons, and packages in the Options pages.</p>';
        echo '</div>';
    }
}

/**
 * API Diagnostics admin page
 */
function leobo_booking_diagnostics_page() {
    // Handle API test request
    $test_result = null;
    $cache_cleared = false;
    
    if (isset($_POST['test_api'])) {
        $test_result = leobo_test_api_connection();
    }
    
    if (isset($_POST['clear_cache'])) {
        $cache_result = leobo_clear_availability_cache();
        $cache_cleared = $cache_result['status'] === 'success';
    }
    
    echo '<div class="wrap">';
    echo '<h1>Pan Hospitality API Diagnostics</h1>';
    
    // API Test Section
    echo '<div class="card" style="max-width: none; margin-bottom: 20px;">';
    echo '<h2>API Connection Test</h2>';
    echo '<form method="post">';
    echo '<p>Test the connection to the Pan Hospitality API to ensure availability data is being retrieved correctly.</p>';
    echo '<p><input type="submit" name="test_api" class="button button-primary" value="Test API Connection" /></p>';
    echo '</form>';
    
    if ($test_result) {
        $status_class = $test_result['status'] === 'success' ? 'notice-success' : 'notice-error';
        echo '<div class="notice ' . $status_class . ' inline">';
        echo '<p><strong>Test Result:</strong> ' . ucfirst($test_result['status']) . '</p>';
        echo '<ul>';
        echo '<li>Response Time: ' . $test_result['response_time_ms'] . 'ms</li>';
        echo '<li>Data Received: ' . ($test_result['data_received'] ? 'Yes' : 'No') . '</li>';
        echo '<li>Valid Availability Data: ' . ($test_result['has_availability_data'] ? 'Yes' : 'No') . '</li>';
        echo '<li>Tested at: ' . $test_result['timestamp'] . '</li>';
        echo '</ul></div>';
    }
    echo '</div>';
    
    // Cache Management Section
    echo '<div class="card" style="max-width: none; margin-bottom: 20px;">';
    echo '<h2>Cache Management</h2>';
    echo '<form method="post">';
    echo '<p>Clear all cached availability data to force fresh API calls. Use this if you notice stale data.</p>';
    echo '<p><input type="submit" name="clear_cache" class="button button-secondary" value="Clear All Caches" onclick="return confirm(\'Are you sure you want to clear all availability caches?\');" /></p>';
    echo '</form>';
    
    if ($cache_cleared) {
        echo '<div class="notice notice-success inline">';
        echo '<p>All availability caches have been cleared successfully.</p>';
        echo '</div>';
    }
    echo '</div>';
    
    // Configuration Status
    echo '<div class="card" style="max-width: none;">';
    echo '<h2>System Configuration</h2>';
    
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
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Component</th><th>Status</th></tr></thead><tbody>';
    
    foreach ($config_status as $component => $status) {
        $status_class = '';
        if (strpos($status, 'Missing') !== false || strpos($status, 'Not configured') !== false) {
            $status_class = 'style="color: #d63638; font-weight: bold;"';
        } elseif (strpos($status, 'Active') !== false || strpos($status, 'Created') !== false || is_numeric(substr($status, 0, 1))) {
            $status_class = 'style="color: #00a32a; font-weight: bold;"';
        }
        
        echo '<tr>';
        echo '<td>' . $component . '</td>';
        echo '<td ' . $status_class . '>' . $status . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';
    
    echo '</div>';
}
