<?php
/**
 * Test page for Custom Booking System
 * 
 * Access this file by going to: /wp-content/themes/leobo/test-booking.php
 */

// Load WordPress
require_once('../../../../../wp-load.php');

if (!current_user_can('administrator')) {
    wp_die('Access denied. You must be an administrator to view this page.');
}

get_header();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Custom Booking System Test</h1>
    
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Testing Environment</h2>
        
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">System Status</h3>
            <ul class="list-disc pl-6 space-y-1">
                <li>WordPress Version: <?php echo get_bloginfo('version'); ?></li>
                <li>Active Theme: <?php echo wp_get_theme()->get('Name'); ?></li>
                <li>ACF Pro Active: <?php echo function_exists('get_field') ? 'Yes' : 'No'; ?></li>
                <li>jQuery Loaded: <span id="jquery-status">Checking...</span></li>
                <li>Custom Booking System Loaded: <span id="booking-system-status">Checking...</span></li>
            </ul>
        </div>
        
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">ACF Data Test</h3>
            <div id="acf-test-results">
                <?php
                // Test ACF data
                $accommodations = get_posts(array(
                    'post_type' => 'accommodations',
                    'posts_per_page' => 3,
                    'post_status' => 'publish'
                ));
                
                echo '<h4>Sample Accommodations:</h4>';
                if ($accommodations) {
                    echo '<ul class="list-disc pl-6">';
                    foreach ($accommodations as $accommodation) {
                        echo '<li>' . $accommodation->post_title . ' (ID: ' . $accommodation->ID . ')</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p class="text-red-600">No accommodations found. Please create some accommodation posts.</p>';
                }
                
                $packages = get_posts(array(
                    'post_type' => 'packages',
                    'posts_per_page' => 3,
                    'post_status' => 'publish'
                ));
                
                echo '<h4 class="mt-4">Sample Packages:</h4>';
                if ($packages) {
                    echo '<ul class="list-disc pl-6">';
                    foreach ($packages as $package) {
                        echo '<li>' . $package->post_title . ' (ID: ' . $package->ID . ')</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p class="text-red-600">No packages found. Please create some package posts.</p>';
                }
                ?>
            </div>
        </div>
        
        <div class="mb-8">
            <h3 class="text-lg font-medium mb-4">Booking Form Test</h3>
            <div class="bg-gray-50 p-4 rounded">
                <?php echo do_shortcode('[leobo_custom_booking_form]'); ?>
            </div>
        </div>
        
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">Browser Console</h3>
            <p class="text-sm text-gray-600">Open your browser's developer tools (F12) and check the Console tab for any JavaScript errors or debug messages.</p>
        </div>
        
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">AJAX Test</h3>
            <button id="test-ajax" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Test AJAX Connection
            </button>
            <div id="ajax-results" class="mt-2"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check jQuery
    const jqueryStatus = document.getElementById('jquery-status');
    if (typeof jQuery !== 'undefined') {
        jqueryStatus.textContent = 'Yes (Version: ' + jQuery.fn.jquery + ')';
        jqueryStatus.className = 'text-green-600';
    } else {
        jqueryStatus.textContent = 'No';
        jqueryStatus.className = 'text-red-600';
    }
    
    // Check Custom Booking System
    const bookingStatus = document.getElementById('booking-system-status');
    if (typeof LeoboCustomBookingSystem !== 'undefined') {
        bookingStatus.textContent = 'Yes';
        bookingStatus.className = 'text-green-600';
    } else {
        bookingStatus.textContent = 'No';
        bookingStatus.className = 'text-red-600';
    }
    
    // AJAX test
    document.getElementById('test-ajax').addEventListener('click', function() {
        const resultsDiv = document.getElementById('ajax-results');
        resultsDiv.innerHTML = 'Testing...';
        
        if (typeof leobo_booking_system !== 'undefined') {
            fetch(leobo_booking_system.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'calculate_booking_price',
                    nonce: leobo_booking_system.nonce,
                    checkin_date: '2024-06-01',
                    checkout_date: '2024-06-03',
                    guests: '2',
                    accommodation_id: '1',
                    packages: '[]'
                })
            })
            .then(response => response.json())
            .then(data => {
                resultsDiv.innerHTML = '<pre class="bg-gray-100 p-2 rounded text-sm overflow-auto">' + 
                    JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                resultsDiv.innerHTML = '<p class="text-red-600">Error: ' + error.message + '</p>';
            });
        } else {
            resultsDiv.innerHTML = '<p class="text-red-600">leobo_booking_system object not found</p>';
        }
    });
});
</script>

<?php get_footer(); ?>
