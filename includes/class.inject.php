<?php

namespace MsgOk;

class Inject {

	/**
	 * Call this method to get singleton
	 *
	 * @return Inject
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
	 * Constructor
	 */
	private function __construct() 
	{
		add_action( 'wp_footer', array( $this, 'inject_js_to_footer' ) );
	}

	/**
	 * Přidání JS kódu do patičky
	 * @return void
	 */
	public function inject_js_to_footer()
	{
		// určení jazyka a získání správné URL scriptu
		$lang_data = Install::get_language_data( StoreInfo::get_current_language() );

		if ( !$lang_data )
		{
			return;
		}

		// vypsání JS
		wp_enqueue_script( 'messageok-injector', $lang_data['script_url'], array(), false, array( 'in_footer' => true ) );
	} 
}
Inject::get_instance();
?>
