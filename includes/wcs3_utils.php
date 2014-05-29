<?php
/**
 * Utility functions for WCS3.
 */

/**
 * Returns the schedule table name including prefix.
 */
function wcs3_get_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'wcs3_schedule';
}

/**
 * Returns all post of the specified type.
 *
 * @param string $type: e.g. class, instructor, etc.
 */
function wcs3_get_posts_of_type( $type ) {
	$args = array(
    	'orderby' => 'post_title',
    	'post_type' => $type,
    	'post_status' => 'publish',
	    'posts_per_page' => 99999,
	);

	$posts = get_posts( $args );
	return $posts;
}

/**
 * Returns and HTTP JSON response.
 * 
 * @param mixed $data: JSON data to be encoded and sent.
 */
function wcs3_json_response( $data ) {
    header('Content-Type: application/json');
    echo json_encode( $data );
}

/**
 * Generates weekday array
 * 
 * @param bool $abbr: if TRUE returns abbreviated weekday names.
 */
function wcs3_get_weekdays( $abbr = FALSE ) {
    global $wp_locale;
    
    $days = array();
    
    $abbr = apply_filters( 'wcs3_abbr_weekdays', $abbr );
    
    if ($abbr) {
        $abbr_array = $wp_locale->weekday_abbrev;
        foreach ( $abbr_array as $value ) {
            $days[] = $value;
        }
    }
    else {
        $days = $wp_locale->weekday;
    }
            
    return $days;
}

/**
 * Returns an indexed array of weekday rotated according to $first_day_of_week.
 * 
 * @param bool $abbr: if TRUE returns abbreviated weekday names.
 * @param int $first_day_of_week: index.
 */
function wcs3_get_indexed_weekdays( $abbr = FALSE, $first_day_of_week = 0 ) {
    $weekdays = wcs3_get_weekdays( $abbr );
    $weekdays = array_flip( $weekdays );
    
    if ( $first_day_of_week > 0 ) {
    	// Rotate array based on first day of week setting.
    	$slice1 = array_slice( $weekdays, $first_day_of_week );
    	$slice2 = array_slice( $weekdays, 0, $first_day_of_week );
    	$weekdays = array_merge( $slice1, $slice2 );
    }
    
    $weekdays = apply_filters( 'wcs3_filter_indexed_weekdays', $weekdays );
    
    return $weekdays;
}

/**
 * Generages a simple HTML checkbox input field.
 * 
 * @param string $name: will be used both for name and id
 * @param bool $checked.
 */
function wcs3_bool_checkbox( $name, $checked = 'yes', $text = '' ) {
    $check = '';
    if ( $checked == 'yes' ) {
        $check = 'checked';
    }

    echo '<input type="hidden" name="' . $name . '" id="' . $name . '" value="no">';
    echo '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="yes" ' . $check . '><span class="wcs3-checkbox-text">' . $text . '</span>';
}

/**
 * Generates an HTML select list.
 * 
 * @param array $values: id => value.
 */
function wcs3_select_list( $values, $name = '', $default = NULL ) {
	$output = ( $name == '' ) ? '<select>' : "<select id='$name' name='$name'>";

	if ( !empty( $values ) ) {
	    foreach ( $values as $key => $value ) {
	        if ( $key == $default ) {
	            $output .= "<option value='$key' selected='selected'>$value</option>";
	        }
	        else {
	            $output .= "<option value='$key'>$value</option>";
	        }
	    }
	}
	else {
	    $output .= '<option value="_none"> --- </option>';
	}

	$output .= '</select>';
	return $output;;
}

function wcs3_colorpicker( $name, $default = 'DDFFDD', $size = 8 ) {
    echo '<input type="text" class="wcs_colorpicker" id="' . $name . '" name="' . $name . '" value="' . $default . '" size="' . $size . '">';
    echo '<span style="background: #' . $default . ';" class="colorpicker-preview ' . $name . '">&nbsp;</span>';
}


/**
 * Returns the installation default timezone. The method first checks for a WP
 * setting and if it can't find it, it uses the server setting. If the server setting
 * is also missing, the string UTC will be used.
 */
function wcs3_get_system_timezone()
{
    
	$php_timezone = ( ini_get('date.timezone') ) ? ini_get('date.timezone') : 'UTC';
	$wp_timezone = get_option( 'timezone_string' );

	return ( $wp_timezone == '' ) ? $php_timezone : $wp_timezone;
}

/**
 * Sets PHP's global timezone var.
 */
function wcs3_set_global_timezone() {
    $timezone = wcs3_get_system_timezone();
    date_default_timezone_set( $timezone );
}

/**
 * Displays a formatted message after options page submission.
 *
 * @param string $message: should already be internationlized.
 * @param string $type: error, warning, or updated.
 */
function wcs3_options_message( $message, $type = 'updated' ) {
	?>
    <div id="wcs3-options-message">
        <div class="<?php echo $type; ?>">
            <p><?php echo $message; ?></p>
        </div>
    </div>
    <?php 
}


/* ---------------- Validation functions --------------- */

/**
 * Performs validation and updates the options array.
 *
 * @param array $fields: field_id => validation callback
 *     Validation callbacks should return a sanitized value on success or
 *     FALSE on failure.
 * @param array (ref) $options: the options array to update with the sanitized options.
 */
function wcs3_perform_validation( $fields, $options, $prefix = 'wcs3_' ) {
	$new_options = array();
	foreach ( $fields as $id => $callback ) {
		$value = call_user_func( $callback, $_POST[$prefix . $id] );
		if ( $value !== FALSE ) {
			$new_options[$id] = $value;
		}
	}
	return $new_options;
}

function wcs3_validate_weekday( $data ) {
	$int = (int) $data;
	if ( $int < 0 || $int > 6) {
		return FALSE;
	}
	return $int;
}

function wcs3_validate_yes_no( $data ) {
	if ( $data === 'yes' || $data === 'no' ) {
		return $data;
	}
	else {
		return FALSE;
	}
}

function wcs3_validate_color( $data ) {
	$pattern = '/^[a-zA-Z0-9][a-zA-Z0-9][a-zA-Z0-9][a-zA-Z0-9][a-zA-Z0-9][a-zA-Z0-9]$/';
	preg_match( $pattern, $data, $matches );

	if ( !empty( $matches) ) {
		return sanitize_text_field( $data );
	}
	else {
		return FALSE;
	}
}

/**
 * Removes all but allowed HTML tags.
 *
 * @see wcs.php for $wcs3_allowed_html_tags.
 */
function wcs3_validate_html( $data ) {
	global $wcs3_allowed_html;

	$data = wp_kses( $data, $wcs3_allowed_html );
	return $data;
}