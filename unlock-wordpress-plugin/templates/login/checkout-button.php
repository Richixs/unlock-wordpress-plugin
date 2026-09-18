<?php
/**
 * Unlock checkout button
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

?>

<style>
	.checkout-button-container {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: 12px;
	}

	.checkout-button-container.align-center {
		align-items: center;
		text-align: center;
	}

	.checkout-button-container.align-right {
		align-items: flex-end;
		text-align: right;
	}

	.checkout-button-container .checkout-button-image {
		max-width: 100%;
		height: auto;
		border-radius: 4px;
	}

	<?php if ( $checkout_button_bg_color && $checkout_button_text_color ) : ?>
	.checkout-button-container .checkout-button {
		background-color: <?php echo sanitize_hex_color( $checkout_button_bg_color ); ?>;
		color: <?php echo sanitize_hex_color( $checkout_button_text_color ); ?>;
	}

	.checkout-button-container .checkout-button:hover {
		background-color: <?php echo sanitize_hex_color( $checkout_button_text_color ); ?>;
		color: <?php echo sanitize_hex_color( $checkout_button_bg_color ); ?>;
	}
	<?php endif; ?>
</style>

<?php do_action( 'unlock_before_checkout_button' ); ?>

<div class="checkout-button-container align-<?php echo esc_attr( $checkout_button_alignment ); ?> <?php echo $blurred_image_activated ? esc_attr( 'has-description' ) : ''; ?>">
	<?php if ( $blurred_image_activated && ! empty( $checkout_bg_image ) ) : ?>
		<img class="checkout-button-image" src="<?php echo esc_url( $checkout_bg_image ); ?>" alt="" />
	<?php endif; ?>

	<?php if ( $blurred_image_activated && ! empty( $checkout_button_description ) ) : ?>
		<p class="checkout-button-description"><?php echo esc_html( $checkout_button_description ); ?></p>
	<?php endif; ?>

	<?php
	/**
	 * Not using esc_url() intentionally. esc_url removes the `{}`
	 * Which is mandatory for unlock protocol checkout.
	 */
	?>
	<a href='<?php echo $checkout_url; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>' class="checkout-button"><?php echo esc_html( $checkout_button_text ); ?></a>
</div>

<?php do_action( 'unlock_after_checkout_button' ); ?>
