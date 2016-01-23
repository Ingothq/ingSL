<?php
/**
 * Create free trial
 *
 * @package   ingotSL
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 Josh Pollock
 */

namespace ingSL;


class create {

	/**
	 * @var int
	 */
	const TRIAL_PRICE_ID = 1;

	/**
	 * @var \WP_User
	 */
	protected $user;

	/**
	 * License ID (license post)
	 *
	 * @var int
	 */
	protected $license;

	/**
	 * @param string $email
	 * @param string $url
	 */
	public function __construct( $email, $url ){
		$this->set_user( $email );
		if ( is_object( $this->user ) ) {
			$payment_id = $this->make_payment();
			if ( is_numeric( $payment_id ) ) {
				$this->make_license( $payment_id );
				affiliateWP::add_visit();
				$this->update_site( $url );
			}

		}

	}

	/**
	 * Get code for the license
	 *
	 * @return string
	 */
	public function get_license_code(){
		if( is_numeric( $this->license ) ){
			return \EDD_Software_Licensing::instance()->get_license_key( $this->license );
		}

	}

	/**
	 * Get license post ID
	 *
	 * @return int
	 */
	public function get_license_id(){
		return $this->license;
	}

	/**
	 * Get the user license was created for
	 *
	 * @return \WP_User
	 */
	public function get_user(){
		return $this->user;
	}

	protected  function set_user( $email ){
		$_user = new user( $email );
		$this->user = $_user->get_user();
	}

	/**
	 * Make a payment

	 *
	 * @return bool|int Payment ID if created succefully, false if not.
	 */
	protected  function make_payment( ) {
		/** @var array */
		global $edd_options;

		$data = array(
			'status' => 'publish',
			'tax' => 0,
			'first' => $this->user->first_name,
			'last' => $this->user->last_name,
			'downloads' => array()

		);

		$user_id 	= $this->user->ID;
		$email 		= $this->user->user_email;
		$user_first = sanitize_text_field( $data['first'] );
		$user_last = sanitize_text_field( $data['last'] );
		$user_info = array(
			'id' 			=> $user_id,
			'email' 		=> $email,
			'first_name'	=> $user_first,
			'last_name'		=> $user_last,
			'discount'		=> 'none'
		);

		$price = 0;

		$cart_details = array();
		$total = 0;
		$download = IngSL::TRIAL_ID;


		$cart_details[ $download]  = array(
			'name'        => get_the_title( $download ),
			'id'          => $download,
			'item_number' => $download,
			'price'       => $price,
			'subtotal'    => $price,
			'quantity'    => 1,
			'tax'         => 0,
			'price_id'=> self::TRIAL_PRICE_ID
		);

		$total = $price;

		$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

		if( strtotime( $date, time() ) > time() ) {
			$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		}

		$purchase_data     = array(
			'price'        => edd_sanitize_amount( $total ),
			'tax'          => 0,
			'post_date'    => $date,
			'purchase_key' => strtolower( md5( uniqid() ) ), // random key
			'user_email'   => $email,
			'user_info'    => $user_info,
			'currency'     => edd_get_currency(),
			'downloads'    => $data['downloads'],
			'cart_details' => $cart_details,
			'status'       => 'pending'
		);

		$payment_id = edd_insert_payment( $purchase_data );


		// increase stats and log earnings
		edd_update_payment_status( $payment_id, $data[ 'status' ] ) ;

		return $payment_id;
	}

	/**
	 * Set/create registered site and its site/license association
	 *
	 * @param string $url
	 *
	 * @return int
	 */
	protected function update_site( $url ){
		$site = new site( $url, $this->user, $this->license );
		$site_id = $site->save();
		return $site_id;
	}

	/**
	 * Generate free trial license code
	 *
	 * @param int $payment_id
	 */
	public function make_license($payment_id ){
		$cart_item['item_number']['options']['price_id'] = self::TRIAL_PRICE_ID;
		$keys = \EDD_Software_Licensing::instance()->generate_license( IngSL::TRIAL_ID, $payment_id, 'default', $cart_item );
		$this->license = pods_v( 0, $keys, 0 );
		update_post_meta( $this->license, '	_ingsl_is_trial', true );
	}

}