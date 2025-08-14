<?php
/**
 * Archive template that renders an Elementor Saved Template for Sardius Media archive
 */

get_header();

$archive_template_id = intval(get_option('sardius_archive_elementor_template_id', 0));

if ($archive_template_id > 0) {
    $content_html = do_shortcode('[elementor-template id="' . $archive_template_id . '"]');
    if (empty($content_html) && class_exists('Elementor\Plugin')) {
        $content_html = do_shortcode(\Elementor\Plugin::instance()->frontend->get_builder_content_for_display($archive_template_id, true));
    }
    echo $content_html; // Render the elementor template content
} else {
    // Fallback if template missing
    include SARDIUS_FEED_PLUGIN_PATH . 'templates/archive-sardius_media.php';
}

get_footer();
