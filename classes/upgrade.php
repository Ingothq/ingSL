<?php
/**
 * Handle update requests from inside of trial mode
 *
 * @package   ingSL
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 Josh Pollock
 */

namespace ingSL;


class upgrade {

	/**
	 * Listen for incoming upgrade attempt and route as needed
	 */
	public static function listen(){
		if( isset( $_GET[ 'imt-upgrade' ] ) && 0 < absint( $_GET[ 'imt-upgrade' ] ) ){
			self::store_token();
			if( is_user_logged_in() ) {
				self::handle_with_cart();
			}else{
				self::login_redirect();
			}

		}
	}

	/**
	 * Redirect to login, with login redirect params set to loop back
	 */
	protected static function login_redirect(){
		$url = wp_login_url(  add_query_arg( 'imt-upgrade',absint( $_GET[ 'imt-upgrade' ] ), edd_get_checkout_uri() ) );
		wp_redirect( $url );
		exit;
	}

	/**
	 * Store upgrade token and site
	 */
	protected static function store_token(){
		if( isset( $_GET[ 'imt-token' ] ) ){
			update_post_meta( absint( $_GET[ 'imt-upgrade' ] ), 'imt_token', strip_tags( $_GET[ 'imt-token' ] ) );

			if( isset( $_GET[ 'imt-site' ] ) ){
				update_post_meta( absint( $_GET[ 'imt-upgrade' ] ), 'imt_site', esc_url_raw( $_GET[ 'imt-site' ] ) );
			}
		}

	}

	/**
	 * Ping API on remote site to attempt completion of upgrade
	 *
	 * @param $license_id
	 */
	public static function ping_site( $license_id ) {
		$token = get_post_meta( $license_id, 'imt_token', true );
		$url   = get_post_meta( $license_id, 'imt_site', true );

		if ( is_string( $token ) && filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$url = trailingslashit( $url ) . 'wp-json/ingot/v1/trial/upgrade';
			$license = \EDD_Software_Licensing::instance()->get_license_key( $license_id );
			//var_dump( [ $token, $url , $license] );
			$r = wp_remote_post( $url,
				[
					'body' => [
							'imt_token' => $token,
							'license' => $license
						]

				]
			);
			//var_dump( $r );die();
		}
	}


	/**
	 * Handle upgrading by adding upgrade to cart and re-directing to EDD checkout
	 */
	protected static function handle_with_cart(){
		$license_id = absint( $_GET[ 'imt-upgrade' ] );
		self::prepare_upgrade( $license_id );
		self::redirect();
	}


	/**
	 * Redirect to EDD cart
	 */
	protected static function redirect(){
		wp_redirect( edd_get_checkout_uri() );
		exit;
	}

	/**
	 * Add the license upgrade from trial to the cart
	 *
	 * @param int $license_id
	 *
	 * @return string
	 */
	protected static function prepare_upgrade( $license_id ){
		$download_id = \EDD_Software_Licensing::instance()->get_download_id( $license_id );
		if ( is_numeric( $download_id ) ) {
			$upgrades     = edd_sl_get_upgrade_paths( $download_id );
			if( is_array( $upgrades ) && ! empty( $upgrades ) ){
				reset( $upgrades );
				$upgrade_price_id = key( $upgrades );
				$upgrade          = edd_sl_get_upgrade_path( ids::$trial_id, $upgrade_price_id );
				$options          = array(
					'price_id'   => $upgrade[ 'price_id' ],
					'is_upgrade' => true,
					'upgrade_id' => $upgrade_price_id,
					'license_id' => $license_id,
					'cost'       => edd_sl_get_license_upgrade_cost( $license_id, $upgrade_price_id )
				);

				$added = edd_add_to_cart( $upgrade[ 'download_id' ], $options );
				return $added;
			}

		}

	}


}
