<?php
/**
 * WCS3 Database operations
 */

/**
 * Creates the required WCS3 db tables.
 */
function wcs3_create_db_tables() {
    $table_name = wcs3_get_table_name();
    
    $sql = "CREATE TABLE `$table_name` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `class_id` int(20) unsigned NOT NULL,
        `instructor_id` int(20) unsigned NOT NULL,
        `location_id` int(20) unsigned NOT NULL,
        `weekday` int(3) unsigned NOT NULL,
        `start_hour` time NOT NULL,
        `end_hour` time NOT NULL,
        `timezone` varchar(255) NOT NULL DEFAULT 'UTC',
        `visible` tinyint(1) NOT NULL DEFAULT '1',
        `notes` text,
        PRIMARY KEY (`id`)
        )";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    add_option( "wcs3_db_version", WCS3_DB_VERSION );
}

/**
 * Get old WCS2 data.
 */
function wcs3_get_static_wcs2_data() {
    global $wpdb;
    
    $results = array();
    
    $table = $wpdb->prefix . 'wcs2_class';
    $query = "SELECT id, class_name, class_description FROM $table";
    
    $r = $wpdb->get_results( $query );
    foreach ( $r as $cls ) {
        $results['classes'][$cls->id] = array( 
            'class_name' => $cls->class_name, 
            'class_description' => $cls->class_description,
        );
    }
    
    $table = $wpdb->prefix . 'wcs2_instructor';
    $query = "SELECT id, instructor_name, instructor_description FROM $table";
    
    $r = $wpdb->get_results( $query );
    foreach ( $r as $inst ) {
    	$results['instructors'][$inst->id] = array(
        	'instructor_name' => $inst->instructor_name,
        	'instructor_description' => $inst->instructor_description,
    	);
    }
    
    $table = $wpdb->prefix . 'wcs2_classroom';
    $query = "SELECT id, classroom_name, classroom_description FROM $table";
    
    $r = $wpdb->get_results( $query );
    foreach ( $r as $loc ) {
    	$results['classrooms'][$loc->id] = array(
        	'classroom_name' => $loc->classroom_name,
        	'classroom_description' => $loc->classroom_description,
    	);
    }
    
    return $results;
}

/**
 * Converts the old WCS2 data to the new WCS3 format.
 * 
 * @param array $data: data array as returned from wcs3_get_static_wcs2_data
 */
function wcs3_create_new_wcs3_static_data( $data ) {
    foreach ( $data as $post_type => $content ) {
        if ( $post_type == 'classes' ) {
            foreach ( $data['classes'] as $key => $class ) {
                $new_post = array(
                    'post_content' => $class['class_description'],
                    'post_title' => $class['class_name'],
                    'post_type' => 'wcs3_class',
                    'post_status' => 'publish',
                );
                
                $data['classes'][$key]['new_id'] = wp_insert_post( $new_post );
            }
        }
        else if ( $post_type == 'instructors' ) {
        	foreach ( $data['instructors'] as $key => $inst ) {
        		$new_post = array(
            		'post_content' => $inst['instructor_description'],
            		'post_title' => $inst['instructor_name'],
            		'post_type' => 'wcs3_instructor',
        	    	'post_status' => 'publish',
        		);
        
        		$data['instructors'][$key]['new_id'] = wp_insert_post( $new_post );
        	}
        }
        else if ( $post_type == 'classrooms' ) {
        	foreach ( $data['classrooms'] as $key => $loc ) {
        		$new_post = array(
            		'post_content' => $loc['classroom_description'],
            		'post_title' => $loc['classroom_name'],
            		'post_type' => 'wcs3_location',
        	    	'post_status' => 'publish',
        		);
        
        		$data['classrooms'][$key]['new_id'] = wp_insert_post( $new_post );
        	}
        }
    }
    
    // Return data with new IDs.
    return $data;
}

/**
 * Gets the wcs2 schedule data and create new wcs3 entries.
 * 
 * @param array $data: wcs2 data.
 */
function wcs3_get_wcs2_schedule_data( $data ) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'wcs2_schedule';
    $wcs3_table = wcs3_get_table_name();
    
    $query = "SELECT class_id, instructor_id, classroom_id, weekday, start_hour,
                end_hour, visibility, notes FROM $table";
    
    $r = $wpdb->get_results( $query );
    
    $timezone = wcs3_get_system_timezone();
    
    foreach ( $r as $entry ) {
        $new_class_id = $data['classes'][$entry->class_id]['new_id'];
        $new_inst_id = $data['instructors'][$entry->instructor_id]['new_id'];
        $new_loc_id = $data['classrooms'][$entry->classroom_id]['new_id'];
        
        $wpdb->insert(
                $wcs3_table,
                array(
                    'class_id' => $new_class_id,
                    'instructor_id' => $new_inst_id,
                    'location_id' => $new_loc_id,
                    'weekday' => $entry->weekday,
                    'start_hour' => $entry->start_hour,
                    'end_hour' => $entry->end_hour,
                    'timezone' => $timezone,
                    'visible' => $entry->visibility,
                    'notes' => $entry->notes,
                ),
                array(
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                )
        );
    }
}