<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Iwe_Settings_Tab class
 *
 */
class Iwe_Settings_Tab {

	public function __construct() {
		$this->id = 'iwe_wc_admin_tab';
		$this->label = __('Invoicing', 'inv-eco');
		add_filter('woocommerce_settings_tabs_array', array( $this, 'add_tab' ), 200);
		add_action('woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action('woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action('woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_style' ) );
	}

	/**
	 * payment gateways
	 *
	 * @var array
	 */
	protected static $pay_gateways = array();

	/**
	 * customer select options
	 *
	 * @var array
	 */
	protected $customer_options = array();

	/**
	 * invoiceline select options
	 *
	 * @var array
	 */
	protected $invoiceline_options = array();

	/**
	 * paymentmethod select options
	 *
	 * @var array
	 */
	protected $layout_options = array();

	/**
	 * payment terms select options
	 *
	 * @var array
	 */
	protected $payment_terms_options = array();

	/**
	 * payment term selects
	 *
	 * @var array
	 */
	protected $payment_term_selects = array();

	/**
	 * payment method notice
	 *
	 * @var string
	 */
	protected $payment_method_notice;

	/**
	 * Get select options
	 *
	 */
	public function get_select_options() {
		if ( get_option( Iwe::$option_key . '_tokens_valid' ) ){
			self::get_customer_options();
			self::get_invoiceline_options();
			self::get_layout_options();
			$this->get_payment_terms_options();
		} else {
			$error_message = __('Invalid tokens', 'inv-eco' );
			$this->customer_options['error'] = $error_message;
			$this->invoiceline_options['error'] = $error_message;
			$this->layout_options['error'] = $error_message;
			$this->payment_terms_options['error'] = $error_message;
		}
	}

	/**
	 * Get e-conomic products
	 *
	 */
	public function get_customer_options() {
		$customers = Iwe_HTTP::get( 'customers?pagesize=1000' )->collection;
		$this->customer_options[ -1 ] = __( 'Select customer', 'inv-eco' );
		foreach( $customers as $customer ) {
			$this->customer_options[ $customer->customerNumber ] = $customer->name;
		}
	}

	/**
	 * Get e-conomic products
	 *
	 */
	public function get_invoiceline_options() {
		$products = Iwe_HTTP::get( 'products?pagesize=1000' )->collection;
		$this->invoiceline_options[ -1 ] = __( 'Select product', 'inv-eco' );
		foreach( $products as $product ) {
			$this->invoiceline_options[ $product->productNumber ] = $product->name;
		}
	}

	/**
	 * Get e-conomic layouts
	 *
	 */
	public function get_layout_options() {
		$layouts = Iwe_HTTP::get( 'layouts' )->collection;
		$this->layout_options[ -1 ] = __( 'Select layout', 'inv-eco' );
		foreach( $layouts as $layout ) {
			$this->layout_options[ $layout->layoutNumber ] = $layout->name;
		}
	}

	/**
	 * get e-conomic payment terms
	 *
	 */
	public function get_payment_terms_options() {
		$terms = Iwe_HTTP::get( 'payment-terms' )->collection;
		$this->payment_terms_options[ -1 ] = __( 'Select payment method', 'inv-eco' );
		foreach( $terms as $term ) {
			$this->payment_terms_options[ $term->paymentTermsNumber ] = $term->name;
		}
	}

	/**
	 * get woocommerce gateways
	 *
	 * @return array
	 */
	public static function get_gateways() {
		if ( function_exists( 'WC' ) ) {
			self::$pay_gateways = [];
			$all_gateways = WC()->payment_gateways->get_available_payment_gateways();
			if( $all_gateways ) {
				foreach( $all_gateways as $gateway ) {
					if( ( ! in_array( $gateway->id, self::$pay_gateways ) ) && ( $gateway->enabled == 'yes' ) ) {
						self::$pay_gateways[] = $gateway->id;
					}
				}
			} else {
				self::$pay_gateways = [];
			}
			return self::$pay_gateways;
		} else {
			return [];
		}
	}

	/**
	 * get woocommerce payment methods
	 *
	 * @return array
	 */
	public function init_selects() {
		if ( class_exists( 'WC_Payment_Gateways' ) ) {
			$gateways = self::get_gateways();
			if ( count( $gateways ) == 0 ) {
				$this->payment_method_notice = __( 'No payment methods found, please setup and activate a payment method in Woocommerce settings', 'inv-eco' );
			} else {
				$this->payment_method_notice = '';
			}
			$gateways_instance = WC_Payment_Gateways::instance();
			foreach( $gateways as $gateway_id ) {
				$this->payment_term_selects[] = array(
					'type'     => 'select',
					'id'       => Iwe::$option_key . '_select_payment_term_' . $gateway_id,
					'name'     => $gateways_instance->payment_gateways()[$gateway_id]->title,
					'options'  => $this->payment_terms_options,
					'class'    => 'wc-enhanced-select',
					'default'  => ''
				);
			}
		} else {
			$this->payment_term_selects = [];
		}
	}

	/**
	 * Register style for wc settings page
	 *
	 */
	public function register_style() {
		wp_register_style( 'settings-tab-style', IWE_PLUGIN_URL . '/css/settings-tab.css' );
		wp_enqueue_style( 'settings-tab-style' );
	}

	/**
	 * Add tab
	 *
	 */
	public function add_tab($settings_tabs) {
		$settings_tabs[$this->id] = $this->label;
		return $settings_tabs;
	}

	/**
	 * Get sections
	 *
	 */
	public function get_sections() {
		$sections = array(
			'' => __('Setup', 'perfect-woocommerce-brands'),
			'tokens' => __('Tokens', 'perfect-woocommerce-brands'),
		);
		return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
	}

