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
		wp_redirect( wp_login_url(  add_query_arg( 'imt-upgrade',absint( $_GET[ 'imt-upgrade' ] ), edd_get_checkout_uri() ) ) );
		exit;
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
		$upgrade_price_id = 1;
		$upgrade     = edd_sl_get_upgrade_path( ids::$trial_id, $upgrade_price_id );
		$options     = array(
			'price_id'   => $upgrade['price_id'],
			'is_upgrade' => true,
			'upgrade_id' => $upgrade_price_id,
			'license_id' => $license_id,
			'cost'       => edd_sl_get_license_upgrade_cost( $license_id, $upgrade_price_id )
		);

		$added = edd_add_to_cart( $upgrade['download_id'], $options );
		return $added;

	}

}
