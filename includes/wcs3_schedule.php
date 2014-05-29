<?php
/**
 * Schedule specific functions.
 */

/**
 * Generates the day table for admin use.
 * 
 * @param int $day: the weekday (sunday = 0, monday = 1)
 */
function wcs3_render_day_table( $day ) {
    $day_data = wcs3_get_day_schedule( $day );
    
    $output = '<div class="wcs3-day-content-wrapper">';
    
    if ( $day_data ) {
        $output .= '<table id="wcs3-admin-table-day-' . $day . '" class="widefat wcs3-admin-schedule-table">';
        $output .= '<tr>
            <th>' . __( 'Class', 'wcs3' ) . '</th>
            <th>' . __( 'Instructor', 'wcs3' ) . '</th>
            <th>' . __( 'Location', 'wcs3' ) . '</th>
            <th>' . __( 'Start', 'wcs3' ) . '</th>
            <th>' . __( 'End', 'wcs3' ) . '</th>
            <th>' . __( 'Visibility', 'wcs3') . '</th>
            <th>' . __( 'Delete', 'wcs3') . '</th>
            <th>' . __( 'Edit', 'wcs3') . '</th>
        </tr>';
        
        foreach ( $day_data as $class ) {
        	$output .= '<tr>';
        	foreach ( $class as $key => $value ) {
        	    if ( $key != 'id' ) {
        	        $output .= "<td>$value</td>";
        	    }
        		else {
        		    $output .= '<td><a href="#delete" class="wcs3-delete-button wcs3-action-button-day-' . $day . '" 
        		                id="delete-entry-' . $value . '">' . __( 'delete', 'wcs3') . '</a></td>';
        		    $output .=  '<td><a href="#" class="wcs3-edit-button wcs3-action-button-day-' . $day . '" 
        		                id="edit-entry-' . $value . '">' . __( 'edit', 'wcs3' ) . '</a>';  
        		}
        	}
        
        	$output .= '</tr>';
        }
        
        
        $output .= '</table>';
    }
    else {
        $output .= '<div class="wcs3-no-classes"><p>' . __( 'No classes', 'wcs3' ) . '</p></div>';
    }
   
    $output .= '</div>'; // day-content-wrapper
    return $output;
}

/**
 * Returns the database data relevant for the provided weekday.
 * 
 * @param int $day: the weekday (sunday = 0, monday = 1)
 */
function wcs3_get_day_schedule( $day, $location_id = NULL, $limit = NULL ) {
    global $wpdb;
    
    $wcs3_settings = wcs3_load_settings();
    
    $format = ( $wcs3_settings['24_hour_mode'] == 'yes' ) ? 'G:i' : 'g:i a';
        
    $table = wcs3_get_table_name();
    $results = array();
    
    $query = "SELECT * FROM $table WHERE weekday = %d ";
    $query_arr = array( $day );
    
    if ( $location_id !== NULL ) {
        $query .= "AND location_id = %d ";
        $query_arr[] = $location_id;
    }
    
    $query .= "ORDER BY start_hour ";
    
    if ( $limit !== NULL ) {
        $query .= "LIMIT %d";
        $query_arr[] = $limit;
    }
    
    $r = $wpdb->get_results( $wpdb->prepare( $query, $query_arr ) );
    
    if ( !empty( $r ) ) {
        foreach ( $r as $entry ) {
            $results[] = array(
                'class' => get_post( $entry->class_id )->post_title,
                'instructor' => get_post( $entry->instructor_id )->post_title,
                'location' => get_post( $entry->location_id )->post_title,
                'start_hour' => date( $format, strtotime( $entry->start_hour ) ),
                'end_hour' => date( $format, strtotime( $entry->end_hour ) ),
                'visible' => ( $entry->visible == 1 ) ? __( 'Visible', 'wcs3' ) : __( 'Hidden', 'wcs3' ),
                'id' => $entry->id,
            );
        }
        return $results;
    }
    else {
        return FALSE;
    }
}

/**
 * Gets all the visible classes from the database including instructors and locations.
 * 
 * @param string $layout: 'normal', 'list', etc.
 * @param string $location
 * @param string $mode: 12 or 24.
 */
function wcs3_get_classes( $layout, $location, $mode = '12' ) {
    global $wpdb;
    
    $format = ( $mode == '12' ) ? 'g:i a' : 'G:i';
    
    $schedule_table = wcs3_get_table_name();
    $posts_table = $wpdb->prefix . 'posts';
    $meta_table = $wpdb->prefix . 'postmeta';
    
    $query = "SELECT 
                c.post_title AS class_title, c.post_content AS class_desc,
                i.post_title AS instructor_title, i.post_content AS instructor_desc,
                l.post_title AS location_title, l.post_content AS location_desc,
                s.weekday, s.start_hour, s.end_hour, 
              s.notes FROM $schedule_table s
              INNER JOIN $posts_table c ON s.class_id = c.ID
              INNER JOIN $posts_table i ON s.instructor_id = i.ID
              INNER JOIN $posts_table l ON s.location_id = l.ID
              WHERE s.visible = 1";
    
    $query = apply_filters( 
            'wcs3_filter_get_classes_query', 
            $query, 
            $schedule_table,
            $posts_table,
            $meta_table );
    
    if ( $location != 'all' ) {
        $query .= " AND l.post_title = %s";
        $query = $wpdb->prepare( $query, array( $location ) );
    }
    
    $query .= " ORDER BY s.start_hour";
    
    $results = $wpdb->get_results( $query );
    $grouped = array();
    
    if ( $results ) {
        foreach ( $results as $class ) {
            // Prep CSS class name
            wcs3_format_class_object( $class, $format );
            
            if ( $layout == 'list' ) {
            	$grouped[$class->weekday][] = $class;
            }
            else {
                $grouped[$class->start_hour_css][] = $class;
            }
        }
    }
    
    return $grouped;
}

/**
 * Formats the time properties of a class object as returned from the database.
 * 
 * @param object $class: reference to class object.
 * @param string $format: time format (e.g. 'g:i a').
 */
function wcs3_format_class_object( &$class, $format ) {	
    $class->start_hour_css = substr( str_replace( ':', '-', $class->start_hour), 0, 5);
    $class->end_hour_css = substr( str_replace( ':', '-', $class->end_hour), 0, 5);
    
    $class->start_hour = date( $format, strtotime( $class->start_hour ) );
    $class->end_hour = date( $format, strtotime( $class->end_hour ) );
    
    $class = apply_filters( 'wcs3_format_class', $class );
}
