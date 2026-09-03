<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if key exit inside settings.
 *
 * @param array  $form_options The saved settings of form.
 * @param string $category The category under settings.
 * @param string $field_names The property of field to check.
 * @return bool
 */
function stla_isset_checker( $form_options, $category, $field_names ) {
	$is_field_set = false;
	if ( ! isset( $form_options[ $category ] ) ) {
		return $is_field_set;
	}
	foreach ( $field_names as $field_name ) {
		if ( ! empty( $form_options[ $category ][ $field_name ] ) ) {
			$is_field_set = true;
		}
	}
	return $is_field_set;
}

/**
 * Sanitize a user supplied custom CSS blob.
 *
 * Custom CSS is only ever printed inside a <style> element. That is a raw text
 * element, so markup within it is inert and the single sequence that can break
 * out of it is an end tag for the element itself. Removing "</style" is
 * therefore sufficient, and unlike wp_strip_all_tags() it leaves valid CSS
 * untouched -- strip_tags() treats the "<=" in a Media Queries Level 4 range
 * such as "@media (width <= 600px)" as the start of a tag and silently discards
 * the remainder of the stylesheet.
 *
 * Applied both on save and on output so that values stored by versions prior to
 * 5.27 cannot reach the page.
 *
 * @param mixed $css The raw custom CSS.
 * @return string The sanitized CSS, or an empty string for non string input.
 */
function stla_sanitize_custom_css( $css ) {
	if ( ! is_string( $css ) ) {
		return '';
	}
	/*
	 * A space is inserted rather than the sequence being deleted: the HTML end
	 * tag open state only produces an end tag when an ASCII letter follows "</",
	 * so "</ style" is inert, and because the filter only ever inserts it cannot
	 * splice two harmless fragments back into a live "</style".
	 */
	return preg_replace( '#</(?=style)#i', '</ ', $css );
}

/**
 * Escape a block of generated CSS for printing inside a <style> element.
 *
 * esc_html() must not be used here. Entities are not decoded inside a raw text
 * element, so escaping turns working CSS into dead CSS: a child combinator
 * becomes "&gt;", a quote in a content property becomes "&quot;", and the "&"
 * in a url() query string becomes "&amp;". Neutralising the element's own end
 * tag is both necessary and sufficient for this context.
 *
 * @param mixed $css The generated CSS.
 * @return string The CSS, safe to print inside <style>.
 */
function stla_esc_css( $css ) {
	return stla_sanitize_custom_css( $css );
}
