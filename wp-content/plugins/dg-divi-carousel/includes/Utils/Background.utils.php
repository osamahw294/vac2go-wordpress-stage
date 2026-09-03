<?php
namespace DIVIGEAR\UTILS;
if ( ! class_exists( 'ET_Builder_Element' ) ) {
	return;
}

trait BACKGROUND {
	/**
	 * add background field
	 */
	function add_bg_field( $args = [] ) {
		$default = [
			'label'           => '',
			'key'             => '',
			'toggle_slug'     => '',
			'sub_toggle'      => null,
			'tab_slug'        => '',
			'mobile_options'  => true,
			'hover'           => 'tabs',
			'color'           => true,
			'gradient'        => true,
			'image'           => true,
			'order_reverse'   => false,
			'show_if'         => null,
			'show_if_not'     => null,
			'prefix'          => 'Background',
			'suffix'          => 'background',
			'depends_show_if' => '',
			'sticky'          => false
		];
		$args    = wp_parse_args( $args, $default );
		$fields  = [];
		$key     = $args['key'];

		$_fields = [
			'label'               => sprintf( esc_html__( '%1$s', 'et_builder' ), $args['label'] ),
			'tab_slug'            => $args['tab_slug'],
			'toggle_slug'         => $args['toggle_slug'],
			'attr_suffix'         => 'dg',
			'type'                => 'composite',
			'hover'               => $args['hover'],
			'composite_type'      => 'default',
			'composite_structure' => [],
			'show_if'             => $args['show_if'],
			'show_if_not'         => $args['show_if_not']
		];

		if ( isset( $args['priority'] ) && $args['priority'] !== '' ) {
			$_fields['priority'] = $args['priority'];
		}
		if ( $args['sub_toggle'] !== '' ) {
			$_fields['sub_toggle'] = $args['sub_toggle'];
		}
		if ( $args['depends_show_if'] !== '' ) {
			$_fields['depends_show_if'] = $args['depends_show_if'];
		}


		$background_fields = [];

		if ( $args['color'] === true ) {
			$background_fields['color'] = [
				'icon'     => 'background-color',
				'controls' => [
					"{$key}_bgcolor" => [
						'label'   => esc_html__( $args['prefix'] . ' Color', 'et_builder' ),
						'type'    => 'color-alpha',
						'hover'   => $args['hover'],
						'sticky'  => $args['sticky'],
						'default' => array_key_exists( 'default_color', $args ) ? $args['default_color'] : '',
					],
				],
			];
		}

		if ( $args['gradient'] === true ) {
			$background_fields['color_gradient'] = [
				'icon'     => 'background-gradient',
				'controls' => [
					"{$key}_use_gradient"       => [
						'label'   => esc_html__( 'Use gradient ' . $args['suffix'], 'et_builder' ),
						'type'    => 'yes_no_button',
						'options' => [
							'on'  => esc_html__( 'On', 'et_builder' ),
							'off' => esc_html__( 'Off', 'et_builder' ),
						],
						'default' => 'off'
					],
					"{$key}_color_gradient_1"   => [
						'label'   => esc_html__( 'Select color', 'et_builder' ),
						'type'    => 'color-alpha',
						'default' => "#2b87da",
						'show_if' => [
							"{$key}_use_gradient" => 'on'
						],
						'hover'   => $args['hover']
					],
					"{$key}_color_gradient_2"   => [
						'label'   => esc_html__( 'Select color', 'et_builder' ),
						'type'    => 'color-alpha',
						'default' => "#29c4a9",
						'show_if' => [
							"{$key}_use_gradient" => 'on'
						],
						'hover'   => $args['hover']
					],
					"{$key}_gradient_type"      => [
						'label'   => esc_html__( 'Gradient Type', 'et_builder' ),
						'type'    => 'select',
						'options' => [
							'leniar' => esc_html__( 'Linear', 'et_builder' ),
							'radial' => esc_html__( 'Radial', 'et_builder' )
						],
						'default' => 'leniar',
						'show_if' => [
							"{$key}_use_gradient" => 'on'
						],
						'hover'   => $args['hover'],
					],
					"{$key}_radial_direction"   => [
						'label'   => esc_html__( 'Radial Direction', 'et_builder' ),
						'type'    => 'select',
						'options' => [
							'center'       => esc_html__( 'Center', 'et_builder' ),
							'top_left'     => esc_html__( 'Top Left', 'et_builder' ),
							'top'          => esc_html__( 'Top', 'et_builder' ),
							'top_right'    => esc_html__( 'Top Right', 'et_builder' ),
							'right'        => esc_html__( 'Right', 'et_builder' ),
							'bottom_right' => esc_html__( 'Bottom Right', 'et_builder' ),
							'bottom'       => esc_html__( 'Bottom', 'et_builder' ),
							'bottom_left'  => esc_html__( 'Bottom Left', 'et_builder' ),
							'left'         => esc_html__( 'Left', 'et_builder' ),
						],
						'default' => 'center',
						'show_if' => [
							"{$key}_use_gradient"  => 'on',
							"{$key}_gradient_type" => 'radial'
						],
						'hover'   => $args['hover'],
					],
					"{$key}_gradient_direction" => [
						'label'            => esc_html__( 'Gradient Direction', 'et_builder' ),
						'type'             => 'range',
						'default'          => '180deg',
						'default_on_front' => '',
						'default_unit'     => 'deg',
						'range_settings'   => [
							'min'  => '0',
							'max'  => '360',
							'step' => '1'
						],
						'show_if'          => [
							"{$key}_use_gradient" => 'on'
						],
						'show_if_not'      => [
							"{$key}_gradient_type" => 'radial'
						],
						'hover'            => $args['hover'],
					],
					"{$key}_start_position"     => [
						'label'   => esc_html__( 'Start Position', 'et_builder' ),
						'type'    => 'range',
						'default' => '0%',
						'show_if' => [
							"{$key}_use_gradient" => 'on'
						],
						'hover'   => $args['hover'],
					],
					"{$key}_end_position"       => [
						'label'   => esc_html__( 'End Position', 'et_builder' ),
						'type'    => 'range',
						'default' => '100%',
						'show_if' => [
							"{$key}_use_gradient" => 'on'
						],
						'hover'   => $args['hover'],
					]
				]
			];

			if ( $args['image'] === true ) {
				$background_fields['color_gradient']['controls']["{$key}_above_image"] = [
					'label'   => esc_html__( 'Place Gradient Above Background Image', 'et_builder' ),
					'type'    => 'yes_no_button',
					'options' => [
						'on'  => esc_html__( 'On', 'et_builder' ),
						'off' => esc_html__( 'Off', 'et_builder' ),
					],
					'show_if' => [
						"{$key}_use_gradient" => 'on'
					]
				];
			}

		}

		if ( $args['image'] === true ) {
			$background_fields['image'] = array(
				'icon'     => 'background-image',
				'controls' => array(
					"{$key}_background_image"          => [
						'label'              => esc_html__( 'Background Image', 'et_builder' ),
						'type'               => 'upload',
						'upload_button_text' => esc_attr__( 'Set Image', 'et_builder' ),
						'hover'              => $args['hover'],
					],
					"{$key}_background_image_size"     => [
						'label'   => esc_html__( 'Background Image Size', 'et_builder' ),
						'type'    => 'select',
						'options' => [
							'cover'       => esc_html__( 'Cover', 'et_builder' ),
							'fit'         => esc_html__( 'Fit', 'et_builder' ),
							'actual_size' => esc_html__( 'Actual Size', 'et_builder' ),
							'custom'      => esc_html__( 'Custom Size', 'et_builder' )
						],
						'default' => 'cover',
						'hover'   => $args['hover'],
					],
					"{$key}_size_width"                => [
						'label'            => esc_html__( 'Background Width', 'et_builder' ),
						'type'             => 'range',
						'default'          => '50%',
						'default_on_front' => '',
						'default_unit'     => '%',
						'range_settings'   => [
							'min'  => '0',
							'max'  => '100',
							'step' => '1'
						],
						'show_if'          => [
							"{$key}_background_image_size" => 'custom'
						],
						'hover'            => $args['hover'],
					],
					"{$key}_size_height"               => [
						'label'            => esc_html__( 'Background Height', 'et_builder' ),
						'type'             => 'range',
						'default'          => '50%',
						'default_on_front' => '',
						'default_unit'     => '%',
						'range_settings'   => [
							'min'  => '0',
							'max'  => '100',
							'step' => '1'
						],
						'show_if'          => [
							"{$key}_background_image_size" => 'custom'
						],
						'hover'            => $args['hover'],
					],
					"{$key}_size_width"                => [
						'label'            => esc_html__( 'Background Width', 'et_builder' ),
						'type'             => 'range',
						'default'          => '50%',
						'default_on_front' => '',
						'default_unit'     => '%',
						'range_settings'   => [
							'min'  => '0',
							'max'  => '100',
							'step' => '1'
						],
						'show_if'          => [
							"{$key}_background_image_size" => 'custom'
						],
						'hover'            => $args['hover'],
					],
					"{$key}_size_height"               => [
						'label'            => esc_html__( 'Background Height', 'et_builder' ),
						'type'             => 'range',
						'default'          => '50%',
						'default_on_front' => '',
						'default_unit'     => '%',
						'range_settings'   => [
							'min'  => '0',
							'max'  => '100',
							'step' => '1'
						],
						'show_if'          => [
							"{$key}_background_image_size" => 'custom'
						],
						'hover'            => $args['hover'],
					],
					"{$key}_background_image_position" => [
						'label'   => esc_html__( 'Background Image Position', 'et_builder' ),
						'type'    => 'select',
						'options' => [
							'top_left'      => esc_html__( 'Top Left', 'et_builder' ),
							'top_center'    => esc_html__( 'Top Center', 'et_builder' ),
							'top_right'     => esc_html__( 'Top Right', 'et_builder' ),
							'center_left'   => esc_html__( 'Center Left', 'et_builder' ),
							'center'        => esc_html__( 'Center', 'et_builder' ),
							'center_right'  => esc_html__( 'Center Right', 'et_builder' ),
							'bottom_left'   => esc_html__( 'Bottom Left', 'et_builder' ),
							'bottom_center' => esc_html__( 'Bottom Center', 'et_builder' ),
							'bottom_right'  => esc_html__( 'Bottom Right', 'et_builder' ),
							'custom'        => esc_html__( 'Custom Position', 'et_builder' )
						],
						'default' => 'center',
						'hover'   => $args['hover'],
					],
					"{$key}_position_horizontal"       => [
						'label'            => esc_html__( 'Horizontal Position', 'et_builder' ),
						'type'             => 'range',
						'default'          => '0px',
						'default_on_front' => '',
						'default_unit'     => 'px',
						'range_settings'   => [
							'min'  => '0',
							'max'  => '1000',
							'step' => '1'
						],
						'show_if'          => [
							"{$key}_background_image_position" => 'custom'
						],
						'hover'            => $args['hover'],
					],
					"{$key}_position_vertical"         => [
						'label'            => esc_html__( 'Vertical Position', 'et_builder' ),
						'type'             => 'range',
						'default'          => '0px',
						'default_on_front' => '',
						'default_unit'     => 'px',
						'range_settings'   => [
							'min'  => '0',
							'max'  => '1000',
							'step' => '1'
						],
						'show_if'          => [
							"{$key}_background_image_position" => 'custom'
						],
						'hover'            => $args['hover'],
					],
					"{$key}_position_horizontal"       => [
						'label'            => esc_html__( 'Horizontal Position', 'et_builder' ),
						'type'             => 'range',
						'default'          => '0px',
						'default_on_front' => '',
						'default_unit'     => 'px',
						'range_settings'   => [
							'min'  => '0',
							'max'  => '1000',
							'step' => '1'
						],
						'show_if'          => [
							"{$key}_background_image_position" => 'custom'
						],
						'hover'            => $args['hover'],
					],
					"{$key}_position_vertical"         => [
						'label'            => esc_html__( 'Vertical Position', 'et_builder' ),
						'type'             => 'range',
						'default'          => '0px',
						'default_on_front' => '',
						'default_unit'     => 'px',
						'range_settings'   => [
							'min'  => '0',
							'max'  => '1000',
							'step' => '1'
						],
						'show_if'          => [
							"{$key}_background_image_position" => 'custom'
						],
						'hover'            => $args['hover'],
					],
					"{$key}_background_image_repeat"   => [
						'label'   => esc_html__( 'Background Image Repeat', 'et_builder' ),
						'type'    => 'select',
						'options' => [
							'no_repeat' => esc_html__( 'No Repeat', 'et_builder' ),
							'repeat'    => esc_html__( 'Repeat', 'et_builder' ),
							'repeat_x'  => esc_html__( 'Repeat X (horizontal)', 'et_builder' ),
							'repeat_y'  => esc_html__( 'Repeat Y (vertical)', 'et_builder' ),
							'space'     => esc_html__( 'Space', 'et_builder' ),
							'round'     => esc_html__( 'Round', 'et_builder' ),
						],
						'default' => 'no_repeat',
						'hover'   => $args['hover'],
					]
				),
			);
		}
		if ( $args['order_reverse'] === true ) {
			$background_fields = array_reverse( $background_fields );
		}

		$_fields['composite_structure'] = $background_fields;

		$fields[ $args['key'] ] = $_fields;

		return $fields;
	}
}