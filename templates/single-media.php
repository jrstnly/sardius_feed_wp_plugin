<?php
if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
    echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->');
} else {
    get_header();
}
?>

<div class="sardius-media-single">
    <div class="container">
        <div class="media-content">
            <div class="media-player">
                <?php echo $plugin->build_video_player_html($media_item); ?>
            </div>

            <h1 class="media-title"><?php echo esc_html($media_item['title']); ?></h1>
            
            <div class="media-meta">
                <span class="media-date">
                    <strong><?php _e('Air Date:', 'sardius-feed'); ?></strong>
                    <?php echo $plugin->format_date($media_item['airDate']); ?>
                </span>
                
                <span class="media-duration">
                    <strong><?php _e('Duration:', 'sardius-feed'); ?></strong>
                    <?php echo $plugin->format_duration($media_item['duration']); ?>
                </span>
                
                <?php if (!empty($media_item['categories'])): ?>
                    <span class="media-categories">
                        <strong><?php _e('Categories:', 'sardius-feed'); ?></strong>
                        <?php echo esc_html(implode(', ', $media_item['categories'])); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($media_item['searchText'])): ?>
                <div class="media-description">
                    <h3><?php _e('Description', 'sardius-feed'); ?></h3>
                    <p><?php echo esc_html($media_item['searchText']); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.sardius-media-single {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.media-header {
    margin-bottom: 30px;
    text-align: center;
}

.media-title {
    font-size: 2.5em;
    margin-bottom: 15px;
    color: #333;
}

.media-meta {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    color: #666;
}

.media-meta span {
    padding: 5px 10px;
    background: #f5f5f5;
    border-radius: 4px;
}

.media-content {
    margin-bottom: 40px;
}

.media-player {
    margin-bottom: 30px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.media-player video {
    width: 100%;
    height: auto;
    display: block;
}

.no-video {
    background: #f5f5f5;
    padding: 60px 20px;
    text-align: center;
    color: #666;
}

.no-video .dashicons {
    font-size: 48px;
    margin-bottom: 10px;
}

.media-description,
.media-downloads {
    margin-bottom: 30px;
}

.media-description h3,
.media-downloads h3 {
    font-size: 1.5em;
    margin-bottom: 15px;
    color: #333;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 5px;
}

.download-links {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.download-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 10px 15px;
    background: #0073aa;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: background 0.3s;
}

.download-link:hover {
    background: #005a87;
    color: white;
}

.media-navigation {
    text-align: center;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.media-navigation .button {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 12px 20px;
    font-size: 16px;
}

@media (max-width: 768px) {
    .sardius-media-single {
        padding: 10px;
    }
    
    .media-title {
        font-size: 1.8em;
    }
    
    .media-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .download-links {
        flex-direction: column;
    }
}
</style>

<?php
if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
    echo do_blocks('<!-- wp:template-part {"slug":"footer"} /-->');
} else {
    get_footer();
}
?> 