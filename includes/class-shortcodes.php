<?php
class WCF_Shortcodes {

    public function __construct() {
        add_shortcode('woocommerce_category_filters', array($this, 'category_filters_shortcode'));
        add_shortcode('woocommerce_selected_category_title', array($this, 'selected_category_title_shortcode'));
    }

    /**
     * Main category filters shortcode
     */
    public function category_filters_shortcode($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(array(
            'show_title' => true,
            'show_reset' => true,
            'ajax_enabled' => true
        ), $atts, 'woocommerce_category_filters');

        // Start output buffering
        ob_start();

        // Initialize the filters class if not already loaded
        if (!class_exists('WCF_Category_Filters')) {
            require_once WCF_PLUGIN_PATH . 'includes/class-category-filters.php';
            $filters = new WCF_Category_Filters();
        } else {
            $filters = new WCF_Category_Filters();
        }

        // Get current category
        $current_category = $filters->get_current_category();

        // Display the filter form
        echo '<div class="wcf-filter-shortcode" data-ajax-enabled="' . esc_attr($atts['ajax_enabled'] ? 'true' : 'false') . '">';
        
        // Display selected category title above the filter container for shortcodes
        echo '<div class="wcf-selected-category-container-wrapper">';
        wc_get_template('selected-category-title.php', array(
            'current_category' => $current_category
        ), '', WCF_PLUGIN_PATH . 'templates/');
        echo '</div>';
        
        // Use wc_get_template to properly pass variables to the template
        wc_get_template('category-filters.php', array(
            'current_category' => $current_category
        ), '', WCF_PLUGIN_PATH . 'templates/');
        
        if ($atts['show_reset']) {
            echo '<button type="reset" class="button wcf-reset-filters">' . __('Reset Filters', 'woocommerce') . '</button>';
        }
        
        echo '</div>';

        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Selected category title shortcode
     */
    public function selected_category_title_shortcode() {
        ob_start();
        
        if (!class_exists('WCF_Category_Filters')) {
            require_once WCF_PLUGIN_PATH . 'includes/class-category-filters.php';
            $filters = new WCF_Category_Filters();
        } else {
            $filters = new WCF_Category_Filters();
        }
        
        $filters->display_selected_category_title();
        
        return ob_get_clean();
    }
}