<?php
/**
 * Leobo Custom Booking System - Optimized Initialization File
 * 
 * This file bootstraps the entire Custom Booking System with performance optimization.
 * Only loads on pages that actually need booking functionality.
 * 
 * @package LeoboCustomBookingSystem
 * @version 2.1.0 - Performance Optimized
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
define('LEOBO_CUSTOM_BOOKING_VERSION', '2.1.0');

/**
 * Determine if booking system should be loaded on current request
 * This prevents unnecessary loading on non-booking pages for better performance
 */
function leobo_should_load_booking_system() {
    // Always load in admin for management
    if (is_admin()) {
        return true;
    }
    
    // Load for AJAX requests (needed for form submissions)
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return true;
    }
    
    // Load for REST API requests
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return true;
    }
    
    // Load on pages with booking shortcodes
    global $post;
    if ($post && is_object($post) && isset($post->post_content)) {
        if (has_shortcode($post->post_content, 'leobo_custom_booking_form') ||
            has_shortcode($post->post_content, 'leobo_test_booking_form')) {
            return true;
        }
    }
    
    // Load on specific booking-related pages (customize these based on your site)
    if (is_page() && $post) {
        $booking_page_slugs = array(
            'booking',
            'make-a-reservation', 
            'book-now',
            'enquiry',
            'contact'
        );
        
        if (in_array($post->post_name, $booking_page_slugs)) {
            return true;
        }
    }
    
    // Check if current page template uses booking functionality
    if (is_page()) {
        $template = get_page_template_slug();
        if (strpos($template, 'booking') !== false) {
            return true;
        }
    }
    
    // Don't load on other pages for performance
    return false;
}

/**
 * Load required files only when needed
 */
function leobo_load_booking_system_files() {
    require_once LEOBO_CUSTOM_BOOKING_PATH . '/includes/BookingAvailability.php';
    require_once LEOBO_CUSTOM_BOOKING_PATH . '/includes/BookingPricing.php';
    require_once LEOBO_CUSTOM_BOOKING_PATH . '/includes/BookingDatabase.php';
    require_once LEOBO_CUSTOM_BOOKING_PATH . '/includes/BookingEmail.php';
    require_once LEOBO_CUSTOM_BOOKING_PATH . '/CustomBookingSystem.php';
}

/**
 * Initialize the Custom Booking System with conditional loading
 * This hook ensures WordPress is fully loaded and only loads when needed
 */
add_action('init', 'leobo_custom_booking_init', 10);

function leobo_custom_booking_init() {
    // Performance optimization: Only load booking system when needed
    if (!leobo_should_load_booking_system()) {
        return; // Exit early to save resources
    }
    
    // Prevent multiple instantiation
    if (isset($GLOBALS['leobo_booking_system'])) {
        return;
    }
    
    // Load files only when needed
    leobo_load_booking_system_files();
    
    // Check if class exists after loading
    if (!class_exists('LeoboCustomBookingSystem')) {
        error_log('Leobo Booking System: Failed to load LeoboCustomBookingSystem class');
        return;
    }
    
    // Initialize the system once
    $GLOBALS['leobo_booking_system'] = new LeoboCustomBookingSystem();
    
    do_action('leobo_custom_booking_loaded');
}

/**
 * Activation hook - Create database tables (optimized)
 */
register_activation_hook(__FILE__, 'leobo_custom_booking_activate');

function leobo_custom_booking_activate() {
    // Load database class for activation
    require_once LEOBO_CUSTOM_BOOKING_PATH . '/includes/BookingDatabase.php';
    
    // Create/update database table
    $database = new LeoboBookingDatabase();
    $database->create_table();
    
    // Set schema version for future optimizations
    update_option('leobo_booking_schema_version', '2.1.0');
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
 * Get the singleton instance of the booking system
 */
function leobo_get_booking_system() {
    return isset($GLOBALS['leobo_booking_system']) ? $GLOBALS['leobo_booking_system'] : null;
}

/**
 * Admin notice for configuration (only show when relevant)
 */
add_action('admin_notices', 'leobo_custom_booking_admin_notices');

function leobo_custom_booking_admin_notices() {
    // Only show configuration notices on booking-related admin pages
    $current_screen = get_current_screen();
    if ($current_screen && (
        strpos($current_screen->id, 'booking') !== false ||
        strpos($current_screen->id, 'leobo') !== false ||
        $current_screen->id === 'dashboard'
    )) {
        if (!leobo_custom_booking_is_configured()) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>Leobo Custom Booking System:</strong> Please configure ACF Pro fields for accommodations, seasons, and packages in the Options pages.</p>';
            echo '</div>';
        }
    }
}

/**
 * Debug function to show loading status (remove in production)
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('wp_footer', function() {
        if (is_super_admin()) {
            $loaded = isset($GLOBALS['leobo_booking_system']);
            $should_load = leobo_should_load_booking_system();
            
            echo '<!-- Leobo Booking System Status: ';
            echo 'Should Load: ' . ($should_load ? 'YES' : 'NO') . ', ';
            echo 'Actually Loaded: ' . ($loaded ? 'YES' : 'NO') . ' -->';
        }
    });
}
