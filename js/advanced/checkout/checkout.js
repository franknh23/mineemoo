OSCheckout = Class.create();
OSCheckout.prototype = {
    /**
     * Initialize object
     */
    initialize: function (
            loadshipping,
            loadpayment,
            loadorderreview,
            loadpaymentShipping,
            loadorderreviewShipping,
            loadorderreviewPayment,
            saveAddressUrl,
            saveShippingMethodUrl,
            savePaymentMethodUrl,
            updateItemQtyUrl,
            deleteItemQtyUrl,
            saveOrderUrl
            ) {
        this.loadshipping = loadshipping;
        this.loadpayment = loadpayment;
        this.loadorderreview = loadorderreview;
        this.loadpaymentShipping = loadpaymentShipping;
        this.loadorderreviewShipping = loadorderreviewShipping;
        this.loadorderreviewPayment = loadorderreviewPayment;
        this.saveAddressUrl = saveAddressUrl;
        this.saveShippingMethodUrl = saveShippingMethodUrl;
        this.savePaymentMethodUrl = savePaymentMethodUrl;
        this.updateItemQtyUrl = updateItemQtyUrl;
        this.deleteItemQtyUrl = deleteItemQtyUrl;
        this.saveOrderUrl = saveOrderUrl;

    },
    saveAddress: function (loadshipping, loadpayment, loadorderreview) {
        $('place-order-button').disable();
        $('place-order-button').addClassName('advanced-button-disable');

        if ($('place-order-button-top')) {
            $('place-order-button-top').disable();
            $('place-order-button-top').addClassName('advanced-button-disable');
        }


        if (loadshipping) {
            if ($('shipping-method-form')) {
                $('load-shipping').style.display = 'block';
                $('form-loadding-shipping').style.display = 'block';
                $('shipping-method-form').style.opacity = '0.7';
            }
        }
        if (loadpayment) {
            $('load-payment').style.display = 'block';
            $('form-loadding-payment').style.display = 'block';
            $('payment-method-form').style.opacity = '0.7';
        }
        if (loadorderreview) {
            $('load-review').style.display = 'block';
            $('form-loadding-review').style.display = 'block';
            $('order-review-form').style.opacity = '0.7';
        }
        var params = $('onestepcheckout').serialize();

        var request = new Ajax.Request(this.saveAddressUrl, {
            parameters: params,
            onSuccess: function (transport) {

                if (transport.status == 200) {
                    
                    var response = JSON.parse(transport.responseText);


                    if (loadshipping) {
                        if ($('shipping-method-form')) {
                            $('load-shipping').style.display = 'none';
                            $('form-loadding-shipping').style.display = 'none';
                            $('shipping-method-form').style.opacity = '1';
                        }
                    }


                    if (loadpayment) {
                        $('load-payment').style.display = 'none';
                        $('form-loadding-payment').style.display = 'none';
                        $('payment-method-form').style.opacity = '1';
                    }
                    if (loadorderreview) {
                        $('load-review').style.display = 'none';
                        $('form-loadding-review').style.display = 'none';
                        $('order-review-form').style.opacity = '1';
                      
                    }
                    $('place-order-button').enable();
                    $('place-order-button').classList.remove('advanced-button-disable');
                    if ($('place-order-button-top')) {
                        $('place-order-button-top').enable();
                        $('place-order-button-top').classList.remove('advanced-button-disable');
                    }

                    var headDoc = $$('head').first();
                    if (loadshipping) {
                        if ($('shipping-method-form')) {
                            $('shipping-method-form').innerHTML = response.shipping_method;
                        }
                    }
                    if (loadpayment) {
                        $('payment-method-form').innerHTML = response.payment_method;
                    }
                    if (loadorderreview) {
                        $('order-review-form').innerHTML = response.order_review;
                        updateqty();

                    }
                    if ($('shipping-method-form')) {
                        for (var i = 0; i < $$('input[name^=shipping_method]').length; i++) {
                            if ($$('input[name^=shipping_method]')[i].checked) {

                                oscheckout.saveShippingMethods($$('input[name^=shipping_method]')[i].value);
                            }
                        }
                    }

                    if (loadshipping) {
                        if ($('shipping-method-form')) {
                            var scriptShipping = response.shipping_method.extractScripts();
                            for (var i = 0; i < scriptShipping.length; i++) {
                                var script = scriptShipping[i];
                                var headDoc = $$('head').first();
                                var jsElement = new Element('script');
                                jsElement.type = 'text/javascript';
                                jsElement.text = script;
                                headDoc.appendChild(jsElement);
                            }
                        }
                    }
                    if (loadorderreview) {
                        var scriptReview = response.order_review.extractScripts();
                        for (var i = 0; i < scriptReview.length; i++) {
                            var script = scriptReview[i];
                            var headDoc = $$('head').first();
                            var jsElement = new Element('script');
                            jsElement.type = 'text/javascript';
                            jsElement.text = script;
                            headDoc.appendChild(jsElement);
                        }
                    }
                    if (loadpayment) {
                        var scriptPayment = response.payment_method.extractScripts();
                        for (var i = 0; i < scriptPayment.length; i++) {
                            var script = scriptPayment[i];
                            var headDoc = $$('head').first();
                            var jsElement = new Element('script');
                            jsElement.type = 'text/javascript';
                            jsElement.text = script;
                            headDoc.appendChild(jsElement);
                        }
                        for (var i = 0; i < $$('input[id^=p_method]').length; i++) {
                            if ($$('input[id^=p_method]')[i].checked) {
                                $($$('input[id^=p_method]')[i].id).click();
                            }
                        }
                    }

                    
                }
            },
            onFailure: ''
        });

    },
    saveShippingMethods: function (shipping_method) {
        $('place-order-button').disable();
        $('place-order-button').addClassName('advanced-button-disable');
        if ($('place-order-button-top')) {
            $('place-order-button-top').disable();
            $('place-order-button-top').addClassName('advanced-button-disable');
        }

        if (this.loadpaymentShipping) {
            $('load-payment').style.display = 'block';
            $('form-loadding-payment').style.display = 'block';
            $('payment-method-form').style.opacity = '0.7';
        }
        if (this.loadorderreviewShipping) {
            $('load-review').style.display = 'block';
            $('form-loadding-review').style.display = 'block';
            $('order-review-form').style.opacity = '0.7';
        }
        var params = {shipping_method: shipping_method};

        var request = new Ajax.Request(this.saveShippingMethodUrl, {
            parameters: params,
            onSuccess: function (transport) {

                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);
                    if (response.error) {
                        alert(response.message);
                        if (this.loadpaymentShipping) {
                            $('load-payment').style.display = 'none';
                            $('form-loadding-payment').style.display = 'none';
                            $('payment-method-form').style.opacity = '1';
                        }
                        if (this.loadorderreviewShipping) {
                            $('load-review').style.display = 'none';
                            $('form-loadding-review').style.display = 'none';
                            $('order-review-form').style.opacity = '1';
                        }
                        $('place-order-button').enable();
                        $('place-order-button').classList.remove('advanced-button-disable');
                        if ($('place-order-button-top')) {
                            $('place-order-button-top').enable();
                            $('place-order-button-top').classList.remove('advanced-button-disable');
                        }
                        return;
                    }

                    if (this.loadpaymentShipping) {
                        $('load-payment').style.display = 'none';
                        $('form-loadding-payment').style.display = 'none';
                        $('payment-method-form').style.opacity = '1';
                    }
                    if (this.loadorderreviewShipping) {
                        $('load-review').style.display = 'none';
                        $('form-loadding-review').style.display = 'none';
                        $('order-review-form').style.opacity = '1';
                    }
                    $('place-order-button').enable();
                    $('place-order-button').classList.remove('advanced-button-disable');
                    if ($('place-order-button-top')) {
                        $('place-order-button-top').enable();
                        $('place-order-button-top').classList.remove('advanced-button-disable');
                    }

                    var headDoc = $$('head').first();
                    if (this.loadpaymentShipping) {
                        $('payment-method-form').innerHTML = response.payment_method;
                        var scriptPayment = response.payment_method.extractScripts();
                        for (var i = 0; i < scriptPayment.length; i++) {
                            var script = scriptPayment[i];
                            var headDoc = $$('head').first();
                            var jsElement = new Element('script');
                            jsElement.type = 'text/javascript';
                            jsElement.text = script;
                            headDoc.appendChild(jsElement);
                        }
                    }
                    if (this.loadorderreviewShipping) {
                        $('order-review-form').innerHTML = response.order_review;
                        updateqty();
                        var scriptReview = response.order_review.extractScripts();
                        for (var i = 0; i < scriptReview.length; i++) {
                            var script = scriptReview[i];
                            var headDoc = $$('head').first();
                            var jsElement = new Element('script');
                            jsElement.type = 'text/javascript';
                            jsElement.text = script;
                            headDoc.appendChild(jsElement);
                        }
                        for (var i = 0; i < $$('input[id^=p_method]').length; i++) {
                            if ($$('input[id^=p_method]')[i].checked) {
                                $($$('input[id^=p_method]')[i].id).click();
                            }
                        }
                    }
                    

                }
            },
            onFailure: ''
        });
    },
    savePaymentMethods: function (payment_method) {
        $('place-order-button').disable();
        $('place-order-button').addClassName('advanced-button-disable');
        if ($('place-order-button-top')) {
            $('place-order-button-top').disable();
            $('place-order-button-top').addClassName('advanced-button-disable');
        }

        if (this.loadorderreviewPayment) {
            $('load-review').style.display = 'block';
            $('form-loadding-review').style.display = 'block';
            $('order-review-form').style.opacity = '0.7';
        }

        var params = {payment_method: payment_method};

        //Find payment parameters and include 
        var items = $$('input[name^=payment]', 'select[name^=payment]');
        var names = items.pluck('name');
        var values = items.pluck('value');

        for (var x = 0; x < names.length; x++) {
            if (names[x] != 'payment[method]') {
                params[names[x]] = values[x];
            }
        }

        var request = new Ajax.Request(this.savePaymentMethodUrl, {
            parameters: params,
            onSuccess: function (transport) {
                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);

                 

                    if (this.loadorderreviewPayment) {
                        $('load-review').style.display = 'none';
                        $('form-loadding-review').style.display = 'none';
                        $('order-review-form').style.opacity = '1';
                    }

                    $('place-order-button').enable();
                    $('place-order-button').classList.remove('advanced-button-disable');
                    if ($('place-order-button-top')) {
                        $('place-order-button-top').enable();
                        $('place-order-button-top').classList.remove('advanced-button-disable');
                    }
                    var headDoc = $$('head').first();

                    if (this.loadorderreviewPayment) {
                        $('order-review-form').innerHTML = response.order_review;
                        updateqty();
                        var scriptReview = response.order_review.extractScripts();
                        for (var i = 0; i < scriptReview.length; i++) {
                            var script = scriptReview[i];
                            var headDoc = $$('head').first();
                            var jsElement = new Element('script');
                            jsElement.type = 'text/javascript';
                            jsElement.text = script;
                            headDoc.appendChild(jsElement);
                        }

                    }

                }
            },
            onFailure: ''
        });
    },
    showform: function (isNew, div) {
        if (isNew) {
            $(div).show();
        } else {
            $(div).hide();
        }
    },
    showPasswordForm: function () {
        if ($('billing:createaccount').checked) {
            $('password-form').style.display = 'block';
        } else {
            $('password-form').style.display = 'none';
        }
    },
    updateItemsQty: function () {
        $('place-order-button').disable();
        $('place-order-button').addClassName('advanced-button-disable');
        if ($('place-order-button-top')) {
            $('place-order-button-top').disable();
            $('place-order-button-top').addClassName('advanced-button-disable');
        }

        var params = $('onestepcheckout').serialize();

        var request = new Ajax.Request(this.updateItemQtyUrl, {
            parameters: params,
            onSuccess: function (transport) {

                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);
                    if (response.error) {
                        alert(response.message);
                        return;
                    } else {
                        if (response.url) {
                            setLocation(response.url);
                            return;
                        } else {
                            oscheckout.saveAddress(1,0,1);
                        }
                    }
                }
            },
            onFailure: ''
        });
    },
    editItem: function (url) {
        $('edit-product-form').innerHTML = '<div id="edit-product-form"><div class="loadding-edit-product"><div class="loadding-edit-product-icon"><i class="fa fa-spinner fa-3x fa-pulse" id="load-popup"></i></div></div></div>';
        var oldValue = $('edit-product-form').innerHTML;
        var request = new Ajax.Request(url, {
            onSuccess: function (transport) {

                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);
                    if (response.error) {
                        alert(response.error);
                        $_adj.advancedfancybox.close();
                        return;
                    } else {
                        $('edit-product-form').innerHTML = response.html;
                        var js = response.html.extractScripts();
                        for (var i = 0; i < js.length; i++) {
                            var script = js[i];
                            var headDoc = $$('head').first();
                            var jsElement = new Element('script');
                            jsElement.type = 'text/javascript';
                            jsElement.text = script;
                            headDoc.appendChild(jsElement);
                        }
                        $_adj.advancedfancybox.update();
                    }
                }
            },
            onFailure: ''
        });
    },
    deleteItem: function (itemId) {
        if (confirm('Are you sure you would like to remove this item from the shopping cart?')) {
            var params = {item_id: itemId};
            $('load-review').style.display = 'block';
            $('form-loadding-review').style.display = 'block';
            $('order-review-form').style.opacity = '0.7';

            var request = new Ajax.Request(this.deleteItemQtyUrl, {
                parameters: params,
                onSuccess: function (transport) {

                    if (transport.status == 200) {
                        var response = JSON.parse(transport.responseText);
                        if (response.error) {
                            if (this.loadshipping) {
                                if ($('shipping-method-form')) {
                                    $('load-shipping').style.display = 'none';
                                    $('form-loadding-shipping').style.display = 'none';
                                    $('shipping-method-form').style.opacity = '1';
                                }
                            }
                            if (this.loadpayment) {
                                $('load-payment').style.display = 'none';
                                $('form-loadding-payment').style.display = 'none';
                                $('payment-method-form').style.opacity = '1';
                            }

                            return;
                        } else {
                            if (response.url) {
                                setLocation(response.url);
                                return;
                            } else {
                                oscheckout.saveAddress(1,0,1);
                            }
                        }
                    }
                    $('load-review').style.display = 'none';
                    $('form-loadding-review').style.display = 'none';
                    $('order-review-form').style.opacity = '1';
                },
                onFailure: ''
            });
        }
    },
    applyCoupon: function (url, remove) {
        if (!($('coupon_code').value.trim() === '')) {
            $('load-coupon').show();
            $('load-coupon-button').hide();

            var params = {coupon_code: $('coupon_code').value, remove: remove};
            var request = new Ajax.Request(url, {
                parameters: params,
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var response = JSON.parse(transport.responseText);
                        if (response.redirect) {
                            setLocation(response.redirect);
                            $('coupon-message').update('');
                        } else {
                            if (response.error) {
                                $('coupon-message').update(response.error);
                                $('load-coupon').hide();
                            } else {
                                if (parseInt(remove) == 1) {
                                    $('load-coupon').hide();
                                    $('cancel-coupon-code').hide();
                                    $('apply-coupon-code').show();
                                    $('apply-coupon-code').enable();
                                    $('coupon_code').value = "";
                                } else {
                                    $('load-coupon').hide();
                                    $('apply-coupon-code').disable();
                                    $('cancel-coupon-code').show();
                                }
                                oscheckout.saveAddress(1,0,1);
                                $('coupon-message').update('');
                            }

                        }
                    }
                    $('load-coupon-button').show();
                },
                onFailure: function () {
                    $('load-coupon-button').show();
                }
            });
        }
    },
    placeOrder: function () {
        var validator = new Validation('onestepcheckout');
        if (validator.validate()) {
            var form = $('onestepcheckout');


            $('place-order-button').disable();
            $('place-order-button').addClassName('advanced-button-disable');
            if ($('place-order-button-top')) {
                $('place-order-button-top').disable();
                $('place-order-button-top').addClassName('advanced-button-disable');
            }
            $('review-please-wait').show();
            var params = form.serialize();

            var request = new Ajax.Request(this.saveOrderUrl, {
                parameters: params,
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        if (transport && transport.responseText) {
                            try {
                                response = eval('(' + transport.responseText + ')');
                            } catch (e) {
                                response = {};
                            }

                            if (response.success) {
                                if (response.redirect) {
                                    location.href = response.redirect;
                                    return;
                                }
                            } else {
                                var msg = response.error_messages;
                                if (typeof (msg) == 'object') {
                                    msg = msg.join("\n");
                                }
                                if (msg) {
                                    alert(msg);
                                }
                                if (!response.update_section) {
                                    $('place-order-button').enable();
                                    $('place-order-button').classList.remove('advanced-button-disable');
                                    if ($('place-order-button-top')) {
                                        $('place-order-button-top').enable();
                                        $('place-order-button-top').classList.remove('advanced-button-disable');
                                    }
                                    $('review-please-wait').hide();
                                }
                            }

                            if (response.update_section) {
                                $('review-buttons-container').innerHTML = response.update_section.html;

                                $('shipping-method-form').style.display = 'block';
                                $('form-loadding-review').style.display = 'block';
                                $('form-loadding-payment').style.display = 'block';

                                if ($('payflow-advanced-iframe')) {
                                    $('iframe-warning').style.display = 'block !important';
                                    $('payflow-advanced-iframe').style.display = 'block';
                                    $('review-buttons-container').style.width = '100% !important';
                                    $('review-buttons-container').style.float = 'left';
                                }
                                if ($('hss-iframe')) {
                                    $('iframe-warning').style.display = 'block !important';
                                    $('hss-iframe').style.display = 'block';
                                    $('review-buttons-container').style.width = '100% !important';
                                    $('review-buttons-container').style.float = 'left';
                                }
                                if ($('payflow-link-iframe')) {
                                    $('iframe-warning').style.display = 'block !important';
                                    $('payflow-link-iframe').style.display = 'block';
                                    $('review-buttons-container').style.width = '100% !important';
                                    $('review-buttons-container').style.float = 'left';
                                }
                            }

                        }
                    }
                },
                onFailure: ''
            });
        }
    },
    _placeOrder: function () {
        var validator = new Validation('onestepcheckout');
        if (validator.validate()) {
            var form = $('onestepcheckout');

            $('place-order-button').disable();
            $('place-order-button').addClassName('button-disable');
            $('review-please-wait').show();
            var params = form.serialize();

            var request = new Ajax.Request(this.saveOrderUrl, {
                parameters: params,
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        if (transport && transport.responseText) {
                            try {
                                response = eval('(' + transport.responseText + ')');
                            } catch (e) {
                                response = {};
                            }

                            if (response.success) {
                                if (response.redirect) {
                                    location.href = response.redirect;
                                    return;
                                }
                            } else {
                                var msg = response.error_messages;
                                if (typeof (msg) == 'object') {
                                    msg = msg.join("\n");
                                }
                                if (msg) {
                                    alert(msg);
                                }
                                if (!response.update_section) {
                                    $('place-order-button').enable();
                                    $('place-order-button').removeClassName('button-disable');
                                    $('review-please-wait').hide();
                                }
                            }

                            if (response.update_section) {
                                $('review-buttons-container').innerHTML = response.update_section.html;

                                $('shipping-method-form').style.display = 'block';
                                $('form-loadding-review').style.display = 'block';
                                $('form-loadding-payment').style.display = 'block';

                                if ($('payflow-advanced-iframe')) {
                                    $('iframe-warning').style.display = 'block !important';
                                    $('payflow-advanced-iframe').style.display = 'block';
                                    $('review-buttons-container').style.width = '100% !important';
                                    $('review-buttons-container').style.float = 'left';
                                }
                                if ($('hss-iframe')) {
                                    $('iframe-warning').style.display = 'block !important';
                                    $('hss-iframe').style.display = 'block';
                                    $('review-buttons-container').style.width = '100% !important';
                                    $('review-buttons-container').style.float = 'left';
                                }
                                if ($('payflow-link-iframe')) {
                                    $('iframe-warning').style.display = 'block !important';
                                    $('payflow-link-iframe').style.display = 'block';
                                    $('review-buttons-container').style.width = '100% !important';
                                    $('review-buttons-container').style.float = 'left';
                                }
                            }

                        }
                    }
                },
                onFailure: ''
            });
        }
    },
    changeVisible: function (method, mode) {
        var block = 'payment_form_' + method;
        [block + '_before', block, block + '_after'].each(function (el) {
            element = $(el);
            if (element) {
                element.style.display = (mode) ? 'none' : '';
                element.select('input', 'select', 'textarea', 'button').each(function (field) {
                    field.disabled = mode;
                });
            }
        });
    },
    disablePaymentForm: function () {
        var inputs = $$('dl#checkout-payment-method-load input');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].name == 'payment[method]') {
                if (inputs[i].checked) {
                    method = inputs[i].value;
                }
            } else {
                inputs[i].disabled = true;
            }
            inputs[i].setAttribute('autocomplete', 'off');
        }
        var selects = $$('dl#checkout-payment-method-load select');
        for (var i = 0; i < selects.length; i++) {
            if (selects[i].name == 'payment[method]') {
                if (selects[i].checked) {
                    method = selects[i].value;
                }
            } else {
                selects[i].disabled = true;
            }
            selects[i].setAttribute('autocomplete', 'off');
        }
    },
    showforgotpassword: function () {
        $('forgot-password-form').show();
        $('login-form').hide();
    },
    showloginform: function () {
        $('forgot-password-form').hide();
        $('login-form').show();
    },
    forgotpassword: function (url) {

        validator = new Validation('forgot-password');
        var params = $('forgot-password').serialize();
        if (validator.validate()) {
            $('forgot-button').disable();
            $('forgot-button').addClassName('advanced-button-disable');
            $('forgot-password-load-login').show();
            var request = new Ajax.Request(url, {
                parameters: params,
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var response = JSON.parse(transport.responseText);
                        if (response.error) {
                            $('forgot-error-ms').innerHTML = '<ul style="margin: 0; padding: 0"><li><span>' + response.error + '</span></li></ul>';
                            $('forgot-error-ms').style.display = 'block';
                            $('forgot-button').enable();
                            $('forgot-button').classList.remove('advanced-button-disable');
                            $('forgot-password-load-login').hide();
                            return;
                        } else {
                            setLocation(response.redirect);
                        }
                    }
                },
                onFailure: ''
            });
        }
    },
    showShippingAddressForm: function () {
        if ($('billing:shippingaddress').checked) {
            $('advanced-shipping-title').update("0");
            $('advanced-shippingmethod-title').update("2");
            $('advanced-paymentmethod-title').update("3");
            $('advanced-review-title').update("4");
            $('shippingaddress-form').style.display = 'none';
        } else {
            $('advanced-shipping-title').update("2");
            $('advanced-shippingmethod-title').update("3");
            $('advanced-paymentmethod-title').update("4");
            $('advanced-review-title').update("5");
            $('shippingaddress-form').style.display = 'block';
        }         
        oscheckout.saveAddress(this.loadshipping,this.loadpayment,this.loadorderreview);
    },
    brazilZipcodevalid: function (zipcode, url, type) {
        var params = {zipcode: zipcode}
        var request = new Ajax.Request(url, {
            parameters: params,
            onSuccess: function (transport) {
                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);
                    if (response.status) {
                        var data = response.data;

                        if (type == 'billing') {
                            $('billing:postcode').classList.remove('validate-brazil-zipcode');
                            Validation.validate($('billing:postcode'));
                            /*
                            if (typeof $_adj('input[name^="billing[street][]"]')[3] != 'undefined') {
                                $_adj('input[name^="billing[street][]"]')[3].value = data.complemento;
                            }
                            */
                            if ($('billing:city')) {
                                $('billing:city').value = data.cidade;
                            }
                            if (typeof $_adj('input[name^="billing[street][]"]')[0] != 'undefined') {
                                $_adj('input[name^="billing[street][]"]')[0].value = data.logradouro;
                            }
                            if ($('billing:region')) {
                                $('billing:region').value = data.estado_info.nome;
                            }
                            if ($('billing:region_id')) {
                                $('billing:region_id').value = data.estado;
                            }
                        }
                        if (type == 'shipping') {
                            $('shipping:postcode').classList.remove('validate-brazil-zipcode');
                            /*
                            if (typeof $_adj('input[name^="shipping[street][]"]')[3] != 'undefined') {
                                $_adj('input[name^="shipping[street][]"]')[3].value = data.complemento;
                            }
                            */
                            if ($('shipping:city')) {
                                $('shipping:city').value = data.cidade;
                            }
                            if (typeof $_adj('input[name^="shipping[street][]"]')[0] != 'undefined') {
                                $_adj('input[name^="shipping[street][]"]')[0].value = data.logradouro;
                            }
                            if ($('shipping:region')) {
                                $('shipping:region').value = data.estado_info.nome;
                            }
                            if ($('shipping:region_id')) {
                                $('shipping:region_id').value = data.estado;
                            }
                            Validation.validate($('shipping:postcode'));
                        }


                        oscheckout.saveAddress(this.loadshipping,this.loadpayment,this.loadorderreview);
                    } else {
                        Validation.add('validate-brazil-zipcode', response.message, function (the_field_value) {
                            if (the_field_value == zipcode)
                            {
                                return false;
                            }
                            return true;
                        });
                        if (type == 'billing') {
                            $('billing:postcode').addClassName('validate-brazil-zipcode');
                            Validation.validate($('billing:postcode'));
                        }
                        if (type == 'shipping') {
                            $('shipping:postcode').addClassName('validate-brazil-zipcode');
                            Validation.validate($('shipping:postcode'));
                        }
                    }
                }
            },
            onFailure: ''
        });
    },
    brazilTaxvalid: function (element, url) {
        var typeTax = $_adj('input[name="tax_type"]:checked').val();
        if (typeTax == 'CPF') {
            $('billing:taxvat').classList.remove('validar_cnpj');
            Validation.add('validar_cpf', 'O CPF informado \xE9 invalido', function (v) {
                return validaCPF(v, 0);
            });
            element.addClassName('validar_cpf');
            Validation.validate(element);
        } else {
            $('billing:taxvat').classList.remove('validar_cpf');

            var params = {cpnj: element.value}
            var request = new Ajax.Request(url, {
                parameters: params,
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var response = JSON.parse(transport.responseText);
                        result = response.valid;

                        Validation.add('validar_cnpj', 'O CNPJ informado \xE9 invalido', function (v) {
                            return result;
                        });
                        element.addClassName('validar_cnpj');
                        Validation.validate(element);

                    }
                },
                onFailure: ''
            });
        }

    }
};

