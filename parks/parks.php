<?php
/**
 * Plugin Name: Parks Manager
 * Description: A plugin to manage parks with custom post type, taxonomy, and shortcode.
 * Version: 1.0.0
 * Author: Milos Bogdanovic
 */

// Register Custom Post Type "Parks"
function parks_custom_post_type() {
    $labels = array(
        'name'               => 'Parks',
        'singular_name'      => 'Park',
        'menu_name'          => 'Parks',
        'name_admin_bar'     => 'Park',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Park',
        'new_item'           => 'New Park',
        'edit_item'          => 'Edit Park',
        'view_item'          => 'View Park',
        'all_items'          => 'All Parks',
        'search_items'       => 'Search Parks',
        'not_found'          => 'No parks found.',
        'not_found_in_trash' => 'No parks found in Trash.',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'supports'           => array('title', 'editor', 'custom-fields'),
        'rewrite'            => array('slug' => 'parks'),
    );

    register_post_type('parks', $args);
}
add_action('init', 'parks_custom_post_type');

// Register Custom Taxonomy "Facilities"
function parks_custom_taxonomy() {
    $labels = array(
        'name'              => 'Facilities',
        'singular_name'     => 'Facility',
        'search_items'      => 'Search Facilities',
        'all_items'         => 'All Facilities',
        'parent_item'       => 'Parent Facility',
        'parent_item_colon' => 'Parent Facility:',
        'edit_item'         => 'Edit Facility',
        'update_item'       => 'Update Facility',
        'add_new_item'      => 'Add New Facility',
        'new_item_name'     => 'New Facility Name',
        'menu_name'         => 'Facilities',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'facilities'),
    );

    register_taxonomy('facilities', array('parks'), $args);
}
add_action('init', 'parks_custom_taxonomy');

// Shortcode to Display Parks
function parks_list_shortcode($atts) {
    $args = array(
        'post_type'      => 'parks',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $output = '<div class="parks-list">';
        while ($query->have_posts()) {
            $query->the_post();

            $name = get_the_title();
            $location = get_post_meta(get_the_ID(), 'location', true);
            $hours = get_post_meta(get_the_ID(), 'hours', true);
            $description = wp_trim_words(get_the_content(), 20, '...');

            $output .= '<div class="park-item">';
            $output .= '<h2>' . esc_html($name) . '</h2>';
            $output .= '<p><strong>Location:</strong> ' . esc_html($location) . '</p>';
            $output .= '<p><strong>Hours:</strong> ' . esc_html($hours) . '</p>';
            $output .= '<p>' . esc_html($description) . '</p>';
            $output .= '</div>';
        }
        $output .= '</div>';

        wp_reset_postdata();

        return $output;
    } else {
        return '<p>No parks found.</p>';
    }
}
add_shortcode('park_list', 'parks_list_shortcode');

// Add Basic Styling
function parks_custom_styles() {
    echo '<style>
        .parks-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .park-item {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
            width: 300px;
        }
        .park-item h2 {
            margin: 0 0 10px;
            font-size: 1.5em;
        }
    </style>';
}
add_action('wp_head', 'parks_custom_styles');
