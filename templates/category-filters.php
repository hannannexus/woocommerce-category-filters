<form class="wcf-filter-form" data-auto-submit="true" data-ajax-enabled="true">
    
    <div class="wcf-filter-section">
        <h3 class="wcf-filter-title">Categories</h3>
        
        <div class="wcf-select-all-container">
            <label class="wcf-select-all">
                <input type="checkbox" id="wcf-select-all-categories" class="wcf-select-all-checkbox"> Select All
            </label>
        </div>
        
        <?php
        $current_category_id = $current_category ? $current_category['current']->term_id : 0;
        
        // Get all top-level categories
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'parent' => 0,
            'hide_empty' => true
        ));
        
        foreach ($categories as $category) :
            $subcategories = get_terms(array(
                'taxonomy' => 'product_cat',
                'parent' => $category->term_id,
                'hide_empty' => true
            ));
            
            $is_current_parent = $current_category && $current_category['parent'] && $current_category['parent']->term_id == $category->term_id;
            $is_current = $current_category && $current_category['current']->term_id == $category->term_id;
            ?>
            
            <div class="wcf-category-group <?php echo $is_current_parent || $is_current ? 'active' : ''; ?>">
                <label class="wcf-category-parent">
                    <input type="checkbox" name="filter_category[]" value="<?php echo $category->term_id; ?>" class="wcf-category-checkbox"
                        <?php checked($is_current || $is_current_parent); ?>>
                    <?php echo $category->name; ?>
                    <?php if ($subcategories) : ?>
                        <span class="wcf-toggle-subcategories"></span>
                    <?php endif; ?>
                </label>
                
                <?php if ($subcategories) : ?>
                    <div class="wcf-subcategories" style="<?php echo $is_current_parent ? 'display: block;' : 'display: none;'; ?>">
                        <?php foreach ($subcategories as $subcategory) : ?>
                            <label class="wcf-category-child">
                                <input type="checkbox" name="filter_category[]" value="<?php echo $subcategory->term_id; ?>" class="wcf-category-checkbox"
                                    <?php checked($current_category && $current_category['current']->term_id == $subcategory->term_id); ?>>
                                <?php echo $subcategory->name; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="wcf-filter-actions">
        <button type="submit" class="button wcf-apply-filters">Apply Filters</button>
        <button type="reset" class="button wcf-reset-filters">Reset</button>
    </div>
</form>