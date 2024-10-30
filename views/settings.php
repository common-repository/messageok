<?php
	// Instalace
	foreach ( $langs as $lang )
	{
		if ( !$lang['settings_ok'] )
		{
			$lang_now = $lang;
			break;
		}
	}
?>
<div class="wrap">

	<h2><?php _e( 'Instalace byla úspěšná', 'messageok' ); ?></h2>

	<div class="msgok-install">
		<img src="<?php echo MSGOK_DIR_URL; ?>/static/images/logo.svg">

		<p>
			<?php _e( 'Založili jsme Vám účet v aplikaci MessageOk.com', 'messageok' ); ?>
		</p>

		<p>
			<?php _e( 'Je nutné provést prvotní nastavení scénářů.', 'messageok' ); ?>
		</p>

		<p class="msgok-mt-sm">
			<a href="javascript:msgok_openSettings('<?php esc_attr_e( $lang_now['data']['live']['install_link'] ); ?>');" class="msgok-button msgok-button-fill">
				<?php _e( 'Nastavit aplikaci', 'messageok' ); ?> →
			</a>
		</p>
	</div>
</div>