# Sardius Feed Plugin - Shortcode Documentation

This document provides comprehensive documentation for all available shortcodes in the Sardius Feed WordPress Plugin.

## Overview

The Sardius Feed Plugin provides 6 powerful shortcodes for displaying media content anywhere on your WordPress site. These shortcodes offer flexible options for displaying single media items, lists, players, search interfaces, and complete archives.

## Available Shortcodes

### 1. `[sardius_media_content]` - Single Media Content Display

**Purpose**: Displays the content of a single media item when used within a virtual media page.

**Usage**: 
```php
[sardius_media_content]
```

**Description**: This shortcode is automatically available on virtual media pages and displays the full content of the current media item, including the video player, metadata, and description.

**Parameters**: None (automatically uses the current media item context)

**Example Output**: 
- Video player with controls
- Media title and description
- Air date, duration, and categories
- Download links (if available)

**Note**: This shortcode only works when placed on a virtual media page (URLs like `/sardius-media/{pid}-{title}/`).

---

### 2. `[sardius_media_archive]` - Complete Media Archive

**Purpose**: Displays a complete media archive with search, filtering, and pagination capabilities.

**Usage**:
```php
[sardius_media_archive]
```

**Description**: This is the most comprehensive shortcode, providing a full-featured media archive interface that includes:
- Search functionality
- Series/category filtering
- Date range filtering
- Sorting options
- Pagination with AJAX loading
- Responsive grid layout

**Parameters**: None (uses global plugin settings)

**Features**:
- **Search**: Find videos by title or description
- **Series Filtering**: Browse by content series (e.g., "Weekly Messages")
- **Date Filtering**: Filter by today, this week, month, quarter, year, or custom range
- **Sorting**: Newest first, oldest first, title A-Z, duration (longest/shortest)
- **Pagination**: Configurable items per page with smooth AJAX transitions
- **Responsive Design**: Works on all devices

**Example Output**: A complete media browsing interface with search controls and a grid of media items.

---

### 3. `[sardius_media]` - Single Media Item Display

**Purpose**: Displays a specific media item by its ID.

**Usage**:
```php
[sardius_media id="56b5f3F79CB6BB6_1095452938"]
```

**Parameters**:
- `id` (required): The media item's unique identifier
- `show_player` (optional): Whether to show the video player (default: "true")
- `show_meta` (optional): Whether to show metadata (default: "true")
- `width` (optional): Player width (default: "100%")
- `height` (optional): Player height (default: "auto")

**Examples**:
```php
// Basic usage
[sardius_media id="56b5f3F79CB6BB6_1095452938"]

// Custom player size
[sardius_media id="56b5f3F79CB6BB6_1095452938" width="800px" height="450px"]

// Player only (no metadata)
[sardius_media id="56b5f3F79CB6BB6_1095452938" show_meta="false"]

// Metadata only (no player)
[sardius_media id="56b5f3F79CB6BB6_1095452938" show_player="false"]
```

**Error Handling**: Returns an error message if the media ID is not provided or if the media item is not found.

---

### 4. `[sardius_media_list]` - Filtered Media List

**Purpose**: Displays a filtered list of media items with customizable display options.

**Usage**:
```php
[sardius_media_list category="Weekly Messages" limit="5" sort="desc"]
```

**Parameters**:
- `category` (optional): Filter by series/category name
- `limit` (optional): Maximum number of items to display (default: "10")
- `sort` (optional): Sort order - "asc", "desc", "title", "duration", "duration-asc" (default: "desc")
- `show_thumbnails` (optional): Whether to show video thumbnails (default: "true")
- `show_duration` (optional): Whether to show video duration (default: "true")
- `show_date` (optional): Whether to show air date (default: "true")

**Examples**:
```php
// Show latest 5 weekly messages
[sardius_media_list category="Weekly Messages" limit="5" sort="desc"]

// Show oldest 10 items with title sorting
[sardius_media_list limit="10" sort="title"]

// Show longest videos first
[sardius_media_list limit="8" sort="duration"]

// Minimal display (no thumbnails or dates)
[sardius_media_list show_thumbnails="false" show_date="false"]
```

**Sort Options**:
- `asc`: Oldest first (by air date)
- `desc`: Newest first (by air date)
- `title`: Alphabetical by title
- `duration`: Longest videos first
- `duration-asc`: Shortest videos first

---

### 5. `[sardius_media_player]` - Video Player Only

**Purpose**: Displays just the video player for a specific media item.

**Usage**:
```php
[sardius_media_player id="56b5f3F79CB6BB6_1095452938"]
```

**Parameters**:
- `id` (required): The media item's unique identifier
- `width` (optional): Player width (default: "100%")
- `height` (optional): Player height (default: "400px")

**Examples**:
```php
// Basic usage
[sardius_media_player id="56b5f3F79CB6BB6_1095452938"]

// Custom size
[sardius_media_player id="56b5f3F79CB6BB6_1095452938" width="800px" height="450px"]

// Responsive width with fixed height
[sardius_media_player id="56b5f3F79CB6BB6_1095452938" height="300px"]
```

