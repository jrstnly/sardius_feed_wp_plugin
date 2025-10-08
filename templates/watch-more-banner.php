<?php
/**
 * Template for the Watch More Banner
 *
 * @package Sardius_Feed_Plugin
 */

// Get banner settings
$banner_image = get_option('sardius_archive_banner_image', '');
$banner_link = get_option('sardius_archive_banner_link', '');

if (empty($banner_image) || empty($banner_link)) {
    return; // Don't show banner if no image or link is configured
}
?>

<div class="sardius-watch-more-banner">
    <div class="banner-content">
        
        <div class="banner-info">
            <h2 class="banner-title">Watch More</h2>
            <h3 class="banner-subtitle">Sermon Archive · Full Services · Feature Stories</h3>
        </div>
        
        <div class="banner-action">
            <a href="<?php echo esc_url($banner_link); ?>" class="watch-more-button" target="_blank" rel="noopener">
                <span class="button-text">Watch More</span>
                <span class="button-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 5v14l11-7z" fill="currentColor"/>
                    </svg>
                </span>
            </a>
        </div>
    </div>
</div>
