<?php

/**
 * Plugin Name: MessageOk
 * Plugin URI: https://www.messageok.com/woocommerce
 * Description: MessageOk plugin for WooCommerce
 * Version: 2.0.6
 * Author: MessageOk
 * Author URI: https://www.messageok.com
 * Domain Path: /languages
 * License: GPL2
 * WC requires at least: 5
 * WC tested up to: 8.7
 */

/*
Messageok is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
Messageok is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with . If not, see http://www.gnu.org/licenses/gpl-2.0.html
*/

namespace MsgOk;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Init {

	/**
	 * Call this method to get singleton
	 *
	 * @return Init
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
		$this->defines();
		$this->require_classes();

		register_activation_hook( __FILE__, array( $this, 'plugin_activated' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivated' ) );

		add_action( 'init', array( $this, 'load_translations' ) );

		// High-Performance-Order-Storage
		add_action( 'before_woocommerce_init', function() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		} );
	}

	/**
	 * Definice
	 */
	private function defines()
	{
		define( 'MSGOK_VERSION', '1.0.0' );
		define( 'MSGOK_DIR_URL', plugin_dir_url( __FILE__ ) );
		define( 'MSGOK_DIR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'MSGOK_BASENAME', plugin_basename( __FILE__ ) );
		define( 'MSGOK_ADMIN_SLUG', 'messageok' );
	}

	/**
	 * Třídy
	 */
	private function require_classes()
    {
		require_once( 'includes/class.utils.php' );
		require_once( 'includes/class.storeinfo.php' );
		require_once( 'includes/class.install.php' );
        require_once( 'includes/class.admin.php' );
        require_once( 'includes/class.integrations.php' );
        require_once( 'includes/class.inject.php' );
        require_once( 'includes/class.shortcode.php' );
        require_once( 'includes/class.api.php' );
    }

	/**
	 * Odchycení aktivace pluginu
	 * @return void
	 */
	public function plugin_activated()
	{
		Install::plugin_activated();
	}

	/**
	 * Odchycení deaktivace pluginu
	 * @return void
	 */
	public function plugin_deactivated()
	{
		Install::plugin_deactivated();
	}

	/**
	 * Načtení překladů
	 * @return void
	 */

	public function load_translations()
	{
		load_plugin_textdomain( 'messageok', false, basename( __DIR__ ) . '/languages/' );

	}
}

Init::get_instance();
