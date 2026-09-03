<?php
/**
 * Reusable helper functions.
 *
 * WHAT THIS FILE IS
 *   The place for small, reusable PHP helpers you call from patterns, block
 *   templates, or other includes. Everything here is fully escaped and
 *   translation-ready, prefixed hw_starter_, and wrapped in
 *   `if ( ! function_exists() )` so renaming/duplicating the theme can't cause
 *   a fatal "cannot redeclare" error.
 *
 * WHEN YOU'D EDIT THIS FILE
 *   - Adding a helper? Copy the pattern below: prefix the name, escape all
 *     output, wrap user-facing text in i18n, and add an @example so the next
 *     dev can copy/paste. Then just call it — no separate wiring needed.
 *   WHAT'S HERE NOW: an inline-SVG icon printer and brand phone/email readers
 *   (values set as theme mods — see hw_starter_get_contact()).
 *
 * @package HW_Starter
 * @link    https://developer.wordpress.org/apis/security/escaping/  Escaping output.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hw_starter_get_icon' ) ) {
	/**
	 * Return a small, safe inline SVG icon from the built-in allowlist.
	 *
	 * Icons are hardcoded here (not read from disk or user input), so the
	 * output is trusted markup. We still run it through wp_kses() with an SVG
	 * allowlist as defense in depth.
	 *
	 * WHY IT EXISTS: gives patterns/templates a safe inline icon without loading
	 * an icon font or plugin. TO ADD AN ICON: add a key + <path> to the $paths
	 * array below (SVG must use a 24x24 viewBox and currentColor so it inherits
	 * text color). Returns markup — use this when you need the string; use
	 * hw_starter_icon() to echo directly.
	 *
	 * @param string $name  Icon key: 'arrow', 'check', 'phone', or 'mail'.
	 * @param int    $size  Pixel size for width/height. Default 24.
	 * @param string $label Optional accessible label; when empty the icon is aria-hidden.
	 * @return string Sanitized SVG markup, or empty string if the name is unknown.
	 *
	 * @example
	 *   // Decorative icon inside a link (screen readers skip it):
	 *   echo '<a href="/services">' . hw_starter_get_icon( 'arrow' ) . '</a>';
	 *
	 *   // Meaningful standalone icon with an accessible label:
	 *   echo hw_starter_get_icon( 'phone', 20, __( 'Call us', 'hw-starter' ) );
	 */
	function hw_starter_get_icon( $name, $size = 24, $label = '' ) {
		$paths = array(
			'arrow' => '<path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
			'check' => '<path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
			'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8.1 9.5a16 16 0 0 0 6 6l1.1-1.1a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
			'mail'  => '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" fill="none" stroke="currentColor" stroke-width="2"/><path d="m22 6-10 7L2 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		);

		if ( ! isset( $paths[ $name ] ) ) {
			return '';
		}

		$size = absint( $size );
		$size = $size > 0 ? $size : 24;

		$a11y = '' !== $label
			? ' role="img" aria-label="' . esc_attr( $label ) . '"'
			: ' aria-hidden="true" focusable="false"';

		$svg = sprintf(
			'<svg class="hw-starter-icon hw-starter-icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"%3$s>%4$s</svg>',
			esc_attr( $name ),
			$size,
			$a11y,
			$paths[ $name ]
		);

		$allowed_svg = array(
			'svg'  => array(
				'class'       => true,
				'width'       => true,
				'height'      => true,
				'viewbox'     => true,
				'xmlns'       => true,
				'role'        => true,
				'aria-label'  => true,
				'aria-hidden' => true,
				'focusable'   => true,
			),
			'path' => array(
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			),
		);

		return wp_kses( $svg, $allowed_svg );
	}
}

if ( ! function_exists( 'hw_starter_icon' ) ) {
	/**
	 * Echo a safe inline SVG icon. Thin wrapper over hw_starter_get_icon().
	 *
	 * WHY IT EXISTS: convenience so templates read `hw_starter_icon('check')`
	 * instead of wrapping every call in echo. Output is pre-sanitized.
	 *
	 * @param string $name  Icon key.
	 * @param int    $size  Pixel size. Default 24.
	 * @param string $label Optional accessible label.
	 * @return void
	 *
	 * @example
	 *   <li><?php hw_starter_icon( 'check', 18 ); ?> Fast turnaround</li>
	 */
	function hw_starter_icon( $name, $size = 24, $label = '' ) {
		// Output is already sanitized inside hw_starter_get_icon().
		echo hw_starter_get_icon( $name, $size, $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

if ( ! function_exists( 'hw_starter_get_contact' ) ) {
	/**
	 * Read a brand contact value (phone or email) with sane, filterable defaults.
	 *
	 * Values are stored as theme mods so they survive per client without code
	 * changes; a filter lets sites override programmatically. Returned raw —
	 * escape at the point of output.
	 *
	 * WHY IT EXISTS: keeps the client's phone/email in ONE place (theme mods)
	 * instead of hardcoded across templates. WHERE THE VALUE IS SET: as theme
	 * mods `hw_starter_contact_phone` / `hw_starter_contact_email` — set them in
	 * a child Customizer control you add, via `set_theme_mod()`, or with the
	 * `hw_starter_contact_value` filter. Returned RAW — escape at output.
	 *
	 * @param string $key Which value: 'phone' or 'email'.
	 * @return string The stored value, or empty string if unknown/unset.
	 *
	 * @example
	 *   $email = hw_starter_get_contact( 'email' );
	 *   if ( $email ) {
	 *       echo esc_html( $email ); // escape because the value is raw
	 *   }
	 */
	function hw_starter_get_contact( $key ) {
		$allowed = array( 'phone', 'email' );

		if ( ! in_array( $key, $allowed, true ) ) {
			return '';
		}

		$value = get_theme_mod( 'hw_starter_contact_' . $key, '' );

		/**
		 * Filter a brand contact value before it is returned.
		 *
		 * @param string $value The stored value.
		 * @param string $key   The requested key ('phone' or 'email').
		 */
		$value = apply_filters( 'hw_starter_contact_value', $value, $key );

		return is_string( $value ) ? $value : '';
	}
}

if ( ! function_exists( 'hw_starter_contact_link' ) ) {
	/**
	 * Echo a ready-made tel:/mailto: link for a brand contact value.
	 *
	 * WHY IT EXISTS: turns the stored contact value into a click-to-call /
	 * click-to-email link with the href built and escaped for you, so templates
	 * don't hand-build tel:/mailto: markup (a common escaping mistake).
	 *
	 * @param string $key Which value: 'phone' or 'email'.
	 * @return void
	 *
	 * @example
	 *   // In a header/footer template:
	 *   hw_starter_contact_link( 'phone' ); // -> <a href="tel:+15551234567">(555) 123-4567</a>
	 */
	function hw_starter_contact_link( $key ) {
		$value = hw_starter_get_contact( $key );

		if ( '' === $value ) {
			return;
		}

		if ( 'email' === $key && is_email( $value ) ) {
			$href = 'mailto:' . sanitize_email( $value );
		} elseif ( 'phone' === $key ) {
			// Keep digits and a leading +; strip everything else for the href.
			$href = 'tel:' . preg_replace( '/[^0-9+]/', '', $value );
		} else {
			return;
		}

		printf(
			'<a class="hw-starter-contact hw-starter-contact--%1$s" href="%2$s">%3$s</a>',
			esc_attr( $key ),
			esc_url( $href, array( 'tel', 'mailto' ) ),
			esc_html( $value )
		);
	}
}
