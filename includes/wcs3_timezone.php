<?php
/**
 * Timezones related functions.
 */


/**
 * Renders a list of timezone and defaults to the server timezone.
 *
 * @param string $name
 * 	The name of the form element to be used in form processing.
 * @param string $default
 */
function wcs3_generate_timezones_select_list( $name = '', $default = NULL )
{
	$server_timezone = wcs3_get_system_timezone();
	$timezones_list = DateTimeZone::listIdentifiers();

	if ( $default == NULL ) {
		$default = $server_timezone;
	}

	$option_groups = array(
    	"Africa",
    	"America",
    	"Antarctica",
    	"Arctic",
    	"Asia",
    	"Atlantic",
    	"Australia",
    	"Europe",
    	"Indian",
    	"Pacific",
	);

	$output = "<select id='$name' name='$name'>";

	foreach ( $option_groups as $group ) {
		$group_timezones = array();

		$output .= "<optgroup label='$group'>";

		foreach ( $timezones_list as $timezone ) {
			if ( preg_match( "/^$group/", $timezone ) > 0 ) {
				$short_timezone = str_replace( $group . '/', '', $timezone );
				$group_timezones[$timezone] = $short_timezone;
			}
		}

		foreach ( $group_timezones as $timezone => $short_timezone ) {
			if ( $timezone == $default ) {
				$output .= "<option value='$timezone' selected='selected'>$short_timezone</option>";
			}
			else
				$output .= "<option value='$timezone'>$short_timezone</option>";
		}

		$output .= '</optgroup>';
	}

	$output .= '</select>';
	return $output;
}