<?php
/**
 * Chelsea Jobs Board Shortcodes Class
 * Handles all shortcode functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChelseaJobs_Shortcodes {
    
    public function __construct() {
        $this->register_shortcodes();
    }
    
    public function register_shortcodes() {
        add_shortcode('jobs_board', array($this, 'jobs_board_shortcode'));
        add_shortcode('job_type_template', array($this, 'job_type_template_shortcode'));
        add_shortcode('job_types_grid', array($this, 'job_types_grid_shortcode'));
        add_shortcode('job_search', array($this, 'job_search_shortcode'));
        add_shortcode('recent_jobs', array($this, 'recent_jobs_shortcode'));
        add_shortcode('job_contact_info', array($this, 'job_contact_info_shortcode'));
        add_shortcode('job_business_info', array($this, 'job_business_info_shortcode'));
        add_shortcode('job_apply_button', array($this, 'job_apply_button_shortcode'));
    }
    
    /**
     * Main jobs board shortcode
     */
    public function jobs_board_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => -1,
            'job_type' => '',
            'show_filter' => 'true',
            'show_search' => 'true',
            'columns' => '1',
            'show_excerpt' => 'true',
            'excerpt_length' => 150,
            'orderby' => 'date',
            'order' => 'DESC',
            'show_dynamic_title' => 'true',
            'show_search_status' => 'true',
        ), $atts);

        // Get search parameter from URL
        $search_term = isset($_GET['job_search']) ? sanitize_text_field($_GET['job_search']) : '';
        $is_searching = !empty($search_term);

        ob_start();
        
        // Get all job types for filter
        $job_types = ChelseaJobs_Queries::get_job_types();
        
        ?>
        <div class="jobs-board" id="jobs-board">
            
            <?php if ($atts['show_dynamic_title'] === 'true'): ?>
            <div class="jobs-header">
                <?php if ($is_searching): ?>
                    <h1 class="jobs-title">Job Search Results</h1>
                    <?php if ($atts['show_search_status'] === 'true'): ?>
                    <div class="search-status">
                        <p class="search-info">
                            Showing results for: <strong>"<?php echo esc_html($search_term); ?>"</strong>
                            <button type="button" class="clear-search-btn" data-clear-search="true">
                                Clear Search
                            </button>
                        </p>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <h1 class="jobs-title">Available Jobs</h1>
                    <?php if ($atts['show_search_status'] === 'true'): ?>
                    <div class="search-status">
                        <p class="browse-info">Browse all available job opportunities</p>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['show_filter'] === 'true' || $atts['show_search'] === 'true'): ?>
            <div class="jobs-filters">
                
                <?php if ($atts['show_search'] === 'true'): ?>
                <div class="search-box">
                    <input type="text" id="job-search" placeholder="Search jobs..." 
                           value="<?php echo esc_attr($search_term); ?>" />
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_filter'] === 'true' && !empty($job_types)): ?>
                <div class="job-type-filter">
                    <select id="job-type-filter">
                        <option value="">All Job Types</option>
                        <?php foreach ($job_types as $type): ?>
                            <option value="<?php echo esc_attr($type->slug); ?>">
                                <?php echo esc_html($type->name); ?> (<?php echo $type->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
            </div>
            <?php endif; ?>
            
            <div class="job-listings-grid" data-columns="<?php echo esc_attr($atts['columns']); ?>">
                <?php
                // Get jobs using our query class
                $jobs_query = ChelseaJobs_Queries::get_jobs(array(
                    'posts_per_page' => $atts['posts_per_page'],
                    'job_type' => $atts['job_type'],
                    'orderby' => $atts['orderby'],
                    'order' => $atts['order'],
                ));
                
                if ($jobs_query->have_posts()):
                    while ($jobs_query->have_posts()): $jobs_query->the_post();
                        $this->render_job_card($atts);
                    endwhile;
                    wp_reset_postdata();
                    
                    // Add result count after listings load
                    if ($is_searching && $atts['show_search_status'] === 'true'): 
                        echo '<div id="search-data" data-search-term="' . esc_attr($search_term) . '" style="display:none;"></div>';
                    endif;
                else:
                    if ($is_searching): ?>
                        <div class="no-results-search">
                            <h3>No jobs found for "<?php echo esc_html($search_term); ?>"</h3>
                            <p>Try:</p>
                            <ul>
                                <li>Checking your spelling</li>
                                <li>Using more general terms</li>
                                <li>Browsing by job type instead</li>
                            </ul>
                            <button type="button" class="view-all-btn" data-view-all="true">
                                View All Jobs
                            </button>
                        </div>
                    <?php else: ?>
                        <p class="no-results">No jobs found.</p>
                    <?php endif;
                endif;
                ?>
                
            </div>
            
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job type template shortcode
     */
    public function job_type_template_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => -1,
            'show_search' => 'true',
            'columns' => '1',
            'show_excerpt' => 'true',
            'excerpt_length' => 150,
            'orderby' => 'date',
            'order' => 'DESC',
            'show_type_title' => 'true',
            'show_type_description' => 'true',
        ), $atts);

        // Get the current job type
        $current_type = get_queried_object();
        
        // Make sure we're on a job type page
        if (!$current_type || $current_type->taxonomy !== 'job-type') {
            return '<p>This shortcode should only be used on Job Type pages.</p>';
        }

        ob_start();
        ?>
        <div class="jobs-board job-type-template" id="jobs-board">
            
            <?php if ($atts['show_type_title'] === 'true'): ?>
            <div class="jobs-header">
                <h1 class="jobs-title"><?php echo esc_html($current_type->name); ?> Jobs</h1>
                <?php if ($atts['show_type_description'] === 'true' && $current_type->description): ?>
                <div class="type-description">
                    <p><?php echo esc_html($current_type->description); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['show_search'] === 'true'): ?>
            <div class="jobs-filters">
                <div class="search-box">
                    <input type="text" id="job-search" placeholder="Search <?php echo esc_attr($current_type->name); ?> jobs..." />
                </div>
            </div>
            <?php endif; ?>
            
            <div class="job-listings-grid" data-columns="<?php echo esc_attr($atts['columns']); ?>">
                <?php
                // Get jobs for this type
                $jobs_query = ChelseaJobs_Queries::get_jobs_by_type($current_type->term_id, array(
                    'posts_per_page' => $atts['posts_per_page'],
                    'orderby' => $atts['orderby'],
                    'order' => $atts['order'],
                ));
                
                if ($jobs_query->have_posts()):
                    while ($jobs_query->have_posts()): $jobs_query->the_post();
                        $this->render_job_card($atts, false); // false = don't show job types since we're on type page
                    endwhile;
                    wp_reset_postdata();
                else:
                    echo '<p class="no-results">No jobs found in this category.</p>';
                endif;
                ?>
                
            </div>
            
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job types grid shortcode
     */
    public function job_types_grid_shortcode($atts) {
        $atts = shortcode_atts(array(
            'columns' => '4',
        ), $atts);

        $terms = get_terms(array(
            'taxonomy' => 'job-type',
            'hide_empty' => true,
        ));
        
        if (empty($terms) || is_wp_error($terms)) {
            return '<p class="no-job-types">No job types found.</p>';
        }

        ob_start();
        ?>
        <div class="job-types-grid" data-columns="<?php echo esc_attr($atts['columns']); ?>">
            <?php foreach ($terms as $term): ?>
                <?php
                $term_link = get_term_link($term);
                ?>
                <div class="job-type-item">
                    <a href="<?php echo esc_url($term_link); ?>" class="job-type-link">
                        <div class="job-type-icon">
                            💼
                        </div>
                        <span class="job-type-name"><?php echo esc_html($term->name); ?></span>
                        <span class="job-count"><?php echo $term->count; ?> jobs</span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job search bar shortcode
     */
    public function job_search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => 'Search for jobs, companies, or job types...',
            'button_text' => 'Search Jobs',
        ), $atts);

        // Get the main jobs page URL for search results
        $pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            's' => '[jobs_board',
        ));
        
        $search_url = !empty($pages) ? get_permalink($pages[0]->ID) : home_url('/jobs/');

        ob_start();
        ?>
        <div class="job-search-container">
            <form class="job-search-form" method="get" action="<?php echo esc_url($search_url); ?>">
                <div class="search-input-wrapper">
                    <span class="search-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                    </span>
                    
                    <input type="text" 
                           name="job_search" 
                           class="job-search-input" 
                           placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                           value="<?php echo esc_attr(isset($_GET['job_search']) ? $_GET['job_search'] : ''); ?>">
                    
                    <button type="submit" class="job-search-button">
                        <?php echo esc_html($atts['button_text']); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Recent jobs shortcode
     */
    public function recent_jobs_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => '5',
            'show_date' => 'true',
            'show_company' => 'true',
        ), $atts);

        $jobs_query = ChelseaJobs_Queries::get_recent_jobs($atts['limit']);
        
        if (!$jobs_query->have_posts()) {
            return '<p class="no-recent-jobs">No recent jobs found.</p>';
        }

        ob_start();
        ?>
        <div class="recent-jobs-widget">
            <ul class="recent-jobs-list">
                <?php while ($jobs_query->have_posts()): $jobs_query->the_post(); ?>
                    <?php $job_fields = ChelseaJobs_Queries::get_job_fields(); ?>
                    <li class="recent-job-item">
                        <h4 class="job-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h4>
                        <?php if ($atts['show_company'] === 'true' && $job_fields['business_name']): ?>
                        <div class="job-company"><?php echo esc_html($job_fields['business_name']); ?></div>
                        <?php endif; ?>
                        <?php if ($atts['show_date'] === 'true'): ?>
                        <div class="job-date"><?php echo get_the_date(); ?></div>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <?php
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Job contact info shortcode (for single job pages)
     */
    public function job_contact_info_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'default', // default, card, minimal
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);

        ob_start();
        ?>
        <div class="job-contact-info <?php echo esc_attr('style-' . $atts['style']); ?>">
            <h3>Contact Information</h3>
            
            <?php if ($job_fields['contact_name']): ?>
            <div class="contact-item">
                <span class="contact-label">Contact:</span>
                <span class="contact-value"><?php echo esc_html($job_fields['contact_name']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($job_fields['contact_email']): ?>
            <div class="contact-item">
                <span class="contact-label">Email:</span>
                <span class="contact-value">
                    <a href="mailto:<?php echo esc_attr($job_fields['contact_email']); ?>">
                        <?php echo esc_html($job_fields['contact_email']); ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($job_fields['contact_phone']): ?>
            <div class="contact-item">
                <span class="contact-label">Phone:</span>
                <span class="contact-value">
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $job_fields['contact_phone'])); ?>">
                        <?php echo esc_html($job_fields['contact_phone']); ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job business info shortcode (for single job pages)
     */
    public function job_business_info_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_description' => 'true',
            'show_website' => 'true',
            'show_address' => 'true',
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);

        ob_start();
        ?>
        <div class="job-business-info">
            <?php if ($job_fields['business_name']): ?>
            <h3>About <?php echo esc_html($job_fields['business_name']); ?></h3>
            <?php endif; ?>
            
            <?php if ($atts['show_description'] === 'true' && $job_fields['business_description']): ?>
            <div class="business-description">
                <?php echo wp_kses_post($job_fields['business_description']); ?>
            </div>
            <?php endif; ?>
            
            <div class="business-details">
                <?php if ($atts['show_website'] === 'true' && $job_fields['business_website']): ?>
                <div class="business-item">
                    <span class="business-label">Website:</span>
                    <span class="business-value">
                        <a href="<?php echo esc_url($job_fields['business_website']); ?>" target="_blank" rel="noopener">
                            <?php echo esc_html(preg_replace('/^https?:\/\/(www\.)?/', '', $job_fields['business_website'])); ?>
                        </a>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_address'] === 'true' && $job_fields['business_office_address']): ?>
                <div class="business-item">
                    <span class="business-label">Location:</span>
                    <span class="business-value"><?php echo esc_html($job_fields['business_office_address']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job apply button shortcode
     */
    public function job_apply_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'text' => 'Apply Now',
            'style' => 'email', // email, phone, website
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);

        $button_url = '';
        $button_class = 'job-apply-btn';

        switch ($atts['style']) {
            case 'phone':
                if ($job_fields['contact_phone']) {
                    $clean_phone = preg_replace('/[^0-9+]/', '', $job_fields['contact_phone']);
                    $button_url = 'tel:' . $clean_phone;
                    $button_class .= ' phone-apply';
                }
                break;
            case 'website':
                if ($job_fields['business_website']) {
                    $button_url = $job_fields['business_website'];
                    $button_class .= ' website-apply';
                }
                break;
            default: // email
                if ($job_fields['contact_email']) {
                    $subject = 'Application for: ' . get_the_title($post_id);
                    $button_url = 'mailto:' . $job_fields['contact_email'] . '?subject=' . urlencode($subject);
                    $button_class .= ' email-apply';
                }
                break;
        }

        ob_start();

        if (!empty($button_url)) {
            // Display apply button with disclaimer
            ?>
            <div class="job-apply-section">
                <a href="<?php echo esc_url($button_url); ?>" class="<?php echo esc_attr($button_class); ?>">
                    <?php echo esc_html($atts['text']); ?>
                </a>
                <p class="apply-disclaimer">Clicking apply will redirect you to the employer's application page.</p>
            </div>
            <?php
        } else {
            // No apply link available - show fallback messaging
            ?>
            <div class="job-apply-section no-online-apply">
                <?php if ($job_fields['contact_phone']): ?>
                    <div class="apply-fallback">
                        <p class="apply-method-label">To apply for this position:</p>
                        <p class="apply-phone">
                            <strong>Call:</strong>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $job_fields['contact_phone'])); ?>">
                                <?php echo esc_html($job_fields['contact_phone']); ?>
                            </a>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="apply-fallback">
                        <p class="apply-in-person"><strong>Apply in Person</strong></p>
                        <?php if ($job_fields['business_office_address']): ?>
                            <p class="apply-address">
                                <?php echo esc_html($job_fields['business_office_address']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }

        return ob_get_clean();
    }
    
    /**
     * Render a single job card
     */
    private function render_job_card($atts, $show_job_types = true) {
        // Get job data
        $job_fields = ChelseaJobs_Queries::get_job_fields();
        $job_types = ChelseaJobs_Queries::get_job_types_for_post();
        $search_content = ChelseaJobs_Queries::get_job_search_content(get_the_ID(), $job_fields, $job_types);
        
        ?>
        <div class="job-card" 
             data-job-types="<?php echo esc_attr(implode(' ', $job_types['slugs'])); ?>"
             data-search-terms="<?php echo esc_attr($search_content); ?>">
            
            <div class="job-content">
                <div class="job-header">
                    <h3 class="job-title">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    
                    <?php if ($job_fields['business_name']): ?>
                    <div class="job-company">
                        <strong><?php echo esc_html($job_fields['business_name']); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($show_job_types && !empty($job_types['names'])): ?>
                <div class="job-types">
                    <?php 
                    $job_type_terms = get_the_terms(get_the_ID(), 'job-type');
                    if ($job_type_terms && !is_wp_error($job_type_terms)):
                        foreach ($job_type_terms as $type): 
                            $type_link = get_term_link($type);
                            ?>
                            <a href="<?php echo esc_url($type_link); ?>" class="job-type-tag">
                                <?php echo esc_html($type->name); ?>
                            </a>
                        <?php endforeach;
                    endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_excerpt'] === 'true'): ?>
                <div class="job-excerpt">
                    <?php
                    $excerpt = get_the_excerpt();
                    if (empty($excerpt) && $job_fields['business_description']) {
                        $excerpt = wp_trim_words($job_fields['business_description'], 20, '...');
                    }
                    echo wp_kses_post($excerpt);
                    ?>
                </div>
                <?php endif; ?>
                
                <div class="job-meta">
                    <?php if ($job_fields['business_office_address']): ?>
                    <div class="job-location">
                        <span class="meta-icon">📍</span>
                        <span><?php echo esc_html($job_fields['business_office_address']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="job-date">
                        <span class="meta-icon">📅</span>
                        <span>Posted <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> ago</span>
                    </div>
                </div>
                
                <div class="job-actions">
                    <a href="<?php the_permalink(); ?>" class="view-job-btn">
                        View Details
                    </a>
                    
                    <?php if ($job_fields['contact_email']): ?>
                    <a href="mailto:<?php echo esc_attr($job_fields['contact_email']); ?>?subject=Application for: <?php echo urlencode(get_the_title()); ?>" class="quick-apply-btn">
                        Quick Apply
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        <?php
    }
}
?>