<?php
/**
* Plugin Name: Bus Ticket Booking with Seat Reservation PRO
* Plugin URI: http://mage-people.com
* Description: Pro version of Woocommerce Bus Tickets Manager, A Complete Bus Ticketig System for WordPress & WooCommerce
* Version: 4.7
* Author: MagePeople Team
* Author URI: http://www.mage-people.com/
* Text Domain: addon-bus--ticket-booking-with-seat-pro
* Domain Path: /languages/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}



add_action( 'init', 'WbtmPro_language_load');
function WbtmPro_language_load(){
    $plugin_dir = basename(dirname(__DIR__))."/languages/";
    load_plugin_textdomain( 'addon-bus--ticket-booking-with-seat-pro', false, $plugin_dir );
}

class WbtmPro_Base{
	public function __construct(){
		$this->define_constants();
		$this->load_main_class();
		add_action( 'admin_enqueue_scripts',array($this,'enqueue_styles' ));
	}
	public function define_constants() {
		define( 'WBTMPRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		// define( 'WBTMPRO_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
		define( 'WBTMPRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'WBTMPRO_PLUGIN_FILE', plugin_basename( __FILE__ ) );
	}
	public function load_main_class(){		
		require WBTMPRO_PLUGIN_DIR . 'includes/class-plugin.php';
		// require WBTMPRO_PLUGIN_DIR . 'includes/function.install_plugin.php';
	}
	public function enqueue_styles() {
        wp_register_style( 'select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
        wp_register_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'select2css' );
        wp_enqueue_script( 'select2' );

		wp_enqueue_style('bus-admin-style',WBTMPRO_PLUGIN_URL.'css/bus-admin.css',array());
		wp_enqueue_script('bus-admin-script',WBTMPRO_PLUGIN_URL.'js/bus-admin.js',array());
	}
}




include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && is_plugin_active( 'bus-ticket-booking-with-seat-reservation/woocommerce-bus.php' ) && is_plugin_active( 'magepeople-pdf-support-master/mage-pdf.php' ) ) {

	require 'includes/plugin-updates/plugin-update-checker.php';

	$ExampleUpdateChecker = PucFactory::buildUpdateChecker(
		'http://vaincode.com/update/bus/pro/bus.json',
		__FILE__
	);
	

new WbtmPro_Base();

}else{
	define( 'WBTMPRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'WBTMPRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require WBTMPRO_PLUGIN_DIR . 'includes/function.install_plugin.php';

function wbtm_wc_bus_pdf_not_activate() {
	  $class = 'notice notice-error';
	  $bus_install_url = get_admin_url().'plugin-install.php?s=bus-ticket-booking-with-seat-reservation&tab=search&type=term';
	  $wc_install_url = get_admin_url().'plugin-install.php?s=woocommerce&tab=search&type=term';
	  $mpdf_install_url = get_admin_url().'edit.php?post_type=wbtm_bus&page=wbtm_install_plugin_page';
	  $message = __( 'Bus Ticket Booking with Seat Reservation PRO Dependent on 3 Plugin: 1. Bus Ticket Booking with Seat Reservation. <a class="btn button" href='.$bus_install_url.'>Click Here to Install Bus</a> 2. Woocommerce <a class="btn button" href='.$wc_install_url.'>Click Here to Install WooCommerce</a> 3. MagePeople PDF Support <a class="btn button" href='.$mpdf_install_url.'>Click Here to Install PDF Support</a>. You need to install and activete these 3 plugin unless Bus Ticket Booking with Seat Reservation PRO will not work. ', 'addon-bus--ticket-booking-with-seat-pro' );
	  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ),  $message  ); 
}
	add_action( 'admin_notices', 'wbtm_wc_bus_pdf_not_activate' );
}












// add_filter('woocommerce_billing_fields', 'custom_woocommerce_billing_fields');

// function custom_woocommerce_billing_fields($fields)
// {

//     $fields['billing_nid'] = array(
//         'label' => __('NID', 'addon-bus--ticket-booking-with-seat-pro'), // Add custom field label
//         'placeholder' => _x('Your NID here....', 'placeholder', 'addon-bus--ticket-booking-with-seat-pro'), // Add custom field placeholder
//         'required' => false, // if field is required or not
//         'clear' => false, // add clear or not
//         'type' => 'text', // add field type
//         'class' => array('my-css')    // add class name
// 	);
	
// 	$fields['billing_tin'] = array(
//         'label' => __('Tin', 'addon-bus--ticket-booking-with-seat-pro'), // Add custom field label
//         'placeholder' => _x('Your Tin here....', 'placeholder', 'addon-bus--ticket-booking-with-seat-pro'), // Add custom field placeholder
//         'required' => false, // if field is required or not
//         'clear' => false, // add clear or not
//         'type' => 'text', // add field type
//         'class' => array('my-css')    // add class name
//     );

//     return $fields;
// }