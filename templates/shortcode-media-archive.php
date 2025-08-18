<?php
/**
 * Template for the [sardius_media_archive] shortcode
 *
 * @package Sardius_Feed_Plugin
 */

// Get feed data and series for dropdown
$feed_data = $plugin->get_feed_data();
$items = $feed_data['hits'] ?? array();

// Get pagination settings for initial load
$items_per_page = intval(get_option('sardius_frontend_items_per_page', 12));

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

// Create nonce for AJAX requests
$frontend_nonce = wp_create_nonce('sardius_frontend_nonce');
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
            <a href="https://grace.live" target="_blank" rel="noopener noreferrer" class="watch-now-button"><?php _e('WATCH NOW', 'sardius-feed'); ?></a>
        </div>

        <div class="filter-group">
            <h3><?php _e('SEARCH:', 'sardius-feed'); ?></h3>
            <div class="search-container">
                <input type="text" id="sardius-search" placeholder="<?php _e('All Messages...', 'sardius-feed'); ?>">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" fill="currentColor"/>
                </svg>
                <button type="button" id="clear-search" class="clear-search-button" style="display: none;" aria-label="Clear search">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="filter-group">
            <h3><?php _e('SERIES:', 'sardius-feed'); ?></h3>
            <div class="autocomplete-container">
                <input type="text" id="sardius-series-filter" placeholder="<?php _e('Select Series', 'sardius-feed'); ?>" autocomplete="off">
                <button type="button" id="clear-series" class="clear-series-button" style="display: none;" aria-label="Clear series filter">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                    </svg>
                </button>
                <div class="autocomplete-dropdown" id="series-dropdown" style="display: none;">
                    <?php foreach ($all_series as $series) : ?>
                        <div class="autocomplete-item" data-value="<?php echo esc_attr($series); ?>"><?php echo esc_html($series); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="filter-group">
            <h3><?php _e('DATE RANGE:', 'sardius-feed'); ?></h3>
            <div class="date-range-container">
                <div class="date-range-input">
                    <input type="date" id="sardius-date-from" placeholder="From">
                    <span class="date-separator">-</span>
                    <input type="date" id="sardius-date-to" placeholder="To">
                    <button type="button" id="clear-date-range" class="clear-date-button" style="display: none;" aria-label="Clear date range filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="filter-group" id="reset-filters-group" style="display: none;">
            <button id="reset-filters" class="reset-filters-button">
                <?php _e('Clear Filters', 'sardius-feed'); ?>
            </button>
        </div>
    </aside>

    <div id="sardius-media-grid">
        <!-- Content will be loaded via JavaScript -->
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p><?php _e('Loading media items...', 'sardius-feed'); ?></p>
        </div>
    </div>
</div>

<div class="sardius-frontend-pagination" style="display: none;">
    <!-- Pagination controls will be loaded via JavaScript -->
</div>

<script>
// Store pagination data for AJAX requests
window.sardiusPaginationData = {
    currentPage: <?php echo isset($_GET['media_page']) ? intval($_GET['media_page']) : 1; ?>,
    totalPages: 0,
    itemsPerPage: <?php echo $items_per_page; ?>,
    nonce: '<?php echo $frontend_nonce; ?>',
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>'
};
</script>
