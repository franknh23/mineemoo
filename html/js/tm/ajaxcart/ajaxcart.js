;(function ( $, window, document, undefined ) {

    "use strict";

    var pluginName = "tmAjaxcart",
        defaults = {
            ajaxcartLink:                   '.ajaxcart-link', //selector for minicart link
            ajaxcartContent:                '#ajaxcart-cart-content', //selector for minicart content block
            ajaxcartHelper:                 '#ajaxcart_helper', //selector for minicart helper block
            ajaxcartRemoveProduct:          '#cart-sidebar .item a.remove', // selector for minicart remove product button
            ajaxcartQtyField:               '#cart-sidebar .item input.cart-item-quantity', // selector for minicart product qty field
            productViewAddToCartButton:     '#add_to_cart', // selector for add to cart button on product view page,
            productViewForm:                '#product_addtocart_form',
            productCategoryAddToCartButton: '.actions .btn-cart', // selector for add to cart button on product listing page,
            productCategoryViewItem:        '.category-products .item', // product container selector in product category view,
            pageSelector:                   '.page',
            minicartOverlay:                '#mincart-overlay',
            progressBar:                    '#ajaxcart-progress'
        };

    // The actual plugin constructor
    function Plugin ( element, options ) {
        this.element    = element;

        this.settings   = $.extend( {
            formKey:        $(element).attr('data-formkey'),
            updateUrl:      $(element).attr('data-updateurl'),
            currentPath:    window.location.pathname
        }, defaults, options );

        this._defaults  = defaults;
        this._name      = pluginName;
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        init: function () {
            //console.log('ajaxcart initialized. ');

            var settings = this.settings;
            // var element = this.element;

            this.cartClickBehaviour(this.element, this.settings);
            this.addProductFromView(this.settings);
            this.addProductFromCategory(this.settings);
            this.removeProduct(this.settings);
            this.editProductQty(this.settings);

            //Add minicart overlay element
            var overlay = settings.minicartOverlay;
            $('body').prepend('<div id="'+ overlay.replace('#', '') +'"></div>');

            //Add minicart progress bar
            $(settings.progressBar).detach().appendTo('body');
        },

        /**
         * Product View add to cart button click action
         * @param settings      Ajaxcart settings object
         */
        addProductFromView: function(settings){
            var plugin = this;

            $(settings.productViewAddToCartButton).on('click', function(){
                var productAjaxAddToCartForm = new VarienForm('product_addtocart_form');

                // Validate form
                if (productAjaxAddToCartForm.validator.validate()) {
                    var form        = productAjaxAddToCartForm.form;
                    var data        = new FormData( jQuery(settings.productViewForm)[0] );
                    var action      = form.action;
                    var update_url  = jQuery('.add-to-cart #update_action').val();
                    var url         = action.replace("checkout/cart", "ajaxcart/cart");

                    //Show progress bar
                    plugin.progressBarAction(settings, 5);

                    //Add overlay on click
                    plugin.ajaxcartOverlay(settings);

                    //Check if product is valid
                    //plugin.validateProduct(settings, url, data, update_url, plugin);
                    plugin.ajaxAddProduct(settings, url, data, update_url, plugin);
                }
            })
        },

        /**
         * Category View add to cart button action
         * @param settings      Ajaxcart settings object
         */
        addProductFromCategory: function(settings){
            var plugin = this;

            //Prepare product data
            $(settings.productCategoryAddToCartButton).each(function(){

                var onclick     = $(this).attr('onclick');
                var path        = onclick.split("\'")[1];
                var url_data    = path.split('/');

                //Get 'product' array key
                var id_key;
                for (var i = 0; i <= url_data.length - 1; i++) {
                    if (url_data[i] === 'product') {
                        id_key = i + 1;
                    }
                }

                //Click action
                if (id_key) {
                    var product_id = url_data[id_key];

                    $(this).attr('onclick', '');
                    $(this).on('click', function(){

                        //Show progress bar
                        plugin.progressBarAction(settings, 5);

                        var url         = path.replace("checkout/cart","ajaxcart/cart");
                        var data        = "form_key=" + settings.formKey + "&product=" + product_id + "&qty=1";

                        //console.log('addProductFromCategory call.');

                        //Add overlay on click
                        plugin.ajaxcartOverlay(settings);
                        plugin.ajaxAddProduct(settings, url, data, settings.updateUrl, plugin);
                    })
                }
            })
        },

        /**
         * AJAX Add Product To Cart function.
         * @param settings      Ajaxcart settings object
         * @param url           ajaxcart/cart/add method call
         * @param data          Serialized add to cart form data
         * @param update_url    ajaxcart/cart/ajaxUpdate method call
         * @param plugin        object
         */
        ajaxAddProduct: function(settings, url, data, update_url, plugin){
            //console.log('ajaxAddProduct call. ');

            //Update progress bar
            plugin.progressBarAction(settings, 25);

            $.ajax({
                url: url,
                type: 'post',
                data: data,
                processData: false,
                contentType: false,
                success: function(data){
                    //console.log('ajaxAddProduct complete.');
                    //console.log(data.status + ": " + data.message);

                    if(data.success == 1){
                        plugin.progressBarAction(settings, 50);
                        plugin.ajaxCartUpdate(settings, update_url, plugin);
                    } else {
                        if(data.message != 'undefined'){
                            plugin.showMessage(data.message, 'error');

                            plugin.ajaxcartOverlay(settings, true);
                            plugin.progressBarAction(settings, 0, false);
                        }
                    }
                    truncateOptions()
                },
                error: function(xhr, ajaxOptions, thrownError){
                    console.log(xhr.status + ": " + thrownError);
                }
            })
        },

        /**
         * Minicart remove product button click action
         * @param settings      Ajaxcart settings object
         */
        removeProduct: function(settings){
            var plugin = this;

            $('body').on('click', settings.ajaxcartRemoveProduct, function(e){
                e.preventDefault();
                //console.log('removeProduct call. ');

                //Update progress bar
                plugin.progressBarAction(settings, 5);

                //Add overlay on click
                plugin.ajaxcartOverlay(settings);

                var url = $(this).attr('data-url').replace("checkout/cart", "ajaxcart/cart");
                var data = 'form_key=' + settings.formKey;

                jQuery.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'post',
                    data: data,
                    success: function(){
                        //console.log('product removed. ');
                        //Update progress bar
                        plugin.progressBarAction(settings, 25);

                        plugin.ajaxCartUpdate(settings, settings.updateUrl, plugin);
                    },
                    error: function(xhr, ajaxOptions, thrownError){
                        console.log(xhr.status + ": " + thrownError);
                    }
                })
            })
        },

        /**
         * Minicart product qty field update action
         * @param settings      Ajaxcart settings object
         */
        editProductQty: function(settings){
            var plugin      = this;

            $('body').on('change', settings.ajaxcartQtyField, function(){
                var qty     = $(this).val();
                var id      = $(this).attr('data-item-id');
                var url     = $(this).attr('data-link').replace("checkout/cart", "ajaxcart/cart");
                var data    =  "form_key=" + settings.formKey + "&product=" + id + "&qty=" + qty;

                //console.log('editProductQty call.');

                //Update progress bar
                plugin.progressBarAction(settings, 5);

                //Add overlay on click
                plugin.ajaxcartOverlay(settings);
                plugin.ajaxProductUpdate(settings, url, data, settings.updateUrl, plugin);
            })
        },

        /**
         * AJAX call for product in cart update
         * @param settings      Ajaxcart settings object
         * @param url           ajax call url
         * @param data          ajax data
         * @param update_url    url for cart update
         * @param plugin        plugin object
         */
        ajaxProductUpdate: function(settings, url, data, update_url, plugin){

            //Update progress bar
            //plugin.progressBarAction(settings, 25);

            jQuery.ajax({
                url: url,
                dataType: 'json',
                type: 'post',
                data: data,
                success: function(data){
                    if(data.success == 1){
                        plugin.progressBarAction(settings, 50);
                        plugin.ajaxCartUpdate(settings, update_url, plugin);
                    } else {
                        if(data.message != 'undefined'){
                            plugin.showMessage(data.message, 'error');

                            plugin.ajaxcartOverlay(settings, true);
                            plugin.progressBarAction(settings, 0, false);
                        }
                    }
                },
                error: function(xhr, ajaxOptions, thrownError){
                    console.log(xhr.status + ": " + thrownError);
                }
            })
        },

        /**
         * AJAX Cart data update action
         * @param settings      Ajaxcart settings object
         * @param update_url    url for cart update
         * @param plugin        plugin object
         */
        ajaxCartUpdate: function(settings, update_url, plugin){
            //console.log('ajaxCartUpdate call.');

            //Update progress bar
            plugin.progressBarAction(settings, 75);

            $.ajax({
                url: update_url,
                dataType: 'json',
                type: 'post',
                data: {'form_key': settings.formKey},
                success: function(data){
                    plugin.minicartMarkupUpdate(settings, data);

                    if(settings.currentPath.indexOf('checkout/cart') >= 0){
                        location.reload();
                    } else {
                        //Overlay
                        plugin.ajaxcartOverlay(settings, true);

                        //Update progress bar
                        plugin.progressBarAction(settings, 100, false);
                    }
                    truncateOptions()

                    //console.log('ajaxCartUpdate complete.');
                },
                error: function(xhr, ajaxOptions, thrownError){
                    console.log(xhr.status + ": " + thrownError);
                }
            })
        },

        minicartMarkupUpdate: function(settings, data){
            var element = this.element;
            //Header Minicart Update
            $('.ajaxcart-count .value', element).text(data.qty);
            $('.ajaxcart-subtotal .value', element).html(data.subtotal);
            $(settings.ajaxcartContent).html(data.content);

            //SidebarCart Update
            var sidebarCartBlock = $('.sidebar  .block.block-cart');

            sidebarCartBlock.each(function(){
                $(this).html(data.sidebar);
            })

            //Mobile cart update
            /** TODO
             * Remove rwd theme relations
             */
            $('.header-minicart.mobile').html(data.mobile);

            $('a[data-target-element="#header-cart-mobile"]').on('click', function(e){
                e.preventDefault();
                $(this).toggleClass('skip-active');
                $('#header-cart-mobile').toggleClass('skip-active')
            })
        },

        /**
         * Define behaviour if minicart content area
         * @param element       AJAX Cart selector
         * @param settings      Ajaxcart settings object
         */
        cartClickBehaviour: function(element, settings){
            /** Show / hide cart content on click */
            var minicartClosed = true;
            $(settings.ajaxcartLink).on('click', function(e){
                var statusIcon = $('.minicart-status', this);
                e.preventDefault();
                $(settings.ajaxcartContent).toggleClass('show');

                if(minicartClosed){
                    statusIcon.removeClass('fa-caret-down');
                    statusIcon.addClass('fa-caret-up');
                    minicartClosed = false;
                } else {
                    statusIcon.removeClass('fa-caret-up');
                    statusIcon.addClass('fa-caret-down');
                    minicartClosed = true;
                }
            });

            $(document).on('click', function(e){
                if ($(e.target).closest(element).length <= 0) {
                    $(settings.ajaxcartContent).removeClass('show');
                }
            });

            $('body').on('click', '.close', function(){
                $(settings.ajaxcartContent).removeClass('show');
            })
        },

        /**
         * Loading animation to element
         * @param settings      Ajaxcart settings object
         * @param disable       overlay statement
         */
        ajaxcartOverlay: function(settings, disable){
            if (typeof disable == "undefined") {
                $(settings.minicartOverlay).addClass('show');
            } else {
                $(settings.minicartOverlay).removeClass('show');
            }
        },

        /**
         * Progress bar action
         * @param settings      Ajaxcart settings object
         * @param progress      Progress value
         * @param state         Progress bar statement, show/hide (true/false)
         */
        progressBarAction: function(settings, progress, state){

            var bar = settings.progressBar;
            var barContent = $(bar).find('.ac-progress-content');

            if (state == undefined){
                state = true;
            }

            //Show / hide progress bar
            if(state == true){
                $(bar).addClass('active');
                $(barContent).width(progress + '%');
            } else {
                $(barContent).width(progress + '%');

                setTimeout(function(){
                    $(bar).removeClass('active');
                    $(barContent).width(0);
                }, 600);
            }

        },

        /**
         * Show message
         * @param settings
         * @param message   string  Message content
         * @param type      string  Error message type ('error', 'success', 'notice' or 'warning')
         */
        showMessage: function(message, type){
            var html = '<ul class="messages"><li class="'+ type +'-msg"><ul><li>' + message + '</li></ul></li></ul>';
            jQuery('.main').prepend(html);
            setTimeout(function(){
                jQuery('.main > .messages').fadeOut(200);
            }, 5000)
        }
    });


    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $[pluginName] = $.fn[pluginName] = function (options) {
        if(!(this instanceof $)) { $.extend(defaults, options) }
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})( jQuery, window, document );