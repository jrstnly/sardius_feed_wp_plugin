<?php
if (!defined('ABSPATH')) {
    exit;
}

$plugin = new SardiusFeedPlugin();
$feed_data = $plugin->get_feed_data();
$total_items = $feed_data ? count($feed_data['hits']) : 0;

// Get pagination parameters
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$items_per_page = intval(get_option('sardius_admin_items_per_page', 25));
$paginated_data = $plugin->get_paginated_feed_data($current_page, $items_per_page);

// Get unique categories
$categories = array();
if ($feed_data) {
    foreach ($feed_data['hits'] as $hit) {
        if (isset($hit['categories']) && is_array($hit['categories'])) {
            foreach ($hit['categories'] as $category) {
                if (!in_array($category, $categories)) {
                    $categories[] = $category;
                }
            }
        }
    }
}
?>

<div class="wrap">
    <h1><?php _e('Sardius Feed Management', 'sardius-feed'); ?></h1>
    
    <?php
    // Handle settings form submission
    if (isset($_POST['submit'])) {
        update_option('sardius_account_id', sanitize_text_field($_POST['sardius_account_id']));
        update_option('sardius_feed_id', sanitize_text_field($_POST['sardius_feed_id']));
        update_option('sardius_services_feed_id', sanitize_text_field($_POST['sardius_services_feed_id']));
        if (isset($_POST['sardius_media_slug'])) {
            update_option('sardius_media_slug', sanitize_text_field($_POST['sardius_media_slug']));
            if (function_exists('flush_rewrite_rules')) {
                flush_rewrite_rules();
            }
        }
        if (isset($_POST['sardius_media_template'])) {
            update_option('sardius_media_template', wp_kses_post($_POST['sardius_media_template']));
        }
        if (isset($_POST['sardius_elementor_template_id'])) {
            update_option('sardius_elementor_template_id', intval($_POST['sardius_elementor_template_id']));
        }

        if (isset($_POST['sardius_max_items'])) {
            update_option('sardius_max_items', intval($_POST['sardius_max_items']));
        }
        if (isset($_POST['sardius_admin_items_per_page'])) {
            update_option('sardius_admin_items_per_page', intval($_POST['sardius_admin_items_per_page']));
        }
        if (isset($_POST['sardius_archive_banner_image'])) {
            update_option('sardius_archive_banner_image', esc_url_raw($_POST['sardius_archive_banner_image']));
        }
        if (isset($_POST['sardius_archive_banner_link'])) {
            update_option('sardius_archive_banner_link', esc_url_raw($_POST['sardius_archive_banner_link']));
        }
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'sardius-feed') . '</p></div>';
    }
    
    $account_id = get_option('sardius_account_id', '');
    $feed_id = get_option('sardius_feed_id', '');
    $services_feed_id = get_option('sardius_services_feed_id', '');
    $media_slug = get_option('sardius_media_slug', 'sardius-media');
    $plugin_instance = new SardiusFeedPlugin();
    $custom_template = get_option('sardius_media_template', '');
    $elementor_template_id = intval(get_option('sardius_elementor_template_id', 0));
    $archive_elementor_template_id = intval(get_option('sardius_archive_elementor_template_id', 0));
    $archive_page_title = get_option('sardius_archive_page_title', 'Media');
    ?>
    
    <div class="sardius-feed-settings">
        <h2><?php _e('API Configuration', 'sardius-feed'); ?></h2>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sardius_account_id"><?php _e('Account ID', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="sardius_account_id" name="sardius_account_id" 
                               value="<?php echo esc_attr($account_id); ?>" class="regular-text" />
                        <p class="description"><?php _e('Enter your Sardius Media Account ID.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sardius_feed_id"><?php _e('Feed ID', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="sardius_feed_id" name="sardius_feed_id" 
                               value="<?php echo esc_attr($feed_id); ?>" class="regular-text" />
                        <p class="description"><?php _e('Enter your Sardius Media Feed ID.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sardius_services_feed_id"><?php _e('Services Feed ID', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="sardius_services_feed_id" name="sardius_services_feed_id" 
                               value="<?php echo esc_attr($services_feed_id); ?>" class="regular-text" />
                        <p class="description"><?php _e('Enter your Sardius Media Services Feed ID for full service videos.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sardius_media_slug"><?php _e('Media Base Slug', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="sardius_media_slug" name="sardius_media_slug" 
                               value="<?php echo esc_attr($media_slug); ?>" class="regular-text" />
                        <p class="description"><?php _e('Customize the base URL for media pages (e.g., \'media\' makes URLs like /media/{id-title}). After changing, you may need to save permalinks to refresh rewrite rules.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sardius_elementor_template_id"><?php _e('Elementor Template ID', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="sardius_elementor_template_id" name="sardius_elementor_template_id" value="<?php echo esc_attr($elementor_template_id); ?>" class="small-text" min="0" />
                        <p class="description"><?php _e('Optional: Use an Elementor saved template (ID from Templates > Saved Templates). Inside that template, place the shortcode [sardius_media_content] where the content should render.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save Settings', 'sardius-feed')); ?>
        </form>
    </div>
    

    
    <div class="sardius-feed-settings">
        <h2><?php _e('Pagination Settings', 'sardius-feed'); ?></h2>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sardius_max_items"><?php _e('Maximum Items to Keep', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="sardius_max_items" name="sardius_max_items" 
                               value="<?php echo esc_attr(get_option('sardius_max_items', 1000)); ?>" 
                               class="small-text" min="100" max="10000" />
                        <p class="description"><?php _e('Maximum number of media items to fetch and keep from the API. Higher values may impact performance.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sardius_admin_items_per_page"><?php _e('Admin Items Per Page', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <?php 
                        $admin_items_per_page = get_option('sardius_admin_items_per_page', 25);
                        $options = array(
                            10 => __('10 items', 'sardius-feed'),
                            25 => __('25 items', 'sardius-feed'),
                            50 => __('50 items', 'sardius-feed'),
                            100 => __('100 items', 'sardius-feed')
                        );
                        ?>
                        <select id="sardius_admin_items_per_page" name="sardius_admin_items_per_page">
                            <?php foreach ($options as $count => $label): ?>
                                <option value="<?php echo esc_attr($count); ?>" <?php selected($admin_items_per_page, $count); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Number of items to display per page in the admin interface.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save Pagination Settings', 'sardius-feed')); ?>
        </form>
    </div>
    
    <div class="sardius-feed-settings">
        <h2><?php _e('Archive Banner Settings', 'sardius-feed'); ?></h2>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sardius_archive_banner_image"><?php _e('Banner Image', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="sardius_archive_banner_image" name="sardius_archive_banner_image" 
                               value="<?php echo esc_attr(get_option('sardius_archive_banner_image', '')); ?>" 
                               class="regular-text" />
                        <button type="button" id="upload_banner_image" class="button"><?php _e('Choose Image', 'sardius-feed'); ?></button>
                        <div id="banner_image_preview" style="margin-top: 10px;">
                            <?php 
                            $banner_image = get_option('sardius_archive_banner_image', '');
                            if ($banner_image) {
                                echo '<img src="' . esc_url($banner_image) . '" style="max-width: 200px; height: auto;" />';
                            }
                            ?>
                        </div>
                        <p class="description"><?php _e('Upload or select an image for the archive banner.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sardius_archive_banner_link"><?php _e('Banner Link URL', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="sardius_archive_banner_link" name="sardius_archive_banner_link" 
                               value="<?php echo esc_attr(get_option('sardius_archive_banner_link', '')); ?>" 
                               class="regular-text" />
                        <p class="description"><?php _e('Enter the URL where the banner should link to (optional).', 'sardius-feed'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save Banner Settings', 'sardius-feed')); ?>
        </form>
    </div>
    
    <?php if (empty($account_id) || empty($feed_id)): ?>
        <div class="notice notice-warning">
            <p><?php _e('Please configure your API settings above before using the feed.', 'sardius-feed'); ?></p>
        </div>
    <?php else: ?>
    
    <div class="sardius-feed-header">
        <div class="sardius-feed-stats">
            <span class="stat-item">
                <strong><?php echo $total_items; ?></strong>
                <?php _e('Total Items', 'sardius-feed'); ?>
            </span>
            <span class="stat-item">
                <strong><?php echo count($categories); ?></strong>
                <?php _e('Categories', 'sardius-feed'); ?>
            </span>
            <span class="stat-item">
                <strong><?php echo $plugin->format_datetime(get_option('sardius_feed_last_update', 0)); ?></strong>
                <?php _e('Last Updated', 'sardius-feed'); ?>
            </span>
        </div>
        
        <button id="refresh-feed" class="button button-primary">
            <span class="dashicons dashicons-update"></span>
            <?php _e('Refresh Feed', 'sardius-feed'); ?>
        </button>
    </div>
    
    
    
    <div class="sardius-feed-results">
        <div id="results-count" class="results-count">
            <?php printf(__('Showing %d of %d items (Page %d of %d)', 'sardius-feed'), 
                count($paginated_data['items']), 
                $paginated_data['total'], 
                $paginated_data['current_page'], 
                $paginated_data['total_pages']
            ); ?>
        </div>
        
        <div id="media-grid" class="media-grid">
            <?php if ($paginated_data['items']): ?>
                <?php foreach ($paginated_data['items'] as $item): ?>
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
                                 <a href="<?php echo $plugin->get_media_url($item); ?>" target="_blank">
                                     <?php echo esc_html($item['title']); ?>
                                 </a>
                             </h3>
                             
                             <div class="media-date">
                                 <?php echo $plugin->format_date($item['airDate']); ?>
                             </div>
                             
                             <?php 
                             $series = $item['series'] ?? '';
                             $bible_reference = !empty($item['metadata']['bibleReference']) ? implode(', ', $item['metadata']['bibleReference']) : '';
                             
                             if ($series) : ?>
                                 <p><strong><?php _e('Series:', 'sardius-feed'); ?></strong> <?php echo esc_html($series); ?></p>
                             <?php endif; 
                             if ($bible_reference) : ?>
                                 <p><strong><?php _e('Scripture:', 'sardius-feed'); ?></strong> <?php echo esc_html($bible_reference); ?></p>
                             <?php endif; ?>
                            
                                                         <div class="media-actions">
                                 <a href="<?php echo $plugin->get_media_url($item); ?>" 
                                    class="button button-small" target="_blank">
                                     <?php _e('View Page', 'sardius-feed'); ?>
                                 </a>
                             </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-items">
                    <p><?php _e('No media items found. Try refreshing the feed.', 'sardius-feed'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($paginated_data['total_pages'] > 1): ?>
            <div class="sardius-pagination">
                <?php
                $current_url = add_query_arg(array(), remove_query_arg('paged'));
                
                // Previous page
                if ($paginated_data['current_page'] > 1): ?>
                    <a href="<?php echo esc_url(add_query_arg('paged', $paginated_data['current_page'] - 1, $current_url)); ?>" 
                       class="button">&laquo; <?php _e('Previous', 'sardius-feed'); ?></a>
                <?php endif; ?>
                
                <?php
                // Page numbers
                $start_page = max(1, $paginated_data['current_page'] - 2);
                $end_page = min($paginated_data['total_pages'], $paginated_data['current_page'] + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i == $paginated_data['current_page']): ?>
                        <span class="current-page"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo esc_url(add_query_arg('paged', $i, $current_url)); ?>" 
                           class="button"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php
                // Next page
                if ($paginated_data['current_page'] < $paginated_data['total_pages']): ?>
                    <a href="<?php echo esc_url(add_query_arg('paged', $paginated_data['current_page'] + 1, $current_url)); ?>" 
                       class="button"><?php _e('Next', 'sardius-feed'); ?> &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="loading-spinner">
        <div class="css-spinner"></div>
        <p><?php _e('Loading...', 'sardius-feed'); ?></p>
    </div>
</div> 