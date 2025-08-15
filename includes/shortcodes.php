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
        
        ob_start();
        include SARDIUS_FEED_PLUGIN_PATH . 'templates/shortcode-media.php';
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
        
        ob_start();
        include SARDIUS_FEED_PLUGIN_PATH . 'templates/shortcode-media-list.php';
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
        
        ob_start();
        include SARDIUS_FEED_PLUGIN_PATH . 'templates/shortcode-media-player.php';
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
        
        ob_start();
        include SARDIUS_FEED_PLUGIN_PATH . 'templates/shortcode-media-search.php';
        return ob_get_clean();
    }
}

// Initialize shortcodes
new SardiusFeedShortcodes(); 