**Features**:
- Responsive video player
- Keyboard shortcuts (spacebar, arrow keys)
- Fullscreen support
- Automatic quality selection

---

### 6. `[sardius_media_search]` - Search and Filter Interface

**Purpose**: Displays a search and filter interface for visitors to find media content.

**Usage**:
```php
[sardius_media_search]
```

**Parameters**:
- `show_search` (optional): Whether to show search box (default: "true")
- `show_series` (optional): Whether to show series filter (default: "true")
- `show_date` (optional): Whether to show date filter (default: "true")
- `show_sort` (optional): Whether to show sort options (default: "true")
- `results_per_page` (optional): Number of results per page (default: "12")

**Examples**:
```php
// Full search interface
[sardius_media_search]

// Search only (no filters)
[sardius_media_search show_series="false" show_date="false" show_sort="false"]

// Custom results per page
[sardius_media_search results_per_page="6"]

// Minimal interface
[sardius_media_search show_search="true" show_series="false" show_date="false" show_sort="false"]
```

**Features**:
- Real-time search functionality
- Series/category dropdown
- Date range picker
- Sort options dropdown
- AJAX-powered results
- Responsive design

## Shortcode Combinations and Best Practices

### Creating a Media Hub Page

Combine multiple shortcodes to create a comprehensive media hub:

```php
<!-- Page Header -->
<h1>Media Library</h1>

<!-- Search Interface -->
[sardius_media_search]

<!-- Featured Media -->
<h2>Latest Messages</h2>
[sardius_media_list category="Weekly Messages" limit="3" sort="desc"]

<!-- Complete Archive -->
<h2>All Media</h2>
[sardius_media_archive]
```

### Sidebar Widgets

Use shortcodes in sidebar widgets for featured content:

```php
<!-- Sidebar: Latest Video -->
<h3>Latest Video</h3>
[sardius_media_list limit="1" sort="desc"]

<!-- Sidebar: Popular Series -->
<h3>Weekly Messages</h3>
[sardius_media_list category="Weekly Messages" limit="5" sort="desc"]
```

### Custom Page Templates

Integrate shortcodes into custom page templates:

```php
<?php
/*
Template Name: Media Archive
*/

get_header(); ?>

<div class="media-archive-page">
    <div class="search-section">
        [sardius_media_search]
    </div>
    
    <div class="archive-section">
        [sardius_media_archive]
    </div>
</div>

<?php get_footer(); ?>
```

## Configuration and Customization

### Global Settings

Shortcodes use these global plugin settings (configured in Admin > Sardius Feed):

- **Frontend Items Per Page**: Default pagination for archive shortcodes (6, 12, 18, 24, or 36)
- **Maximum Items**: Total items to fetch from API (100-10,000)
- **API Settings**: Account ID and Feed ID for data source

### CSS Customization

Each shortcode has dedicated CSS files for styling:

- `shortcode-media.css` - Single media display
- `shortcode-media-list.css` - Media lists
- `shortcode-media-player.css` - Video players
- `shortcode-media-search.css` - Search interface
- `shortcode-media-archive.css` - Archive display

### JavaScript Features

Shortcodes include JavaScript for enhanced functionality:

- AJAX pagination (no page reloads)
- Real-time search and filtering
- Smooth loading transitions
- Responsive interactions

## Error Handling

All shortcodes include error handling for common issues:

- **Missing Media ID**: Returns error message for required ID parameters
- **No Feed Data**: Returns error when API data is unavailable
- **Invalid Parameters**: Gracefully handles invalid parameter values
- **Empty Results**: Shows appropriate "no results" messages

## Performance Considerations

### Caching

- All shortcodes use the plugin's 1-hour cache
- No additional database queries per shortcode
- Efficient data filtering and pagination

### Loading Optimization

- CSS and JS files are loaded only when needed
- AJAX pagination reduces initial page load
- Responsive images for thumbnails

### Best Practices

1. **Limit Results**: Use the `limit` parameter to avoid loading too many items
2. **Use Pagination**: Let the archive shortcode handle large datasets
3. **Cache Management**: The plugin automatically manages caching
4. **Responsive Design**: All shortcodes are mobile-friendly

## Troubleshooting

### Common Issues

1. **Shortcode Not Displaying**:
   - Check if the plugin is activated
   - Verify API settings are configured
   - Check for JavaScript errors in browser console

2. **No Results Showing**:
   - Refresh the feed data in admin
   - Check API connectivity
   - Verify filter parameters

3. **Pagination Not Working**:
   - Ensure JavaScript is enabled
   - Check for theme conflicts
   - Verify AJAX endpoints are accessible

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Version Compatibility

These shortcodes are compatible with:
- WordPress 5.0+
- PHP 7.4+
- Modern browsers (Chrome, Firefox, Safari, Edge)

## Support

For technical support or feature requests, please refer to the main plugin documentation or contact the plugin developer.

---

*Last updated: Version 1.2.0*
