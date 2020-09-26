<?php
/**
 * GeneratePress child theme functions and definitions.
 */

add_filter('generate_featured_image_output', function($output){
    if (get_post_format() === 'video') {
        return '';
    }

    return $output;
}, 50);

add_action('wp', function() {
    if (is_singular() && get_post_format() === 'video') {
        remove_action('generate_after_entry_header', 'generate_blog_single_featured_image');
        remove_action('generate_before_content', 'generate_blog_single_featured_image');
        remove_action('generate_after_header', 'generate_blog_single_featured_image');
        remove_action('generate_before_content', 'generate_featured_page_header_inside_single');
        remove_action('generate_after_header', 'generate_featured_page_header');
    }
}, 200);
