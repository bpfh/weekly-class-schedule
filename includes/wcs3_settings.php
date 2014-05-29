<?php
/**
 * Settings page.
 */

function wcs3_standard_settings_page_callback() {
    $wcs3_options = wcs3_load_settings();
    
    if ( isset( $_POST['wcs3_options_nonce'] ) ) {
        // We got a submission
        $nonce = sanitize_text_field( $_POST['wcs3_options_nonce'] );
        $valid = wp_verify_nonce( $nonce, 'wcs3_save_options' );
        
        if ($valid === FALSE) {
        	// Nonce verification failed.
        	wcs3_options_message( __('Nonce verification failed', 'wcs3'), 'error' );
        }
        else {
        	wcs3_options_message( __('Options updated', 'wcs3') );
        
        	// Create a validataion fields array:
        	// id_of_field => validation_function_callback
        	$fields = array(
        	    'first_day_of_week' => 'wcs3_validate_weekday',
        	    '24_hour_mode' => 'wcs3_validate_yes_no',
        	    'location_collision' => 'wcs3_validate_yes_no',
        	    'instructor_collision' => 'wcs3_validate_yes_no',
        	    'details_template' => 'wcs3_validate_html',
        	    'allow_html_in_notes' => 'wcs3_validate_yes_no',
        	    'color_base' => 'wcs3_validate_color',
        	    'color_details_box' => 'wcs3_validate_color',
        	    'color_text' => 'wcs3_validate_color',
        	    'color_border' => 'wcs3_validate_color',
        	    'color_headings_text' => 'wcs3_validate_color',
        	    'color_headings_background' => 'wcs3_validate_color',
        	    'color_background' => 'wcs3_validate_color',
        	    'color_qtip_background' => 'wcs3_validate_color',
        	    'color_links' => 'wcs3_validate_color',
        	);
        	
        	$wcs3_options = wcs3_perform_validation( $fields, $wcs3_options );
        	
        	wcs3_save_settings( $wcs3_options );
        }
    }
    
    ?>
    
    <h2><?php _e( 'Weekly Class Schedule Settings', 'wcs3' ); ?></h2>
    <h4><?php _e( 'Using Weekly Class Schedule', 'wcs3' ); ?></h4>
    <p>
        <?php _e( 'To display all the classes in a single schedule, simply enter the shortcode', 'wcs3'); ?><code>[wcs]</code>
        <?php _e( 'inside a page or a post.', 'wcs3 '); ?>
        <?php _e( 'It\'s also possible to output the schedule as a list using the list layout:', 'wcs3' ); ?><code>[wcs layout=list]</code>
        <?php _e( 'In order to display a single location, use the location attribute like this:', 'wcs3' ); ?><code>[wcs location="Classroom A"]</code>
        <?php _e( 'Where "Classroom A" is the name of the location as it appears in the database.', 'wcs3' ); ?>
    </p>
    
    <p> 
        <?php _e( 'A finalized shortcode may look something like', 'wcs3' ); ?> <code>[wcs location="Classroom A" layout=list]</code>
    </p>  
    
    <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" name="wcs3_general_settings">
        <h3> <?php _e( 'General Settings', 'wcs3' ); ?></h3>
        <table class="form-table">
            <tr>
                <th>
                    <?php _e( 'First day of week', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'The day the schedule will start in', 'wcs3' ); ?></div>
                </th>
                <td><?php echo wcs3_generate_weekday_select_list( 'wcs3_first_day_of_week', $wcs3_options['first_day_of_week'] ); ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Enable 24-hour mode', 'wcs3' ); ?>
                    <div class="wcs3-description"><?php _e( 'Enabling this will display all the hours on the front-end in a 24 hour clock mode as opposed to 12 hour clock mode (AM/PM).', 'wcs3' ); ?></div>    
                </th>
                <td><?php wcs3_bool_checkbox( 'wcs3_24_hour_mode', $wcs3_options['24_hour_mode'], __('Yes') ); ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Detect location collisions', 'wcs3' ); ?>
                    <div class="wcs3-description"><?php _e( 'Enabling this feature will prevent scheduling of multiple classes at the same location at the same time.', 'wcs3' ); ?></div>    
                </th>
                <td><?php wcs3_bool_checkbox( 'wcs3_location_collision', $wcs3_options['location_collision'], __('Yes') ); ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Detect instructor collisions', 'wcs3' ); ?>
                    <div class="wcs3-description"><?php _e( 'Enabling this feature will prevent the scheduling of an instructor for multiple classes at the same.', 'wcs3' ); ?></div>    
                </th>
                <td><?php wcs3_bool_checkbox( 'wcs3_instructor_collision', $wcs3_options['instructor_collision'], __('Yes') ); ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Class Details Template', 'wcs3' ); ?>
                    <div class="wcs3-description"><?php _e( 'Use placholders to design the way the class details appear in the schedule. Certain HTML tags are allowed (to customize edit $wcs3_allowed_html in wcs.php).', 'wcs3' ); ?></div>
                    <br/>
                    <div class="wcs3-description"><strong><?php _e( 'Available placholders:', 'wcs3'); ?></strong> [class], [instructor], [location], [start hour], [end hour], [notes].</div>
                </th>
                <td>
                    <textarea name="wcs3_details_template" cols="40" rows="6"><?php echo $wcs3_options['details_template']; ?></textarea>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Allow all HTML in notes', 'wcs3' ); ?>
                    <div class="wcs3-description"><?php _e( 'Allow all HTML tags in notes field. PLEASE NOTE: Allowing all HTML tags has security implications so use at your own risk.', 'wcs3' ); ?></div>    
                </th>
                <td><?php wcs3_bool_checkbox( 'wcs3_allow_html_in_notes', $wcs3_options['allow_html_in_notes'], __('Yes') ); ?></td>
            </tr>
        </table>
        
        <h3> <?php _e( 'Appearance Settings', 'wcs3' ); ?></h3>
        <table class="form-table">
            <tr>
                <th>
                    <?php _e( 'Base class', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'The default background color for classes in the schedule.', 'wcs3' ); ?></div>
                </th>
                <td><?php wcs3_colorpicker( 'wcs3_color_base', $wcs3_options['color_base'] ) ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Class details box', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'Background color of the class details box which appears when hovering over a class.', 'wcs3' ); ?></div>
                </th>
                <td><?php wcs3_colorpicker( 'wcs3_color_details_box', $wcs3_options['color_details_box'] ) ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Text', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'Text color of schedule entries/classes.', 'wcs3' ); ?></div>
                </th>
                <td><?php wcs3_colorpicker( 'wcs3_color_text', $wcs3_options['color_text'] ) ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Border', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'This color is used for all borders in the schedule output.', 'wcs3' ); ?></div>
                </th>
                <td><?php wcs3_colorpicker( 'wcs3_color_border', $wcs3_options['color_border'] ) ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Schedule headings color', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'Text color of the schedule headings (weekdays, hours).', 'wcs3' ); ?></div>
                </th>
                <td><?php wcs3_colorpicker( 'wcs3_color_headings_text', $wcs3_options['color_headings_text'] ) ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Schedule headings background', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'Background color of the schedule headings (weekdays, hours).', 'wcs3' ); ?></div>
                </th>
                <td><?php wcs3_colorpicker( 'wcs3_color_headings_background', $wcs3_options['color_headings_background'] ) ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Background', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'Background color for the entire schedule.', 'wcs3' ); ?></div>
                </th>
                <td><?php wcs3_colorpicker( 'wcs3_color_background', $wcs3_options['color_background'] ) ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'qTip background', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'Background color of the qTip pop-up box.', 'wcs3' ); ?></div>
                </th>
                <td><?php wcs3_colorpicker( 'wcs3_color_qtip_background', $wcs3_options['color_qtip_background'] ) ?></td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Links', 'wcs3' ); ?><br/>
                    <div class="wcs3-description"><?php _e( 'The color of the links which appear in the class details box.', 'wcs3' ); ?></div>
                </th>
                <td><?php wcs3_colorpicker( 'wcs3_color_links', $wcs3_options['color_links'] ) ?></td>
            </tr>
        </table>
        
        <?php submit_button( __( 'Save Settings' ) ); ?>
        <?php wp_nonce_field( 'wcs3_save_options', 'wcs3_options_nonce' ); ?>
    </form>
    
    <?php 
}

