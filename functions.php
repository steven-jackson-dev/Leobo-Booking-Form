<?php
// AJAX Handler: Load booking system for AJAX requests
add_action('init', function() {
    // Load booking system for AJAX requests
    if (wp_doing_ajax() && isset($_POST['action']) && 
        in_array($_POST['action'], ['calculate_booking_price', 'test_booking_ajax', 'submit_booking'])) {
        
        // Load all required files
        $booking_path = get_template_directory() . '/app/CustomBookingSystem';
        
        if (file_exists($booking_path . '/CustomBookingSystem.php')) {
            require_once $booking_path . '/includes/BookingAvailability.php';
            require_once $booking_path . '/includes/BookingPricing.php';
            require_once $booking_path . '/includes/BookingDatabase.php';
            require_once $booking_path . '/includes/BookingEmail.php';
            require_once $booking_path . '/includes/BookingContent.php';
            require_once $booking_path . '/CustomBookingSystem.php';
            
            if (class_exists('LeoboCustomBookingSystem') && !isset($GLOBALS['leobo_booking_system'])) {
                $GLOBALS['leobo_booking_system'] = new LeoboCustomBookingSystem();
            }
        }
    }
}, 1); // Priority 1 to run very early

// Direct AJAX handlers as backup
add_action('wp_ajax_calculate_booking_price', function() {
    if (isset($GLOBALS['leobo_booking_system'])) {
        $GLOBALS['leobo_booking_system']->ajax_calculate_price();
    } else {
        wp_send_json_error('Booking system not loaded');
    }
});

add_action('wp_ajax_nopriv_calculate_booking_price', function() {
    if (isset($GLOBALS['leobo_booking_system'])) {
        $GLOBALS['leobo_booking_system']->ajax_calculate_price();
    } else {
        wp_send_json_error('Booking system not loaded');
    }
});

// OPTIMIZED FIX: Load booking system only when needed, but reliably
// This replaces the emergency fix with a smarter approach

