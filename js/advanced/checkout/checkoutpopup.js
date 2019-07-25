Event.observe(window, 'load', function () {
    $_adj('body').append('<a id="osc-quick-checkout-button" style="display:none" onclick="quickcheckout()"><img src="data:image/png;base64,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"/></a>');
    $_adj('#osc-quick-checkout-button').show();
    closepopup = function (type) {
        if (type == 'login') {
            document.getElementById("form-forgot-password-validate").style.display = "none";
            document.getElementById("form-forgot-register-validate").style.display = "none";
            document.getElementById("form-login-validate").style.display = "block";
        }
        if (type == 'edit') {
            document.getElementById("form-forgot-password-validate").style.display = "none";
            document.getElementById("form-forgot-register-validate").style.display = "none";
            document.getElementById("form-login-validate").style.display = "block";
        }
    }
    
    //catch event click checkout button
    $_adj('.checkout-button').click(function (e) {
        quickcheckout();
        return false;
    });
    $_adj('.btn-checkout').prop('onclick',null);
    $_adj('.btn-checkout').click(function (e) {
        quickcheckout();
        return false;
    });
    
    //wishlist mini
    $_adj('#wishlist-sidebar a').click(function (e) {
        setLocation(e.toElement.href);
        return false;
    });
    
    
    //catalog page
    setLocation = function (url) {

        var check = 0;
        $('osc-poup-button').innerHTML = '';

        if (url.search('checkout/cart/updateItemOptions') != -1) {
            $_adj.advancedfancybox.open('#osc-waiting', {
                'width': 400,
                'padding': 0,
                'height': 'auto',
                'autoSize': false,
                'closeBtn': false,
            });
            $_adj('.zoomContainer').hide()
            $('spinner-loadding').show();
            var url = url.replace("/checkout", "/onestepcheckout");

            var request = new Ajax.Request(url, {
                parameters: $('product_addtocart_form').serialize(),
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var response = JSON.parse(transport.responseText);
                        if (!response.error) {
                            var productImage = response.product.image;
                            var productName = response.product.name;
                            var price = response.product.price;
                            var sku = response.product.sku;

                            var message = response.message;
                            var html = '';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-12">';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-4 advanced-col-xs-6">';
                            html += '<img src="' + productImage + '" style="max-width:100%"/>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-8 advanced-col-xs-6">';
                            html += '<h3>' + productName + '</h3>';
                            html += '<p><span>' + Translator.translate('Price') + ':' + price + '</span></p>';
                            html += '<p><span>' + Translator.translate('Sku') + ':' + sku + '</span></p>';
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="osc-waiting-button-group advanced-row">';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="$_adj.advancedfancybox.close(\'#osc-waiting\')" title="' + Translator.translate('Continue shopping') + '" value="' + Translator.translate('Continue shopping') + '">' + Translator.translate('Continue shopping') + '</button>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="quickcheckout()" title="' + Translator.translate('Checkout Now') + '" value="' + Translator.translate('Checkout Now') + '">' + Translator.translate('Checkout Now') + '</button>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            $('spinner-loadding').hide();
                            $('osc-poup-button').innerHTML = html;
                        } else {
                            var productImage = response.product.image;
                            var productName = response.product.name;
                            var price = response.product.price;
                            var sku = response.product.sku;

                            var message = response.message;
                            var html = '';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-12 message-success">';
                            html += '<ul class="messages">';
                            html += '<li class="error-msg"><ul><li><span>' + message + '</span></li></ul></li>';
                            html += '</ul>';
                            html += '</div>';

                            html += '<div class="advanced-col-md-12">';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-4 advanced-col-xs-6">';
                            html += '<img src="' + productImage + '" style="max-width:100%"/>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-8 advanced-col-xs-6">';
                            html += '<h3>' + productName + '</h3>';
                            html += '<p><span>' + Translator.translate('Price') + ':' + price + '</span></p>';
                            html += '<p><span>' + Translator.translate('Sku') + ':' + sku + '</span></p>';
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="osc-waiting-button-group advanced-row">';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="$_adj.advancedfancybox.close(\'#osc-waiting\')" title="' + Translator.translate('Continue shopping') + '" value="' + Translator.translate('Continue shopping') + '">' + Translator.translate('Continue shopping') + '</button>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="quickcheckout()" title="' + Translator.translate('Checkout Now') + '" value="' + Translator.translate('Checkout Now') + '">' + Translator.translate('Checkout Now') + '</button>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            $('spinner-loadding').hide();
                            $('osc-poup-button').innerHTML = html;
                        }
                    }
                },
                onFailure: ''
            });
            check = 1;
        }
        if (url.search('checkout/cart/add') != -1) {
            $_adj.advancedfancybox.open('#osc-waiting', {
                'width': 400,
                'padding': 0,
                'height': 'auto',
                'autoSize': false,
                'closeBtn': false,
            });
            $_adj('.zoomContainer').hide()
            $('spinner-loadding').show();
            var url = url.replace("/checkout", "/onestepcheckout");
            var request = new Ajax.Request(url, {
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var response = JSON.parse(transport.responseText);
                        if (!response.error) {
                            var productImage = response.product.image;
                            var productName = response.product.name;
                            var price = response.product.price;
                            var sku = response.product.sku;

                            var message = response.message;
                            var html = '';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-12">';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-4 advanced-col-xs-6">';
                            html += '<img src="' + productImage + '" style="max-width:100%"/>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-8 advanced-col-xs-6">';
                            html += '<h3>' + productName + '</h3>';
                            html += '<p><span>' + Translator.translate('Price') + ':' + price + '</span></p>';
                            html += '<p><span>' + Translator.translate('Sku') + ':' + sku + '</span></p>';
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="osc-waiting-button-group advanced-row">';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="$_adj.advancedfancybox.close(\'#osc-waiting\')" title="' + Translator.translate('Continue shopping') + '" value="' + Translator.translate('Continue shopping') + '">' + Translator.translate('Continue shopping') + '</button>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="quickcheckout()" title="' + Translator.translate('Checkout Now') + '" value="' + Translator.translate('Checkout Now') + '">' + Translator.translate('Checkout Now') + '</button>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            $('spinner-loadding').hide();
                            $('osc-poup-button').innerHTML = html;
                            
                        } else {
                            var productImage = response.product.image;
                            var productName = response.product.name;
                            var price = response.product.price;
                            var sku = response.product.sku;

                            var message = response.message;
                            var html = '';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-12 message-success">';
                            html += '<ul class="messages">';
                            html += '<li class="error-msg"><ul><li><span>' + message + '</span></li></ul></li>';
                            html += '</ul>';
                            html += '</div>';

                            html += '<div class="advanced-col-md-12">';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-4 advanced-col-xs-6">';
                            html += '<img src="' + productImage + '" style="max-width:100%"/>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-8 advanced-col-xs-6">';
                            html += '<h3>' + productName + '</h3>';
                            html += '<p><span>' + Translator.translate('Price') + ':' + price + '</span></p>';
                            html += '<p><span>' + Translator.translate('Sku') + ':' + sku + '</span></p>';
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="osc-waiting-button-group advanced-row">';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="$_adj.advancedfancybox.close(\'#osc-waiting\')" title="' + Translator.translate('Continue shopping') + '" value="' + Translator.translate('Continue shopping') + '">' + Translator.translate('Continue shopping') + '</button>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="quickcheckout()" title="' + Translator.translate('Checkout Now') + '" value="' + Translator.translate('Checkout Now') + '">' + Translator.translate('Checkout Now') + '</button>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            $('spinner-loadding').hide();
                            $('osc-poup-button').innerHTML = html;
                            
                        }
                    }
                },
                onFailure: ''
            });
            check = 1;
        }

        if (url.search('wishlist') != -1) {
            $_adj.advancedfancybox.open('#osc-waiting', {
                'width': 400,
                'padding': 0,
                'height': 'auto',
                'autoSize': false,
                'closeBtn': false,
            });
            $_adj('.zoomContainer').hide()
            $('spinner-loadding').show();
            var url = url.replace("/wishlist/index", "/onestepcheckout/cart");
            var request = new Ajax.Request(url, {
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var response = JSON.parse(transport.responseText);
                        if (!response.error) {
                            var productImage = response.product.image;
                            var productName = response.product.name;
                            var price = response.product.price;
                            var sku = response.product.sku;

                            var message = response.message;
                            var html = '';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-12">';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-4 advanced-col-xs-6">';
                            html += '<img src="' + productImage + '" style="max-width:100%"/>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-8 advanced-col-xs-6">';
                            html += '<h3>' + productName + '</h3>';
                            html += '<p><span>' + Translator.translate('Price') + ':' + price + '</span></p>';
                            html += '<p><span>' + Translator.translate('Sku') + ':' + sku + '</span></p>';
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="osc-waiting-button-group advanced-row">';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="$_adj.advancedfancybox.close(\'#osc-waiting\')" title="' + Translator.translate('Continue shopping') + '" value="' + Translator.translate('Continue shopping') + '">' + Translator.translate('Continue shopping') + '</button>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="quickcheckout()" title="' + Translator.translate('Checkout Now') + '" value="' + Translator.translate('Checkout Now') + '">' + Translator.translate('Checkout Now') + '</button>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            $('spinner-loadding').hide();
                            $('osc-poup-button').innerHTML = html;
                        } else {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                                return false;
                            }
                            var productImage = response.product.image;
                            var productName = response.product.name;
                            var price = response.product.price;
                            var sku = response.product.sku;

                            var message = response.message;
                            var html = '';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-12 message-success">';
                            html += '<ul class="messages">';
                            html += '<li class="error-msg"><ul><li><span>' + message + '</span></li></ul></li>';
                            html += '</ul>';
                            html += '</div>';

                            html += '<div class="advanced-col-md-12">';
                            html += '<div class="advanced-row">';
                            html += '<div class="advanced-col-md-4 advanced-col-xs-6">';
                            html += '<img src="' + productImage + '" style="max-width:100%"/>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-8 advanced-col-xs-6">';
                            html += '<h3>' + productName + '</h3>';
                            html += '<p><span>' + Translator.translate('Price') + ':' + price + '</span></p>';
                            html += '<p><span>' + Translator.translate('Sku') + ':' + sku + '</span></p>';
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="osc-waiting-button-group advanced-row">';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="$_adj.advancedfancybox.close(\'#osc-waiting\')" title="' + Translator.translate('Continue shopping') + '" value="' + Translator.translate('Continue shopping') + '">' + Translator.translate('Continue shopping') + '</button>';
                            html += '</div>';
                            html += '<div class="advanced-col-md-6">';
                            html += '<button class="advanced-button btn waves-effect waves-light" onclick="quickcheckout()" title="' + Translator.translate('Checkout Now') + '" value="' + Translator.translate('Checkout Now') + '">' + Translator.translate('Checkout Now') + '</button>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            $('spinner-loadding').hide();
                            $('osc-poup-button').innerHTML = html;
                        }
                    }
                },
                onFailure: ''
            });
            check = 1;
        }

        if (check == 0) {
            window.location.href = url;
        }
    }

    //product page
    if(typeof productAddToCartForm != 'undefined'){
        productAddToCartForm.submit = function (button, url) {
            var validator = new Validation('product_addtocart_form');

            if (validator.validate()) {
                var form = $('product_addtocart_form');
                var oldUrl = form.action;

                if (url) {
                    form.action = url;
                }
                var e = null;
                try {
                    addProduct(form.serialize(), form.action);
                } catch (e) {

                }
                form.action = oldUrl;
                if (e) {
                    throw e;
                }

            }
        }.bind(productAddToCartForm);

        productAddToCartForm.submitLight = function (button, url) {

            if (validator) {
                var nv = Validation.methods;
                delete Validation.methods['required-entry'];
                delete Validation.methods['validate-one-required'];
                delete Validation.methods['validate-one-required-by-name'];
                // Remove custom datetime validators
                for (var methodName in Validation.methods) {
                    if (methodName.match(/^validate-datetime-.*/i)) {
                        delete Validation.methods[methodName];
                    }
                }
                var form = $('product_addtocart_form');
                if (validator.validate()) {
                    if (url) {
                        form.action = url;
                    }
                    addProduct(form.serialize(), form.action);
                }
                Object.extend(Validation.methods, nv);
            }
        }.bind(productAddToCartForm);
    }
    function addProduct(formData, url) {
        var url = url.replace("/checkout", "/onestepcheckout");
        $('osc-poup-button').innerHTML = '';

        $_adj.advancedfancybox.open('#osc-waiting', {
            'width': 400,
            'padding': 0,
            'height': 'auto',
            'autoSize': false,
            'closeBtn': false,
        });
        $_adj('.zoomContainer').hide()
        $('spinner-loadding').show();
        var request = new Ajax.Request(url, {
            parameters: formData,
            onSuccess: function (transport) {
                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);
                    if (!response.error) {
                        var productImage = response.product.image;
                        var productName = response.product.name;
                        var price = response.product.price;
                        var sku = response.product.sku;

                        var message = response.message;
                        var html = '';
                        html += '<div class="advanced-row">';
                        html += '<div class="advanced-col-md-12">';
                        html += '<div class="advanced-row">';
                        html += '<div class="advanced-col-md-4 advanced-col-xs-6">';
                        html += '<img src="' + productImage + '" style="max-width:100%"/>';
                        html += '</div>';
                        html += '<div class="advanced-col-md-8 advanced-col-xs-6">';
                        html += '<h3>' + productName + '</h3>';
                        html += '<p><span>' + Translator.translate('Price') + ':' + price + '</span></p>';
                        html += '<p><span>' + Translator.translate('Sku') + ':' + sku + '</span></p>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="osc-waiting-button-group advanced-row">';
                        html += '<div class="advanced-col-md-6">';
                        html += '<button class="advanced-button btn waves-effect waves-light" onclick="$_adj.advancedfancybox.close(\'#osc-waiting\')" title="' + Translator.translate('Continue shopping') + '" value="' + Translator.translate('Continue shopping') + '">' + Translator.translate('Continue shopping') + '</button>';
                        html += '</div>';
                        html += '<div class="advanced-col-md-6">';
                        html += '<button onclick="quickcheckout()" class="advanced-button btn waves-effect waves-light" title="' + Translator.translate('Checkout Now') + '" value="' + Translator.translate('Checkout Now') + '">' + Translator.translate('Checkout Now') + '</button>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        $('spinner-loadding').hide();
                        $('osc-poup-button').innerHTML = html;
                        
                    } else {
                        var productImage = response.product.image;
                        var productName = response.product.name;
                        var price = response.product.price;
                        var sku = response.product.sku;

                        var message = response.message;
                        var html = '';
                        html += '<div class="advanced-row">';
                        html += '<div class="advanced-col-md-12 message-success">';
                        html += '<ul class="messages">';
                        html += '<li class="error-msg"><ul><li><span>' + message + '</span></li></ul></li>';
                        html += '</ul>';
                        html += '</div>';

                        html += '<div class="advanced-col-md-12">';
                        html += '<div class="advanced-row">';
                        html += '<div class="advanced-col-md-4 advanced-col-xs-6">';
                        html += '<img src="' + productImage + '" style="max-width:100%"/>';
                        html += '</div>';
                        html += '<div class="advanced-col-md-8 advanced-col-xs-6">';
                        html += '<h3>' + productName + '</h3>';
                        html += '<p><span>' + Translator.translate('Price') + ':' + price + '</span></p>';
                        html += '<p><span>' + Translator.translate('Sku') + ':' + sku + '</span></p>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="osc-waiting-button-group advanced-row">';
                        html += '<div class="advanced-col-md-6">';
                        html += '<button class="advanced-button btn waves-effect waves-light" onclick="$_adj.advancedfancybox.close(\'#osc-waiting\')" title="' + Translator.translate('Continue shopping') + '" value="' + Translator.translate('Continue shopping') + '">' + Translator.translate('Continue shopping') + '</button>';
                        html += '</div>';
                        html += '<div class="advanced-col-md-6">';
                        html += '<button class="advanced-button btn waves-effect waves-light" onclick="quickcheckout()" title="' + Translator.translate('Checkout Now') + '" value="' + Translator.translate('Checkout Now') + '">' + Translator.translate('Checkout Now') + '</button>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        $('spinner-loadding').hide();
                        $('osc-poup-button').innerHTML = html;
                      
                    }
                }
            },
            onFailure: ''
        });
    }

});
var checkoutPage = 0;
function quickcheckout() {
    if ($('osc-waiting'))
        $_adj.advancedfancybox.close('#osc-waiting');
    if (checkoutPage) {
        $_adj('body').addClass('osc-body-relative');

        $_adj('#osc-quick-checkout-button').hide();
        $_adj('body').css("overflow", "hidden");
        $_adj('.osc-body-disable').css("height", "100%");
        $_adj('.osc-quick-checkout').css("width", "0px");
        $_adj('.osc-quick-checkout').css("padding", "0px");
        $_adj('.osc-quick-checkout').css("overflow-y", "scroll");

        $_adj('#osc-quick-checkout').animate({width: "95%", padding: "10px"}, 1000).promise().done(function () {
            $_adj('#osc-close-quick-checkout').show();
            oscheckout.saveAddress(1,0,1);
        });
    } else {

        $_adj('body').addClass('osc-body-relative');
        $_adj('body').append('<div class="osc-body-disable"></div><a id="osc-quick-checkout-button" style="display:none" onclick="quickcheckout()"><img src="data:image/png;base64,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"/></a><div class="osc-quick-checkout" id="osc-quick-checkout"></div><div id="osc-close-quick-checkout" style="display:none"><a id="osc-icon-quick-checkout" onclick="closequickcheckout();"><i class="fa fa-angle-double-right fa-2x"></i></a></div>');
        $_adj('body').css("overflow", "hidden");
        $_adj('.osc-quick-checkout').css("width", "0px");
        //$_adj('#osc-close-quick-checkout').css("width", "0px");
        //$_adj('#osc-icon-quick-checkout').hide();
        $_adj('.osc-quick-checkout').css("padding", "0px");
        $_adj('.osc-quick-checkout').css("overflow-y", "scroll");
        $_adj('#osc-quick-checkout-button').hide();
        var request = new Ajax.Request(getosclayoutUrl, {
            onSuccess: function (transport) {
                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);
                    if (typeof googlemapUrl != 'undefined') {
                        var element = document.createElement("script");
                        element.src = googlemapUrl;
                        document.body.appendChild(element);
                    }
                    $('osc-quick-checkout').innerHTML = '<div class="advanced-container">' + response.html + '</div>';
                    $_adj('#osc-quick-checkout').animate({width: "95%", padding: "10px"}, 1000).promise().done(function () {
                        $_adj('#osc-close-quick-checkout').show();
                    });

                    checkoutPage = response.checkout_page;
                    try {
                        var scripts = response.html.extractScripts();
                        for (var i = 0; i < scripts.length; i++) {
                            var script = scripts[i];
                            var headDoc = $$('head').first();
                            var jsElement = new Element('script');
                            jsElement.type = 'text/javascript';
                            jsElement.text = script;
                            headDoc.appendChild(jsElement);
                        }
                    } catch (e) {
                        console.log(e);
                    }
                }
            },
            onFailure: ''
        });
    }
    
}

function closequickcheckout() {
    $_adj('body').removeClass('osc-body-relative');
    $_adj('#osc-close-quick-checkout').hide();
    $_adj('#osc-quick-checkout').animate({width: "0%", padding: "0px"}, 1000).promise().done(function () {

        $_adj('.osc-quick-checkout').css("width", "0px");
        $_adj('.osc-quick-checkout').css("padding", "0px");
        $_adj('.osc-quick-checkout').css("overflow-y", "hidden");

        $_adj('.osc-body-disable').css("height", "0px");
        $_adj('body').css("overflow-y", "scroll");
        $_adj('#osc-quick-checkout-button').show();
    });
    
}