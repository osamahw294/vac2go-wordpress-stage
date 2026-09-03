<?php
/**
 * Placeholder controls for customizer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Form placeholders section uses refresh method.

/* Start of Section */
$wp_customize->add_section(
	'gf_stla_form_id_placeholders',
	array(
		'title' => 'Placeholders',
		'panel' => 'gf_stla_panel',
	)
);

// Font style buttons.
$wp_customize->add_setting(
	'gf_stla_form_id_' . $current_form_id . '[placeholders][font-style]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Stla_Font_Style_Option(
		$wp_customize,
		'gf_stla_form_id_' . $current_form_id . '[placeholders][font-style]',
		array(
			'label'   => 'Font Style',
			'section' => 'gf_stla_form_id_placeholders',
			'type'    => 'font_style',
			'choices' => $font_style_choices,
		)
	)
);

// Label.
$wp_customize->add_control(
	new WP_Customize_Label_Only(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_' . $current_form_id . '[placeholders][font-size-label-only]', // Setting id.
		array( // Args, including any custom ones.
			'label'    => __( 'Font Size' , 'styles-and-layouts-for-gravity-forms'),
			'section'  => 'gf_stla_form_id_placeholders',
			'settings' => array(),
		)
	)
);
/* for pc */
$wp_customize->add_setting(
	'gf_stla_form_id_' . $current_form_id . '[placeholders][font-size]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Stla_Desktop_Text_Input_Option(
		$wp_customize,
		'gf_stla_form_id_' . $current_form_id . '[placeholders][font-size]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'gf_stla_form_id_placeholders', // Required, core or custom.
			'label'       => '',
			'input_attrs' => array(
				'placeholder' => 'Ex: 40px',
			),
		)
	)
);
/* for tablet */
$wp_customize->add_setting(
	'gf_stla_form_id_' . $current_form_id . '[placeholders][font-size-tab]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Stla_Tab_Text_Input_Option(
		$wp_customize,
		'gf_stla_form_id_' . $current_form_id . '[placeholders][font-size-tab]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'gf_stla_form_id_placeholders', // Required, core or custom.
			'label'       => '',
			'input_attrs' => array(
				'placeholder' => 'Ex: 40px',
			),
		)
	)
);

/* for mobile */
$wp_customize->add_setting(
	'gf_stla_form_id_' . $current_form_id . '[placeholders][font-size-phone]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Stla_Mobile_Text_Input_Option(
		$wp_customize,
		'gf_stla_form_id_' . $current_form_id . '[placeholders][font-size-phone]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'gf_stla_form_id_placeholders', // Required, core or custom.
			'label'       => '',
			'input_attrs' => array(
				'placeholder' => 'Ex: 40px',
			),
		)
	)
);


/* Start of Section */
$wp_customize->add_setting(
	'gf_stla_form_id_' . $current_form_id . '[placeholders][font-color]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new WP_Customize_Color_Control(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_' . $current_form_id . '[placeholders][font-color]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Font Color' , 'styles-and-layouts-for-gravity-forms'),
			'section' => 'gf_stla_form_id_placeholders',
		)
	)
);
