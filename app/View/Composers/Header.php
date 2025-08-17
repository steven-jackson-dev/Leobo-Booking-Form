<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Header extends Composer
{

    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.header',
        'sections.footer',
        'index',
        '404',
        '*'
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'header_enquiry_button' => get_field('header_enquiry_button', 'option'),
            'header_videos' => get_field('header_videos', 'option'),
            'header_phone_number' => get_field('header_phone_number', 'option'),
            'header_email' => get_field('header_email', 'option'),
            'header_socials' => get_field('header_socials', 'option'),
            'footer_logo' => get_field('footer_logo', 'option'),
            'footer_privacy_policy_link' => get_field('footer_privacy_policy_link', 'option'),
            'footer_terms_link' => get_field('footer_terms_link', 'option'),
            'footer_copyright_text' => get_field('footer_copyright_text', 'option'),
            'footer_testimonial_text' => get_field('footer_testimonial_text', 'option'),
            'footer_testimonial_logo' => get_field('footer_testimonial_logo', 'option'),
            'not_found_background_image' => get_field('not_found_background_image', 'option'),
            'not_found_mobile_background_image' => get_field('not_found_mobile_background_image', 'option'),
            'not_found_heading' => get_field('not_found_heading', 'option'),
            'not_found_subheading' => get_field('not_found_subheading', 'option'),
            'not_found_cta' => get_field('not_found_cta', 'option'),
            'thankyou_background_image' => get_field('thankyou_background_image', 'option'),
            'thankyou_mobile_background_image' => get_field('thankyou_mobile_background_image', 'option'),
            'thankyou_heading' => get_field('thankyou_heading', 'option'),
            'thankyou_subheading' => get_field('thankyou_subheading', 'option'),
            'thankyou_cta' => get_field('thankyou_cta', 'option'),
            'guest_enquiry_bg' => get_field('guest_enquiry_bg', 'option'),

        ];
    }
}
