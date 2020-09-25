<?php
/**
 * Template Name: Nu pour page d'accueil
 * Template Post Type: post, page
 */

add_filter( 'generate_sidebar_layout', function($layout) {
    return 'no-sidebar';
}, 50);

add_filter('generate_show_title', function() {
    return false;
}, 50);

add_filter('option_generate_secondary_nav_settings', function($options) {
    $options['secondary_nav_position_setting'] = '';

    return $options;
}, 50);

add_filter('body_class', function ($classes) {
    if (is_int($i = array_search('contained-content', $classes))) {
        $classes[$i] = 'full-width-content';
    } else {
        $classes[] = 'full-width-content';
    }

    return $classes;
}, 50);

add_filter('generate_featured_image_output', function(){return '';}, 50);

require_once dirname(__FILE__).'/../generatepress/page.php';
