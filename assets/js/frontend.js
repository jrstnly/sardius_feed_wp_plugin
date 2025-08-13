jQuery(document).ready(function($) {
    
    // Initialize video players with better controls
    $('.sardius-media-single video').each(function() {
        var $video = $(this);
        
        // Add custom controls if needed
        $video.on('loadedmetadata', function() {
            // Video is ready
            console.log('Video loaded:', $video[0].duration);
        });
        
        $video.on('error', function() {
            console.error('Video error:', $video[0].error);
        });
    });
    
    // Handle download links
    $('.download-link').on('click', function(e) {
        var $link = $(this);
        var url = $link.attr('href');
        
        // Show download progress
        $link.addClass('downloading');
        $link.html('<span class="dashicons dashicons-download"></span> Downloading...');
        
        // Reset after a delay
        setTimeout(function() {
            $link.removeClass('downloading');
            $link.html('<span class="dashicons dashicons-download"></span> Download');
        }, 3000);
    });
    
    // Add smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });
    
    // Add loading states for external links
    $('a[target="_blank"]').on('click', function() {
        var $link = $(this);
        var originalText = $link.text();
        
        $link.text('Opening...');
        
        setTimeout(function() {
            $link.text(originalText);
        }, 2000);
    });
    
    // Add responsive video handling
    function handleVideoResponsiveness() {
        $('.media-player video').each(function() {
            var $video = $(this);
            var $container = $video.closest('.media-player');
            
            // Maintain aspect ratio
            var aspectRatio = 16 / 9; // Default aspect ratio
            var containerWidth = $container.width();
            var containerHeight = containerWidth / aspectRatio;
            
            $video.css({
                'width': '100%',
                'height': 'auto',
                'max-height': containerHeight + 'px'
            });
        });
    }
    
    // Handle window resize
    $(window).on('resize', function() {
        handleVideoResponsiveness();
    });
    
    // Initial call
    handleVideoResponsiveness();
    
    // Add keyboard shortcuts for video player
    $(document).on('keydown', function(e) {
        var $video = $('.sardius-media-single video:focus, .sardius-media-single video:hover');
        if ($video.length) {
            switch(e.keyCode) {
                case 32: // Spacebar - play/pause
                    e.preventDefault();
                    if ($video[0].paused) {
                        $video[0].play();
                    } else {
                        $video[0].pause();
                    }
                    break;
                case 37: // Left arrow - rewind 10s
                    e.preventDefault();
                    $video[0].currentTime = Math.max(0, $video[0].currentTime - 10);
                    break;
                case 39: // Right arrow - forward 10s
                    e.preventDefault();
                    $video[0].currentTime = Math.min($video[0].duration, $video[0].currentTime + 10);
                    break;
                case 38: // Up arrow - volume up
                    e.preventDefault();
                    $video[0].volume = Math.min(1, $video[0].volume + 0.1);
                    break;
                case 40: // Down arrow - volume down
                    e.preventDefault();
                    $video[0].volume = Math.max(0, $video[0].volume - 0.1);
                    break;
            }
        }
    });
    
    // Add video progress indicator
    $('.sardius-media-single video').on('timeupdate', function() {
        var $video = $(this);
        var progress = ($video[0].currentTime / $video[0].duration) * 100;
        
        // You can add a custom progress bar here if needed
        // console.log('Video progress:', progress + '%');
    });
    
    // Handle video errors gracefully
    $('.sardius-media-single video').on('error', function() {
        var $video = $(this);
        var $container = $video.closest('.media-player');
        
        $container.html(
            '<div class="no-video">' +
            '<span class="dashicons dashicons-warning"></span>' +
            '<p>Video could not be loaded. Please try again later.</p>' +
            '</div>'
        );
    });
    
    // Add lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Add accessibility improvements
    $('.media-item').on('keydown', function(e) {
        if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
            e.preventDefault();
            $(this).find('.media-title a').click();
        }
    });
    
    // Add focus styles for better accessibility
    $('.media-item').attr('tabindex', '0');
    
    // Add ARIA labels
    $('.media-item').each(function() {
        var $item = $(this);
        var title = $item.find('.media-title').text();
        $item.attr('aria-label', 'Media item: ' + title);
    });
}); 