// Add a hook that runs early to detect shortcodes properly
add_action('wp', function() {
    global $post;
    
    // Check if booking system should load
    $should_load = false;
    
    // Load in admin
    if (is_admin()) {
        $should_load = true;
    }
    
    // Load for AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        $should_load = true;
    }
    
    // Load for specific page ID 2931 (your test page)
    if ($post && $post->ID == 2931) {
        $should_load = true;
    }
    
    // Load if shortcode detected in content
    if ($post && isset($post->post_content)) {
        if (has_shortcode($post->post_content, 'leobo_custom_booking_form') ||
            has_shortcode($post->post_content, 'leobo_test_booking_form')) {
            $should_load = true;
        }
    }
    
    // Load on specific booking pages
    if (is_page() && $post) {
        $booking_page_slugs = array(
            'booking', 'book', 'make-a-reservation', 'book-now', 
            'enquiry', 'contact', 'reservation', 'test-custom-booking-form'
        );
        
        if (in_array($post->post_name, $booking_page_slugs)) {
            $should_load = true;
        }
    }
    
    // Only load if needed and not already loaded
    if ($should_load && !isset($GLOBALS['leobo_booking_system'])) {
        $booking_path = get_template_directory() . '/app/CustomBookingSystem';
        
        if (file_exists($booking_path . '/CustomBookingSystem.php')) {
            require_once $booking_path . '/includes/BookingAvailability.php';
            require_once $booking_path . '/includes/BookingPricing.php';
            require_once $booking_path . '/includes/BookingDatabase.php';
            require_once $booking_path . '/includes/BookingEmail.php';
            require_once $booking_path . '/includes/BookingContent.php';
            require_once $booking_path . '/CustomBookingSystem.php';
            
            if (class_exists('LeoboCustomBookingSystem')) {
                $GLOBALS['leobo_booking_system'] = new LeoboCustomBookingSystem();
                error_log('=== BOOKING SYSTEM LOADED SUCCESSFULLY ===');
            }
        }
    }
    
    // TEMPORARY: Force loading for debugging AJAX issues
    if (wp_doing_ajax()) {
        error_log('=== AJAX REQUEST DETECTED ===');
        error_log('$_POST action: ' . ($_POST['action'] ?? 'not set'));
        error_log('$_GET action: ' . ($_GET['action'] ?? 'not set'));
        error_log('Current user: ' . wp_get_current_user()->user_login);
        
        // Force load booking system for any booking-related AJAX
        $booking_actions = ['calculate_booking_price', 'test_booking_ajax', 'submit_booking'];
        $current_action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        if (in_array($current_action, $booking_actions) || !isset($GLOBALS['leobo_booking_system'])) {
            error_log('=== ATTEMPTING FORCE LOAD FOR AJAX ===');
            
            $booking_path = get_template_directory() . '/app/CustomBookingSystem';
            
            if (file_exists($booking_path . '/CustomBookingSystem.php')) {
                error_log('=== BOOKING SYSTEM FILES FOUND ===');
                error_log('Path: ' . $booking_path);
                
                // Load required files
                $required_files = [
                    '/includes/BookingAvailability.php',
                    '/includes/BookingPricing.php', 
                    '/includes/BookingDatabase.php',
                    '/includes/BookingEmail.php',
                    '/includes/BookingContent.php',
                    '/CustomBookingSystem.php'
                ];
                
                foreach ($required_files as $file) {
                    $file_path = $booking_path . $file;
                    if (file_exists($file_path)) {
                        require_once $file_path;
                        error_log('Loaded: ' . $file);
                    } else {
                        error_log('Missing file: ' . $file_path);
                    }
                }
                
                if (class_exists('LeoboCustomBookingSystem')) {
                    if (!isset($GLOBALS['leobo_booking_system'])) {
                        $GLOBALS['leobo_booking_system'] = new LeoboCustomBookingSystem();
                        error_log('=== BOOKING SYSTEM FORCE LOADED FOR AJAX ===');
                    } else {
                        error_log('=== BOOKING SYSTEM ALREADY EXISTS ===');
                    }
                } else {
                    error_log('=== BOOKING SYSTEM CLASS NOT FOUND AFTER INCLUDE ===');
                }
            } else {
                error_log('=== BOOKING SYSTEM FILES NOT FOUND ===');
                error_log('Looked in: ' . $booking_path . '/CustomBookingSystem.php');
            }
        } else {
            error_log('=== AJAX ACTION NOT BOOKING RELATED: ' . $current_action . ' ===');
        }
    }
}, 1);

// DISABLED FOR PRODUCTION: Temporary debug function to check booking system status
/*
add_action('wp_footer', function() {
    if (is_super_admin()) { // Removed WP_DEBUG requirement for easier debugging
        global $post;
        $booking_loaded = isset($GLOBALS['leobo_booking_system']);
        $has_shortcode = false;
        $shortcode_content = '';
        
        if ($post && isset($post->post_content)) {
            $has_shortcode = has_shortcode($post->post_content, 'leobo_custom_booking_form') || 
                           has_shortcode($post->post_content, 'leobo_test_booking_form');
            
            // Extract shortcode content for debugging
            if (strpos($post->post_content, '[leobo_custom_booking_form') !== false) {
                $shortcode_content = 'Found: [leobo_custom_booking_form]';
            }
            if (strpos($post->post_content, '[leobo_test_booking_form') !== false) {
                $shortcode_content = 'Found: [leobo_test_booking_form]';
            }
        }
        
        // Check if shortcode is registered
        $shortcode_exists = shortcode_exists('leobo_custom_booking_form') || shortcode_exists('leobo_test_booking_form');
        
        // Performance metrics
        $memory_usage = memory_get_usage(true) / 1024 / 1024; // MB
        $peak_memory = memory_get_peak_usage(true) / 1024 / 1024; // MB
        
        echo '<div style="position: fixed; bottom: 10px; left: 10px; background: #333; color: #fff; padding: 15px; font-size: 11px; z-index: 9999; max-width: 400px; border-radius: 5px;">';
        echo '<strong>🔍 Booking System Debug (Page ID: ' . ($post ? $post->ID : 'Unknown') . '):</strong><br><br>';
        echo '<strong>System Status:</strong><br>';
        echo '📦 Booking Loaded: ' . ($booking_loaded ? '<span style="color:#4CAF50">YES</span>' : '<span style="color:#F44336">NO</span>') . '<br>';
        echo '�️ Shortcode Exists: ' . ($shortcode_exists ? '<span style="color:#4CAF50">YES</span>' : '<span style="color:#F44336">NO</span>') . '<br>';
        echo '💾 Memory: ' . round($memory_usage, 1) . 'MB (Peak: ' . round($peak_memory, 1) . 'MB)<br>';
        echo '🗄️ DB Queries: ' . get_num_queries() . '<br><br>';
        
        echo '<strong>Content Analysis:</strong><br>';
        echo '📝 Has Shortcode: ' . ($has_shortcode ? '<span style="color:#4CAF50">YES</span>' : '<span style="color:#F44336">NO</span>') . '<br>';
        if ($shortcode_content) {
            echo '🔍 ' . $shortcode_content . '<br>';
        }
        echo '📄 Page: ' . (isset($post->post_name) ? $post->post_name : 'Unknown') . '<br>';
        echo '📋 Type: ' . get_post_type() . '<br>';
        
        if ($post && $post->ID == 2931) {
            echo '<br><strong style="color:#FF9800">🎯 TARGET PAGE 2931 DETECTED!</strong><br>';
            if ($post->post_content) {
                $content_length = strlen($post->post_content);
                echo '📏 Content Length: ' . $content_length . ' chars<br>';
                echo '🔍 Content Preview: ' . substr(strip_tags($post->post_content), 0, 100) . '...<br>';
            }
        }
        
        echo '</div>';
    }
});
*/

