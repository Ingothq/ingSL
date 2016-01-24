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


class checkin {

	/**
	 * Name of Pod
	 *
	 * @since 1.1.0
	 */
	const POD_NAME = 'checkin';

	/**
	 * @var \Pods
	 */
	protected $pod;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var int
	 */
	protected $ID;

	/**
	 * @var \WP_User
	 */
	protected $user;

	/**
	 *
	 *
	 * @param string $data
	 * @param \WP_User|null $user
	 */
	public function __construct( $data, \WP_User $user = null ){
		$this->set_pod( pods_v( 'id', $data, null, true ) );
		$this->prepare_data( $data );
		$this->set_user( $user );
		$this->save();
	}

	/**
	 * Get ID of checkin
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->ID;
	}

	/**
	 * Setup user
	 *
	 * If not explicitly set, user is gotten from license
	 *
	 * @param \WP_User|null $user
	 */
	protected function set_user( \WP_User $user = null ){
		if( is_a( $user, 'WP_User' ) ) {
			$this->user = $user;
			$this->data[ 'user' ] = $user->ID;
		}else{
			$this->data[ 'user'] = (int) get_post_meta(  $this->data[ 'license' ], '_edd_sl_user_id', true );
			$this->user = get_user_by( 'ID', $this->data[ 'user' ] );
		}
	}

	/**
	 * Save
	 */
	protected function save(){
		$this->ID  = $this->pod->save( $this->data );
	}

	/**
	 * Set Pods object
	 *
	 * @param $id
	 */
	protected function set_pod( $id ){
		$this->pod = pods( self::POD_NAME, $id );
	}

	/**
	 * Prepare data to be saved
	 *
	 * @param array $data
	 */
	protected function prepare_data( $data ){
		$details = pods_v_sanitized( 'details', $data, [], true );

		$this->data = [
			'datetime'  => current_time( 'mysql' ),
			'license'   => $this->get_license_id( $data[ 'license' ] ),
			'url'       => pods_v_sanitized( 'url', $data, null ),
			'wp_ver'    => pods_v_sanitized( 'wp_version', $details, 0 ),
			'ingot_ver' => pods_v_sanitized( 'ingot_version', $details, 0 ),
			'php_ver'   => pods_v_sanitized( 'php_version', $details, 0 ),
			'vertical'  => pods_v_sanitized( 'ing_vertical', $details, 0 ),
			'trial'     => boolval( pods_v( 'trial', $data, false, true ) )
		];


	}

	/**
	 * @param $license
	 *
	 * @return bool|null|string
	 */
	protected function get_license_id( $license ) {
		return \EDD_Software_Licensing::instance()->get_license_by_key( $license );
	}
}
