<?php
/**
 * Chelsea Jobs Board Admin Class
 * Handles all admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChelseaJobs_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function admin_menu() {
        add_submenu_page(
            'edit.php?post_type=job-listing',
            'Jobs Board Settings',
            'Jobs Board Settings',
            'manage_options',
            'chelsea-jobs-settings',
            array($this, 'admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('chelsea_jobs_settings', 'chelsea_jobs_company_email');
        register_setting('chelsea_jobs_settings', 'chelsea_jobs_notification_email');
        
        add_settings_section(
            'chelsea_jobs_general',
            'General Settings',
            null,
            'chelsea_jobs_settings'
        );
    }
    
    public function admin_page() {
        // Handle form submission
        if (isset($_POST['submit']) && check_admin_referer('chelsea_jobs_settings_nonce', 'chelsea_jobs_nonce')) {
            update_option('chelsea_jobs_company_email', sanitize_email($_POST['chelsea_jobs_company_email']));
            update_option('chelsea_jobs_notification_email', sanitize_email($_POST['chelsea_jobs_notification_email']));
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved!</p></div>';
        }
        
        $company_email = get_option('chelsea_jobs_company_email', '');
        $notification_email = get_option('chelsea_jobs_notification_email', '');
        
        ?>
        <div class="wrap">
            <h1>Chelsea Jobs Board Settings</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('chelsea_jobs_settings_nonce', 'chelsea_jobs_nonce'); ?>
                
                <div class="card">
                    <h2>Email Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Company Email</th>
                            <td>
                                <input type="email" name="chelsea_jobs_company_email" 
                                       value="<?php echo esc_attr($company_email); ?>" 
                                       class="regular-text" />
                                <p class="description">Default email for job applications when business email is not provided.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Notification Email</th>
                            <td>
                                <input type="email" name="chelsea_jobs_notification_email" 
                                       value="<?php echo esc_attr($notification_email); ?>" 
                                       class="regular-text" />
                                <p class="description">Email address to notify when new jobs are submitted via forms.</p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Save Settings'); ?>
                </div>
            </form>
            
            <div class="card">
                <h2>Main Jobs Board Shortcode</h2>
                <p>Use this shortcode to display your complete jobs board:</p>
                <code>[jobs_board]</code>
                
                <h3>Available Parameters:</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>columns</code></td>
                            <td>1</td>
                            <td>Number of columns (1-3)</td>
                        </tr>
                        <tr>
                            <td><code>posts_per_page</code></td>
                            <td>-1</td>
                            <td>Number of jobs to show (-1 for all)</td>
                        </tr>
                        <tr>
                            <td><code>job_type</code></td>
                            <td></td>
                            <td>Show only specific job type (use type slug)</td>
                        </tr>
                        <tr>
                            <td><code>show_filter</code></td>
                            <td>true</td>
                            <td>Show job type filter dropdown (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>show_search</code></td>
                            <td>true</td>
                            <td>Show search box (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>show_excerpt</code></td>
                            <td>true</td>
                            <td>Show job description excerpt (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>orderby</code></td>
                            <td>date</td>
                            <td>Sort by: date, title, menu_order, rand</td>
                        </tr>
                        <tr>
                            <td><code>order</code></td>
                            <td>DESC</td>
                            <td>Sort order: ASC or DESC</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Job Type Template Shortcode</h2>
                <p>Use this shortcode in your Divi Job Type template:</p>
                <code>[job_type_template]</code>
                
                <h3>Available Parameters:</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>columns</code></td>
                            <td>1</td>
                            <td>Number of columns (1-3)</td>
                        </tr>
                        <tr>
                            <td><code>posts_per_page</code></td>
                            <td>-1</td>
                            <td>Number of jobs to show (-1 for all)</td>
                        </tr>
                        <tr>
                            <td><code>show_search</code></td>
                            <td>true</td>
                            <td>Show search box (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>orderby</code></td>
                            <td>date</td>
                            <td>Sort by: date, title, menu_order, rand</td>
                        </tr>
                        <tr>
                            <td><code>order</code></td>
                            <td>DESC</td>
                            <td>Sort order: ASC or DESC</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Job Types Grid Shortcode</h2>
                <p>Use this shortcode to display a grid of job types:</p>
                <code>[job_types_grid]</code>
                
                <h3>Available Parameters:</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>columns</code></td>
                            <td>4</td>
                            <td>Number of columns (1-6)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Job Search Shortcode</h2>
                <p>Use this shortcode to display a search bar:</p>
                <code>[job_search]</code>
                
                <h3>Available Parameters:</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>placeholder</code></td>
                            <td>Search for jobs, companies, or job types...</td>
                            <td>Placeholder text in search box</td>
                        </tr>
                        <tr>
                            <td><code>button_text</code></td>
                            <td>Search Jobs</td>
                            <td>Text on search button</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Recent Jobs Widget Shortcode</h2>
                <p>Use this shortcode to display recent jobs in sidebars or widgets:</p>
                <code>[recent_jobs]</code>
                
                <h3>Available Parameters:</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>limit</code></td>
                            <td>5</td>
                            <td>Number of jobs to show</td>
                        </tr>
                        <tr>
                            <td><code>show_date</code></td>
                            <td>true</td>
                            <td>Show posting date (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>show_company</code></td>
                            <td>true</td>
                            <td>Show company name (true/false)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Single Job Page Shortcodes</h2>
                <p>Use these shortcodes on individual job listing pages in Divi modules:</p>
                
                <h3>Job Contact Information</h3>
                <code>[job_contact_info]</code>
                
                <h4>Available Parameters:</h4>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>style</code></td>
                            <td>default</td>
                            <td>Style: default, card, minimal</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Job Business Information</h3>
                <code>[job_business_info]</code>
                
                <h4>Available Parameters:</h4>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>show_description</code></td>
                            <td>true</td>
                            <td>Show business description (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>show_website</code></td>
                            <td>true</td>
                            <td>Show website link (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>show_address</code></td>
                            <td>true</td>
                            <td>Show business address (true/false)</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Job Apply Button</h3>
                <code>[job_apply_button]</code>
                
                <h4>Available Parameters:</h4>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>text</code></td>
                            <td>Apply Now</td>
                            <td>Button text</td>
                        </tr>
                        <tr>
                            <td><code>style</code></td>
                            <td>email</td>
                            <td>Action: email, phone, website</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Example Usage</h2>
                
                <h3>Main Jobs Board Examples:</h3>
                <p><strong>Basic jobs board:</strong><br>
                <code>[jobs_board]</code></p>
                
                <p><strong>Full-time jobs only:</strong><br>
                <code>[jobs_board job_type="full-time"]</code></p>
                
                <p><strong>No filters, most recent first:</strong><br>
                <code>[jobs_board show_filter="false" show_search="false" orderby="date"]</code></p>
                
                <h3>Search Examples:</h3>
                <p><strong>Basic search bar:</strong><br>
                <code>[job_search]</code></p>
                
                <p><strong>Custom text:</strong><br>
                <code>[job_search placeholder="Find your next job..." button_text="Find Jobs"]</code></p>
                
                <h3>Single Job Page Examples:</h3>
                <p><strong>Contact info card:</strong><br>
                <code>[job_contact_info style="card"]</code></p>
                
                <p><strong>Business info without description:</strong><br>
                <code>[job_business_info show_description="false"]</code></p>
                
                <p><strong>Apply by phone button:</strong><br>
                <code>[job_apply_button style="phone" text="Call to Apply"]</code></p>
                
                <h3>Widget Examples:</h3>
                <p><strong>Recent jobs widget:</strong><br>
                <code>[recent_jobs limit="3"]</code></p>
                
                <p><strong>Job types grid:</strong><br>
                <code>[job_types_grid columns="3"]</code></p>
            </div>
            
            <div class="card">
                <h2>ACF Field Mapping</h2>
                <p>The plugin automatically reads these ACF field names:</p>
                <ul>
                    <li><strong>Business Name:</strong> 'business_name'</li>
                    <li><strong>Business Description:</strong> 'business_description'</li>
                    <li><strong>Contact Name:</strong> 'contact_name'</li>
                    <li><strong>Contact Email:</strong> 'contact_email'</li>
                    <li><strong>Contact Phone:</strong> 'contact_phone'</li>
                    <li><strong>Business Address:</strong> 'business_office_address'</li>
                    <li><strong>Business Website:</strong> 'business_website'</li>
                </ul>
                <p><em>Make sure your ACF field names match these exactly for automatic integration.</em></p>
            </div>
            
            <div class="card">
                <h2>Integration with Business Directory</h2>
                <p>The job board can work alongside your Business Directory plugin:</p>
                <ul>
                    <li>Cross-link between business listings and their job postings</li>
                    <li>Use similar design patterns for consistency</li>
                    <li>Shared search functionality across both directories</li>
                </ul>
                
                <h3>Lead Generation Opportunities</h3>
                <p>Every job submission is a potential client for your web development business:</p>
                <ul>
                    <li>Review process gives you direct contact opportunity</li>
                    <li>Business information capture for follow-up</li>
                    <li>Natural entry point for web services discussion</li>
                </ul>
            </div>
            
            <div class="card">
                <h2>Setup Checklist</h2>
                <ol>
                    <li>✅ Job Listings post type (you have this)</li>
                    <li>✅ Job Types taxonomy (you have this)</li>
                    <li>✅ ACF fields for job information (you have this)</li>
                    <li>Create a main jobs page and add <code>[jobs_board]</code></li>
                    <li>Set up Divi Job Type template with <code>[job_type_template]</code></li>
                    <li>Add job search to homepage with <code>[job_search]</code></li>
                    <li>Use field shortcodes in individual job templates</li>
                    <li>Set up Gravity Forms for job submissions</li>
                    <li>Configure email notifications for new submissions</li>
                </ol>
            </div>
        </div>
        <?php
    }
    
    public function admin_notices() {
        // Check if ACF is active
        if (!function_exists('get_field')) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong>Chelsea Jobs Board:</strong> Advanced Custom Fields plugin is required for full functionality.</p>
            </div>
            <?php
        }
        
        // Check if job listings post type exists
        if (!post_type_exists('job-listing')) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>Chelsea Jobs Board:</strong> "Job Listings" post type not found. Please make sure it's properly registered.</p>
            </div>
            <?php
        }
        
        // Check if job types taxonomy exists
        if (!taxonomy_exists('job-type')) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>Chelsea Jobs Board:</strong> "Job Types" taxonomy not found. Please make sure it's properly registered.</p>
            </div>
            <?php
        }
    }
}
?>