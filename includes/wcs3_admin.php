<?php
/**
 * Admin area functions.
 */


/**
 * Register styles and scripts.
 */
function wcs3_load_admin_style() {
	wp_register_style( 'wcs3_admin_css', WCS3_PLUGIN_URL . '/css/wcs3_admin.css', false, '1.0.0' );
	wp_enqueue_style( 'wcs3_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'wcs3_load_admin_style' );

/**
 * Load admin area scripts.
 */
function wcs3_load_admin_script() { 
	wp_register_script('wcs3_admin_js', WCS3_PLUGIN_URL . '/js/wcs3_admin.js', array( 'jquery' ), '1.0.0');
	wp_enqueue_script( 'wcs3_admin_js' );

	wp_localize_script( 'wcs3_admin_js', 'WCS3_AJAX_OBJECT', array(
    	'ajax_error' => __( 'Error', 'wcs3' ),
	    'add_item' => __( 'Add Item', 'wcs3' ),
	    'save_item' => __( 'Save Item', 'wcs3' ),
	    'cancel_editing' => __( 'Exit edit mode', 'wcs3' ),
	    'edit_mode' => __( 'Edit Mode', 'wcs3' ),
	    'delete_warning' => __( 'Are you sure you want to delete this entry?', 'wcs3' ),
	    'import_warning' => __( 'Are you sure you want to to this? This will delete all data added after updating to version 3.', 'wcs3' ),
    	'ajax_url' => admin_url( 'admin-ajax.php' ),
    	'ajax_nonce' => wp_create_nonce( 'wcs3-ajax-nonce' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'wcs3_load_admin_script' );

/**
 * Loads plugins necessary for admin area such as the colorpicker.
 */
function wcs3_load_admin_plugins() {
    // Colorpicker
    wp_register_style( 'wcs3_colorpicker_css', WCS3_PLUGIN_URL . '/plugins/colorpicker/css/colorpicker.min.css' );
    wp_enqueue_style( 'wcs3_colorpicker_css' );
    
    wp_enqueue_script(
    		'wcs3_colorpicker',
    		WCS3_PLUGIN_URL. '/plugins/colorpicker/js/colorpicker.min.js',
    		array( 'jquery' )
    );
}
add_action( 'admin_enqueue_scripts', 'wcs3_load_admin_plugins' );

/**
 * Callback for generating the schedule management page.
 */
function wcs3_schedule_management_page_callback() {
    ?>
    <h1><?php _e('Schedule Management', 'wcs3'); ?></h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">    
        <div id="wcs3-schedule-management-form-wrapper">
            <table id="wcs3-schedule-management-form" class="widefat wp-list-table">
                <tr>
                    <td class="wcs3-col-label"><?php _e('Class', 'wcs3'); ?></td>
                    <td><?php echo wcs3_generate_admin_select_list( 'class', 'wcs3_class' ); ?></td>
                </tr>
                <tr>
                    <td class="wcs3-col-label"><?php _e('Instructor', 'wcs3'); ?></td>
                    <td><?php echo wcs3_generate_admin_select_list( 'instructor', 'wcs3_instructor' ); ?></td>
                </tr>
                <tr>
                    <td class="wcs3-col-label"><?php _e('Location', 'wcs3'); ?></td>
                    <td><?php echo wcs3_generate_admin_select_list( 'location', 'wcs3_location' ); ?></td>
                </tr>
                <tr>
                    <td class="wcs3-col-label"><?php _e('Day', 'wcs3'); ?></td>
                    <td><?php echo wcs3_generate_weekday_select_list( 'wcs3_weekday' ); ?></td>
                </tr>
                <tr>
                    <td class="wcs3-col-label"><?php _e('Start Hour', 'wcs3'); ?></td>
                    <td><?php echo wcs3_generate_hour_select_list( 'wcs3_start_time', array( 'hour' => 9, 'minute' => 0 ) ); ?></td>
                </tr>
                <tr>
                    <td class="wcs3-col-label"><?php _e('End Hour', 'wcs3'); ?></td>
                    <td><?php echo wcs3_generate_hour_select_list( 'wcs3_end_time', array( 'hour' => 10, 'minute' => 0 ) ); ?></td>
                </tr>
                <tr>
                    <td class="wcs3-col-label"><?php _e('Visibility', 'wcs3'); ?></td>
                    <td><?php echo wcs3_generate_visibility_select_list( 'wcs3_visibility', 'visible' ); ?></td>
                </tr>
                <tr>
                    <td class="wcs3-col-label"><?php _e('Notes', 'wcs3'); ?></td>
                    <td><textarea rows="3" id="wcs3_notes" name="wcs3_notes" placeholder="Notes"></textarea></td>
                </tr>
            </table>
            
            <div id="wcs3-schedule-buttons-wrapper">
                <input id="wcs3-submit-item" type="submit" class="button-primary" value="<?php _e( 'Add Item', 'wcs3' ); ?>" name="wcs3-submit-item" />
                <span class="wcs3-ajax-loader"><img src="<?php echo WCS3_PLUGIN_URL . '/img/loader.gif'; ?>" alt="Ajax Loader" /></span>
                <div id="wcs3-ajax-text-wrapper" class="wcs3-ajax-text"></div>
            </div>
        </div> <!-- /#schedule-management-form-wrapper -->
    </form>
    
    <div id="wcs3-schedule-events-list-wrapper">
        <?php $days = wcs3_get_weekdays(); ?>
        <?php foreach( $days as $key => $day ): ?>
            <div id="wcs3-schedule-day-<?php echo $key; ?>">
                <h3><?php echo $day; ?></h3>
                <?php echo wcs3_render_day_table( $key ); ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php 
}

/**
 * Import/Update page callback.
 */
function wcs3_import_update_page_callback() { ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">    
        <div id="wcs3-import-update-wrapper" class="wrap">
            <h2>Import/Update</h2>
            
            <p><?php _e( 'Click the button below to import the data from Weekly Class Schedule 2.x.', 'wcs3' ); ?></p>
            <p><strong><?php _e( 'WARNING:' ) ?></strong> <?php _e( 'All data added after updating to version 3 will be deleted.' ); ?></p>
            
            <input type="submit" name="wcs3_import_wcs2_data" id="wcs3_import_wcs2_data" class="button-primary" value="<?php _e( 'Import/Update', 'wcs3' ); ?>">
            <span class="wcs3-ajax-loader"><img src="<?php echo WCS3_PLUGIN_URL . '/img/loader.gif'; ?>" alt="Ajax Loader" /></span>
            <div id="wcs3-ajax-text-wrapper" class="wcs3-ajax-text"></div>
        </div>
    </form>
    <?php 
}

/**
 * Generates a select list of id => titles from the array of WP_Post objects.
 *
 * @param string $type: can be either class, instructor, or location
 */
function wcs3_generate_admin_select_list( $type, $name = '', $default = NULL ) {
	$t = 'wcs3_' . $type;
	$posts = wcs3_get_posts_of_type( $t );

	$values = array();

	if (!empty($posts)) {
		foreach ( $posts as $post ) {
			$values[$post->ID] = $post->post_title;
		}
	}

	return wcs3_select_list( $values, $name, $default );
}

/**
 * Generates a select list of weekdays.
 */
function wcs3_generate_weekday_select_list( $name = '', $default = NULL ) {
    $days = wcs3_get_weekdays();
    return wcs3_select_list( $days, $name, $default );
}

function wcs3_generate_hour_select_list( $name = '', 
    $default = array( 'hour' => NULL, 'minute' => NULL ) ) {
    
    $output = '';
    
    $hours = wcs3_select_list( range( 0, 24, 1 ) , $name . '_hours', $default['hour'] );
    
    $minutes_arr = array();
    foreach ( range(0, 59, 5) as $key => $value ) {
        $minutes_arr[$value] = $value;
    }
    
    $minutes = wcs3_select_list( $minutes_arr , $name . '_minutes', $default['minute'] );
    
    $output .= $hours . $minutes;
    
    return $output;
}

/**
 * Generates the simple visibility list.
 */
function wcs3_generate_visibility_select_list( $name = '', $default = NULL ) {
    $values = array(
        'hidden' => __( 'Hidden', 'wcs3' ),
        'visible' => __( 'Visible', 'wcs3' ),
    );
    
    return wcs3_select_list( $values, $name, $default );
}

/**
 * Generates the locations select list.
 */
function wcs3_generate_locations_select_list( $name = '', $default = NULL, $id = '' ) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'posts';
    
    $values = array( 'all' => __( 'All Locations', 'wcs3' ) );
    
    $query = "SELECT ID, post_title FROM $table WHERE post_type = 'wcs3_location'";
    $results = $wpdb->get_results( $query );
    
    if ( $results ) {
        foreach ( $results as $location ) {
            $values[$location->ID] = $location->post_title;
        }
    }
    
    return wcs3_select_list( $values, $name, $default, $id );
}

/**
 * Delete schedule entries when class, instructor, or location gets deleted.
 */
function wcs3_schedule_sync( $post_id ) {
    global $wpdb;
    $table = wcs3_get_table_name();
    
    // Since all three custom post types are in the same table, we can
    // assume the the ID will be unique so there's no need to check for
    // post type.
    $query = "DELETE FROM $table 
                WHERE class_id = %d OR instructor_id = %d 
                OR location_id = %d";
    
    $wpdb->query( $wpdb->prepare(
            $query, 
            array( $post_id, $post_id, $post_id )
            ) );
}
add_action( 'delete_post', 'wcs3_schedule_sync', 10 );