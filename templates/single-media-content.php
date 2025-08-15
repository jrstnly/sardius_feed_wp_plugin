<?php
/**
 * Template for single media content
 *
 * @package Sardius_Feed_Plugin
 */

$title = esc_html($media_item['title'] ?? '');
$airDate = esc_html($plugin->format_date($media_item['airDate'] ?? ''));
$duration = esc_html($plugin->format_duration($media_item['duration'] ?? 0));
$categories = !empty($media_item['categories']) ? '<span class="media-categories">' . esc_html(implode(', ', (array)$media_item['categories'])) . '</span>' : '';
$descriptionText = $media_item['searchText'] ?? '';
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
                        <strong><?php _e('Air Date:', 'sardius-feed'); ?></strong> <?php echo $airDate; ?>
                    </span>
                    <span class="media-duration">
                        <strong><?php _e('Duration:', 'sardius-feed'); ?></strong> <?php echo $duration; ?>
                    </span>
                    <?php echo $categories; ?>
                </div>
            </div>
            
            <?php echo $description; ?>
        </div>
    </div>
</div>
