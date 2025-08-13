# Sardius Feed WordPress Plugin

A WordPress plugin that pulls media from the Sardius content feed and creates virtual pages for each media item with advanced filtering and management capabilities.

## Features

- **Automatic Feed Integration**: Pulls media data from the Sardius API feed
- **Virtual Pages**: Creates individual pages for each media item with custom URLs
- **Advanced Filtering**: Filter media by category, search terms, and sort by date
- **Admin Management**: Complete admin interface for managing and viewing media
- **Caching System**: Intelligent caching to improve performance
- **Responsive Design**: Mobile-friendly interface
- **Video Player**: Built-in video player with keyboard shortcuts
- **Download Support**: Direct download links for media files
- **SEO Optimized**: SEO-friendly URLs, meta tags, and structured data
- **Sitemap Generation**: Automatic XML sitemap at `/sardius-sitemap.xml`

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