	/**
	 * Output sections
	 *
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if (empty($sections) || 1 === sizeof($sections)) {
		  return;
		}

		echo '<ul class="subsubsub">';
		$array_keys = array_keys($sections);
		foreach ($sections as $id => $label) {
			echo '<li><a href="' . admin_url('admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title($id)) . '" class="' . ($current_section == $id ? 'current' : '') . '">' . $label . '</a> ' . (end($array_keys) == $id ? '' : '|') . ' </li>';
		}
		echo '</ul><br class="clear" />';
	}

	/**
	 * Get settings
	 *
	 */
	public function get_settings($current_section = '') {
		if ('' == $current_section)  {
			$this->get_select_options();
			$this->init_selects();
			$arr = array(
				array(
					'name' => __( 'Dedicated e-conomic customer', 'inv-eco' ),
					'type' => 'title',
					'desc' => __( 'Dedicated e-conomic customer', 'inv-eco' ),
					'id'   => 'Iwe_customer_options',
				),
				array(
					'type'     => 'select',
					'id'       => Iwe::$option_key . '_select_customer',
					'name'     => __( 'Customer', 'inv-eco' ),
					'options'  => $this->customer_options,
					'class'    => 'wc-enhanced-select',
					'default'  => '',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'Iwe_customer_options'
				),

				array(
					'name' => __( 'Dedicated e-conomic product', 'inv-eco' ),
					'type' => 'title',
					'desc' => __( 'The woocommerce order total is placed as 1 invoice line ( using this product ) on the e-conomic invoice draft.', 'inv-eco' ),
					'id'   => 'Iwe_product_options',
				),
				array(
					'type'     => 'select',
					'id'       => Iwe::$option_key . '_select_productline',
					'name'     => __( 'Product', 'inv-eco' ),
					'options'  => $this->invoiceline_options,
					'class'    => 'wc-enhanced-select',
					'default'  => '',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'Iwe_product_options'
				),
				
				array(
					'name' => __( 'e-conomic invoice layout', 'inv-eco' ),
					'type' => 'title',
					'desc' => 'Choose which e-conomic invoice layout is used for the invoce draft',
					'id'   => 'iwe_payment_options',
				),
				array(
					'type'     => 'select',
					'id'       => Iwe::$option_key . '_select_layout',
					'name'     => __( 'Layout', 'inv-eco' ),
					'options'  => $this->layout_options,
					'class'    => 'wc-enhanced-select',
					'default'  => ''
				));

				$arr[] = array(
					'type' => 'sectionend',
					'id'   => 'iwe_payment_options'
				);

				$arr[] = array(
					'name' => __( 'Match woocommerce payment methods with e-conomic payment methods', 'inv-eco' ),
					'type' => 'title',
					'desc' => $this->payment_method_notice,
					'id'   => 'iwe_payment_term_options',
				);

				$arr = array_merge( $arr, $this->payment_term_selects );
				$this->payment_term_selects = [];

				$arr[] = array(
					'type' => 'sectionend',
					'id'   => 'iwe_payment_term_options'
				);
			$settings = apply_filters( 'iwe_wc_admin_tab_settings', $arr );

		} elseif ('tokens' == $current_section) {
			if ( get_option( Iwe::$option_key . '_tokens_valid' ) ) {
				 $class = 'valid-token';
			} else {
				 $class = 'invalid-token';
			}
			$settings = apply_filters( 'iwe_wc_admin_tab_tokens_settings', array(
				array(
					'name' => __( 'Tokens', 'inv-eco' ),
					'type' => 'title',
					'desc' => '',
					'id'   => 'Iwe_general_options',
				),
				array(
					'type'     => 'text',
					'id'       => Iwe::$option_key . '_input_secret_token',
					'name'     => __( 'Secret token', 'inv-eco' ),
					'desc'     => __( '', 'inv-eco' ),
					'class'    => $class,
					'default'  => '',
				),
				array(
					'type'     => 'text',
					'id'       => Iwe::$option_key . '_input_grant_token',
					'name'     => __( 'Agreement grant token', 'inv-eco' ),
					'desc'     => __( '', 'inv-eco' ),
					'class'    => $class,
					'default'  => '',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'Iwe_general_options'
				),
			) );
		}
		return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);
	}

	/**
	 * output
	 *
	 */
	public function output() {
		global $current_section;
		$settings = $this->get_settings($current_section);
		WC_Admin_Settings::output_fields($settings);
	}

	/**
	 * save
	 *
	 */
	public function save() {
		global $current_section;
		$settings = $this->get_settings( $current_section );
		if ( 'tokens' == $current_section ) {
			$old_tokens = array(
				'secret' => get_option(Iwe::$option_key . '_input_secret_token'),
				'grant' => get_option(Iwe::$option_key . '_input_grant_token')
			);
		}			
		WC_Admin_Settings::save_fields( $settings );
		if ( 'tokens' == $current_section ) {
			if ( ( get_option( Iwe::$option_key . '_input_secret_token') != $old_tokens['secret'] ) || ( get_option( Iwe::$option_key . '_input_grant_token') != $old_tokens['grant'] ) ) {
				$response = Iwe_HTTP::validate_tokens( [ get_option( Iwe::$option_key . '_input_secret_token'), get_option( Iwe::$option_key . '_input_grant_token') ] );
				if ( isset( $response->apiName ) ) {
					update_option( Iwe::$option_key . '_tokens_valid', true );
				} else {
					update_option( Iwe::$option_key . '_tokens_valid', false );
				}
			}
		}
	}
}