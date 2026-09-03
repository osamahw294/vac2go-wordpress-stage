<?php
/**
 * Text field controls for customizer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wp_customize->add_section(
	'gf_stla_form_id_text_fields',
	array(
		'title' => 'Text Fields',
		'panel' => 'gf_stla_panel',
	)
);

// label.
$wp_customize->add_control(
	new WP_Customize_Label_Only(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_' . $current_form_id . '[text-fields][max-width-label-only]', // Setting id.
		array( // Args, including any custom ones.
			'label'    => __( 'Width' , 'styles-and-layouts-for-gravity-forms'),
			'section'  => 'gf_stla_form_id_text_fields',
			'settings' => array(),
		)
	)
);

/* for pc */
$wp_customize->add_setting(
	'gf_stla_form_id_' . $current_form_id . '[text-fields][max-width]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

	$wp_customize->add_control(
		new Stla_Desktop_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][max-width]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 400px',
				),
			)
		)
	);

	/* for tab */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][max-width-tab]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Tab_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][max-width-tab]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 400px',
				),
			)
		)
	);

	/* for mobile */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][max-width-phone]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Mobile_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][max-width-phone]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 400px',
				),
			)
		)
	);

	// Height label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[text-fields][height-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Height' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_text_fields',
				'settings' => array(),
			)
		)
	);

	/* for pc */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][height]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Desktop_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][height]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 50px ',
				),
			)
		)
	);
	/* for tablet */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][height-tab]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Tab_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][height-tab]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 50px ',
				),
			)
		)
	);
	/* for mobile */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][height-phone]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Mobile_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][height-phone]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 50px ',
				),
			)
		)
	);

	// font style buttons.
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][font-style]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Font_Style_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][font-style]',
			array(
				'label'   => 'Font Style',
				'section' => 'gf_stla_form_id_text_fields',
				'type'    => 'font_style',
				'choices' => $font_style_choices,
			)
		)
	);

	/* Start of Section */

	// label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[text-fields][font-size-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Font Size' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_text_fields',
				'settings' => array(),
			)
		)
	);
	/* for pc */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][font-size]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Desktop_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][font-size]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 40px',
				),
			)
		)
	);
	/* for tablet */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][font-size-tab]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Tab_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][font-size-tab]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 40px',
				),
			)
		)
	);

	/* for mobile */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][font-size-phone]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Mobile_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[text-fields][font-size-phone]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Ex: 40px',
				),
			)
		)
	);

	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][font-color]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[text-fields][font-color]', // Setting id.
			array( // Args, including any custom ones.
				'label'   => __( 'Font Color' , 'styles-and-layouts-for-gravity-forms'),
				'section' => 'gf_stla_form_id_text_fields',
			)
		)
	);


	/* Start of Section */
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[text-fields][border-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Border' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_text_fields',
				'settings' => array(),
			)
		)
	);

	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][border-size]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][border-size]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
			'label'       => __( 'Size' , 'styles-and-layouts-for-gravity-forms'),
			'input_attrs' => array(
				'placeholder' => 'Example: 4px or 10%',
			),
		)
	);

	/* Start of Section */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][border-type]',
		array(
			'default'   => 'solid',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][border-type]',
		array(
			'type'     => 'select',
			'priority' => 10, // Within the section.
			'section'  => 'gf_stla_form_id_text_fields', // Required, core or custom.
			'label'    => __( 'Type' , 'styles-and-layouts-for-gravity-forms'),
			'choices'  => $border_types,
		)
	);

	/* Start of Section */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][border-radius]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][border-radius]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'gf_stla_form_id_text_fields', // Required, core or custom.
			'label'       => __( 'Radius' , 'styles-and-layouts-for-gravity-forms'),
			'input_attrs' => array(
				'placeholder' => 'Example: 4px or 10%',
			),
		)
	);
	/* Start of Section */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][border-color]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[text-fields][border-color]', // Setting id.
			array( // Args, including any custom ones.
				'label'   => __( 'Border Color' , 'styles-and-layouts-for-gravity-forms'),
				'section' => 'gf_stla_form_id_text_fields',
			)
		)
	);




	/* Start of Section */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[text-fields][background-color]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[text-fields][background-color]', // Setting id.
			array( // Args, including any custom ones.
				'label'   => __( 'Background Color' , 'styles-and-layouts-for-gravity-forms'),
				'section' => 'gf_stla_form_id_text_fields',
			)
		)
	);



	// Start of Section
	// Label
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[text-fields][margin-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( ' Margin' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_text_fields',
				'settings' => array(),
			)
		)
	);

	stla_margin_padding_controls( $wp_customize, $current_form_id, 'gf_stla_form_id_text_fields', 'text-fields', 'margin' );

	// Start of Section
	// Label
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager
			'gf_stla_form_id_' . $current_form_id . '[text-fields][padding-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Padding' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_text_fields',
				'settings' => array(),
			)
		)
	);
	stla_margin_padding_controls( $wp_customize, $current_form_id, 'gf_stla_form_id_text_fields', 'text-fields', 'padding' );
