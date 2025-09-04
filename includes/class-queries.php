<?php

if (!defined('ABSPATH')) {
    exit;
}

class ChelseaJobs_Queries {
    
    public static function get_jobs($args = array()) {
        $defaults = array(
            'posts_per_page' => -1,
            'post_type' => 'job-listing',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'job_type' => '',
            'featured_first' => false,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = array(
            'post_type' => $args['post_type'],
            'posts_per_page' => $args['posts_per_page'],
            'post_status' => $args['post_status'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
        );
        
        if (!empty($args['job_type'])) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'job-type',
                    'field' => 'slug',
                    'terms' => $args['job_type'],
                ),
            );
        }
        
        return new WP_Query($query_args);
    }
    
    public static function get_jobs_by_type($type_id, $args = array()) {
        $defaults = array(
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = array(
            'post_type' => 'job-listing',
            'posts_per_page' => $args['posts_per_page'],
            'post_status' => 'publish',
            'orderby' => $args['orderby'],
            'order' => $args['order'],
            'tax_query' => array(
                array(
                    'taxonomy' => 'job-type',
                    'field' => 'term_id',
                    'terms' => $type_id,
                ),
            ),
        );
        
        return new WP_Query($query_args);
    }
    
    public static function get_job_types() {
        return get_terms(array(
            'taxonomy' => 'job-type',
            'hide_empty' => true,
        ));
    }
    
    public static function get_job_fields($post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        // Get the linked business (relationship field)
        $linked_business = get_field('business_name', $post_id);
        $business_name_text = '';
        $business_data = array();
        
        if ($linked_business && !empty($linked_business)) {
            $business_post = is_array($linked_business) ? $linked_business[0] : $linked_business;
            $business_name_text = $business_post->post_title;
            
            // Get business data from the linked business listing
            if (class_exists('BusinessDirectory_Queries')) {
                $business_data = BusinessDirectory_Queries::get_business_fields($business_post->ID);
            } else {
                // Fallback if business directory not available
                $business_data = array(
                    'phone' => get_field('phone', $business_post->ID) ?: get_field('business_phone', $business_post->ID),
                    'email' => get_field('email', $business_post->ID) ?: get_field('business_email', $business_post->ID),
                    'website' => get_field('website', $business_post->ID) ?: get_field('business_website', $business_post->ID),
                    'address' => get_field('address', $business_post->ID) ?: get_field('business_address', $business_post->ID),
                    'logo' => get_field('logo', $business_post->ID) ?: get_field('business_logo', $business_post->ID),
                    'description' => get_field('description', $business_post->ID) ?: get_field('business_description', $business_post->ID),
                );
            }
        }
        
        return array(
            'business_name' => $business_name_text,
            'linked_business_object' => $linked_business,
            'linked_business_data' => $business_data,
            'business_description' => get_field('business_description', $post_id),
            'contact_name' => get_field('contact_name', $post_id),
            'contact_email' => get_field('contact_email', $post_id),
            'contact_phone' => get_field('contact_phone', $post_id),
            'business_office_address' => get_field('business_office_address', $post_id),
            'business_website' => get_field('business_website', $post_id),
        );
    }
    
    public static function get_job_types_for_post($post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        $job_types = get_the_terms($post_id, 'job-type');
        $type_data = array(
            'names' => array(),
            'slugs' => array(),
        );
        
        if ($job_types && !is_wp_error($job_types)) {
            foreach ($job_types as $type) {
                $type_data['names'][] = $type->name;
                $type_data['slugs'][] = $type->slug;
            }
        }
        
        return $type_data;
    }
    
    public static function get_job_search_content($post_id = null, $job_fields = null, $job_types = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        if (!$job_fields) {
            $job_fields = self::get_job_fields($post_id);
        }
        
        if (!$job_types) {
            $job_types = self::get_job_types_for_post($post_id);
        }
        
        $search_content = get_the_title($post_id) . ' ' . get_the_content(null, false, $post_id);
        
        if ($job_fields['business_name']) {
            $search_content .= ' ' . $job_fields['business_name'];
        }
        
        if ($job_fields['business_description']) {
            $search_content .= ' ' . $job_fields['business_description'];
        }
        
        if ($job_fields['business_office_address']) {
            $search_content .= ' ' . $job_fields['business_office_address'];
        }
        
        $search_content .= ' ' . implode(' ', $job_types['names']);
        
        return strtolower($search_content);
    }
    
    public static function get_recent_jobs($limit = 5) {
        return self::get_jobs(array(
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
        ));
    }
    
    public static function get_job_count_by_type() {
        $types = self::get_job_types();
        $counts = array();
        
        foreach ($types as $type) {
            $counts[$type->slug] = $type->count;
        }
        
        return $counts;
    }
}

?>