function validaCPF(cpf, pType) {

    var cpf_filtrado = "", valor_1 = " ", valor_2 = " ", ch = "";
    var valido = false;

    for (i = 0; i < cpf.length; i++) {
        ch = cpf.substring(i, i + 1);
        if (ch >= "0" && ch <= "9") {
            cpf_filtrado = cpf_filtrado.toString() + ch.toString()
            valor_1 = valor_2;
            valor_2 = ch;
        }
        if ((valor_1 != " ") && (!valido))
            valido = !(valor_1 == valor_2);
    }

    if (!valido)
        cpf_filtrado = "12345678912";

    if (cpf_filtrado.length < 11) {
        for (i = 1; i <= (11 - cpf_filtrado.length); i++) {
            cpf_filtrado = "0" + cpf_filtrado;
        }
    }

    if (pType <= 1) {
        if ((cpf_filtrado.substring(9, 11) == checkCPF(cpf_filtrado.substring(0, 9))) && (cpf_filtrado.substring(11, 12) == "")) {
            return true;
        }
    }

    if ((pType == 2) || (pType == 0)) {
        if (cpf_filtrado.length >= 14) {
            if (cpf_filtrado.substring(12, 14) == checkCNPJ(cpf_filtrado.substring(0, 12))) {
                return true;
            }
        }
    }

    return false;
}