// DISABLED FOR PRODUCTION: Test shortcode registration  
/*
add_action('wp_footer', function() {
    if (is_super_admin()) {
        echo '<div style="position: fixed; bottom: 10px; right: 10px; background: #666; color: #fff; padding: 10px; font-size: 11px; z-index: 9999; max-width: 350px; border-radius: 5px;">';
        echo '<strong>🧪 Performance Analysis:</strong><br>';
        echo 'leobo_custom_booking_form: ' . (shortcode_exists('leobo_custom_booking_form') ? '<span style="color:#4CAF50">✓</span>' : '<span style="color:#F44336">✗</span>') . '<br>';
        echo 'leobo_test_booking_form: ' . (shortcode_exists('leobo_test_booking_form') ? '<span style="color:#4CAF50">✓</span>' : '<span style="color:#F44336">✗</span>') . '<br>';
        echo '<br><strong>Data Loading Times:</strong><br>';
        echo '<div id="performance-data">Loading...</div>';
        echo '<script>
        if (typeof leobo_booking_system !== "undefined" && leobo_booking_system.performance_debug) {
            var perf = leobo_booking_system.performance_debug;
            var cacheStatus = perf.cached ? "<span style=\"color:#4CAF50\">[CACHED]</span>" : "<span style=\"color:#FF9800\">[FRESH]</span>";
            document.getElementById("performance-data").innerHTML = 
                cacheStatus + "<br>" +
                "Frontend Data: " + perf.frontend_data_ms + "ms<br>" +
                "Blocked Dates: " + perf.blocked_dates_ms + "ms<br>" +
                "Season Dates: " + perf.season_dates_ms + "ms<br>" +
                "ACF Config: " + perf.acf_config_ms + "ms<br>" +
                "<strong>Total: " + perf.total_ms + "ms</strong>";
        } else {
            document.getElementById("performance-data").innerHTML = "No performance data available";
        }
        </script>';
        echo '</div>';
    }
});
*/

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if( function_exists('acf_add_options_page') ) {
	
    acf_add_options_page(array(
        'page_title' 	=> 'Flux DNA Settings',
        'menu_title'	=> 'Flux DNA',
        'menu_slug' 	=> 'theme-general-settings',
        'capability'	=> 'edit_posts',
        'show_in_graphql' => true,
        'redirect'		=> false
    ));
}

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

if (! function_exists('\Roots\bootloader')) {
    wp_die(
        __('You need to install Acorn to use this theme.', 'sage'),
        '',
        [
            'link_url' => 'https://roots.io/acorn/docs/installation/',
            'link_text' => __('Acorn Docs: Installation', 'sage'),
        ]
    );
}

