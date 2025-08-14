jQuery(document).ready(function($) {

    // --- Sardius Media Archive Filtering ---

    const filters = {
        type: 'message',
        search: '',
        category: '',
        dateFrom: '',
        dateTo: ''
    };

    const $grid = $('#sardius-media-grid');
    const $filtersContainer = $('#sardius-filters');
    const $resetGroup = $('#reset-filters-group');
    const $seriesInput = $('#sardius-series-filter');
    const $seriesDropdown = $('#series-dropdown');
    const $seriesItems = $('.autocomplete-item');

    let selectedIndex = -1;
    let filteredItems = [];

    function initializeAutocomplete() {
        // Store all series items for filtering
        filteredItems = $seriesItems.toArray();
        
        // Show dropdown on focus
        $seriesInput.on('focus', function() {
            showDropdown();
        });
        
        // Handle input changes for filtering
        $seriesInput.on('input', function() {
            filterDropdown();
        });
        
        // Handle keyboard navigation
        $seriesInput.on('keydown', function(e) {
            handleKeyboardNavigation(e);
        });
        
        // Handle item selection
        $seriesDropdown.on('click', '.autocomplete-item', function() {
            selectItem($(this));
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.autocomplete-container').length) {
                hideDropdown();
            }
        });
    }

    function showDropdown() {
        $seriesDropdown.show();
        filterDropdown();
    }

    function hideDropdown() {
        $seriesDropdown.hide();
        selectedIndex = -1;
        $seriesItems.removeClass('highlighted');
    }

    function filterDropdown() {
        const searchTerm = $seriesInput.val().toLowerCase();
        
        $seriesItems.each(function() {
            const itemText = $(this).text().toLowerCase();
            if (itemText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Show dropdown if there are visible items
        const visibleItems = $seriesItems.filter(':visible');
        if (visibleItems.length > 0) {
            $seriesDropdown.show();
        } else {
            $seriesDropdown.hide();
        }
        
        selectedIndex = -1;
        $seriesItems.removeClass('highlighted');
    }

    function handleKeyboardNavigation(e) {
        const visibleItems = $seriesItems.filter(':visible');
        
        switch(e.keyCode) {
            case 40: // Down arrow
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, visibleItems.length - 1);
                highlightItem(visibleItems.eq(selectedIndex));
                break;
            case 38: // Up arrow
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                if (selectedIndex === -1) {
                    $seriesItems.removeClass('highlighted');
                } else {
                    highlightItem(visibleItems.eq(selectedIndex));
                }
                break;
            case 13: // Enter
                e.preventDefault();
                if (selectedIndex >= 0) {
                    selectItem(visibleItems.eq(selectedIndex));
                }
                break;
            case 27: // Escape
                hideDropdown();
                $seriesInput.blur();
                break;
        }
    }

    function highlightItem($item) {
        $seriesItems.removeClass('highlighted');
        $item.addClass('highlighted');
    }

    function selectItem($item) {
        const value = $item.data('value');
        $seriesInput.val(value);
        hideDropdown();
        filters.category = value;
        checkFiltersActive();
        applyFilters();
    }

    function checkFiltersActive() {
        const hasActiveFilters = filters.search !== '' || 
                                filters.category !== '' || 
                                filters.dateFrom !== '' || 
                                filters.dateTo !== '' ||
                                filters.type !== 'message';
        
        if (hasActiveFilters) {
            $resetGroup.show();
        } else {
            $resetGroup.hide();
        }
    }

    function resetFilters() {
        // Reset filter values
        filters.type = 'message';
        filters.search = '';
        filters.category = '';
        filters.dateFrom = '';
        filters.dateTo = '';
        
        // Reset UI elements
        $('.filter-button').removeClass('active');
        $('.filter-button[data-filter-value="message"]').addClass('active');
        $('#sardius-search').val('');
        $('#sardius-series-filter').val('');
        $('#sardius-date-from').val('');
        $('#sardius-date-to').val('');
        
        // Hide reset button and dropdown
        $resetGroup.hide();
        hideDropdown();
        
        // Apply filters (which will show all items)
        applyFilters();
    }

    function applyFilters() {
        $grid.css('opacity', 0.5); // Dim the grid while loading

        $.ajax({
            url: sardius_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sardius_get_filtered_items',
                nonce: sardius_ajax.nonce,
                filters: filters
            },
            success: function(response) {
                if (response.success) {
                    renderMediaItems(response.data.items);
                } else {
                    $grid.html('<p>Error loading media. Please try again.</p>');
                }
                $grid.css('opacity', 1);
            },
            error: function() {
                $grid.html('<p>Error loading media. Please try again.</p>');
                $grid.css('opacity', 1);
            }
        });
    }

    function renderMediaItems(items) {
        $grid.empty();
        if (items.length === 0) {
            $grid.html('<p>No media items match your criteria.</p>');
            return;
        }

        items.forEach(function(item) {
            const thumbnailUrl = item.thumbnail_url || '';
            const duration = item.duration_formatted || '';
            const mediaUrl = item.url;
            
            let thumbnailHtml = '';
            if (thumbnailUrl) {
                thumbnailHtml = `<img src="${thumbnailUrl}" alt="${item.title}" class="video-thumbnail" onclick="window.location.href='${mediaUrl}'">`;
            } else {
                thumbnailHtml = `<div class="video-thumbnail" style="background-color: #333; display: flex; align-items: center; justify-content: center; color: white;" onclick="window.location.href='${mediaUrl}'"><span style="font-size: 48px;">▶</span></div>`;
            }
            
            const itemHtml = `
                <div class="sardius-media-item" data-id="${item.pid}">
                    <div class="video-player-container">
                        ${thumbnailHtml}
                        <div class="video-duration">${duration}</div>
                    </div>
                    <div class="sardius-media-item-info">
                        <h3><a href="${item.url}">${item.title}</a></h3>
                        <div class="media-date">${item.air_date_formatted}</div>
                        ${item.series ? `<p><strong>Series:</strong> ${item.series}</p>` : ''}
                        ${item.bible_reference ? `<p><strong>Text:</strong> ${item.bible_reference}</p>` : ''}
                    </div>
                </div>
            `;
            $grid.append(itemHtml);
        });
    }

    // Event Handlers
    $filtersContainer.on('click', '.filter-button', function() {
        const $button = $(this);
        $button.addClass('active').siblings().removeClass('active');
        filters.type = $button.data('filter-value');
        checkFiltersActive();
        applyFilters();
    });

    $('#sardius-search').on('keyup', debounce(function() {
        filters.search = $(this).val();
        checkFiltersActive();
        applyFilters();
    }, 500));

    $('#sardius-series-filter').on('change', function() {
        filters.category = $(this).val();
        checkFiltersActive();
        applyFilters();
    });

    $('#sardius-date-from, #sardius-date-to').on('change', function() {
        filters.dateFrom = $('#sardius-date-from').val();
        filters.dateTo = $('#sardius-date-to').val();
        if (filters.dateFrom && filters.dateTo) {
            checkFiltersActive();
            applyFilters();
        }
    });

    $('#reset-filters').on('click', function() {
        resetFilters();
    });

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
    
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

    // Initialize autocomplete
    initializeAutocomplete();
}); 