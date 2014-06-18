<?php
/**
 * Shortcodes for WCS3 (standard)
 */

/**
 * Standard [wcs] shortcode
 *
 * Default:
 *     [wcs layout="normal" location="all"]
 */
function wcs3_standard_shortcode( $atts ) {
    global $wcs3_js_data;

    $output = '';
    $buffer = '';

	extract( shortcode_atts( array(
	    'layout' => 'normal',
	    'location' => 'all',
		'class' => 'all',
		'instructor' => 'all',
		'weekday' => 'all',
	    'style' => 'normal',
		'template' => null
	), $atts ) );

	$wcs3_options = wcs3_load_settings();

	$first_day_of_week = $wcs3_options['first_day_of_week'];
	$mode = ( $wcs3_options['24_hour_mode'] == 'yes' ) ? '24' : '12';


	// Get indexed weekday array
	$weekdays = wcs3_get_indexed_weekdays( $abbr = TRUE, $first_day_of_week );
	if ( $weekday == "today" ) {
		$weekday = strtolower(date('l'));
	}

	if ( $weekday != 'all' ) {
		// convert string weekday to day of week
		$weekday_needle = substr(ucfirst(strtolower($weekday)), 0, 3);
		$weekday = array_search( $weekday_needle, array_keys($weekdays) );
	}

	// Render normal layout
	$classes = wcs3_get_classes( $layout, $location, $class, $instructor, $weekday, $mode );

	// Location
	$location_slug = preg_replace( "/[^A-Za-z0-9]/", '-', $location );
	$location_slug = strtolower( $location_slug );

	$wcs3_js_data['options'] = $wcs3_options;
	$wcs3_js_data['locations'][] = array(
    	'unique_start_times' => array_keys( $classes ),
    	'classes' => $classes,
    	'layout' => $layout,
	    'location_slug' => $location_slug,
	);


    // Render schedule.
    $output = apply_filters( 'wcs3_pre_render', $output, $style );
	$output .= '<div class="wcs3-schedule-wrapper" id="wcs3-location-' . $location_slug . '">';

	if ( $layout == 'normal' ) {
	    // Render normal layout
	    $output .= wcs3_render_normal_schedule( $classes, $location, $weekdays );
	}
	else if ( $layout == 'list' ) {
	    // Render list layout
	    $output .= wcs3_render_list_schedule( $classes, $location, $weekdays, $template );
	}
	else if ( $layout == 'list2' ) {
	    // Render list layout
	    $output .= wcs3_render_list2_schedule( $classes, $location, $weekdays, $template );
	}
	else {
	    $buffer = apply_filters( 'wcs3_render_layout', $buffer, $classes, $location, $weekdays, $wcs3_js_data );
	    if ( empty( $buffer ) ) {
	        $output .= __( 'Unsupported layout' );
	    }
	    else {
	        $output .= $buffer;
	    }
	}

	$output .= '</div>';
	$output = apply_filters( 'wcs3_post_render', $output, $style, $classes, $location, $weekdays );

	// Only load front end scripts and styles if it's our shortcode
	add_action('wp_footer', 'wcs3_localize_front_end_scripts');

	return $output;
}
add_shortcode( 'wcs', 'wcs3_standard_shortcode' );

/**
 * Hook into the footer for localizing Javascript.
 */

function wcs3_localize_front_end_scripts() {
    global $wcs3_js_data;

	// Load JS and localize.
	wcs3_load_frontend_scripts( $wcs3_js_data );
}

/**
 * Renders normal layout
 *
 * @param array $classes: classes array as returned by wcs3_get_classes().
 * @param string $location: location to render.
 * @param array $weekdays: indexed weekday array.
 */
