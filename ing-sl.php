<?php
/**
 Plugin Name: IngSL
 */

/**
 * Setup
 */
add_action( 'plugins_loaded', function(){
	if( defined( 'PODS_VERSION' ) && defined( 'EDD_SL_VERSION' ) ){
		ingSL_load_classes();
		add_action( 'init', [ '\ingSL\IngSL', 'listen' ], 1 );
		add_action( 'edd_activate_license', [ '\ingSL\IngSL', 'activate_license' ], 1 );
		add_filter( 'edd_remote_license_activation_response', [ '\ingSL\IngSL', 'response' ] );
		add_filter( 'pods_api_pre_save_pod_item_site', 'ingSL_pre_save_site', 10 );
	}


});

/**
 * Load our classes
 */
function ingSL_load_classes(){
	$files = glob( dirname(__FILE__ ) . '/classes/*.php' );

	foreach( $files as $i => $path ){
		include_once( $path );
	}

}

/**
 * Sanatize savins of site URL
 *
 * @uses 'pods_pre_save_item_site'
 *
 * @param $pieces
 *
 * @return mixed
 */
function ingSL_pre_save_site( $pieces ){

		$pieces[ 'fields' ][ 'url' ][ 'value' ] = esc_url_raw( $pieces[ 'fields' ][ 'url' ][ 'value' ] );

		return $pieces;

}
