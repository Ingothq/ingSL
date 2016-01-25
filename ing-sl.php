<?php
/**
 Plugin Name: IngSL
 */


/**
 * Setup
 */
add_action( 'plugins_loaded', function(){
	if(  defined( 'EDD_SL_VERSION' ) ){

		ingSL_load_classes();
		if( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ){
			\ingSL\ids::$download_id = 29;
			\ingSL\ids::$trial_id = 30;
			\ingSL\ids::$upgrade_id = 2;
		}
		add_action( 'init', [ '\ingSL\IngSL', 'listen' ], 1 );
		add_action( 'init', [ '\ingSL\upgrade', 'listen'], 2 );
		add_action( 'edd_activate_license', [ '\ingSL\IngSL', 'activate_license' ], 1 );
		add_action( 'edd_sl_license_upgraded', [ '\ingSL\ingSL', 'maybe_upsell' ]);
		add_filter( 'edd_remote_license_activation_response', [ '\ingSL\IngSL', 'activation_response' ] );
		add_filter( 'edd_remote_license_check_response', [ '\ingSL\IngSL', 'license_check_response' ], 10, 3 );

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
 * List of acceptable verticals
 *
 * @todo  pull from api
 *
 * @return array
 */
function ingSL_verticals(){
	$verticals = [
		'startup',
		'store',
		'membership',
		'non-profit',
		'wp',
		'media',
		'inbound',
	];

	return $verticals;

}

