<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
{
	die;
}

// delete our options
$delete_options = array(
	'msgok_integrations',
	'msgok_installed',
	'msgok_installations'
);

foreach ( $delete_options as $option_name )
{
	delete_option( $option_name );
	delete_site_option( $option_name );
}
