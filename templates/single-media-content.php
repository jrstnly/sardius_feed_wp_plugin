<?php
/**
 * Template for single media content
 *
 * @package Sardius_Feed_Plugin
 */

$title_text = $plugin->format_text_value($media_item['title'] ?? '');
$air_date_text = $plugin->format_text_value($media_item['airDate'] ?? '');
$series_text = $plugin->format_text_value($media_item['series'] ?? '');

$title = esc_html($title_text);
$airDate = esc_html($plugin->format_date($air_date_text));
$series = $series_text !== '' ? '<span class="media-series"><strong>' . esc_html__('Series:', 'sardius-feed') . '</strong> ' . esc_html($series_text) . '</span>' : '';

// Create individual pills for each scripture reference
$scripture = '';
$metadata = $media_item['metadata'] ?? array();
if (is_object($metadata)) {
    $metadata = get_object_vars($metadata);
}
$bible_reference = is_array($metadata) ? ($metadata['bibleReference'] ?? '') : '';
if ($bible_reference !== '' && $bible_reference !== array()) {
    $scripture_references = is_array($bible_reference) ? $bible_reference : array($bible_reference);
    if (!empty($scripture_references) && array_keys($scripture_references) !== range(0, count($scripture_references) - 1)) {
        $scripture_references = array($scripture_references);
    }
    $scripture_pills = array();
    foreach ($scripture_references as $reference) {
        $reference_text = $plugin->format_text_value($reference);
        if ($reference_text !== '') {
            $scripture_pills[] = '<span class="media-scripture">' . esc_html($reference_text) . '</span>';
        }
    }
    if (!empty($scripture_pills)) {
        $scripture = '<div class="media-scripture-container"><strong>' . esc_html__('Scripture:', 'sardius-feed') . '</strong><div class="scripture-pills">' . implode(' ', $scripture_pills) . '</div></div>';
    }
}

$descriptionText = $plugin->format_text_value($media_item['description'] ?? '');
$description = $descriptionText ? ('<div class="media-description"><h3>' . esc_html__('Description', 'sardius-feed') . '</h3><p>' . esc_html($descriptionText) . '</p></div>') : '';
$video = $plugin->build_video_player_html($media_item);
?>

<div class="sardius-media-single">
    <div class="container">
        <div class="media-content">
            <div class="media-player">
                <?php echo $video; ?>
            </div>
            
            <div class="media-header">
                <h1 class="media-title"><?php echo $title; ?></h1>
                <div class="media-meta">
                    <span class="media-date">
                        <strong><?php _e('Date:', 'sardius-feed'); ?></strong> <?php echo $airDate; ?>
                    </span>
                    <?php echo $series; ?>
                    <?php echo $scripture; ?>
                </div>
            </div>
            
            <?php echo $description; ?>
        </div>
    </div>
</div>
