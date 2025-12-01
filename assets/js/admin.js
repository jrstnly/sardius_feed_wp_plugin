jQuery(document).ready(function($) {
    
    // Refresh feed functionality
    $('#refresh-feed').on('click', function() {
        var $button = $(this);
        var $spinner = $button.find('.dashicons');
        
        // Show loading state
        $button.prop('disabled', true);
        $spinner.addClass('spinning');
        
        // Show loading overlay
        $('#loading-overlay').show();
        
        $.ajax({
            url: sardius_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sardius_refresh_feed',
                nonce: sardius_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    // Reload the page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('Failed to refresh feed. Please try again.', 'error');
            },
            complete: function() {
                $button.prop('disabled', false);
                $spinner.removeClass('spinning');
                $('#loading-overlay').hide();
            }
        });
    });
    
    // Minimal helpers left: URL generator respects configured base slug
    function getMediaUrl(mediaItem) {
        var baseSlug = (window.sardius_ajax && sardius_ajax.base_slug) ? sardius_ajax.base_slug : 'sardius-media';
        var cleanTitle = String(mediaItem.title || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        return (window.sardius_ajax && sardius_ajax.site_origin ? sardius_ajax.site_origin : window.location.origin)
            + '/' + baseSlug + '/' + mediaItem.pid + '-' + cleanTitle + '/';
    }
    
    // Show notice messages
    function showNotice(message, type) {
        var noticeClass = 'notice-' + type;
        var $notice = $('<div class="notice ' + noticeClass + '">' + message + '</div>');
        
        // Remove existing notices
        $('.notice').remove();
        
        // Add new notice
        $('.wrap h1').after($notice);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Add spinning animation class
    $('<style>')
        .prop('type', 'text/css')
        .html('.spinning { animation: spin 1s linear infinite; }')
        .appendTo('head');
    
    // Banner image upload functionality
    $('#upload_banner_image').on('click', function(e) {
        e.preventDefault();
        
        console.log('Button clicked!');
        
        // Wait for wp.media to be available
        if (typeof wp === 'undefined' || !wp.media) {
            console.log('wp.media not available, waiting...');
            setTimeout(function() {
                $('#upload_banner_image').click();
            }, 1000);
            return;
        }
        
        console.log('Opening media uploader...');
        
        var imageUploader = wp.media({
            title: 'Select Banner Image',
            button: {
                text: 'Use this image'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        imageUploader.on('select', function() {
            var attachment = imageUploader.state().get('selection').first().toJSON();
            console.log('Selected image:', attachment);
            // Archive banner functionality removed
        });
        
        imageUploader.open();
    });
}); 