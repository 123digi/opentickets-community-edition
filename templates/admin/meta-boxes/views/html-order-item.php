<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<tr class="item <?php echo apply_filters( 'woocommerce_admin_html_order_item_class', ( ! empty( $class ) ? $class : '' ), $item ); ?>" data-order_item_id="<?php echo $item_id; ?>">
	<td class="check-column"><input type="checkbox" /></td>
	<td class="thumb">
		<?php if ( $_product ) : ?>
			<a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $_product->id ) . '&action=edit' ) ); ?>" class="tips" data-tip="<?php

				echo '<strong>' . __( 'Product ID:', 'woocommerce' ) . '</strong> ' . absint( $item['product_id'] );

				if ( $item['variation_id'] && 'product_variation' === get_post_type( $item['variation_id'] ) ) {
					echo '<br/><strong>' . __( 'Variation ID:', 'woocommerce' ) . '</strong> ' . absint( $item['variation_id'] );
				} elseif ( $item['variation_id'] ) {
					echo '<br/><strong>' . __( 'Variation ID:', 'woocommerce' ) . '</strong> ' . absint( $item['variation_id'] ) . ' (' . __( 'No longer exists', 'woocommerce' ) . ')';
				}

				if ( $_product && $_product->get_sku() ) {
					echo '<br/><strong>' . __( 'Product SKU:', 'woocommerce' ).'</strong> ' . esc_html( $_product->get_sku() );
				}

				if ( $_product && isset( $_product->variation_data ) ) {
					echo '<br/>' . wc_get_formatted_variation( $_product->variation_data, true );
				}

			?>"><?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ); ?></a>
		<?php else : ?>
			<?php echo wc_placeholder_img( 'shop_thumbnail' ); ?>
		<?php endif; ?>
	</td>
	<td class="name">

		<?php echo ( $_product && $_product->get_sku() ) ? esc_html( $_product->get_sku() ) . ' &ndash; ' : ''; ?>

		<?php if ( $_product ) : ?>
			<a target="_blank" href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $_product->id ) . '&action=edit' ) ); ?>">
				<?php echo esc_html( $item['name'] ); ?>
			</a>
		<?php else : ?>
			<?php echo esc_html( $item['name'] ); ?>
		<?php endif; ?>

		<input type="hidden" class="order_item_id" name="order_item_id[]" value="<?php echo esc_attr( $item_id ); ?>" />
		<?php if ( !isset($legacy_order) || ! $legacy_order ): /*@@@@LOUSHOU - backwards compat for WC2.1 */ ?>
			<input type="hidden" name="order_item_tax_class[<?php echo absint( $item_id ); ?>]" value="<?php echo isset( $item['tax_class'] ) ? esc_attr( $item['tax_class'] ) : ''; ?>" />
		<?php endif; ?>

		<?php do_action('woocommerce_before_order_itemmeta', $item_id, $item, $_product) ?>

		<div class="view">
			<?php do_action('woocommerce_before_view_order_itemmeta', $item_id, $item, $_product) /*@@@@LOUSHOU - filter for customizing meta */ ?>
			<?php
				global $wpdb;

				if ( $metadata = $order->has_meta( $item_id ) ) {
					echo '<table cellspacing="0" class="display_meta">';
					foreach ( $metadata as $meta ) {

						// Skip hidden core fields
						if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
							'_qty',
							'_tax_class',
							'_product_id',
							'_variation_id',
							'_line_subtotal',
							'_line_subtotal_tax',
							'_line_total',
							'_line_tax',
						) ) ) ) {
							continue;
						}

						// Skip serialised meta
						if ( is_serialized( $meta['meta_value'] ) ) {
							continue;
						}

						// Get attribute data
						if ( taxonomy_exists( $meta['meta_key'] ) ) {
							$term           = get_term_by( 'slug', $meta['meta_value'], $meta['meta_key'] );
							$attribute_name = str_replace( 'pa_', '', wc_clean( $meta['meta_key'] ) );
							$attribute      = $wpdb->get_var(
								$wpdb->prepare( "
										SELECT attribute_label
										FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
										WHERE attribute_name = %s;
									",
									$attribute_name
								)
							);

							$meta['meta_key']   = ( ! is_wp_error( $attribute ) && $attribute ) ? $attribute : $attribute_name;
							$meta['meta_value'] = ( isset( $term->name ) ) ? $term->name : $meta['meta_value'];
						}

						echo '<tr><th>' . wp_kses_post( urldecode( $meta['meta_key'] ) ) . ':</th><td>' . wp_kses_post( wpautop( urldecode( $meta['meta_value'] ) ) ) . '</td></tr>';
					}
					echo '</table>';
				}
			?>
			<?php do_action('woocommerce_after_view_order_itemmeta', $item_id, $item, $_product) /*@@@@LOUSHOU - filter for customizing meta */ ?>
		</div>
		<div class="edit" style="display: none;">
			<?php do_action('woocommerce_before_edit_order_itemmeta', $item_id, $item, $_product, $order) /*@@@@LOUSHOU - filter for customizing meta */ ?>
			<table class="meta" cellspacing="0">
				<tbody class="meta_items">
				<?php
					if ( $metadata = $order->has_meta( $item_id )) {
						foreach ( $metadata as $meta ) {

							// Skip hidden core fields
							if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
								'_qty',
								'_tax_class',
								'_product_id',
								'_variation_id',
								'_line_subtotal',
								'_line_subtotal_tax',
								'_line_total',
								'_line_tax',
							) ) ) ) {
								continue;
							}

							// Skip serialised meta
							if ( is_serialized( $meta['meta_value'] ) ) {
								continue;
							}

							$meta['meta_key']   = urldecode( $meta['meta_key'] );
							$meta['meta_value'] = esc_textarea( urldecode( $meta['meta_value'] ) ); // using a <textarea />
							$meta['meta_id']    = absint( $meta['meta_id'] );

							echo '<tr data-meta_id="' . esc_attr( $meta['meta_id'] ) . '">
								<td>
									<input type="text" name="meta_key[' . $meta['meta_id'] . ']" value="' . esc_attr( $meta['meta_key'] ) . '" />
									<textarea name="meta_value[' . $meta['meta_id'] . ']">' . $meta['meta_value'] . '</textarea>
								</td>
								<td width="1%"><button class="remove_order_item_meta button">&times;</button></td>
							</tr>';
						}
					}
				?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4"><button class="add_order_item_meta button"><?php _e( 'Add&nbsp;meta', 'woocommerce' ); ?></button></td>
					</tr>
				</tfoot>
			</table>
			<?php do_action('woocommerce_after_edit_order_itemmeta', $item_id, $item, $_product, $order) /*@@@@LOUSHOU - filter for customizing meta */ ?>
		</div>
	</td>

	<?php do_action( 'woocommerce_admin_order_item_values', $_product, $item, absint( $item_id ) ); ?>

	<?php if ( isset( $legacy_order ) && ! $legacy_order && 'yes' == get_option( 'woocommerce_calc_taxes' ) ) :
		/*@@@@LOUSHOU - end backwards compat - for tax classes */
		$tax_classes         = array_filter( array_map( 'trim', explode( "\n", get_option('woocommerce_tax_classes' ) ) ) );
		$classes_options     = array();
		$classes_options[''] = __( 'Standard', 'woocommerce' );

		if ( $tax_classes )
			foreach ( $tax_classes as $class )
				$classes_options[ sanitize_title( $class ) ] = $class;
		?>
		<td class="tax_class" width="1%">
			<div class="view">
				<?php
					$item_value = isset( $item['tax_class'] ) ? sanitize_title( $item['tax_class'] ) : '';
					echo $classes_options[ $item_value ];
				?>
			</div>
			<div class="edit" style="display:none">
				<select class="tax_class" name="order_item_tax_class[<?php echo absint( $item_id ); ?>]" title="<?php _e( 'Tax class', 'woocommerce' ); ?>">
					<?php
					$item_value  = isset( $item['tax_class'] ) ? sanitize_title( $item['tax_class'] ) : '';

					foreach ( $classes_options as $value => $name )
						echo '<option value="' . esc_attr( $value ) . '" ' . selected( $value, $item_value, false ) . '>' . esc_html( $name ) . '</option>';
					?>
				</select>
			</div>
		</td>
		<?php
	endif; /*@@@@LOUSHOU - end backwards compat */
	?>

	<td class="quantity" width="1%">
		<div class="view">
			<?php
				echo ( isset( $item['qty'] ) ) ? esc_html( $item['qty'] ) : '';

				/*@@@@LOUSHOU - backwards compatibility */
				if ( is_callable(array(&$order, 'get_qty_refunded_for_item')) && $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
					echo '<small class="refunded">-' . $refunded_qty . '</small>';
				}
			?>
		</div>
		<div class="edit" style="display: none;">
			<?php $item_qty = esc_attr( $item['qty'] ); ?>
			<input type="number" step="<?php echo apply_filters( 'woocommerce_quantity_input_step', '1', $_product ); ?>" min="0" autocomplete="off" name="order_item_qty[<?php echo absint( $item_id ); ?>]" placeholder="0" value="<?php echo $item_qty; ?>" data-qty="<?php echo $item_qty; ?>" size="4" class="quantity" />
		</div>
		<div class="refund" style="display: none;">
			<input type="number" step="<?php echo apply_filters( 'woocommerce_quantity_input_step', '1', $_product ); ?>" min="0" max="<?php echo $item['qty']; ?>" autocomplete="off" name="refund_order_item_qty[<?php echo absint( $item_id ); ?>]" placeholder="0" size="4" class="refund_order_item_qty" />
		</div>
	</td>

	<td class="line_cost" width="1%">
		<div class="view">
			<?php
				if ( isset( $item['line_total'] ) ) {
					if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
						echo '<del>' . wc_price( $item['line_subtotal'] ) . '</del> ';
					}

					echo wc_price( $item['line_total'] );
				}

				/*@@@@LOUSHOU - backwards compatibility */
				if ( is_callable(array(&$order, 'get_total_refunded_for_item')) && $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
					echo '<small class="refunded">-' . wc_price( $refunded ) . '</small>';
				}
			?>
		</div>
		<div class="edit" style="display: none;">
			<div class="split-input">
				<?php $item_total = ( isset( $item['line_total'] ) ) ? esc_attr( wc_format_localized_price( $item['line_total'] ) ) : ''; ?>
				<input type="text" name="line_total[<?php echo absint( $item_id ); ?>]" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" value="<?php echo $item_total; ?>" class="line_total wc_input_price tips" data-tip="<?php _e( 'After pre-tax discounts.', 'woocommerce' ); ?>" data-total="<?php echo $item_total; ?>" />

				<?php $item_subtotal = ( isset( $item['line_subtotal'] ) ) ? esc_attr( wc_format_localized_price( $item['line_subtotal'] ) ) : ''; ?>
				<input type="text" name="line_subtotal[<?php echo absint( $item_id ); ?>]" value="<?php echo $item_subtotal; ?>" class="line_subtotal wc_input_price tips" data-tip="<?php _e( 'Before pre-tax discounts.', 'woocommerce' ); ?>" data-subtotal="<?php echo $item_subtotal; ?>" />
			</div>
		</div>
		<div class="refund" style="display: none;">
			<input type="text" name="refund_line_total[<?php echo absint( $item_id ); ?>]" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" class="refund_line_total wc_input_price" />
		</div>
	</td>

	<?php
		if ( isset( $legacy_order ) && ! $legacy_order && 'yes' == get_option( 'woocommerce_calc_taxes' ) ) :
			$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
			$tax_data      = maybe_unserialize( $line_tax_data );

			foreach ( $order_taxes as $tax_item ) :
				$tax_item_id       = $tax_item['rate_id'];
				$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
				$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';

				?>
					<td class="line_tax" width="1%">
						<div class="view">
							<?php
								if ( '' != $tax_item_total ) {
									if ( isset( $tax_item_subtotal ) && $tax_item_subtotal != $tax_item_total ) {
										echo '<del>' . wc_price( wc_round_tax_total( $tax_item_subtotal ) ) . '</del> ';
									}

									echo wc_price( wc_round_tax_total( $tax_item_total ) );
								} else {
									echo '&ndash;';
								}

								if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id ) ) {
									echo '<small class="refunded">-' . wc_price( $refunded ) . '</small>';
								}
							?>
						</div>
						<div class="edit" style="display: none;">
							<div class="split-input">
								<?php $item_total_tax = ( isset( $tax_item_total ) ) ? esc_attr( wc_format_localized_price( $tax_item_total ) ) : ''; ?>
								<input type="text" name="line_tax[<?php echo absint( $item_id ); ?>][<?php echo absint( $tax_item_id ); ?>]" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" value="<?php echo $item_total_tax; ?>" class="line_tax wc_input_price tips" data-tip="<?php _e( 'After pre-tax discounts.', 'woocommerce' ); ?>" data-total_tax="<?php echo $item_total_tax; ?>" />

								<?php $item_subtotal_tax = ( isset( $tax_item_subtotal ) ) ? esc_attr( wc_format_localized_price( $tax_item_subtotal ) ) : ''; ?>
								<input type="text" name="line_subtotal_tax[<?php echo absint( $item_id ); ?>][<?php echo absint( $tax_item_id ); ?>]" value="<?php echo $item_subtotal_tax; ?>" class="line_subtotal_tax wc_input_price tips" data-tip="<?php _e( 'Before pre-tax discounts.', 'woocommerce' ); ?>"data-subtotal_tax="<?php echo $item_subtotal_tax; ?>" />
							</div>
						</div>
						<div class="refund" style="display: none;">
							<input type="text" name="refund_line_tax[<?php echo absint( $item_id ); ?>][<?php echo absint( $tax_item_id ); ?>]" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" class="refund_line_tax wc_input_price" data-tax_id="<?php echo absint( $tax_item_id ); ?>" />
						</div>
					</td>
				<?php
			endforeach;
		endif;
	?>

	<?php do_action( 'woocommerce_admin_after_order_item_values', $_product, $item, absint( $item_id ) ); /*@@@@LOUSHOU - add columns at the end of the values list */ ?>

	<td class="wc-order-edit-line-item">
		<?php if ( !is_callable(array(&$order, 'is_editable')) ): /*@@@@LOUSHOU - backwards compatibility */ ?>
			<a class="edit_order_item" href="#"><img src="<?php echo WC()->plugin_url(); ?>/assets/images/icons/edit.png" alt="Edit" width="14" /></a>
		<?php elseif ( $order->is_editable() ) : ?>
			<div class="wc-order-edit-line-item-actions">
				<a class="edit-order-item" href="#"></a><a class="delete-order-item" href="#"></a>
			</div>
		<?php endif; ?>
	</td>
</tr>
