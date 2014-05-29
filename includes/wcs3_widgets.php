<?php
/**
 * WCS3 Widgets
 */

/**
 * Adds Foo_Widget widget.
 */
class WCS3_TodayClassesWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
				'wcs3_today_classes_widget', // Base ID
				__( 'WCS3 Today\'s Classes', 'wcs3' )
		);
		
		// IMPORTANT
		wcs3_set_global_timezone();
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	    global $wpdb;
	    $table = wcs3_get_table_name();
	    $output = '';
	    
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
		
		// Get today's weekday index
		$today = date( 'w' , time());
		$location_id = ( $instance['location'] != 'all' ) ? $instance['location'] : NULL;
		$max_classes = intval( $instance['max_classes'] );
		$limit = ( is_int( $max_classes ) ) ? $max_classes : NULL;
		$no_entries_msg = ( strlen( $instance['no_entries_text'] ) > 0 ) ? $instance['no_entries_text'] : __( 'No classes today' );
		
		$schedule = wcs3_get_day_schedule( $today, $location_id, $limit );
		
		if ( $schedule == FALSE ) {
		    $output .= '<div class="wcs3-no-classes">' . $no_entries_msg . '</div>';
		    echo $output;
		    return;
		}
		
		$output .= '<ul class="wcs3-today-classes-widget-list">';
		
		foreach ( $schedule as $key => $entry ) {
		    $start_hour = $entry['start_hour'];
		    $class_name = $entry['class'];
		    $output .= "<li>$start_hour - $class_name</li>";
		}
		
		$output .= '</ul>';
		
		echo $output;
		
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
	    $title = ( isset( $instance[ 'title' ] ) ) ? $instance[ 'title' ] : __( "Today's Classes", 'wcs3' );
	    $max_classes = ( isset( $instance[ 'max_classes'] ) ) ? $instance[ 'max_classes'] : 5;
	    $location = ( isset( $instance[ 'location' ] ) ) ? $instance[ 'location'] : 'all';
        $no_entries_text = ( isset( $instance[ 'no_entries_text' ] ) ) ? $instance[ 'no_entries_text'] : __( 'No classes today' );
		
		/* Print Form */
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'wcs3' ); ?>:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'max_classes' ); ?>"><?php _e( 'Maximum Classes to Display', 'wcs3' ); ?>:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'max_classes' ); ?>" name="<?php echo $this->get_field_name( 'max_classes' ); ?>" type="text" value="<?php echo esc_attr( $max_classes ); ?>" />
		<span class='wcs3-description'><?php __( 'Maximum number of classes to display', 'wcs3' ); ?></span>
		</p>

		<p>
		    <?php echo wcs3_generate_locations_select_list( $this->get_field_name( 'location' ), 
		                                                    esc_attr( $location ),
		                                                    $this->get_field_id( 'location' ) ); ?>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'no_entries_text' ); ?>"><?php _e( 'No entries message', 'wcs3' ); ?>:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'no_entries_text' ); ?>" name="<?php echo $this->get_field_name( 'no_entries_text' ); ?>" type="text" value="<?php echo esc_attr( $no_entries_text ); ?>" />
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max_classes'] = strip_tags( $new_instance['max_classes'] );
		$instance['location'] = strip_tags( $new_instance['location'] );
		$instance['no_entries_text'] = strip_tags( $new_instance['no_entries_text'] );

		return $instance;
	}

} // class WCS3_TodayClassesWidget

// Register WCS3 widgets
function register_wcs3_widgets() {
    // Register today's classes widget
	register_widget( 'WCS3_TodayClassesWidget' );
}
add_action( 'widgets_init', 'register_wcs3_widgets' );