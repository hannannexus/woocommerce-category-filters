jQuery(document).ready(function($) {
    // Handle filter form submission - works for both sidebar and shortcode
    $(document).on('submit change', '.wcf-filter-form', function(e) {
        var form = $(this);
        var currentCategoryId = $('.wcf-selected-category').data('category-id') || 0;
        // Check for ajax-enabled attribute on form or closest parent
        var ajaxEnabled = true;
        if (form.attr('data-ajax-enabled') !== undefined) {
            var ajaxAttr = form.attr('data-ajax-enabled');
            ajaxEnabled = ajaxAttr !== 'false' && ajaxAttr !== false;
        } else if (form.closest('[data-ajax-enabled]').length) {
            var parentAjax = form.closest('[data-ajax-enabled]').data('ajax-enabled');
            ajaxEnabled = parentAjax !== false && parentAjax !== 'false';
        }
        var autoSubmit = form.data('auto-submit') === true || form.attr('data-auto-submit') === 'true';
        var isCheckboxChange = (e.type === 'change' && $(e.target).hasClass('wcf-category-checkbox'));

        // Prevent default for form submission and non-checkbox changes
        if (e.type === 'submit' || (e.type === 'change' && !isCheckboxChange)) {
            e.preventDefault();
        }

        // If AJAX is disabled, just submit the form normally (but not for checkbox changes in auto-submit forms)
        if (!ajaxEnabled) {
            if (e.type === 'submit' || (e.type === 'change' && !isCheckboxChange)) {
                form[0].submit();
            }
            return;
        }

        // For auto-submit forms, only proceed if it's a checkbox change or form submission
        if (autoSubmit && e.type === 'change' && !isCheckboxChange) {
            return;
        }
        
        // For non-auto-submit forms, prevent processing on checkbox changes
        if (!autoSubmit && isCheckboxChange) {
            return;
        }

        // Get selected categories
        var selectedCategories = form.find('[name="filter_category[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        // If no categories selected and it's not a checkbox change, skip processing
        // But allow processing when it's a checkbox change even if no categories are selected
        if (selectedCategories.length === 0 && !isCheckboxChange) {
            return;
        }
        
        // Check if wcf_ajax object exists before making AJAX call
        if (typeof wcf_ajax === 'undefined') {
            console.warn('WCF: wcf_ajax object not found, AJAX filtering disabled');
            return;
        }
        
        $.ajax({
            url: wcf_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wcf_filter_products',
                nonce: wcf_ajax.nonce,
                filter_category: selectedCategories,
                current_category_id: currentCategoryId
            },
            beforeSend: function() {
                // Update loading indicators for different selectors
                var $productsContainers = $('.products, .products-wrapper, .shop-container, .woocommerce-products, .woodmart-shop-content, .products-container');
                $productsContainers.addClass('loading');
                $('.wcf-filter-sidebar, .wcf-filter-shortcode').addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    // Update products - handle different selectors for Woodmart theme
                    var $productsContainer = $('.products, .products-wrapper, .shop-container, .woocommerce-products, .woodmart-shop-content, .products-container');
                    if ($productsContainer.length) {
                        $productsContainer.first().html(response.data.html);
                        // Update result count - handle different selectors for Woodmart theme
                        var $resultCount = $('.woocommerce-result-count, .products-found, .woodmart-products-count');
                        if ($resultCount.length) {
                            $resultCount.text(response.data.count + ' products found');
                        }
                    }
                    
                    // Update title in both sidebar and shortcode
                    form.find('.wcf-selected-category-container').html(response.data.title_html);
                    
                    // Update title above products if it exists - handle Woodmart theme selectors
                    var $titleContainers = $('.wcf-selected-category-container:not(.wcf-filter-form .wcf-selected-category-container), .woodmart-title-container, .page-title, .category-title, .woodmart-page-title');
                    if ($titleContainers.length) {
                        $titleContainers.first().html(response.data.title_html);
                    }
                    
                    // Also update any wrapper containers
                    $('.wcf-selected-category-container-wrapper').html(response.data.title_html);
                    
                    // Update title in the specific Woodmart theme container
                    var $woodmartTitleContainer = $('.liner-continer .woodmart-title-container.title.wd-fontsize-l');
                    if ($woodmartTitleContainer.length) {
                        // Extract just the text content from the title_html and update the container
                        var tempDiv = $('<div>').html(response.data.title_html);
                        var titleText = tempDiv.find('.wcf-current-category').text() || tempDiv.text();
                        $woodmartTitleContainer.text(titleText);
                    }
                    
                    // Update filter sidebar for Woodmart theme compatibility
                    var $filterSidebar = $('.wcf-filter-sidebar');
                    if ($filterSidebar.length && response.data.filter_html) {
                        $filterSidebar.html(response.data.filter_html);
                    }
                    
                    // Update URL without reload
                    if (history.pushState) {
                        if (selectedCategories.length > 0) {
                            var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + $.param({
                                'filter_category': selectedCategories
                            });
                        } else {
                            var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        }
                        window.history.pushState({path:newurl}, '', newurl);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('WCF: AJAX error', xhr, status, error);
            },
            complete: function() {
                // Remove loading indicators for different selectors
                var $productsContainers = $('.products, .products-wrapper, .shop-container, .woocommerce-products, .woodmart-shop-content, .products-container');
                $productsContainers.removeClass('loading');
                $('.wcf-filter-sidebar, .wcf-filter-shortcode').removeClass('loading');
            }
        });
    });
    
    // Handle checkbox changes for auto-submit forms
    $(document).on('change', '.wcf-category-checkbox', function() {
        var form = $(this).closest('.wcf-filter-form');
        var autoSubmit = form.data('auto-submit') === true || form.attr('data-auto-submit') === 'true';
        if (autoSubmit) {
            // Trigger the form's change event instead of submit to ensure proper handling
            form.trigger('change');
        }
    });
    
    // Handle reset button
    $(document).on('click', '.wcf-reset-filters', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        form[0].reset();
        form.trigger('submit');
    });
    
    // Add a safety check for the wcf_ajax object to prevent warnings
    // This ensures that the AJAX call only proceeds if the object is properly defined
    if (typeof wcf_ajax === 'undefined') {
        console.warn('WCF: wcf_ajax object not found. AJAX filtering will be disabled.');
    }
    
    // Handle select all checkbox
    $(document).on('change', '#wcf-select-all-categories', function() {
        var isChecked = $(this).is(':checked');
        $('.wcf-category-checkbox').prop('checked', isChecked);
        
        // Trigger the form's change event to update products
        var form = $(this).closest('.wcf-filter-form');
        form.trigger('change');
    });
    
    // Handle individual checkbox changes to update select all checkbox state
    $(document).on('change', '.wcf-category-checkbox', function() {
        var form = $(this).closest('.wcf-filter-form');
        var allCheckboxes = form.find('.wcf-category-checkbox');
        var checkedCheckboxes = form.find('.wcf-category-checkbox:checked');
        
        // Update select all checkbox state
        $('#wcf-select-all-categories').prop('checked', allCheckboxes.length === checkedCheckboxes.length);
    });
});