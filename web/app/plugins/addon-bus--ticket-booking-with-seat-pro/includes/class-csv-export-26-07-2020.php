<?php
function wbtm_csv_head_row($post_id=''){
    global $woocommerce, $post;
    $head_row = array(
        'Name',
        'Mobile',
        'Email',        
        'Seat',                
        'Bus Name',                
        'Journey Date',             
        'Start',            
        'End',            
        'Order',            
        'Order Status'
    );
    return $head_row;
}

function wbtm_csv_passenger_data($post_id=''){
$order_id = get_post_meta($post_id,'wbtm_order_id',true);
$order = wc_get_order( $order_id );
    $passenger_data = array(
            get_post_meta( $post_id, 'wbtm_user_name', true ),
            get_post_meta( $post_id, 'wbtm_user_phone', true ),  
            get_post_meta( $post_id, 'wbtm_user_email', true ),              
            get_post_meta( $post_id, 'wbtm_seat', true ),              
            get_the_title(get_post_meta($post_id,'wbtm_bus_id',true))."-".get_post_meta(get_post_meta($post_id,'wbtm_bus_id',true),'wbtm_bus_no',true),              
            date('d.m.Y H:i',strtotime(get_post_meta( $post_id, 'wbtm_journey_date', true ).' '.get_post_meta( $post_id, 'wbtm_bus_start', true ))),
            get_post_meta( $post_id, 'wbtm_boarding_point', true ),            
            get_post_meta( $post_id, 'wbtm_droping_point', true ),            
            get_post_meta( $post_id, 'wbtm_order_id', true ),      
            $order->get_status()      

    );
return $passenger_data;
}


// Add action hook only if action=download_csv
if ( isset($_GET['action'] ) && $_GET['action'] == 'export_passenger_list' )  {
  // Handle CSV Export
  add_action( 'admin_init', 'wpmsems_export_default_form') ;
}

function wpmsems_export_default_form() {
    // Check for current user privileges 
    if( !current_user_can( 'manage_options' ) ){ return false; }
    // Check if we are in WP-Admin
    if( !is_admin() ){ return false; }
    ob_start();
	$bus_id = strip_tags($_GET['bus_id']);
	$j_date = strip_tags($_GET['j_date']);    
    $domain = $_SERVER['SERVER_NAME'];
    $filename = 'Passenger_list' . $domain . '_' . time() . '.csv';
    $header_row      = wbtm_csv_head_row();   
    $data_rows = array();
    $args = array(
        'post_type' => 'wbtm_bus_booking',
        'posts_per_page' => -1,
        'meta_query'  => array(
            'relation' => 'AND',
            array(
                'relation' => 'AND',
                    array(
                        'key'     => 'wbtm_journey_date',
                        'value' => $j_date,
                        'compare' => '='									
                    ),
                    array(
                        'key'     => 'wbtm_bus_id',
                        'value' => $bus_id,
                        'compare' => '='									
                    ),								
            ),
            array(
                'relation' => 'OR',										
                array(
                    'key'     => 'wbtm_status',
                    'value'   => 1,
                    'compare' => '='
                ),
                array(
                    'key'     => 'wbtm_status',
                    'value' => 2,
                    'compare' => '='
                ),
            )
        )	
    );
    $passenger = new WP_Query($args);
    $passger_query = $passenger->posts;
    foreach ($passger_query as $_passger) {
    $passenger_id = $_passger->ID;


            if (get_post_type($passenger_id) == 'wbtm_bus_booking') {
                $row      =  wbtm_csv_passenger_data($passenger_id);  
            }
            $data_rows[] = $row;
    }
    wp_reset_postdata();
    $fh = @fopen( 'php://output', 'w' );
    fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header( 'Content-Description: File Transfer' );
    header( 'Content-type: text/csv' );
    header( "Content-Disposition: attachment; filename={$filename}" );
    header( 'Expires: 0' );
    header( 'Pragma: public' );
    fputcsv( $fh, $header_row );
    foreach ( $data_rows as $data_row ) {
        fputcsv( $fh, $data_row );
    }
    fclose( $fh );    
    ob_end_flush();    
    die();
}