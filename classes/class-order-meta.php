<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Iwe_Order_Meta
 */
class Iwe_Order_Meta {

	function __construct() {
		add_filter( 'woocommerce_checkout_fields', array( $this , 'register_eco_checkout_field' ) );
		add_action( 'woocommerce_checkout_after_customer_details' , array( $this, 'add_eco_checkout_field' ) );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'save_eco_checkout_fields' ), 10, 2 );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'display_order_data_in_admin' ) );
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'eco_add_order_new_column_header' ), 20);
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'eco_add_wc_order_admin_list_column_content' ) );
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'eco_sync_bulk_actions_edit_product' ), 20, 1 );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'eco_sync_handle_bulk_action_edit_shop_order' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'eco_sync_bulk_action_admin_notice' ) );
	}

	/**
	 * register_eco_checkout_field
	 *
	 * @return array
	 */
	public static function register_eco_checkout_field($fields)	{
		$fields['eco_field'] = array(
				'eco_field' => array(
					'type' => 'text',
					'required'      => true,
					'label' => __( 'Status : ', 'inv-eco' )
					)
		);
		return $fields;
	}

	/**
	 * add_eco_checkout_field
	 * 
	 * @return void
	 */
	public static function add_eco_checkout_field()	{
		$checkout = WC()->checkout(); ?>
		<div class="eco-field" style="display: none;">
			<?php woocommerce_form_field( 'eco_field', 'eco_field', __( 'Not sent', 'inv-eco' ) ); ?>
		</div>
	<?php }

	/**
	 * save_eco_checkout_fields
	 *
	 * @return void
	 */
	public static function save_eco_checkout_fields( $order, $data ) {
		if( isset( $data['eco_field'] ) )
		{
			$order->update_meta_data( '_eco_field', sanitize_text_field( $data['eco_field'] ) );
		}
	}

	/**
	 * display_order_data_in_admin
	 *
	 * @return void
	 */
	public static function display_order_data_in_admin( $order ) {
		if ( $order->get_meta( '_eco_field' ) )
		{
			$status = $order->get_meta( '_eco_field' );
		} else
		{
			$status = __( 'Not sent', 'inv-eco' );
		}
	?>
		<div class="order_data_column" style="width: 120px;">
			<h4><?php _e( 'e-conomic', 'inv-eco' ); ?></h4>
			<?php 
				echo '<p><strong>' . __( 'Status', 'inv-eco' ) . ':</strong>&nbsp;&nbsp;&nbsp;' . $status . '</p>';
			?>
		</div>
	<?php
	}

	/**
	 * eco_add_order_new_column_header
	 *
	 * @return array
	 */
	public static function eco_add_order_new_column_header( $columns ) {
		$new_columns = array();
		foreach ( $columns as $column_name => $column_info ) {
			$new_columns[ $column_name ] = $column_info;
			if ( 'order_total' === $column_name ) {
				$new_columns['order_details'] = __( 'e-conomic', 'inv-eco' );
			}
		}
		return $new_columns;
	}

	/**
	 * eco_add_wc_order_admin_list_column_content
	 *
	 * @return void
	 */
	public static function eco_add_wc_order_admin_list_column_content( $column ) {
		global $post;
		if ( 'order_details' === $column )
		{
			$order = wc_get_order( $post->ID );
			if ( $order->get_meta( '_eco_field' ) )
			{
				echo $order->get_meta( '_eco_field' );
			}
			else
			{
				echo __( 'Not sent', 'inv-eco' );
			}
		}
	}

	/**
	 * eco_sync_bulk_actions_edit_product
	 *
	 * @return array
	 */
	public static function eco_sync_bulk_actions_edit_product( $actions ) {
		if ( ( get_option( Iwe::$option_key . '_select_customer' ) ) && ( get_option( Iwe::$option_key . '_select_customer' ) != -1 ) 
		&& ( get_option( Iwe::$option_key . '_select_productline' ) ) && ( get_option( Iwe::$option_key . '_select_productline' ) != -1 )
		&& ( get_option( Iwe::$option_key . '_select_layout' ) ) && ( get_option( Iwe::$option_key . '_select_layout' ) != -1 ) 
		&& ( get_option( Iwe::$option_key . '_tokens_valid' ) == true ) ) {
			$gateways = Iwe_Settings_Tab::get_gateways();
			if ( count( $gateways ) == 0 ) {
				return $actions;
			} else {
				$gateways_instance = WC_Payment_Gateways::instance();
				$gateways_ok = 0;
				foreach( $gateways as $gateway_id ) {
					if ( ( get_option( Iwe::$option_key . '_select_payment_term_' . $gateway_id ) ) && ( get_option( Iwe::$option_key . '_select_payment_term_' . $gateway_id ) != -1 ) ) {
						$gateways_ok++;
					}
				}
				if ( $gateways_ok != count( $gateways ) ) {
					return $actions;
				}
			}
			$actions['eco_sync'] = __( 'Send to e-conomic', 'inv-eco' );
		}
		return $actions;
	}

	/**
	 * eco_sync_handle_bulk_action_edit_shop_order
	 *
	 * @return array
	 */
	public static function eco_sync_handle_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ) {
		if ( $action !== 'eco_sync' )
			return $redirect_to; // Exit

		$processed_ids = array();
		foreach ( $post_ids as $post_id ) {
			$order = wc_get_order( $post_id );
			$order_data = $order->get_data();
			$response = Iwe_HTTP::post_invoice_draft( $post_id );
			if ( isset( $response->draftInvoiceNumber ) ) {
				$order->update_meta_data( '_eco_field', __( 'Sent', 'inv-eco' ) );
			} else {
				// ERROR
			}
			$order->save();
			$processed_ids[] = $post_id;
		}
		$redirect_to = add_query_arg( array(
			'eco_sync' => '1',
			'processed_count' => count( $processed_ids ),
			'processed_ids' => implode( ',', $processed_ids ),
		), $redirect_to );
		return $redirect_to;
	}

	/**
	 * eco_sync_bulk_action_admin_notice
	 *
	 * @return void
	 */
	public static function eco_sync_bulk_action_admin_notice() {
		if ( empty( $_REQUEST['eco_sync'] ) ) return; // Exit

		$count = intval( $_REQUEST['processed_count'] );

		printf( '<div id="message" class="updated fade"><p>' .
			_n( 'Behandlet %s Ordre.',
			'Behandlet %s Ordrer.',
			$count,
			'eco_sync'
		) . '</p></div>', $count );
	}
}