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
