<?php
/**
 * Template for the [sardius_media_list] shortcode
 *
 * @package Sardius_Feed_Plugin
 */

// Get shortcode attributes
$category = $atts['category'] ?? '';
$limit = intval($atts['limit'] ?? 10);
$sort = $atts['sort'] ?? 'desc';
$show_thumbnails = $atts['show_thumbnails'] ?? 'true';
$show_duration = $atts['show_duration'] ?? 'true';
$show_date = $atts['show_date'] ?? 'true';

// Filter items
$items = $feed_data['hits'];

if (!empty($category)) {
    $items = array_filter($items, function($item) use ($category) {
        return in_array($category, $item['categories'] ?? array());
    });
}

// Sort items
usort($items, function($a, $b) use ($sort) {
    $date_a = strtotime($a['airDate']);
    $date_b = strtotime($b['airDate']);
    
    if ($sort === 'asc') {
        return $date_a - $date_b;
    } else {
        return $date_b - $date_a;
    }
});

// Limit items
$items = array_slice($items, 0, $limit);
?>

<div class="sardius-media-list">
    <?php foreach ($items as $item): ?>
        <div class="media-list-item">
            <?php if ($show_thumbnails === 'true' && !empty($item['files']) && !empty($item['files'][0]['url'])): ?>
                <div class="media-thumbnail">
                    <img src="<?php echo esc_url($item['files'][0]['url']); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                </div>
            <?php endif; ?>
            
            <div class="media-info">
                <h4><a href="<?php echo $plugin->get_media_url($item); ?>"><?php echo esc_html($item['title']); ?></a></h4>
                
                <div class="media-details">
                    <?php if ($show_date === 'true'): ?>
                        <span class="media-date"><?php echo $plugin->format_date($item['airDate']); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($show_duration === 'true'): ?>
                        <span class="media-duration"><?php echo $plugin->format_duration($item['duration']); ?></span>
                    <?php endif; ?>
                    
                    <?php if (!empty($item['categories'])): ?>
                        <span class="media-categories"><?php echo esc_html(implode(', ', $item['categories'])); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
