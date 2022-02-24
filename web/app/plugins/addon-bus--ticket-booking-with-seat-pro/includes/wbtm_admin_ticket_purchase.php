<?php
if (is_admin()) {


    function wbtm_generate_order_key()
    {
        return 'wc_' . apply_filters('woocommerce_generate_order_key', 'order_' . wp_generate_password(13, false));
    }


    add_action('admin_menu', 'wbtm_admin_purchase_ticket_menu');
    function wbtm_admin_purchase_ticket_menu()
    {
        add_submenu_page('edit.php?post_type=wbtm_bus', __('Purchase Ticket', 'addon-bus--ticket-booking-with-seat-pro'), __('Purchase Ticket', 'addon-bus--ticket-booking-with-seat-pro'), 'manage_options', 'admin_purchase_ticket', 'wbtm_admin_purchase_ticket');
    }

    function wbtm_admin_purchase_ticket()
    {
        global $wpdb, $magepdf, $wbtmmain, $wbtmpublic;


        $date = isset($_GET['j_date']) ? strip_tags($_GET['j_date']) : date('Y-m-d');

        $bus_stops = get_terms('wbtm_bus_stops', array(
            'hide_empty' => false,
        ));


        if (isset($_POST['add-to-cart-admin'])) {

//            echo '<pre>';
//            print_r($_POST);
//            die;

            $wbtm_order_seat_plan = sanitize_text_field($_POST['wbtm_order_seat_plan']);
            $start = isset($_POST['start_stops']) ? $_POST['start_stops'] : '';
            $end = isset($_POST['end_stops']) ? $_POST['end_stops'] : '';
            $j_date = sanitize_text_field($_POST['journey_date']);
            $seat_name = isset($_POST['seat_name']) ? $_POST['seat_name'] : '';
            if ($wbtm_order_seat_plan == 'yes') {
                $count_seat = count($seat_name);
                $bus_fare = (sanitize_text_field($_POST['bus_fare']) * $count_seat);
                $seats = implode(",", $seat_name);
                $fare = $wbtmmain->wbtm_array_strip($_POST['seat_fare']);
                $passenger_label = $wbtmmain->wbtm_array_strip($_POST['passenger_label']);
            }
            $b_time = isset($_POST['user_start_time']) ? $_POST['user_start_time'] : '';
            $j_time = isset($_POST['bus_start_time']) ? $_POST['bus_start_time'] : '';
            $bus_id = sanitize_text_field($_POST['bus_id']);
            $add_datetime = date("Y-m-d h:i:s");
            $uname = 'Order &ndash; ' . $add_datetime;

            $extra_bag_price = get_post_meta($bus_id, 'wbtm_extra_bag_price', true) ? get_post_meta($bus_id, 'wbtm_extra_bag_price', true) : 0;

            $passenger_type_num = $wbtmmain->wbtm_array_strip($_POST['passenger_type']);

            $seat_qty = isset($_POST['seat_qty']) ? $_POST['seat_qty'] : null;
            $passenger_type = $_POST['passenger_type'];

            $bus_type = $_POST['wbtm_bus_type'];

            // pickup point
            $wbtm_pickpoint = isset($_POST['wbtm_pickpoint']) ? $_POST['wbtm_pickpoint'] : '';

            $wbtm_billing_type = isset($_POST['mtsa_billing_type']) ? $_POST['mtsa_billing_type'] : '';
            $city_zone = isset($_POST['city_zone']) ? $_POST['city_zone'] : '';


            $passenger_type_num = array(
                'Adult' => 0,
                'Child' => 1,
                'Infant' => 2,
            );

            // ADD THE FORM INPUT TO $new_post ARRAY
            $new_post = array(
                'post_title' => $uname,
                'post_content' => '',
                'post_category' => array(),
                'tags_input' => array(),
                'post_status' => 'wc-completed',
                'post_type' => 'shop_order'
            );

            //SAVE THE POST
            $pid = wp_insert_post($new_post);
            update_post_meta($pid, '_order_key', wbtm_generate_order_key());
            update_post_meta($pid, '_created_via', 'admin');
            update_post_meta($pid, '_customer_user', get_current_user_id());
            update_post_meta($pid, '_payment_method', 'cod');
            update_post_meta($pid, '_payment_method_title', 'Cash on delivery');
            update_post_meta($pid, '_created_via', 'Admin Dashboard');
            update_post_meta($pid, '_completed_date', $add_datetime);
            update_post_meta($pid, '_recorded_sales', 'yes');
            update_post_meta($pid, '_recorded_coupon_usage_counts', 'yes');
            update_post_meta($pid, '_order_stock_reduced', 'yes');


            $order_id = $pid;
            $user_id = get_current_user_id();
            $counter = 0;


            $next_stops = maybe_serialize($wbtmmain->wbtm_get_all_stops_after_this($bus_id, $start, $end));

            if ($wbtm_order_seat_plan == 'yes') {
                $extra_bag_price_total = 0;

                foreach ($seat_name as $_seats) {
                    if (!empty($_seats)) {

                        // Custom Reg user
                        $usr_inf[$counter]['wbtm_user_name'] = $user_name = isset($_POST['wbtm_user_name'][$counter]) ? $_POST['wbtm_user_name'][$counter] : '';
                        $usr_inf[$counter]['wbtm_user_email'] = $user_email = isset($_POST['wbtm_user_email'][$counter]) ? $_POST['wbtm_user_email'][$counter] : '';
                        $usr_inf[$counter]['wbtm_user_phone'] = $user_phone = isset($_POST['wbtm_user_phone'][$counter]) ? $_POST['wbtm_user_phone'][$counter] : '';
                        $usr_inf[$counter]['wbtm_user_address'] = $user_address = isset($_POST['wbtm_user_address'][$counter]) ? $_POST['wbtm_user_address'][$counter] : '';
                        $usr_inf[$counter]['wbtm_user_gender'] = $user_gender = isset($_POST['wbtm_user_gender'][$counter]) ? $_POST['wbtm_user_gender'][$counter] : '';
                        $usr_inf[$counter]['wbtm_extra_bag_qty'] = $wbtm_extra_bag_qty = isset($_POST['wbtm_extra_bag_qty'][$counter]) ? $_POST['wbtm_extra_bag_qty'][$counter] : 0;

                        $extra_bag_price_total = $extra_bag_price_total + ($wbtm_extra_bag_qty * $extra_bag_price);
//                        $wbtm_form_builder_data = get_post_meta($bus_id, 'wbtm_form_builder_data', true);
//                        if ($wbtm_form_builder_data) {
//                            foreach ($wbtm_form_builder_data as $_field) {
//                                if ($wbtm_user_ticket_type[$iu] != '') :
//                                    $user[$iu][$_field['wbtm_fbc_id']] = stripslashes(strip_tags($_POST[$_field['wbtm_fbc_id']][$iu]));
//                                endif;
//                            }
//                        }


                        // New
                        if (isset($passenger_label[$counter])) {
                            $passenger_label_s = $passenger_label[$counter];
                        } else {
                            $passenger_label_s = "";
                        }

                        if (isset($passenger_type_num[$counter])) {
                            $passenger_type_num_s = $passenger_type_num[$counter];
                        } else {
                            $passenger_type_num_s = "";
                        }

                        if (isset($fare[$counter])) {
                            $fare_s = $fare[$counter];
                        } else {
                            $fare_s = "";
                        }

                        $wbtmmain->create_bus_passenger($order_id, $bus_id, $user_id, $start, $next_stops, $end, $b_time, $j_time, $_seats, $fare_s, $j_date, $add_datetime, $user_name, $user_email, $passenger_label_s, $passenger_type_num_s, $user_phone, $user_gender, $user_address, $wbtm_extra_bag_qty, $usr_inf, $counter, 2, null, $wbtm_billing_type, $city_zone, $wbtm_pickpoint);
                        $download_url = $magepdf->get_invoice_ajax_url(array('order_id' => $order_id));
                        ?>
                        <div id="message" class="updated notice notice-success is-dismissible">
                            <p><?php _e('Seat Booked Successfully', 'addon-bus--ticket-booking-with-seat-pro') ?>. <a
                                        class="wbtm-button" href="<?php echo $download_url; ?>"
                                        style="background: green;color: #fff;padding: 5px 20px;font-size: 14px;font-style: normal;text-decoration: none;margin: 0 auto;width: 200px;display: inline;text-align: center;"><?php _e('Download Ticket', 'addon-bus--ticket-booking-with-seat-pro'); ?></a>
                            </p>
                            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
                            </button>
                        </div>
                        <?php
                    }
                    $counter++;
                }
                $bus_fare = array_sum($fare) + $extra_bag_price_total;
                update_post_meta($pid, '_order_total', $bus_fare);
            } elseif ($wbtm_order_seat_plan == 'no') {
                $no_seat_total = 0;
                $extra_bag_price_total = 0;
                if ($seat_qty) {

                    $j = 0;
                    foreach ($seat_qty as $key => $qty) {
                        $counter = $j;
                        if ($qty > 0) {
                            for ($i = 0; $i < (int)$qty; $i++) {
                                // Seat
                                $_seats = $passenger_type[$key] . '(1)';
                                $passenger_label_s = $passenger_type[$key];
                                $passenger_type_num_s = $passenger_type_num[$passenger_type[$key]];

                                // Price
                                if ($bus_type === 'sub') { // Subscription Bus
                                    $fare_s = mtsa_bus_price_get($bus_id, $start, $end, $wbtm_billing_type, $passenger_type[$key], $city_zone);
                                    $no_seat_total = $no_seat_total + $fare_s;

                                    $is_return = false;
                                } else { // General Bus
                                    $fare_s = mage_bus_seat_price($bus_id, $start, $end, false, $passenger_type_num[$passenger_type[$key]]);
                                    $no_seat_total = $no_seat_total + $fare_s;
                                }


                                // Custom reg user yes
                                $usr_inf[$j]['wbtm_user_name'] = $user_name = isset($_POST['wbtm_user_name'][$j]) ? $_POST['wbtm_user_name'][$j] : '';
                                $usr_inf[$j]['wbtm_user_email'] = $user_email = isset($_POST['wbtm_user_email'][$j]) ? $_POST['wbtm_user_email'][$j] : '';
                                $usr_inf[$j]['wbtm_user_phone'] = $user_phone = isset($_POST['wbtm_user_phone'][$j]) ? $_POST['wbtm_user_phone'][$j] : '';
                                $usr_inf[$j]['wbtm_user_address'] = $user_address = isset($_POST['wbtm_user_address'][$j]) ? $_POST['wbtm_user_address'][$j] : '';
                                $usr_inf[$j]['wbtm_user_gender'] = $user_gender = isset($_POST['wbtm_user_gender'][$j]) ? $_POST['wbtm_user_gender'][$j] : '';
                                $usr_inf[$j]['wbtm_extra_bag_qty'] = $wbtm_extra_bag_qty = isset($_POST['wbtm_extra_bag_qty'][$j]) ? $_POST['wbtm_extra_bag_qty'][$j] : 0;

                                $extra_bag_price_total = $extra_bag_price_total + ($wbtm_extra_bag_qty * $extra_bag_price);
                                $j++;

                                $wbtmmain->create_bus_passenger($order_id, $bus_id, $user_id, $start, $next_stops, $end, $b_time, $j_time, $_seats, $fare_s, $j_date, $add_datetime, $user_name, $user_email, $passenger_label_s, $passenger_type_num_s, $user_phone, $user_gender, $user_address, $wbtm_extra_bag_qty, $usr_inf = array(), $counter, 2, null, $wbtm_billing_type, $city_zone, $wbtm_pickpoint);
                                $download_url = $magepdf->get_invoice_ajax_url(array('order_id' => $order_id));
                                ?>
                                <div id="message" class="updated notice notice-success is-dismissible">
                                    <p><?php _e('Seat Booked Successfully', 'addon-bus--ticket-booking-with-seat-pro') ?>
                                        . <a
                                                class="wbtm-button" href="<?php echo $download_url; ?>"
                                                style="background: green;color: #fff;padding: 5px 20px;font-size: 14px;font-style: normal;text-decoration: none;margin: 0 auto;width: 200px;display: inline;text-align: center;"><?php _e('Download Ticket', 'addon-bus--ticket-booking-with-seat-pro'); ?></a>
                                    </p>
                                    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
                                    </button>
                                </div>
                                <?php
                            }
                        }
                    }
                    $no_seat_total = $no_seat_total + $extra_bag_price_total;
                    update_post_meta($pid, '_order_total', $no_seat_total);
                }
            }
        }

        $bus_start_route = isset($_GET['bus_start_route']) ? $_GET['bus_start_route'] : '';
        $bus_end_route = isset($_GET['bus_end_route']) ? $_GET['bus_end_route'] : '';
        $j_date = isset($_GET['j_date']) ? $_GET['j_date'] : '';


        ob_start();
        ?>
        <div class="wbtm-page-wrap">
            <div class="wbtm-page-top">
                <div class="wbtm-page-top-inner">
                    <h3>
                        <?php if(has_action('wbtm_admin_purchase_ticket_subscription_tab')) :
                            _e('Select Bus type', 'addon-bus--ticket-booking-with-seat-pro');
                        else :
                            _e('Bus type', 'addon-bus--ticket-booking-with-seat-pro');
                        endif;?>
                    </h3>
                    <ul class="wbtm_tab_link_wrap">
                        <li class="clickme">
                            <button data-tag="general"
                                    class="wbtm_tab_link wbtm_btn_primary wbtm_tab_active"><?php _e('General Bus', 'addon-bus--ticket-booking-with-seat-pro') ?></button>
                        </li>
                        <?php do_action('wbtm_admin_purchase_ticket_subscription_tab') ?>
                    </ul>
                </div>
            </div>
            <div style="clear: both;"></div>
            <div id="container">
                <div class="wbtm_content_item" id="general">
                    <div class="wbtm-search-form-sec the_select">
                        <h3><?php _e('General Bus', 'addon-bus--ticket-booking-with-seat-pro') ?></h3>
                        <form action="" method="GET">
                            <input type="hidden" name="post_type" value="wbtm_bus">
                            <input type="hidden" name="page" value="admin_purchase_ticket">
                            <div class="wbtm_search_form_inner">
                                <div class="wbtm_field_group">
                                    <label for="wbtm_start"><?php _e('Boarding Point', 'addon-bus--ticket-booking-with-seat-pro') ?></label>
                                    <select name="bus_start_route" id="wbtm_start">
                                        <?php foreach ($bus_stops as $route) : ?>
                                            <option value="<?php echo $route->name ?>" <?php echo (($route->name == $bus_start_route) ? 'selected' : ''); ?>><?php echo $route->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="wbtm_field_group">
                                    <label for="wbtm_end"><?php _e('Dropping Point', 'addon-bus--ticket-booking-with-seat-pro') ?></label>
                                    <select name="bus_end_route" id="wbtm_end">
                                        <?php foreach ($bus_stops as $route) : ?>
                                            <option value="<?php echo $route->name ?>" <?php echo (($route->name == $bus_end_route) ? 'selected' : ''); ?>><?php echo $route->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="wbtm_field_group">
                                    <label for="j_date"><?php _e('Journey date', 'addon-bus--ticket-booking-with-seat-pro') ?></label>
                                    <input type="text" id="j_date" name="j_date" value="<?php echo $j_date; ?>">
                                </div>
                                <div class="wbtm_field_group" style="width:100px;">
                                    <input type="submit" name="wbtm_admin_bus_search" class="wbtm_btn_primary"
                                           value="<?php _e('Search', 'addon-bus--ticket-booking-with-seat-pro'); ?>"/>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="bus_list_header do-flex">
                        <div class="bus_img do-flex">
                            <div><p><?php _e('Image', 'addon-bus--ticket-booking-with-seat-pro'); ?></p></div>
                        </div>
                        <div class="left do-flex">
                            <div><p><?php _e('Bus Name', 'addon-bus--ticket-booking-with-seat-pro'); ?></p></div>
                            <div><p><?php _e('Schedule', 'addon-bus--ticket-booking-with-seat-pro'); ?></p></div>
                        </div>
                        <div class="right do-flex">
                            <div><p>Type<?php _e('', 'addon-bus--ticket-booking-with-seat-pro'); ?></p></div>
                            <div><p><?php _e('Seats Available', 'addon-bus--ticket-booking-with-seat-pro'); ?></p></div>
                            <div><p><?php _e('View', 'addon-bus--ticket-booking-with-seat-pro'); ?></p></div>
                        </div>
                    </div>
                    <ul class="">
                        <?php
                        if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) {
                            $start = $_GET['bus_start_route'];
                            $end = $_GET['bus_end_route'];
                            $j_date = $_GET['j_date'];
                            $args_search_qqq = array(
                                'post_type' => array('wbtm_bus'),
                                'posts_per_page' => -1,
                                'order' => 'ASC',
                                'orderby' => 'meta_value',
                                // 'meta_key' => 'wbtm_bus_start_time',
                                'meta_query' => array(
                                    'relation' => 'AND',
                                    array(
                                        'key' => 'wbtm_bus_bp_stops',
                                        'value' => $start,
                                        'compare' => 'LIKE',
                                    ),
                                    array(
                                        'key' => 'wbtm_bus_next_stops',
                                        'value' => $end,
                                        'compare' => 'LIKE',
                                    ),
                                    array(
                                        'relation' => 'OR',
                                        array(
                                            'key' => 'wbtm_seat_type_conf',
                                            'compare' => 'NOT EXISTS',
                                        ),
                                        array(
                                            'key' => 'wbtm_seat_type_conf',
                                            'value' => 'wbtm_seat_plan',
                                            'compare' => '=',
                                        ),
                                        array(
                                            'key' => 'wbtm_seat_type_conf',
                                            'value' => 'wbtm_without_seat_plan',
                                            'compare' => '=',
                                        ),
                                    )
                                )

                            );


                            $loop = new WP_Query($args_search_qqq);
                            while ($loop->have_posts()) {
                                $loop->the_post();
                                $id = get_the_ID();
                                $values = get_post_custom(get_the_id());
                                $term = get_the_terms(get_the_id(), 'wbtm_bus_cat');

                                $total_seat = $values['wbtm_total_seat'][0];
                                $sold_seat = $wbtmmain->wbtm_get_available_seat(get_the_id(), $date);
                                $available_seat = ($total_seat - $sold_seat);

                                $price_arr = get_post_meta(get_the_id(), 'wbtm_bus_prices', true);
                                $bus_bp_array = maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_bp_stops', true));
                                $bus_dp_array = maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_next_stops', true));
                                $bp_time = $wbtmmain->wbtm_get_bus_start_time($start, $bus_bp_array);
                                $dp_time = $wbtmmain->wbtm_get_bus_end_time($end, $bus_dp_array);

                                $bus_meta = get_post_custom(get_the_id());
                                $next_stops_arr = get_post_meta(get_the_id(), 'wbtm_bus_next_stops', true);
                                $wbtm_bus_bp_stops = get_post_meta(get_the_id(), 'wbtm_bus_bp_stops', true);
                                $count = 1;
                                $term = get_the_terms(get_the_id(), 'wbtm_bus_cat');
                                $price_arr = get_post_meta(get_the_id(), 'wbtm_bus_prices', true);
                                $seat_price = mage_bus_seat_price($id, $start, $end, false);
                                // Pickpoint
                                $pickpoints = get_post_meta($id, 'wbtm_selected_pickpoint_name_' . strtolower($start), true);
                                if ($pickpoints != '') {
                                    $pickpoints = maybe_unserialize($pickpoints);
                                }


                                ?>
                                <li>
                                    <div class="admin-bus-list">
                                        <div class="admin-bus-list-item one bus-img">
                                            <?php the_post_thumbnail('thumb'); ?>
                                        </div>
                                        <div class="admin-bus-list-item two">
                                            <div class="">
                                                <strong><?php the_title(); ?></strong>
                                                <?php echo $values['wbtm_bus_no'][0]; ?>
                                            </div>
                                            <div class="">
                                                <strong><?php echo date('h:i A', strtotime($bp_time)); ?></strong>
                                            </div>
                                        </div>
                                        <div class="admin-bus-list-item three">
                                            <div class="">
                                                <strong>AC</strong>
                                            </div>
                                            <div class="">
                                                <span class='seat-left'><?php echo $available_seat; ?> Seat Left</span>
                                            </div>
                                            <div class="" style="text-align: center">
                                                <button class="wbtm_btn_primary admin-general-bus-detail-toggle">
                                                    View Seats
                                                </button>
                                            </div>
                                        </div>
                                    </div> <!-- Bus list header end -->
                                    <!-- Bus Detail -->
                                    <div class="admin-bus-details" data-bus-id="<?php echo $id; ?>"
                                         id="admin-bus-details<?php echo get_the_id() . $wbtmmain->wbtm_make_id($date); ?>">

                                        <form action="" method='post'>
                                            <input type="hidden" name='journey_date' class="text"
                                                   value='<?php echo $date; ?>'/>
                                            <input type="hidden" name='start_stops'
                                                   value="<?php echo $start; ?>"
                                                   class="hidden"/>
                                            <input type='hidden' value='<?php echo $end; ?>'
                                                   name='end_stops'/>
                                            <input type='hidden' value='<?php the_title(); ?>'
                                                   name='bus_name'/>
                                            <input type="hidden" name="bus_id"
                                                   value="<?php echo get_the_id(); ?>">
                                            <input type="hidden" name="wbtm_bus_type" value="general">
                                            <?php
                                            // Bus Seat Type
                                            $bus_seat_type_conf = get_post_meta($id, 'wbtm_seat_type_conf', true);
                                            if ($bus_seat_type_conf === 'wbtm_without_seat_plan') : // Without Seat Plan
                                                // Price
                                                $seatPrices = get_post_meta($id, 'wbtm_bus_prices', true);
                                                $available_seat_type = array();
                                                if ($seatPrices) {
                                                    $i = 0;
                                                    foreach ($seatPrices as $price) {
                                                        if (strtolower($price['wbtm_bus_bp_price_stop']) == strtolower($start) && strtolower($price['wbtm_bus_dp_price_stop']) == strtolower($end)) {
                                                            if ((float)$price['wbtm_bus_price'] > 0) {
                                                                $available_seat_type[$i]['type'] = 'Adult';
                                                                $available_seat_type[$i]['price'] = $price['wbtm_bus_price'];
                                                                $i++;
                                                            }
                                                            if ((float)$price['wbtm_bus_child_price'] > 0) {
                                                                $available_seat_type[$i]['type'] = 'Child';
                                                                $available_seat_type[$i]['price'] = $price['wbtm_bus_child_price'];
                                                                $i++;
                                                            }
                                                            if ((float)$price['wbtm_bus_infant_price'] > 0) {
                                                                $available_seat_type[$i]['type'] = 'Infant';
                                                                $available_seat_type[$i]['price'] = $price['wbtm_bus_infant_price'];
                                                                $i++;
                                                            }
                                                            break;
                                                        }  // end foreach
                                                    }
                                                } // end if
                                                ?>
                                                <input type="hidden" name="wbtm_order_seat_plan" value="no">
                                                <input type="hidden"
                                                       value="<?php echo date('h:i A', strtotime($bp_time)); ?>"
                                                       name="user_start_time"
                                                       id='user_start_time<?php echo get_the_id(); ?>'>
                                                <input type="hidden" name="bus_start_time"
                                                       value="<?php echo date('h:i A', strtotime($bp_time)); ?>"
                                                       id='bus_start_time'>
                                                <div class="mage-no-seat">
                                                    <div class="mage-no-seat-inner">
                                                        <div class="mage-no-seat-left">
                                                            <table class="mage-seat-table mage-bus-short-info">
                                                                <tr>
                                                                    <th><i class="fas fa-map-marker"></i>
                                                                        <?php mage_bus_label('wbtm_boarding_points_text', __('Boarding', 'bus-ticket-booking-with-seat-reservation')); ?>
                                                                    </th>
                                                                    <td><?php echo $start; ?> ( <?php echo $bp_time; ?>
                                                                        )
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th><i class="fas fa-map-marker"></i>
                                                                        <?php mage_bus_label('wbtm_dropping_points_text', __('Dropping', 'bus-ticket-booking-with-seat-reservation')) ?>
                                                                    </th>
                                                                    <td><?php echo $end; ?> ( <?php echo $dp_time; ?>
                                                                        )
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th><i class="fa fa-bus" aria-hidden="true"></i>
                                                                        <?php mage_bus_label('wbtm_type_text', __('Coach Type:', 'bus-ticket-booking-with-seat-reservation')); ?>
                                                                    </th>
                                                                    <td><?php echo mage_bus_type(); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th><i class="fa fa-calendar"
                                                                           aria-hidden="true"></i>
                                                                        <?php mage_bus_label('wbtm_date_text', __('Date:', 'bus-ticket-booking-with-seat-reservation')); ?>
                                                                    </th>
                                                                    <td><?php echo get_wbtm_datetime($date, 'date'); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th><i class="fa fa-clock" aria-hidden="true"></i>
                                                                        <?php mage_bus_label('wbtm_start_time_text', __('Start Time:', 'bus-ticket-booking-with-seat-reservation')) ?>
                                                                    </th>
                                                                    <td><?php echo $bp_time; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th><i class="fa fa-money-alt"></i>
                                                                        <?php mage_bus_label('wbtm_fare_text', __('Fare:', 'bus-ticket-booking-with-seat-reservation')); ?>
                                                                    </th>
                                                                    <td><?php echo wc_price($seat_price); ?> /
                                                                        <?php mage_bus_label('wbtm_seat_text', __('Seat', 'bus-ticket-booking-with-seat-reservation')); ?>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="mage-no-seat-right">
                                                            <table class="mage-seat-table">
                                                                <thead>
                                                                <tr>
                                                                    <th><?php _e('Type', 'bus-ticket-booking-with-seat-reservation'); ?></th>
                                                                    <th><?php _e('Quantity', 'bus-ticket-booking-with-seat-reservation'); ?></th>
                                                                    <th><?php _e('Price', 'bus-ticket-booking-with-seat-reservation'); ?></th>
                                                                    <th><?php _e('SubTotal', 'bus-ticket-booking-with-seat-reservation'); ?></th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php foreach ($available_seat_type as $type) : ?>
                                                                    <tr>
                                                                        <td><?php echo $type['type'] ?></td>
                                                                        <td class="mage-seat-qty">
                                                                            <button class="wbtm-qty-change wbtm-qty-dec"
                                                                                    data-qty-change="dec">-
                                                                            </button>
                                                                            <input class="qty-input" type="text"
                                                                                   data-seat-type="<?php echo strtolower($type['type']); ?>"
                                                                                   data-price="<?php echo $type['price']; ?>"
                                                                                   name="seat_qty[]"/>
                                                                            <button class="wbtm-qty-change wbtm-qty-inc"
                                                                                    data-qty-change="inc">+
                                                                            </button>
                                                                            <input type="hidden" name="passenger_type[]"
                                                                                   value="<?php echo $type['type'] ?>">
                                                                            <input type="hidden" name="bus_dd[]"
                                                                                   value="no">
                                                                        </td>
                                                                        <td><?php echo get_woocommerce_currency_symbol() . $type['price'] . '<sub> / seat</sub>'; ?>
                                                                        </td>
                                                                        <td class="mage-seat-price">
                                                                            <?php echo get_woocommerce_currency_symbol() . '<span class="price-figure">0.00</span>' ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                                </tbody>
                                                                <tfoot>
                                                                <tr>
                                                                    <td colspan="4"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td>
                                                                        <strong><?php _e('Total', 'bus-ticket-booking-with-seat-reservation'); ?>
                                                                            :</strong></td>
                                                                    <td class="mage-price-total">
                                                                        <strong><?php echo get_woocommerce_currency_symbol(); ?>
                                                                            <span
                                                                                    class="price-figure">0.00</span></strong>
                                                                    </td>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                            <?php if ($pickpoints) : ?>
                                                                <div class="wbtm-pickpoint-wrap">
                                                                    <label for="wbtm-pickpoint-no-seat"><?php _e('Pickup Point:', 'bus-ticket-booking-with-seat-reservation') ?></label>
                                                                    <select name="wbtm_pickpoint"
                                                                            id="wbtm-pickpoint-no-seat" required>
                                                                        <option value=""><?php _e('Select Pickup Point', 'bus-ticket-booking-with-seat-reservation') ?></option>
                                                                        <?php foreach ($pickpoints as $point) :
                                                                            $d = ucfirst($point['pickpoint']) . ' [' . $point['time'] . ']';
                                                                            ?>
                                                                            <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="wbtm-form-builder">
                                                        <img class="wbtm-loading"
                                                             src="<?php echo plugin_dir_url(__FILE__) . '../../' . '/images/new-loading.gif'; ?>"
                                                             alt=""/>
                                                        <div class="wbtm-form-builder-adult wbtm-form-builder-type-wrapper mage_customer_info_area"></div>
                                                        <div class="wbtm-form-builder-child wbtm-form-builder-type-wrapper mage_customer_info_area"></div>
                                                        <div class="wbtm-form-builder-infant wbtm-form-builder-type-wrapper mage_customer_info_area"></div>
                                                    </div>
                                                    <button class="mage_button no-seat-submit-btn wbtm_btn_primary"
                                                            disabled
                                                            type="submit" name="add-to-cart-admin"
                                                            value="<?php echo get_post_meta($id, 'link_wc_product', true); ?>"
                                                            class="single_add_to_cart_button">
                                                        <?php mage_bus_label('wbtm_book_now_text', __('Book Now', 'bus-ticket-booking-with-seat-reservation')); ?>
                                                    </button>
                                                </div>
                                            <?php else : // With Seat Plan ?>
                                                <input type="hidden" name="wbtm_order_seat_plan" value="yes">
                                                <div style="display:flex;justify-content:space-between">
                                                    <?php $wbtmmain->wbtm_bus_seat_plan($wbtmmain->wbtm_get_this_bus_seat_plan(), $start, $date); ?>
                                                    <div class="bus-info-sec">
                                                        <?php
                                                        $price_arr = maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_prices', true));
                                                        $fare = $wbtmmain->wbtm_get_bus_price($start, $end, $price_arr);
                                                        ?>

                                                        <div class="top-search-section">
                                                            <div class="leaving-list">
                                                                <h6>Route</h6>
                                                                <div class="selected_route">
                                                                    <?php printf('<span>%s <i class="fa fa-long-arrow-right"></i> %s<span>', $start, $end); ?>
                                                                    (<?php echo get_woocommerce_currency_symbol(); ?><?php echo $wbtmmain->wbtm_get_bus_price($start, $end, $price_arr); ?>
                                                                    )
                                                                    <input type="hidden" name="bus_fare"
                                                                           value="<?php echo $wbtmmain->wbtm_get_bus_price($start, $end, $price_arr); ?>">
                                                                </div>
                                                            </div>
                                                            <div class="leaving-list">
                                                                <h6>Date</h6>
                                                                <div class="selected_date">
                                                                    <?php printf('<span>%s</span>', date('jS F, Y', strtotime($date))); ?>
                                                                </div>
                                                            </div>
                                                            <div class="leaving-list">
                                                                <h6>Start & Arrival Time</h6>
                                                                <div class="selected_date">
                                                                    <?php
                                                                    // $bus_bp_array = get_post_meta(get_the_id(),'wbtm_bus_bp_stops',true);
                                                                    // $bus_dp_array = get_post_meta(get_the_id(),'wbtm_bus_next_stops',true);
                                                                    // $bp_time = $wbtmmain->wbtm_get_bus_start_time($start, $bus_bp_array);
                                                                    // $dp_time = $wbtmmain->wbtm_get_bus_end_time($end, $bus_dp_array);
                                                                    echo date('h:i A', strtotime($bp_time)) . ' <i class="fa fa-long-arrow-right"></i> ' . date('h:i A', strtotime($dp_time));
                                                                    ?>
                                                                    <input type="hidden"
                                                                           value="<?php echo date('h:i A', strtotime($bp_time)); ?>"
                                                                           name="user_start_time"
                                                                           id='user_start_time<?php echo get_the_id(); ?>'>
                                                                    <input type="hidden" name="bus_start_time"
                                                                           value="<?php echo date('h:i A', strtotime($bp_time)); ?>"
                                                                           id='bus_start_time'>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="seat-selected-list-fare">
                                                            <table class="selected-seat-list<?php echo get_the_id() . $wbtmmain->wbtm_make_id($date); ?> selected-seat-table">
                                                                <tr class='list_head<?php echo get_the_id() . $wbtmmain->wbtm_make_id($date); ?>'>
                                                                    <th>Seat No</th>
                                                                    <th>Type</th>
                                                                    <th>Fare</th>
                                                                    <th>Remove</th>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center">Total <span
                                                                                id='total_seat<?php echo get_the_id() . $wbtmmain->wbtm_make_id($date); ?>_booked'></span><input
                                                                                type="hidden" value=""
                                                                                id="tq<?php echo get_the_id() . $wbtmmain->wbtm_make_id($date); ?>"
                                                                                name='total_seat' class="number"/></td>
                                                                    <td></td>
                                                                    <td align="center"><input type="hidden" value=""
                                                                                              id="tfi<?php echo get_the_id() . $wbtmmain->wbtm_make_id($date); ?>"
                                                                                              class="number"/><span
                                                                                id="totalFare<?php echo get_the_id() . $wbtmmain->wbtm_make_id($date); ?>"></span>
                                                                    </td>
                                                                    <td></td>
                                                                </tr>
                                                            </table>
                                                            <?php if ($pickpoints) : ?>
                                                                <div class="wbtm-pickpoint-wrap">
                                                                    <label for="wbtm-pickpoint-no-seat"><?php _e('Pickup Point:', 'bus-ticket-booking-with-seat-reservation') ?></label>
                                                                    <select name="wbtm_pickpoint"
                                                                            id="wbtm-pickpoint-no-seat" required>
                                                                        <option value=""><?php _e('Select Pickup Point', 'bus-ticket-booking-with-seat-reservation') ?></option>
                                                                        <?php foreach ($pickpoints as $point) :
                                                                            $d = ucfirst($point['pickpoint']) . ' [' . $point['time'] . ']';
                                                                            ?>
                                                                            <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div id="divParent<?php echo get_the_id() . $wbtmmain->wbtm_make_id($date); ?>"></div>
                                                            <button id='bus-booking-btn<?php echo get_the_id() . $wbtmmain->wbtm_make_id($date); ?>'
                                                                    type="submit" name="add-to-cart-admin"
                                                                    value="<?php echo esc_attr(get_the_id()); ?>"
                                                                    class="single_add_to_cart_button button alt btn-mep-event-cart wbtm_btn_primary">
                                                                Book Now
                                                            </button>
                                                        </div>

                                                    </div>
                                                </div>

                                                <?php
                                                $uid = get_the_id() . $wbtmmain->wbtm_make_id($date);
                                                $wbtmmain->wbtm_seat_booking_js($uid, $fare);
                                            endif; ?>
                                        </form>
                                    </div>

                                    <?php

                                    ?>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </div>
                <div class="wbtm_content_item hide" id="subscription">
                    <div class="wbtm_subscription_inner">
                        <?php if (has_action('subscription_admin_order')) {
                            do_action('subscription_admin_order');
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }


    add_action('woocommerce_order_status_changed', 'wbtm_pro_bus_ticket_seat_management', 10, 99);
    function wbtm_pro_bus_ticket_seat_management($order_id, $from_status, $to_status, $order)
    {
        global $wpdb;
        // Getting an instance of the order object
        $order = wc_get_order($order_id);
        $order_meta = get_post_meta($order_id);
        $created_by = get_post_meta($order_id, '_created_via', true);
        $fare = $order->get_total();

        if ($order->has_status('processing') || $order->has_status('pending') || $order->has_status('on-hold')) {
            if ($created_by == 'Admin Dashboard') {
                $status = 1;
                $table_name = $wpdb->prefix . 'wbtm_bus_booking_list';
                $wpdb->query($wpdb->prepare("UPDATE $table_name 
                SET status = %d 
             WHERE order_id = %d", $status, $order_id)
                );
                // $fare = wbtm_get_total_of_order($order_id);
                update_post_meta($order_id, '_order_total', $fare);
            }
        }

        if ($order->has_status('cancelled')) {
            if ($created_by == 'Admin Dashboard') {
                $status = 3;
                $table_name = $wpdb->prefix . 'wbtm_bus_booking_list';
                $wpdb->query($wpdb->prepare("UPDATE $table_name 
                SET status = %d 
             WHERE order_id = %d", $status, $order_id)
                );
                // $fare = wbtm_get_total_of_order($order_id);
                update_post_meta($order_id, '_order_total', $fare);
            }
        }

        if ($order->has_status('completed')) {
            if ($created_by == 'Admin Dashboard') {
                $status = 2;
                $table_name = $wpdb->prefix . 'wbtm_bus_booking_list';
                $wpdb->query($wpdb->prepare("UPDATE $table_name 
                SET status = %d 
             WHERE order_id = %d", $status, $order_id)
                );
                // $fare = wbtm_get_total_of_order($order_id);
                update_post_meta($order_id, '_order_total', $fare);
            }
        }

    }

}