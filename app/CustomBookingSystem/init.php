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
