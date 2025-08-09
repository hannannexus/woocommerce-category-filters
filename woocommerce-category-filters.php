<?php
/*
Plugin Name: WooCommerce Category Filters
Description: Advanced category and subcategory filtering for WooCommerce with title display
Version: 1.0
Author: Hannan
*/

defined('ABSPATH') or die('Direct access not allowed');

// Define plugin constants
define('WCF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WCF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>WooCommerce Category Filters requires WooCommerce to be installed and active.</p></div>';
    });
    return;
}

// Include necessary files
require_once WCF_PLUGIN_PATH . 'includes/class-category-filters.php';
require_once WCF_PLUGIN_PATH . 'includes/class-ajax-handler.php';
require_once WCF_PLUGIN_PATH . 'includes/class-shortcodes.php';

// Initialize the plugin
function wcf_init() {
    new WCF_Category_Filters();
    new WCF_Ajax_Handler();
    new WCF_Shortcodes();
}
add_action('plugins_loaded', 'wcf_init');

// Add body class for Woodmart theme detection
function wcf_add_woodmart_body_class($classes) {
    // Check for Woodmart theme in multiple ways
    if (function_exists('woodmart_get_theme_info') ||
        (function_exists('wp_get_theme') &&
         (wp_get_theme()->get('Name') === 'Woodmart' ||
          wp_get_theme()->get('Template') === 'woodmart'))) {
        $classes[] = 'woodmart-theme';
    }
    return $classes;
}
add_filter('body_class', 'wcf_add_woodmart_body_class');