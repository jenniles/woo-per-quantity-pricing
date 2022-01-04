(function($) {
    'use strict';

    $(document).ready(function() {
        $('.cart').each(function() {
            $(this).wc_pqpsingle_form();
        });

        if (iwe_pqp.type != 'variable') {
            var pqp_enable = iwe_pqp.pqp_enable;
            var product_id = iwe_pqp.product_id;
            var qty = $('.input-text.qty.text').val();
            if (!isNaN(qty) && qty >= 1) {
                if (pqp_enable == 'yes') {
                    fetch_price(qty, product_id);
                }
            }
        }
    });
    var PqpsingleForm = function($form) {
        this.$form = $form;
        $form.on('change', '.variation_id', { pqpsingleForm: this }, this.onChangeVaration);
        $form.on('input', '.input-text.qty.text', { pqpsingleForm: this }, this.onChangeQty);
    };
    $.fn.wc_pqpsingle_form = function() {
        new PqpsingleForm(this);
        return this;
    };
    var fetch_price = function(qty, product_id) {
        block($('div.summary.entry-summary'));
        var data = {
            action: 'iwe_pqp_single_qty',
            product_id: product_id,
            nonce: iwe_pqp.nonce,
            qty: qty
        };
        $.ajax({
            url: iwe_pqp.ajaxurl,
            type: "POST",
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.result == true) {
                    $('.summary.entry-summary').find('p.price').html(response.data);
                }
                if (iwe_pqp.type == 'variable') {
                    if (response.html) {
                        $('.iwe_pqp_per_qty_table').html(response.html);
                    }
                }
            },
            complete: function() {
                unblock($('div.summary.entry-summary'));
            }
        });
    }
    PqpsingleForm.prototype.onChangeQty = function(event) {
        var qty = $(this).val();
        var product_id = 0;
        var pqp_enable = '';
        if (iwe_pqp.type == 'variable') {
            product_id = $('.variation_id').val();
            var childs = iwe_pqp.var_arr;
            var is_pqp = false;
            $.each(childs, function(key, value) {
                if (product_id == key && value == 'yes') {
                    is_pqp = true;
                }
            });

            if (is_pqp) {
                if (!isNaN(qty)) {
                    fetch_price(qty, product_id);
                }
            }
        } else {
            pqp_enable = iwe_pqp.pqp_enable;
            product_id = iwe_pqp.product_id;

            if (!isNaN(qty) && qty >= 1) {
                if (pqp_enable == 'yes') {
                    fetch_price(qty, product_id);
                }
            }
        }
    };



    PqpsingleForm.prototype.onChangeVaration = function(event) {

        var product_id = parseInt($(this).val());
        var childs = iwe_pqp.var_arr;
        var is_pqp = false;
        $.each(childs, function(key, value) {
            if (product_id == key && value == 'yes') {
                is_pqp = true;
            }
        });

        if (is_pqp) {
            var qty = $('.input-text.qty.text').val();
            if (!isNaN(qty)) {
                fetch_price(qty, product_id);
            }
        }
    };
    var is_blocked = function($node) {
        return $node.is('.processing') || $node.parents('.processing').length;
    };

    var block = function($node) {
        if (!is_blocked($node)) {
            $node.addClass('processing').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        }
    };

    var unblock = function($node) {
        $node.removeClass('processing').unblock();
    };

})(jQuery);