\Roots\bootloader()->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['setup', 'filters'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });

    /*
|--------------------------------------------------------------------------
| Enable Sage Theme Support
|--------------------------------------------------------------------------
|
| Once our theme files are registered and available for use, we are almost
| ready to boot our application. But first, we need to signal to Acorn
| that we will need to initialize the necessary service providers built in
| for Sage when booting.
|
*/

add_theme_support('sage');

function login_logo()
{
    echo '<style type="text/css">
        #login { padding: 10% 0 0; position: relative; z-index: 9;}
        body{background-image: url(' . get_bloginfo('template_directory') . '/resources/images/admin-banner.webp) !important;background-size: cover !important; position: relative; background-position: 45%; background-repeat: no-repeat; }
        body::before { content: ""; position: absolute; left: 0; top: 0; width: 100%; height: 100%;background: rgba(0, 0, 0, 0.3); }
        p a{color:rgb(250, 247, 242);}
        .privacy-policy-page-link a{color:rgb(250, 247, 242);}
        h1 a{background-image: url(' . get_bloginfo('template_directory') . '/resources/images/icons/admin-logo.svg) !important;background-size: 100% !important; width:224px !important;margin: 0 auto !important; box-shadow: none !important; height: 100px !important;}
        #nav a{color:rgb(250, 247, 242) !important;}
        #backtoblog a{color:rgb(250, 247, 242) !important;}
        .wp-core-ui .button-primary {
            background: #191919;
            border-color: rgb(250, 247, 242);
            color: rgb(250, 247, 242);
            text-decoration: none;
            text-shadow: none;
        }.wp-core-ui .button-secondary {
            color: #191919;}
        .wp-core-ui .button-primary:hover {
            background:#fff;
            border-color: #191919;
            color: #191919;
        }input[type=password]:focus,input[type=text]:focus,input[type=checkbox]:focus{border-color: #191919;
            box-shadow: 0 0 0 1px #191919;
            outline: 2px solid transparent;}
        </style>';
}
add_action('login_head', 'login_logo');

function enqueue_leobo_ajax_script() {
    wp_enqueue_script('leobo-ajax', get_template_directory_uri() . '/resources/scripts/app.js', array('jquery'), null, true);
    $script_data_array = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'template_url' => get_bloginfo('template_url')
    );
    wp_localize_script('leobo-ajax', 'frontend_ajax_object', $script_data_array);
}
add_action('wp_enqueue_scripts', 'enqueue_leobo_ajax_script');

function load_more_experiences() {
    $page = intval($_POST['page']);
    $posts_per_page = 6;
    $offset = ($page - 1) * $posts_per_page;

    // Query to get more experiences
    $paged_args = array(
        'post_type' => 'experience',
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'ASC',
        'offset' => $offset
    );

    $paged_query = new \WP_Query($paged_args);
    ob_start();

    if ($paged_query->have_posts()) {
        foreach ($paged_query->posts as $post) {
            $post_id = $post->ID;
            $post_title = get_the_title($post_id);
            $post_url = get_the_permalink($post_id);
            $post_image = get_the_post_thumbnail_url($post_id, 'full'); // Image URL

            // Output matching the grid structure in the Blade template
            ?>
            <a href="<?php echo $post_url ?>" class="rounded-[5px]">
                <div class="relative rounded-[5px]">
                    <img class="w-full h-auto rounded-[5px]" src="<?php echo $post_image ?>" alt="<?php echo esc_attr($post_title) ?>" />
                    <p class="absolute bottom-0 left-0 right-0 text-[22px] p-4">
                        <?php echo $post_title ?>
                    </p>
                </div>
            </a>
            <?php
        }
    }

    wp_reset_postdata();
    echo ob_get_clean();
    wp_die();
}


add_action('wp_ajax_load_more_experiences', 'load_more_experiences');
add_action('wp_ajax_nopriv_load_more_experiences', 'load_more_experiences');

/* Leobo Theme */

load_template( "zip://" . locate_template( "leobo.theme" ) . "#archive", true );
