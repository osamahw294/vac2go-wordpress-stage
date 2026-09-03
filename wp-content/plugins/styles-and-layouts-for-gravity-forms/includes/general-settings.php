<?php
/**
 * General settings for customizer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wp_customize->add_section(
	'gf_stla_form_id_general_settings',
	array(
		'title' => 'General Settings',
		'panel' => 'gf_stla_panel',
	)
);

$wp_customize->add_setting(
	'gf_stla_general_settings' . $current_form_id . '[force-style]',
	array(
		'default'   => false,
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	'gf_stla_general_settings' . $current_form_id . '[force-style]',
	array(
		'type'     => 'checkbox',
		'priority' => 10, // Within the section.
		'section'  => 'gf_stla_form_id_general_settings', // Required, core or custom.
		'label'    => __( 'Enable this option if your theme is overwriting "Styles & layout plugin\'s styling"' , 'styles-and-layouts-for-gravity-forms'),
	)
);

$wp_customize->add_setting(
	'gf_stla_general_settings[reset-styles]',
	array(
		'default'   => -1,
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

// Get all gravity forms created by user.
if ( class_exists( 'RGFormsModel' ) ) {
	$forms = RGFormsModel::get_forms( null, 'title' );

	$select_form = array( -1 => '---Select form --' );
	foreach ( $forms as $form ) {
		$style_current_form = get_option( 'gf_stla_form_id_' . $form->id );
		if ( ! empty( $style_current_form ) ) {

			$select_form[ $form->id ] = $form->title;
		}
	}
} else {
	$select_form['form not installed'] = 'Gravity Forms not installed';
}

$wp_customize->add_setting(
	'gf_stla_form_id_' . $current_form_id . '[general-settings][custom-css]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	'gf_stla_form_id_' . $current_form_id . '[general-settings][custom-css]',
	array(
		'type'        => 'textarea',
		'priority'    => 10, // Within the section.
		'section'     => 'gf_stla_form_id_general_settings', // Required, core or custom.
		'label'       => __( 'Custom CSS' , 'styles-and-layouts-for-gravity-forms'),
		'input_attrs' => array(
			'placeholder' => 'Enter your custom css code here',
		),
	)
);

// reset styles button.
$wp_customize->add_setting(
	'gf_stla_form_id_reset_style_button',
	array(
		'default'   => 'Delete Styles',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	'gf_stla_general_settings[reset-styles]',
	array(
		'type'     => 'select',
		'priority' => 10, // Within the section.
		'section'  => 'gf_stla_form_id_general_settings', // Required, core or custom.
		'label'    => __( 'Delete Form Styles' , 'styles-and-layouts-for-gravity-forms'),
		'choices'  => $select_form,

	)
);

$wp_customize->add_control(
	'gf_stla_form_id_reset_style_button',
	array(
		'type'        => 'button',
		'priority'    => 10, // Within the section.
		'section'     => 'gf_stla_form_id_general_settings', // Required, core or custom.
		'input_attrs' => array(
			'style' => 'float:left',
			'class' => 'gf-stla-reset-style-button',
		),
	)
);
