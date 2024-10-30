<?php
	namespace MsgOk;
?>
<h2><?php _e( 'Propojení dat', 'messageok' ); ?></h2>

<p>
	<?php _e( 'Některá rozšíření se nám podaří propojit automaticky. U něčeho potřebujeme Vaši pomoc. Více naleznete v <a href="https://messageok.com/info/kategorie/zaciname/" target="_blank">nápovědě.</a>', 'messageok' ); ?>
</p>

<form method="post">
	<table class="form-table">
		<thead>
			<tr>
				<th><?php _e( 'Funkce', 'messageok' ); ?></th>
				<th><?php _e( 'Stav propojení', 'messageok' ); ?></th>
				<th><?php _e( 'Výběr hodnoty', 'messageok' ); ?></th>
				<th><?php _e( 'Ruční nastavení (pokročilé)', 'messageok' ); ?></th>
			</tr>
		</thead>

		<?php
			$available_postmeta = Integrations::get_available_postmeta();
			$integrations = Integrations::get_integrations();
		?>
		<tbody>
			<tr>
				<th scope="row">
					<?php _e( 'Číslo objednávky', 'messageok' ); ?>
				</th>

				<td style="color: green;">
					<?php if ( $integrations['order_number']['status'] ) : ?>
						<span style="color: green;"><span class="dashicons dashicons-yes"></span> <?php _e( 'nastaveno', 'messageok' ); ?></span>
					<?php else: ?>
						<span style="color: red;"><span class="dashicons dashicons-no"></span> <?php _e( 'nenastaveno', 'messageok' ); ?></span>
					<?php endif; ?>
				</td>

				<td>
					<select name="integrations[order_number][select]" data-type="order_number" class="msgok-integrations-select">
						<option value="__order_id__" <?php if ( empty( $integrations['order_number']['value'] ) ) : ?>selected<?php endif; ?>><?php _e( 'ID objednávky', 'messageok' ); ?></option>
						<option value="__custom__" <?php if ( $integrations['order_number']['type'] == 'custom' && !empty( $integrations['order_number']['value'] ) ) : ?>selected<?php endif; ?>><?php _e( '(vyplnit vlastní)', 'messageok' ); ?></option>

						<?php
							foreach ( $available_postmeta as $k => $v ) {
								printf( '<option value="%s" %s>%s (%s: %s)</option>',
									$k,
									( $k == $integrations['order_number']['value'] ? 'selected' : '' ),
									$k,
									__( 'např.', 'messageok' ),
									( is_serialized( $v ) ? __( '(serializovaná data)', 'messageok' ) : esc_attr( $v ) )
								);
							}
						?>
					</select>
				</td>

				<td id="msgok_i_order_number" <?php if ( $integrations['order_number']['type'] != 'custom' || empty( $integrations['order_number']['value'] ) ) : ?>style="display: none;"<?php endif; ?>>
					<input name="integrations[order_number][custom]" value="<?php esc_attr_e( $integrations['order_number']['value'] ); ?>" class="regular-text">
				</td>
			</tr>

			<tr>
				<th scope="row">
					<?php _e( 'Sledování zásilek', 'messageok' ); ?>
				</th>

				<td style="color: green;">
					<?php if ( $integrations['delivery']['status'] ) : ?>
						<span style="color: green;"><span class="dashicons dashicons-yes"></span> <?php _e( 'nastaveno', 'messageok' ); ?></span>
					<?php else: ?>
						<span style="color: red;"><span class="dashicons dashicons-no"></span> <?php _e( 'nenastaveno', 'messageok' ); ?></span>
					<?php endif; ?>
				</td>

				<td>
					<select name="integrations[delivery][select][]" data-type="delivery" class="msgok-integrations-select" multiple>
						<option value="" <?php if ( empty( $integrations['delivery']['value'] ) ) : ?>selected<?php endif; ?>></option>
						<option value="__custom__" <?php if ( $integrations['delivery']['type'] == 'custom' && !empty( $integrations['delivery']['value'] ) ) : ?>selected<?php endif; ?>>(vyplnit vlastní)</option>

						<?php
							$active_values = explode( ';', $integrations['delivery']['value'] );
							foreach ( $available_postmeta as $k => $v ) {
								printf( '<option value="%s" %s>%s (%s: %s)</option>',
									$k,
									( in_array( $k, $active_values ) ? 'selected' : '' ),
									$k,
									__( 'např.', 'messageok' ),
									( is_serialized( $v ) ? __( '(serializovaná data)', 'messageok' ) : esc_attr( $v ) )
								);
							}
						?>
					</select>
				</td>

				<td id="msgok_i_delivery" <?php if ( $integrations['delivery']['type'] != 'custom' || empty( $integrations['delivery']['value'] ) ) : ?>style="display: none;"<?php endif; ?>>
					<input name="integrations[delivery][custom]" value="<?php esc_attr_e( $integrations['delivery']['value'] ); ?>" class="regular-text">
				</td>
			</tr>

			<tr>
				<th scope="row">
					<?php _e( 'Faktury', 'messageok' ); ?>
				</th>

				<td>
					<?php if ( $integrations['invoice']['status'] ) : ?>
						<span style="color: green;"><span class="dashicons dashicons-yes"></span> <?php _e( 'nastaveno', 'messageok' ); ?></span>
					<?php else: ?>
						<span style="color: red;"><span class="dashicons dashicons-no"></span> <?php _e( 'nenastaveno', 'messageok' ); ?></span>
					<?php endif; ?>
				</td>

				<td>
					<select name="integrations[invoice][select][]" data-type="invoice" class="msgok-integrations-select" multiple>
						<option value="" <?php if ( empty( $integrations['invoice']['value'] ) ) : ?>selected<?php endif; ?>></option>
						<option value="__custom__" <?php if ( $integrations['invoice']['type'] == 'custom' && !empty( $integrations['invoice']['value'] ) ) : ?>selected<?php endif; ?>>(vyplnit vlastní)</option>

						<?php
							$active_values = explode( ';', $integrations['invoice']['value'] );
							foreach ( $available_postmeta as $k => $v ) {
								printf( '<option value="%s" %s>%s (%s: %s)</option>',
									$k,
									( in_array( $k, $active_values ) ? 'selected' : '' ),
									$k,
									__( 'např.', 'messageok' ),
									( is_serialized( $v ) ? __( '(serializovaná data)', 'messageok' ) : esc_attr( $v ) )
								);
							}
						?>
					</select>
				</td>

				<td id="msgok_i_invoice" <?php if ( $integrations['invoice']['type'] != 'custom' || empty( $integrations['invoice']['value'] ) ) : ?>style="display: none;"<?php endif; ?>>
					<input name="integrations[invoice][custom]" value="<?php esc_attr_e( $integrations['invoice']['value'] ); ?>" class="regular-text">
				</td>
			</tr>
		</tbody>
	</table>

	<p>
		<button type="submit" class="button button-primary">
			<?php _e( 'Uložit nastavení propojení', 'messageok' ); ?>
		</button>
	</p>

	<input type="hidden" name="msgok_save_integrations" value="1">
</form>