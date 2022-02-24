<?php


add_action('admin_init', 'passenger_list_init');
function passenger_list_init()
{
    global $pagination_per_page;
    $pagination_per_page = 50;

    if (!isset($_COOKIE["pagination_limit"])) {
        setcookie("pagination_limit", $pagination_per_page, time() + 30 * 24 * 60 * 60);
    }
}

add_action('admin_menu', 'wbtm_passenger_list_menu');

function wbtm_passenger_list_menu()
{
    add_submenu_page('edit.php?post_type=wbtm_bus', __('Passenger List', 'addon-bus--ticket-booking-with-seat-pro'), __('Passenger List', 'wbtm-menu'), 'manage_options', 'passenger_list', 'wbtm_passenger_list');
}


function wbtm_passenger_list()
{
    global $wpdb, $magepdf, $wbtmmain, $pagination_per_page;
    $table_name = $wpdb->prefix . "wbtm_bus_booking_list";

    if (isset($_POST['pagination_limit'])) {
        $limit = $_POST['pagination_limit'];
        setcookie("pagination_limit", $_POST['pagination_limit'], time() + 30 * 24 * 60 * 60);
    } else {
        if (isset($_COOKIE["pagination_limit"])) {
            $limit = $_COOKIE["pagination_limit"];
        } else {
            $limit = $pagination_per_page;
        }
    }

    if (isset($_GET['action']) && $_GET['action'] == 'delete_seat') {
        // echo 'Yes Did';die;
        $booking_id = strip_tags($_GET['booking_id']);
        $status = 5;
        $del = update_post_meta($booking_id, 'wbtm_status', $status);
        if ($del) {
            ?>
            <div id="message" class="updated notice notice-success is-dismissible">
                <p><?php _e('Seat Deleted', 'addon-bus--ticket-booking-with-seat-pro'); ?></p>
                <button type="button" class="notice-dismiss"><span
                            class="screen-reader-text"><?php _e('Dismiss this notice.', 'addon-bus--ticket-booking-with-seat-pro'); ?></span>
                </button>
            </div>
            <?php
        }
    }

    if (isset($_GET['action']) && $_GET['action'] == 'restore_seat') {
        $booking_id = strip_tags($_GET['booking_id']);
        $status = 1;
        $restore = update_post_meta($booking_id, 'wbtm_status', $status);
        if ($restore) {
            ?>
            <div id="message" class="updated notice notice-success is-dismissible">
                <p><?php _e('Seat restore successfully', 'addon-bus--ticket-booking-with-seat-pro'); ?></p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'addon-bus--ticket-booking-with-seat-pro'); ?></span>
                </button>
            </div>
            <?php
        }
    }

    if (isset($_GET['bus_id']) && isset($_GET['j_date'])) {
        $bus_id = strip_tags($_GET['bus_id']);
        $j_date = strip_tags($_GET['j_date']);
        $bid_arr = explode('-', $bus_id);
        // $bid = $bid_arr[0];
        // $btime = $bid_arr[1];

    } else {
        $bus_id = 0;
        $j_date = date('Y-m-d');
    }

    $isSub = false;
    $isZone = false;
    if (isset($_GET['bus_type'])) $isSub = $_GET['bus_type'] == 'sub' ? true : false;
    if (isset($_GET['route_type'])) $isZone = $_GET['route_type'] == 'city_zone' ? true : false;

    ?>
    <div class="wrap">
        <h2><?php _e('Passenger List', 'addon-bus--ticket-booking-with-seat-pro') ?></h2>
        <?php 
        if(isset($_GET['action']) && $_GET['action'] == 'deleted_list') : 
            $args = array(
                'post_type' => 'wbtm_bus_booking',
                'posts_per_page' => '-1',
                'order' => 'DESC',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'wbtm_status',
                        'compare' => '=',
                        'value' => '5',
                    )
                )
            );
            $passenger_deleted = new WP_Query($args);


        ?>
        <a href="<?php echo get_admin_url(); ?>edit.php?post_type=wbtm_bus&page=passenger_list"><?php _e('Go back', 'addon-bus--ticket-booking-with-seat-pro') ?></a>
        <table class="wbtm_deleted_table">
            <thead>
                <tr>
                    <th><?php _e('Sl', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                    <th><?php _e('Order no', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                    <th><?php _e('Name', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                    <th><?php _e('Price', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                    <th><?php _e('Bus Name', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                    <th><?php _e('Booking Date', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                    <th><?php _e('Journey Date', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                    <th><?php _e('Boarding', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                    <th><?php _e('Dropping', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                    <th><?php _e('Action', 'addon-bus--ticket-booking-with-seat-pro') ?></th>
                </tr>
            </thead>
            <?php if($passenger_deleted->found_posts > 0) : ?>
                <tbody>
                <?php 
                while($passenger_deleted->have_posts()) {
                    $passenger_deleted->the_post();
                    $d_id = get_the_ID();
                    
                    echo '<tr>';
                    echo '<td>'.($passenger_deleted->current_post + 1).'</td>';
                    echo '<td>'.get_post_meta($d_id, 'wbtm_order_id', true).'</td>';
                    echo '<td>'.get_post_meta($d_id, 'wbtm_user_name', true).'</td>';
                    echo '<td>'.get_post_meta($d_id, 'wbtm_bus_fare', true).'</td>';
                    echo '<td>'.get_the_title(get_post_meta($d_id, 'wbtm_bus_id', true)).'</td>';
                    echo '<td>'.get_post_meta($d_id, 'wbtm_booking_date', true).'</td>';
                    echo '<td>'.get_post_meta($d_id, 'wbtm_journey_date', true).'</td>';
                    echo '<td>'.get_post_meta($d_id, 'wbtm_boarding_point', true).'</td>';
                    echo '<td>'.get_post_meta($d_id, 'wbtm_droping_point', true).'</td>';
                    echo '<td><a href="'.get_admin_url(); ?>edit.php?post_type=wbtm_bus&page=passenger_list&action=restore_seat&booking_id=<?php echo $d_id.'" onclick="javascript:return confirm(\'Are you sure you want to restore this seat?\')">'.__('Restore', 'addon-bus--ticket-booking-with-seat-pro').'</a></td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
            <?php endif; wp_reset_postdata();?>
        </table>

        <?php else : ?>
            <form action="<?php echo get_admin_url(); ?>edit.php?post_type=wbtm_bus&page=passenger_list" method="get">
            <input type="hidden" name="post_type" value="wbtm_bus">
            <input type="hidden" name="page" value="passenger_list">

            <div class="mage-custom-filter-area">
                <span class="mage-custom-filter-text"><?php _e('Filter list', 'addon-bus--ticket-booking-with-seat-pro') ?></span>
                <a href="?post_type=wbtm_bus&page=passenger_list"
                   class="mage-custom-filter-clear"><?php _e('Clear all filter', 'addon-bus--ticket-booking-with-seat-pro') ?></a>
                <?php do_action('mtsa_bus_type_fiter', $isSub, $isZone) ?>
                <div class="mage-custom-filter-item">
                    <label for="mage-f-bus-type">Bus Name</label>
                    <select id="mage-f-bus-type" name="bus_id">
                        <option value=""><?php _e('By Bus', 'addon-bus--ticket-booking-with-seat-pro'); ?></option>
                        <?php
                        $args = array(
                            'post_type' => 'wbtm_bus',
                            'posts_per_page' => -1
                        );
                        $loop = new WP_Query($args);
                        while ($loop->have_posts()) {
                            $loop->the_post();
                            $start = get_post_meta(get_the_id(), 'wbtm_bus_no', true);
                            $busit = get_the_id() . "-" . $start;
                            ?>
                            <option value="<?php echo $busit; ?>" <?php if ($busit == $bus_id) {
                                echo 'selected';
                            } ?>><?php the_title(); ?> - <?php echo $start; ?></option>
                            <?php
                        }
                        wp_reset_postdata();
                        ?>
                    </select>
                </div>

                <div class="mage-custom-filter-item">
                    <label for="mage-f-journey-date">Journey Date</label>
                    <input id="mage-f-journey-date" type="text" id="ja_date" name="j_date"
                           placeholder="<?php _e('By Journey Date', 'addon-bus--ticket-booking-with-seat-pro') ?>"
                           value="<?php echo(isset($_GET['j_date']) ? $_GET['j_date'] : '') ?>">
                </div>

                <div class="mage-custom-filter-item">
                    <label for="filter_name">Passenger Name</label>
                    <input type="text" id="filter_name" name="filter_name"
                           placeholder="<?php _e('By Name', 'addon-bus--ticket-booking-with-seat-pro') ?>"
                           value="<?php echo(isset($_GET['filter_name']) ? $_GET['filter_name'] : '') ?>">
                </div>

                <div class="mage-custom-filter-item">
                    <label for="filter_email">Passenger Email</label>
                    <input type="text" id="filter_email" name="filter_email"
                           placeholder="<?php _e('By Email', 'addon-bus--ticket-booking-with-seat-pro') ?>"
                           value="<?php echo(isset($_GET['filter_email']) ? $_GET['filter_email'] : '') ?>">
                </div>

                <div class="mage-custom-filter-item">
                    <label for="filter_phone">Passenger Phone</label>
                    <input type="text" id="filter_phone" name="filter_phone"
                           placeholder="<?php _e('By Phone no', 'addon-bus--ticket-booking-with-seat-pro') ?>"
                           value="<?php echo(isset($_GET['filter_phone']) ? $_GET['filter_phone'] : '') ?>">
                </div>

                <div class="mage-custom-filter-item">
                    <label for="filter_booking_date">Booking Date</label>
                    <input type="text" id="filter_booking_date" name="filter_booking_date"
                           placeholder="<?php _e('Booking Date', 'addon-bus--ticket-booking-with-seat-pro') ?>"
                           value="<?php echo(isset($_GET['filter_booking_date']) ? $_GET['filter_booking_date'] : '') ?>">
                </div>

                <div class="mage-custom-filter-item">
                    <label for="filter_order_id">Order Id</label>
                    <input type="text" id="filter_order_id" name="filter_order_id"
                           placeholder="<?php _e('By Order Id', 'addon-bus--ticket-booking-with-seat-pro') ?>"
                           value="<?php echo(isset($_GET['filter_order_id']) ? $_GET['filter_order_id'] : '') ?>">
                </div>


                <div class="mage-custom-filter-item">
                    <button type="submit"><?php _e('Filter', 'addon-bus--ticket-booking-with-seat-pro') ?></button>
                </div>

            </div>

        </form>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-direction: row-reverse;margin:20px 0;">
            <div class="alignright">
                <form method='get' action="edit.php">
                    <input type="hidden" name='bus_id'
                           value="<?php echo(isset($_GET['bus_id']) ? $_GET['bus_id'] : '') ?>"/>
                    <input type="hidden" name='bus_type'
                           value="<?php echo(isset($_GET['bus_type']) ? $_GET['bus_type'] : 'general') ?>"/>
                    <input type="hidden" name='route_type'
                           value="<?php echo(isset($_GET['route_type']) ? $_GET['route_type'] : '') ?>"/>
                    <input type="hidden" name='j_date'
                           value="<?php echo(isset($_GET['j_date']) ? $_GET['j_date'] : '') ?>"/>
                    <input type="hidden" name="filter_name"
                           value="<?php echo(isset($_GET['filter_name']) ? $_GET['filter_name'] : '') ?>">
                    <input type="hidden" name="filter_email"
                           value="<?php echo(isset($_GET['filter_email']) ? $_GET['filter_email'] : '') ?>">
                    <input type="hidden" name="filter_phone"
                           value="<?php echo(isset($_GET['filter_phone']) ? $_GET['filter_phone'] : '') ?>">
                    <input type="hidden" name="filter_booking_date"
                           value="<?php echo(isset($_GET['filter_booking_date']) ? $_GET['filter_booking_date'] : '') ?>">
                    <input type="hidden" name="filter_order_id"
                           value="<?php echo(isset($_GET['filter_order_id']) ? $_GET['filter_order_id'] : '') ?>">
                    <input type="hidden" name="post_type" value="wbtm_bus">
                    <input type="hidden" name="page" value="passenger_list">
                    <input type="hidden" name='noheader' value="1"/>
                    <input type="hidden" name='action' value="export_passenger_list"/>
                    <input style="display:none" type="radio" name='format' id="formatCSV" value="csv"
                           checked="checked"/>
                    <input type="submit" name='export' id="csvExport" value="Export to CSV"/>
                </form>
            </div>


            <?php

            // Passenger List query and pagination data
            $current_page = (isset($_GET['paged']) ? $_GET['paged'] : 1);

            $offset = ($current_page * $limit) - $limit;

            // Common Meta
            $common = array(
                'relation' => 'OR',
                array(
                    'key' => 'wbtm_status',
                    'value' => 1,
                    'compare' => '='
                ),
                array(
                    'key' => 'wbtm_status',
                    'value' => 2,
                    'compare' => '='
                ),
            );

            $meta_query = array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'wbtm_status',
                        'value' => 1,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'wbtm_status',
                        'value' => 2,
                        'compare' => '='
                    ),
                ),
            );

            $filter_query = array(
                'relation' => 'AND',
            );

            // Filter Meta
            $has_filter = false;
            $filter_text = '';

            // Bus Id
            $bus_id = (isset($_GET['bus_id']) ? $_GET['bus_id'] : null);
            if ($bus_id) {
                $bus_id = explode('-', $bus_id);
                $bus_id = $bus_id[0];

                array_push($filter_query, array(
                    'key' => 'wbtm_bus_id',
                    'value' => $bus_id,
                    'compare' => '='
                ));
                $has_filter = true;
                $filter_text .= ' <span class="mage-filted_item">Bus -> ' . $bus_id . '</span>';
            }
            // Journey Date
            $j_date = (isset($_GET['j_date']) ? $_GET['j_date'] : null);
            if ($j_date) {
                array_push($filter_query, array(
                    'key' => 'wbtm_journey_date',
                    'value' => $j_date,
                    'compare' => '='
                ));
                $has_filter = true;
                $filter_text .= ' <span class="mage-filted_item">Journey Date -> ' . $j_date . '</span>';
            }
            // Name
            $filter_name = (isset($_GET['filter_name']) ? $_GET['filter_name'] : null);
            if ($filter_name) {
                array_push($filter_query, array(
                    'key' => 'wbtm_user_name',
                    'value' => $filter_name,
                    'compare' => 'LIKE'
                ));
                $has_filter = true;
                $filter_text .= ' <span class="mage-filted_item">Customer Name -> ' . $filter_name . '</span>';
            }
            // Email
            $filter_email = (isset($_GET['filter_email']) ? $_GET['filter_email'] : null);
            if ($filter_email) {
                array_push($filter_query, array(
                    'key' => 'wbtm_user_email',
                    'value' => $filter_email,
                    'compare' => '='
                ));
                $has_filter = true;
                $filter_text .= ' <span class="mage-filted_item">Customer Email -> ' . $filter_email . '</span>';
            }
            // Phone No
            $filter_phone = (isset($_GET['filter_phone']) ? $_GET['filter_phone'] : null);
            if ($filter_phone) {
                array_push($filter_query, array(
                    'key' => 'wbtm_user_phone',
                    'value' => $filter_phone,
                    'compare' => '='
                ));
                $has_filter = true;
                $filter_text .= ' <span class="mage-filted_item">Customer Phone no -> ' . $filter_phone . '</span>';
            }
            // Booking Date
            $filter_booking_date = (isset($_GET['filter_booking_date']) ? $_GET['filter_booking_date'] : null);
            if ($filter_booking_date) {
                array_push($filter_query, array(
                    'key' => 'wbtm_booking_date',
                    'value' => $filter_booking_date,
                    'compare' => 'LIKE'
                ));
                $has_filter = true;
                $filter_text .= ' <span class="mage-filted_item">Customer Phone no -> ' . $filter_booking_date . '</span>';
            }
            // Order ID
            $filter_order_id = (isset($_GET['filter_order_id']) ? $_GET['filter_order_id'] : null);
            if ($filter_order_id) {
                array_push($filter_query, array(
                    'key' => 'wbtm_order_id',
                    'value' => $filter_order_id,
                    'compare' => '='
                ));
                $has_filter = true;
                $filter_text .= ' <span class="mage-filted_item">Order ID -> ' . $filter_order_id . '</span>';
            }

            // Bus Type
            $bus_type = isset($_GET['bus_type']) ? $_GET['bus_type'] : 'general';
            if ($bus_type === 'sub') {
                array_push($filter_query, array(
                    'key' => 'wbtm_billing_type',
                    'value' => null,
                    'compare' => '!='
                ));
                $has_filter = true;
                $filter_text .= ' <span class="mage-filted_item">Bus Type -> Subscription</span>';
            } else {
                $generalType = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'wbtm_billing_type',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key' => 'wbtm_billing_type',
                        'value' => '',
                        'compare' => '=',
                    ),
                );
                array_push($filter_query, $generalType);
                $has_filter = true;
                $filter_text .= ' <span class="mage-filted_item">Bus Type -> General</span>';
            }

            // Route Type
            $route_type = isset($_GET['route_type']) ? $_GET['route_type'] : '';
            if ($route_type == 'city_zone') {
                array_push($filter_query, array(
                    'key' => 'wbtm_city_zone',
                    'value' => null,
                    'compare' => '!='
                ));
                $has_filter = true;
                $filter_text .= '<span class="mage-filted_item"> Route Type -> Zone</span>';
            }

            if ($has_filter) {
                array_push($meta_query, $filter_query);
            }
            // array_push($meta_query, $common);

            // Custom FIeld
            $get_settings = get_option('wbtm_bus_settings');
            $get_val = isset($get_settings['custom_fields']) ? $get_settings['custom_fields'] : '';
            $custom_fields = $get_val ? $get_val : null;

            $custom_heading_html = '';
            $custom_tbody_html = '';
            if ($custom_fields) {
                $custom_fields_arr = explode(',', $custom_fields);
                if ($custom_fields_arr) {
                    foreach ($custom_fields_arr as $item) {
                        $custom_heading_html .= '<th>' . $item . '</th>';
                    }
                }
            }

            // Defautl Billing field
            $billing_default_heading = '';

            $billing_default_fields_setting = $wbtmmain->bus_get_option('default_billing_fields_setting', 'ticket_manager_settings', array());

            $p_name = (in_array('p_name', $billing_default_fields_setting) ? true : false);
            $p_phone = (in_array('p_phone', $billing_default_fields_setting) ? true : false);
            $p_email = (in_array('p_email', $billing_default_fields_setting) ? true : false);
            $p_company = (in_array('p_company', $billing_default_fields_setting) ? true : false);
            $p_address = (in_array('p_address', $billing_default_fields_setting) ? true : false);
            $p_city = (in_array('p_city', $billing_default_fields_setting) ? true : false);
            $p_state = (in_array('p_state', $billing_default_fields_setting) ? true : false);
            $p_postcode = (in_array('p_postcode', $billing_default_fields_setting) ? true : false);
            $p_country = (in_array('p_country', $billing_default_fields_setting) ? true : false);
            $p_total_paid = (in_array('p_total_paid', $billing_default_fields_setting) ? true : false);
            $p_payment_method = (in_array('p_payment_method', $billing_default_fields_setting) ? true : false);


            if ($p_name) {
                $billing_default_heading .= "<th>" . __('Name', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            if ($p_phone) {
                $billing_default_heading .= "<th>" . __('Phone', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            if ($p_email) {
                $billing_default_heading .= "<th>" . __('Email', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            if ($p_company) {
                $billing_default_heading .= "<th>" . __('Company', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            if ($p_address) {
                $billing_default_heading .= "<th>" . __('Address', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            if ($p_city) {
                $billing_default_heading .= "<th>" . __('City', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            if ($p_state) {
                $billing_default_heading .= "<th>" . __('State', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            if ($p_postcode) {
                $billing_default_heading .= "<th>" . __('Postcode', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            if ($p_country) {
                $billing_default_heading .= "<th>" . __('Country', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            if ($p_country) {
                $billing_default_heading .= "<th>" . __('Country', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }
            // if($p_total_paid) {
            //     $billing_default_heading .= "<th>".__('Total Paid', 'addon-bus--ticket-booking-with-seat-pro')."</th>";
            // }
            if ($p_payment_method) {
                $billing_default_heading .= "<th>" . __('Payment Method', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }

            // Defautl Billing field END

            // Subcscription
            $subscription_fields_heading = '';
            $bus_type = isset($_GET['bus_type']) ? $_GET['bus_type'] : 'general';
            if ($bus_type == 'sub') {
                $subscription_fields_heading .= "<th>" . __('Valid till', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
                $subscription_fields_heading .= "<th>" . __('Billing Type', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
                $subscription_fields_heading .= "<th>" . __('Zone', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
                $subscription_fields_heading .= "<th>" . __('Check in', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
            }


            // Route Type END
            // Subcscription ENd

            ?>

            <?php echo ($filter_text != null) ? '<p class="mage-filted-text">Search By ->' . $filter_text . '</p>' : ''; ?>
        </div>
        <div class="wbtm-table-wrap" style="overflow-x: auto">
            <div style="display: flex;justify-content: flex-end;align-items:center;">
                <div class="restore-data-list-wrap">
                    <a href="<?php echo get_admin_url(); ?>edit.php?post_type=wbtm_bus&page=passenger_list&action=deleted_list" style="padding: 5px;color: #946826;cursor: pointer;"><?php _e('Show removed passeger list', 'addon-bus--ticket-booking-with-seat-pro') ?></a>
                </div>
                <form action="" method="POST"
                      style="min-width: 230px;margin-right: 0;display: flex;align-items: center;justify-content: flex-end;">
                    <label for=""
                           style="margin-right:5px"><?php _e('Row Per Page', 'addon-bus--ticket-booking-with-seat-pro') ?></label>
                    <input type="number" name="pagination_limit"
                           value="<?php echo isset($_COOKIE["pagination_limit"]) ? $_COOKIE["pagination_limit"] : '' ?>"
                           style="width:17px;border-radius: 0;border-color: #ccd0d4;margin: 0;flex-basis: 50%;">
                </form>
            </div>
            <table class="wp-list-table widefat striped posts" style="width: 100%">
                <thead>
                <tr>
                    <th>Sl</th>
                    <?php
                    if ($billing_default_heading != '') {
                        echo $billing_default_heading;
                    }
                    ?>
                    <?php
                    if ($custom_heading_html != '') {
                        echo $custom_heading_html;
                    }
                    ?>
                    <th><?php _e('Seat', 'addon-bus--ticket-booking-with-seat-pro'); ?></th>
                    <th><?php _e('Order', 'addon-bus--ticket-booking-with-seat-pro'); ?></th>
                    <th><?php _e('Bus Name', 'addon-bus--ticket-booking-with-seat-pro'); ?></th>
                    <th><?php _e('Booking Date', 'addon-bus--ticket-booking-with-seat-pro'); ?></th>
                    <?php

                    if ($subscription_fields_heading) {
                        echo "<th>" . __('Start Date', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
                        echo $subscription_fields_heading;
                        if ($route_type != 'city_zone') {
                            echo "<th>" . __('From', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
                            echo "<th>" . __('To', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
                        }
                    } else {
                        echo "<th>" . __('Journey Date', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
                        echo "<th>" . __('From', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
                        echo "<th>" . __('To', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
                        echo "<th>" . __('Pickup Point', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";

                    } ?>

                    <th><?php _e('Price', 'addon-bus--ticket-booking-with-seat-pro'); ?></th>
                    <th><?php _e('Status', 'addon-bus--ticket-booking-with-seat-pro'); ?></th>
                    <th><?php _e('Action', 'addon-bus--ticket-booking-with-seat-pro'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $limit = (int)$limit;
                $args = array(
                    'post_type' => 'wbtm_bus_booking',
                    'posts_per_page' => $limit,
                    'paged' => $current_page,
                    'offset' => $offset,
                    'order' => 'DESC',
                    'meta_query' => $meta_query
                );
                $passenger = new WP_Query($args);


                $res = $passenger->posts;

                $total = $passenger->found_posts;
                $pages = ceil($total / $limit);
                wp_reset_postdata();

                $index = $offset + 1;
                foreach ($res as $_passger) {
                    $passenger_id = $_passger->ID;
                    $order_id = get_post_meta($passenger_id, 'wbtm_order_id', true);
                    $order = wc_get_order($order_id);
                    $order_meta_data = get_post_meta($order_id);
                    $download_url = $magepdf->get_invoice_ajax_url(array('order_id' => $order_id));

                    // Custom Field
                    $custom_tbody_html = '';
                    if ($custom_fields) {
                        $custom_fields_arr = explode(',', $custom_fields);
                        if ($custom_fields_arr) {
                            foreach ($custom_fields_arr as $item) {
                                $custom_tbody_html .= '<td>' . get_post_meta($passenger_id, 'wbtm_custom_field' . $item, true) . '</td>';
                            }
                        }
                    }

                    // Default Field
                    $billing_default_body = '';
                    if ($p_name) {
                        $billing_default_body .= "<td>" . get_post_meta($passenger_id, 'wbtm_user_name', true) . "</td>";
                    }
                    if ($p_phone) {
                        $billing_default_body .= "<td>" . get_post_meta($passenger_id, 'wbtm_user_phone', true) . "</td>";
                    }
                    if ($p_email) {
                        $billing_default_body .= "<td>" . get_post_meta($passenger_id, 'wbtm_user_email', true) . "</td>";
                    }
                    if ($p_company) {
                        $billing_default_body .= "<td>" . $order_meta_data['_billing_company'][0] . "</td>";
                    }
                    if ($p_address) {
                        $billing_default_body .= "<td>" . $order_meta_data['_billing_address_1'][0] . "</td>";
                    }
                    if ($p_city) {
                        $billing_default_body .= "<td>" . $order_meta_data['_billing_city'][0] . "</td>";
                    }
                    if ($p_state) {
                        $billing_default_body .= "<td>" . $order_meta_data['_billing_state'][0] . "</td>";
                    }
                    if ($p_postcode) {
                        $billing_default_body .= "<td>" . $order_meta_data['_billing_postcode'][0] . "</td>";
                    }
                    if ($p_country) {
                        $billing_default_body .= "<td>" . $order_meta_data['_billing_country'][0] . "</td>";
                    }
                    // if($p_total_paid) {
                    //     $billing_default_body .= "<td>".$order_meta_data['_order_total'][0]."</td>";
                    // }
                    if ($p_payment_method) {
                        $billing_default_body .= "<td>" . $order_meta_data['_payment_method'][0] . "</td>";
                    }
                    // Default Field END
                    ?>
                    <tr>
                        <td><?php echo $index++; ?></td>
                        <?php
                        if ($billing_default_body != '') {
                            echo $billing_default_body;
                        }
                        ?>
                        <?php
                        if ($custom_tbody_html != '') {
                            echo $custom_tbody_html;
                        }
                        ?>
                        <td><?php echo get_post_meta($passenger_id, 'wbtm_seat', true); ?></td>
                        <td><?php echo get_post_meta($passenger_id, 'wbtm_order_id', true); ?></td>
                        <td><?php echo get_the_title(get_post_meta($passenger_id, 'wbtm_bus_id', true)) . "-" . get_post_meta(get_post_meta($passenger_id, 'wbtm_bus_id', true), 'wbtm_bus_no', true); ?></td>
                        <td><?php echo get_post_meta($passenger_id, 'wbtm_booking_date', true); ?></td>
                        <td>
                            <?php
                            $j_date = get_post_meta($passenger_id, 'wbtm_journey_date', true);
                            echo $j_date;
                            ?>
                            <?php echo get_post_meta($passenger_id, 'wbtm_user_start', true) . ' ' . mage_time_24_to_12(get_post_meta($passenger_id, 'wbtm_user_start', true)); ?>
                        </td>
                        <?php if ($subscription_fields_heading) {

                            $billing_type = get_post_meta($passenger_id, 'wbtm_billing_type', true);
                            $city_zone_id = get_post_meta($passenger_id, 'wbtm_city_zone', true);
                            $checkin_count = get_post_meta($passenger_id, 'wbtm_ticket_checkin_count', true);

                            // Billing Type
                            if ($billing_type) {
                                echo '<td>' . mtsa_calculate_valid_date($j_date, $billing_type) . '</td>';
                                echo '<td>' . $billing_type . '</td>';
                            } else {
                                echo '<td></td>';
                            }

                            // City Zone
                            if ($city_zone_id) {
                                $city_zone_data = get_term($city_zone_id);
                                echo '<td>' . $city_zone_data->name . '</td>';
                            } else {
                                echo '<td></td>';
                            }

                            // Check in Count
                            if ($checkin_count) {
                                echo '<td>' . $checkin_count . '</td>';
                            } else {
                                echo '<td>0</td>';
                            }
                        } ?>
                        <?php if($route_type != 'city_zone') {
                             echo '<td>'.get_post_meta($passenger_id, 'wbtm_boarding_point', true).'</td>';
                             echo '<td>'.get_post_meta($passenger_id, 'wbtm_droping_point', true).'</td>';
                        } ?>

                        <?php echo (!$subscription_fields_heading) ? '<td>'.get_post_meta($passenger_id, 'wbtm_pickpoint', true).'</td>' : '' ?>

                        <td><?php echo wc_price(get_post_meta($passenger_id, 'wbtm_bus_fare', true)); ?></td>
                        <td>
                            <?php
                            if ($order) {
                                echo $order->get_status();
                            }
                            ?>
                            <?php //do_action('wbtm_ticket_status',$_passger->ticket_status); ?>
                        </td>
                        <td>

                            <a href="<?php echo $download_url; ?>" title="Download PDF"><span
                                        class="dashicons dashicons-tickets-alt"></span></a>

                            <a href="<?php echo get_admin_url(); ?>edit.php?post_type=wbtm_bus&page=passenger_list&action=delete_seat&booking_id=<?php echo $_passger->ID; ?>"
                               title="Delete Data" onclick="javascript:return confirm('Are you sure you want to delete this seat?')"><span class="dashicons dashicons-no" style="color:#f44336"></span></a>

                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="mage-table-footer">
            <?php
            echo '<div class="mage-footer-item">';
            // Pagination
            echo ($total > $limit) ? mage_pagination($current_page, $pages) : '';
            echo '</div>';
            ?>
            <div class="mage-footer-item">
                <?php echo __('Found', 'addon-bus--ticket-booking-with-seat-pro') . ' ' . (($total > 1) ? $total . ' ' . __('items', 'addon-bus--ticket-booking-with-seat-pro') : $total . ' ' . __('item', 'addon-bus--ticket-booking-with-seat-pro')); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/*
* $current_page = Current Page
* $pages = Total page number
* return all button link
*/
function mage_pagination($current_page, $pages)
{
    $main_link = '?post_type=wbtm_bus&page=passenger_list';
    $bus_id = (isset($_GET['bus_id']) ? $_GET['bus_id'] : null);
    if ($bus_id) {
        $bus_id = explode('-', $bus_id);
        $bus_id = $bus_id[0];

        $main_link .= '&bus_id=' . $bus_id;
    }

    $j_date = (isset($_GET['j_date']) ? $_GET['j_date'] : null);
    if ($j_date) {
        $main_link .= '&j_date=' . $j_date;
    }

    // The "back" link
    $prevlink = ($current_page > 1) ? '<a class="mage_paginate_link" href="' . $main_link . '&paged=1" title="First page">&laquo;</a> <a class="mage_paginate_link" href="' . $main_link . '&paged=' . ($current_page - 1) . '" title="Previous page">&lsaquo;</a>' : '<span class="disabled">&laquo;</span> <span class="disabled">&lsaquo;</span>';

    // The "forward" link
    $nextlink = ($current_page < $pages) ? '<a class="mage_paginate_link" href="' . $main_link . '&paged=' . ($current_page + 1) . '" title="Next page">&rsaquo;</a> <a class="mage_paginate_link" href="' . $main_link . '&paged=' . $pages . '" title="Last page">&raquo;</a>' : '<span class="disabled">&rsaquo;</span> <span class="disabled">&raquo;</span>';

    // Display the paging information
    $output = '<div class="mage-pagination"><p>' . $prevlink . ' Page ' . $current_page . ' of ' . $pages . ' ' . $nextlink . ' </p></div>';
    return $output;
}
