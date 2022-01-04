<?php
/**
 * Plugin Name:  Woo Per Quantity Pricing
 * Plugin URI: http://innowebeye.com
 * Description: Allows merchants to set per quantity pricing for all products.
 * Version: 1.0.0
 * Author:            InnoWebEye
 * Author URI:        http://innowebeye.com
 * Requires at least: 3.5
 * Tested up to: 4.8
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-per-quantity-pricing
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin_activation_check = true;
if ( function_exists('is_multisite') && is_multisite() ) {
	include_once  ABSPATH . 'wp-admin/includes/plugin.php' ;
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$plugin_activation_check = false;
	}
} else {
	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		$plugin_activation_check = false;
	}
}
/*
 * Runs only when WooCommerce is activated
 *
 */
if ($plugin_activation_check) {

	define('IWE_PQP_DIRPATH', plugin_dir_path( __FILE__ ));
	define('IWE_PQP_URL', plugin_dir_url( __FILE__ ));
	define('IWE_PQP_HOME_URL', home_url());

	/*
	* Including Admin and Public files
	*/
	include_once IWE_PQP_DIRPATH . '/admin/woo-admin-per-quantity.php';
	include_once IWE_PQP_DIRPATH . '/public/woo-public-per-quantity.php';

} else {
	function iwe_pqp_plugin_error_notice() {
		?>
		  <div class="error notice is-dismissible">
			 <p><?php esc_html_e( 'Woocommerce is not activated, Please activate Woocommerce first to install WooCommerce Per Quantity Pricing.', 'woo-per-quantity-pricing' ); ?></p>
		   </div>
		   <style>
		   #message{display:none;}
		   </style>
	<?php 
	} 
	add_action( 'admin_init', 'iwe_pqp_plugin_deactivate' );  
 
	function iwe_pqp_plugin_deactivate() {
	   deactivate_plugins( plugin_basename( __FILE__ ) );
	   add_action( 'admin_notices', 'iwe_pqp_plugin_error_notice' );
	}
}
