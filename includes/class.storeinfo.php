<?php

namespace MsgOk;

class StoreInfo {

	public static $installed = null;

	/**
	 * Call this method to get singleton
	 *
	 * @return StoreInfo
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
	 * Konstruktor
	 * @return void
	 */
	private function __construct()
	{
		$this->is_installed();
	}

	/**
	 * Načtení stavu instalace
	 * @return bool
	 */
	public static function is_installed()
	{
		if ( self::$installed == null )
		{
			$installed = get_option( 'msgok_installed' );
			self::$installed = !empty( $installed );
			return !empty( $installed );
		}

		return self::$installed;
	}

	/**
	 * Posbírání dat potřebných pro registraci
	 * @return array
	 */
	public static function collect_register_data()
	{
		// pomocna promenna
		$data = array();

		// email
		$current_user = wp_get_current_user();

		// URL eshopu
		$data['shop_url'] = home_url();
		$data['admin_email'] = $current_user->user_email;
		$data['address'] = false;

		// je woocommerce aktivní?
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) )
		{
			$data['address']['street'] = get_option( 'woocommerce_store_address' );
			$data['address']['street_2'] = get_option( 'woocommerce_store_address_2' );
			$data['address']['city'] = get_option( 'woocommerce_store_city' );
			$data['address']['zip'] = get_option( 'woocommerce_store_postcode' );
			$data['address']['country'] = get_option( 'woocommerce_default_country' );
		}

		return $data;
	}

	/**
	 * Navrácení seznamu jazyků
	 * @return array
	 */
	public static function get_available_languages()
	{
		// langs
		$langs = array();

		// wpml aktivní?
		$use_wp_langs = true;

		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )
		{
			$wpml_langs = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );

			if ( !empty( $wpml_langs ) )
			{
				foreach ( $wpml_langs as $lang )
				{
					$langs[$lang['code']] = $lang['native_name'];
				}

				$use_wp_langs = false;
			}
		}

		// polylang aktivní?
		if ( empty( $langs ) )
		{
			if ( is_plugin_active( 'polylang/polylang.php' ) )
			{
				$langs_all = pll_languages_list();

				if ( !empty( $langs_all ) )
				{
					foreach ( $langs_all as $lang )
					{
						$langs[$lang] = Utils::country_to_language( $lang );
					}
				}
			}
		}

		// načtení nainstalovaných jazyků ve WP
		if ( $use_wp_langs )
		{
			$core_langs = get_available_languages();

			if ( !empty( $core_langs ) )
			{
				foreach ( $core_langs as $lang )
				{
					$code = Utils::transform_locale_to_code( $lang );
					$lang_name = Utils::country_to_language( $code );
					$langs[$code] = $lang_name;
				}
			}
		}

		// navrátíme seznam jazyků
		return $langs;
	}

	/**
	 * Získání aktuálního jazyka
	 * @param bool verify_install_exists (ověřovat zdali mame pro daný jazyk aktivní instalaci?)
	 * @return string
	 */
	public static function get_current_language( $verify_install_exists = true )
	{
		// Fallback jazyk
		$language = 'cs';

		// Kontrola, zda je WPML plugin aktivní - potom bereme jazyk odsud
		if ( function_exists( 'icl_get_languages' ) )
		{
			$wpml_languages = icl_get_languages( 'skip_missing=0' );
			if ( !empty( $wpml_languages ) )
			{
				$current_language = $wpml_languages[ICL_LANGUAGE_CODE]['default_locale'];
				$current_language = Utils::transform_locale_to_code( $current_language );

				if ( !$verify_install_exists )
				{
					return $current_language;
				}

				if ( Install::get_language_data( $current_language ) )
				{
					return $current_language;
				}
			}

		}

		// Kontrola toho, zdali je POLYLANG plugin aktivní - potom bereme jazyk odsud
		if ( function_exists( 'pll_current_language' ) )
		{
			$curr_lang = pll_current_language();

			if ( !$verify_install_exists )
			{
				return $curr_lang;
			}

			if ( Install::get_language_data( $curr_lang ) )
			{
				return $curr_lang;
			}
		}

		// Zjištění jazyka aktuálně přihlášeného uživatele (pokud je přihlášen)
		if ( is_user_logged_in() )
		{
			$user_id = get_current_user_id();
			$user_locale = get_user_meta( $user_id, 'locale', true );
			if ( !empty( $user_locale ) )
			{
				$user_lang = Utils::transform_locale_to_code( $user_locale );

				if ( !$verify_install_exists )
				{
					return $user_lang;
				}

				if ( Install::get_language_data( $user_lang ) )
				{
					return $user_lang;
				}
			}
		}

		// Zjištění jazyka stránky
		$site_locale = get_locale();
		if ( !empty( $site_locale ) )
		{
			$site_locale_code = Utils::transform_locale_to_code( $site_locale );

			if ( !$verify_install_exists )
			{
				return $site_locale_code;
			}
			else if ( Install::get_language_data( $site_locale_code ) )
			{
				$language = $site_locale_code;
			}
		}

		return $language;
	}
}
StoreInfo::get_instance();
?>
