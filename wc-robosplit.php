<?php
/*
Plugin Name: WC Robokassa Split
Author URI: https://github.com/stasdk3000
Description:
Version: 1.0.0
Author: Stanislav Kritskiy
License: GPLv2 or later
Text Domain: wc-robosplit
*/


defined( 'ABSPATH' ) or die( 'silence is golden' );

define('RBSP_ROBOKASSA_URL', 'https://auth.robokassa.ru/Merchant/Payment/CreateV2');

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

register_deactivation_hook( __FILE__, 'rbspDeactivatePlugin' );
register_activation_hook( __FILE__, 'rbspActivatePlugin' );

function rbspIsWoocommerceActivated(): bool {
    return class_exists( 'woocommerce' );
}

function rbspActivatePlugin()
{
}

function rbspDeactivatePlugin()
{
}

new Rbsp\Plugin();