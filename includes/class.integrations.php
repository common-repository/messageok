<?php

namespace MsgOk;

use Automattic\WooCommerce\Utilities\OrderUtil;

class Integrations {

	private $api_endpoint;

	/**
	 * Call this method to get singleton
	 *
	 * @return Integrations
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
	 * Načtení všech možných postmeta z posledních objednávek
	 * @return array
	 */
	public static function get_available_postmeta()
	{
		$meta_found = array();

		// woocommerce funkce musi existovat
		if ( function_exists( 'wc_get_order_statuses' ) )
		{
			if ( OrderUtil::custom_orders_table_usage_is_enabled() )
			{
				$last_orders = wc_get_orders( array(
					'posts_per_page' => 50,
					'status' => array_keys( wc_get_order_statuses() ),
					'order' => 'DESC',
					'orderby' => 'date'
				) );	
				
				if ( !empty( $last_orders ) )
				{
					foreach ( $last_orders as $order )
					{
						$post_meta_all_raw = $order->get_meta_data();
						
						if ( !empty( $post_meta_all_raw ) )
						{
							$post_meta_all = [];
							foreach ( $post_meta_all_raw as $post_meta_all_raw_item )
							{
								$post_meta_all[$post_meta_all_raw_item->key] = $post_meta_all_raw_item->value;
							}

							foreach ( $post_meta_all as $post_meta_key => $post_meta )
							{
								$search_index = array_search( $post_meta_key, $meta_found );

								if ( !$search_index )
								{
									$meta_found[$post_meta_key] = $post_meta;

								} else if ( empty( $meta_found[$search_index] ) && !empty( $post_meta[0] ) ) {
									$meta_found[$post_meta_key] = $post_meta;
								}
							}
						}
					}
				}
			} 
			else
			{
				$last_orders = new \WP_Query( array(
					'post_type' => 'shop_order',
					'posts_per_page' => 50,
					'post_status' => array_keys( wc_get_order_statuses() ),
					'order' => 'DESC',
					'orderby' => 'post_date',
					'fields' => 'ids'
				) );

				if ( $last_orders->have_posts() )
				{
					foreach ( $last_orders->posts as $post_id )
					{
						$post_meta_all = get_post_meta( $post_id );

						if ( !empty( $post_meta_all ) )
						{
							foreach ( $post_meta_all as $post_meta_key => $post_meta )
							{
								$search_index = array_search( $post_meta_key, $meta_found );

								if ( !$search_index )
								{
									$meta_found[$post_meta_key] = $post_meta[0];

								} else if ( empty( $meta_found[$search_index] ) && !empty( $post_meta[0] ) ) {
									$meta_found[$post_meta_key] = $post_meta[0];
								}
							}
						}
					}
				}
			}
		}

		return $meta_found;
	}

	/**
	 * Načtení defaultně zjištěných integrací (jejich postmeta)
	 * @return array
	 */
	public static function find_integrations()
	{
		// seznam integrací
		$integrations = array(
			'order_number' => array( 'type' => 'order_id', 'value' => '' ), // vychozi se načte ID objednávky
			'invoice' => array( 'type' => 'custom', 'value' => '' ),
			'delivery' => array( 'type' => 'custom', 'value' => '' )
		);

		// ORDER NUMBER
		if ( is_plugin_active( 'wt-woocommerce-sequential-order-numbers/wt-advanced-order-number.php' ) ) 
		{
			$integrations['order_number'] = array( 'type' => 'select', 'value' => '_order_number' );
		}

		// SUPERFAKTURA
		if ( is_plugin_active( 'woocommerce-superfaktura/woocommerce-superfaktura.php' ) ) 
		{
			$integrations['invoice'] = array( 'type' => 'select', 'value' => 'wc_sf_invoice_regular' );
		}

		return $integrations;
	}

	/**
	 * Uložení defaultních integrací
	 * @return array
	 */
	public static function set_default_integrations()
	{
		$integrations = self::find_integrations();
		update_option( 'msgok_integrations', $integrations );
		return $integrations;
	}

	/**
	 * Aktuální seznam integrací
	 * @return array
	 */
	public static function get_integrations()
	{
		// načteme seznam integrací a jejich stav
		$integrations = get_option( 'msgok_integrations' );
		
		if ( !is_array( $integrations ) )
		{
			$integrations = self::set_default_integrations();
		}

		// projdeme integrace a zjistíme jejich stav propojení (automatický)
		foreach ( $integrations as $key => $val )
		{
			$status = ( !empty( $val['value'] ) ? true : false );

			if ( $key == 'order_number' && $val['type'] == 'order_id' )
			{
				$status = true;
			}

			$integrations[$key]['status'] = $status;
		}

		return $integrations;
	}

	/**
	 * Meta políčko pro danou integraci
	 * @param string integration
	 * @return array
	 */
	public static function get_integration_meta_field( $integration )
	{
		$integrations = self::get_integrations();

		if ( !isset( $integrations[$integration] ) )
		{
			return array();
		}

		return explode( ';', $integrations[$integration]['value'] );
	}
}
Integrations::get_instance();
?>
