<?php

namespace MsgOk;

class Admin {

	/**
	 * Call this method to get singleton
	 *
	 * @return Admin
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
		add_action( 'admin_menu', array( $this, 'settings_item' ), 30 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_static_files') );
	}

	/**
	 * Vytvoření admin menu
	 * @return void
	 */
	public function settings_item()
	{
		add_menu_page( 'MessageOk', 'MessageOk', 'edit_others_posts', MSGOK_ADMIN_SLUG, array( $this, 'init_view' ), 'dashicons-randomize' );
		add_action( 'admin_init', array( $this, 'init_post_actions' ) );
	}

	/**
	 * Admin static styles
	 * @return void
	 */
	public function admin_static_files()
	{
		wp_enqueue_style( 'msgok-plugin-admin-styles', MSGOK_DIR_URL . 'static/css/msgok.css', array(), MSGOK_VERSION );
		wp_register_script( 'msgok-plugin-admin-scripts', MSGOK_DIR_URL . 'static/js/msgok.js', array( 'jquery' ), MSGOK_VERSION, true );
		wp_enqueue_script( 'msgok-plugin-admin-scripts' );
	}

	/**
	 * Post AKCE
	 * @return void
	 */
	public function init_post_actions()
	{
		// INSTALACE
		if ( isset( $_POST['msgok_install_sent'] ) )
		{
			$lang = sanitize_text_field( $_POST['lang'] );
			$email = sanitize_text_field( $_POST['email'] );
			$install_result = Install::install_language( $lang, $email );

			if ( $install_result === true )
			{
				wp_redirect( admin_url( 'options-general.php?page=' . MSGOK_ADMIN_SLUG . '&installed=1' ) );
				exit;
			}
		}

		// INSTALACE DALSICH JAZYKU
		if ( isset( $_POST['msgok_install_another_language'] ) )
		{
			$lang = sanitize_text_field( $_POST['lang'] );
			$install_result = Install::install_language( $lang );

			if ( $install_result === true )
			{
				wp_redirect( admin_url( 'options-general.php?page=' . MSGOK_ADMIN_SLUG . '&installed=1' ) );
				exit;
			}
		}

		// ULOŽENÍ NASTAVENÍ INTEGRACÍ
		if ( isset( $_POST['msgok_save_integrations'] ) )
		{
			$integrations = Utils::recursive_sanitize_text_field( $_POST['integrations'] );
			$save_integrations = array();

			foreach ( $integrations as $i_key => $i_val )
			{
				$i_key = sanitize_text_field( $i_key );

				if ( $i_key == 'order_number' && $i_val['select'] == '__order_id__' ) {
					$save_integrations[$i_key] = array(
						'type' => 'order_id',
						'value' => ''
					);

					continue;
				}

				$type = ( $i_val['select'] == '__custom__' ? 'custom' : 'select' );

				if ( $type == 'select' ) 
				{
					if ( is_array( $i_val['select'] ) )
					{
						$save_integrations[$i_key] = array(
							'type' => $type,
							'value' => sanitize_text_field( implode( ';', $i_val['select'] ) )
						);
					}
					else
					{
						$save_integrations[$i_key] = array(
							'type' => $type,
							'value' => sanitize_text_field( $i_val['select'] )
						);
					}
				}
				else
				{
					$save_integrations[$i_key] = array(
						'type' => $type,
						'value' => sanitize_text_field( $i_val['custom'] )
					);
				}
			}

			// uložení
			update_option( 'msgok_integrations', $save_integrations );
		}
	}

	/**
	 * Router - hlavní pohled.. tady rozhodneme co budeme zobrazovat
	 * @return void
	 */
	public function init_view()
	{
		$installed = StoreInfo::is_installed();

		if ( !$installed )
		{
			require_once( MSGOK_DIR_PATH . 'views/install.php' );
		}
		else
		{
			$langs = Install::get_installed_languages();

			// načtení informací z API
			$any_not_fully_installed = false;

			if ( !empty( $langs ) )
			{
				foreach ( $langs as $lang_key => $lang )
				{
					$res = Install::update_language_data( $lang_key );

					if ( $res['live']['install'] < 100 )
					{
						$any_not_fully_installed = true;
					}

					$langs[$lang_key]['data'] = $res;
				}
			}

			// vykreslení stránky
			if ( $any_not_fully_installed )
			{
				require_once( MSGOK_DIR_PATH . 'views/settings.php' );
			}
			else
			{
				require_once( MSGOK_DIR_PATH . 'views/main.php' );
			}
		}
	}

}
Admin::get_instance();
?>
