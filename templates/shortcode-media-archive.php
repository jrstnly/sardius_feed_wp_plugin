<?php
/**
 * Template for the [sardius_media_archive] shortcode
 *
 * @package Sardius_Feed_Plugin
 */

// Get feed data and series for dropdown
$feed_data = $plugin->get_feed_data();
$items = $feed_data['hits'] ?? array();

// Get all unique series for the filter dropdown
$all_series = [];
if (!empty($items)) {
    foreach ($items as $item) {
        if (!empty($item['series']) && !in_array($item['series'], $all_series)) {
            $all_series[] = $item['series'];
        }
    }
    sort($all_series);
}
?>

<div class="sardius-media-archive-container">
    <aside id="sardius-filters">
        <!--div class="filter-group">
            <h3><?php _e('FILTER BY:', 'sardius-feed'); ?></h3>
            <button class="filter-button active" data-filter-type="type" data-filter-value="message"><?php _e('MESSAGE ONLY', 'sardius-feed'); ?></button>
            <button class="filter-button" data-filter-type="type" data-filter-value="full_service"><?php _e('FULL SERVICE', 'sardius-feed'); ?></button>
            <button class="filter-button" data-filter-type="type" data-filter-value="spanish"><?php _e('SPANISH', 'sardius-feed'); ?></button>
        </div-->

        <div class="filter-group">
            <h3><?php _e('WATCH GRACE LIVE:', 'sardius-feed'); ?></h3>
            <p>SUNDAYS 9:00A & 10:40A CT</p>
            <a href="#" class="watch-now-button"><?php _e('WATCH NOW', 'sardius-feed'); ?></a>
        </div>

        <div class="filter-group">
            <h3><?php _e('SEARCH:', 'sardius-feed'); ?></h3>
            <div class="search-container">
                <input type="text" id="sardius-search" placeholder="<?php _e('All Messages...', 'sardius-feed'); ?>">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" fill="currentColor"/>
                </svg>
            </div>
        </div>

        <div class="filter-group">
            <h3><?php _e('SERIES:', 'sardius-feed'); ?></h3>
            <div class="autocomplete-container">
                <input type="text" id="sardius-series-filter" placeholder="<?php _e('Select Series', 'sardius-feed'); ?>" autocomplete="off">
                <div class="autocomplete-dropdown" id="series-dropdown" style="display: none;">
                    <?php foreach ($all_series as $series) : ?>
                        <div class="autocomplete-item" data-value="<?php echo esc_attr($series); ?>"><?php echo esc_html($series); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="filter-group">
            <h3><?php _e('DATE RANGE:', 'sardius-feed'); ?></h3>
            <div class="date-range-fields">
                <input type="date" id="sardius-date-from" placeholder="From">
                <span>To</span>
                <input type="date" id="sardius-date-to" placeholder="To">
            </div>
        </div>

        <div class="filter-group" id="reset-filters-group" style="display: none;">
            <button id="reset-filters" class="reset-filters-button">
                <?php _e('Clear Filters', 'sardius-feed'); ?>
            </button>
        </div>
    </aside>

    <div id="sardius-media-grid">
        <?php if (!empty($items)) : ?>
            <?php foreach ($items as $item) : ?>
                <div class="sardius-media-item" data-id="<?php echo esc_attr($item['pid']); ?>">
                    <div class="video-player-container">
                        <?php 
                        $thumbnail_url = !empty($item['files'][0]['url']) ? $item['files'][0]['url'] : '';
                        $duration = $plugin->format_duration($item['duration'] ?? 0);
                        $media_url = $plugin->get_media_url($item);
                        
                        if ($thumbnail_url) : ?>
                            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($item['title']); ?>" class="video-thumbnail" onclick="window.location.href='<?php echo esc_url($media_url); ?>'">
                        <?php else : ?>
                            <div class="video-thumbnail" style="background-color: #333; display: flex; align-items: center; justify-content: center; color: white;" onclick="window.location.href='<?php echo esc_url($media_url); ?>'">
                                <span style="font-size: 48px;">▶</span>
                            </div>
                        <?php endif; ?>
                        <div class="video-duration"><?php echo esc_html($duration); ?></div>
                    </div>
                    <div class="sardius-media-item-info">
                        <h3><a href="<?php echo esc_url($media_url); ?>"><?php echo esc_html($item['title']); ?></a></h3>
                        <div class="media-date"><?php echo esc_html($plugin->format_date($item['airDate'])); ?></div>
                        <?php 
                        $series = $item['series'] ?? '';
                        $bible_reference = !empty($item['metadata']['bibleReference']) ? implode(', ', $item['metadata']['bibleReference']) : '';
                        
                        if ($series) : ?>
                            <p><strong><?php _e('Series:', 'sardius-feed'); ?></strong> <?php echo esc_html($series); ?></p>
                        <?php endif; 
                        if ($bible_reference) : ?>
                            <p><strong><?php _e('Text:', 'sardius-feed'); ?></strong> <?php echo esc_html($bible_reference); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p><?php _e('No media items found.', 'sardius-feed'); ?></p>
        <?php endif; ?>
    </div>
</div>
