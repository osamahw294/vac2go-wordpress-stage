<?php
/**
 * Addons controls.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$wp_customize->add_section(
	'gf_stla_form_id_addons',
	array(
		'title' => 'Addons',
		'panel' => 'gf_stla_panel',
	)
);

$wp_customize->add_setting(
	'gf_stla_form_id_[addons][addon-bundle]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Addon_Bundle_Custom_Control(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_[addons][addon-bundle]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Addon Bundle' , 'styles-and-layouts-for-gravity-forms'),
			'section' => 'gf_stla_form_id_addons',
		)
	)
);


$wp_customize->add_setting(
	'gf_stla_form_id_[addons][material-design]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Material_Design_Custom_Control(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_[addons][material-design]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Material Design' , 'styles-and-layouts-for-gravity-forms'),
			'section' => 'gf_stla_form_id_addons',
		)
	)
);



$wp_customize->add_setting(
	'gf_stla_form_id_[addons][theme-pack]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Themes_Pack_Custom_Control(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_[addons][theme-pack]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Theme Pack' , 'styles-and-layouts-for-gravity-forms'),
			'section' => 'gf_stla_form_id_addons',
		)
	)
);



$wp_customize->add_setting(
	'gf_stla_form_id_[addons][field-icons]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Field_Icons_Custom_Control(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_[addons][field-icons]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Field Icons' , 'styles-and-layouts-for-gravity-forms'),
			'section' => 'gf_stla_form_id_addons',
		)
	)
);

$wp_customize->add_setting(
	'gf_stla_form_id_[addons][tooltips]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Tooltips_Custom_Control(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_[addons][tooltips]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Tooltips' , 'styles-and-layouts-for-gravity-forms'),
			'section' => 'gf_stla_form_id_addons',
		)
	)
);


$wp_customize->add_setting(
	'gf_stla_form_id_[addons][widget-sidebar]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);



$wp_customize->add_setting(
	'gf_stla_form_id_[addons][custom-themes]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Custom_Themes_Custom_Control(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_[addons][custom-themes]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Custom Themes' , 'styles-and-layouts-for-gravity-forms'),
			'section' => 'gf_stla_form_id_addons',
		)
	)
);

$wp_customize->add_setting(
	'gf_stla_form_id_[addons][woocommerce-addon]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);



$wp_customize->add_setting(
	'gf_stla_form_id_[addons][more]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new More_Addons_Custom_Control(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_[addons][more]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'More Addons ' , 'styles-and-layouts-for-gravity-forms'),
			'section' => 'gf_stla_form_id_addons',
		)
	)
);

$wp_customize->add_setting(
	'gf_stla_form_id_[addons][customization-support]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Customization_Support_Custom_Control(
		$wp_customize, // WP_Customize_Manager.
		'gf_stla_form_id_[addons][customization-support]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Customization & Support' , 'styles-and-layouts-for-gravity-forms'),
			'section' => 'gf_stla_form_id_addons',
		)
	)
);
