<?php
/**
 * Template Name: Largeur de fenêtre sans barre latériale
 * Template Post Type: post, page
 */

add_filter('generate_sidebar_layout', function($layout) {
    return 'no-sidebar';
});

add_filter('body_class', function ($classes) {
    if (is_int($i = array_search('contained-content', $classes))) {
        $classes[$i] = 'full-width-content';
    } else {
        $classes[] = 'full-width-content';
    }

    $classes[] = 'template-legacy-header';

    return $classes;
}, 20);

add_filter('generate_featured_image_output', function(){return '';});

require_once dirname(__FILE__).'/../generatepress/page.php';
