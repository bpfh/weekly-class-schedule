<?php
/*
Plugin Name: Weekly Class Schedule
Plugin URI: http://pulsarwebdesign.com/weekly-class-schedule
Description: Weekly Class Schedule generates a weekly schedule of classes. It provides you with an easy way to manage and update the schedule as well as the classes and instructors database.
Version: 3.11
Text Domain: wcs3
Author: Pulsar Web Design
Author URI: http://pulsarwebdesign.com
License: GPL2

Copyright 2011  Pulsar Web Design  (email : info@pulsarwebdesign.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'WCS3_VERSION', '3.11' );

define( 'WCS3_REQUIRED_WP_VERSION', '3.0' );

if ( ! defined( 'WCS3_PLUGIN_BASENAME' ) )
	define( 'WCS3_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WCS3_PLUGIN_NAME' ) )
	define( 'WCS3_PLUGIN_NAME', trim( dirname( WCS3_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'WCS3_PLUGIN_DIR' ) )
	define( 'WCS3_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'WCS3_PLUGIN_URL' ) )
	define( 'WCS3_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

if ( ! defined( 'WCS3_ADMIN_READ_CAPABILITY' ) )
	define( 'WCS3_ADMIN_READ_CAPABILITY', 'edit_posts' );

if ( ! defined( 'WCS3_ADMIN_READ_WRITE_CAPABILITY' ) )
	define( 'WCS3_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );

if ( ! defined( 'WCS3_DB_VERSION' ) )
	define( 'WCS3_DB_VERSION', '1.0' );

if ( ! defined( 'WCS3_BASE_DATE' ) )
	define( 'WCS3_BASE_DATE', '2001-01-01' );

/**
 * List of allowed HTML tags for the notes field (if enabled).
 * 
 * @see http://codex.wordpress.org/Function_Reference/wp_kses
 */
$wcs3_allowed_html = array(
            'a' => array(
                'href' => true,
                'title' => true,
            ),
            'abbr' => array(
                'title' => true,
            ),
            'acronym' => array(
                'title' => true,
            ),
            'b' => array(),
            'blockquote' => array(
                'cite' => true,
            ),
            'cite' => array(),
            'code' => array(),
            'del' => array(
                'datetime' => true,
            ),
            'em' => array(),
            'i' => array(),
            'q' => array(
                'cite' => true,
            ),
            'strike' => array(),
            'strong' => array(),
	    );

/**
 * A global data structure to allow for passing of Javascript data to the
 * front end.
 */
$wcs3_js_data = array();

/**
 * Load modules.
 */
require_once WCS3_PLUGIN_DIR . '/wcs3_modules.php';

/**
 * Create the class, instructor, and classroom post types.
 */
add_action( 'init', 'wcs3_create_post_types' );

function wcs3_create_post_types() {
    // Register class
	register_post_type( 'wcs3_class',
		array(
    		'labels' => array(
        		'name' => __( 'Classes', 'wcs3' ),
        		'singular_name' => __( 'Class', 'wcs3' )
    		),
    		'public' => true,
    		'has_archive' => true,
		)
	);
	
	// Register instructor
	register_post_type( 'wcs3_instructor',
		array(
			'labels' => array(
			'name' => __( 'Instructors', 'wcs3' ),
			'singular_name' => __( 'Instructor', 'wcs3' )
			),
			'public' => true,
			'has_archive' => true,
		)
	);
	
	// Register location
	register_post_type( 'wcs3_location',
		array(
			'labels' => array(
			'name' => __( 'Locations', 'wcs3' ),
			'singular_name' => __( 'Location', 'wcs3' )
			),
			'public' => true,
			'has_archive' => true,
		)
	);
}

/**
 * Register admin pages (schedule management, settings, etc...).
 */
function wcs3_register_schedule_management_page() {
    // Schedule page
    add_menu_page( __( 'Schedule Management', 'wcs3' ), 
            __( 'Schedule', 'wcs3' ), 
            WCS3_ADMIN_READ_WRITE_CAPABILITY, 
            'wcs3-schedule',
            'wcs3_schedule_management_page_callback' );
    
    // Standard settings page
    add_submenu_page( 'wcs3-schedule', 
            __( 'Options', 'wcs3' ), 
            __( 'Options', 'wcs3' ), 
            'manage_options', 
            'wcs3-standard-options', 
            'wcs3_standard_settings_page_callback' );
    
    // Standard settings page
    add_submenu_page( 'wcs3-schedule',
    		__( 'Import/Update', 'wcs3' ),
    		__( 'Import/Update', 'wcs3' ),
    		'manage_options',
    		'wcs3-import-update',
    		'wcs3_import_update_page_callback' );
}

add_action( 'admin_menu', 'wcs3_register_schedule_management_page' );

/**
 * Loads plugin text domain
 */
function wcs3_load_textdomain() {
    load_plugin_textdomain( 'wcs3' );
}

add_action( 'init', 'wcs3_load_textdomain' );

/**
 * Updates the version in the options table.
 */
function wcs3_update() {
    $version = get_option( 'wcs3_version' );	
	if ( is_admin() && $version < WCS3_VERSION ) {
	    update_option( 'wcs3_version', WCS3_VERSION );
	    
	    // Run update procedures.
	}
}
add_action( 'admin_init', 'wcs3_update' );

/**
 * Deletes all the data after wcs3
 */
function wcs3_delete_everything() {
	global $wpdb;

	delete_option( 'wcs3_db_version' );
	delete_option( 'wcs3_settings' );
	delete_option( 'wcs3_advanced_settings' );
	delete_option( 'wcs3_version' );

	$post_types = array(
    	'wcs3_class',
    	'wcs3_instructor',
    	'wcs3_location',
	);

	foreach ( $post_types as $type ) {
		$posts = get_posts( array(
    		'numberposts' => -1,
    		'post_type' => $type,
    		'post_status' => 'any' ) );

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	$table_name = $wpdb->prefix . "wcs3_schedule";

	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

/**
 * Register activation hook
 */
function wcs3_register_activation() {
	do_action( 'wcs3_activate_action' );
}
register_activation_hook( __FILE__, 'wcs3_register_activation' );	

/**
 * Activation
 */
function wcs3_activate() {
    $version = get_option( 'wcs3_version' );
    if ( $version == FALSE ) {
        // This is a new installation. Let's create the necessary
        // db table.
        wcs3_create_db_tables();
        
        // Update version option
        add_option( 'wcs3_version', WCS3_VERSION );
    }
}
add_action( 'wcs3_activate_action', 'wcs3_activate' );


