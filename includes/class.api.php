<?php

namespace MsgOk;

use Automattic\WooCommerce\Utilities\OrderUtil;

class Api {

	const MSGOK_INSTALL_URL = 'https://app.messageok.com/connect/wordpress/install.php';
	const MSGOK_USER_URL = 'https://app.messageok.com/connect/wordpress/user.php';

	/**
	 * Call this method to get singleton
	 *
	 * @return Api
	 */
	public static function get_instance()
	{
		static $instance = null;

		if ( $instance === null ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Private construct so nobody else can instance it
	 *
	 */
	private function __construct()
	{
		// api endpoint
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// nastavení user key
		add_action( 'init', array( $this, 'set_user_key' ), 20 );

		// nonce
		add_action( 'wp_footer', array( $this, 'footer_scripts' ), 30 );
	}

	/**
	 * Poskládání API URL
	 * @param string lang_code
	 * @param string api_hash
	 * @return string
	 */
	public static function get_api_url( $lang_code, $api_hash )
	{
		return sprintf( '%s/wp-json/messageok/%s/%s',
			home_url(),
			$lang_code,
			$api_hash
		);
	}

	/**
	 * Zaregistrování API URL
	 * @return void
	 */
	public function register_rest_routes()
	{
		$languages = Install::get_installed_languages();

		if ( !empty( $languages ) )
		{
			foreach ( $languages as $lang )
			{
				register_rest_route( 'messageok', sprintf( '/%s/%s', $lang['lang_key'], $lang['api_hash'] ), array(
					'methods' => array( 'GET', 'POST' ),
					'callback' => array( $this, 'process_rest_api' ),
				) );

				register_rest_route( 'messageok', sprintf( '/%s/%s/order', $lang['lang_key'], $lang['api_hash'] ), array(
					'methods' => array( 'GET', 'POST' ),
					'callback' => array( $this, 'process_rest_api' ),
				) );
			}
		}
	}

	/**
	 * Nastavení uživatelského klíče
	 * @return void
	 */
	public function set_user_key()
	{
		$current_user = wp_get_current_user();
		$user_key = get_user_meta( $current_user->ID, 'messageok_key', true );
		if ( empty( $user_key ) )
		{
			update_user_meta( $current_user->ID, 'messageok_key', wp_generate_password( 24, false ) );
		}
	}

	/**
	 * Získání uživatelského klíče
	 * @return string
	 */
	public function get_user_key()
	{
		$current_user = wp_get_current_user();
		return get_user_meta( $current_user->ID, 'messageok_key', true );
	}

	/**
	 * Získání ID uživatele podle klíče
	 * @param string key
	 * @return int
	 */
	public function get_userid_by_key( $key )
	{
		$user = reset(
			get_users(
				array(
					'meta_key' => 'messageok_key',
					'meta_value' => $key,
					'number' => 1,
					'count_total' => false
				)
			)
		);

		return ( $user->ID );
	}

	/**
	 * Nonce + samotny script do patičky
	 * @return void
	 */
	public function footer_scripts()
	{
		?>
		<script>var msgok_wp_nonce = "<?php esc_attr_e( $this->get_user_key() ); ?>";</script>


		<?php
	}

	/**
	 * Get tracking by postmeta field
	 * @param int order_id
	 * @return array
	 */
	public function get_tracking_url( $order_id )
	{
		$return = array();
		
		$meta_keys = Integrations::get_integration_meta_field( 'delivery' );
		if ( !empty( $meta_keys ) )
		{
			// HPOS
			if ( OrderUtil::custom_orders_table_usage_is_enabled() )
			{
				$order_object = wc_get_order( $order_id );

				if ( !$order_object ) return $return;

				foreach ( $meta_keys as $meta_key )
				{
					$found_value = $order_object->get_meta( $meta_key );
					$return[$meta_key] = $found_value;
				}
			}
			else
			{
				foreach ( $meta_keys as $meta_key )
				{
					$found_value = get_post_meta( $order_id, $meta_key, TRUE );
					$return[$meta_key] = $found_value;
				}
			}
		}

		return $return;
	}

	/**
	 * Get invoice URL
	 * @param int order_id
	 * @return array
	 */
	public function get_invoice_url( $order_id )
	{
		$return = array();

		$meta_keys = Integrations::get_integration_meta_field( 'invoice' );
		if ( !empty( $meta_keys ) )
		{
			// HPOS
			if ( OrderUtil::custom_orders_table_usage_is_enabled() )
			{
				$order_object = wc_get_order( $order_id );

				if ( !$order_object ) return $return;

				foreach ( $meta_keys as $meta_key )
				{
					$found_value = $order_object->get_meta( $meta_key );
					$return[$meta_key] = $found_value;
				}
			}
			else
			{
				foreach ( $meta_keys as $meta_key )
				{
					$found_value = get_post_meta( $order_id, $meta_key, TRUE );
					$return[$meta_key] = $found_value;
				}
			}
		}

		return $return;
	}

	/**
	 * Získání ID objednávky - pokud má uživatel přenastavené získávání ID objednávky pomocí nějakého postmeta klíče
	 * @param mixed order_id
	 * @return int
	 */
	public function get_real_order_id( $order_id )
	{
		$meta_key = Integrations::get_integration_meta_field( 'order_number' );

		if ( !empty( $meta_key ) )
		{
			$meta_key = $meta_key[0];
			$order_id = esc_sql( sanitize_text_field( $order_id ) );

			// HPOS
			if ( OrderUtil::custom_orders_table_usage_is_enabled() )
			{
				global $wpdb;
				$loaded_order_id = $wpdb->get_var( $wpdb->prepare( "
					SELECT order_id
					FROM {$wpdb->prefix}wc_orders_meta
					WHERE meta_key = %s
					AND meta_value = %s
				", $meta_key, $order_id ) );
			}
			else
			{
				// Legacy (post type) storage
				global $wpdb;
				$loaded_order_id = $wpdb->get_var( $wpdb->prepare( "
					SELECT post_id
					FROM {$wpdb->postmeta}
					WHERE meta_key = %s
					AND meta_value = %s
				", $meta_key, $order_id ) );
			}

			if ( !empty( $loaded_order_id ) )
			{
				return $loaded_order_id;
			}
			else
			{
				// premyslim, jestli to nebude lepší stopnout a navrátit nulové ID
				// aby to někdo nemohl enumerovat či tak
				// ale to je na probrání s lukášem později
			}
		}

		return $order_id;
	}

	/**
	 * Získání všech postmeta
	 * @param int order_id
	 * @return array
	 */
	public function get_all_postmeta( $order_id )
	{
		$meta = array();

		// HPOS
		if ( OrderUtil::custom_orders_table_usage_is_enabled() )
		{
			$order = wc_get_order( $order_id );
			$metadata = $order->get_meta_data();

			if ( !empty( $metadata ) )
			{
				foreach ( $metadata as $meta_item )
				{
					$val = $meta_item->value;

					if ( is_serialized( $val ) )
					{
						$val = unserialize( $val );
					}

					$meta[$meta_item->key] = $val;
				}
			}
		}
		else
		{
			// Legacy (post type) storage
			$loaded_metafields = get_post_meta( $order_id );

			if ( !empty( $loaded_metafields ) )
			{
				foreach ( $loaded_metafields as $key => $val )
				{
					$val = $val[0];
	
					if ( is_serialized( $val ) )
					{
						$val = unserialize( $val );
					}
	
					$meta[$key] = $val;
				}
			}
		}

		return $meta;
	}

	/**
	 * Zpracování API requestu
	 * @return void
	 */
	public function process_rest_api()
	{
		global $woocommerce;

		if ( isset( $_GET['ordernumber'] ) )
		{
			$order_number = $this->get_real_order_id( $_GET['ordernumber'] );

			$order_json = array();
			$order = wc_get_order( $order_number );
			if ( !empty( $order ) )
			{
				$real_order_email = $order->get_billing_email();
				$obtained_email = ( isset( $_GET['email'] ) ? sanitize_text_field( $_GET['email'] ) : '' );

				// CUSTOMER
				$order_json['order_info']['email'] = $real_order_email;
				$order_json['order_info']['order_number'] = $order->get_id();

				if ( $obtained_email == $real_order_email )
				{
					// ALL POST FIELDS
					$order_json['fields'] = $this->get_all_postmeta( $order->get_id() );

					// CUSTOMER DATA
					$order_json['order_info']['customer']['first_name'] = $order->get_billing_first_name();
					$order_json['order_info']['customer']['last_name'] = $order->get_billing_last_name();
					$order_json['order_info']['customer']['email'] = $real_order_email;
					$order_json['order_info']['customer']['adress'] = $order->get_billing_address_1();
					$order_json['order_info']['customer']['city'] = $order->get_billing_city();
					$order_json['order_info']['customer']['zip'] = $order->get_billing_postcode();
					$order_json['order_info']['customer']['country'] = $order->get_billing_country();
					$order_json['order_info']['customer']['complete_shipping_adress'] = $order->get_address();
					$order_json['order_info']['customer']['map'] = $order->get_shipping_address_map_url();
				}

				// ORDER INFO
				$order_json['order_info']['order']['created_at'] = $order->get_date_created()->getTimestamp();

				if ( !$order->get_date_completed() == null )
				{
					$order_json['order_info']['order']['payed_at'] = $order->get_date_completed()->getTimestamp();
					$order_json['order_info']['order']['payed_at_friendly'] = date( 'd. m. Y', $order->get_date_completed()->getTimestamp() );
					$datediff = time() - $order->get_date_completed()->getTimestamp();
					$order_json['order_info']['order']['days_from_payed'] = round( $datediff / ( 60 * 60 * 24 ) );
				}

				$order_json['order_info']['order']['shipping'] = $order->get_shipping_method();
				$order_json['order_info']['order']['status'] = $order->get_status();
				$order_json['order_info']['order']['payment_method'] = $order->get_payment_method_title();
				$order_json['order_info']['order']['total'] = $order->get_total();
				$order_json['order_info']['order']['currency'] = $order->get_currency();

				// TRACKING AND DELIVERY
				$order_json['order_info']['order']['tracking_number'] = $this->get_tracking_url( $order->get_id() );
				$order_json['order_info']['order']['invoice_url'] = $this->get_invoice_url( $order->get_id() );

				// INVOICE URL - woocommerce-pdf-invoices-packing-slips
				if ( is_plugin_active( 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' ) )
				{
					$load_meta = get_option( 'wpo_wcpdf_settings_debug' );

					if ( !empty( $load_meta ) )
					{
						if ( isset( $load_meta['document_link_access_type'] ) && ( $load_meta['document_link_access_type'] == 'guest' || $load_meta['document_link_access_type'] == 'full' ) )
						{
							$pdf_url = add_query_arg( array(
								'action'        => 'generate_wpo_wcpdf',
								'document_type' => 'invoice',
								'order_ids'     => $order->get_id(),
								'order_key'     => $order->get_order_key(),
							), admin_url( 'admin-ajax.php' ) );

							$order_json['order_info']['order']['invoice_url_generated'] = $pdf_url;
						}
					}
				}

				if ( $obtained_email == $real_order_email )
				{
					// ITEMS
					$items_arr = $order->get_items();
					$items_arr = array_unique( $items_arr );

					foreach ( $items_arr as $item_id => $item )
					{
						$item_data = $item_data = $item->get_data();
						$arr = array(
							'name' => $item_data['name'],
							'variation_id' => $item_data['variation_id'],
							'quantity' => $item_data['quantity'],
							'total' => $item_data['total'],
							'thumb_url' => get_the_post_thumbnail_url( $item_data['product_id'], 'post-thumbnail' ),
						);
						$order_json['order_info']['order']['items'][] = $arr;
					}
				}

			} else {
				$error = 3;
			}

			if ( empty( $error ) )
			{
				echo( wp_json_encode( $order_json, true ) );
				exit;

			} else {
				$this->do_error( $error );
			}
		}

		// Suggest
		if ( isset( $_GET['suggest'] ) )
		{
			$return = array();
			$type = ( isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '' );

			if ( $type == 'status' )
			{
				// seznam stavů objednávek
				$return = array();
				$statuses = wc_get_order_statuses();

				foreach ( $statuses as $status_key => $status_val )
				{
					$return[] = array(
						'id' => $status_key,
						'title' => $status_val
					);
				}

			}
			else if ( $type == 'shipping' )
			{
				// seznam metod doručení
				$shipping_methods = WC()->shipping->get_shipping_methods();

				if ( !empty( $shipping_methods ) )
				{
					foreach ( $shipping_methods as $shipping_method )
					{
						$return[] = array(
							'id' => $shipping_method->id,
							'title' => $shipping_method->method_title
						);
					}
				}

			}
			else if ( $type == 'payment' )
			{
				// seznam metod plateb
				$payment_gateways = WC()->payment_gateways->get_available_payment_gateways();

				if ( !empty( $payment_gateways ) )
				{
					foreach ( $payment_gateways as $payment_gateway )
					{
						$return[] = array(
							'id' => $payment_gateway->id,
							'title' => $payment_gateway->method_title
						);
					}
				}
			}

			header( 'Content-Type: application/json' );
			echo( wp_json_encode( $return, true ) );
			exit;
		}

		// PRIHLASENY UZIVATEL - seznam jeho objednávek
		if ( isset( $_GET['nonce'] ) )
		{
			$nonce = sanitize_text_field( $_GET['nonce'] );
			$userID = $this->get_userid_by_key( $nonce );

			if ( $userID )
			{
				$customer_orders = wc_get_orders( array(
					'meta_key' => '_customer_user',
					'meta_value' => $userID,
					'numberposts' => -1
				) );

				$orders = array();
				if ( !empty( $customer_orders ) )
				{
					foreach ( $customer_orders as $order )
					{
						$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;

						if ( $order_id )
						{
							global $woocommerce;
							$order = wc_get_order( $order_id );

							$single_order = array(
								'order_number' => $order_id,
								'created_at' => $order->get_date_created()->getTimestamp(),
								'payment_method' => $order->get_payment_method_title(),
								'total' => $order->get_total(),
								'currency' => $order->get_currency()
							);

							$orders[] = $single_order;

						}
						else
						{
							$error = 4;
						}
					}
				}

			}
			else
			{
				$error = 6;
			}

			if ( empty( $error ) )
			{
				echo( wp_json_encode( $orders, true ) );
				exit;

			}
			else
			{
				$this->do_error( $error );
			}
		}

		if ( isset( $_GET['updateorder'] ) )
		{
			// TODO doresit a zjistit funčknost
			$order_number = $this->get_real_order_id( $_GET['updateorder'] );

			$verify = sanitize_text_field( $_GET['verify'] );
			$verifyemail = sanitize_text_field( $_GET['verifyemail'] );
			$final_status = sanitize_text_field( $_GET['finalstatus'] );

			if ( !empty( $order_number ) )
			{
				if ( !empty( $verify ) )
				{
					$order = wc_get_order( $order_number );

					if ( $order->get_customer_id() !== $this->get_userid_by_key( $verify ) )
					{
						$this->do_error( 2 );
					}

					if ( !empty( $final_status ) )
					{
						if ( !empty( $order ) )
						{
							$current_status = $order->get_status();
							if ( $final_status == 'cancelled' )
							{
								$note = sprintf( __( 'Zákazník změnil stav objednávky z %s na %s pomocí MessageOk', 'messageok' ), wc_get_order_status_name( $current_status ), wc_get_order_status_name( $final_status ) );
								$order->update_status( $final_status );
								$order->add_order_note( $note );
								echo '{"status": "updated"}';
								exit;

							}
							else
							{
								$this->do_error( 7 );
							}

						}
						else
						{
							$this->do_error( 1 );
						}

					}
					else
					{
						$this->do_error( 7 );
					}
				}
				else if ( !empty( $verifyemail ) )
				{
					$order = wc_get_order( $order_number );
					$real_order_email = $order->get_billing_email();

					$current_status = $order->get_status();
					if ( $real_order_email == $verifyemail )
					{
						$note = sprintf( __( 'Zákazník změnil stav objednávky z %s na %s pomocí MessageOk', 'messageok' ), wc_get_order_status_name( $current_status ), wc_get_order_status_name( $final_status ) );
						$order->update_status( $final_status );
						$order->add_order_note( $note );

						echo '{"status": "updated"}';
						exit;
					}
					else
					{
						$this->do_error( 2 );
					}

				}
				else
				{
					$this->do_error( 2 );
				}

			}
			else
			{
				$this->do_error( 1 );
			}
		}

		// Uložení poznámky k objednávce
		if ( isset( $_GET['addnote'] ) )
		{
			$order_number = $this->get_real_order_id( $_GET['addnote'] );
			$verifyemail = sanitize_text_field( $_GET['verifyemail'] );
			$note = sanitize_text_field( $_GET['note'] );

			if ( !empty( $verifyemail ) )
			{
				$order = wc_get_order( $order_number ); // TODO práce s číslem objednávky?
				$real_order_email = $order->get_billing_email();

				if ( $real_order_email == $verifyemail )
				{
					$order->add_order_note( $note );
					echo '{"status": "updated"}';
					exit;
				}
				else
				{
					$this->do_error( 4 );
				}
			}
			else
			{
				$this->do_error( 4 );
			}

		}
	}

	/**
	 * Vytvoření chyby
	 * @return void
	 */
	public function do_error( $err_code )
	{
		$error_messages = array(
			1 => 'Empty order number',
			2 => 'Empty or wrong user nonce',
			3 => 'Wrong or non-exist order number',
			4 => 'Your email address is not associated with order.',
			6 => 'This user have not order yet',
			7 => 'Final status is not set or allowed'
		);

		$err_status = array();
		$err_status['error']['code'] = $err_code;
		$err_status['error']['message'] = $error_messages[$err_code];

		echo wp_json_encode( $err_status );
		exit;
	}

}
Api::get_instance();
?>
