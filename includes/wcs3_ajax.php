<?php

/**
 * Ajax handlers for WCS3.
 */

/**
 * Performs standard AJAX nonce verification.
 */
function wcs3_verify_nonce() {
    $valid = check_ajax_referer( 'wcs3-ajax-nonce', 'security', FALSE );
    if (!$valid) {
    	$response = __( 'Nonce verification failed. Please report this to the site administrator', 'wcs3' );
    	$result = 'error';
    	wcs3_json_response( array( 'response' => $response, 'result' => $result ) );
    	die();
    }
}

/**
 * Verifies all required fields are available.
 * 
 * @param array $data: list of required fields ( field_name => Field Name ).
 */
function wcs3_verify_required_fields( array $data ) {
    foreach ( $data as $k => $v ) {
    	if ( !isset( $_POST[$k] ) || $_POST[$k] == '_none' ) {
    		$response = __( "$v field is required");
    		$result = 'error';
    		wcs3_json_response( array( 'response' => $response, 'result' => $result ) );
    		die();
    	}
    }
}

/**
 * Add or update schedule entry handler.
 */
function wcs3_add_or_update_schedule_entry_callback() {
    wcs3_verify_nonce();
    
    global $wpdb;
    $response = __( 'Schedule entry added successfully', 'wcs3' );
    $result = 'updated';
    $update_request = FALSE;
    $row_id = NULL;
    $days_to_update = array();
    
    $table = wcs3_get_table_name();
    
    $required = array(
        'class_id' => __( 'Class ID' ),
        'instructor_id' => __( 'Instructor ID' ),
        'location_id' => __( 'Location ID' ),
        'weekday' => __( 'Weekday' ),
        'start_hour' => __( 'Start Hour' ),
        'start_minute' => __( 'Start Minute' ),
        'end_hour' => __( 'End Hour' ),
        'end_minute' => __( 'End Minute' ),
        'visible' => __( 'Visible' ),
    );
    
    wcs3_verify_required_fields( $required );
    
    if ( isset( $_POST['row_id'] ) ) {
    	// This is an update request and not an insert.
    	$update_request = TRUE;
    	$row_id = sanitize_text_field( $_POST['row_id'] );
    }
    
    $wcs3_options = wcs3_load_settings();
    
    $class_id = sanitize_text_field( $_POST['class_id'] );
    $instructor_id = sanitize_text_field( $_POST['instructor_id'] );
    $location_id = sanitize_text_field( $_POST['location_id'] );
    $weekday = sanitize_text_field( $_POST['weekday'] );
    $start_hour = sanitize_text_field( $_POST['start_hour'] );
    $start_minute = sanitize_text_field( $_POST['start_minute'] );
    $end_hour = sanitize_text_field( $_POST['end_hour'] );
    $end_minute= sanitize_text_field( $_POST['end_minute'] );
    $visible = sanitize_text_field( $_POST['visible'] );
    
    $notes = '';
    
    // Check if we need to sanitize the notes or leave as is.
    if ( $_POST['notes'] != NULL) {
        if ( $wcs3_options['allow_html_in_notes'] == 'yes' ) {
            $notes = stripslashes_deep($_POST['notes']);
        }
        else {
            global $wcs3_allowed_html;
            $notes = wp_kses( $_POST['notes'], $wcs3_allowed_html );
        }
    }
      
    $start = $start_hour . ':' . $start_minute . ':00';
    $end = $end_hour . ':' . $end_minute . ':00';
    
    $days_to_update[$weekday] = TRUE;
    
    // Validate time logic
    $timezone = wcs3_get_system_timezone();
    $tz = new DateTimeZone( $timezone );
    $start_dt = new DateTime( WCS3_BASE_DATE . ' ' . $start, $tz );
    $end_dt = new DateTime( WCS3_BASE_DATE . ' ' . $end, $tz );
    
    $wcs3_settings = wcs3_load_settings();
    echo '';
    
     if ( $wcs3_settings['location_collision'] == 'yes' ) {
         // Validate location collision (if applicable)
         $location_collision = $wpdb->get_col( $wpdb->prepare(
         		"
         		SELECT id FROM $table
         		WHERE location_id = %d AND weekday = %d
         		AND %s < end_hour AND %s > start_hour
         		AND id != %d
         		",
         		array(
         		$location_id,
         		$weekday,
         		$start,
         		$end,
         		$row_id,
         ) ) );
     }
   
    if ( $wcs3_settings['instructor_collision'] == 'yes' ) {
        // Validate instructor collision (if applicable)
        $instructor_collision = $wpdb->get_col( $wpdb->prepare(
        		"
        		SELECT id FROM $table
        		WHERE instructor_id = %d AND weekday = %d
        		AND %s < end_hour AND %s > start_hour
        		AND id != %d
        		",
        		array(
        		$instructor_id,
        		$weekday,
        		$start,
        		$end,
        		$row_id,
        ) ) );
    }
    
    // Prepare response
    if ( ( $wcs3_settings['location_collision'] == 'yes' ) && !empty( $location_collision ) ) {
        $response = __( 'Location is not available at this time', 'wcs3' );
        $result = 'error';
    }
    else if ( ( $wcs3_settings['instructor_collision'] == 'yes' ) && !empty( $instructor_collision ) ) {
        $response = __( 'Instructor is not available at this time', 'wcs3' );
        $result = 'error';
    }
    else if ( $start_dt >= $end_dt ) {
        // Invalid class time
        $response = __( 'A class cannot start before it ends', 'wcs3' );
        $result = 'error';
    }
    else {
        $data = array(
            'class_id' => $class_id,
            'instructor_id' => $instructor_id,
            'location_id' => $location_id,
            'weekday' => $weekday,
            'start_hour' => $start,
            'end_hour' => $end,
            'timezone' => $timezone,
            'visible' => ( $visible == 'visible') ? 1 : 0,
            'notes' => $notes,
        );
        
        if ( $update_request ) {
            $old_weekday = $wpdb->get_var( $wpdb->prepare(
            		"
            		SELECT weekday FROM $table
            		WHERE id = %d;
            		",
            		array(
                		$row_id,
            ) ) );
            
            $days_to_update[$old_weekday] = TRUE;
            
            $r = $wpdb->update(
                    $table, 
                    $data, 
                    array( 'id' => $row_id ),
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
                    ),
                    array( '%d' )
                );
            
            if ($r === FALSE) {
            	$response = __( 'Failed to update schedule entry', 'wcs3' );
            	$result = 'error';
            }
            else {
                $response = __( 'Schedule entry updated successfully' );
            }
        }
        else {
            $r = $wpdb->insert(
            		$table,
            		$data,
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
            
            if ($r === FALSE) {
            	$response = __( 'Failed to add schedule entry', 'wcs3' );
            	$result = 'error';
            }
        }
        
    }
    
    wcs3_json_response( array( 'response' => $response, 'result' => $result, 'days_to_update' => $days_to_update ) );
    die();
}

