jQuery(document).ready(function($) {
    const searchInput = $("#job-search");
    const jobTypeFilter = $("#job-type-filter");
    const jobCards = $(".job-card");
    
    function updateSearchResultCount() {
        const searchData = $('#search-data');
        if (searchData.length > 0) {
            const searchTerm = searchData.data('search-term');
            setTimeout(function() {
                const visibleCards = $('.job-card:not(.hidden)').length;
                const searchInfo = $('.search-info');
                if (searchInfo.length > 0 && searchTerm) {
                    const resultText = visibleCards === 1 ? 'result' : 'results';
                    // Create elements instead of using innerHTML for CSP compliance
                    const resultSpan = $('<span class="result-count"></span>').text(visibleCards + ' ' + resultText);
                    const searchTermStrong = $('<strong></strong>').text('"' + searchTerm + '"');
                    const clearBtn = $('<button type="button" class="clear-search-btn" data-clear-search="true">Clear Search</button>');
                    
                    searchInfo.empty().append(resultSpan).append(' for: ').append(searchTermStrong).append(' ').append(clearBtn);
                }
            }, 500);
        }
    }
    
    function filterJobs() {
        const searchTerm = searchInput.val().toLowerCase();
        const selectedJobType = jobTypeFilter.val();
        
        jobCards.each(function() {
            const $card = $(this);
            const searchTerms = $card.data("search-terms") || "";
            const jobTypes = $card.data("job-types") || "";
            
            const matchesSearch = !searchTerm || searchTerms.includes(searchTerm);
            const matchesJobType = !selectedJobType || jobTypes.includes(selectedJobType);
            
            if (matchesSearch && matchesJobType) {
                $card.removeClass("hidden").fadeIn(300);
            } else {
                $card.addClass("hidden").fadeOut(300);
            }
        });
        
        // Show/hide no results message
        setTimeout(function() {
            const visibleCards = jobCards.filter(":visible").length;
            const $noResults = $(".no-results");
            
            if (visibleCards === 0 && $noResults.length === 0) {
                const noResultsP = $('<p class="no-results">No jobs match your search criteria.</p>');
                $(".job-listings-grid").append(noResultsP);
            } else if (visibleCards > 0) {
                $noResults.remove();
            }
            
            // Update search result count if we're on a search page
            updateSearchResultCount();
        }, 350);
    }
    
    // Check for URL search parameter on page load
    function initializeFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('job_search');
        
        if (searchParam && searchInput.length > 0) {
            searchInput.val(searchParam);
            // Trigger filtering after a short delay to ensure DOM is ready
            setTimeout(function() {
                filterJobs();
            }, 100);
        } else {
            // Still update result count on initial load if searching
            updateSearchResultCount();
        }
    }
    
    // Initialize search from URL parameter
    initializeFromURL();
    
    // Bind events
    searchInput.on("input", filterJobs);
    jobTypeFilter.on("change", filterJobs);
    
    // Clear search functionality
    searchInput.on("keyup", function(e) {
        if (e.keyCode === 27) { // ESC key
            $(this).val("");
            filterJobs();
        }
    });
    
    // Update URL when searching (CSP-safe method)
    searchInput.on("input", function() {
        const searchTerm = $(this).val();
        try {
            if (searchTerm) {
                const url = new URL(window.location.href);
                url.searchParams.set('job_search', searchTerm);
                window.history.replaceState(null, '', url.toString());
            } else {
                const url = new URL(window.location.href);
                url.searchParams.delete('job_search');
                window.history.replaceState(null, '', url.toString());
            }
        } catch (e) {
            // Fallback if URL manipulation fails
            console.log('URL update failed:', e);
        }
    });
    
    // Handle clear search buttons (CSP-safe)
    $(document).on('click', '[data-clear-search]', function(e) {
        e.preventDefault();
        // Remove search parameter and reload
        try {
            const url = new URL(window.location.href);
            url.searchParams.delete('job_search');
            window.location.href = url.toString();
        } catch (e) {
            // Fallback
            window.location.href = window.location.pathname;
        }
    });
    
    // Handle view all buttons (CSP-safe)
    $(document).on('click', '[data-view-all]', function(e) {
        e.preventDefault();
        // Remove search parameter and reload
        try {
            const url = new URL(window.location.href);
            url.searchParams.delete('job_search');
            window.location.href = url.toString();
        } catch (e) {
            // Fallback
            window.location.href = window.location.pathname;
        }
    });
    
    // Enhanced functionality for single job pages
    
    // Smooth scroll to apply section when apply buttons are clicked
    $('.job-apply-btn').on('click', function(e) {
        // Add a small delay to show the action before navigation
        const $btn = $(this);
        $btn.addClass('applying');
        
        setTimeout(function() {
            $btn.removeClass('applying');
        }, 200);
    });
    
    // Add copy functionality for contact info (useful for mobile)
    if (navigator.clipboard) {
        $('.contact-value, .business-value').on('dblclick', function() {
            const text = $(this).text().trim();
            if (text && (text.includes('@') || text.match(/[\d\-\(\)\s\+]/))) {
                navigator.clipboard.writeText(text).then(function() {
                    // Show brief success message
                    const $element = $(this);
                    const originalBg = $element.css('background-color');
                    $element.css('background-color', '#e8f5e8');
                    setTimeout(function() {
                        $element.css('background-color', originalBg);
                    }, 1000);
                }.bind(this));
            }
        });
    }
    
    // Auto-expand job descriptions that are truncated
    $('.job-excerpt').each(function() {
        const $excerpt = $(this);
        const text = $excerpt.text();
        
        if (text.length > 150 && text.endsWith('...')) {
            const readMoreLink = $('<a href="#" class="read-more" style="color: #1976d2; text-decoration: none; font-weight: 500;">Read more</a>');
            $excerpt.append(' ').append(readMoreLink);
        }
    });
    
    // Handle read more clicks
    $(document).on('click', '.read-more', function(e) {
        e.preventDefault();
        const $link = $(this);
        const $card = $link.closest('.job-card');
        const jobUrl = $card.find('.job-title a').attr('href');
        
        if (jobUrl) {
            window.location.href = jobUrl;
        }
    });
    
    // Function to hide empty blurb modules
    function hideEmptyBlurbs() {
        $('.et_pb_blurb').each(function() {
            var $blurb = $(this);
            var $container = $blurb.find('.et_pb_blurb_container');
            
            // Check if container is empty or contains only whitespace
            if ($container.length && ($container.is(':empty') || $container.text().trim() === '')) {
                $blurb.hide();
            }
        });
    }
    
    // Run immediately
    hideEmptyBlurbs();
    
    // Run again after a short delay (in case shortcodes load later)
    setTimeout(hideEmptyBlurbs, 500);
    
    // Run when window loads (final safety net)
    $(window).on('load', hideEmptyBlurbs);
});