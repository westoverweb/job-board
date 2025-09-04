<?php
/**
 * Plugin Name: Chelsea Jobs Board
 * Plugin URI: https://chelseabusiness.com
 * Description: A complete job board solution with search and filtering capabilities for ACF-powered job listings.
 * Version: 1.0.0
 * Author: Ben DeLoach
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CHELSEA_JOBS_VERSION', '1.0.0');
define('CHELSEA_JOBS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CHELSEA_JOBS_PLUGIN_URL', plugin_dir_url(__FILE__));

class ChelseaJobsBoardPlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load files first
        $this->load_includes();
        
        // Then initialize classes
        if (class_exists('ChelseaJobs_Shortcodes')) {
            new ChelseaJobs_Shortcodes();
        }
        if (class_exists('ChelseaJobs_Admin')) {
            new ChelseaJobs_Admin();
        }
        
        // Debug shortcode registration
        if (shortcode_exists('jobs_board')) {
            error_log('Chelsea Jobs: jobs_board shortcode registered successfully');
        } else {
            error_log('Chelsea Jobs: jobs_board shortcode NOT registered');
        }
        
        if (shortcode_exists('job_type_template')) {
            error_log('Chelsea Jobs: job_type_template shortcode registered successfully');
        } else {
            error_log('Chelsea Jobs: job_type_template shortcode NOT registered');
        }
    }
    
    public function load_includes() {
        // Load files in correct order
        require_once CHELSEA_JOBS_PLUGIN_DIR . 'includes/class-queries.php';
        require_once CHELSEA_JOBS_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once CHELSEA_JOBS_PLUGIN_DIR . 'includes/class-admin.php';
        
        // Debug: Check if files exist
        if (!class_exists('ChelseaJobs_Queries')) {
            error_log('Chelsea Jobs: class-queries.php not loaded properly');
        }
        if (!class_exists('ChelseaJobs_Shortcodes')) {
            error_log('Chelsea Jobs: class-shortcodes.php not loaded properly');
        }
        if (!class_exists('ChelseaJobs_Admin')) {
            error_log('Chelsea Jobs: class-admin.php not loaded properly');
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style(
            'chelsea-jobs-style',
            CHELSEA_JOBS_PLUGIN_URL . 'assets/style.css',
            array(),
            CHELSEA_JOBS_VERSION
        );
        
        wp_enqueue_script(
            'chelsea-jobs-script',
            CHELSEA_JOBS_PLUGIN_URL . 'assets/script.js',
            array('jquery'),
            CHELSEA_JOBS_VERSION,
            true
        );
    }
    
    public function activate() {
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new ChelseaJobsBoardPlugin();
?>