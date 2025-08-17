<?php
/**
 * Template for single media content
 *
 * @package Sardius_Feed_Plugin
 */

$title = esc_html($media_item['title'] ?? '');
$airDate = esc_html($plugin->format_date($media_item['airDate'] ?? ''));
$series = !empty($media_item['series']) ? '<span class="media-series"><strong>' . esc_html__('Series:', 'sardius-feed') . '</strong> ' . esc_html($media_item['series']) . '</span>' : '';

// Create individual pills for each scripture reference
$scripture = '';
if (!empty($media_item['metadata']['bibleReference'])) {
    $scripture_references = (array)$media_item['metadata']['bibleReference'];
    $scripture_pills = array();
    foreach ($scripture_references as $reference) {
        $scripture_pills[] = '<span class="media-scripture">' . esc_html($reference) . '</span>';
    }
    $scripture = '<div class="media-scripture-container"><strong>' . esc_html__('Scripture:', 'sardius-feed') . '</strong><div class="scripture-pills">' . implode(' ', $scripture_pills) . '</div></div>';
}

$descriptionText = $media_item['description'] ?? '';
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
