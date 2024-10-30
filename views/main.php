<?php
	namespace MsgOk;
?>
<div class="wrap">

	<div class="msgok-logo">
		<img src="<?php echo MSGOK_DIR_URL; ?>/static/images/logo.svg">
	</div>

	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php foreach ( $langs as $lang_key => $lang ) : ?>
			<a href="#msgok-lang-<?php esc_attr_e( $lang_key ); ?>" class="nav-tab msgok-nav-bar <?php echo ( $lang['is_first'] ? 'nav-tab-active' : '' ); ?>">
				<?php esc_html_e( $lang['lang_name'] ); ?>
			</a>
		<?php endforeach; ?>

		<a href="#msgok-lang-add" class="nav-tab msgok-nav-bar">
			<?php _e( '+ Přidat jazyk', 'messageok' ); ?>
		</a>
	</nav>

	<?php foreach ( $langs as $lang_key => $lang ) : ?>
		<div id="msgok-lang-<?php esc_attr_e( $lang_key ); ?>" class="msgok-lang-wrap" <?php if ( !$lang['is_first'] ) : ?>style="display: none;"<?php endif; ?>>
			<h2><?php esc_html_e( $lang['lang_name'] ); ?></h2>

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'API endpoint', 'messageok' ); ?></th>
						<td>
							<p>
								<code><?php esc_html_e( Api::get_api_url( $lang['lang_key'], $lang['api_hash'] ) ); ?></code>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Vložení do stránky', 'messageok' ); ?></th>
						<td>
							<p>
								<a href="javascript:msgok_openSettings('<?php esc_attr_e( $lang['data']['live']['widget_link'] ); ?>')" class="button button-primary">
									<?php _e( 'Nastavit umístění', 'messageok' ); ?>
								</a>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Správa scénářů', 'messageok' ); ?></th>
						<td>
							<?php if ( !empty( $lang['data']['live']['apps'] ) ) : ?>
								<table class="table widefat msgok-table">
									<?php foreach ( $lang['data']['live']['apps'] as $app ) : ?>
										<tr>
											<td>
												<?php esc_html_e( $app['description'] ); ?>
											</td>

											<td>
												<a href="javascript:msgok_openScennary('<?php esc_attr_e( $lang['data']['live']['editor_link'] ); ?>', '<?php esc_attr_e( $app['id'] ); ?>_<?php esc_attr_e( $app['name'] ); ?>');"><?php _e( 'Upravit', 'messageok' ); ?></a>
											</td>
										</tr>
									<?php endforeach; ?>
								</table>
							<?php else: ?>
								<p>
									<?php _e( 'Zatím nemáte žádné scénáře.', 'messageok' ); ?>
								</p>
							<?php endif; ?>

							<p>
								<a href="<?php echo esc_url( $lang['login_link'] ); ?>" class="button button-primary" target="_blank">
									<?php _e( 'Otevřít na webu MessageOk', 'messageok' ); ?> <small class="dashicons dashicons-external"></small>
								</a>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php endforeach; ?>

	<!-- Novy jazyk -->

	<div id="msgok-lang-add" class="msgok-lang-wrap" style="display: none;">
		<h2>
			<?php _e( 'Instalace nového jazyka', 'messageok' ); ?>
		</h2>

		<form method="post">
			<p>
				<label>
					<input type="checkbox" name="conditions" value="1" required> <?php _e( 'Souhlasím s předáním dat společnosti <b>MessageOk s.r.o.</b> a jejich <a href="https://messageok.com/vop.pdf" target="_blank">Obchodními podmínkami</a> a <a href="https://messageok.com/os.pdf" target="_blank">Zásadami ochrany osobních údajů</a>.', 'messageok' ); ?>
				</label>
			</p>

			<p class="msgok-mt-sm">
				<label><?php _e( 'Zvolte další jazyk instalace:', 'messageok' ); ?></label>
			</p>

			<p>
				<select name="lang">
					<?php
						$langs = \MsgOk\StoreInfo::get_available_languages();
						foreach ( $langs as $lang_code => $lang_name )
						{
							$is_installed = \MsgOk\Install::get_language_data( $lang_code );

							printf( '<option value="%s" %s>%s</option>',
								esc_attr( $lang_code ),
								( $is_installed ? 'disabled' : '' ),
								esc_html( $lang_name )
							);
						}
					?>
				</select>
			</p>

			<p class="msgok-mt-sm">
				<input type="submit" class="msgok-button msgok-button-fill" value="<?php _e( 'Propojit aplikaci', 'messageok' ); ?>">
			</p>

			<input type="hidden" name="msgok_install_another_language" value="1">
		</form>

		<p>&nbsp;</p>
	</div>

	<!-- Propojení dat -->
	<?php
		require_once( MSGOK_DIR_PATH . 'views/integrations.php' );
	?>
</div>
