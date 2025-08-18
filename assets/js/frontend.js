jQuery(document).ready(function($) {

    // Store the initial page parameter if it exists
    const initialUrlParams = new URLSearchParams(window.location.search);
    const initialPage = initialUrlParams.get('media_page');
    if (initialPage) {
        window.sardiusInitialPage = initialPage;
    }

    // Mark initial load at the very beginning
    window.sardiusIsInitialLoad = true;

    // --- Sardius Media Archive Filtering ---

    const filters = {
        type: 'message',
        search: '',
        category: '',
        speaker: '',
        dateFrom: '',
        dateTo: ''
    };

    // URL parameter management functions
    function updateURLParameters() {
        // Don't update URL during initial load
        if (window.sardiusIsInitialLoad) {
            return;
        }
        
        const url = new URL(window.location);
        
        // Update or remove filter parameters
        if (filters.type !== 'message') {
            url.searchParams.set('type', filters.type);
        } else {
            url.searchParams.delete('type');
        }
        
        if (filters.search) {
            url.searchParams.set('search', filters.search);
        } else {
            url.searchParams.delete('search');
        }
        
        if (filters.category) {
            url.searchParams.set('series', filters.category);
        } else {
            url.searchParams.delete('series');
        }
        
        if (filters.speaker) {
            url.searchParams.set('speaker', filters.speaker);
        } else {
            url.searchParams.delete('speaker');
        }
        
        if (filters.dateFrom) {
            url.searchParams.set('dateFrom', filters.dateFrom);
        } else {
            url.searchParams.delete('dateFrom');
        }
        
        if (filters.dateTo) {
            url.searchParams.set('dateTo', filters.dateTo);
        } else {
            url.searchParams.delete('dateTo');
        }
        
        // Remove page parameter when filters are applied (reset to page 1)
        url.searchParams.delete('media_page');
        
        console.log('New URL will be:', url.href);
        // Update URL without page reload
        window.history.pushState({filters: filters}, '', url);
    }

    function loadFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Load filter values from URL parameters
        const type = urlParams.get('type');
        if (type) {
            filters.type = type;
            $(`.filter-button[data-filter-value="${type}"]`).addClass('active').siblings().removeClass('active');
        }
        
        const search = urlParams.get('search');
        if (search) {
            filters.search = search;
            $('#sardius-search').val(search);
        }
        
        const series = urlParams.get('series');
        if (series) {
            filters.category = series;
            $('#sardius-series-filter').val(series);
        }
        
        const speaker = urlParams.get('speaker');
        if (speaker) {
            filters.speaker = speaker;
            $('#sardius-speaker-filter').val(speaker);
        }
        
        const dateFrom = urlParams.get('dateFrom');
        if (dateFrom) {
            filters.dateFrom = dateFrom;
            $('#sardius-date-from').val(dateFrom);
        }
        
        const dateTo = urlParams.get('dateTo');
        if (dateTo) {
            filters.dateTo = dateTo;
            $('#sardius-date-to').val(dateTo);
        }
        
        // Check if any filters are active
        checkFiltersActive();
        
        // Store filters globally
        window.sardiusCurrentFilters = filters;
        
        // Update clear series button visibility
        updateClearSeriesButton();
        
        // Update clear speaker button visibility
        updateClearSpeakerButton();
        
        // Update clear date button visibility
        updateClearDateButton();
        
        // Update clear search button visibility
        updateClearSearchButton();
        
        // Return true if any filters were loaded from URL
        return !!(type || search || series || speaker || dateFrom || dateTo);
    }

    const $grid = $('#sardius-media-grid');
    const $filtersContainer = $('#sardius-filters');
    const $resetGroup = $('#reset-filters-group');
    const $seriesInput = $('#sardius-series-filter');
    const $seriesDropdown = $('#series-dropdown');
    const $seriesItems = $('#series-dropdown .autocomplete-item');
    const $clearSeriesButton = $('#clear-series');
    const $speakerInput = $('#sardius-speaker-filter');
    const $speakerDropdown = $('#speaker-dropdown');
    const $speakerItems = $('#speaker-dropdown .autocomplete-item');
    const $clearSpeakerButton = $('#clear-speaker');
    const $clearDateButton = $('#clear-date-range');
    const $clearSearchButton = $('#clear-search');

    let seriesSelectedIndex = -1;
    let speakerSelectedIndex = -1;
    let seriesFilteredItems = [];
    let speakerFilteredItems = [];

    function initializeAutocomplete() {
        // Initialize series autocomplete
        initializeSeriesAutocomplete();
        
        // Initialize speaker autocomplete
        initializeSpeakerAutocomplete();
    }
    
    function initializeSeriesAutocomplete() {
        // Store all series items for filtering
        seriesFilteredItems = $seriesItems.toArray();
        
        // Show dropdown on focus
        $seriesInput.on('focus', function() {
            showSeriesDropdown();
        });
        
        // Handle input changes for filtering
        $seriesInput.on('input', function() {
            filterSeriesDropdown();
        });
        
        // Handle keyboard navigation
        $seriesInput.on('keydown', function(e) {
            handleSeriesKeyboardNavigation(e);
        });
        
        // Handle item selection
        $seriesDropdown.on('click', '.autocomplete-item', function() {
            selectSeriesItem($(this));
        });
    }
    
    function initializeSpeakerAutocomplete() {
        // Store all speaker items for filtering
        speakerFilteredItems = $speakerItems.toArray();
        
        // Show dropdown on focus
        $speakerInput.on('focus', function() {
            showSpeakerDropdown();
        });
        
        // Handle input changes for filtering
        $speakerInput.on('input', function() {
            filterSpeakerDropdown();
        });
        
        // Handle keyboard navigation
        $speakerInput.on('keydown', function(e) {
            handleSpeakerKeyboardNavigation(e);
        });
        
        // Handle item selection
        $speakerDropdown.on('click', '.autocomplete-item', function() {
            selectSpeakerItem($(this));
        });
    }
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.autocomplete-container').length) {
            hideSeriesDropdown();
            hideSpeakerDropdown();
        }
    });

    function showSeriesDropdown() {
        $seriesDropdown.show();
        filterSeriesDropdown();
    }

    function hideSeriesDropdown() {
        $seriesDropdown.hide();
        seriesSelectedIndex = -1;
        $seriesItems.removeClass('highlighted');
    }

    function filterSeriesDropdown() {
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
        
        seriesSelectedIndex = -1;
        $seriesItems.removeClass('highlighted');
    }
    
    function showSpeakerDropdown() {
        $speakerDropdown.show();
        filterSpeakerDropdown();
    }

    function hideSpeakerDropdown() {
        $speakerDropdown.hide();
        speakerSelectedIndex = -1;
        $speakerItems.removeClass('highlighted');
    }

    function filterSpeakerDropdown() {
        const searchTerm = $speakerInput.val().toLowerCase();
        
        $speakerItems.each(function() {
            const itemText = $(this).text().toLowerCase();
            if (itemText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Show dropdown if there are visible items
        const visibleItems = $speakerItems.filter(':visible');
        if (visibleItems.length > 0) {
            $speakerDropdown.show();
        } else {
            $speakerDropdown.hide();
        }
        
        speakerSelectedIndex = -1;
        $speakerItems.removeClass('highlighted');
    }

    function handleSeriesKeyboardNavigation(e) {
        const visibleItems = $seriesItems.filter(':visible');
        
        switch(e.keyCode) {
            case 40: // Down arrow
                e.preventDefault();
                seriesSelectedIndex = Math.min(seriesSelectedIndex + 1, visibleItems.length - 1);
                highlightSeriesItem(visibleItems.eq(seriesSelectedIndex));
                break;
            case 38: // Up arrow
                e.preventDefault();
                seriesSelectedIndex = Math.max(seriesSelectedIndex - 1, -1);
                if (seriesSelectedIndex === -1) {
                    $seriesItems.removeClass('highlighted');
                } else {
                    highlightSeriesItem(visibleItems.eq(seriesSelectedIndex));
                }
                break;
            case 13: // Enter
                e.preventDefault();
                if (seriesSelectedIndex >= 0) {
                    selectSeriesItem(visibleItems.eq(seriesSelectedIndex));
                }
                break;
            case 27: // Escape
                hideSeriesDropdown();
                $seriesInput.blur();
                break;
        }
    }
    
    function handleSpeakerKeyboardNavigation(e) {
        const visibleItems = $speakerItems.filter(':visible');
        
        switch(e.keyCode) {
            case 40: // Down arrow
                e.preventDefault();
                speakerSelectedIndex = Math.min(speakerSelectedIndex + 1, visibleItems.length - 1);
                highlightSpeakerItem(visibleItems.eq(speakerSelectedIndex));
                break;
            case 38: // Up arrow
                e.preventDefault();
                speakerSelectedIndex = Math.max(speakerSelectedIndex - 1, -1);
                if (speakerSelectedIndex === -1) {
                    $speakerItems.removeClass('highlighted');
                } else {
                    highlightSpeakerItem(visibleItems.eq(speakerSelectedIndex));
                }
                break;
            case 13: // Enter
                e.preventDefault();
                if (speakerSelectedIndex >= 0) {
                    selectSpeakerItem(visibleItems.eq(speakerSelectedIndex));
                }
                break;
            case 27: // Escape
                hideSpeakerDropdown();
                $speakerInput.blur();
                break;
        }
    }

    function highlightSeriesItem($item) {
        $seriesItems.removeClass('highlighted');
        $item.addClass('highlighted');
    }
    
    function highlightSpeakerItem($item) {
        $speakerItems.removeClass('highlighted');
        $item.addClass('highlighted');
    }

    function selectSeriesItem($item) {
        const value = $item.data('value');
        $seriesInput.val(value);
        hideSeriesDropdown();
        filters.category = value;
        checkFiltersActive();
        updateURLParameters();
        applyFilters();
    }
    
    function selectSpeakerItem($item) {
        const value = $item.data('value');
        $speakerInput.val(value);
        hideSpeakerDropdown();
        filters.speaker = value;
        checkFiltersActive();
        updateURLParameters();
        applyFilters();
    }

    function checkFiltersActive() {
        const hasActiveFilters = filters.search !== '' || 
                                filters.category !== '' || 
                                filters.speaker !== '' || 
                                filters.dateFrom !== '' || 
                                filters.dateTo !== '' ||
                                filters.type !== 'message';
        
        if (hasActiveFilters) {
            $resetGroup.show();
        } else {
            $resetGroup.hide();
        }
        
        // Update clear series button visibility
        updateClearSeriesButton();
        
        // Update clear speaker button visibility
        updateClearSpeakerButton();
        
        // Update clear date button visibility
        updateClearDateButton();
        
        // Update clear search button visibility
        updateClearSearchButton();
    }
    
    function updateClearSeriesButton() {
        if (filters.category !== '') {
            $clearSeriesButton.show();
            $seriesInput.addClass('has-clear-button');
        } else {
            $clearSeriesButton.hide();
            $seriesInput.removeClass('has-clear-button');
        }
    }
    
    function updateClearSpeakerButton() {
        if (filters.speaker !== '') {
            $clearSpeakerButton.show();
            $speakerInput.addClass('has-clear-button');
        } else {
            $clearSpeakerButton.hide();
            $speakerInput.removeClass('has-clear-button');
        }
    }
    
    function updateClearDateButton() {
        if (filters.dateFrom !== '' || filters.dateTo !== '') {
            $clearDateButton.show();
        } else {
            $clearDateButton.hide();
        }
    }
    
    function updateClearSearchButton() {
        const searchValue = $('#sardius-search').val();
        if (searchValue !== '') {
            $clearSearchButton.show();
            $('#sardius-search').addClass('has-clear-button');
        } else {
            $clearSearchButton.hide();
            $('#sardius-search').removeClass('has-clear-button');
        }
    }

    function resetFilters() {
        // Reset filter values
        filters.type = 'message';
        filters.search = '';
        filters.category = '';
        filters.speaker = '';
        filters.dateFrom = '';
        filters.dateTo = '';
        
        // Update global filters
        window.sardiusCurrentFilters = filters;
        
        // Reset UI elements
        $('.filter-button').removeClass('active');
        $('.filter-button[data-filter-value="message"]').addClass('active');
        $('#sardius-search').val('');
        $('#sardius-series-filter').val('');
        $('#sardius-speaker-filter').val('');
        $('#sardius-date-from').val('');
        $('#sardius-date-to').val('');
        
        // Hide reset button and dropdown
        $resetGroup.hide();
        hideSeriesDropdown();
        hideSpeakerDropdown();
        
        // Update clear button visibility
        updateClearSeriesButton();
        updateClearSpeakerButton();
        updateClearDateButton();
        updateClearSearchButton();
        
        // Update URL parameters
        updateURLParameters();
        
        // Apply filters (which will show all items)
        applyFilters();
    }

    // Global loadPage function for pagination
    let loadPage = null;
    let paginationData = null;
    
    // Function to scroll pagination to center the active page
    function scrollToActivePage() {
        const $paginationNumbers = $('.pagination-numbers');
        const $currentPage = $('.current-page');
        
        if ($currentPage.length && $paginationNumbers.length) {
            const containerWidth = $paginationNumbers.width();
            const currentPageOffset = $currentPage.position().left;
            const currentPageWidth = $currentPage.outerWidth();
            const scrollLeft = currentPageOffset - (containerWidth / 2) + (currentPageWidth / 2);
            
            $paginationNumbers.scrollLeft(scrollLeft);
        }
    }

    // Define loadPage function immediately if pagination data is available
    if (typeof window.sardiusPaginationData !== 'undefined') {
        paginationData = window.sardiusPaginationData;
        
        // Function to load a specific page (no URL updates)
        loadPage = function(page, filters = {}) {
            // Show loading state within the grid container
            $grid.html('<div class="loading-spinner"><div class="spinner"></div><p>Loading media items...</p></div>');
            
            $.ajax({
                url: paginationData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sardius_get_frontend_paginated_items',
                    nonce: paginationData.nonce,
                    page: page,
                    items_per_page: paginationData.itemsPerPage,
                    filters: filters
                },
                success: function(response) {
                    if (response.success) {
                        // Update grid content
                        $grid.html(response.data.items_html);
                        
                        // Update pagination
                        const $pagination = $('.sardius-frontend-pagination');
                        if (response.data.pagination_html) {
                            $pagination.html(response.data.pagination_html).show();
                            // Scroll to center the active page after a brief delay to ensure DOM is updated
                            setTimeout(scrollToActivePage, 100);
                        } else {
                            $pagination.hide();
                        }
                        
                        // Update pagination data
                        paginationData.currentPage = response.data.pagination.current_page;
                        paginationData.totalPages = response.data.pagination.total_pages;
                        
                        // Scroll to top of grid smoothly only if not initial page load
                        if (!window.sardiusIsInitialLoad) {
                            $('html, body').animate({
                                scrollTop: $grid.offset().top - 100
                            }, 500);
                        } else {
                            // Mark initial load as complete after first load
                            window.sardiusIsInitialLoad = false;
                        }
                    } else {
                        // Show error message within the grid
                        $grid.html('<p>Error loading page. Please try again.</p>');
                    }
                },
                error: function() {
                    // Show error message within the grid
                    $grid.html('<p>Error loading page. Please try again.</p>');
                }
            });
        };
        
        // Function for user pagination navigation (includes URL updates)
        window.navigateToPage = function(page, filters = {}) {
            // Load the page content
            loadPage(page, filters);
            
            // Don't update URL during initial load
            if (window.sardiusIsInitialLoad) {
                return;
            }
            
            // Update URL for user navigation
            const url = new URL(window.location);
            if (page > 1) {
                url.searchParams.set('media_page', page);
            } else {
                url.searchParams.delete('media_page');
            }
            // Preserve filter parameters
            if (filters.type !== 'message') {
                url.searchParams.set('type', filters.type);
            }
            if (filters.search) {
                url.searchParams.set('search', filters.search);
            }
            if (filters.category) {
                url.searchParams.set('series', filters.category);
            }
            if (filters.speaker) {
                url.searchParams.set('speaker', filters.speaker);
            }
            if (filters.dateFrom) {
                url.searchParams.set('dateFrom', filters.dateFrom);
            }
            if (filters.dateTo) {
                url.searchParams.set('dateTo', filters.dateTo);
            }
            window.history.pushState({page: page, filters: filters}, '', url);
        };
    }

    function applyFilters() {
        // If pagination is available, use it instead of the simple filter
        if (typeof window.sardiusPaginationData !== 'undefined') {
            // Reset to page 1 when filters are applied
            const page = 1;
            
            // Load page 1 with current filters
            loadPage(page, filters);
            return;
        }
        
        // For non-paginated pages, use the simple filter
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
                        ${item.bible_reference ? `<p><strong>Scripture:</strong> ${item.bible_reference}</p>` : ''}
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
        window.sardiusCurrentFilters = filters;
        checkFiltersActive();
        updateURLParameters();
        applyFilters();
    });

    $('#sardius-search').on('keyup', debounce(function() {
        filters.search = $(this).val();
        window.sardiusCurrentFilters = filters;
        checkFiltersActive();
        updateURLParameters();
        applyFilters();
    }, 500));
    
    // Update clear button visibility immediately when typing
    $('#sardius-search').on('input', function() {
        updateClearSearchButton();
    });

    $('#sardius-series-filter').on('change', function() {
        filters.category = $(this).val();
        window.sardiusCurrentFilters = filters;
        checkFiltersActive();
        updateURLParameters();
        applyFilters();
    });

    $('#sardius-date-from, #sardius-date-to').on('change', function() {
        filters.dateFrom = $('#sardius-date-from').val();
        filters.dateTo = $('#sardius-date-to').val();
        if (filters.dateFrom && filters.dateTo) {
            window.sardiusCurrentFilters = filters;
            checkFiltersActive();
            updateURLParameters();
            applyFilters();
        }
    });

    $('#reset-filters').on('click', function() {
        resetFilters();
    });
    
    // Clear series button event handler
    $clearSeriesButton.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Clear the series filter
        filters.category = '';
        $seriesInput.val('');
        
        // Update global filters
        window.sardiusCurrentFilters = filters;
        
        // Update UI
        checkFiltersActive();
        hideSeriesDropdown();
        
        // Update URL and apply filters
        updateURLParameters();
        applyFilters();
    });
    
    // Clear speaker button event handler
    $clearSpeakerButton.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Clear the speaker filter
        filters.speaker = '';
        $speakerInput.val('');
        
        // Update global filters
        window.sardiusCurrentFilters = filters;
        
        // Update UI
        checkFiltersActive();
        hideSpeakerDropdown();
        
        // Update URL and apply filters
        updateURLParameters();
        applyFilters();
    });
    
    // Clear date range button event handler
    $clearDateButton.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Clear the date range filters
        filters.dateFrom = '';
        filters.dateTo = '';
        $('#sardius-date-from').val('');
        $('#sardius-date-to').val('');
        
        // Update global filters
        window.sardiusCurrentFilters = filters;
        
        // Update UI
        checkFiltersActive();
        
        // Update URL and apply filters
        updateURLParameters();
        applyFilters();
    });
    
    // Clear search button event handler
    $clearSearchButton.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Clear the search filter
        filters.search = '';
        $('#sardius-search').val('');
        
        // Update global filters
        window.sardiusCurrentFilters = filters;
        
        // Update UI
        checkFiltersActive();
        
        // Update URL and apply filters
        updateURLParameters();
        applyFilters();
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
    
    // Load filters from URL parameters on page load
    const filtersLoadedFromURL = loadFiltersFromURL();
    
    // Initialize clear series button visibility
    updateClearSeriesButton();
    
    // Initialize clear speaker button visibility
    updateClearSpeakerButton();
    
    // Initialize clear date button visibility
    updateClearDateButton();
    
    // Initialize clear search button visibility
    updateClearSearchButton();
    
    // Check if we need to apply filters on initial load
    const hasActiveFilters = filters.search !== '' || 
                            filters.category !== '' || 
                            filters.speaker !== '' || 
                            filters.dateFrom !== '' || 
                            filters.dateTo !== '' ||
                            filters.type !== 'message';
    
            // Check if we have a page parameter in URL
        const urlParams = new URLSearchParams(window.location.search);
        const hasPageParam = urlParams.has('media_page');
    
    // Store the fact that we need to apply filters after pagination is ready
    window.sardiusNeedsInitialLoad = (!hasActiveFilters && !filtersLoadedFromURL && !hasPageParam && typeof window.sardiusPaginationData === 'undefined');
    
    // --- Custom Date Input Behavior ---
    initializeCustomDateInputs();
    
    // --- Custom Date Input Behavior ---
    function initializeCustomDateInputs() {
        const $dateInputs = $('#sardius-date-from, #sardius-date-to');
        let calendarOpen = false;
        let activeInput = null;
        
        $dateInputs.each(function() {
            const $input = $(this);
            
            // Focus event - open calendar
            $input.on('focus', function(e) {
                activeInput = this;
                calendarOpen = true;
                
                // Trigger the native date picker
                this.showPicker();
                
                // Prevent default focus behavior
                e.preventDefault();
            });
            
            // Blur event - handle calendar interaction
            $input.on('blur', function(e) {
                // Use a small delay to check if user clicked on calendar
                setTimeout(() => {
                    // If calendar is still open, don't close it
                    if (calendarOpen && activeInput === this) {
                        this.focus();
                        return;
                    }
                    
                    // Calendar is closed, proceed with normal blur
                    calendarOpen = false;
                    activeInput = null;
                }, 100);
            });
            
            // Input event - handle date selection
            $input.on('input', function() {
                // When user selects a date, close the calendar
                calendarOpen = false;
                activeInput = null;
            });
            
            // Click event - ensure focus is maintained
            $input.on('click', function(e) {
                if (!calendarOpen) {
                    this.focus();
                }
            });
        });
        
        // Handle clicks outside to close calendar
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.date-range-input').length && calendarOpen) {
                calendarOpen = false;
                activeInput = null;
            }
        });
        
        // Handle clicks on the date range container to focus the first input
        $('.date-range-input').on('click', function(e) {
            if (!$(e.target).is('input[type="date"]')) {
                $('#sardius-date-from').focus();
            }
        });
    }
    
    // --- Sardius Media Archive Pagination ---
    
    // Check if pagination data is available
    if (typeof window.sardiusPaginationData !== 'undefined') {
        initializePagination();
    } else {
        // For non-paginated pages, if we have URL parameters, we should apply them
        const urlParams = new URLSearchParams(window.location.search);
        const hasURLParams = urlParams.has('type') || urlParams.has('search') || 
                           urlParams.has('series') || urlParams.has('speaker') || 
                           urlParams.has('dateFrom') || urlParams.has('dateTo');
        
        if (hasURLParams || window.sardiusNeedsInitialLoad) {
            // Apply filters to show filtered results on non-paginated pages
            applyFilters();
        }
    }
    
    function initializePagination() {
        const paginationData = window.sardiusPaginationData;
        const $pagination = $('.sardius-frontend-pagination');
        
        // Mark that this is the initial load (only if not already set)
        if (typeof window.sardiusIsInitialLoad === 'undefined') {
            window.sardiusIsInitialLoad = true;
        }
        
        // Get initial page from stored value or URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const initialPage = parseInt(window.sardiusInitialPage || urlParams.get('media_page')) || 1;
        
        // Ensure we have the current filters (they should be loaded by now)
        const currentFilters = window.sardiusCurrentFilters || filters;
        
        // Load initial page with current filters
        loadPage(initialPage, currentFilters);
        
        // Scroll to center the active page on initial load
        setTimeout(scrollToActivePage, 500);
        
        // Handle pagination button clicks
        $pagination.on('click', '.pagination-button', function(e) {
            e.preventDefault();
            const $button = $(this);
            const page = $button.data('page');
            
            if (page && page !== paginationData.currentPage) {
                // Use current filters when paginating and update URL
                navigateToPage(page, window.sardiusCurrentFilters || {});
            }
        });
        
        // scrollToActivePage function is already defined globally above
        
        // loadPage function is already defined globally above
        
        // Override the existing applyFilters function to work with pagination
        const originalApplyFilters = applyFilters;
        applyFilters = function() {
            // Reset to page 1 when filters are applied
            const page = 1;
            
            // Load page 1 with new filters
            loadPage(page, filters);
        };
        
        // Store current filters globally for pagination
        window.sardiusCurrentFilters = filters;
        
        // Handle browser back/forward buttons
        $(window).on('popstate', function(e) {
            const urlParams = new URLSearchParams(window.location.search);
            const page = parseInt(urlParams.get('media_page')) || 1;
            
            // Check if filters have changed
            const newFilters = {
                type: urlParams.get('type') || 'message',
                search: urlParams.get('search') || '',
                category: urlParams.get('series') || '',
                speaker: urlParams.get('speaker') || '',
                dateFrom: urlParams.get('dateFrom') || '',
                dateTo: urlParams.get('dateTo') || ''
            };
            
            // Update filters if they've changed
            let filtersChanged = false;
            for (let key in newFilters) {
                if (newFilters[key] !== filters[key]) {
                    filters[key] = newFilters[key];
                    filtersChanged = true;
                }
            }
            
            // Update UI if filters changed
            if (filtersChanged) {
                // Update UI elements
                $(`.filter-button[data-filter-value="${filters.type}"]`).addClass('active').siblings().removeClass('active');
                $('#sardius-search').val(filters.search);
                $('#sardius-series-filter').val(filters.category);
                $('#sardius-speaker-filter').val(filters.speaker);
                $('#sardius-date-from').val(filters.dateFrom);
                $('#sardius-date-to').val(filters.dateTo);
                checkFiltersActive();
                window.sardiusCurrentFilters = filters;
            }
            
            // Load page with current filters
            loadPage(page, filters);
        });
    }
}); 