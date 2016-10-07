<?php

/**
 * Beaver Lister Plugin for Beaver Builder Plugin.
 * 
 * @wordpress-plugin
 * Plugin Name:   Beaver Lister
 * Plugin URI:    http://www.wpbeaverworld.com/beaver-lister/
 * Description:   Showing page builder enabled pages and listing the used modules
 * Author:        WP Beaver World (@wpbeaverworld)
 * Author URI:    http://www.wpbeaverworld.com
 * Support:       http://support.wpbeaverworld.com
 *   
 * Version:       1.0
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

$bl_version = "1.0";

define( 'BL_DIR', plugin_dir_path( __FILE__ ) );
define( 'BL_URL', plugins_url( '/', __FILE__ ) );
define( 'BL_LANGUAGES_DIR', plugin_basename( dirname( __FILE__ ) ) . '/languages'  );

add_action( 'plugins_loaded', 'wpbw_bl_plugin_loaded' );
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