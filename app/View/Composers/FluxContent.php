<?php
namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class FluxContent extends Composer
{

    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'partials.content-flux',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'fluxContetData' => $this->fluxContetData(),
        ];
    }

    public function fluxContetData()
    {
        $data = [];
        $flexible_content = get_field('page_content');
        if($flexible_content){
            foreach($flexible_content as $content) {
                if($content['acf_fc_layout']=='hero'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'hero_type'=> $content['hero_type'],
                        'pre_heading'=> $content['pre_heading'],
                        'heading'=> $content['heading'],
                        'sub_heading'=> $content['sub_heading'],
                        'background_video_url'=> $content['background_video_url'],
                        'full_video_url'=> $content['full_video_url'],
                        'background_image'=> $content['background_image'],
                        'mobile_background_image'=> $content['mobile_background_image'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                }
                elseif($content['acf_fc_layout']=='intro_content'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'heading'=> $content['heading'],
                        'description'=> $content['description'],
                        'cta'=> $content['cta'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                } elseif($content['acf_fc_layout']=='main_intro'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'scroll_headings'=> $content['scroll_headings'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                } elseif($content['acf_fc_layout']=='general_content'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'general_content'=> $content['general_content'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                } elseif($content['acf_fc_layout']=='awards_slider'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'heading'=> $content['heading'],
                        'slider'=> $content['slider'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                } elseif($content['acf_fc_layout']=='highlights_slider'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'heading'=> $content['heading'],
                        'description'=> $content['description'],
                        'slider'=> $content['slider'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                } elseif($content['acf_fc_layout']=='experiences_slider'){
                    $experience_listing_arr = array();
                    $experience_listing_arg = array(
                        'post_type' => 'experience',
                        'posts_per_page' => '-1',
                        'post_status' => 'publish',
                        'orderby' => 'date',
                        'order' => 'ASC',
                    );
                    $experience_listing_query = new \WP_Query($experience_listing_arg);
                    if ($experience_listing_query->have_posts()) {
                        while ($experience_listing_query->have_posts()) : $experience_listing_query->the_post();
                            $fea_img = '';
                            if (get_the_post_thumbnail_url()) {
                                $fea_img = get_the_post_thumbnail_url();
                                $image_id = get_post_thumbnail_id();
                                $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', TRUE);
                                $image_title = get_the_title($image_id);
                                if ($image_alt) {
                                    $image_alt = $image_alt;
                                } else {
                                    $image_alt = get_the_title();
                                }
                            }
                            $experience_listing_arr[] = array(
                                'id' => get_the_ID(),
                                'title' => get_the_title(),
                                'url' => get_the_permalink(),
                                'content' => get_the_content(),
                                'author' => get_the_author(),
                                'date' => get_the_date(),
                                'excerpt_desc' => get_the_excerpt(),
                                'img' => $fea_img,
                                'img_alt' => $image_alt,
                            );
                        endwhile;
                        wp_reset_postdata();
                    }
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'heading'=> $content['heading'],
                        'description'=> $content['description'],
                        'experience_listing_arr'  => $experience_listing_arr,
                        'cta'=> $content['cta'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                } elseif($content['acf_fc_layout']=='experiences_listing'){
                    $experience_listing_arr = array();
                    $experience_listing_arg = array(
                        'post_type' => 'experience',
                        'posts_per_page' => '6',
                        'post_status' => 'publish',
                        'orderby' => 'date',
                        'order' => 'ASC',
                    );
                    $experience_listing_query = new \WP_Query($experience_listing_arg);
                    if ($experience_listing_query->have_posts()) {
                        while ($experience_listing_query->have_posts()) : $experience_listing_query->the_post();
                            $fea_img = '';
                            if (get_the_post_thumbnail_url()) {
                                $fea_img = get_the_post_thumbnail_url();
                                $image_id = get_post_thumbnail_id();
                                $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', TRUE);
                                $image_title = get_the_title($image_id);
                                if ($image_alt) {
                                    $image_alt = $image_alt;
                                } else {
                                    $image_alt = get_the_title();
                                }
                            }
                            $experience_listing_arr[] = array(
                                'id' => get_the_ID(),
                                'title' => get_the_title(),
                                'url' => get_the_permalink(),
                                'author' => get_the_author(),
                                'date' => get_the_date(),
                                'excerpt_desc' => get_the_excerpt(),
                                'img' => $fea_img,
                                'img_alt' => $image_alt,
                            );
                        endwhile;
                        wp_reset_postdata();
                    }
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'experience_listing_arr'  => $experience_listing_arr,
                    ];
                    array_push($data, $this_content);
                } elseif($content['acf_fc_layout']=='gallery_tabs'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'tabs'=> $content['tabs'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                } elseif($content['acf_fc_layout']=='rates_tabs'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'tabs'=> $content['tabs'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                }
                elseif($content['acf_fc_layout']=='get_in_touch'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'heading' => $content['heading'],
                        'description' => $content['description'],
                        'contact' => $content['contact'],
                        'address' => $content['address'],
                        'form_shortcode' => $content['form_shortcode'],
                        'button' => $content['button'],
                        'extra_id' => $content['extra_id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                }
                elseif($content['acf_fc_layout']=='video_banner'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'heading'=> $content['heading'],
                        'description'=> $content['description'],
                        'cta'=> $content['cta'],
                        'bg_image' => $content['bg_image'],
                        'video' => $content['video'],
                        'extra_id' => $content['extra_id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                }
                else if($content['acf_fc_layout']=='image_content'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'background_color' => $content['background_color'],
                        'image' => $content['image'],
                        'image_position' => $content['image_position'],
                        'heading' => $content['heading'],
                        'description' => $content['description'],
                        'button' => $content['button'],
                        'extra_class' => $content['extra_class'],
                        'extra_id' => $content['extra_id']
                    ];
                    array_push($data, $this_content);
                } else if($content['acf_fc_layout']=='image_content_with_tabs'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'image' => $content['image'],
                        'image_position' => $content['image_position'],
                        'heading' => $content['heading'],
                        'description' => $content['description'],
                        'tabs' => $content['tabs'],
                        'button' => $content['button'],
                        'button_two' => $content['button_two'],
                        'extra_class' => $content['extra_class'],
                        'extra_id' => $content['extra_id']
                    ];
                    array_push($data, $this_content);
                } else if($content['acf_fc_layout']=='image_content_with_popup'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'image' => $content['image'],
                        'image_position' => $content['image_position'],
                        'heading' => $content['heading'],
                        'description' => $content['description'],
                        'popup_image' => $content['popup_image'],
                        'popup_content_left' => $content['popup_content_left'],
                        'popup_content_right' => $content['popup_content_right'],
                        'button' => $content['button'],
                        'button_two' => $content['button_two'],
                        'extra_class' => $content['extra_class'],
                        'extra_id' => $content['extra_id']
                    ];
                    array_push($data, $this_content);
                } else if($content['acf_fc_layout']=='media_content'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'background_color' => $content['background_color'],
                        'media_type' => $content['media_type'],
                        'image_position' => $content['image_position'],
                        'heading' => $content['heading'],
                        'description' => $content['description'],
                        'slider' => $content['slider'],
                        'video_url' => $content['video_url'],
                        'video_poster' => $content['video_poster'],
                        'button' => $content['button'],
                        'button_two' => $content['button_two'],
                        'extra_class' => $content['extra_class'],
                        'extra_id' => $content['extra_id']
                    ];
                    array_push($data, $this_content);
                } else if($content['acf_fc_layout']=='related_cards'){
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'card_one'=> $content['card_one'],
                        'card_two'=> $content['card_two'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                } elseif ($content['acf_fc_layout'] == 'stay_banner') {
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'heading' => $content['heading'],
                        'description' => $content['description'],
                        'slider' => $content['slider'],
                        'button' => $content['button'],
                        'extra_id' => $content['extra_id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                } elseif ($content['acf_fc_layout'] == 'stacked_media_content') {
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'type' => $content['type'],
                        'heading' => $content['heading'],
                        'description' => $content['description'],
                        'video_url' => $content['video_url'],
                        'large_image' => $content['large_image'],
                        'small_image' => $content['small_image'],
                        'button' => $content['button'],
                        'popup_button' => $content['popup_button'],
                        'popup_image' => $content['popup_image'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section'],
                    ];
                    array_push($data, $this_content);
                } elseif ($content['acf_fc_layout'] == 'full_width_banner') {
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'background_image' => $content['background_image'],
                        'heading' => $content['heading'],
                        'description' => $content['description'],
                        'button' => $content['button'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section'],
                    ];
                    array_push($data, $this_content);
                } elseif ($content['acf_fc_layout'] == 'press_listing') {
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'press_item'=> $content['press_item'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section'],
                    ];
                    array_push($data, $this_content);
                } elseif ($content['acf_fc_layout'] == 'accordion') {
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'heading'=> $content['heading'],
                        'tabs'=> $content['tabs'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section'],
                    ];
                    array_push($data, $this_content);
                } elseif ($content['acf_fc_layout'] == 'testimonials') {
                    $this_content = (object) [
                        'layout' => $content['acf_fc_layout'],
                        'testimonial_text' => $content['testimonial_text'],
                        'testimonial_logo' => $content['testimonial_logo'],
                        'id' => $content['id'],
                        'extra_class' => $content['extra_class'],
                        'hide_section' => $content['hide_section']
                    ];
                    array_push($data, $this_content);
                }
            }
        }
        return $data;
    }
}
