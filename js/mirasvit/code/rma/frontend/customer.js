(function ($) {
    jQuery.fn.orderBlockTable = function () {
        var $this = this;

        $('thead', this).click(function (event) {
            var selector = $(this).data('order');
            if ($(event.target).parents('thead').length && event.target.nodeName != 'INPUT') {
                $('tbody[data-order="' + selector + '"]').toggle();
            }
        });

        $('.UI-SELECT-ORDER', this).click(function () {
            selectOrderItems(this);
        });

        $('.UI-SELECT-PRODUCT', this).click(function () {
            selectItemOrder(this);
        });

        $('.UI-ORDER-ITEM-QTY', this).change(function () {
            var $tr = $(this).parents('tr');
            if ($(this).val() > 0 && !$('td .select-order-item:checked', $tr).length) {
                $('.UI-SELECT-PRODUCT', $tr).click();
            }
            if ($(this).val() <= 0 && $('td .select-order-item', $tr).length) {
                $('.UI-SELECT-PRODUCT', $tr).click();
            }
        });

        var init = function () {
            $('thead', $this).each(function () {
                var selector = $(this).data('order');
                if (
                    $('tbody[data-order="'+selector+'"] .UI-SELECT-PRODUCT[disabled=""]').length ==
                        $('tbody[data-order="'+selector+'"] .UI-SELECT-PRODUCT').length
                ) {
                    $('.UI-SELECT-ORDER', this).attr('disabled', '');
                }
            })

        };

        var selectOrderItems = function (el) {
            var order = $(el).data('order');
            var $tbody = $('tbody[data-order=' + order + ']');
            var classes = 'required-entry validate-rma-quantity-less validate-rma-quantity';

            if (el.checked) {
                $('.UI-SELECT-PRODUCT', $tbody).each(function () {
                    if (!this.disabled) {
                        this.checked = true;
                    }
                });
                $('input[type="number"]', $tbody).each(function () {
                    $(this).addClass(classes);
                });
                $('select', $tbody).each(function () {
                    $(this).addClass(classes);
                });
            } else {
                $('.UI-SELECT-PRODUCT', $tbody).each(function () {
                    this.checked = false;
                });
                $('input[type="number"]', $tbody).each(function () {
                    $(this).removeClass(classes);
                });
                $('select', $tbody).each(function () {
                    $(this).removeClass(classes);
                });
            }
        }

        var selectItemOrder = function (el) {
            var $tbody = $(el).parents('tbody');
            var order = $($tbody).data('order');
            var $thead = $('thead[data-order=' + order + ']');
            var $tr = $(el).parents('tr');
            var classes = 'required-entry validate-rma-quantity-less validate-rma-quantity';

            if ($('.UI-SELECT-PRODUCT:checked', $tbody).length) {
                $('.UI-SELECT-ORDER', $thead)[0].checked = true;
            }
            else {
                $('.UI-SELECT-ORDER', $thead)[0].checked = false;
            }

            if (el.checked) {
                $('input[type="number"]', $tr).each(function () {
                    $(this).addClass(classes);
                });
                $('select', $tr).each(function () {
                    $(this).addClass(classes);
                });
            }
            else {
                $('input[type="number"]', $tr).each(function () {
                    $(this).removeClass(classes);
                });
                $('select', $tr).each(function () {
                    $(this).removeClass(classes);
                });
            }
        }

        return this.each(init);
    }

    var defaults = {
        orderIdTemplate:        'order_id_template',
        orderItemTemplate:      'order_item_template',
        offlineItems:           'offline_items',
        otherManualOrderButton: 'add-other-manual-order',
        offlineOrderInput:      'UI-OFFLINE-ORDER-INPUT',
    };

    jQuery.fn.offlineOrder = function (params) {
        var $body = $('body');
        var $this = this;
        var options = $.extend({}, defaults, params);

        var init = function () {
            options.addNewOfflineOrderButton = $('#add_new_offline_order button');
            options.otherManualOrderButton = $('.add-other-manual-order');
            options.addNewOfflineItem = '.add_offline_item';
            options.deleteOfflineItem = '.delete_offline_item';
            options.deleteOfflineOrder = '.delete_offline_order';

            $(options.addNewOfflineOrderButton).click(function () {
                addOrder();
            });

            $body.on('click', options.addNewOfflineItem, function () {
                options.orderContainer = $(this).parents('.UI-ORDER-CONTAINER');
                addItem();
            });

            $body.on('click', options.deleteOfflineItem, function () {
                var $itemBlock = $(this).parents('.UI-OFFLINE-PRODUCT');
                var $orderBlock = $itemBlock.parents('.'+options.offlineItems);

                $itemBlock.remove();

                $(options.addNewOfflineItem, $orderBlock).last().show();
            });

            $body.on('click', options.deleteOfflineOrder, function () {
                $(this).parents('.UI-ORDER-CONTAINER').remove();
            });

            $body.on('change', '.'+options.offlineOrderInput, function () {
                updateIds($(this).parents('.UI-ORDER-CONTAINER'));
            });

            $body.on('change', '.UI-ITEMNAME', function () {
                updateIds($(this).parents('.UI-ORDER-CONTAINER'));
            });

            $body.on('change', '.UI-ITEMQTY', function () {
                updateIds($(this).parents('.UI-ORDER-CONTAINER'));
            });

        };

        var addOrder = function () {
            $($this).append($('#'+options.orderIdTemplate).html());

            options.orderContainer = $('.UI-ORDER-CONTAINER', $this).last();

            addItem();

            $(options.otherManualOrderButton).show();
        }

        var addItem = function () {
            $('.' + options.offlineItems + ' ' + options.addNewOfflineItem, options.orderContainer).hide();
            $('.' + options.offlineItems, options.orderContainer).append($('#'+options.orderItemTemplate).html());
        }

        var updateIds = function (el) {
            var orderId = $('.'+options.offlineOrderInput, el).val();
            $('.UI-OFFLINE-ORDER-INPUT', el).attr('name', 'offline_orders[' + orderId + '][id]').val(orderId);
            $('.'+options.offlineItems+' > div', el).each(function () {
                var itemNameField = $('.UI-ITEMNAME', this);
                $(itemNameField).attr('name', 'items[' + orderId + '][' + $(itemNameField).val() + '][name]');

                $('.UI-OFFLINE-SELECT-ORDER', this).attr('name', 'items[' + orderId + '][' + $(itemNameField).val() + '][checkbox]').val(orderId);
                $('.UI-IS-OFFLINE', this).attr('name', 'items[' + orderId + '][' + $(itemNameField).val() + '][is_offline]').val(orderId);

                var itemQtyField = $('.itemqty', this);
                $(itemQtyField).attr('name', 'items[' + orderId + '][' + $(itemNameField).val() + '][qty_requested]');
            });
        }

        return this.each(init);
    }

    $(document).ready(function ($) {
        $('.UI-ORDER-BLOCK').orderBlockTable();
        $('#add_new_offline_order_container').offlineOrder();
    });
}) (jQuery);



Validation.add('validate-rma-quantity-less', 'The quantity is less than allowed.', function (v, elm) {
    var result = Validation.get('IsEmpty').test(v) || !/[^\d]/.test(v);
    var reRange = new RegExp(/^digits-range-[0-9]+-[0-9]+$/);
    $w(elm.className).each(function (name, index) {
        if (name.match(reRange) && result) {
            var min = parseInt(name.split('-')[2], 10);
            var max = parseInt(name.split('-')[3], 10);
            var val = parseInt(v, 10);
            result = (v >= min);
        }
    });
    return result;
});
Validation.add('validate-rma-quantity', 'The quantity is greater than allowed.', function (v, elm) {
    var result = Validation.get('IsEmpty').test(v) || !/[^\d]/.test(v);
    var reRange = new RegExp(/^digits-range-[0-9]+-[0-9]+$/);
    $w(elm.className).each(function (name, index) {
        if (name.match(reRange) && result) {
            var min = parseInt(name.split('-')[2], 10);
            var max = parseInt(name.split('-')[3], 10);
            var val = parseInt(v, 10);
            result = (v <= max);
        }
    });
    return result;
});