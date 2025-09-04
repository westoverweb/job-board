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
        add_shortcode('job_description', array($this, 'job_description_shortcode'));
        add_shortcode('job_apply_button', array($this, 'job_apply_button_shortcode'));
        
        // Individual job field shortcodes
        add_shortcode('job_business_name', array($this, 'job_business_name_shortcode'));
        add_shortcode('job_office_address', array($this, 'job_office_address_shortcode'));
        add_shortcode('job_salary', array($this, 'job_salary_shortcode'));
        add_shortcode('job_business_description', array($this, 'job_business_description_shortcode'));
        add_shortcode('job_requirements', array($this, 'job_requirements_shortcode'));
        add_shortcode('job_benefits', array($this, 'job_benefits_shortcode'));
        add_shortcode('job_business_logo', array($this, 'job_business_logo_shortcode'));
        add_shortcode('job_business_phone', array($this, 'job_business_phone_shortcode'));
        add_shortcode('job_business_website', array($this, 'job_business_website_shortcode'));
        add_shortcode('job_business_address', array($this, 'job_business_address_shortcode'));
        add_shortcode('job_contact_email_button', array($this, 'job_contact_email_button_shortcode'));
        add_shortcode('job_business_profile_button', array($this, 'job_business_profile_button_shortcode'));
    }
    
    /**
     * Job business profile button shortcode
     */
    public function job_business_profile_button_shortcode($atts) {
        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);
        $linked_business = $job_fields['linked_business_object'];
        
        if (!$linked_business || empty($linked_business)) {
            return '';
        }

        $business_post = is_array($linked_business) ? $linked_business[0] : $linked_business;
        $business_link = get_permalink($business_post->ID);
        $business_name = $business_post->post_title;

        ob_start();
        ?>
        <div class="job-business-profile-container">
            <a href="<?php echo esc_url($business_link); ?>" class="job-business-profile-btn">
                View <?php echo esc_html($business_name); ?> Profile
            </a>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job contact email button shortcode
     */
    public function job_contact_email_button_shortcode($atts) {
        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $contact_email = get_field('job_contact_email', $post_id);
        
        if (empty($contact_email)) {
            return '';
        }

        // Get job title for email subject
        $job_title = get_the_title($post_id);
        $subject = 'Inquiry about: ' . $job_title;
        
        // Use rawurlencode and replace + with %20 for proper email subject formatting
        $encoded_subject = str_replace('+', '%20', urlencode($subject));
        
        // Create mailto link with subject
        $mailto_link = 'mailto:' . $contact_email . '?subject=' . $encoded_subject;

        ob_start();
        ?>
        <div class="job-contact-email-container">
            <a href="<?php echo esc_url($mailto_link); ?>" class="job-contact-email-btn">
                Contact About This Job
            </a>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job business phone shortcode
     */
    public function job_business_phone_shortcode($atts) {
        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);
        $phone = '';
        
        // Get phone from linked business
        if (isset($job_fields['linked_business_data']['phone']) && !empty($job_fields['linked_business_data']['phone'])) {
            $phone = $job_fields['linked_business_data']['phone'];
        }
        
        if (empty($phone)) {
            return '';
        }

        ob_start();
        ?>
        <div class="job-business-phone">
            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="business-phone-link">
                <?php echo esc_html($phone); ?>
            </a>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job business website shortcode
     */
    public function job_business_website_shortcode($atts) {
        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);
        $website = '';
        
        // Get website from linked business
        if (isset($job_fields['linked_business_data']['website']) && !empty($job_fields['linked_business_data']['website'])) {
            $website = $job_fields['linked_business_data']['website'];
        }
        
        if (empty($website)) {
            return '';
        }

        // Ensure website has protocol
        if (!preg_match('/^https?:\/\//', $website)) {
            $website = 'https://' . $website;
        }

        // Get display text (remove protocol for cleaner display)
        $display_text = preg_replace('/^https?:\/\//', '', $website);
        $display_text = rtrim($display_text, '/'); // Remove trailing slash

        ob_start();
        ?>
        <div class="job-business-website">
            <a href="<?php echo esc_url($website); ?>" class="business-website-btn" target="_blank" rel="noopener">
                <?php echo esc_html($display_text); ?>
            </a>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job business address shortcode
     */
    public function job_business_address_shortcode($atts) {
        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);
        $address = '';
        
        // Get address from linked business
        if (isset($job_fields['linked_business_data']['address']) && !empty($job_fields['linked_business_data']['address'])) {
            $address = $job_fields['linked_business_data']['address'];
        }
        
        if (empty($address)) {
            return '';
        }

        ob_start();
        ?>
        <div class="job-business-address">
            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($address); ?>" 
               class="business-address-btn" target="_blank" rel="noopener">
                <?php echo esc_html($address); ?>
            </a>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job business logo shortcode
     */
    public function job_business_logo_shortcode($atts) {
        $atts = shortcode_atts(array(
            'size' => 'medium',
            'link_to_business' => 'false',
            'class' => 'job-business-logo',
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);
        $logo = '';
        
        // Get logo from linked business
        if (isset($job_fields['linked_business_data']['logo']) && !empty($job_fields['linked_business_data']['logo'])) {
            $logo = $job_fields['linked_business_data']['logo'];
        }
        
        if (empty($logo)) {
            return '';
        }

        // Handle different logo field types (ID vs array vs URL)
        $logo_html = '';
        if (is_numeric($logo)) {
            // Logo is an attachment ID
            $logo_html = wp_get_attachment_image($logo, $atts['size'], false, array('class' => $atts['class']));
        } elseif (is_array($logo) && isset($logo['ID'])) {
            // Logo is an ACF image array
            $logo_html = wp_get_attachment_image($logo['ID'], $atts['size'], false, array('class' => $atts['class']));
        } elseif (is_array($logo) && isset($logo['url'])) {
            // Logo is an ACF image array with URL
            $alt = isset($logo['alt']) ? $logo['alt'] : '';
            $logo_html = '<img src="' . esc_url($logo['url']) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($atts['class']) . '">';
        } elseif (is_string($logo) && filter_var($logo, FILTER_VALIDATE_URL)) {
            // Logo is a URL string
            $logo_html = '<img src="' . esc_url($logo) . '" alt="" class="' . esc_attr($atts['class']) . '">';
        }
        
        if (empty($logo_html)) {
            return '';
        }

        ob_start();
        ?>
        <div class="job-business-logo-container">
            <?php if ($atts['link_to_business'] === 'true'): ?>
                <?php 
                $linked_business = $job_fields['linked_business_object'];
                if ($linked_business && !empty($linked_business)) {
                    $business_post = is_array($linked_business) ? $linked_business[0] : $linked_business;
                    $business_link = get_permalink($business_post->ID);
                    ?>
                    <a href="<?php echo esc_url($business_link); ?>" class="business-logo-link">
                        <?php echo $logo_html; ?>
                    </a>
                    <?php
                } else {
                    echo $logo_html;
                }
                ?>
            <?php else: ?>
                <?php echo $logo_html; ?>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job requirements shortcode
     */
    public function job_requirements_shortcode($atts) {
        $atts = shortcode_atts(array(
            'word_limit' => '',
            'show_title' => 'false',
            'title' => 'Requirements',
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $requirements = get_field('job_requirements', $post_id);
        
        if (empty($requirements)) {
            return '';
        }

        // Apply word limit if specified
        if (!empty($atts['word_limit']) && is_numeric($atts['word_limit'])) {
            $word_limit = intval($atts['word_limit']);
            $words = explode(' ', $requirements);
            if (count($words) > $word_limit) {
                $requirements = implode(' ', array_slice($words, 0, $word_limit)) . '...';
            }
        }

        ob_start();
        ?>
        <div class="job-requirements">
            <?php if ($atts['show_title'] === 'true'): ?>
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            <div class="requirements-text">
                <?php echo wp_kses_post($requirements); ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job benefits shortcode
     */
    public function job_benefits_shortcode($atts) {
        $atts = shortcode_atts(array(
            'word_limit' => '',
            'show_title' => 'false',
            'title' => 'Benefits',
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $benefits = get_field('job_benefits', $post_id);
        
        if (empty($benefits)) {
            return '';
        }

        // Apply word limit if specified
        if (!empty($atts['word_limit']) && is_numeric($atts['word_limit'])) {
            $word_limit = intval($atts['word_limit']);
            $words = explode(' ', $benefits);
            if (count($words) > $word_limit) {
                $benefits = implode(' ', array_slice($words, 0, $word_limit)) . '...';
            }
        }

        ob_start();
        ?>
        <div class="job-benefits">
            <?php if ($atts['show_title'] === 'true'): ?>
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            <div class="benefits-text">
                <?php echo wp_kses_post($benefits); ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job business description shortcode
     */
    public function job_business_description_shortcode($atts) {
        $atts = shortcode_atts(array(
            'word_limit' => '',
            'show_title' => 'false',
            'title' => 'About the Company',
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);
        $description = '';
        
        // Only get description from linked business
        if (isset($job_fields['linked_business_data']['description']) && !empty($job_fields['linked_business_data']['description'])) {
            $description = $job_fields['linked_business_data']['description'];
        }
        
        if (empty($description)) {
            return '';
        }

        // Apply word limit if specified
        if (!empty($atts['word_limit']) && is_numeric($atts['word_limit'])) {
            $word_limit = intval($atts['word_limit']);
            $words = explode(' ', $description);
            if (count($words) > $word_limit) {
                $description = implode(' ', array_slice($words, 0, $word_limit)) . '...';
            }
        }

        ob_start();
        ?>
        <div class="job-business-description">
            <?php if ($atts['show_title'] === 'true'): ?>
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            <div class="business-description-text">
                <?php echo wp_kses_post($description); ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job description shortcode (for single job pages)
     */
    public function job_description_shortcode($atts) {
        $atts = shortcode_atts(array(
            'word_limit' => '',
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        // Get job description from ACF field first
        $description = get_field('job_description', $post_id);
        
        // If no ACF field, try post content as fallback
        if (empty($description)) {
            $description = get_post_field('post_content', $post_id);
            if (!empty($description)) {
                $description = apply_filters('the_content', $description);
            }
        }
        
        // Final fallback to business description
        if (empty($description)) {
            $description = get_field('business_description', $post_id);
        }

        if (empty($description)) {
            return '';
        }

        // Apply word limit if specified
        if (!empty($atts['word_limit']) && is_numeric($atts['word_limit'])) {
            $word_limit = intval($atts['word_limit']);
            $words = explode(' ', $description);
            if (count($words) > $word_limit) {
                $description = implode(' ', array_slice($words, 0, $word_limit)) . '...';
            }
        }

        ob_start();
        ?>
        <div class="job-description-content">
            <div class="job-description-text">
                <?php echo wp_kses_post($description); ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job business name shortcode
     */
    public function job_business_name_shortcode($atts) {
        $atts = shortcode_atts(array(
            'link_to_business' => 'true',
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);
        $business_name = $job_fields['business_name'];
        
        if (!$business_name) {
            return '';
        }

        if ($atts['link_to_business'] === 'true') {
            $linked_business = $job_fields['linked_business_object'];
            if ($linked_business && !empty($linked_business)) {
                $business_post = is_array($linked_business) ? $linked_business[0] : $linked_business;
                $business_link = get_permalink($business_post->ID);
                return '<a href="' . esc_url($business_link) . '">' . esc_html($business_name) . '</a>';
            }
        }
        
        return esc_html($business_name);
    }
    
    /**
     * Job office address shortcode
     */
    public function job_office_address_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_label' => 'true',
            'label' => 'Location',
            'link_to_maps' => 'true',
            'show_icon' => 'true',
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        // First try job-specific office address
        $address = get_field('job_office_address', $post_id);
        
        // If no job-specific address, fallback to linked business address
        if (empty($address)) {
            $job_fields = ChelseaJobs_Queries::get_job_fields($post_id);
            $business_data = isset($job_fields['linked_business_data']) ? $job_fields['linked_business_data'] : array();
            $address = isset($business_data['address']) ? $business_data['address'] : '';
        }
        
        // Final fallback to old field name if it exists
        if (empty($address)) {
            $address = get_field('business_office_address', $post_id);
        }
        
        if (empty($address)) {
            return '';
        }

        ob_start();
        ?>
        <div class="job-address">
            <?php if ($atts['show_label'] === 'true'): ?>
            <span class="address-label"><?php echo esc_html($atts['label']); ?>:</span>
            <?php endif; ?>
            
            <span class="address-content">
                <?php if ($atts['show_icon'] === 'true'): ?>
                <span class="address-icon">📍</span>
                <?php endif; ?>
                
                <?php if ($atts['link_to_maps'] === 'true'): ?>
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($address); ?>" 
                   target="_blank" rel="noopener" class="address-link">
                    <?php echo esc_html($address); ?>
                </a>
                <?php else: ?>
                    <?php echo esc_html($address); ?>
                <?php endif; ?>
            </span>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Job salary shortcode
     */
    public function job_salary_shortcode($atts) {
        $atts = shortcode_atts(array(
            'format' => 'raw', // raw, uppercase, capitalize
        ), $atts);

        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $salary = get_field('job_salary_hourly_rate', $post_id);
        
        // If empty, don't show anything
        if (empty($salary)) {
            return '';
        }

        // Apply formatting based on format parameter
        $formatted_salary = $salary;
        switch ($atts['format']) {
            case 'uppercase':
                $formatted_salary = strtoupper($salary);
                break;
            case 'capitalize':
                $formatted_salary = ucwords(strtolower($salary));
                break;
            case 'raw':
            default:
                $formatted_salary = $salary; // Keep exactly as entered
                break;
        }

        ob_start();
        ?>
        <div class="job-salary">
            <span class="salary-amount"><?php echo esc_html($formatted_salary); ?></span>
        </div>
        <?php
        
        return ob_get_clean();
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
        
        // Get all job types for filter (with safety check)
        $job_types = array();
        if (taxonomy_exists('job-type')) {
            $job_types = ChelseaJobs_Queries::get_job_types();
        }
        
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
        return '<p>Job type template - coming soon</p>';
    }
    
    /**
     * Job types grid shortcode
     */
    public function job_types_grid_shortcode($atts) {
        return '<p>Job types grid - coming soon</p>';
    }
    
    /**
     * Job search bar shortcode
     */
    public function job_search_shortcode($atts) {
        return '<p>Job search - coming soon</p>';
    }
    
    /**
     * Recent jobs shortcode
     */
    public function recent_jobs_shortcode($atts) {
        return '<p>Recent jobs - coming soon</p>';
    }
    
    /**
     * Job apply button shortcode
     */
    public function job_apply_button_shortcode($atts) {
        $post_id = get_the_ID();
        if (!$post_id || get_post_type($post_id) !== 'job-listing') {
            return '';
        }

        $apply_url = get_field('job_apply_url', $post_id);
        
        if (empty($apply_url)) {
            return '';
        }

        // Determine button style based on URL type
        $button_class = 'job-apply-btn';
        $button_text = 'Apply Now';
        
        if (strpos($apply_url, 'mailto:') === 0) {
            $button_class .= ' email-apply';
            $button_text = 'Email Application';
        } elseif (strpos($apply_url, 'tel:') === 0) {
            $button_class .= ' phone-apply';
            $button_text = 'Call to Apply';
        } else {
            $button_class .= ' website-apply';
            $button_text = 'Apply Now';
        }

        ob_start();
        ?>
        <div class="job-apply-container">
            <a href="<?php echo esc_url($apply_url); ?>" 
               class="<?php echo esc_attr($button_class); ?>"
               <?php if (strpos($apply_url, 'http') === 0): ?>target="_blank" rel="noopener"<?php endif; ?>>
                <?php echo esc_html($button_text); ?>
            </a>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
/**
 * Enhanced render_job_card function - replace in your jobs plugin class-shortcodes.php
 */
private function render_job_card($atts, $show_job_types = true) {
    // Get job data with safety checks
    $job_fields = ChelseaJobs_Queries::get_job_fields();
    $job_types = ChelseaJobs_Queries::get_job_types_for_post();
    $search_content = ChelseaJobs_Queries::get_job_search_content(get_the_ID(), $job_fields, $job_types);
    
    // Get additional job info
    $job_salary = get_field('job_salary_hourly_rate');
    $job_address = get_field('job_office_address');
    $job_excerpt = get_the_excerpt();
    $posted_date = get_the_date();
    
    // If no job-specific address, try business address
    if (empty($job_address) && isset($job_fields['linked_business_data']['address'])) {
        $job_address = $job_fields['linked_business_data']['address'];
    }
    
    // Safe job types handling
    $job_type_attr = '';
    if (isset($job_types) && is_array($job_types) && isset($job_types['slugs']) && is_array($job_types['slugs'])) {
        $job_type_attr = implode(' ', $job_types['slugs']);
    }
    
    $search_attr = isset($search_content) ? $search_content : '';
    ?>
    <div class="job-card" 
         data-job-types="<?php echo esc_attr($job_type_attr); ?>"
         data-search-terms="<?php echo esc_attr($search_attr); ?>">
        
        <div class="job-content">
            <div class="job-header">
                <h3 class="job-title">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h3>
                
                <?php if (isset($job_fields['business_name']) && $job_fields['business_name']): ?>
                <div class="job-company">
                    <strong><?php echo esc_html($job_fields['business_name']); ?></strong>
                </div>
                <?php endif; ?>

                <?php if ($show_job_types && isset($job_types['names']) && !empty($job_types['names'])): ?>
                <div class="job-types">
                    <?php foreach ($job_types['names'] as $type_name): ?>
                        <span class="job-type-tag"><?php echo esc_html($type_name); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($job_excerpt) && $atts['show_excerpt'] === 'true'): ?>
            <div class="job-excerpt">
                <?php 
                // Limit excerpt length if specified
                if (!empty($atts['excerpt_length']) && is_numeric($atts['excerpt_length'])) {
                    $word_limit = intval($atts['excerpt_length']);
                    $words = explode(' ', $job_excerpt);
                    if (count($words) > $word_limit) {
                        $job_excerpt = implode(' ', array_slice($words, 0, $word_limit)) . '...';
                    }
                }
                echo wp_kses_post($job_excerpt); 
                ?>
            </div>
            <?php endif; ?>

            <div class="job-meta">
                <?php if (!empty($job_salary)): ?>
                <div class="job-salary">
                    <span class="meta-icon">💰</span>
                    <span class="salary-amount"><?php echo esc_html($job_salary); ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($job_address)): ?>
                <div class="job-location">
                    <span class="meta-icon">📍</span>
                    <span class="location-text"><?php echo esc_html($job_address); ?></span>
                </div>
                <?php endif; ?>

                <div class="job-date">
                    <span class="meta-icon">📅</span>
                    <span class="date-text">Posted <?php echo esc_html(human_time_diff(get_the_time('U'), current_time('timestamp'))); ?> ago</span>
                </div>
            </div>
            
            <div class="job-actions">
                <a href="<?php the_permalink(); ?>" class="view-job-btn">
                    View Details
                </a>
            </div>
        </div>
        
    </div>
    <?php
}
}
?>