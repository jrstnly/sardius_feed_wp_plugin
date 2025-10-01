<?php
/**
 * Template for the [sardius_media_search] shortcode
 *
 * @package Sardius_Feed_Plugin
 */

// Get shortcode attributes
$show_search = $atts['show_search'] ?? 'true';
$show_series = $atts['show_series'] ?? 'true';
$show_date = $atts['show_date'] ?? 'true';
$show_sort = $atts['show_sort'] ?? 'true';
$results_per_page = intval($atts['results_per_page'] ?? 12);

// Get unique categories
$categories = array();
foreach ($feed_data['hits'] as $hit) {
    if (isset($hit['categories']) && is_array($hit['categories'])) {
        foreach ($hit['categories'] as $category) {
            if (!in_array($category, $categories)) {
                $categories[] = $category;
            }
        }
    }
}
?>

<div class="sardius-media-search">
    <?php 
    // Include the latest service banner
    include SARDIUS_FEED_PLUGIN_PATH . 'templates/latest-service-banner.php';
    ?>
    
    <div class="search-filters">
        <?php if ($show_search === 'true'): ?>
            <div class="filter-group">
                <label for="frontend-search"><?php _e('Search:', 'sardius-feed'); ?></label>
                <input type="text" id="frontend-search" placeholder="<?php _e('Search titles...', 'sardius-feed'); ?>">
            </div>
        <?php endif; ?>
        
        <?php if ($show_series === 'true'): ?>
            <div class="filter-group">
                <label for="frontend-series"><?php _e('Series:', 'sardius-feed'); ?></label>
                <select id="frontend-series">
                    <option value=""><?php _e('All Series', 'sardius-feed'); ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        
        <?php if ($show_date === 'true'): ?>
            <div class="filter-group">
                <label for="frontend-date"><?php _e('Date Range:', 'sardius-feed'); ?></label>
                <select id="frontend-date">
                    <option value=""><?php _e('All Dates', 'sardius-feed'); ?></option>
                    <option value="today"><?php _e('Today', 'sardius-feed'); ?></option>
                    <option value="week"><?php _e('This Week', 'sardius-feed'); ?></option>
                    <option value="month"><?php _e('This Month', 'sardius-feed'); ?></option>
                    <option value="year"><?php _e('This Year', 'sardius-feed'); ?></option>
                </select>
            </div>
        <?php endif; ?>
        
        <?php if ($show_sort === 'true'): ?>
            <div class="filter-group">
                <label for="frontend-sort"><?php _e('Sort:', 'sardius-feed'); ?></label>
                <select id="frontend-sort">
                    <option value="desc"><?php _e('Newest First', 'sardius-feed'); ?></option>
                    <option value="asc"><?php _e('Oldest First', 'sardius-feed'); ?></option>
                    <option value="title"><?php _e('Title A-Z', 'sardius-feed'); ?></option>
                </select>
            </div>
        <?php endif; ?>
        
        <button id="frontend-apply-filters" class="button">
            <?php _e('Apply Filters', 'sardius-feed'); ?>
        </button>
        
        <button id="frontend-clear-filters" class="button button-secondary">
            <?php _e('Clear All', 'sardius-feed'); ?>
        </button>
    </div>
    
    <div class="search-results">
        <div id="frontend-results-count" class="results-count">
            <?php printf(__('Showing %d items', 'sardius-feed'), count($feed_data['hits'])); ?>
        </div>
        
        <div id="frontend-media-grid" class="media-grid">
            <?php foreach ($feed_data['hits'] as $item): ?>
                <div class="media-item" data-id="<?php echo esc_attr($item['id']); ?>">
                    <div class="media-thumbnail">
                        <?php if (!empty($item['files']) && !empty($item['files'][0]['url'])): ?>
                            <img src="<?php echo esc_url($item['files'][0]['url']); ?>" 
                                 alt="<?php echo esc_attr($item['title']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="no-thumbnail">
                                <span class="dashicons dashicons-video-alt3"></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="media-duration">
                            <?php echo $plugin->format_duration($item['duration']); ?>
                        </div>
                    </div>
                    
                    <div class="media-info">
                        <h3 class="media-title">
                            <a href="<?php echo $plugin->get_media_url($item); ?>">
                                <?php echo esc_html($item['title']); ?>
                            </a>
                        </h3>
                        
                        <div class="media-meta">
                            <span class="media-date">
                                <?php echo $plugin->format_date($item['airDate']); ?>
                            </span>
                            
                            <?php if (!empty($item['categories'])): ?>
                                <span class="media-categories">
                                    <?php echo esc_html(implode(', ', $item['categories'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
