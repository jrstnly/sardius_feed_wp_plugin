<?php
/**
 * Template for the [sardius_media_player] shortcode
 *
 * @package Sardius_Feed_Plugin
 */

// Get shortcode attributes
$id = $atts['id'] ?? '';
$width = $atts['width'] ?? '100%';
$height = $atts['height'] ?? '400px';

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

if (!$media_item || empty($media_item['media']['url'])) {
    echo '<p>Error: Media not found or no video available.</p>';
    return;
}
?>

<div class="sardius-media-player" style="width: <?php echo esc_attr($width); ?>; height: <?php echo esc_attr($height); ?>;">
    <video controls style="width: 100%; height: 100%;">
        <source src="<?php echo esc_url($media_item['media']['url']); ?>" type="<?php echo esc_attr($media_item['media']['mimeType']); ?>">
        Your browser does not support the video tag.
    </video>
</div>
