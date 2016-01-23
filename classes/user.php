<?php
/**
 * Query for user or create by email
 *
 * @package   ingSL
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 Josh Pollock
 */

namespace ingSL;


class user {
	/**
	 * @var \WP_User
	 */
	protected $user;

	/**
	 * @param \WP_User|string $user User object or email
	 */
	public function __construct( $user ){
		$this->set_user( $user );
	}

	/**
	 * Get the user
	 *
	 * @return \WP_User
	 */
	public function get_user(){
		return $this->user;
	}

	/**
	 * Set user, creating if needed
	 *
	 * @param $user
	 *
	 * @return bool
	 */
	protected function set_user( $user ){
		if( is_a( $user, 'WP_User'  ) ){
			$this->user = $user;
			return true;
		}

		if( is_email( $user ) ) {
			$email = $user;
		}

		$user = get_user_by( 'email', $email );
		if( is_a( $user, 'WP_User' ) ){
			$this->user = $user;
			return true;
		}

		$id = wp_create_user( $email, wp_generate_password( rand( 8, 13 ) ), $email );

		if( is_wp_error( $id ) ){
			$this->user = get_user_by( 'email', $email );
		}else{
			$this->user = get_user_by( 'ID', $id );
		}

		if( ! is_a( $this->user, 'WP_User') ){
			return false;
		}



	}


}
