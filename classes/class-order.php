<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Iwe_Order class
 */
class Iwe_Order {

	/**
	 * __construct
	 *
	 * @return void
	 */
	function __construct( $order_id ) {
		$this->order = wc_get_order( $order_id );
	}

	/**
	 * order
	 *
	 * @var object
	 */
	private $order;

	/**
	 * get_order_tax_rate
	 *
	 * @return integer
	 */
	private function get_order_tax_rate() {
		$taxes = $this->order->get_items('tax');
		foreach( $taxes as $tax ) {
			return intval( $tax->get_rate_percent() );
		}
	}

	/**
	 * get_order_info
	 *
	 * @return array
	 */
	public function get_order_info() {
		return array(
			'order_number' => $this->order->get_order_number(),
			'customer_number' => intval( get_option( Iwe::$option_key . '_select_customer' ) ),
			'order_date' => $this->order->get_date_created()->format ('Y-m-d'),
			'order_total' => round( floatval( number_format((float)$this->order->get_total() * ( 100 / ( 100 + $this->get_order_tax_rate() ) ), 2, '.', '') ), 2 ),
			'customer_name' => $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name(),
			'currency' => $this->order->get_currency(),
			'layout' => intval( get_option( Iwe::$option_key . '_select_layout' ) )
		);
	}
}