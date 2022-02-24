<?php
function wbtm_csv_head_row($post_id = '', $bus_type = 'general', $route_type = '')
{
    global $wbtmmain;
    $billing_default_fields_setting = $wbtmmain->bus_get_option('default_billing_fields_setting', 'ticket_manager_settings', array());

    $billing_default_heading_array = array();

    if (in_array('p_name', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'Name';
    }
    if (in_array('p_phone', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'Phone';
    }
    if (in_array('p_email', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'Email';
    }
    if (in_array('p_company', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'Company';
    }
    if (in_array('p_address', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'Address';
    }
    if (in_array('p_city', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'City';
    }
    if (in_array('p_state', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'State';
    }
    if (in_array('p_postcode', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'Postcode';
    }
    if (in_array('p_country', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'Country';
    }
    if (in_array('p_total_paid', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'Price';
    }
    if (in_array('p_payment_method', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = 'Payment Method';
    }

    if ($bus_type == 'sub' && $route_type != 'city_zone') {
        $head_row = array(
            'Seat',
            'Bus Name',
            'Booking Date',
            'Start Date',
            'Valid Till',
            'Billing Type',
            'Zone',
            'From',
            'To',
            'Order',
            'Order Status'
        );
    } elseif ($bus_type == 'sub' && $route_type == 'city_zone') {
        $head_row = array(
            'Seat',
            'Bus Name',
            'Booking Date',
            'Start Date',
            'Valid Till',
            'Billing Type',
            'Zone',
            'Order',
            'Order Status'
        );
    } else {
        $head_row = array(
            'Seat',
            'Bus Name',
            'Booking Date',
            'Journey Date',
            'From',
            'To',
            'Pickup Point',
            'Order',
            'Order Status'
        );
    }

    if (count($billing_default_fields_setting) > 0) {
        $head_row = array_merge($billing_default_heading_array, $head_row);
    }

    return $head_row;
}

function wbtm_csv_passenger_data($post_id = '', $bus_type = 'general', $route_type = '')
{
    global $wbtmmain;
    $order_id = get_post_meta($post_id, 'wbtm_order_id', true);
    $order = wc_get_order($order_id);
    $order_meta_data = get_post_meta($order_id);
    $billing_default_fields_setting = $wbtmmain->bus_get_option('default_billing_fields_setting', 'ticket_manager_settings', array());

    $billing_default_heading_array = array();

    if (in_array('p_name', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = get_post_meta($post_id, 'wbtm_user_name', true);
    }
    if (in_array('p_phone', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = get_post_meta($post_id, 'wbtm_user_phone', true);
    }
    if (in_array('p_email', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = get_post_meta($post_id, 'wbtm_user_email', true);
    }
    if (in_array('p_company', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = $order_meta_data['_billing_company'][0];
    }
    if (in_array('p_address', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = $order_meta_data['_billing_address_1'][0];
    }
    if (in_array('p_city', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = $order_meta_data['_billing_city'][0];
    }
    if (in_array('p_state', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = $order_meta_data['_billing_state'][0];
    }
    if (in_array('p_postcode', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = $order_meta_data['_billing_postcode'][0];
    }
    if (in_array('p_country', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = $order_meta_data['_billing_country'][0];
    }
    if (in_array('p_total_paid', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = get_post_meta($post_id, 'wbtm_bus_fare', true);
    }
    if (in_array('p_payment_method', $billing_default_fields_setting)) {
        $billing_default_heading_array[] = isset($order_meta_data['_payment_method_title'][0]) ? $order_meta_data['_payment_method_title'][0] : null;
    }

    $status = (is_object($order) ? $order->get_status() : null);
    $billing_type_str = get_post_meta($post_id, 'wbtm_billing_type', true);
    $j_date = get_post_meta($post_id, 'wbtm_journey_date', true);
    $valid_till = '';
    $zone_text = '';

    if ($billing_type_str) {
        $valid_till = mtsa_calculate_valid_date($j_date, $billing_type_str);

        $zone = get_post_meta($post_id, 'wbtm_city_zone', true);
        if($zone) {
            $zone = get_term($zone);
            $zone_text = $zone->name;
        }

    }

    if ($bus_type == 'sub' && $route_type != 'city_zone') {

        $passenger_data = array(
            get_post_meta($post_id, 'wbtm_seat', true),
            get_the_title(get_post_meta($post_id, 'wbtm_bus_id', true)) . "-" . get_post_meta(get_post_meta($post_id, 'wbtm_bus_id', true), 'wbtm_bus_no', true),
            get_post_meta($post_id, 'wbtm_booking_date', true),
            get_post_meta($post_id, 'wbtm_journey_date', true) . ' ' . get_post_meta($post_id, 'wbtm_bus_start', true),
            $valid_till,
            $billing_type_str,
            $zone_text,
            get_post_meta($post_id, 'wbtm_boarding_point', true),
            get_post_meta($post_id, 'wbtm_droping_point', true),
            get_post_meta($post_id, 'wbtm_order_id', true),
            $status
        );
    } elseif ($bus_type == 'sub' && $route_type == 'city_zone') {

        $passenger_data = array(
            get_post_meta($post_id, 'wbtm_seat', true),
            get_the_title(get_post_meta($post_id, 'wbtm_bus_id', true)) . "-" . get_post_meta(get_post_meta($post_id, 'wbtm_bus_id', true), 'wbtm_bus_no', true),
            get_post_meta($post_id, 'wbtm_booking_date', true),
            get_post_meta($post_id, 'wbtm_journey_date', true) . ' ' . get_post_meta($post_id, 'wbtm_bus_start', true),
            $valid_till,
            $billing_type_str,
            $zone_text,
            get_post_meta($post_id, 'wbtm_order_id', true),
            $status
        );
    } else {
        $passenger_data = array(
            get_post_meta($post_id, 'wbtm_seat', true),
            get_the_title(get_post_meta($post_id, 'wbtm_bus_id', true)) . "-" . get_post_meta(get_post_meta($post_id, 'wbtm_bus_id', true), 'wbtm_bus_no', true),
            get_post_meta($post_id, 'wbtm_booking_date', true),
            get_post_meta($post_id, 'wbtm_journey_date', true) . ' ' . get_post_meta($post_id, 'wbtm_bus_start', true),
            get_post_meta($post_id, 'wbtm_boarding_point', true),
            get_post_meta($post_id, 'wbtm_droping_point', true),
            get_post_meta($post_id, 'wbtm_pickpoint', true),
            get_post_meta($post_id, 'wbtm_order_id', true),
            $status
        );
    }

    if (count($billing_default_fields_setting) > 0) {
        $passenger_data = array_merge($billing_default_heading_array, $passenger_data);
    }


    return $passenger_data;
}


// Add action hook only if action=download_csv
if (isset($_GET['action']) && $_GET['action'] == 'export_passenger_list') {
    // Handle CSV Export
    add_action('admin_init', 'wpmsems_export_default_form');
}

function wpmsems_export_default_form()
{
    // Check for current user privileges 
    if (!current_user_can('manage_options')) {
        return false;
    }
    // Check if we are in WP-Admin
    if (!is_admin()) {
        return false;
    }
    ob_start();
    $bus_id = strip_tags($_GET['bus_id']);
    $j_date = strip_tags($_GET['j_date']);
    $domain = $_SERVER['SERVER_NAME'];
    // Bus Type
    $bus_type = (isset($_GET['bus_type']) ? $_GET['bus_type'] : 'general');
    // Route Type
    $route_type = isset($_GET['route_type']) ? $_GET['route_type'] : '';
    $filename = 'Passenger_list' . $domain . '_' . time() . '.csv';
    $header_row = wbtm_csv_head_row('', $bus_type, $route_type);

    $data_rows = array();

    // -------------------------------------------------
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
    );

    $filter_query = array(
        'relation' => 'AND',
    );

    // Filter Meta
    $has_filter = false;

    // Bus Id
    $bus_id = (isset($_GET['bus_id']) ? strip_tags($_GET['bus_id']) : null);
    if ($bus_id) {
        $bus_id = explode('-', $bus_id);
        $bus_id = $bus_id[0];

        array_push($filter_query, array(
            'key' => 'wbtm_bus_id',
            'value' => $bus_id,
            'compare' => '='
        ));
        $has_filter = true;
    }
    // Journey Date
    $j_date = (isset($_GET['j_date']) ? strip_tags($_GET['j_date']) : null);
    if ($j_date) {
        array_push($filter_query, array(
            'key' => 'wbtm_journey_date',
            'value' => $j_date,
            'compare' => '='
        ));
        $has_filter = true;
    }
    // Name
    $filter_name = (isset($_GET['filter_name']) ? strip_tags($_GET['filter_name']) : null);
    if ($filter_name) {
        array_push($filter_query, array(
            'key' => 'wbtm_user_name',
            'value' => $filter_name,
            'compare' => 'LIKE'
        ));
        $has_filter = true;
    }
    // Email
    $filter_email = (isset($_GET['filter_email']) ? strip_tags($_GET['filter_email']) : null);
    if ($filter_email) {
        array_push($filter_query, array(
            'key' => 'wbtm_user_email',
            'value' => $filter_email,
            'compare' => '='
        ));
        $has_filter = true;
    }
    // Phone
    $filter_phone = (isset($_GET['filter_phone']) ? strip_tags($_GET['filter_phone']) : null);
    if ($filter_phone) {
        array_push($filter_query, array(
            'key' => 'wbtm_user_phone',
            'value' => $filter_phone,
            'compare' => '='
        ));
        $has_filter = true;
    }
    // Booking Date
    $filter_booking_date = (isset($_GET['filter_booking_date']) ? strip_tags($_GET['filter_booking_date']) : null);
    if ($filter_booking_date) {
        array_push($filter_query, array(
            'key' => 'wbtm_booking_date',
            'value' => $filter_booking_date,
            'compare' => 'LIKE'
        ));
        $has_filter = true;
    }
    // Order ID
    $filter_order_id = (isset($_GET['filter_order_id']) ? strip_tags($_GET['filter_order_id']) : null);
    if ($filter_order_id) {
        array_push($filter_query, array(
            'key' => 'wbtm_order_id',
            'value' => $filter_order_id,
            'compare' => '='
        ));
        $has_filter = true;
    }

    // Bus Type
    if ($bus_type === 'sub' && $route_type != 'city_zone') {
        array_push($filter_query, array(
            'key' => 'wbtm_billing_type',
            'value' => null,
            'compare' => '!='
        ));
        $has_filter = true;
    } elseif($bus_type === 'sub' && $route_type == 'city_zone') {
        array_push($filter_query, array(
            'relation' => 'AND',
            array(
                'key' => 'wbtm_billing_type',
                'value' => null,
                'compare' => '!='
            ),
            'key' => 'wbtm_city_zone',
            'value' => null,
            'compare' => '!='
        ));
        $has_filter = true;
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
    }

    if ($has_filter) {
        array_push($meta_query, $filter_query);
    }
    array_push($meta_query, $common);

    // -------------------------------------------------
    $args = array(
        'post_type' => 'wbtm_bus_booking',
        'posts_per_page' => -1,
        'meta_query' => $meta_query,
    );
    $passenger = new WP_Query($args);

    $passger_query = $passenger->posts;
    foreach ($passger_query as $_passger) {
        $passenger_id = $_passger->ID;


        if (get_post_type($passenger_id) == 'wbtm_bus_booking') {
            $row = wbtm_csv_passenger_data($passenger_id, $bus_type, $route_type);
        }
        $data_rows[] = $row;
    }
    // echo '<pre>';
    // print_r($data_rows);
    // die;
    wp_reset_postdata();
    $fh = @fopen('php://output', 'w');
    fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-type: text/csv');
    header("Content-Disposition: attachment; filename={$filename}");
    header('Expires: 0');
    header('Pragma: public');
    fputcsv($fh, $header_row);
    foreach ($data_rows as $data_row) {
        fputcsv($fh, $data_row);
    }
    fclose($fh);
    ob_end_flush();
    die();
}