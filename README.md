# Sardius Feed WordPress Plugin

A WordPress plugin that pulls media from the Sardius content feed and creates virtual pages for each media item with advanced filtering and management capabilities.

## Features

- **Smart Feed Integration**: Pulls media data from the Sardius API feed with pagination support
- **Virtual Pages**: Creates individual pages for each media item with custom URLs
- **Advanced Filtering**: Filter media by category, search terms, and sort by date
- **Admin Management**: Complete admin interface for managing and viewing media with pagination
- **Intelligent Caching**: 1-hour cache with automatic refresh on user visits
- **Responsive Design**: Mobile-friendly interface
- **Video Player**: Built-in video player with keyboard shortcuts
- **Download Support**: Direct download links for media files
- **SEO Optimized**: SEO-friendly URLs, meta tags, and structured data
- **Sitemap Generation**: Automatic XML sitemap at `/sardius-sitemap.xml`
- **Pagination Support**: Handles large datasets with configurable pagination settings for both admin and frontend

## Installation

1. **Upload the Plugin**:
   - Upload the `sardius-feed-plugin` folder to your `/wp-content/plugins/` directory
   - Or zip the folder and upload via WordPress admin

2. **Activate the Plugin**:
   - Go to **Plugins** > **Installed Plugins**
   - Find "Sardius Feed Plugin" and click **Activate**

3. **Configure API Settings**:
   - Go to **Sardius Feed** in the admin menu
   - Enter your **Account ID** and **Feed ID**
   - Click **Save Settings**

4. **Configure Permalinks**:
   - Go to **Settings** > **Permalinks**
   - Click **Save Changes** to flush rewrite rules

## Usage

### Admin Interface

1. **Access the Plugin**:
   - Navigate to **Sardius Feed** in the WordPress admin menu

2. **Refresh Feed Data**:
   - Click the **Refresh Feed** button to fetch the latest data from the Sardius API
   - Data is automatically cached for 1 hour

3. **Advanced Filtering and Search**:
   - **Search**: Find media by title or description
   - **Series/Category**: Filter by content series (e.g., "Weekly Messages")
   - **Date Range**: Filter by today, this week, month, quarter, year, or custom range
   - **Sort Options**: Newest first, oldest first, title A-Z, duration (longest/shortest)
   - **Clear All**: Reset all filters with one click

4. **View Media Items**:
   - Each media item shows thumbnail, title, date, duration, and categories
   - Click **View Page** to see the virtual page
   - Click **Watch** to view the video directly
   - Navigate through pages using the pagination controls at the bottom

5. **Pagination Settings**:
   - **Admin Items Per Page**: Configure how many items to show per page in the admin interface (10, 25, 50, or 100)
   - **Frontend Items Per Page**: Configure how many items to show per page on the public archive page (6, 12, 18, 24, or 36)
   - **Maximum Items to Keep**: Set the maximum number of items to fetch from the API (100-10,000)

### Frontend Search Interface

Use the `[sardius_media_search]` shortcode to add a search interface for visitors:

```php
[sardius_media_search show_search="true" show_series="true" show_date="true" show_sort="true"]
```

This provides visitors with:
- **Search functionality**: Find videos by title
- **Series filtering**: Browse by content series
- **Date filtering**: Filter by time periods
- **Sorting options**: Organize results by date or title
- **Responsive design**: Works on all devices

### Frontend Archive Pagination

The archive page now includes pagination to improve performance and user experience:

1. **Configurable Items Per Page**:
   - Go to **Sardius Feed** > **Pagination Settings**
   - Set **Frontend Items Per Page** (6, 12, 18, 24, or 36 items)
   - Default is 12 items per page

2. **Pagination Features**:
   - **JavaScript-only pagination**: All pagination handled via AJAX for smooth user experience
   - **No page reloads**: Instant page transitions with loading indicators
   - **URL updates**: Page numbers are reflected in the URL for bookmarking and sharing
   - **Filter integration**: Pagination works seamlessly with search and filtering
   - **Responsive design**: Pagination controls adapt to mobile devices
   - **Loading states**: Visual feedback during page transitions

3. **Performance Benefits**:
   - Faster initial page load (no more loading 1000+ items at once)
   - Reduced memory usage
   - Better user experience on slower connections
   - Improved SEO with faster page load times
   - Smooth loading transitions with visual feedback

### Virtual Pages

Each media item gets its own SEO-friendly virtual page accessible at:
```
https://yoursite.com/sardius-media/{pid}-{clean-title}/
```

For example:
```
https://yoursite.com/sardius-media/1095452938-justified-by-faith-galatians-21116/
```

The URLs are automatically generated from the PID and cleaned title, with:
- Spaces replaced with hyphens
- Special characters removed
- Lowercase formatting
- Proper URL structure for SEO

The virtual pages include:
- Full video player
- Media metadata (date, duration, categories)
- Download links for different quality versions
- Responsive design

### API Integration

The plugin integrates with the Sardius API endpoint:
```
https://api.sardius.media/feeds/{accountId}/{feedId}/public
```

You can configure your Account ID and Feed ID in the plugin settings.

## File Structure

```
sardius-feed-plugin/
├── sardius-feed-plugin.php      # Main plugin file
├── templates/
│   ├── admin-page.php           # Admin interface template
│   └── single-media.php         # Virtual page template
├── assets/
│   ├── css/
│   │   └── admin.css           # Admin styles
│   └── js/
│       ├── admin.js            # Admin JavaScript
│       └── frontend.js         # Frontend JavaScript
└── README.md                   # This file
```

