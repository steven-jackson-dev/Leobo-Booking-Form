<?php
// Initialize the new unified Custom Booking System
require_once get_template_directory() . '/app/CustomBookingSystem/init.php';

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
