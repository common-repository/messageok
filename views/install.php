<div class="wrap">

	<h2><?php _e( 'Instalace', 'messageok' ); ?></h2>

	<?php
		global $msgok_install_error;
		if ( isset( $msgok_install_error ) && !empty( $msgok_install_error ) )
		{
			printf( '<div class="error">%s</div>', esc_html( $msgok_install_error ) );
		}
	?>

	<div class="msgok-install">
		<img src="<?php echo MSGOK_DIR_URL; ?>/static/images/logo.svg">

		<form method="post">
			<p>
				<label>
					<input type="checkbox" name="conditions" value="1" required> <?php _e( 'Souhlasím s předáním dat společnosti <b>MessageOk s.r.o.</b> a jejich <a href="https://messageok.com/vop.pdf" target="_blank">Obchodními podmínkami</a> a <a href="https://messageok.com/os.pdf" target="_blank">Zásadami ochrany osobních údajů</a>.', 'messageok' ); ?>
				</label>
			</p>

			<p class="msgok-mt-sm">
				<label><?php _e( 'Zvolte jazyk první instalace:', 'messageok' ); ?></label>
			</p>

			<p>
				<select name="lang">
					<?php
						$langs = \MsgOk\StoreInfo::get_available_languages();
						foreach ( $langs as $lang_code => $lang_name )
						{
							printf( '<option value="%s">%s</option>',
								esc_attr( $lang_code ),
								esc_html( $lang_name )
							);
						}
					?>
				</select>
			</p>

			<p>
				<small><?php _e( 'Později budete moci založit MessageOk i pro další jazyky.', 'messageok' ); ?></small>
			</p>

			<p class="msgok-mt-sm">
				<label><?php _e( 'E-mail pro vytvoření účtu na MessageOk (pokud již máte účet, zadejte zde stejný email, jako máte na MessageOk.com):', 'messageok' ); ?></label>
			</p>

			<p>
				<?php
					$current_user = wp_get_current_user();
				?>
				<input type="email" name="email" value="<?php esc_attr_e( $current_user->user_email ); ?>" size="35" required>
			</p>

			<p class="msgok-mt-sm">
				<input type="submit" class="msgok-button msgok-button-fill" value="<?php _e( 'Propojit aplikaci', 'messageok' ); ?>">
			</p>

			<p class="msgok-mt-md">
				<?php _e( 'Aplikaci budou předány následující údaje:', 'messageok' ); ?>
			</p>

			<ul class="msgok-ul">
				<li><?php _e( 'aktuální adresa webu', 'messageok' ); ?></li>
				<li><?php _e( 'seznam aktivních jazyků', 'messageok' ); ?></li>
				<li><?php _e( 'kontaktní údaje administrátora', 'messageok' ); ?></li>
			</ul>

			<input type="hidden" name="msgok_install_sent" value="1">
		</form>
	</div>

</div>