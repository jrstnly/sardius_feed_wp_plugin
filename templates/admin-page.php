<?php
if (!defined('ABSPATH')) {
    exit;
}

$plugin = new SardiusFeedPlugin();
$feed_data = $plugin->get_feed_data();
$total_items = $feed_data ? count($feed_data['hits']) : 0;

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
        if (isset($_POST['sardius_archive_elementor_template_id'])) {
            update_option('sardius_archive_elementor_template_id', intval($_POST['sardius_archive_elementor_template_id']));
        }
        if (isset($_POST['sardius_feed_refresh_interval'])) {
            update_option('sardius_feed_refresh_interval', intval($_POST['sardius_feed_refresh_interval']));
        }
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'sardius-feed') . '</p></div>';
    }
    
    $account_id = get_option('sardius_account_id', '');
    $feed_id = get_option('sardius_feed_id', '');
    $media_slug = get_option('sardius_media_slug', 'sardius-media');
    $plugin_instance = new SardiusFeedPlugin();
    $custom_template = get_option('sardius_media_template', '');
    $elementor_template_id = intval(get_option('sardius_elementor_template_id', 0));
    $archive_elementor_template_id = intval(get_option('sardius_archive_elementor_template_id', 0));
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
                <tr>
                    <th scope="row">
                        <label for="sardius_archive_elementor_template_id"><?php _e('Archive Elementor Template ID', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="sardius_archive_elementor_template_id" name="sardius_archive_elementor_template_id" value="<?php echo esc_attr($archive_elementor_template_id); ?>" class="small-text" min="0" />
                        <p class="description"><?php _e('Optional: Use an Elementor saved template for the list/archive page. Inside that template, place the shortcode [sardius_media_archive] where the archive grid should render.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save Settings', 'sardius-feed')); ?>
        </form>
    </div>
    
    <div class="sardius-feed-settings">
        <h2><?php _e('Auto-Refresh Configuration', 'sardius-feed'); ?></h2>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sardius_feed_refresh_interval"><?php _e('Refresh Interval', 'sardius-feed'); ?></label>
                    </th>
                    <td>
                        <?php 
                        $refresh_interval = get_option('sardius_feed_refresh_interval', 3600);
                        $intervals = array(
                            900 => __('15 minutes', 'sardius-feed'),
                            1800 => __('30 minutes', 'sardius-feed'),
                            3600 => __('1 hour', 'sardius-feed'),
                            7200 => __('2 hours', 'sardius-feed'),
                            14400 => __('4 hours', 'sardius-feed'),
                            28800 => __('8 hours', 'sardius-feed'),
                            86400 => __('24 hours', 'sardius-feed')
                        );
                        ?>
                        <select id="sardius_feed_refresh_interval" name="sardius_feed_refresh_interval">
                            <?php foreach ($intervals as $seconds => $label): ?>
                                <option value="<?php echo esc_attr($seconds); ?>" <?php selected($refresh_interval, $seconds); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('How often should the feed be automatically refreshed in the background?', 'sardius-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Auto-Refresh Status', 'sardius-feed'); ?></th>
                    <td>
                        <?php 
                        $last_auto_refresh = $plugin->get_last_auto_refresh_time();
                        $next_scheduled = wp_next_scheduled('sardius_feed_auto_refresh');
                        ?>
                        <p>
                            <strong><?php _e('Last Auto-Refresh:', 'sardius-feed'); ?></strong> 
                            <?php if ($last_auto_refresh > 0): ?>
                                <?php echo get_date_from_gmt(date('Y-m-d H:i:s', $last_auto_refresh), 'Y-m-d H:i:s'); ?>
                                <small>(<?php echo wp_timezone_string(); ?>)</small>
                            <?php else: ?>
                                <em><?php _e('No auto-refresh has been performed yet.', 'sardius-feed'); ?></em>
                            <?php endif; ?>
                        </p>
                        <p>
                            <strong><?php _e('Next Scheduled Refresh:', 'sardius-feed'); ?></strong> 
                            <?php if ($next_scheduled): ?>
                                <?php echo get_date_from_gmt(date('Y-m-d H:i:s', $next_scheduled), 'Y-m-d H:i:s'); ?>
                                <small>(<?php echo wp_timezone_string(); ?>)</small>
                            <?php else: ?>
                                <em><?php _e('Not scheduled.', 'sardius-feed'); ?></em>
                            <?php endif; ?>
                        </p>
                        <p class="description"><?php _e('The feed will be automatically updated in the background using WordPress cron jobs.', 'sardius-feed'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save Auto-Refresh Settings', 'sardius-feed')); ?>
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
            <?php printf(__('Showing %d items', 'sardius-feed'), $total_items); ?>
        </div>
        
        <div id="media-grid" class="media-grid">
            <?php if ($feed_data): ?>
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
                                 <p><strong><?php _e('Text:', 'sardius-feed'); ?></strong> <?php echo esc_html($bible_reference); ?></p>
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
    </div>
    <?php endif; ?>
</div>

<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="loading-spinner">
        <span class="dashicons dashicons-update"></span>
        <p><?php _e('Loading...', 'sardius-feed'); ?></p>
    </div>
</div> 