// Register AJAX handler for add_or_update_schedule_entry.
add_action( 'wp_ajax_add_or_update_schedule_entry', 'wcs3_add_or_update_schedule_entry_callback' );

/**
 * Schedule entry delete handler.
 */
function wcs3_delete_schedule_entry_callback() {
    wcs3_verify_nonce();
    
	global $wpdb;
	$response = __( 'Schedule entry deleted successfully', 'wcs3' );
	$result = 'updated';
	
	$table = wcs3_get_table_name();
	
	$required = array(
	    'row_id' => __( 'Row ID' ),
	);
	
	wcs3_verify_required_fields( $required );
	
	$row_id = sanitize_text_field( $_POST['row_id'] );
	
	$result = $wpdb->delete( $table, array( 'id' => $row_id ), array( '%d' ) );
	
	if ($result == 0) {
	    $response = __( 'Failed to delete entry', 'wcs3' );
	    $result = 'error';
	}
	
	wcs3_json_response( array( 'response' => $response, 'result' => $result ) );
    die();
}

// Register AJAX handler for delete_schedule_entry.
add_action( 'wp_ajax_delete_schedule_entry', 'wcs3_delete_schedule_entry_callback' );


/**
 * Schedule entry edit handler.
 */
function wcs3_edit_schedule_entry_callback() {
    wcs3_verify_nonce();
    
	global $wpdb;
	$response = new stdClass();

	$table = wcs3_get_table_name();

	$required = array(
	    'row_id' => __( 'Row ID' ),
	);

	wcs3_verify_required_fields( $required );

	$row_id = sanitize_text_field( $_POST['row_id'] );
	
	$result = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $row_id ), ARRAY_A );
	if ($result) {
	    $response = $result;
	}

	wcs3_json_response( array( 'response' => $response ) );
	die();
}

// Register AJAX handler for delete_schedule_entry.
add_action( 'wp_ajax_edit_schedule_entry', 'wcs3_edit_schedule_entry_callback' );


/**
 * Returns the schedule for a specific day.
 */
function wcs3_get_day_schedule_callback() {
    wcs3_verify_nonce();
    
    global $wpdb;
    $response = __( 'Day schedule retrieved successfully', 'wcs3' );
    $result = 'updated';
    
    $table = wcs3_get_table_name();
    
    $required = array(
        'day' => __( 'Day' ),
    );
    
    wcs3_verify_required_fields( $required );
    
    $day = sanitize_text_field( $_POST['day'] );
    
    $day_table = wcs3_render_day_table( $day );
    
    wcs3_json_response( array( 'html' => $day_table ) );
    die();
}

// Register AJAX handler for get_day_schedule.
add_action( 'wp_ajax_get_day_schedule', 'wcs3_get_day_schedule_callback' );

/**
 * Handle import update
 */
function wcs3_import_update_callback() {
	wcs3_verify_nonce();
	
	wcs3_delete_everything();
	
	update_option( 'wcs3_version', WCS3_VERSION );
	
	/* do stuff once right after activation */
	// Create db tables
	wcs3_create_db_tables();
	
	// Run default settings hook.
	do_action( 'wcs3_default_settings' );
	
	// Update old versions
	// New installation, let's try and get data from wcs2
	$wcs2_static_data = wcs3_get_static_wcs2_data();
	$new_ids = wcs3_create_new_wcs3_static_data( $wcs2_static_data );
	$wcs2_schedule = wcs3_get_wcs2_schedule_data( $new_ids );
	
	$response = __( 'Weekly Class Schedule 2.x data imported successfully.', 'wcs3' );
	$result = 'updated';
	wcs3_json_response( array( 'response' => $response, 'result' => $result ) );
	die();
}

// Register AJAX handler for get_day_schedule.
add_action( 'wp_ajax_import_update_data', 'wcs3_import_update_callback' );

?>