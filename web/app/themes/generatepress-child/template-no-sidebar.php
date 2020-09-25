<?php
/**
 * Template Name: Colonne de texte sans barre latérale (écrase les options de Generatepress)
 * Template Post Type: post, page
 */

add_filter( 'generate_sidebar_layout', function($layout) {
    return 'no-sidebar';
});

add_filter('body_class', function ($classes) {
    if (is_int($i = array_search('full-width-content', $classes))) {
        $classes[$i] = 'contained-content';
    } else {
        $classes[] = 'contained-content';
    }

    $classes[] = 'small-container';
    $classes[] = 'template-legacy-header';

    return $classes;
}, 20);

add_filter('generate_featured_image_output', function(){return '';});

require_once dirname(__FILE__).'/../generatepress/page.php';
