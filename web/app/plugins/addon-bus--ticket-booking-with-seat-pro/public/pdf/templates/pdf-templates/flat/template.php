<?php
/*
* Template Name : Flat
* ----------------------------
* Author        : @MagePeople 
*/

if ( ! defined('ABSPATH')) exit;  // if direct access
global $wbtmmain, $magepdf;
$logo = wp_get_attachment_url($wbtmmain->bus_get_option('pdf_logo', 'ticket_manager_settings', ''));
$bg = wp_get_attachment_url($wbtmmain->bus_get_option('pdf_bacckground_image', 'ticket_manager_settings', ''));
$bg_color = $wbtmmain->bus_get_option('pdf_backgroud_color', 'ticket_manager_settings', '#fff');
$text_color = $wbtmmain->bus_get_option('pdf_text_color', 'ticket_manager_settings', '#000');
$tc_title = $wbtmmain->bus_get_option('pdf_terms_title', 'ticket_manager_settings', '');
$tc_text = $wbtmmain->bus_get_option('pdf_terms_text', 'ticket_manager_settings', '');
$company_address = $wbtmmain->bus_get_option('pdf_company_address', 'ticket_manager_settings', '');
$company_phone = $wbtmmain->bus_get_option('pdf_company_phone', 'ticket_manager_settings', '');
$company_email = $wbtmmain->bus_get_option('pdf_company_email', 'ticket_manager_settings', '');
$date_format = get_option('date_format');
$time_format = get_option('time_format');
$datetimeformat = $date_format . '  ' . $time_format;
?>
    <style type="text/css">
        .mep_ticket_body {
            background: <?php echo $bg_color; ?>;
            padding: 0 10px;
        <?php if($text_color) { ?> color: <?php echo $text_color; ?>;
        <?php }else{ ?> color: #000;
        <?php } ?> position: relative;

        }

        .pdf-ticket-body {
            background: #fff;
            padding: 10px;
            margin: 0 auto;
            width: 100%;
            max-width: 680px;
        }

        .pdf-header {
            text-align: left;
            border-bottom: 1px solid #666;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }

        .pdf-header img {
            max-width: 200px;
            height: auto;
        }

        .pdf-header p {
            margin: 0;
            padding: 0;
            font-size: 12px
        }

        .wbtm-ticket {
            padding: 10px;
            border: 1px solid #666;
            width: 100%;
        }

        .pdf-header h4,
        .pdf-header h5 {
            padding: 0;
            margin: 10px 0;
            font-size: 14px;
        }

        /*Tickets*/
        .wbtm-ticket-body {
            background: #fff;
            padding: 10px;
            margin: 40px 0 50px;
            width: 100%;
            overflow: hidden;
            display: block;
        }

        .wbtm-ticket-body table {
            width: 100%;
            overflow: hidden;
            display: block;
        }

        .wbtm-ticket-body table tr {
            border: 1px solid #fbfbfb !important;
        }

        .wbtm-ticket-body table tr td {
            padding: 6px;
            font-size: 15px;
            border-bottom: 1px solid #666;
        }

        .wbtm-ticket-body table tr td h3 {
            padding: 10px 0;
            margin: 0;
            font-size: 25px;
        }

        .ticket-search {
            width: 400px;
            background: #fff;
            text-align: center;
            padding: 20px;
            margin: 30px auto;
            border: 1px solid #666;
        }

        .ticket-search h2 {
            margin: 0;
            padding: 0 0 20px 0;
        }

        .ticket-search input {
            display: block;
            width: 100%;
            padding: 7px;
            margin-bottom: 10px;
        }

        .ticket-search button {
            background: #676fec;
            color: #fff;
            border: 0;
            padding: 10px 20px;
            font-size: 15px;
        }

        span.wbtm-ticket-hold {
            background: #d0d0d0;
            padding: 5px 20px;
        }

        span.wbtm-ticket-confirm {
            background: #02792f;
            padding: 5px 20px;
            color: #fff;
        }

        input.ticket-input {
            padding: 10px !important;
            margin-bottom: 30px !important;
        }

        .wbtm-ticket-single {
            padding: 3px;
        }

        .wbtm-ticket-single .wbtm-ticket-inline {
            /* display: inline-block; */
            float: left;
            width: 48%;
            position: relative;
        }

        .wbtm-bus-title {
            font-size: 18px;
            font-weight: 700;
            color: red;
        }

        .wbtm-ticket-qr-code {
            text-align: center;
        }

        .wbtm-ticket-qr-code img {
            width: 110px;
            border: 1px solid #666;
            margin-bottom: 20px;
        }

        .mep_event_ticket_terms h3 {
            font-size: 15px;
        }

        .mep_event_ticket_terms {
            text-align: center;
            margin: 30px 0;
            /* border-top:1px solid #666; */
            font-size: 12px;
        }

        .page_break {
            page-break-after: always;
        }

        .page_break:last-child {
            page-break-after: auto;
        }

        .bus-ticket-logo img {
            width: 80px;
        }

        .border {
            border-bottom: 1px solid #666;
            padding-bottom: 4px;
        }
    </style>
    <!-- <div class='pdf-ticket-body' > -->
<?php

$args = array(
    'post_type' => 'wbtm_bus_booking',
    'posts_per_page' => -1,
    'meta_query' => array(
        // 'relation' => 'AND',
        array(
            'key' => 'wbtm_order_id',
            'value' => $order_id,
            'compare' => '='
        ),

    ),
);

$ticket_query = new WP_Query($args);
echo '<div class="wbtm-ticket-body wbtm-tickets">';

// Merge pdf ticket setting
$get_settings = get_option('wbtm_bus_settings');
$get_val = isset($get_settings['merge_pdf_ticket']) ? $get_settings['merge_pdf_ticket'] : '';
$merge_pdf_ticket = $get_val ? $get_val : null;

$ticket_count = count($ticket_query->posts);
$seat_all = '';
$total_fare = 0;
$index = 0;
$name_array = array();
foreach ($ticket_query->posts as $_ticket) {
    $index++;
    $ticket = $_ticket->ID;
    $order_id = get_post_meta($ticket, 'wbtm_order_id', true);
    $user_id = get_post_meta($ticket, 'wbtm_user_id', true);
    $bus_id = get_post_meta($ticket, 'wbtm_bus_id', true);
    $boarding = get_post_meta($ticket, 'wbtm_boarding_point', true);
    $dropping = get_post_meta($ticket, 'wbtm_droping_point', true);
    $start_time = get_post_meta($ticket, 'wbtm_bus_start', true);
    $seat = get_post_meta($ticket, 'wbtm_seat', true);
    $fare = get_post_meta($ticket, 'wbtm_bus_fare', true);
    $journey_date = get_post_meta($ticket, 'wbtm_journey_date', true);
    $booking_date = get_post_meta($ticket, 'wbtm_booking_date', true);
    $status = get_post_meta($ticket, 'wbtm_status', true);
    $name = get_post_meta($ticket, 'wbtm_user_name', true);
    $email = get_post_meta($ticket, 'wbtm_user_email', true);
    $phone = get_post_meta($ticket, 'wbtm_user_phone', true);
    $gender = get_post_meta($ticket, 'wbtm_user_gender', true);
    $address = get_post_meta($ticket, 'wbtm_user_address', true);

    // $extra_bag      = get_post_meta($ticket,'wbtm_user_extra_bag',true);

    $pin = $order_id . "-" . $ticket . "-" . $user_id . "-" . $bus_id;
    $order = wc_get_order($order_id);
    // $price_arr      = get_post_meta($bus_id,'wbtm_bus_prices',true);
    // $fare           = wbtm_get_bus_price($ticket->boarding_point,$ticket->droping_point, $price_arr);

    // Subscription Bus
    $valid_till = '';
    $zone_text = '';
    $billing_type = get_post_meta($ticket, 'wbtm_billing_type', true);
    if($billing_type) {
        $billing_type_str = trim($billing_type);
        $valid_till = mtsa_calculate_valid_date($journey_date, $billing_type);

        $zone = get_post_meta($ticket, 'wbtm_city_zone', true);
        if($zone) {
            $zone = get_term($zone);
            $zone_text = $zone->name;
        }
    }
    // Subscription Bus END

    // Pickup Point
    $pickup_point = get_post_meta($ticket, 'wbtm_pickpoint', true);


    if ($merge_pdf_ticket == 'yes') { // If pdf merge YES
        $total_fare += $fare;
        $name_array[] = $name;

        if ($ticket_count != $index) {
            $seat_all .= $seat . ', ';
            continue;
        } else {
            $seat_all .= $seat;
            // Comma separated Name on merge pdf
            if (count($name_array) > count(array_unique($name_array))) {
                $final_name = $name;
            } else {
                $final_name = implode(', ', $name_array);
            }
            // Comma separated Name on merge pdf END
        }

    } else { // If pdf merge NO
        $total_fare = $fare;
        $final_name = $name;
    }

    ?>
    <div class="mep_ticket_body" <?php if ($bg){ ?>style="background: url(<?php echo $bg; ?>);" <?php } ?> >
        <div class="wbtm-ticket">
            <div class="pdf-header">
                <div class="wbtm-ticket-single">
                    <div class="wbtm-ticket-inline">
                        <div class='bus-ticket-logo'>
                            <?php if (!empty($logo)) printf('<img style="width:110px;text-align:left" src="%s"/>', $logo); ?>
                        </div>
                    </div>
                    <div class="wbtm-ticket-inline">
                        <p><?php echo $company_address; ?> </p>
                        <p><?php echo $company_email; ?> </p>
                        <p><?php echo $company_phone; ?> </p>
                    </div>
                </div>
            </div>
            <div class="wbtm-ticket-qr-code">
                <?php do_action('before_wbtm_qr_display', $pin); ?>
                <div class='qr-code'>
                    <?php do_action('wbtm_qr_display', $pin); ?>
                </div>
                <?php do_action('after_wbtm_qr_display', $pin); ?>
            </div>

            <div class="wbtm-ticket-single">
                <div class="wbtm-ticket-inline border"><h3><?php echo get_the_title($bus_id); ?> </h3></div>
                <div class="wbtm-ticket-inline border">
                    <strong><?php _e('PIN:', 'addon-bus--ticket-booking-with-seat-pro'); ?> </strong> <?php echo $pin; ?>
                </div>
            </div>

            <div class="wbtm-ticket-single">
                <?php if ($name) { ?>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Name:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $final_name; ?>
                    </div>
                <?php }
                if ($phone) { ?>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Phone:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $phone; ?>
                    </div>
                <?php } else { ?>
                    <div class="wbtm-ticket-inline"></div> <?php } ?>
            </div>

            <div class="wbtm-ticket-single">
                <?php if ($gender) { ?>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Gender:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $gender; ?>
                    </div>
                <?php }
                if ($email) { ?>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Email:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $email; ?>
                    </div>
                <?php } else { ?>
                    <div class="wbtm-ticket-inline"></div> <?php }
                ?>
            </div>

            <?php

            $reg_form_arr = unserialize(get_post_meta($bus_id, 'attendee_reg_form', true));
            if (is_array($reg_form_arr) && sizeof($reg_form_arr) > 0) {
                foreach ($reg_form_arr as $builder) {
                    ?>
                    <div class="wbtm-ticket-single">
                        <div class="wbtm-ticket-inline border">
                            <strong><?php echo $builder['field_label']; ?></strong>
                            <?php echo get_post_meta($ticket, $builder['field_id'], true); ?>
                        </div>
                    </div>

                    <?php
                }
            }
            if ($address) { ?>
                <div class="wbtm-ticket-single">
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Address:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $address; ?>
                    </div>
                </div>
            <?php } ?>

            <div class="wbtm-ticket-single">
                <?php if($billing_type) : ?>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Start Date:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo get_wbtm_datetime($journey_date, 'date'); ?>
                    </div>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Valid Till:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo get_wbtm_datetime($valid_till, 'date'); ?>
                    </div>
                <?php else : ?>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Journey Date:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo get_wbtm_datetime($journey_date, 'date'); ?>
                    </div>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Time:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $start_time; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="wbtm-ticket-single">

                <?php if($zone) : ?>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Zone:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $zone_text; ?>
                    </div>
                <?php else : ?>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Boarding:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $boarding; ?>
                    </div>
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Dropping:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $dropping; ?>
                    </div>
                <?php endif; ?>

            </div>
            <?php if ($pickup_point) { ?>
                <div class="wbtm-ticket-single">
                    <div class="wbtm-ticket-inline border">
                        <strong><?php _e('Pickup Point:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo $pickup_point; ?>
                    </div>
                </div>
            <?php } ?>

            <div class="wbtm-ticket-single">
                <div class="wbtm-ticket-inline border">
                    <strong><?php _e('Seat:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo($merge_pdf_ticket == 'yes' ? $seat_all : $seat); ?>
                </div>
                <div class="wbtm-ticket-inline border">
                    <strong><?php _e('Bus No:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo get_post_meta($bus_id, 'wbtm_bus_no', true); ?>
                </div>
            </div>

            <div class="wbtm-ticket-single">
                <div class="wbtm-ticket-inline border">
                    <strong><?php _e('Price:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php
                    echo wc_price($total_fare);

                    ?></div>
                <div class="wbtm-ticket-inline border">
                    <strong><?php _e('Purchase on:', 'addon-bus--ticket-booking-with-seat-pro'); ?></strong> <?php echo get_wbtm_datetime($booking_date, 'date-time-text'); ?>
                </div>
            </div>

        </div>
    </div>
    <div class="mep_tkt_row">
        <div class="mep_ticket_body_col_12">
            <div class="mep_event_ticket_terms">
                <?php if ($tc_title) {
                    echo "<h3>" . $tc_title . "</h3>";
                }
                if ($tc_text) {
                    echo $tc_text;
                }
                ?>
            </div>
        </div>
    </div>
    </div>
    <div class="page_break"></div>
    <?php
}