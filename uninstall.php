<?php
/**
 * Uninstall script for Signalfire Auto Featured plugin
 *
 * This file is executed when the plugin is uninstalled via the WordPress admin.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('saf_settings');

// For multisite, delete options for all sites
if (is_multisite()) {
    $sites = get_sites(array('fields' => 'ids'));
    $original_blog_id = get_current_blog_id();
    
    foreach ($sites as $blog_id) {
        switch_to_blog($blog_id);
        delete_option('saf_settings');
    }
    
    switch_to_blog($original_blog_id);
}

// Clear any cached data that has been stored
wp_cache_flush();