<?php

/**
 * Beaver Lister Plugin for Beaver Builder Plugin.
 * 
 * @wordpress-plugin
 * Plugin Name:   Beaver Lister
 * Plugin URI:    https://github.com/wpbeaverworld/wpbw-beaver-lister
 * Description:   Checking page builder is activated on a page, post or cpt and making the list of the used modules
 * Author:        WP Beaver World (@wpbeaverworld)
 * Author URI:    https://www.wpbeaverworld.com
 *   
 * Version:       1.0.7
 *  
 * Text Domain:   beaver-lister
 * Domain Path:   languages  
 */
  
/**
 * Copyright (c) 2016 WP Beaver World. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 */

/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
  wp_die( __( "Sorry, you are not allowed to access this page directly." ) );
}

$bl_version = "1.0.7";

define( 'BL_DIR', plugin_dir_path( __FILE__ ) );
define( 'BL_URL', plugins_url( '/', __FILE__ ) );
define( 'BL_LANGUAGES_DIR', plugin_basename( dirname( __FILE__ ) ) . '/languages'  );

register_activation_hook( __FILE__, 'wpbw_activate' );
		
add_action( 'admin_init', 		'wpbw_plugin_deactivate' );
add_action( 'switch_theme',		'wpbw_plugin_deactivate' );
add_action( 'plugins_loaded', 	'wpbw_bl_plugin_loaded' );

/**
 * Activate plugin
 */ 
function wpbw_activate()
{
	if ( ! class_exists('FLBuilder') )
	{
		//* Deactivate ourself
		deactivate_plugins( __FILE__ );
		add_action( 'admin_notices', 			'wpbw_admin_notice_message' );
		add_action( 'network_admin_notices', 	'wpbw_admin_notice_message' );
		return;	
	}
}

/**
 * This function is triggered when the WordPress theme is changed.
 * It checks if the Beaver Builder Plugin is active. If not, it deactivates itself.
 */
function wpbw_plugin_deactivate()
{
	if ( ! class_exists('FLBuilder') )
	{
		//* Deactivate ourself
		deactivate_plugins( __FILE__ );
		add_action( 'admin_notices', 			'wpbw_admin_notice_message' );
		add_action( 'network_admin_notices', 	'wpbw_admin_notice_message' );
	}
}

/**
 * Shows an admin notice if you're not using the Beaver Builder Plugin.
 */
function wpbw_admin_notice_message()
{
	if ( ! is_admin() ) {
		return;
	}
	else if ( ! is_user_logged_in() ) {
		return;
	}
	else if ( ! current_user_can( 'update_core' ) ) {
		return;
	}

	$error = __( 'Sorry, you can\'t use the Beaver Lister Plugin unless the Beaver Builder Plugin is active. The plugin has been deactivated.', 'beaver-lister' );

	echo '<div class="error"><p>' . $error . '</p></div>';

	if ( isset( $_GET['activate'] ) )
	{
		unset( $_GET['activate'] );
	}
}

/**
 * Doing some activities when plugin loaded
 */
function wpbw_bl_plugin_loaded() {
	if( ! class_exists( 'FLBuilderModel' ) )
		return;

	//* Load textdomain for translation
	load_plugin_textdomain( 'beaver-lister', false, BL_LANGUAGES_DIR );

	require_once 'classes/class.beaverlister.php';
	new BeaverLister();
}
