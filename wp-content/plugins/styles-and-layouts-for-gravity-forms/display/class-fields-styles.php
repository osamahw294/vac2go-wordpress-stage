<?php
/**
 * Display fields styles.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$get_fields_options = get_option( 'gf_stla_field_id_' . $css_form_id );

if ( empty( $get_fields_options ) ) {
	return;
}

ob_start();
?>

<style type="text/css">

<?php

foreach ( $get_fields_options as $field_id => $get_field_option ) {
	// Inputs.
	if ( isset( $get_fields_options[ $field_id ]['text-fields'] ) ) {
		?>
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=text],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=email],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=tel],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=url],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=password],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=number]
		{
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'text-fields', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
			max-width:100%;
		<?php
		if ( ! isset( $get_fields_options[ $field_id ]['text-fields']['border-size'] ) ) {
			echo 'border-width: 1px;';
		}
		?>
		}

		<?php
		if ( $get_fields_options[ $field_id ]['text-fields']['max-width'] !== '' ) {
			?>

			#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_complex.ginput_container,
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container.ginput_container_list
		{
			width: <?php echo esc_html( $get_fields_options[ $field_id ]['text-fields']['max-width'] ); ?>;
			max-width: 100%;
		}

		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_complex.ginput_container input[type="text"],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container.ginput_container_list input[type="text"] {
			width: 100%;
			max-width: 100%;
		}

			<?php
		}
	}

	// Labels.
	if ( isset( $get_fields_options[ $field_id ]['field-labels'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_label {
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'field-labels', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		}
	
		<?php if ( ! empty( $get_fields_options[ $field_id ]['field-labels']['asterisk-color'] ) ) { ?>
	
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield .gfield_label .gfield_required {
	
			color: <?php echo esc_html( $get_fields_options[ $field_id ]['field-labels']['asterisk-color'] ); ?>;
		}
			<?php
		}
	}

	// Sub labels.
	if ( isset( $get_fields_options[ $field_id ]['field-sub-labels'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> div label{
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'field-sub-labels', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		}
		<?php
	}

	// Field Description.
	if ( isset( $get_fields_options[ $field_id ]['field-descriptions'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_description {
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'field-descriptions', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		}
		<?php
	}

	// Dropdown fields.
	if ( isset( $get_fields_options[ $field_id ]['dropdown-fields'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> select {
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'dropdown-fields', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
			max-width: 100%;
		<?php
		if ( ! isset( $get_fields_options[ $field_id ]['dropdown-fields']['border-size'] ) ) {
			echo 'border-width: 1px;';
		}
		?>
		}
		<?php
	}

	// Radio.
	if ( isset( $get_fields_options[ $field_id ]['radio-inputs'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_radio label {
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'radio-inputs', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		<?php echo ! empty( $get_fields_options[ $field_id ]['radio-inputs']['max-width'] ) ? 'width: 100%;' : ''; ?>
		}

		<?php if ( ! empty( $get_fields_options[ $field_id ]['radio-inputs']['max-width'] ) ) { ?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_radio .gfield_radio {
				width: <?php echo esc_html( $get_fields_options[ $field_id ]['radio-inputs']['max-width'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['radio-inputs']['max-width'] ) ); ?>; 
			}
			<?php
		}
	}

	// Checkbox.
	if ( isset( $get_fields_options[ $field_id ]['checkbox-inputs'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_checkbox label,
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield .ginput_container_consent label{
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'checkbox-inputs', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		<?php echo ! empty( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width'] ) ? 'width: 100%;' : ''; ?>
		}

	
		<?php if ( ! empty( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width'] ) ) { ?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_checkbox .gfield_checkbox,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_consent {
				width: <?php echo esc_html( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width'] ) ); ?>; 
			}
			<?php
		}
	}

	if ( isset( $get_fields_options[ $field_id ]['paragraph-textarea'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> textarea {
		<?php
			echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'paragraph-textarea', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css().
		?>
		<?php

		if ( ! isset( $get_fields_options[ $field_id ]['paragraph-textarea']['border-size'] ) ) {
			echo 'border-width: 1px;';
		}

		?>
		}
		<?php
	}

	// section field styles.
	if ( isset( $get_fields_options[ $field_id ]['section-break-title'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsection#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gsection_title {
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'section-break-title', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		}
		<?php
	}

	if ( isset( $get_fields_options[ $field_id ]['section-break-description'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsection#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gsection_description {
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'section-break-description', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
			padding: 0 16px 0 0 !important;
		}
		<?php
	}

	if ( ! empty( $get_fields_options[ $field_id ]['section-break-description']['padding-top'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsection#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> {
			padding-top: <?php echo esc_html( $get_fields_options[ $field_id ]['section-break-description']['padding-top'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['section-break-description']['padding-top'] ) ) . ';'; ?>
		}
		<?php
	}

	if ( ! empty( $get_fields_options[ $field_id ]['section-break-description']['padding-left'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsection#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> {
			padding-left: <?php echo esc_html( $get_fields_options[ $field_id ]['section-break-description']['padding-left'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['section-break-description']['padding-left'] ) ) . ';'; ?>
		}
		<?php
	}

	if ( ! empty( $get_fields_options[ $field_id ]['section-break-description']['padding-bottom'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsection#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> {
			padding-bottom: <?php echo esc_html( $get_fields_options[ $field_id ]['section-break-description']['padding-bottom'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['section-break-description']['padding-bottom'] ) ) . ';'; ?>
		}
		
		<?php
	}

	if ( ! empty( $get_fields_options[ $field_id ]['section-break-description']['padding-right'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsection#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> {
			padding-right: <?php echo esc_html( $get_fields_options[ $field_id ]['section-break-description']['padding-right'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['section-break-description']['padding-right'] ) ) . ';'; ?>
		}
	
		<?php
	}

	// List field styles.
	if ( isset( $get_fields_options[ $field_id ]['list-field-table'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list,
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list .gfield_list_groups .gfield_list_group{
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'list-field-table', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		}
		<?php
	}

	if ( isset( $get_fields_options[ $field_id ]['list-field-heading'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list .gfield_list .gfield_header_item:not(:last-child),
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list .gfield_list thead th:not(:last-child),
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list .gfield_list .gfield_list_cell::before,
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list .gfield_list .gfield_list_cell::after{
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'list-field-heading', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		}
		<?php
	}

	if ( isset( $get_fields_options[ $field_id ]['list-field-cell'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list .gfield_list .gfield_list_cell input {
		<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'list-field-cell', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		}
		<?php
	}

	if ( isset( $get_fields_options[ $field_id ]['list-field-cell-container'] ) ) {
		?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_list .gfield_list_cell  {
			<?php echo stla_esc_css( $main_class_object->gf_sb_get_saved_styles( $css_form_id, 'list-field-cell-container', $important, $field_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS for a <style> element; esc_html() would corrupt it. See stla_esc_css(). ?>
		}
		<?php
	}
	?>

	/* Styling for Tablets */
	@media only screen and ( max-width: 800px ) and ( min-width:481px ) {

		<?php if ( stla_isset_checker( $get_fields_options[ $field_id ], 'text-fields', array( 'font-size-tab', 'max-width-tab', 'height-tab', 'line-height-tab' ) ) ) { ?>

			#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=text],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=email],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=tel],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=url],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=password],
		#gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=number] {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'text-fields', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( ! empty( $get_fields_options[ $field_id ]['text-fields']['max-width-tab'] ) ) {
			?>
		
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> div.ginput_complex.ginput_container.ginput_container_name,
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> div.ginput_complex.ginput_container,
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container.ginput_container_list {
			width: <?php echo esc_html( $get_fields_options[ $field_id ]['text-fields']['max-width-tab'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['text-fields']['max-width-tab'] ) ); ?>;
			max-width:100%;
		}

		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> div.ginput_complex.ginput_container.ginput_container_name input[type=text],
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> div.ginput_complex.ginput_container.ginput_container_name select,
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> div.ginput_complex.ginput_container input[type="text"],
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> div.ginput_complex.ginput_container input select,
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container.ginput_container_list input[type=text] {
			max-width:100%;
			width:100%;
		}

		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?>.gform_wrapper .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_time_ampm select {
			width: calc( 3rem + 20px );
		}
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?>.gform_wrapper .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_time_hour input,
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?>.gform_wrapper .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_time_minute input {
			width: calc( 3rem + 8px );
		}
			<?php
		}

		// paragraph textarea.
		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'paragraph-textarea', array( 'max-width-tab', 'font-size-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> textarea {
					<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'paragraph-textarea', $important, $field_id ) ); ?>
			}

			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'dropdown-fields', array( 'font-size-tab', 'max-width-tab', 'height-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> select {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'dropdown-fields', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'radio-inputs', array( 'font-size-tab', 'max-width-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_radio label {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'radio-inputs', $important, $field_id ) ); ?>
				<?php echo ! empty( $get_fields_options[ $field_id ]['radio-inputs']['max-width-tab'] ) ? 'width: 100%;' : ''; ?>
			}

				<?php if ( ! empty( $get_fields_options[ $field_id ]['radio-inputs']['max-width-tab'] ) ) { ?>
				body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_radio .gfield_radio {
					width: <?php echo esc_html( $get_fields_options[ $field_id ]['radio-inputs']['max-width-tab'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['radio-inputs']['max-width-tab'] ) ); ?>;
					
				}
					<?php
				}
		}


		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'checkbox-inputs', array( 'font-size-tab', 'max-width-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_checkbox label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_consent label {
						<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'checkbox-inputs', $important, $field_id ) ); ?>
						<?php echo ! empty( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width-tab'] ) ? 'width: 100%;' : ''; ?>
			}

					<?php if ( ! empty( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width-tab'] ) ) { ?>
				body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_checkbox .gfield_checkbox,
				body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_consent {
					width: <?php echo esc_html( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width-tab'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width-tab'] ) ); ?>;
				}
						<?php
					}
		}


		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'field-labels', array( 'font-size-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_label {
						<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'field-labels', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'field-descriptions', array( 'font-size-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_description {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'field-descriptions', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'section-break-title', array( 'font-size-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsection#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gsection_title {
					<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'section-break-title', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'section-break-description', array( 'font-size-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsectionn#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gsection_description {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'section-break-description', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'field-sub-labels', array( 'font-size-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_complex .ginput_full label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_complex .ginput_right label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_complex .ginput_left label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .name_first label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .name_last label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_line_1 label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_line_2 label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_city label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_state label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_zip label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_country label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_time_hour label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_time_minute label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_date_month label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_date_day label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_date_year label{
					<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'field-sub-labels', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'list-field-heading', array( 'font-size-tab', 'line-height-tab' ) ) ) {
			?>
		
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list .gfield_list tbody .gfield_list_cell::after {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'list-field-heading', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'list-field-cell', array( 'font-size-tab', 'line-height-tab' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list .gfield_list .gfield_list_cell input {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_tab( $css_form_id, 'list-field-cell', $important, $field_id ) ); ?>
			}
		<?php } ?> 

		
	}

	@media only screen and ( max-width: 480px ) {

		<?php if ( stla_isset_checker( $get_fields_options[ $field_id ], 'text-fields', array( 'font-size-phone', 'max-width-phone', 'height-phone', 'line-height-phone' ) ) ) { ?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=text],
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=email],
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=tel],
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=url],
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=password],
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> input[type=number] {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'text-fields', $important, $field_id ) ); ?>
			} 

			<?php if ( ! empty( $get_fields_options[ $field_id ]['text-fields']['max-width-phone'] ) ) { ?>
					body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> #field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> div.ginput_complex.ginput_container.ginput_container_name,
					body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> #field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> div.ginput_complex.ginput_container,
					body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container.ginput_container_list {
						width: <?php echo esc_html( $get_fields_options[ $field_id ]['text-fields']['max-width-phone'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['text-fields']['max-width-phone'] ) ); ?>;
						max-width:100%;
					}
				<?php
			}
		}


		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'paragraph-textarea', array( 'max-width-phone', 'line-height-phone', 'font-size-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> textarea {
						<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'paragraph-textarea', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'dropdown-fields', array( 'font-size-phone', 'max-width-phone', 'height-phone', 'line-height-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> select {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'dropdown-fields', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'radio-inputs', array( 'font-size-phone', 'max-width-phone', 'line-height-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_radio label {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'radio-inputs', $important, $field_id ) ); ?>
				<?php echo ! empty( $get_fields_options[ $field_id ]['radio-inputs']['max-width-tab'] ) ? 'width: 100%;' : ''; ?>
			}

			<?php if ( ! empty( $get_fields_options[ $field_id ]['radio-inputs']['max-width-phone'] ) ) { ?>
					body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_radio .gfield_radio {
						width: <?php echo esc_html( $get_fields_options[ $field_id ]['radio-inputs']['max-width-phone'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['radio-inputs']['max-width-phone'] ) ); ?>; 
					}
				<?php
			}
		}


		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'checkbox-inputs', array( 'font-size-phone', 'max-width-phone', 'line-height-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_checkbox label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_consent label {
						<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'checkbox-inputs', $important, $field_id ) ); ?>
						<?php echo ! empty( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width-phone'] ) ? 'width: 100%;' : ''; ?>
			}
					<?php if ( ! empty( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width-phone'] ) ) { ?>
				body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_checkbox .gfield_checkbox,
				body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_container_consent {
					width: <?php echo esc_html( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width-phone'] ) . esc_html( $main_class_object->gf_stla_add_px_to_value( $get_fields_options[ $field_id ]['checkbox-inputs']['max-width-phone'] ) ); ?>;
				}
						<?php
					}
		}


		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'field-labels', array( 'font-size-phone', 'line-height-phone' ) ) ) {
			?>
		body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_label {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'field-labels', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'field-descriptions', array( 'font-size-phone', 'line-height-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_description {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'field-descriptions', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'section-break-title', array( 'font-size-phone', 'line-height-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsection#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gsection_title {
						<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'section-break-title', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'section-break-description', array( 'font-size-phone', 'line-height-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gsection#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gsection_description {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'section-break-description', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'field-sub-labels', array( 'font-size-phone', 'line-height-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_complex .ginput_full label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_complex .ginput_right label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_complex .ginput_left label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .name_first label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .name_last label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_line_1 label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_line_2 label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_city label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_state label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_zip label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .address_country label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_time_hour label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_time_minute label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_date_month label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_date_day label,
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .gfield_date_year label {
				<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'field-sub-labels', $important, $field_id ) ); ?>
			}
			<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'list-field-heading', array( 'font-size-phone', 'line-height-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list .gfield_list .gfield_list_cell::before {
					<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'list-field-heading', $important, $field_id ) ); ?>
			}

				<?php
		}

		if ( stla_isset_checker( $get_fields_options[ $field_id ], 'list-field-cell', array( 'font-size-phone', 'line-height-phone' ) ) ) {
			?>
			body #gform_wrapper_<?php echo esc_html( $css_form_id ); ?> .gform_body .gform_fields .gfield#field_<?php echo esc_html( $css_form_id ); ?>_<?php echo esc_html( $field_id ); ?> .ginput_list table.gfield_list tbody tr td.gfield_list_cell input {
			<?php echo esc_html( $main_class_object->gf_sb_get_saved_styles_phone( $css_form_id, 'list-field-cell', $important, $field_id ) ); ?>
			}
				
			<?php
		}
		?>
		  

	}
	
	<?php
}

?>

</style>

<?php
$styles = ob_get_contents();
ob_end_clean();

// replacing empty spacing and line-breaks.
$styles = preg_replace( '/\v(?:[\v\h]+)|(?:\t+)/', '', $styles );

/*
 * Printed as-is. The buffer is the whole <style> element, so it must not be run
 * through stla_esc_css() -- that neutralises "</style" and would rewrite this
 * element's own end tag, leaving the element open so the rest of the page (the
 * form included) is swallowed as CSS text. Every value interpolated above is
 * already passed through stla_esc_css()/stla_sanitize_custom_css() on its own.
 */
echo $styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Values inside are individually sanitized; see note above.
