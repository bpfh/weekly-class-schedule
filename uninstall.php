<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function wcs3_delete_plugin() {
    global $wpdb;
    
    delete_option( 'wcs3_db_version' );
    delete_option( 'wcs3_settings' );
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
        
        foreach ( $posts as $post )
        	wp_delete_post( $post->ID, true );
    }
    
    $table_name = $wpdb->prefix . "wcs3_schedule";
    
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

wcs3_delete_plugin();

?>