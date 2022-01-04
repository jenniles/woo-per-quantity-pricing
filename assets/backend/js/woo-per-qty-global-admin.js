(function($) {
    'use strict';

    $(document).ready(function() {
        $('#iwe_pqp_choose_cat').select2();
        $('#iwe_pqp_choose_prod').select2();
        $('.iwe_pqp_color').each(function() {
            $(this).wpColorPicker();
        });
    });
    $(document).on('change', '#iwe_pqp_choose_cat', function() {
        var catID = $(this).val();
        $('#iwe_pqp_choose_prod').html("");
        if (catID) {
            var data = {
                action: 'iwe_pqp_choose_categories',
                nonce: iwe_pqp.nonce,
                catID: catID,
            };

            $('#iwe_pqp_loader').show();
            $.ajax({
                url: iwe_pqp.ajaxurl,
                type: "POST",
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response) {
                        $('#iwe_pqp_choose_prod').html(response);
                        $('#iwe_pqp_choose_prod').select2();
                    }
                    $('#iwe_pqp_loader').hide();
                }
            });
        }
    });
    $(document).on('click', '.pqp_action_remove', function() {
        $(this).closest('tr').remove();
    });

    $(document).on('click', '.pqp_action_global_add', function() {

        var empty = false;

        $('.pqp_minimum_global_qty').each(function() {
            if (!$(this).val()) {
                $(this).css("border-color", "red");
                empty = true;
            } else {
                $(this).css("border-color", "");
            }
        });
        $('.pqp_maximum_global_qty').each(function() {
            if (!$(this).val()) {
                $(this).css("border-color", "red");
                empty = true;
            } else {
                $(this).css("border-color", "");
            }
        });
        $('.pqp_price_global_qty').each(function() {
            if (!$(this).val()) {
                $(this).css("border-color", "red");
                empty = true;
            } else {
                $(this).css("border-color", "");
            }
        });
        if (!empty) {

            var index = $('.pqp_tbody_global_rows').find('tr:last-child td .pqp_action_remove').data('index');
            var html = '<tr><td><input type="number" class="pqp_minimum_global_qty" name="pqp_minimum_global_qty[]"></td><td><input type="number" class="pqp_maximum_global_qty" name="pqp_maximum_global_qty[]"></td><td><select name="pqp_pricing_global_type[]"><option value="pqp_sell_price">Selling Price</option><option value="pqp_fixed_disc">Fixed Discount</option><option value="pqp_perc_disc">Discount Percentage</option></select></td><td><input type="text" class="pqp_price_global_qty wc_input_price" name="pqp_price_global_qty[]"></td><td><input type="button" class="pqp_action_remove button" data-index="' + (index + 1) + '" value="Remove"></td></tr>';
            $('.pqp_tbody_global_rows').append(html);
        }
    });
    $(document).on('click', '#iwe_pqp_bulk_add', function(e) {
        e.preventDefault();
        var empty = false;
        var catID = $('#iwe_pqp_choose_cat').val();
        if (!catID) {
            empty = true;
            $('.notice.notice-error.is-dismissible').each(function() {
                $(this).remove();
            });
            $('.notice.notice-success.is-dismissible').each(function() {
                $(this).remove();
            });

            $('html, body').animate({
                scrollTop: $(".woocommerce_page_iwe-pqp-setting").offset().top
            }, 800);
            var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Please Select the Categories!</strong></p></div>';
            $(empty_message).insertAfter($('h1.iwe_pqp_setting_title'));
            return;
        }
        $('.pqp_minimum_global_qty').each(function() {
            if (!$(this).val()) {
                $(this).css("border-color", "red");
                empty = true;
            } else {
                $(this).css("border-color", "");
            }
        });
        $('.pqp_maximum_global_qty').each(function() {
            if (!$(this).val()) {
                $(this).css("border-color", "red");
                empty = true;
            } else {
                $(this).css("border-color", "");
            }
        });
        $('.pqp_price_global_qty').each(function() {
            if (!$(this).val()) {
                $(this).css("border-color", "red");
                empty = true;
            } else {
                $(this).css("border-color", "");
            }
        });
        if (!empty) {
            $(this).closest("form#iwe_setting_form").submit();
        } else {
            $('.notice.notice-error.is-dismissible').each(function() {
                $(this).remove();
            });
            $('.notice.notice-success.is-dismissible').each(function() {
                $(this).remove();
            });

            $('html, body').animate({
                scrollTop: $(".woocommerce_page_iwe-pqp-setting").offset().top
            }, 800);
            var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Some Fields are empty!</strong></p></div>';
            $(empty_message).insertAfter($('h1.iwe_pqp_setting_title'));
            return;
        }
    });
})(jQuery);