function checkCPF(vCPF) {
    var mControle = ""
    var mContIni = 2, mContFim = 10, mDigito = 0;
    for (j = 1; j <= 2; j++) {
        mSoma = 0;
        for (i = mContIni; i <= mContFim; i++)
            mSoma = mSoma + (vCPF.substring((i - j - 1), (i - j)) * (mContFim + 1 + j - i));
        if (j == 2)
            mSoma = mSoma + (2 * mDigito);
        mDigito = (mSoma * 10) % 11;
        if (mDigito == 10)
            mDigito = 0;
        mControle1 = mControle;
        mControle = mDigito;
        mContIni = 3;
        mContFim = 11;
    }
    return((mControle1 * 10) + mControle);
}



function closepopup(type) {
    if (type == 'login') {
        $_adj("body").css({'overflow-y': 'visible'});
        document.getElementById("form-forgot-password-validate").style.display = "none";
        document.getElementById("form-forgot-register-validate").style.display = "none";
        document.getElementById("form-login-validate").style.display = "block";
    }
    if (type == 'term') {
        $_adj("body").css({'overflow-y': 'visible'});
    }
    if (type == 'edit') {
        $_adj("body").css({'overflow-y': 'visible'});
        document.getElementById("form-forgot-password-validate").style.display = "none";
        document.getElementById("form-forgot-register-validate").style.display = "none";
        document.getElementById("form-login-validate").style.display = "block";
    }
}
function LightenDarkenColor(col, amt) {
    return shadeRGBColor(col, amt);
}

function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

function shadeRGBColor(color, percent) {
    var f = color.split(","), t = percent < 0 ? 0 : 255, p = percent < 0 ? percent * -1 : percent, R = parseInt(f[0].slice(4)), G = parseInt(f[1]), B = parseInt(f[2]);
    return "rgb(" + (Math.round((t - R) * p) + R) + "," + (Math.round((t - G) * p) + G) + "," + (Math.round((t - B) * p) + B) + ")";
}

function updateqty(){

                var options = {
                    minimum: 1,
                    onChange: valChanged,
                    onMinimum: function(e) {
                        console.log('reached minimum: '+e)
                    },
                    onMaximize: function(e) {
                        console.log('reached maximize'+e)
                    }
                };

                for(var i = 0; i < $_adj('input.input-qty').length; i++){
                    $_adj('#qty-'+$_adj('input.input-qty')[i].id).handleCounter(options);
                }


}
function valChanged() {
    updateqtyosc();
}
function updateqtyosc(){
    oscheckout.updateItemsQty();  
}
