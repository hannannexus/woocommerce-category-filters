<div class="wcf-selected-category-container">
    <?php if ($current_category) : ?>
        <div class="wcf-selected-category" data-category-id="<?php echo $current_category['current']->term_id; ?>">
            <h2 class="wcf-category-title">
                <?php if ($current_category['parent']) : ?>
                    <a href="<?php echo get_term_link($current_category['parent']); ?>" class="wcf-parent-category">
                        <?php echo $current_category['parent']->name; ?>
                    </a>
                    <span class="wcf-category-separator">/</span>
                <?php endif; ?>
                
                <span class="wcf-current-category">
                    <?php echo $current_category['current']->name; ?>
                </span>
            </h2>
            
            <?php if (term_description($current_category['current']->term_id, 'product_cat')) : ?>
                <div class="wcf-category-description">
                    <?php echo term_description($current_category['current']->term_id, 'product_cat'); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <div class="wcf-selected-category" data-category-id="0">
            <h2 class="wcf-category-title">
                <span class="wcf-current-category">All Products</span>
            </h2>
        </div>
    <?php endif; ?>
</div>