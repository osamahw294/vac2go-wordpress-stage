<?php
/**
 * Form wrapper controls in customizer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wp_customize->add_section(
	'gf_stla_form_id_form_wrapper',
	array(
		'title' => 'Form Wrapper',
		'panel' => 'gf_stla_panel',
	)
);

// Label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][max-width-label-only]', // Setting id
			array( // Args, including any custom ones.
				'label'    => __( 'Width' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_form_wrapper',
				'settings' => array(),
			)
		)
	);

	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][max-width]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Desktop_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][max-width]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Example: 400px or 90%',
				),
			)
		)
	);

	// Tablet.
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][max-width-tab]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Tab_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][max-width-tab]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Example: 400px or 90%',
				),
			)
		)
	);

	// Mobile.
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][max-width-phone]',
		array(
			'default'   => '',
			'transport' => 'refresh',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new Stla_Mobile_Text_Input_Option(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][max-width-phone]',
			array(
				'type'        => 'text',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
				'label'       => '',
				'input_attrs' => array(
					'placeholder' => 'Example: 400px or 90%',
				),
			)
		)
	);

	/* Start of Section */

	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][font]',
		array(
			'default'   => 'Default',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][font]',
		array(
			'type'     => 'select',
			'priority' => 10, // Within the section.
			'section'  => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
			'label'    => __( 'Font Family' , 'styles-and-layouts-for-gravity-forms'),
			'choices'  => $font_collection,
		)
	);

	/* Start of Section */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-type]',
		array(
			'default'   => 'color',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-type]',
		array(
			'type'     => 'radio',
			'priority' => 10, // Within the section.
			'section'  => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
			'label'    => __( 'Background Type' , 'styles-and-layouts-for-gravity-forms'),
			'choices'  => array(
				'color'    => 'Color',
				'image'    => 'Image',
				'gradient' => 'Gradient',
			),
		)
	);

	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-color]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-color]', // Setting id.
			array( // Args, including any custom ones.
				'label'   => __( 'Background Color' , 'styles-and-layouts-for-gravity-forms'),
				'section' => 'gf_stla_form_id_form_wrapper',
				'title'   => 'Select Color',
			)
		)
	);

	/* Start of Section */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-image]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-image]',
			array(
				'label'    => 'Background Image',
				'section'  => 'gf_stla_form_id_form_wrapper',
				'settings' => 'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-image]',
			)
		)
	);



	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][gradient-direction]',
		array(
			'default'   => 'left',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][gradient-direction]',
		array(
			'type'     => 'select',
			'priority' => 10, // Within the section.
			'section'  => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
			'label'    => __( 'Gradient Direction' , 'styles-and-layouts-for-gravity-forms'),
			'choices'  => array(
				'left'     => 'Left to Right',
				'top'      => 'Top to Bottom',
				'diagonal' => 'Diagonal',
			),
		)
	);

	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][gradient-color-1]',
		array(
			'default'   => '#ffffff',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][gradient-color-1]', // Setting id.
			array( // Args, including any custom ones.
				'label'   => __( 'Gradient Color 1' , 'styles-and-layouts-for-gravity-forms'),
				'section' => 'gf_stla_form_id_form_wrapper',

			)
		)
	);

	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][gradient-color-2]',
		array(
			'default'   => '#ffffff',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][gradient-color-2]', // Setting id.
			array( // Args, including any custom ones.
				'label'   => __( 'Gradient Color 2' , 'styles-and-layouts-for-gravity-forms'),
				'section' => 'gf_stla_form_id_form_wrapper',

			)
		)
	);



	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-opacity]',
		array(
			'default'   => 1,
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-opacity]',
		array(
			'type'        => 'range',
			'priority'    => 10, // Within the section.
			'section'     => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
			'label'       => __( 'Opacity' , 'styles-and-layouts-for-gravity-forms'),
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 1,
				'step' => 0.05,
			),
		)
	);

	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-opacity]',
		array(
			'default'   => 1,
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);


	$wp_customize->add_control(
		new Stla_Customize_Control_Range_Slider(
			$wp_customize,
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][background-opacity]',
			array(
				'type'        => 'rangeslider',
				'priority'    => 10, // Within the section.
				'section'     => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
				'label'       => __( 'Opacity' , 'styles-and-layouts-for-gravity-forms'),
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 1,
					'step' => .05,
				),
			)
		)
	);


	// Label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][border-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Border' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_form_wrapper',
				'settings' => array(),
			)
		)
	);

	/* Start of Section */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][border-size]',
		array(
			'default'   => '0',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][border-size]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
			'label'       => __( 'Size' , 'styles-and-layouts-for-gravity-forms'),
			'input_attrs' => array(
				'placeholder' => 'Example: 4px or 10%',
			),
		)
	);

	/* Start of Section */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][border-type]',
		array(
			'default'   => 'solid',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][border-type]',
		array(
			'type'     => 'select',
			'priority' => 10, // Within the section.
			'section'  => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
			'label'    => __( 'Type' , 'styles-and-layouts-for-gravity-forms'),
			'choices'  => $border_types,
		)
	);

	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][border-radius]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][border-radius]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'gf_stla_form_id_form_wrapper', // Required, core or custom.
			'label'       => __( 'Radius' , 'styles-and-layouts-for-gravity-forms'),
			'input_attrs' => array(
				'placeholder' => 'Example: 4px or 10%',
			),
		)
	);


	/* Start of Section */
	$wp_customize->add_setting(
		'gf_stla_form_id_' . $current_form_id . '[form-wrapper][border-color]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][border-color]', // Setting id.
			array( // Args, including any custom ones.
				'label'   => __( 'Border Color' , 'styles-and-layouts-for-gravity-forms'),
				'section' => 'gf_stla_form_id_form_wrapper',
			)
		)
	);

	// Start of Section
	// Label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][margin-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Margin' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_form_wrapper',
				'settings' => array(),
			)
		)
	);

	stla_margin_padding_controls( $wp_customize, $current_form_id, 'gf_stla_form_id_form_wrapper', 'form-wrapper', 'margin' );

	// Start of Section
	// Label.
	$wp_customize->add_control(
		new WP_Customize_Label_Only(
			$wp_customize, // WP_Customize_Manager.
			'gf_stla_form_id_' . $current_form_id . '[form-wrapper][padding-label-only]', // Setting id.
			array( // Args, including any custom ones.
				'label'    => __( 'Padding' , 'styles-and-layouts-for-gravity-forms'),
				'section'  => 'gf_stla_form_id_form_wrapper',
				'settings' => array(),
			)
		)
	);
	stla_margin_padding_controls( $wp_customize, $current_form_id, 'gf_stla_form_id_form_wrapper', 'form-wrapper', 'padding' );