/**
 * Gets the standard wcs3 settings from the database and return as an array.
 */
function wcs3_load_settings() {
    wcs3_set_default_settings();
    $settings = get_option( 'wcs3_settings' );
    return unserialize( $settings );
}

/**
 * Saves the settings array
 * 
 * @param array $settings: 'option_name' => 'value'
 */
function wcs3_save_settings( $settings ) {
    $settings = serialize( $settings );
    update_option( 'wcs3_settings', $settings );
}

/**
 * Set default WCS3 settings.
 */
function wcs3_set_default_settings() {
    $settings = get_option( 'wcs3_settings' );
    if ( $settings === FALSE ) {
        // No settings yet, let's load up the default.
        $options = array(
            'first_day_of_week' => 0,
            '24_hour_mode' => 'no',
            'location_collision' => 'yes',
            'instructor_collision' => 'yes',
            'details_template' => '[class] with [instructor] [start hour] to [end hour] [notes]',
            'allow_html_in_notes' => 'no',
            'color_base' => 'DDFFDD',
            'color_details_box' => 'FFDDDD',
            'color_text' => '373737',
            'color_border' => 'DDDDDD',
            'color_headings_text' => '666666',
            'color_headings_background' => 'EEEEEE',
            'color_background' => 'FFFFFF',
            'color_qtip_background' => 'FFFFFF',
            'color_links' => '1982D1',
        );
        
        $serialized = serialize( $options );
        add_option( 'wcs3_settings', $serialized );
    }
}
add_action( 'wcs3_default_settings', 'wcs3_set_default_settings' );