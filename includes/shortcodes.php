<?php
/**
 * Shortcodes for Sardius Feed Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SardiusFeedShortcodes {
    
    public function __construct() {
        add_shortcode('sardius_media', array($this, 'media_shortcode'));
        add_shortcode('sardius_media_list', array($this, 'media_list_shortcode'));
        add_shortcode('sardius_media_player', array($this, 'media_player_shortcode'));
        add_shortcode('sardius_media_search', array($this, 'media_search_shortcode'));
    }
    
    /**
     * Display a single media item
     * Usage: [sardius_media id="56b5f3F79CB6BB6_1095452938"]
     */
    public function media_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'show_player' => 'true',
            'show_meta' => 'true',
            'width' => '100%',
            'height' => 'auto'
        ), $atts);
        
        if (empty($atts['id'])) {
            return '<p>Error: Media ID is required.</p>';
        }
        
        $plugin = new SardiusFeedPlugin();
        $feed_data = $plugin->get_feed_data();
        
        if (!$feed_data) {
            return '<p>Error: Unable to load media data.</p>';
        }
        
        // Find the media item
        $media_item = null;
        foreach ($feed_data['hits'] as $hit) {
            if ($hit['id'] === $atts['id']) {
                $media_item = $hit;
                break;
            }
        }
        
        if (!$media_item) {
            return '<p>Error: Media not found.</p>';
        }
        
        ob_start();
        ?>
        <div class="sardius-media-embed" style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;">
            <?php if ($atts['show_player'] === 'true' && !empty($media_item['media']['url'])): ?>
                <div class="media-player">
                    <video controls style="width: 100%; height: auto;">
                        <source src="<?php echo esc_url($media_item['media']['url']); ?>" type="<?php echo esc_attr($media_item['media']['mimeType']); ?>">
                        Your browser does not support the video tag.
                    </video>
                </div>
            <?php endif; ?>
            
            <?php if ($atts['show_meta'] === 'true'): ?>
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
        <?php
        return ob_get_clean();
    }
    
    /**
     * Display a list of media items
     * Usage: [sardius_media_list category="Weekly Messages" limit="5" sort="desc"]
     */
    public function media_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => '10',
            'sort' => 'desc',
            'show_thumbnails' => 'true',
            'show_duration' => 'true',
            'show_date' => 'true'
        ), $atts);
        
        $plugin = new SardiusFeedPlugin();
        $feed_data = $plugin->get_feed_data();
        
        if (!$feed_data) {
            return '<p>Error: Unable to load media data.</p>';
        }
        
        // Filter items
        $items = $feed_data['hits'];
        
        if (!empty($atts['category'])) {
            $items = array_filter($items, function($item) use ($atts) {
                return in_array($atts['category'], $item['categories'] ?? array());
            });
        }
        
        // Sort items
        usort($items, function($a, $b) use ($atts) {
            $date_a = strtotime($a['airDate']);
            $date_b = strtotime($b['airDate']);
            
            if ($atts['sort'] === 'asc') {
                return $date_a - $date_b;
            } else {
                return $date_b - $date_a;
            }
        });
        
        // Limit items
        $items = array_slice($items, 0, intval($atts['limit']));
        
        ob_start();
        ?>
        <div class="sardius-media-list">
            <?php foreach ($items as $item): ?>
                <div class="media-list-item">
                    <?php if ($atts['show_thumbnails'] === 'true' && !empty($item['files']) && !empty($item['files'][0]['url'])): ?>
                        <div class="media-thumbnail">
                            <img src="<?php echo esc_url($item['files'][0]['url']); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="media-info">
                        <h4><a href="<?php echo $plugin->get_media_url($item); ?>"><?php echo esc_html($item['title']); ?></a></h4>
                        
                        <div class="media-details">
                            <?php if ($atts['show_date'] === 'true'): ?>
                                <span class="media-date"><?php echo $plugin->format_date($item['airDate']); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($atts['show_duration'] === 'true'): ?>
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
        
        <style>
        .sardius-media-list {
            margin: 20px 0;
        }
        .media-list-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
        }
        .media-thumbnail {
            flex-shrink: 0;
            width: 120px;
            height: 67px;
            overflow: hidden;
            border-radius: 4px;
        }
        .media-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .media-info {
            flex: 1;
        }
        .media-info h4 {
            margin: 0 0 10px 0;
        }
        .media-info h4 a {
            color: #333;
            text-decoration: none;
        }
        .media-info h4 a:hover {
            color: #0073aa;
        }
        .media-details {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #666;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Display just the video player
     * Usage: [sardius_media_player id="56b5f3F79CB6BB6_1095452938" width="100%" height="400px"]
     */
    public function media_player_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'width' => '100%',
            'height' => '400px'
        ), $atts);
        
        if (empty($atts['id'])) {
            return '<p>Error: Media ID is required.</p>';
        }
        
        $plugin = new SardiusFeedPlugin();
        $feed_data = $plugin->get_feed_data();
        
        if (!$feed_data) {
            return '<p>Error: Unable to load media data.</p>';
        }
        
        // Find the media item
        $media_item = null;
        foreach ($feed_data['hits'] as $hit) {
            if ($hit['id'] === $atts['id']) {
                $media_item = $hit;
                break;
            }
        }
        
        if (!$media_item || empty($media_item['media']['url'])) {
            return '<p>Error: Media not found or no video available.</p>';
        }
        
        ob_start();
        ?>
        <div class="sardius-media-player" style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;">
            <video controls style="width: 100%; height: 100%;">
                <source src="<?php echo esc_url($media_item['media']['url']); ?>" type="<?php echo esc_attr($media_item['media']['mimeType']); ?>">
                Your browser does not support the video tag.
            </video>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Display a search and filter interface for frontend
     * Usage: [sardius_media_search]
     */
    public function media_search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_search' => 'true',
            'show_series' => 'true',
            'show_date' => 'true',
            'show_sort' => 'true',
            'results_per_page' => '12'
        ), $atts);
        
        $plugin = new SardiusFeedPlugin();
        $feed_data = $plugin->get_feed_data();
        
        if (!$feed_data) {
            return '<p>' . __('No media data available', 'sardius-feed') . '</p>';
        }
        
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
        
        ob_start();
        ?>
        <div class="sardius-media-search">
            <div class="search-filters">
                <?php if ($atts['show_search'] === 'true'): ?>
                    <div class="filter-group">
                        <label for="frontend-search"><?php _e('Search:', 'sardius-feed'); ?></label>
                        <input type="text" id="frontend-search" placeholder="<?php _e('Search titles...', 'sardius-feed'); ?>">
                    </div>
                <?php endif; ?>
                
                <?php if ($atts['show_series'] === 'true'): ?>
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
                
                <?php if ($atts['show_date'] === 'true'): ?>
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
                
                <?php if ($atts['show_sort'] === 'true'): ?>
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
        
        <style>
        .sardius-media-search {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .search-filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 150px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #0073aa;
            box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.2);
        }
        
        .search-results {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .results-count {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            font-weight: 600;
            color: #333;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .media-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .media-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .media-thumbnail {
            position: relative;
            height: 180px;
            background: #f5f5f5;
            overflow: hidden;
        }
        
        .media-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .media-item:hover .media-thumbnail img {
            transform: scale(1.05);
        }
        
        .no-thumbnail {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
        }
        
        .no-thumbnail .dashicons {
            font-size: 48px;
        }
        
        .media-duration {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .media-info {
            padding: 15px;
        }
        
        .media-title {
            margin: 0 0 10px 0;
            font-size: 16px;
            line-height: 1.4;
        }
        
        .media-title a {
            color: #333;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .media-title a:hover {
            color: #0073aa;
        }
        
        .media-meta {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 12px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .search-filters {
                flex-direction: column;
                gap: 15px;
            }
            
            .filter-group {
                min-width: auto;
            }
            
            .media-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 15px;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
}

// Initialize shortcodes
new SardiusFeedShortcodes(); 