function wcs3_render_normal_schedule( $classes, $location, $weekdays ) {
    if ( empty( $classes ) ) {
        $output = '<div class="wcs3-no-classes-message">' . __( 'No classes scheduled' ) . '</div>';
        return $output;
    }

    $output = '<table class="wcs3-schedule-normal-layout">';
    $output .= '<tr><th class="wcs3-hour-col"></th>';
    foreach ( $weekdays as $day => $index ) {
        $output .= '<th class="wcs3-day-col wcs3-day-col-' . $index . '">' . $day . '</th>';
    }
    $output .= '</tr>';

    // Classes are grouped by start hour.
    foreach ( $classes as $hour => $v ) {
        $output .= '<tr><th class="wcs3-hour-row-' . $hour . '">' . $v[0]->start_hour . '</th>';
        $counter = 0;
        foreach ( $weekdays as $day => $index ) {
            $css_name = 'wcs3-hour-row-' . $hour . ' wcs3-day-col-' . $index . ' wcs3-abs-col-' . $counter;
            $output .= '<td class="wcs3-cell ' . $css_name . '"></td>';
            $counter++;
        }
        $output .= '</tr>';
    }

    $output .= '</table>';

    return $output;
}

/**
 * Renders list layout
 *
 * @param array $classes: classes array as returned by wcs3_get_classes().
 * @param string $location: location to render.
 * @param int $first_day_of_week: index.
 */
function wcs3_render_list_schedule( $classes, $location, $weekdays, $template ) {
    if ( empty( $classes ) ) {
    	$output = '<div class="wcs3-no-classes-message">' . __( 'No classes scheduled' ) . '</div>';
    	return $output;
    }

    $wcs3_options = wcs3_load_settings();
    $weekdays_dict = wcs3_get_weekdays();
	if ( $template == null ) {
		$template = $wcs3_options['details_template'];
	}

    $output = '<div class="wcs3-schedule-list-layout">';

    // Classes are grouped by indexed weekdays.
    foreach ( $weekdays as $day => $index ) {
        $day = $weekdays_dict[$index];
        $day_classes = $classes[$index];

        if ( !empty( $day_classes ) ) {
            $output .= "<h3>$day</h3>";
            $output .= '<ul class="wcs3-weekday-list wcs3-weekday-list-' . $index . '">';

            foreach ( $day_classes as $class ) {
                $output .= '<li class="wcs3-list-item-class">';
                $output .= wcs3_process_template( $class, $template, $day );
                $output .= '</li>';
            }

            $output .= '</ul>';
            echo '';
        }
    }

    $output .= '</div>';

    return $output;
}

/**
 * Renders list2 layout
 *
 * @param array $classes: classes array as returned by wcs3_get_classes().
 * @param string $location: location to render.
 * @param int $first_day_of_week: index.
 */
function wcs3_render_list2_schedule( $classes, $location, $weekdays, $template) {
    if ( empty( $classes ) ) {
    	$output = '<div class="wcs3-no-classes-message">' . __( 'No classes scheduled' ) . '</div>';
    	return $output;
    }

    $wcs3_options = wcs3_load_settings();
    $weekdays_dict = wcs3_get_weekdays();
    // $template = $wcs3_options['details_template'];
	if ( $template == null ) {
		// $template = $wcs3_options['details_template'];
		$template = "[class] with [instructor] on [weekday] from [start hour] to [end hour] [notes]";
	}

    $output = '<div class="wcs3-schedule-list-layout">';
	$ul_present = false;

    // Classes are grouped by indexed weekdays.
    foreach ( $weekdays as $day => $index ) {
        $day = $weekdays_dict[$index];
        $day_classes = $classes[$index];

        if ( !empty( $day_classes ) ) {
            // $output .= "<h3>$day</h3>";
			if ( !$ul_present ) {
				$output .= '<ul class="wcs3-weekday-list wcs3-weekday-list-' . $index . '">';
				$ul_present = true;
			}

            foreach ( $day_classes as $class ) {
                $output .= '<li class="wcs3-list-item-class">';
                $output .= wcs3_process_template( $class, $template, $day );
                $output .= '</li>';
            }

        }
    }

	if ( $ul_present ) {
		$output .= '</ul>';
		echo '';
	}

    $output .= '</div>';

    return $output;
}

