<?php
/**
 * Template Name: Largeur normalisée sans barre latérale
 * Template Post Type: post, page
 */

add_filter( 'generate_sidebar_layout', function($layout) {
    return 'no-sidebar';
});

add_filter('body_class', function ($classes) {
    $classes[] = 'template-legacy-header';

    return $classes;
}, 20);

add_filter('generate_featured_image_output', function(){return '';});

require_once dirname(__FILE__).'/../generatepress/page.php';
