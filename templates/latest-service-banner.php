<?php
/**
 * Template for the Latest Full Service Banner
 *
 * @package Sardius_Feed_Plugin
 */

// Get the latest service
$latest_service = $plugin->get_latest_service();

if (!$latest_service) {
    return; // Don't show banner if no service is available
}

$service_url = $plugin->get_media_url($latest_service);
$service_title = $latest_service['title'];
$service_date = $plugin->format_date($latest_service['airDate']);
$service_duration = $plugin->format_duration($latest_service['duration']);
$service_thumbnail = !empty($latest_service['files'][0]['url']) ? $latest_service['files'][0]['url'] : '';
?>

<div class="sardius-latest-service-banner">
    <div class="banner-content">
        <div class="banner-thumbnail">
            <a href="<?php echo esc_url($service_url); ?>">
                <?php if ($service_thumbnail): ?>
                    <img src="<?php echo esc_url($service_thumbnail); ?>" 
                         alt="<?php echo esc_attr($service_title); ?>" 
                         loading="lazy">
                <?php else: ?>
                    <div class="no-thumbnail">
                        <span class="dashicons dashicons-video-alt3"></span>
                    </div>
                <?php endif; ?>
                <div class="service-duration"><?php echo esc_html($service_duration); ?></div>
            </a>
        </div>
        
        <div class="banner-info">
            <h2 class="banner-title">Latest Full Service</h2>
            <h3 class="service-title">
                <a href="<?php echo esc_url($service_url); ?>">
                    <?php echo esc_html($service_title); ?>
                </a>
            </h3>
            <div class="service-meta">
                <span class="service-date"><?php echo esc_html($service_date); ?></span>
                <!--<span class="service-duration-text"><?php echo esc_html($service_duration); ?></span>-->
            </div>
        </div>
        
        <div class="banner-action">
            <a href="<?php echo esc_url($service_url); ?>" class="watch-now-button-large">
                <span class="button-text">Watch Now</span>
                <span class="button-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 5v14l11-7z" fill="currentColor"/>
                    </svg>
                </span>
            </a>
        </div>
    </div>
</div>