/**
 * Processes a template (replace placeholder, apply plugins).
 *
 * @param object $class: class object with all required data.
 * @param string $template: user defined template from settings.
 * @param string $weekday: name of the weekday
 */
function wcs3_process_template( $class, $template, $weekday = '' ) {

	// Templates specified immediately (as parameters to a shortcode invocation)
	// cannot generally use the [ and ] characters. So we allow angle brackets to
	// serve instead.
	$template = preg_replace("/<|&lt;|&#60;/", "[", $template);
	$template = preg_replace("/>|&gt;|&#62;/", "]", $template);

    $class_a = '<span class="wcs3-qtip-box"><a href="#qtip" class="wcs3-qtip">' . $class->class_title . '</a>';
    $class_a .= '<span class="wcs3-qtip-data">' . $class->class_desc . '</span></span>';

    $instructor_a = '<span class="wcs3-qtip-box"><a href="#qtip" class="wcs3-qtip">' . $class->instructor_title . '</a>';
    $instructor_a .= '<span class="wcs3-qtip-data">' . $class->instructor_desc . '</span></span>';

    $location_a = '<span class="wcs3-qtip-box"><a href="#qtip" class="wcs3-qtip">' . $class->location_title . '</a>';
    $location_a .= '<span class="wcs3-qtip-data">' . $class->location_desc . '</span></span>';

    $template = str_replace( '[class]', $class_a, $template );
    $template = str_replace( '[instructor]', $instructor_a, $template );
    $template = str_replace( '[location]', $location_a, $template );
    $template = str_replace( '[start hour]', $class->start_hour, $template );
    $template = str_replace( '[end hour]', $class->end_hour, $template );
    $template = str_replace( '[weekday]', $weekday, $template );
    $template = str_replace( '[notes]', $class->notes, $template );

    return $template;
}

/**
 * Enqueue and localize styles and scripts for WCS3 front end.
 */
function wcs3_load_frontend_scripts( $js_data = array() ) {
    // Load qTip plugin
    wp_register_style( 'wcs3_qtip_css', WCS3_PLUGIN_URL . '/plugins/qtip/jquery.qtip.min.css', false, '1.0.0' );
    wp_enqueue_style( 'wcs3_qtip_css' );

    wp_register_script('wcs3_qtip_js', WCS3_PLUGIN_URL . '/plugins/qtip/jquery.qtip.min.js', array( 'jquery' ), '1.0.0');
    wp_enqueue_script( 'wcs3_qtip_js' );

    wp_register_script('wcs3_qtip_images_js', WCS3_PLUGIN_URL . '/plugins/qtip/imagesloaded.pkg.min.js', array( 'jquery' ), '1.0.0');
    wp_enqueue_script( 'wcs3_qtip_images_js' );

    // Load hoverintent
    wp_register_script('wcs3_hoverintent_js', WCS3_PLUGIN_URL . '/plugins/hoverintent/jquery.hoverIntent.minified.js', array( 'jquery' ), '1.0.0');
    wp_enqueue_script( 'wcs3_hoverintent_js' );

    // Load common WCS3 JS
    wp_register_script('wcs3_common_js', WCS3_PLUGIN_URL . '/js/wcs3_common.js', array( 'jquery' ), '1.0.0');
    wp_enqueue_script( 'wcs3_common_js' );

    // Load custom scripts
    wp_register_style( 'wcs3_front_css', WCS3_PLUGIN_URL . '/css/wcs3_front.css', false, '1.0.0' );
    wp_enqueue_style( 'wcs3_front_css' );

    wp_register_script('wcs3_front_js', WCS3_PLUGIN_URL . '/js/wcs3_front.js', array( 'jquery' ), '1.0.0');
    wp_enqueue_script( 'wcs3_front_js' );

    // Localize script
    wp_localize_script( 'wcs3_front_js', 'WCS3_DATA', $js_data);
}