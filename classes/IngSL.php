<?php

/**
 * Main class for system
 *
 * @package   ingSL
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 Josh Pollock
 */

namespace ingSL;

class IngSL {

	/** @var bool  */
	protected static $trial = false;

	/** @var string  */
	protected static $code = false;

	//** @var \WP_User  */
	protected static $user;

	/** @var  int */
	protected static $license_id;

	/**
	 * When license is activated create checkin
	 *
	 * @used "edd_activate_license"
	 */
	public static  function activate_license( $args ){
		$args[ 'trial' ] = self::$trial;
		new checkin( $args, self::$user );

	}


	/**
	 * Watch for license activation
	 *
	 * @TODO Update checks
	 *
	 * @uses init
	 */
	public  static function listen(){
		if( 'activate_license' == pods_v( 'edd_action', 'post', false, true )  ) {
			if( is_email( pods_v( 'license', 'post', false, true ) ) ) {
				$create = new create(
					sanitize_email( pods_v( 'license', 'post' ) ),
					pods_v( 'url', 'post' )
				);
				if( is_string( self::$code = $create->get_license_code() ) ){
					self::$license_id  = $create->get_license_id();
					$_POST[ 'license' ] = self::$code;
					self::$user = $create->get_user();
					self::$trial = true;

				}
			}else{
				self::$code = pods_v_sanitized( 'license', 'post' );
			}

		}


	}

	/**
	 * Add data to the response from API
	 *
	 * @uses edd_remote_license_activation_response
	 *
	 * @param array $result
	 *
	 * @returns array
	 */
	public static function activation_response( $result ){
		$result[ 'trial' ] = (string) self::$trial;
		$result[ 'license_code' ] = (string) self::$code;
		$result[ 'ing_uid' ] =  self::$user->ID;
		$result[ 'license_id' ] = self::$license_id;

		return $result;

	}

	/**
	 * On license check response add trial indication
	 *
	 * @uses "edd_remote_license_check_response"
	 *
	 * @param array $result
	 * @param array $args
	 *
	 * @return array
	 */
	public static function license_check_response( $result, $args, $license_id ){
		if( ids::$trial_id == $args[ 'item_id' ]  ){
			self::$trial = true;
		}elseif( ids::$download_id == $args[ 'item_id' ] ){
			self::$trial = false;
		}elseif ( get_post_meta( $license_id, '_ingsl_is_trial', true ) && ! get_post_meta( $license_id, '_ingsl_upsold', true ) ) {
			self::$trial = true;
		}elseif( get_post_meta( $license_id, '_ingsl_is_trial', true ) && get_post_meta( $license_id, '_ingsl_upsold', true ) ){
			self::$trial = false;
		}else{
			self::$trial = 'unknown';
		}

		$result[ 'trial' ] = self::$trial;

		return $result;
	}

	/**
	 * Check if upgrade of license is an upsell
	 *
	 * @uses "edd_sl_license_upgraded"
	 */
	public static function maybe_upsell( $license_id ){

			if( get_post_meta( $license_id, '_ingsl_is_trial', true  ) && ! get_post_meta( $license_id, '_ingsl_upsold', true  ) ){
				update_post_meta( $license_id, '_ingsl_upsold', 1  );
				upgrade::ping_site( $license_id );

			}


	}

}
