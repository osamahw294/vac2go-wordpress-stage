<?php
if ( ! class_exists( 'ET_Builder_Element' ) ) {
	return;
}
trait DG_BACKGROUND {
    /**
     * add background field
     */
    function dg_add_bg_field( $args = array() )
    {
        $default    = array(
            'label'				=> '',
            'key'               => '',
            'toggle_slug'       => '',
            'sub_toggle'		=> null,
            'tab_slug'			=> '',
            'mobile_options'    => true,
            'hover'				=> 'tabs',
        );
        $args   = wp_parse_args( $args, $default );
        $fields = array();
        $key = $args['key'];

        $fields[$args['key']] = array(
            'label'               => sprintf(esc_html__('%1$s', 'et_builder'), $args['label']),
            'tab_slug'            => $args['tab_slug'],
            'toggle_slug'         => $args['toggle_slug'],
            'attr_suffix'         => 'dg',
            'type'                => 'composite',
            'composite_type'      => 'default',
            'composite_structure' => array(
                'color' => array(
                    'icon'     => 'background-color',
                    'controls' => array(
                        "{$key}_bgcolor" => array(
                            'label' => esc_html__( 'Background Color', 'et_builder' ),
                            'type'  => 'color-alpha',
                            'hover' => 'tabs',
                        ),
                    ),
                ),
                'color_gradient' => array(
                    'icon'     => 'background-gradient',
                    'controls' => array(
                        "{$key}_use_gradient" => array(
                            'label'           => esc_html__( 'Use gradient background', 'et_builder' ),
                            'type'            => 'yes_no_button', 
                            'options'           => array(
                                'on'  => esc_html__( 'On', 'et_builder' ),
                                'off' => esc_html__( 'Off', 'et_builder' ),
                            ),
                            'default'   => 'off'
                        ),
                        "{$key}_color_gradient_1" => array(
                            'label' => esc_html__( 'Select color', 'et_builder' ),
                            'type'  => 'color-alpha',
                            'default'   => "#2b87da",
                            'show_if' => array(
                                "{$key}_use_gradient" => 'on'
                            )
                        ),
                        "{$key}_color_gradient_2" => array(
                            'label' => esc_html__( 'Select color', 'et_builder' ),
                            'type'  => 'color-alpha',
                            'default'   => "#29c4a9",
                            'show_if' => array(
                                "{$key}_use_gradient" => 'on'
                            )
                        ),
                        "{$key}_gradient_type" => array(
                            'label' => esc_html__( 'Gradient Type', 'et_builder' ),
                            'type'  => 'select',
                            'options'         => array(
                                'leniar'    => esc_html__( 'Linear', 'et_builder' ),
                                'radial'    => esc_html__( 'Radial', 'et_builder' )
                            ),
                            'default'   => 'option_1',
                            'show_if' => array(
                                "{$key}_use_gradient" => 'on'
                            )
                        ),
                        "{$key}_radial_direction" => array(
                            'label' => esc_html__( 'Radial Direction', 'et_builder' ),
                            'type'  => 'select',
                            'options'         => array(
                                'center'    => esc_html__( 'Center', 'et_builder' ),
                                'top_left'    => esc_html__( 'Top Left', 'et_builder' ),
                                'top'    => esc_html__( 'Top', 'et_builder' ),
                                'top_right'    => esc_html__( 'Top Right', 'et_builder' ),
                                'right'    => esc_html__( 'Right', 'et_builder' ),
                                'bottom_right'    => esc_html__( 'Bottom Right', 'et_builder' ),
                                'bottom'    => esc_html__( 'Bottom', 'et_builder' ),
                                'bottom_left'    => esc_html__( 'Bottom Left', 'et_builder' ),
                                'left'    => esc_html__( 'Left', 'et_builder' ),
                            ),
                            'default'   => 'center',
                            'show_if' => array(
                                "{$key}_use_gradient" => 'on',
                                "{$key}_gradient_type" => 'radial'
                            )
                        ),
                        "{$key}_gradient_direction" => array(
                            'label'             => esc_html__( 'Gradient Direction', 'et_builder' ),
                            'type'              => 'range', 
                            'default'           => '180deg',
                            'default_on_front'  => '',
                            'default_unit'      => 'deg',
                            'range_settings'         => array(
                                'min'    => '0',
                                'max'    => '360',
                                'step'    => '1'
                            ),
                            'show_if'           => array(
                                "{$key}_use_gradient" => 'on'
                            ),
                            'show_if_not'       => array(
                                "{$key}_gradient_type" => 'radial'
                            )
                        ),
                        "{$key}_start_position" => array(
                            'label'           => esc_html__( 'Start Position', 'et_builder' ),
                            'type'            => 'range', 
                            'default'   => '0%',
                            'show_if' => array(
                                "{$key}_use_gradient" => 'on'
                            )
                        ),
                        "{$key}_end_position" => array(
                            'label'           => esc_html__( 'End Position', 'et_builder' ),
                            'type'            => 'range', 
                            'default'   => '100%',
                            'show_if' => array(
                                "{$key}_use_gradient" => 'on'
                            )
                        ),
                        "{$key}_above_image" => array(
                            'label'           => esc_html__( 'Place Gradiet Above Background Image', 'et_builder' ),
                            'type'            => 'yes_no_button', 
                            'options'           => array(
                                'on'  => esc_html__( 'On', 'et_builder' ),
                                'off' => esc_html__( 'Off', 'et_builder' ),
                            ),
                            'show_if' => array(
                                "{$key}_use_gradient" => 'on'
                            )
                        ),
                    ),
                ),
                'image' => array(
                    'icon'     => 'background-image',
                    'controls' => array(
                        "{$key}_background_image" => array(
                            'label' => esc_html__( 'Background Image', 'et_builder' ),
                            'type'  => 'upload',
                            'upload_button_text' => esc_attr__( 'Set Image', 'et_builder' )
                        ),
                        "{$key}_background_image_size" => array(
                            'label' => esc_html__( 'Background Image Size', 'et_builder' ),
                            'type'  => 'select',
                            'options'         => array(
                                'cover'    => esc_html__( 'Cover', 'et_builder' ),
                                'fit'    => esc_html__( 'Fit', 'et_builder' ),
                                'actual_size'    => esc_html__( 'Actual Size', 'et_builder' )
                            ),
                            'default'   => 'cover',
                            'hover' => 'tabs',
                        ),
                        "{$key}_background_image_position" => array(
                            'label' => esc_html__( 'Background Image Position', 'et_builder' ),
                            'type'  => 'select',
                            'options'         => array(
                                'top_left'    => esc_html__( 'Top Left', 'et_builder' ),
                                'top_center'    => esc_html__( 'Top Center', 'et_builder' ),
                                'top_right'    => esc_html__( 'Top Right', 'et_builder' ),
                                'center_left'    => esc_html__( 'Center Left', 'et_builder' ),
                                'center'    => esc_html__( 'Center', 'et_builder' ),
                                'center_right'    => esc_html__( 'Center Right', 'et_builder' ),
                                'bottom_left'    => esc_html__( 'Bottom Left', 'et_builder' ),
                                'bottom_center'    => esc_html__( 'Bottom Center', 'et_builder' ),
                                'bottom_right'    => esc_html__( 'Bottom Right', 'et_builder' ),
                            ),
                            'default'   => 'center',
                            'hover' => 'tabs',
                        ),
                        "{$key}_background_image_repeat" => array(
                            'label' => esc_html__( 'Background Image Repeat', 'et_builder' ),
                            'type'  => 'select',
                            'options'         => array(
                                'no_repeat'    => esc_html__( 'No Repeat', 'et_builder' ),
                                'repeat'    => esc_html__( 'Repeat', 'et_builder' ),
                                'repeat_x'    => esc_html__( 'Repeat X (horizontal)', 'et_builder' ),
                                'repeat_y'    => esc_html__( 'Repeat Y (vertical)', 'et_builder' ),
                                'space'    => esc_html__( 'Space', 'et_builder' ),
                                'round'    => esc_html__( 'Round', 'et_builder' ),
                            ),
                            'default'   => 'no_repeat',
                            'hover' => 'tabs',
                        )
                    ),
                )
                
            ),
        );

        return $fields;
    }
}