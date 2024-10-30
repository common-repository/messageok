<?php

namespace MsgOk;

class Install {

	/**
	 * Call this method to get singleton
	 *
	 * @return Install
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
	public function __construct()
	{
		// add_action( 'activated_plugin', array( $this, 'redirect_after_activation' ) );
	}

	/**
	 * Plugin byl aktivován
	 * @return void
	 */
	public static function plugin_activated()
	{
		// výchozí integrace
		$integrations = get_option( 'msgok_integrations' );

		if ( empty( $integrations ) )
		{
			Integrations::set_default_integrations();
		}
	}

	/**
	 * Plugin byl deaktivován
	 * @return void
	 */
	public static function plugin_deactivated()
	{

	}

	/**
	 * Přesměrování po aktivaci pluginu
	 * @return void
	 */
	public function redirect_after_activation( $plugin )
	{
		if ( $plugin == MSGOK_BASENAME )
		{
			exit( wp_redirect( admin_url( 'options-general.php?page=' . MSGOK_ADMIN_SLUG ) ) );
		}
	}

	/**
	 * Uložení metadat o instalaci
	 * @param string language
	 * @param array data
	 * @return void
	 */
	public static function save_language_installation( $language, $data )
	{
		// nastavíme plugin obecně jako nainstalovaný
		update_option( 'msgok_installed', time() );

		// aktuální data
		$now = get_option( 'msgok_installations' );
		if ( empty( $now ) )
		{
			$now = array();

	 	} else {
			$now = json_decode( $now, true );
		}

		// data k uložení
		$now[$language] = $data;

		// uložíme
		update_option( 'msgok_installations', wp_json_encode( $now ) );
	}

	/**
	 * Instalace pro nový jazyk
	 * @param string lang
	 * @param string email
	 * @return bool
	 */
	public static function install_language( $lang, $email = '' )
	{
		// vytvoření API endpoint hashe pro URL
		$api_hash = wp_generate_password( 20, false, false );

		// install meta data
		$install_data = StoreInfo::collect_register_data();
		$install_data['lang'] = $lang;
		$install_data['api_url'] = Api::get_api_url( $lang, $api_hash );
		$install_data['api_hash'] = $api_hash;

		// emailová adresa
		if ( !empty( $email ) )
		{
			$install_data['user_email'] = $email;
		}
		else
		{
			//die( "<pre>" . print_r( self::get_installed_languages() , true ) );
			$install_data['user_email'] = $install_data['admin_email'];
		}

		// post request
		$api_request = Utils::json_request( Api::MSGOK_INSTALL_URL, $install_data );

		if ( !$api_request )
		{
			global $msgok_install_error;
			$msgok_install_error = __( 'Chyba při komunikaci s instalačním serverem. Zkuste to prosím znovu později.', 'messageok' );
			return false;

		} else {
			// ověříme návratová data
			$response = json_decode( $api_request, true );

			if ( $response['install'] == 'OK' && $response['result'] && empty( $response['error'] ) )
			{
				// uložení metadat do nastavení
				self::save_language_installation( $lang, array(
					'login_link' => $response['login_link'],
					'script_url' => $response['script_url'],
					'installed_at' => time(),
					'install_user_id' => get_current_user_id(),
					'api_hash' => $api_hash
				) );

				// dokončeno
				return true;

			} else {
				// chyba
				global $msgok_install_error;
				$msgok_install_error = __( 'Chyba při zakládání účtu v MessageOk. Kontaktujte nás prosím na info@messageok.com', 'messageok' );
				return false;
			}
		}
	}

	/**
	 * Seznam nainstalovaných jazyků
	 * @return array
	 */
	public static function get_installed_languages()
	{
		$installed_langs = json_decode( get_option( 'msgok_installations' ), true );

		if ( empty( $installed_langs ) )
		{
			return [];

			// chyba - nejspíše se ztratily vazby, plugin se musí přeinstalovat
			delete_option( 'msgok_installed' );
			delete_option( 'msgok_installations' );

			wp_redirect( admin_url( 'options-general.php?page=' . MSGOK_ADMIN_SLUG ) );
			exit;
		}

		// k nalezeným jazykům přidáme další informace (prozatím jen název jazyka)
		$i = 0;
		$return = array();

		foreach ( $installed_langs as $lang_key => $val )
		{
			$return[$lang_key] = array(
				'is_first' => $i == 0,
				'lang_key' => $lang_key,
				'lang_name' => Utils::country_to_language( $lang_key ),
				'login_link' => ( isset( $val['login_link'] ) ? $val['login_link'] : '' ),
				'script_url' => ( isset( $val['script_url'] ) ? $val['script_url'] : '' ),
				'install_date' => ( isset( $val['installed_at'] ) ? $val['installed_at'] : '' ),
				'install_user' => ( isset( $val['install_user_id'] ) ? $val['install_user_id'] : '' ),
				'api_hash' => ( isset( $val['api_hash'] ) ? $val['api_hash'] : '' )
			);

			$i++;
		}

		return $return;
	}

	/**
	 * Načtení informací pro požadovaný nainstalovaný jazyk
	 * @param string language
	 * @return array|bool
	 */
	public static function get_language_data( $language )
	{
		$langs = self::get_installed_languages();
		$language = strtolower( $language );

		if ( isset( $langs[$language] ) )
		{
			return $langs[$language];
		}

		return false;
	}

	/**
	 * Aktualizace dat z MSGOK
	 * @param string lang_id
	 * @return array
	 */
	public static function update_language_data( $language )
	{
		// lang data now
		$lang_data_now = self::get_language_data( $language );

		// request meta data
		$verify_data = [
			'lang' => $language,
			'api_hash' => $lang_data_now['api_hash']
		];

		// post request
		$api_response = json_decode( Utils::json_request( Api::MSGOK_USER_URL, $verify_data ), TRUE );

		// aktualizace stavu instalace
		$lang_data_now['login_link'] = $api_response['login_link'];
		$lang_data_now['live'] = $api_response;

		self::save_language_installation( $language, $lang_data_now );

		// return
		return $lang_data_now;
	}
}
Install::get_instance();
?>