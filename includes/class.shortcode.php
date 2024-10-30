<?php

namespace MsgOk;

class Shortcode {

	/**
	 * Call this method to get singleton
	 *
	 * @return Shortcode
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
	private function __construct() {
		add_shortcode( 'messageok', array( $this, 'shortcode_callback' ) );
	}

	/**
	 * Shortcode callback for [messageok lang="cz"]
	 *
	 * @param array $atts Attributes passed to the shortcode
	 * @return string The output of the shortcode
	 */
	public function shortcode_callback( $atts ) {
		// zjištění jazyka
		$atts = shortcode_atts( array(
			'id' => '',
		), $atts, 'messageok' );

		// vypsání kódu
		return sprintf( '<div id="msgok%s"></div>', esc_attr( $atts['id'] ) );
	}

}
Shortcode::get_instance();
?>