## Configuration

### Cache Duration

The plugin caches feed data for 1 hour by default. To change this, modify the `$cache_duration` property in the main plugin file:

```php
private $cache_duration = 3600; // 1 hour in seconds
```

### API Configuration

The Sardius API URL is dynamically generated from your settings:

```php
$api_url = 'https://api.sardius.media/feeds/' . $account_id . '/' . $feed_id . '/public';
```

You can configure these values in the admin interface under **Sardius Feed** > **API Configuration**.

### Pagination Settings

The plugin now supports pagination for handling large datasets:

1. **Maximum Items to Keep**: Configure how many media items to fetch and store from the API (default: 1000)
2. **Admin Items Per Page**: Set how many items to display per page in the admin interface (default: 25)

These settings can be configured in the admin interface under **Sardius Feed** > **Pagination Settings**.

**Note**: The Sardius API returns 25 items per page by default. The plugin automatically fetches all available pages to build a complete dataset, then applies your configured limits.

### Caching Behavior

The plugin uses intelligent caching to improve performance:

- **Cache Duration**: Data is cached for 1 hour by default
- **Automatic Refresh**: When the cache expires, fresh data is automatically fetched on the next user visit
- **Manual Refresh**: Use the "Refresh Feed" button in the admin interface to manually update the cache
- **No Background Jobs**: The plugin doesn't use WordPress cron jobs, making it simpler and more reliable

## SEO Features

### SEO-Friendly URLs
The plugin generates SEO-friendly URLs in the format:
```
https://yoursite.com/sardius-media/{pid}-{clean-title}/
```

### Meta Tags
Each virtual page includes comprehensive meta tags:
- **Title**: Media title + site name
- **Description**: Truncated media description
- **Open Graph**: For social media sharing
- **Twitter Cards**: For Twitter sharing
- **Video-specific**: Duration, release date, categories

### Structured Data
Schema.org VideoObject structured data is automatically added to help search engines understand the content.

### XML Sitemap
An automatic XML sitemap is generated at:
```
https://yoursite.com/sardius-sitemap.xml
```

## API Response Structure

The plugin expects the Sardius API to return JSON in this format:

```json
{
  "total": 881,
  "hits": [
    {
      "id": "56b5f3F79CB6BB6_1095452938",
      "title": "\"Justified By Faith\" | Galatians 2:11-16",
      "airDate": "2025-06-22T22:09:04.000Z",
      "duration": 1973000,
      "categories": ["Weekly Messages"],
      "media": {
        "url": "https://storage.sardius.media/...",
        "mimeType": "application/x-mpegURL"
      },
      "files": [
        {
          "url": "https://storage.sardius.media/...",
          "types": ["thumbnail"]
        }
      ],
      "bitrates": [
        {
          "url": "https://storage.sardius.media/...",
          "width": 1920,
          "height": 1080
        }
      ]
    }
  ]
}
```

## Keyboard Shortcuts

When viewing a media page, you can use these keyboard shortcuts:

- **Spacebar**: Play/Pause video
- **Left Arrow**: Rewind 10 seconds
- **Right Arrow**: Forward 10 seconds
- **Up Arrow**: Increase volume
- **Down Arrow**: Decrease volume

## Troubleshooting

### Virtual Pages Not Working

1. **Flush Rewrite Rules**:
   - Go to **Settings** > **Permalinks**
   - Click **Save Changes**

2. **Check Plugin Activation**:
   - Ensure the plugin is activated
   - Check for any PHP errors in the error log

### Feed Not Loading

1. **Check API Connectivity**:
   - Verify the API URL is accessible
   - Check for network connectivity issues

2. **Clear Cache**:
   - Click **Refresh Feed** in the admin interface
   - Or manually delete the cache options from the database

### Performance Issues

1. **Reduce Cache Duration**:
   - Lower the cache duration for more frequent updates
   - Or increase it for better performance

2. **Optimize Images**:
   - Ensure thumbnails are properly sized
   - Consider using a CDN for media files

## Development

### Adding Custom Filters

To add new filter types, modify the `filter_items()` method in the main plugin file:

```php
private function filter_items($items, $filters) {
    $filtered = $items;
    
    // Add your custom filter logic here
    if (!empty($filters['your_custom_filter'])) {
        $filtered = array_filter($filtered, function($item) use ($filters) {
            // Your filter logic
            return $your_condition;
        });
    }
    
    return array_values($filtered);
}
```

### Customizing Templates

The plugin uses template files in the `templates/` directory. You can customize these files to match your theme's design.

### Adding New Features

The plugin is built with extensibility in mind. You can:

- Add new AJAX endpoints for additional functionality
- Extend the filtering system
- Add custom media player features
- Integrate with other WordPress plugins

## Support

For support and feature requests, please contact the plugin developer.

## Changelog

### Version 1.1.0
- Added pagination support for large datasets
- Implemented recursive API fetching to handle all available pages
- Added configurable maximum items limit (default: 1000)
- Added admin pagination with configurable items per page (default: 25)
- Added pagination controls to admin interface
- Improved performance for large media libraries
- Added pagination settings section to admin interface
- Removed automatic refresh functionality for simpler, more reliable operation
- Data now refreshes automatically on user visits when cache expires (1 hour)

### Version 1.0.0
- Initial release
- Basic feed integration
- Virtual page creation
- Admin management interface
- Filtering and search capabilities
- Video player with keyboard shortcuts
- Download functionality
- Responsive design

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- Built for WordPress
- Integrates with Sardius Media API
- Uses WordPress coding standards 