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
	 * After license activation, if is trial -- add to a list
	 *
	 * @used "edd_activate_license"
	 */
	public static  function activate_license( $data ){

		$license    = ! empty( $data[ 'license' ] ) ? urldecode( $data[ 'license' ] ) : false;
		$license_id = \EDD_Software_Licensing::instance()->get_license_by_key( $license );
		$payment_id = get_post_meta( $license_id, '_edd_sl_payment_id', true );
		$user_info  = edd_get_payment_meta_user_info( $payment_id );

		self::save_license_meta( $data, $license_id, $user_info, $payment_id );

		$item_id    = ! empty( $data[ 'item_id' ] ) ? absint( $data[ 'item_id' ] ) : false;

		if(1 ==3 && class_exists( 'EDD_ConvertKit' ) && isset( $data[ 'details' ][ 'vertical' ] ) && ( self::$trial || ingSL::$trial == $item_id )  ) {
			self::$trial = true;
			$vertical = $data[ 'details' ][ 'vertical' ];
			if(  ! in_array( $vertical, ingSL_verticals() ) ) {
				return;
			}

			//$url        = isset( $data[ 'url' ] ) ? urldecode( $data[ 'url' ] ) : '';

			$convert_kit = new \EDD_ConvertKit();
			$convert_kit->subscribe_email( $user_info, $vertical, true );
		}

	}

	public static function save_license_meta( $data, $license_id, $user_info, $payment_id ){

		$metas = [
			'datetime'   => current_time( 'mysql' ),
			'url'        => isset( $data[ 'url' ] ) ? esc_url_raw( $data[ 'url' ] ) : false,
			'user'       => $user_info,
			'payment_id' => $payment_id
		];



		if( isset( $data[ 'details' ]) ){
			$details = $data[ 'details' ];
			foreach (
				[
					'wp_ver',
					'ingot_ver',
					'php_ver',
					'vertical'
				] as $detail
			) {
				$metas[ $detail ] = isset( $details[ $detail ] ) ? trim( $details[ $detail ] ) : false;
			}
		}

		update_post_meta( $license_id, '_ingSL_details', $metas );



	}

	/**
	 * Watch for license activation
	 *
	 * @uses init
	 */
	public  static function listen(){
		if( isset( $_POST[ 'edd_action' ], $_POST[ 'license' ] ) && 'activate_license' == $_POST[ 'edd_action' ]  ) {
			if( is_email( $_POST[ 'license' ] ) ) {
				$create = new create( sanitize_email( $_POST[ 'license' ] ) );
				if( is_string( self::$code = $create->get_license_code() ) ){
					self::$license_id  = $create->get_license_id();
					$_POST[ 'license' ] = self::$code;
					self::$user = $create->get_user();
					self::$trial = true;

				}
			}else{
				self::$code = strip_tags( $_POST[ 'license' ] );
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
		$result[ 'trial' ] = self::$trial;
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
