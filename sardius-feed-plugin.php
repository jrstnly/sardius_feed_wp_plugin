<?php
/**
 * Plugin Name: Sardius Feed Plugin
 * Plugin URI: https://sardius.media
 * Description: Pulls media from Sardius feed and creates virtual pages with filtering capabilities
 * Version: 1.0.0
 * Author: JR Stanley
 * License: GPL v2 or later
 * Text Domain: sardius-feed
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SARDIUS_FEED_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SARDIUS_FEED_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Derive version from the plugin header so there is a single source of truth
$__sardius_plugin_data = function_exists('get_file_data')
    ? get_file_data(__FILE__, array('Version' => 'Version'), false)
    : array('Version' => '');
$__sardius_version = !empty($__sardius_plugin_data['Version']) ? $__sardius_plugin_data['Version'] : '0.0.0';
define('SARDIUS_FEED_VERSION', $__sardius_version);
unset($__sardius_plugin_data, $__sardius_version);

class SardiusFeedPlugin {
    
    private static $current_media_item = null;
    private $cache_duration = 3600; // 1 hour
    private $feed_data = null;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('template_redirect', array($this, 'handle_virtual_pages'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_sardius_refresh_feed', array($this, 'ajax_refresh_feed'));
        add_action('wp_ajax_sardius_get_filtered_items', array($this, 'ajax_get_filtered_items'));
        add_action('wp_ajax_sardius_get_paginated_items', array($this, 'ajax_get_paginated_items'));
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_shortcode('sardius_media_content', array($this, 'shortcode_media_content'));
        add_shortcode('sardius_media_archive', array($this, 'shortcode_media_archive'));
        add_filter('body_class', array($this, 'filter_body_class'));
        add_filter('archive_template', array($this, 'load_archive_template'));
        add_filter('pre_handle_404', array($this, 'prevent_archive_404'), 10, 2);
        add_action('updated_option', array($this, 'maybe_flush_on_slug_change'), 10, 3);
        add_action('admin_init', array($this, 'ensure_rewrite_rules'));
        
        // Add settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add sitemap generation
        add_action('init', array($this, 'add_sitemap_endpoint'));
        
        // Include shortcodes
        require_once SARDIUS_FEED_PLUGIN_PATH . 'includes/shortcodes.php';
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('sardius-feed', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function activate() {
        // Create necessary database tables or options
        add_option('sardius_feed_last_update', 0);
        add_option('sardius_feed_cache', '');
        if (get_option('sardius_media_slug', null) === null) {
            add_option('sardius_media_slug', 'sardius-media');
        }
        if (get_option('sardius_max_items', null) === null) {
            add_option('sardius_max_items', 1000); // Default: 1000 items
        }
        if (get_option('sardius_admin_items_per_page', null) === null) {
            add_option('sardius_admin_items_per_page', 25); // Default: 25 items per page
        }
        
        // Ensure our rewrite rules are registered before flushing
        $this->add_rewrite_rules();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up if necessary
        flush_rewrite_rules();
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('typekit-fonts', 'https://use.typekit.net/lkd1tkd.css', array(), null);
        wp_enqueue_style('sardius-feed-frontend', SARDIUS_FEED_PLUGIN_URL . 'assets/css/frontend.css', array(), SARDIUS_FEED_VERSION);
        wp_enqueue_style('sardius-feed-single-media-content', SARDIUS_FEED_PLUGIN_URL . 'assets/css/single-media-content.css', array(), SARDIUS_FEED_VERSION);
        wp_enqueue_style('sardius-feed-shortcode-media', SARDIUS_FEED_PLUGIN_URL . 'assets/css/shortcode-media.css', array(), SARDIUS_FEED_VERSION);
        wp_enqueue_style('sardius-feed-shortcode-media-list', SARDIUS_FEED_PLUGIN_URL . 'assets/css/shortcode-media-list.css', array(), SARDIUS_FEED_VERSION);
        wp_enqueue_style('sardius-feed-shortcode-media-player', SARDIUS_FEED_PLUGIN_URL . 'assets/css/shortcode-media-player.css', array(), SARDIUS_FEED_VERSION);
        wp_enqueue_style('sardius-feed-shortcode-media-search', SARDIUS_FEED_PLUGIN_URL . 'assets/css/shortcode-media-search.css', array(), SARDIUS_FEED_VERSION);
        wp_enqueue_style('sardius-feed-shortcode-media-archive', SARDIUS_FEED_PLUGIN_URL . 'assets/css/shortcode-media-archive.css', array(), SARDIUS_FEED_VERSION);
        wp_enqueue_script('sardius-feed-frontend', SARDIUS_FEED_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), SARDIUS_FEED_VERSION, true);
        wp_localize_script('sardius-feed-frontend', 'sardius_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sardius_nonce')
        ));

        // Ensure Elementor frontend assets (and its localized config) are loaded on virtual media pages
        $archive_template_id = intval(get_option('sardius_archive_elementor_template_id', 0));
        if (($this->is_media_request() || ($this->is_media_archive_request() && $archive_template_id > 0)) && class_exists('Elementor\\Plugin')) {
            $elementor = \Elementor\Plugin::instance();
            // Core Elementor assets
            $elementor->frontend->enqueue_styles();
            $elementor->frontend->enqueue_scripts();
            // Try to enqueue active kit/global styles when available
            if (method_exists($elementor, 'kits_manager')) {
                try { $elementor->kits_manager->enqueue_styles(); } catch (\Throwable $e) {}
            }
            // If a specific Saved Template is selected, enqueue its CSS file
            $template_id = intval(get_option('sardius_elementor_template_id', 0));
            if (class_exists('Elementor\\Core\\Files\\CSS\\Post')) {
                if ($template_id > 0) {
                    try { \Elementor\Core\Files\CSS\Post::create($template_id)->enqueue(); } catch (\Throwable $e) {}
                }
                if ($archive_template_id > 0) {
                    try { \Elementor\Core\Files\CSS\Post::create($archive_template_id)->enqueue(); } catch (\Throwable $e) {}
                }
            }
        }
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'sardius-feed') !== false) {
            wp_enqueue_script('sardius-feed-admin', SARDIUS_FEED_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SARDIUS_FEED_VERSION, true);
            wp_enqueue_style('sardius-feed-admin', SARDIUS_FEED_PLUGIN_URL . 'assets/css/admin.css', array(), SARDIUS_FEED_VERSION);

            wp_localize_script('sardius-feed-admin', 'sardius_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('sardius_nonce'),
                'base_slug' => $this->get_base_slug(),
                'site_origin' => home_url()
            ));
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Sardius Feed', 'sardius-feed'),
            __('Sardius Feed', 'sardius-feed'),
            'manage_options',
            'sardius-feed',
            array($this, 'admin_page'),
            'dashicons-video-alt3',
            30
        );
    }
    
    public function admin_page() {
        include SARDIUS_FEED_PLUGIN_PATH . 'templates/admin-page.php';
    }
    
    public function register_settings() {
        register_setting('sardius_feed_settings', 'sardius_account_id');
        register_setting('sardius_feed_settings', 'sardius_feed_id');
        register_setting('sardius_feed_settings', 'sardius_media_slug');
        register_setting('sardius_feed_settings', 'sardius_elementor_template_id');
        register_setting('sardius_feed_settings', 'sardius_archive_elementor_template_id');
        register_setting('sardius_feed_settings', 'sardius_max_items');
        register_setting('sardius_feed_settings', 'sardius_admin_items_per_page');
        
        add_settings_section(
            'sardius_feed_api_settings',
            __('API Settings', 'sardius-feed'),
            array($this, 'settings_section_callback'),
            'sardius-feed'
        );
        
        add_settings_field(
            'sardius_account_id',
            __('Account ID', 'sardius-feed'),
            array($this, 'account_id_field_callback'),
            'sardius-feed',
            'sardius_feed_api_settings'
        );
        
        add_settings_field(
            'sardius_feed_id',
            __('Feed ID', 'sardius-feed'),
            array($this, 'feed_id_field_callback'),
            'sardius-feed',
            'sardius_feed_api_settings'
        );
        

        
        add_settings_section(
            'sardius_feed_pagination_settings',
            __('Pagination Settings', 'sardius-feed'),
            array($this, 'pagination_section_callback'),
            'sardius-feed'
        );
        
        add_settings_field(
            'sardius_max_items',
            __('Maximum Items to Keep', 'sardius-feed'),
            array($this, 'max_items_field_callback'),
            'sardius-feed',
            'sardius_feed_pagination_settings'
        );
        
        add_settings_field(
            'sardius_admin_items_per_page',
            __('Admin Items Per Page', 'sardius-feed'),
            array($this, 'admin_items_per_page_field_callback'),
            'sardius-feed',
            'sardius_feed_pagination_settings'
        );
    }
    
    public function settings_section_callback() {
        echo '<p>' . __('Configure your Sardius Media API settings below.', 'sardius-feed') . '</p>';
    }
    
    public function account_id_field_callback() {
        $value = get_option('sardius_account_id', '');
        echo '<input type="text" id="sardius_account_id" name="sardius_account_id" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your Sardius Media Account ID.', 'sardius-feed') . '</p>';
    }
    
    public function feed_id_field_callback() {
        $value = get_option('sardius_feed_id', '');
        echo '<input type="text" id="sardius_feed_id" name="sardius_feed_id" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your Sardius Media Feed ID.', 'sardius-feed') . '</p>';
    }
    

    
    public function pagination_section_callback() {
        echo '<p>' . __('Configure pagination settings for the feed and admin interface.', 'sardius-feed') . '</p>';
    }
    
    public function max_items_field_callback() {
        $value = get_option('sardius_max_items', 1000);
        echo '<input type="number" id="sardius_max_items" name="sardius_max_items" value="' . esc_attr($value) . '" class="small-text" min="100" max="10000" />';
        echo '<p class="description">' . __('Maximum number of media items to fetch and keep from the API. Higher values may impact performance.', 'sardius-feed') . '</p>';
    }
    
    public function admin_items_per_page_field_callback() {
        $value = get_option('sardius_admin_items_per_page', 25);
        $options = array(
            10 => __('10 items', 'sardius-feed'),
            25 => __('25 items', 'sardius-feed'),
            50 => __('50 items', 'sardius-feed'),
            100 => __('100 items', 'sardius-feed')
        );
        
        echo '<select id="sardius_admin_items_per_page" name="sardius_admin_items_per_page">';
        foreach ($options as $count => $label) {
            $selected = ($value == $count) ? 'selected' : '';
            echo '<option value="' . esc_attr($count) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Number of items to display per page in the admin interface.', 'sardius-feed') . '</p>';
    }
    
    public function get_feed_data() {
        if ($this->feed_data === null) {
            $cached_data = get_option('sardius_feed_cache');
            $last_update = get_option('sardius_feed_last_update');
            
            if ($cached_data && (time() - $last_update) < $this->cache_duration) {
                $this->feed_data = json_decode($cached_data, true);
            } else {
                $this->feed_data = $this->fetch_feed_data();
                if ($this->feed_data) {
                    update_option('sardius_feed_cache', json_encode($this->feed_data));
                    update_option('sardius_feed_last_update', time());
                }
            }
        }
        
        return $this->feed_data;
    }
    
    public function get_paginated_feed_data($page = 1, $items_per_page = null) {
        $feed_data = $this->get_feed_data();
        if (!$feed_data) {
            return array(
                'items' => array(),
                'total' => 0,
                'total_pages' => 0,
                'current_page' => 1
            );
        }
        
        if ($items_per_page === null) {
            $items_per_page = intval(get_option('sardius_admin_items_per_page', 25));
        }
        
        $total_items = count($feed_data['hits']);
        $total_pages = ceil($total_items / $items_per_page);
        $page = max(1, min($page, $total_pages));
        
        $offset = ($page - 1) * $items_per_page;
        $items = array_slice($feed_data['hits'], $offset, $items_per_page);
        
        return array(
            'items' => $items,
            'total' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'items_per_page' => $items_per_page
        );
    }
    
    private function fetch_feed_data() {
        $account_id = get_option('sardius_account_id', '');
        $feed_id = get_option('sardius_feed_id', '');
        
        if (empty($account_id) || empty($feed_id)) {
            error_log('Sardius Feed Plugin: Account ID or Feed ID not configured');
            return false;
        }
        
        $all_hits = array();
        $page = 1;
        $count = 100; // Maximum items per page
        $total_fetched = 0;
        $max_items = intval(get_option('sardius_max_items', 1000)); // Default max items to keep
        
        do {
            $api_url = 'https://api.sardius.media/feeds/' . $account_id . '/' . $feed_id . '/public?count=' . $count . '&page=' . $page;
            
            $response = wp_remote_get($api_url, array(
                'timeout' => 30,
                'headers' => array(
                    'User-Agent' => 'WordPress/SardiusFeedPlugin'
                )
            ));
            
            if (is_wp_error($response)) {
                error_log('Sardius Feed Plugin: Failed to fetch feed data page ' . $page . ' - ' . $response->get_error_message());
                break;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (!$data || !isset($data['hits'])) {
                error_log('Sardius Feed Plugin: Invalid feed data received for page ' . $page);
                break;
            }
            
            // Add hits from this page
            $all_hits = array_merge($all_hits, $data['hits']);
            $total_fetched += count($data['hits']);
            
            // Check if we've reached the maximum items limit
            if ($total_fetched >= $max_items) {
                // Trim to exact limit
                $all_hits = array_slice($all_hits, 0, $max_items);
                break;
            }
            
            // Check if there are more pages
            $total_items = $data['total'] ?? 0;
            if ($total_fetched >= $total_items) {
                break; // No more items to fetch
            }
            
            $page++;
            
            // Safety check to prevent infinite loops
            if ($page > 100) {
                error_log('Sardius Feed Plugin: Safety limit reached while fetching pages');
                break;
            }
            
        } while (true);
        
        if (empty($all_hits)) {
            error_log('Sardius Feed Plugin: No items fetched from API');
            return false;
        }
        
        // Return data in the same format as before
        return array(
            'total' => count($all_hits),
            'hits' => $all_hits
        );
    }
    
    public function handle_virtual_pages() {
        // Render virtual single page when our media slug is present
        $media_slug = get_query_var('media_slug');
        if (!empty($media_slug)) {
            $this->render_virtual_page($media_slug);
        }
    }
    
    private function render_virtual_page($media_slug) {
        $feed_data = $this->get_feed_data();
        if (!$feed_data) {
            wp_die(__('Unable to load media data', 'sardius-feed'));
        }
        
        // Slug comes from rewrite query var
        $slug = $media_slug;
        
        // Parse the slug to get PID
        $slug_parts = explode('-', $slug);
        $pid = $slug_parts[0] ?? '';
        
        // Find the media item by PID
        $media_item = null;
        foreach ($feed_data['hits'] as $hit) {
            if (($hit['pid'] ?? '') === $pid) {
                $media_item = $hit;
                break;
            }
        }
        
        if (!$media_item) {
            wp_die(__('Media not found', 'sardius-feed'));
        }
        
        // Set up virtual page
        global $wp_query;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_home = false;
        $wp_query->is_archive = false;
        $wp_query->is_404 = false;
        
        // Set up SEO-friendly page title and meta
        $wp_query->post_title = $media_item['title'];
        $wp_query->post_content = $media_item['searchText'] ?? '';
        
        // Add SEO meta tags
        add_action('wp_head', function() use ($media_item) {
            $this->add_seo_meta_tags($media_item);
        });
        
        // Try Elementor template first if configured
        $elementor_template_id = intval(get_option('sardius_elementor_template_id', 0));
        if ($elementor_template_id > 0 && function_exists('do_shortcode')) {
            self::$current_media_item = $media_item;
            // Prefer Elementor's shortcode API to render a saved template; allows Elementor to manage its own assets
            $content_html = do_shortcode('[elementor-template id="' . intval($elementor_template_id) . '"]');
            // Fallback to builder API if shortcode produced nothing and Elementor core is present
            if (empty($content_html) && class_exists('Elementor\\Plugin')) {
                $content_html = do_shortcode(\Elementor\Plugin::instance()->frontend->get_builder_content_for_display($elementor_template_id, true));
            }
            // Prepare WP query state for a page-like template
            global $wp_query;
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_404 = false;

            // Output within header/footer
            if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
                echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->');
            } else {
                get_header();
            }

            echo $content_html;

            if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
                echo do_blocks('<!-- wp:template-part {"slug":"footer"} /-->');
            } else {
                get_footer();
            }
        } elseif (!empty($custom_template_html = $this->get_media_template_html($media_item))) {
            // If a custom admin-defined template exists, render it; otherwise use default template file
            // Prepare WP query state for a page-like template
            global $wp_query;
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_404 = false;

            if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
                echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->');
            } else {
                get_header();
            }

            echo $custom_template_html; // Admin-defined HTML

            if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
                echo do_blocks('<!-- wp:template-part {"slug":"footer"} /-->');
            } else {
                get_footer();
            }
        } else {
            // Make current plugin instance available to the template for helpers
            $plugin = $this;
            // Include the template
            include SARDIUS_FEED_PLUGIN_PATH . 'templates/single-media.php';
        }
        exit;
    }
    
    public function ajax_refresh_feed() {
        check_ajax_referer('sardius_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'sardius-feed'));
        }
        
        // Clear cache and fetch new data
        delete_option('sardius_feed_cache');
        delete_option('sardius_feed_last_update');
        
        $feed_data = $this->get_feed_data();
        
        if ($feed_data) {
            wp_send_json_success(array(
                'message' => sprintf(__('Feed refreshed successfully. Found %d items.', 'sardius-feed'), count($feed_data['hits']))
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to refresh feed', 'sardius-feed')
            ));
        }
    }
    
    public function ajax_get_filtered_items() {
        check_ajax_referer('sardius_nonce', 'nonce');
        
        $feed_data = $this->get_feed_data();
        if (!$feed_data) {
            wp_send_json_error(array('message' => __('No feed data available', 'sardius-feed')));
        }
        
        $filters = $_POST['filters'] ?? array();
        $filtered_items = $this->filter_items($feed_data['hits'], $filters);

        // Prepare items for frontend rendering
        $prepared_items = [];
        foreach ($filtered_items as $item) {
            $prepared_items[] = [
                'pid' => $item['pid'],
                'title' => $item['title'],
                'url' => $this->get_media_url($item),
                'thumbnail_url' => !empty($item['files'][0]['url']) ? $item['files'][0]['url'] : '',
                'duration_formatted' => $this->format_duration($item['duration'] ?? 0),
                'series' => $item['series'] ?? '',
                'bible_reference' => !empty($item['metadata']['bibleReference']) ? implode(', ', $item['metadata']['bibleReference']) : '',
                'air_date_formatted' => $this->format_date($item['airDate']),
            ];
        }
        
        wp_send_json_success(array(
            'items' => $prepared_items,
            'total' => count($prepared_items)
        ));
    }
    
    public function ajax_get_paginated_items() {
        check_ajax_referer('sardius_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'sardius-feed'));
        }
        
        $page = intval($_POST['page'] ?? 1);
        $items_per_page = intval($_POST['items_per_page'] ?? 25);
        
        $paginated_data = $this->get_paginated_feed_data($page, $items_per_page);
        
        // Prepare items for frontend rendering
        $prepared_items = [];
        foreach ($paginated_data['items'] as $item) {
            $prepared_items[] = [
                'id' => $item['id'],
                'pid' => $item['pid'],
                'title' => $item['title'],
                'url' => $this->get_media_url($item),
                'thumbnail_url' => !empty($item['files'][0]['url']) ? $item['files'][0]['url'] : '',
                'duration_formatted' => $this->format_duration($item['duration'] ?? 0),
                'series' => $item['series'] ?? '',
                'bible_reference' => !empty($item['metadata']['bibleReference']) ? implode(', ', $item['metadata']['bibleReference']) : '',
                'air_date_formatted' => $this->format_date($item['airDate']),
                'air_date' => $item['airDate'],
                'duration' => $item['duration'] ?? 0,
                'categories' => $item['categories'] ?? []
            ];
        }
        
        wp_send_json_success(array(
            'items' => $prepared_items,
            'pagination' => array(
                'total' => $paginated_data['total'],
                'total_pages' => $paginated_data['total_pages'],
                'current_page' => $paginated_data['current_page'],
                'items_per_page' => $paginated_data['items_per_page']
            )
        ));
    }
    
    private function filter_items($items, $filters) {
        $filtered = $items;
        
        // Filter by type (e.g. 'message', 'full_service', 'spanish')
        if (!empty($filters['type'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                // This logic assumes that the type is stored in the tags or categories.
                // You might need to adjust this depending on where this data is in your feed.
                $type_tags = ['message', 'full service', 'spanish']; // Example tags
                $item_tags = array_map('strtolower', $item['tags'] ?? []);
                
                if ($filters['type'] === 'message') {
                    // Exclude 'full service' and 'spanish'
                    return !in_array('full service', $item_tags) && !in_array('spanish', $item_tags);
                }
                
                return in_array(strtolower($filters['type']), $item_tags);
            });
        }
        
        // Filter by series
        if (!empty($filters['category'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                return $item['series'] === $filters['category'];
            });
        }
        
        // Filter by search term
        if (!empty($filters['search'])) {
            $search_term = strtolower($filters['search']);
            $filtered = array_filter($filtered, function($item) use ($search_term) {
                return strpos(strtolower($item['title']), $search_term) !== false ||
                       strpos(strtolower($item['searchText'] ?? ''), $search_term) !== false ||
                       strpos(strtolower($item['series'] ?? ''), $search_term) !== false ||
                       strpos(strtolower(implode(', ', $item['metadata']['bibleReference'] ?? [])), $search_term) !== false;
            });
        }
        
        // Filter by date range
        if (!empty($filters['dateFrom']) && !empty($filters['dateTo'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                $item_date = strtotime($item['airDate']);
                $from_date = strtotime($filters['dateFrom']);
                $to_date = strtotime($filters['dateTo'] . ' 23:59:59');
                return $item_date >= $from_date && $item_date <= $to_date;
            });
        }
        
        // Sort items
        if (!empty($filters['sort'])) {
            usort($filtered, function($a, $b) use ($filters) {
                switch ($filters['sort']) {
                    case 'asc':
                        return strtotime($a['airDate']) - strtotime($b['airDate']);
                    case 'desc':
                        return strtotime($b['airDate']) - strtotime($a['airDate']);
                    case 'title':
                        return strcasecmp($a['title'], $b['title']);
                    case 'duration':
                        return $b['duration'] - $a['duration'];
                    case 'duration-asc':
                        return $a['duration'] - $b['duration'];
                    default:
                        return strtotime($b['airDate']) - strtotime($a['airDate']);
                }
            });
        }
        
        return array_values($filtered);
    }
    
    public function get_media_url($media_item) {
        $pid = $media_item['pid'] ?? '';
        $title = $media_item['title'] ?? '';
        
        // Clean the title for URL
        $clean_title = $this->clean_title_for_url($title);
        
        $base_slug = $this->get_base_slug();
        return home_url('/' . $base_slug . '/' . $pid . '-' . $clean_title . '/');
    }
    
    private function clean_title_for_url($title) {
        // Remove quotes and special characters
        $clean = str_replace(array('"', '"', '"', "'", "'", '|'), '', $title);
        
        // Replace spaces with hyphens
        $clean = str_replace(' ', '-', $clean);
        
        // Remove any remaining special characters except hyphens
        $clean = preg_replace('/[^a-zA-Z0-9\-]/', '', $clean);
        
        // Remove multiple consecutive hyphens
        $clean = preg_replace('/-+/', '-', $clean);
        
        // Remove leading/trailing hyphens
        $clean = trim($clean, '-');
        
        return strtolower($clean);
    }

    public function load_archive_template($template) {
        if (is_post_type_archive('sardius_media')) {
            $archive_template_id = intval(get_option('sardius_archive_elementor_template_id', 0));
            if ($archive_template_id > 0 && function_exists('do_shortcode')) {
                // Return a simple template that just outputs the Elementor template
                // This lets the theme handle header/footer and Elementor initialization
                $elementor_template = SARDIUS_FEED_PLUGIN_PATH . 'templates/archive-elementor-simple.php';
                if (file_exists($elementor_template)) {
                    return $elementor_template;
                }
            }
            // Don't override theme template - let theme handle page structure
            // Plugin content will be injected via shortcode
        }
        return $template;
    }

    public function prevent_archive_404($preempt, $wp_query) {
        $post_type = isset($wp_query->query['post_type']) ? $wp_query->query['post_type'] : '';
        if ($post_type === 'sardius_media') {
            // Ensure 200 OK for our virtual archive page
            status_header(200);
            $wp_query->is_404 = false;
            return true; // short-circuit core 404 handling
        }
        return $preempt;
    }

    public function maybe_flush_on_slug_change($option, $old_value, $value) {
        if ($option === 'sardius_media_slug' && $old_value !== $value) {
            $this->add_rewrite_rules();
            flush_rewrite_rules();
        }
    }
    
    public function maybe_reschedule_on_interval_change($option, $old_value, $value) {
        if ($option === 'sardius_feed_refresh_interval' && $old_value !== $value) {
            $this->schedule_auto_refresh();
        }
    }

    public function ensure_rewrite_rules() {
        // Flush once per version and slug combo to avoid 404 on archive route
        $marker = get_option('sardius_rewrite_initialized');
        $current = SARDIUS_FEED_VERSION . '|' . $this->get_base_slug();
        if ($marker !== $current) {
            $this->add_rewrite_rules();
            flush_rewrite_rules();
            update_option('sardius_rewrite_initialized', $current);
        }
    }
    
    public function format_duration($duration_ms) {
        $seconds = floor($duration_ms / 1000);
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
    
    public function format_date($date_string) {
        return date_i18n(get_option('date_format'), strtotime($date_string));
    }
    
    public function format_datetime($timestamp) {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
    }
    
    private function add_seo_meta_tags($media_item) {
        $title = $media_item['title'];
        $description = $media_item['searchText'] ?? $title;
        $duration = $this->format_duration($media_item['duration']);
        $air_date = $this->format_date($media_item['airDate']);
        $categories = !empty($media_item['categories']) ? implode(', ', $media_item['categories']) : '';
        
        // Meta title
        echo '<title>' . esc_html($title) . ' - ' . get_bloginfo('name') . '</title>' . "\n";
        
        // Meta description
        echo '<meta name="description" content="' . esc_attr(wp_trim_words($description, 25, '...')) . '" />' . "\n";
        
        // Open Graph tags
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr(wp_trim_words($description, 25, '...')) . '" />' . "\n";
        echo '<meta property="og:type" content="video.other" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($_SERVER['REQUEST_URI']) . '" />' . "\n";
        
        // Add thumbnail if available
        if (!empty($media_item['files']) && !empty($media_item['files'][0]['url'])) {
            echo '<meta property="og:image" content="' . esc_url($media_item['files'][0]['url']) . '" />' . "\n";
        }
        
        // Twitter Card tags
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr(wp_trim_words($description, 25, '...')) . '" />' . "\n";
        
        // Video-specific meta tags
        echo '<meta property="video:duration" content="' . esc_attr($media_item['duration'] / 1000) . '" />' . "\n";
        echo '<meta property="video:release_date" content="' . esc_attr($media_item['airDate']) . '" />' . "\n";
        
        if (!empty($categories)) {
            echo '<meta property="article:section" content="' . esc_attr($categories) . '" />' . "\n";
        }
        
        // Schema.org structured data
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $title,
            'description' => $description,
            'duration' => 'PT' . floor($media_item['duration'] / 60000) . 'M' . floor(($media_item['duration'] % 60000) / 1000) . 'S',
            'uploadDate' => $media_item['airDate'],
            'thumbnailUrl' => !empty($media_item['files'][0]['url']) ? $media_item['files'][0]['url'] : '',
            'contentUrl' => $media_item['media']['url'] ?? '',
            'genre' => $categories
        );
        
        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>' . "\n";
    }
    
    public function add_rewrite_rules() {
        $base_slug = $this->get_base_slug();
        add_rewrite_rule(
            '^' . preg_quote($base_slug, '/') . '/?$',
            'index.php?post_type=sardius_media',
            'top'
        );
        add_rewrite_rule(
            '^' . preg_quote($base_slug, '/') . '/([^/]+)/?$',
            'index.php?media_slug=$matches[1]',
            'top'
        );
    }
    
    private function sanitize_base_slug($slug) {
        $slug = sanitize_title($slug);
        if (empty($slug)) {
            $slug = 'sardius-media';
        }
        return $slug;
    }
    
    public function get_base_slug() {
        $stored = get_option('sardius_media_slug', 'sardius-media');
        return $this->sanitize_base_slug($stored);
    }

    public function build_video_player_html(array $media_item) {
        $account_id = get_option('sardius_account_id', '');
        $pid = $media_item['pid'] ?? '';
        if (!empty($account_id) && !empty($pid)) {
            $src = 'https://players.sardius.media/' . rawurlencode($account_id) . '/primary/asset/' . rawurlencode($pid);
            return '<div style="position: relative; padding-top: 56.25%;"><iframe src="' . esc_url($src) . '" scrolling="no" frameborder="0" style="border: 0; position: absolute; top: 0; left: 0; height: 100%; width: 100%;" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen="true"></iframe></div>';
        }
        // Fallback to direct video if iframe cannot be constructed
        $url = $media_item['media']['url'] ?? '';
        $mime = $media_item['media']['mimeType'] ?? '';
        if (empty($url)) {
            return '<div class="no-video"><span class="dashicons dashicons-video-alt3"></span><p>' . esc_html__('Video not available', 'sardius-feed') . '</p></div>';
        }
        $typeAttr = $mime ? ' type="' . esc_attr($mime) . '"' : '';
        return '<video controls width="100%" height="auto"><source src="' . esc_url($url) . '"' . $typeAttr . '></video>';
    }

    public function get_media_template_html(array $media_item) {
        $template = get_option('sardius_media_template', '');
        if (empty($template)) {
            return '';
        }
        $title = $media_item['title'] ?? '';
        $descriptionText = $media_item['searchText'] ?? '';
        $descriptionHtml = $descriptionText ? ('<div class="media-description"><h3>' . esc_html__('Description', 'sardius-feed') . '</h3><p>' . esc_html($descriptionText) . '</p></div>') : '';
        $categories = !empty($media_item['categories']) ? '<span class="media-categories">' . esc_html(implode(', ', (array)$media_item['categories'])) . '</span>' : '';
        $replacements = array(
            '{title}' => esc_html($title),
            '{air_date}' => esc_html($this->format_date($media_item['airDate'] ?? '')),
            '{duration}' => esc_html($this->format_duration($media_item['duration'] ?? 0)),
            '{categories}' => $categories,
            '{description}' => $descriptionHtml,
            '{video_player}' => $this->build_video_player_html($media_item),
            '{video_url}' => esc_url($media_item['media']['url'] ?? ''),
            '{thumbnail_url}' => esc_url(($media_item['files'][0]['url'] ?? '')),
            '{page_url}' => esc_url($this->get_media_url($media_item)),
            '{back_url}' => esc_url(admin_url('admin.php?page=sardius-feed')),
        );
        $html = strtr($template, $replacements);
        return $html;
    }

    private function is_media_request() {
        $media_slug = get_query_var('media_slug');
        return !empty($media_slug);
    }

    private function is_media_archive_request() {
        return is_post_type_archive('sardius_media');
    }

    public function filter_body_class($classes) {
        if ($this->is_media_request()) {
            $classes[] = 'sardius-media-page';
            if (intval(get_option('sardius_elementor_template_id', 0)) > 0) {
                // Help Hello Elementor and other themes apply Elementor-specific styling
                $classes[] = 'elementor-page';
                $classes[] = 'elementor-template-full-width';
                $classes[] = 'elementor-default';
                $classes[] = 'hello-elementor-default';
                $classes[] = 'wp-theme-hello-elementor';
                $classes[] = 'page-template-elementor_header_footer';
                // Add active kit class to match standard pages (e.g., elementor-kit-6)
                if (class_exists('Elementor\\Plugin')) {
                    try {
                        $kit_id = \Elementor\Plugin::instance()->kits_manager->get_active_id();
                        if ($kit_id) {
                            $classes[] = 'elementor-kit-' . intval($kit_id);
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                // Mirror common page classes and elementor-page-{id} based on the selected template id
                $template_id = intval(get_option('sardius_elementor_template_id', 0));
                if ($template_id > 0) {
                    $classes[] = 'page';
                    $classes[] = 'wp-singular';
                    $classes[] = 'singular';
                    $classes[] = 'page-id-' . $template_id;
                    $classes[] = 'elementor-page-' . $template_id;
                }
            }
        }
        if ($this->is_media_archive_request()) {
            $classes[] = 'sardius-media-archive';
            if (intval(get_option('sardius_archive_elementor_template_id', 0)) > 0) {
                $classes[] = 'elementor-page';
                $classes[] = 'elementor-template-full-width';
            }
        }
        return $classes;
    }

    public function shortcode_media_content() {
        if (!is_array(self::$current_media_item)) {
            return '';
        }
        $custom_template_html = $this->get_media_template_html(self::$current_media_item);
        if (!empty($custom_template_html)) {
            return $custom_template_html;
        }
        // Fallback to default PHP template rendering, but capture its core content only
        return $this->build_default_media_content_html(self::$current_media_item);
    }

    // Archive shortcode for Elementor archive template
    public function shortcode_media_archive() {
        ob_start();
        $plugin = $this;
        include SARDIUS_FEED_PLUGIN_PATH . 'templates/shortcode-media-archive.php';
        return ob_get_clean();
    }

    private function build_default_media_content_html(array $media_item) {
        ob_start();
        $plugin = $this;
        include SARDIUS_FEED_PLUGIN_PATH . 'templates/single-media-content.php';
        return ob_get_clean();
    }
    
    public function add_sitemap_endpoint() {
        add_rewrite_rule(
            '^sardius-sitemap\.xml$',
            'index.php?sardius_sitemap=1',
            'top'
        );
    }
    
    public function generate_sitemap() {
        $feed_data = $this->get_feed_data();
        if (!$feed_data) {
            return '';
        }
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($feed_data['hits'] as $item) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . esc_url($this->get_media_url($item)) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d', strtotime($item['airDate'])) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    

}

// Initialize the plugin
new SardiusFeedPlugin();

// Add a dummy post type to represent the feed items
function sardius_media_post_type() {
    $base_slug = get_option('sardius_media_slug', 'sardius-media');
	register_post_type('sardius_media',
		array(
			'labels'      => array(
				'name'          => __('Sardius Media', 'sardius-feed'),
				'singular_name' => __('Sardius Media', 'sardius-feed'),
			),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => array('slug' => $base_slug),
            'supports'    => array('title', 'editor', 'thumbnail'),
            'show_in_menu'=> false,
		)
	);
}
add_action('init', 'sardius_media_post_type');

// Add query vars
add_filter('query_vars', function($vars) {
    $vars[] = 'media_slug';
    $vars[] = 'sardius_sitemap';
    return $vars;
});

// Handle sitemap requests
add_action('template_redirect', function() {
    if (get_query_var('sardius_sitemap')) {
        $plugin = new SardiusFeedPlugin();
        $sitemap = $plugin->generate_sitemap();
        
        header('Content-Type: application/xml; charset=UTF-8');
        echo $sitemap;
        exit;
    }
}); 