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
                    searchInfo.html('<span class="result-count">' + visibleCards + ' ' + resultText + '</span> for: <strong>"' + searchTerm + '"</strong> <button type="button" class="clear-search-btn" data-clear-search="true">Clear Search</button>');
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
                $(".job-listings-grid").append("<p class=\"no-results\">No jobs match your search criteria.</p>");
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
    
    // Update URL when searching (optional - keeps search in browser history)
    searchInput.on("input", function() {
        const searchTerm = $(this).val();
        if (searchTerm) {
            const url = new URL(window.location);
            url.searchParams.set('job_search', searchTerm);
            window.history.replaceState({}, '', url);
        } else {
            const url = new URL(window.location);
            url.searchParams.delete('job_search');
            window.history.replaceState({}, '', url);
        }
    });
    
    // Handle clear search buttons (CSP-safe)
    $(document).on('click', '[data-clear-search]', function(e) {
        e.preventDefault();
        // Remove search parameter and reload
        const url = new URL(window.location);
        url.searchParams.delete('job_search');
        window.location.href = url.toString();
    });
    
    // Handle view all buttons (CSP-safe)
    $(document).on('click', '[data-view-all]', function(e) {
        e.preventDefault();
        // Remove search parameter and reload
        const url = new URL(window.location);
        url.searchParams.delete('job_search');
        window.location.href = url.toString();
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
    
    // Add loading state styles
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .job-apply-btn.applying {
                opacity: 0.7;
                transform: scale(0.98);
            }
            
            .job-card:hover .job-meta {
                color: #333;
            }
            
            .recent-job-item:hover {
                background: #f9f9f9;
                padding-left: 5px;
                transition: all 0.3s ease;
            }
        `)
        .appendTo('head');
    
    // Add copy functionality for contact info (useful for mobile)
    if (navigator.clipboard) {
        $('.contact-value, .business-value').on('dblclick', function() {
            const text = $(this).text().trim();
            if (text && (text.includes('@') || text.match(/[\d\-\(\)\s\+]/))) {
                navigator.clipboard.writeText(text).then(function() {
                    // Show brief success message
                    const $element = $(this);
                    const originalBg = $element.css('background-color');
                    $element.css('background-color', '#e8f5e8').animate({
                        'background-color': originalBg
                    }, 1000);
                });
            }
        }.bind(this));
    }
    
    // Auto-expand job descriptions that are truncated
    $('.job-excerpt').each(function() {
        const $excerpt = $(this);
        const text = $excerpt.text();
        
        if (text.length > 150 && text.endsWith('...')) {
            $excerpt.append(' <a href="#" class="read-more" style="color: #1976d2; text-decoration: none; font-weight: 500;">Read more</a>');
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
});