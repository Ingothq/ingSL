<?php
/**
 * Record or retrieve a registered site
 *
 * @package   ingSL
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 Josh Pollock
 */

namespace ingSL;


class site {

	/**
	 * Name of Pod
	 */
	const POD_NAME = 'site';

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
	 * @param null|int|string $identifier Site ID or URL
	 * @param \WP_User|null $user User
	 * @param bool|false $license License ID for querying site by
	 */
	public function __construct( $identifier = null, \WP_User $user = null, $license = false ){
		$this->set_pod( $identifier, $license );
		$this->user = $user;

		$url = null;
		if( filter_var( $identifier, FILTER_VALIDATE_URL ) ){
			$url = esc_url_raw( $identifier );
		}

		$this->set_data( $url, $license );

	}

	/**
	 * Save site
	 *
	 * @return int ID of saved site
	 */
	public function save(){
		$this->ID = $this->pod->save( $this->data, null, $this->ID );
		return $this->ID;
	}

	/**
	 * Set up Pods object
	 *
	 * @param int|string|null $identifier
	 */
	private function set_pod( $identifier, $license ) {
		if ( is_numeric( $identifier ) ) {
			$this->pod = pods( self::POD_NAME, $identifier, true );
		} elseif ( filter_var( $identifier, FILTER_VALIDATE_URL ) ) {
			$params[ 'where' ] = sprintf( 't.url = "%s"', esc_url_raw( $identifier ) );
			if( is_numeric( $license ) ) {
				$params[ 'where' ] .= sprintf(' OR license.ID = "%d"', $license );
			}
			$this->pod         = pods( self::POD_NAME, $params, true );
		} else {
			$this->pod = pods( self::POD_NAME );
		}


	}

	/**
	 * Prepare data to save
	 *
	 * @param string $url
	 * @param string $license
	 */
	protected function set_data( $url, $license ){
		if( is_null( $url ) ){
			$this->url = $this->find_url();
		}

		$this->data = [
			'url' => $url,
			'user' => $this->user_id(),
			'license' => (int) $license
		];

	}

	protected function find_url(){
		return null;
	}

	/**
	 * Get URL
	 *
	 * @return int
	 */
	protected function user_id(){
		if( is_null( $this->user ) ){
			return 0;
		}

		return $this->user->ID;
	}


}
