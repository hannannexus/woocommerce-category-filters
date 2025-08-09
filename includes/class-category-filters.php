<?php
class WCF_Category_Filters {
    public function __construct() {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Add filter sidebar
        add_action('woocommerce_before_shop_loop', array($this, 'display_filter_sidebar'), 20);
        // Also add filter sidebar for Woodmart theme
        add_action('woodmart_before_shop_loop', array($this, 'display_filter_sidebar'), 20);
        
        // Add category title above products
        add_action('woocommerce_before_shop_loop', array($this, 'display_category_title_above_products'), 1);
        // Also add category title above products for Woodmart theme
        add_action('woodmart_before_shop_loop', array($this, 'display_category_title_above_products'), 1);
        
        // Add category title at the very top of the shop section
        add_action('woocommerce_before_main_content', array($this, 'display_category_title_at_top'), 5);
        // Also add category title at the very top for Woodmart theme
        add_action('woodmart_before_main_content', array($this, 'display_category_title_at_top'), 5);
        
        // Modify product query
        add_action('woocommerce_product_query', array($this, 'modify_product_query'));
    }
    
    public function enqueue_assets() {
        // Always enqueue assets to ensure they're available for shortcodes
        // This might be slightly less efficient but ensures compatibility
        wp_enqueue_style('wcf-frontend', WCF_PLUGIN_URL . 'assets/css/frontend.css');
        wp_enqueue_script('wcf-frontend', WCF_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.0', true);
        
        wp_localize_script('wcf-frontend', 'wcf_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcf-nonce')
        ));
    }
    
    public function display_filter_sidebar() {
        // Get current category for title display
        $current_category = $this->get_current_category();
        
        // Display selected category title above the filter container
        echo '<div class="wcf-selected-category-container-wrapper">';
        wc_get_template('selected-category-title.php', array(
            'current_category' => $current_category
        ), '', WCF_PLUGIN_PATH . 'templates/');
        echo '</div>';
        
        echo '<div class="wcf-filter-sidebar">';
        
        // Display category filters (without the title inside)
        wc_get_template('category-filters.php', array(
            'current_category' => $current_category
        ), '', WCF_PLUGIN_PATH . 'templates/');
        
        echo '</div>';
    }
    
    public function display_category_title_above_products() {
        $current_category = $this->get_current_category();
        if ($current_category) {
            echo '<div class="wcf-selected-category-container-wrapper">';
            wc_get_template('selected-category-title.php', array(
                'current_category' => $current_category
            ), '', WCF_PLUGIN_PATH . 'templates/');
            echo '</div>';
        }
    }
    
    public function display_category_title_at_top() {
        // Only display on shop pages
        if (is_shop() || is_product_category() || is_product_tag()) {
            $current_category = $this->get_current_category();
            if ($current_category) {
                echo '<div class="wcf-selected-category-container-wrapper">';
                wc_get_template('selected-category-title.php', array(
                    'current_category' => $current_category
                ), '', WCF_PLUGIN_PATH . 'templates/');
                echo '</div>';
            }
        }
    }
    
    public function display_selected_category_title() {
        $current_category = $this->get_current_category();
        echo '<div class="wcf-selected-category-container-wrapper">';
        wc_get_template('selected-category-title.php', array(
            'current_category' => $current_category
        ), '', WCF_PLUGIN_PATH . 'templates/');
        echo '</div>';
    }
    
    public function get_current_category() {
        if (is_product_category()) {
            $current_category = get_queried_object();
            
            // Get parent category if this is a subcategory
            if ($current_category->parent) {
                $parent_category = get_term($current_category->parent, 'product_cat');
                return array(
                    'parent' => $parent_category,
                    'current' => $current_category
                );
            }
            
            return array(
                'parent' => null,
                'current' => $current_category
            );
        }
        
        return null;
    }
    
    public function modify_product_query($q) {
        if (!is_admin() && $q->is_main_query()) {
            // Handle category filter from GET parameters
            if (!empty($_GET['filter_category'])) {
                // Handle both array and string cases
                if (is_array($_GET['filter_category'])) {
                    $category_ids = array_map('intval', $_GET['filter_category']);
                } else {
                    $category_ids = array_map('intval', explode(',', $_GET['filter_category']));
                }
                $q->set('tax_query', $this->get_category_filter_tax_query($category_ids));
            } else {
                // If no categories are selected, show all products by getting all category IDs
                $all_category_ids = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => false,
                    'fields' => 'ids'
                ));
                
                $q->set('tax_query', array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $all_category_ids,
                        'operator' => 'IN'
                    )
                ));
            }
        }
    }
    
    private function get_category_filter_tax_query($category_ids) {
        return array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_ids,
                'operator' => 'IN'
            )
        );
    }
}