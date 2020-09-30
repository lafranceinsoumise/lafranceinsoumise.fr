<?php
/**
 * Template Name: Largeur de fenêtre sans barre latériale (écrase les options de Generatepress)
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

remove_action('generate_after_entry_header', 'generate_blog_single_featured_image');
remove_action('generate_before_content', 'generate_blog_single_featured_image');
remove_action('generate_after_header', 'generate_blog_single_featured_image');
remove_action('generate_before_content', 'generate_featured_page_header_inside_single');
remove_action('generate_after_header', 'generate_featured_page_header');

require_once dirname(__FILE__).'/../generatepress/page.php';
