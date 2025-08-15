<?php
/**
 * Template for the [sardius_media] shortcode
 *
 * @package Sardius_Feed_Plugin
 */

// Get shortcode attributes
$id = $atts['id'] ?? '';
$show_player = $atts['show_player'] ?? 'true';
$show_meta = $atts['show_meta'] ?? 'true';
$width = $atts['width'] ?? '100%';
$height = $atts['height'] ?? 'auto';

if (empty($id)) {
    echo '<p>Error: Media ID is required.</p>';
    return;
}

// Find the media item
$media_item = null;
foreach ($feed_data['hits'] as $hit) {
    if ($hit['id'] === $id) {
        $media_item = $hit;
        break;
    }
}

if (!$media_item) {
    echo '<p>Error: Media not found.</p>';
    return;
}
?>

<div class="sardius-media-embed" style="width: <?php echo esc_attr($width); ?>; height: <?php echo esc_attr($height); ?>;">
    <?php if ($show_player === 'true' && !empty($media_item['media']['url'])): ?>
        <div class="media-player">
            <video controls style="width: 100%; height: auto;">
                <source src="<?php echo esc_url($media_item['media']['url']); ?>" type="<?php echo esc_attr($media_item['media']['mimeType']); ?>">
                Your browser does not support the video tag.
            </video>
        </div>
    <?php endif; ?>
    
    <?php if ($show_meta === 'true'): ?>
        <div class="media-meta">
            <h3><?php echo esc_html($media_item['title']); ?></h3>
            <p>
                <strong>Date:</strong> <?php echo $plugin->format_date($media_item['airDate']); ?> |
                <strong>Duration:</strong> <?php echo $plugin->format_duration($media_item['duration']); ?>
                <?php if (!empty($media_item['categories'])): ?>
                    | <strong>Categories:</strong> <?php echo esc_html(implode(', ', $media_item['categories'])); ?>
                <?php endif; ?>
            </p>
            <p><a href="<?php echo $plugin->get_media_url($media_item); ?>" target="_blank">View Full Page</a></p>
        </div>
    <?php endif; ?>
</div>
