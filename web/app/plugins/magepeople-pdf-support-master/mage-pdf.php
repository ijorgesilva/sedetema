<?php
/**
* Plugin Name: MagePeople PDF Support
* Plugin URI: http://mage-people.com
* Description: This is the main PDF Lib for PDF Ticket, You Need to keep active this plugin for working MagePeople Plugin which generate PDF Tickets.
* Version: 2.0
* Author: MagePeople Team
* Author URI: http://www.mage-people.com/
* Text Domain: magepeople-pdf-support
* Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
if( ! class_exists( 'mPDF' ) ) {
    require 'inc/plugin-updates/plugin-update-checker.php';
    $ExampleUpdateChecker = PucFactory::buildUpdateChecker(
        'http://vaincode.com/update/mpdf/mpdf.json',
        __FILE__
    );
    require_once( dirname(__FILE__) . "/lib/vendor/autoload.php");
}
