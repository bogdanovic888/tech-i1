<?php
/**
 * Plugin Name: Parks Manager
 * Description: A plugin to manage parks with custom post type, taxonomy, and shortcode.
 * Version: 1.1.0
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

// Shortcode to Display Parks with Filtering
function parks_list_shortcode($atts) {
    // Capture current filter value
    $selected_facility = isset($_GET['facility']) ? sanitize_text_field($_GET['facility']) : '';

    // Query arguments
    $args = array(
        'post_type'      => 'parks',
        'posts_per_page' => -1,
    );

    // Add taxonomy filter if a facility is selected
    if (!empty($selected_facility)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'facilities',
                'field'    => 'slug',
                'terms'    => $selected_facility,
            ),
        );
    }

    $query = new WP_Query($args);

    // Build dropdown for filtering
    $output = '<form method="GET" style="margin-bottom: 20px;">';
    $output .= '<select name="facility" onchange="this.form.submit()">';
    $output .= '<option value="">All Facilities</option>';

    $facilities = get_terms(array('taxonomy' => 'facilities', 'hide_empty' => true));
    foreach ($facilities as $facility) {
        $selected = $selected_facility === $facility->slug ? 'selected' : '';
        $output .= '<option value="' . esc_attr($facility->slug) . '" ' . $selected . '>' . esc_html($facility->name) . '</option>';
    }

    $output .= '</select>';
    $output .= '</form>';

    // List parks
    if ($query->have_posts()) {
        $output .= '<div class="parks-list">';
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
    } else {
        $output .= '<p>No parks found.</p>';
    }

    wp_reset_postdata();

    return $output;
}
add_shortcode('park_list', 'parks_list_shortcode');

// Enqueue Styling
function parks_enqueue_styles() {
    wp_enqueue_style('parks-styles', plugin_dir_url(__FILE__) . 'css/parks-styles.css');

    // Ensure styles are loaded on single park pages
    if (is_singular('parks')) {
        wp_enqueue_style('parks-styles');
    }
}
add_action('wp_enqueue_scripts', 'parks_enqueue_styles');

// Register meta fields for Parks
function parks_register_meta_boxes() {
    add_meta_box(
        'park_details',
        'Park Details',
        'parks_meta_box_callback',
        'parks'
    );
}
add_action('add_meta_boxes', 'parks_register_meta_boxes');

function parks_meta_box_callback($post) {
    // Get existing meta values
    $location = get_post_meta($post->ID, 'location', true);
    $hours = get_post_meta($post->ID, 'hours', true);

    // Display fields
    echo '<label for="park_location">Location:</label>';
    echo '<input type="text" id="park_location" name="park_location" value="' . esc_attr($location) . '" style="width:100%;"><br><br>';
    echo '<label for="park_hours">Hours:</label>';
    echo '<input type="text" id="park_hours" name="park_hours" value="' . esc_attr($hours) . '" style="width:100%;">';
}

// Save meta fields
function parks_save_meta_boxes($post_id) {
    if (array_key_exists('park_location', $_POST)) {
        update_post_meta($post_id, 'location', sanitize_text_field($_POST['park_location']));
    }
    if (array_key_exists('park_hours', $_POST)) {
        update_post_meta($post_id, 'hours', sanitize_text_field($_POST['park_hours']));
    }
}
add_action('save_post', 'parks_save_meta_boxes');

