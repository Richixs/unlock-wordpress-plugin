<?php
/**
 * Unlock login button
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

?>

<style>
	.login-button-container {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: 12px;
	}

	.login-button-container .login-button-image {
		max-width: 100%;
		height: auto;
		border-radius: 4px;
	}

	<?php if ( $login_button_bg_color && $login_button_text_color ) : ?>
	.login .login-button-container .login-button,
	.login-button-container .login-button {
		background-color: <?php echo sanitize_hex_color( $login_button_bg_color ); ?>;
		color: <?php echo sanitize_hex_color( $login_button_text_color ); ?>;
	}

	.login .login-button-container .login-button:hover,
	.login .login-button-container .login-button:focus,
	.login-button-container .login-button:hover,
	.login-button-container .login-button:focus {
		background-color: <?php echo sanitize_hex_color( $login_button_text_color ); ?> !important;
		color: <?php echo sanitize_hex_color( $login_button_bg_color ); ?>;
	}
	<?php endif; ?>
</style>

<?php do_action( 'unlock_before_login_button' ); ?>

<div class="login-button-container <?php echo $blurred_image_activated ? esc_attr( 'has-description' ) : ''; ?>">
	<?php if ( $blurred_image_activated && ! empty( $login_bg_image ) ) : ?>
		<img class="login-button-image" src="<?php echo esc_url( $login_bg_image ); ?>" alt="" />
	<?php endif; ?>

	<?php if ( $blurred_image_activated && ! empty( $login_button_description ) ) : ?>
		<p class="login-button-description"><?php echo esc_html( $login_button_description ); ?></p>
	<?php endif; ?>

	<a href="<?php echo esc_url( $login_url ); ?>" class="login-button"><?php echo esc_html( $login_button_text ); ?></a>
</div>

<?php do_action( 'unlock_after_login_button' ); ?>
