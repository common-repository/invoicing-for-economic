<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Iwe_HTTP' ) ) {

	/**
	 * Iwe_HTTP class
	 *
	 */
	class Iwe_HTTP {

		/**
		 * e-conomic base url
		 *
		 * @var string
		 */
		public static $eco_base_url = 'https://restapi.e-conomic.com/';

		/**
		 * get_secret_token
		 *
		 * @return void
		 */
		public static function get_secret_token() {
			return get_option(Iwe::$option_key . '_input_secret_token');
		}

		/**
		 * get_grant_token
		 *
		 * @return void
		 */
		public static function get_grant_token() {
			return get_option(Iwe::$option_key . '_input_grant_token');
		}

		/**
		 * validate_tokens
		 *
		 * @return object
		 */
		public static function validate_tokens( $tokens ) {
			$args = array(
				'headers' => array(
					'X-AppSecretToken' => $tokens[0],
					'X-AgreementGrantToken' => $tokens[1],
					'Content-Type' => 'application/json'
				)
			);
			$response = wp_remote_get( self::$eco_base_url, $args );
			return json_decode( wp_remote_retrieve_body ( $response ) );
		}

		/**
		 * post_invoice_draft
		 *
		 * @return object
		 */
		public static function post_invoice_draft( $order_id ) {
			$order = new Iwe_Order( $order_id );
			$order_info = $order->get_order_info();
			$eco_productnumber = get_option( Iwe::$option_key . '_select_productline' );
			$data = array(
				"date" => $order_info['order_date'],
				"currency" => $order_info['currency'],
				"paymentTerms" => array(
					"paymentTermsNumber" => 1
				),
				"customer" => array(
					"customerNumber" => $order_info['customer_number']
				),
				"recipient" => array(
					"name" => __( 'Webshop customer', 'inv-eco' ) . " ( " . $order_info['customer_name'] . " )",
					"address" => "",
					"zip" => "",
					"city" => "",
					"vatZone" => array(
						"name" => "Domestic",
						"vatZoneNumber" => 1,
						"enabledForCustomer" => true,
						"enabledForSupplier" => true
					)
				),
				"references" => array(
					"other" => $order_info['order_number']
				),
				"layout" => array(
					"layoutNumber" => $order_info['layout']
				),
				"lines" => [ array(
					"unit" => array(
						"unitNumber" => 1,
						"name" => ""
					),
					"product" => array(
						"productNumber" => $eco_productnumber
					),
					"quantity" => 1,
					"unitNetPrice" => $order_info['order_total'],
					"discountPercentage" => 0,
					"description" => __( 'Webshop order', 'inv-eco' )
				) ]
			);
			$response = self::post( 'invoices/drafts', $data );
			return $response;
		}

		/**
		 * get
		 *
		 * @return object
		 */
		public static function get( $endpoint ) {
			$args = array(
				'headers' => array(
					'X-AppSecretToken' => self::get_secret_token(),
					'X-AgreementGrantToken' => self::get_grant_token(),
					'Content-Type' => 'application/json'
				)
			);
			$response = wp_remote_get( self::$eco_base_url . $endpoint, $args );
			return json_decode( wp_remote_retrieve_body ( $response ) );
		}

		/**
		 * post
		 *
		 * @return object
		 */
		public static function post( $endpoint, $body ) {
			$headers = array(
				'X-AppSecretToken' => self::get_secret_token(),
				'X-AgreementGrantToken' => self::get_grant_token(),
				'Content-Type' => 'application/json'
			);
			$args = array(
				'body'        => json_encode($body),
				'timeout'     => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => $headers,
				'cookies'     => array(),
			);
			$response = wp_remote_post( self::$eco_base_url . $endpoint, $args );
			return json_decode( wp_remote_retrieve_body ( $response ) );
		}
	}
}