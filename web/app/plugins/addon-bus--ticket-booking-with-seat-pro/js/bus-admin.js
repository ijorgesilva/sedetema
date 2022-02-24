(function ($) {
    
    $(document).ready(function () {

        // Bus Report Detail
        $('.wbbm_bus_detail--report .wbbm_detail_inside').click(function () {
            let $this = $(this);
            let parent = $this.parents('tr');
            let bus_id = $this.parents('td').attr('data-bus-id');

            if (parent.next('.wbbm_report_detail').hasClass('show')) {
                parent.next('.wbbm_report_detail').removeClass('show');
                parent.next('.wbbm_report_detail').hide();
                return;
            }

            $('.wbbm-main-table tbody tr.wbbm_report_detail').each(function () {
                if ($(this).hasClass('show')) {
                    $(this).removeClass('show');
                    $(this).hide();
                }
            });

            $.ajax({
                url: ajaxurl,
                type: 'post',
                dataType: 'html',
                data: { bus_id: bus_id, action: 'wbbm_get_bus_details' },
                beforeSend: function () {
                    $this.parent().siblings('.wbbm_report_loading').show();
                },
                success: function (data) {
                    if (data) {
                        if (parent.next('.wbbm_report_detail').children().length == 0) {
                            $(data).insertAfter(parent);
                            parent.next('.wbbm_report_detail').slideDown(100);
                        }
                        if (parent.next('.wbbm_report_detail').hasClass('show')) {
                            parent.next('.wbbm_report_detail').hide();
                        } else {
                            parent.next('.wbbm_report_detail').slideDown(100);
                        }
                        parent.next('.wbbm_report_detail').toggleClass('show');

                        $this.parent().siblings('.wbbm_report_loading').hide();
                    }
                }
            });

        });

        // order wise detail report
        $('.wbbm_order_detail--report .wbbm_detail_inside').click(function () {
            let $this = $(this);
            let parent = $this.parents('tr');
            let order_id = $this.parents('td').attr('data-order-id');

            if (parent.next('.wbbm_report_detail').hasClass('show')) {
                parent.next('.wbbm_report_detail').removeClass('show');
                parent.next('.wbbm_report_detail').hide();
                return;
            }

            $('.wbbm-main-table-order-wise tbody tr.wbbm_report_detail').each(function () {
                if ($(this).hasClass('show')) {
                    $(this).removeClass('show');
                    $(this).hide();
                }
            });

            $.ajax({
                url: ajaxurl,
                type: 'post',
                dataType: 'html',
                data: { order_id: order_id, action: 'wbbm_get_order_details' },
                beforeSend: function () {
                    $this.parent().siblings('.wbbm_report_loading').show();
                },
                success: function (data) {
                    if (data) {
                        if (parent.next('.wbbm_report_detail').children().length == 0) {
                            $(data).insertAfter(parent);
                            parent.next('.wbbm_report_detail').slideDown(100);
                        }
                        if (parent.next('.wbbm_report_detail').hasClass('show')) {
                            parent.next('.wbbm_report_detail').hide();
                        } else {
                            parent.next('.wbbm_report_detail').slideDown(100);
                        }
                        parent.next('.wbbm_report_detail').toggleClass('show');

                        $this.parent().siblings('.wbbm_report_loading').hide();
                        // $this.toggleClass('wbbm_report_detail_active');
                    }
                }
            });

        });

        $('#bus_id').select2({
            width: 'resolve',
            theme: "classic"
        });
        $('#boarding_point').select2({
            width: 'resolve',
            theme: "classic"
        });
        $('#dropping_point').select2({
            width: 'resolve',
            theme: "classic"
        });

        $("#one_from_date").datepicker({
            dateFormat: "yy-mm-dd",
        });
        $("#one_to_date").datepicker({
            dateFormat: "yy-mm-dd",
        });

        $("#three_from_date").datepicker({
            dateFormat: "yy-mm-dd",
        });
        $("#three_to_date").datepicker({
            dateFormat: "yy-mm-dd",
        });

        $('input[name="j_date"]').datepicker({
            dateFormat: "yy-mm-dd",
        });

        $('input[name="filter_booking_date"]').datepicker({
            dateFormat: "yy-mm-dd",
        });

        // Bus Item slide
        $('.wbtm_bus_detail_btn').click(function() {
            let target = $(this).parents('.wbtm_bus_list_item').find('.item_bottom');
            if (target.is(':visible')) {
                target.slideUp(300);
            } else {
                $('.wbtm_bus_list_item').find('.item_bottom').slideUp(300);
                target.slideDown(300);
            }
        });

        // Without Seat Plan
        $('.mage-seat-qty input').on('input', function () {
            let $this = $(this);
            let price = $this.attr('data-price');
            let type = $this.attr('data-seat-type');
            let qty = $this.val();
            qty = qty > 0 ? qty : 0;
            $this.parent().siblings('.mage-seat-price').find('.price-figure').text(price * qty);
            $this.parent().siblings('.mage-seat-price').attr('data-price', (price * qty));

            let p = 0.00;
            $this.parents('.mage-seat-table').find('tbody tr').each(function () {
                if (parseFloat($(this).find('.mage-seat-price').attr('data-price'))) {
                    p = p + parseFloat($(this).find('.mage-seat-price').attr('data-price'));
                }

            });

            $this.parents('.mage-seat-table').find('.mage-price-total .price-figure').text(parseFloat(p));

            // Enable Booking Button
            if ( type == 'adult' ) {
                if (qty > 0) {
                    $('.no-seat-submit-btn').prop('disabled', false);
                } else {
                    $('.no-seat-submit-btn').prop('disabled', true);
                }
            }

            // Append Custom Registration Field
            if (type) {
                mageCustomRegField($(this), type, qty);
            }

        });

        // Change qty button
        $('.wbtm-qty-change').click(function (e) {
            e.preventDefault();
            let changeType = $(this).attr('data-qty-change');
            let targetEle = $(this).siblings('.qty-input');
            let qty = parseInt(targetEle.val());
            let qtyUpdated = 0;

            if (changeType == 'inc') {
                qtyUpdated = (qty > 0 ? qty + 1 : 1);
            } else {
                qtyUpdated = (qty > 0 ? qty - 1 : 0);
            }

            targetEle.val(qtyUpdated); // Update qty
            targetEle.trigger('input');
        });


        $('#j_date').datepicker({
            dateFormat: "yy-mm-dd",
            minDate: 0
        });

        $('#wbtm_start').select2({
            width: 'resolve',
            theme: "classic"
        });
        $('#wbtm_end').select2({
            width: 'resolve',
            theme: "classic"
        });

        // Custom Tab
        $('.clickme button').click(function (e) {
            e.preventDefault();
            $('.clickme button').removeClass('wbtm_tab_active');
            $(this).addClass('wbtm_tab_active');
            var tagid = $(this).attr('data-tag');
            var tab_no = $(this).attr('data-tab-no');
            $('.wbtm_content_item').removeClass('active').addClass('hide');

            $('#' + tagid).addClass('active').removeClass('hide');
            
            $.ajax({
                url: wbtm_ajaxurl,
                type: 'post',
                data: {tab_no: tab_no, action: 'wbtm_tab_assign'}
            });
        });
        // Custom Tab END

        // Detail Toggle
        $('.admin-general-bus-detail-toggle').click(function () {
            let target = $(this).parents('.admin-bus-list').next('.admin-bus-details');
            if (target.is(':visible')) {
                target.slideUp(300);
            } else {
                $('.admin-bus-list').next('.admin-bus-details').slideUp(300);
                target.slideDown(300);
            }
        });
    });

    // Custom Reg Field New way
    function mageCustomRegField($this, seatType, qty) {
        let parent = $this.parents('.admin-bus-details');
        let bus_id = parent.attr('data-bus-id');
        console.log('ksdkjf');
        $.ajax({
            url: wbtm_ajaxurl,
            type: 'POST',
            async: true,
            data: { busID: bus_id, seatType: seatType, seats: qty, action: 'wbtm_form_builder' },
            beforeSend: function () {
                parent.find('#wbtm-form-builder .wbtm-loading').show();
            },
            success: function (data) {
                let s = seatType.toLowerCase();
                if (data !== '') {
                    $(".wbtm-form-builder-" + s).html(data).find('.mage_hidden_customer_info_form').each(function (index){
                        $(this).find('.mage_title h5').html(seatType+' : '+(index+1));
                        $(this).removeClass('mage_hidden_customer_info_form').find('.mage_form_list').slideDown(200);
                    });

                } else {
                    parent.find(".wbtm-form-builder-" + s).empty();
                }
                parent.find('.wbtm-form-builder .wbtm-loading').hide();
            }
        });
    }


})(jQuery);