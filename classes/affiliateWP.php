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


class affiliateWP {

	const MOJO_ID = 1;

	public static function add_visit(){
		$x = get_option( __CLASS__ . __METHOD__ , 0 );
		update_option( __CLASS__ . __METHOD__, $x + 1 );
	}

	public static function add_conversion(){
		$x = get_option( __CLASS__ . __METHOD__ , 0 );
		update_option( __CLASS__ . __METHOD__, $x + 1 );
	}

}
