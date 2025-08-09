<?php
class WCF_Ajax_Handler {
    public function __construct() {
        add_action('wp_ajax_wcf_filter_products', array($this, 'filter_products'));
        add_action('wp_ajax_nopriv_wcf_filter_products', array($this, 'filter_products'));
    }
    
    public function filter_products() {
        check_ajax_referer('wcf-nonce', 'nonce');
        
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => wc_get_default_products_per_row() * wc_get_default_product_rows_per_page(),
            'tax_query' => array()
        );
        
        // Handle category filter
        if (!empty($_POST['filter_category'])) {
            $category_ids = array_map('intval', $_POST['filter_category']);
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_ids,
                    'operator' => 'IN'
                )
            );
        } else {
            // If no categories are selected, show all products by getting all category IDs
            $all_category_ids = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'fields' => 'ids'
            ));
            
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $all_category_ids,
                    'operator' => 'IN'
                )
            );
        }
        
        // Get current category info for title display
        $current_category = null;
        if (!empty($_POST['filter_category'])) {
            // If filters are applied, use the first selected category for title display
            $category_ids = array_map('intval', $_POST['filter_category']);
            $current_category_id = $category_ids[0]; // Use the first selected category
            $current_category = get_term($current_category_id, 'product_cat');
        } else if (!empty($_POST['current_category_id'])) {
            // If no filters are applied, use the original category
            $current_category_id = intval($_POST['current_category_id']);
            $current_category = get_term($current_category_id, 'product_cat');
        }
        
        // Format current_category data to match what the template expects
        $formatted_category = null;
        if ($current_category) {
            if ($current_category->parent) {
                $parent_category = get_term($current_category->parent, 'product_cat');
                $formatted_category = array(
                    'parent' => $parent_category,
                    'current' => $current_category
                );
            } else {
                $formatted_category = array(
                    'parent' => null,
                    'current' => $current_category
                );
            }
        }
        
        $products = new WP_Query($args);
        
        // Start output buffering for the entire product grid
        ob_start();
        
        // Add the opening wrapper for the product grid
        $columns = wc_get_loop_prop('columns', wc_get_default_products_per_row());
        echo '<ul class="products columns-' . esc_attr($columns) . '">';
        
        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                
                // Check if Woodmart theme is active
                $is_woodmart = function_exists('woodmart_get_theme_info') ||
                    (function_exists('wp_get_theme') &&
                     (wp_get_theme()->get('Name') === 'Woodmart' ||
                      wp_get_theme()->get('Template') === 'woodmart'));
                
                if ($is_woodmart) {
                    // Use Woodmart's product template if available
                    wc_get_template('content-product.php', array(
                        'product' => wc_get_product(get_the_ID())
                    ), '', get_template_directory() . '/woocommerce/');
                } else {
                    // Use default WooCommerce product template
                    wc_get_template_part('content', 'product');
                }
            }
        } else {
            echo '<li class="no-products">' . __('No products found', 'woocommerce') . '</li>';
        }
        
        // Add the closing wrapper for the product grid
        echo '</ul>';
        
        wp_reset_postdata();
        
        $output = ob_get_clean();
        
        // Get updated category title HTML
        ob_start();
        wc_get_template('selected-category-title.php', array(
            'current_category' => $formatted_category
        ), '', WCF_PLUGIN_PATH . 'templates/');
        $title_html = ob_get_clean();
        
        // Get filter sidebar HTML for Woodmart theme compatibility
        ob_start();
        echo '<div class="wcf-selected-category-container-wrapper">';
        wc_get_template('selected-category-title.php', array(
            'current_category' => $formatted_category
        ), '', WCF_PLUGIN_PATH . 'templates/');
        echo '</div>';
        
        wc_get_template('category-filters.php', array(
            'current_category' => $formatted_category
        ), '', WCF_PLUGIN_PATH . 'templates/');
        $filter_html = ob_get_clean();
        
        wp_send_json_success(array(
            'html' => $output,
            'title_html' => $title_html,
            'filter_html' => $filter_html,
            'count' => $products->found_posts
        ));
    }
}