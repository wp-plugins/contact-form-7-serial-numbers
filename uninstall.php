<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

function wpcf7sn_delete_plugin() {
    global $wpdb;

    $options = wpcf7sn_get_plugin_options();

    $option_file = dirname( __FILE__ ) . '/' . 'wpcf7sn_options.txt';
    if ( file_exists( $option_file ) ) {
        unlink( $option_file );
        foreach( $options as $key=>$valuve ) {
            delete_option( $key );
        }
    }
}

function wpcf7sn_get_plugin_options() {
    global $wpdb;

    $values = array();
    $results = $wpdb->get_results( "
        SELECT *
          FROM $wpdb->options
         WHERE 1 = 1
           AND option_name like 'nklab_wpcf7sn_%'
         ORDER BY option_name
    " );

    foreach ( $results as $result ) {
        $values[ $result->option_name ] = $result->option_value;
    }

    return $values;
}

wpcf7sn_delete_plugin();

?>