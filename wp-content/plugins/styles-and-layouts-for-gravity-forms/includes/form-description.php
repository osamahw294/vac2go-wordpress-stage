<?php
/**
 * Form Description controls for customizer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Start of Section */
$wp_customize->add_setting(
	'gf_stla_form_id_' . $current_form_id . '[form-description][font-color]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-description][font-color]', // Setting id.
			array( // Args, including any custom ones.
				'label'   => __( 'Description Font Color' , 'styles-and-layouts-for-gravity-forms'),
				'section' => 'gf_stla_form_id_form_title_description',
			)
		)
	);

	// Font style buttons.
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-description][font-style]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Font_Style_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-description][font-style]',
			array(
				'label'   => 'Description Font Style',
				'section' => 'gf_stla_form_id_form_title_description',
				'type'    => 'font_style',
				'choices' => $font_style_choices,
			)
		)
	);


	// label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-description][font-size-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Description Font Size' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_form_title_description',
				'settings' => array(),
			)
		)
	);

	/* for pc */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-description][font-size]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Desktop_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-description][font-size]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_title_description', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 40px ',
				),
			)
		)
	);

	/* for tablet */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-description][font-size-tab]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Tab_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-description][font-size-tab]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_title_description', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 40px ',
				),
			)
		)
	);

	/* for mobile */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-description][font-size-phone]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Mobile_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-description][font-size-phone]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_title_description', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 40px ',
				),
			)
		)
	);


	// Line height label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-description][line-height-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Description Line Height' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_form_title_description',
				'settings' => array(),
			)
		)
	);
	/* for pc*/
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-description][line-height]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Desktop_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-description][line-height]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_title_description', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 40px',
				),
			)
		)
	);
	/* for_tablet*/
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-description][line-height-tab]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Tab_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-description][line-height-tab]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_title_description', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 40px',
				),
			)
		)
	);


	/* for mobile*/
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-description][line-height-phone]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Mobile_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-description][line-height-phone]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_title_description', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 40px',
				),
			)
		)
	);

	// Font align style buttons.
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-description][text-align]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Text_Alignment_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-description][text-align]',
			array(
				'label'   => 'Description Font Alignment',
				'section' => 'gf_stla_form_id_form_title_description',
				'type'    => 'text_alignment',
				'choices' => $align_pos,
			)
		)
	);



	// Label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-description][margin-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Description Margin' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_form_title_description',
				'settings' => array(),
			)
		)
	);

	stla_margin_padding_controls( $wp_customize, $current_form_id, 'gf_stla_form_id_form_title_description', 'form-description', 'margin' );


	// Label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-description][padding-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Description Padding' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_form_title_description',
				'settings' => array(),
			)
		)
	);
	stla_margin_padding_controls( $wp_customize, $current_form_id, 'gf_stla_form_id_form_title_description', 'form-description', 'padding' );
