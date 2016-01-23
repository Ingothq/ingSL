<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 Josh Pollock
 */

namespace ingSL;


class upgrade {




	public static function listen(){
		if( isset( $_GET[ 'imt_upgrade' ], $_GET[ 'imt_iuid' ] ) ){

		}
	}

	public static function redirect_one(){

	}

	public static function prepare_upgrade( $license_id ){
		$uprgade_price_id = 0;
		$upgrade     = edd_sl_get_upgrade_path( IngSL::TRIAL_ID, $uprgade_price_id );
		$options     = array(
			'price_id'   => $upgrade['price_id'],
			'is_upgrade' => true,
			'upgrade_id' => $uprgade_price_id,
			'license_id' => $license_id,
			'cost'       => edd_sl_get_license_upgrade_cost( $license_id, $uprgade_price_id )
		);

		edd_add_to_cart( $upgrade['download_id'], $options );
	}

}
