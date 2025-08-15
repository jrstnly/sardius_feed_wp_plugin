<?php
/**
 * Simple archive template for Elementor integration
 *
 * @package Sardius_Feed_Plugin
 */

get_header(); 

// Get the selected Elementor template ID
$archive_template_id = intval(get_option('sardius_archive_elementor_template_id', 0));

if ($archive_template_id > 0) {
    // Render the Elementor template using the shortcode
    echo do_shortcode('[elementor-template id="' . intval($archive_template_id) . '"]');
} else {
    // Fallback to shortcode if no Elementor template is selected
    echo do_shortcode('[sardius_media_archive]');
}

get_footer(); ?>
