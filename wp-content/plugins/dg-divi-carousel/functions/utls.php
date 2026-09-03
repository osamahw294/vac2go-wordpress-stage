<?php
if ( ! class_exists( 'ET_Builder_Element' ) ) {
	return;
}
require_once ( DICA_MAIN_DIR . '/functions/background.php');
trait Dg_utls {
    use DG_Background;
    /**
     * Add margin and padding fields
     */
    function dg_margin_padding(&$fields, $options, $type ) {
        $key = $options['key'] . '_' . $type;
 
        $fields[$key] = array(
            'label'				=> sprintf(esc_html__('%1$s %2$s', 'et_builder'), $options['title'], $type),
            'type'				=> 'custom_margin',
            'toggle_slug'       => $options['toggle_slug'],
            'sub_toggle'		=> $options['sub_toggle'],
            'tab_slug'			=> 'advanced',
            'mobile_options'    => true,
            'hover'				=> 'tabs',
            'priority' 			=> $options['priority'],
        );
        $fields[$key . '_tablet'] = array(
            'type'            	=> 'skip',
            'tab_slug'        	=> 'advanced',
            'toggle_slug'		=> $options['toggle_slug'],
            'sub_toggle'		=> $options['sub_toggle']
        );
        $fields[$key.'_phone'] = array(
            'type'            	=> 'skip',
            'tab_slug'        	=> 'advanced',
            'toggle_slug'		=> $options['toggle_slug'],
            'sub_toggle'		=> $options['sub_toggle']
        );
        $fields[$key.'_last_edited'] = array(
            'type'            	=> 'skip',
            'tab_slug'        	=> 'advanced',
            'toggle_slug'		=> $options['toggle_slug'],
            'sub_toggle'		=> $options['sub_toggle']
        );
    }
    function add_margin_padding( $options = array() ) {
        $margin_padding = array();
        $default = array(
            'title'         => '',
            'key'           => '',
            'toggle_slug'   => '',
            'sub_toggle'    => null,
            'option'        => 'both',
            'priority'      => 30
        );
        $args = wp_parse_args( $options, $default );

        if ( $args['option'] === 'both' || $args['option'] === 'margin' ) {
            $this->dg_margin_padding($margin_padding, $args, 'margin');
        }
        if ( $args['option'] === 'both' || $args['option'] === 'padding' ) {
            $this->dg_margin_padding($margin_padding, $args, 'padding');
        }
        return $margin_padding;
    }

    /**
     * add max-width field with alignment
     */
    function dg_add_max_width($options = array()){
        $default = array(
            'key'                   => '',
            'toggle_slug'           => '',
            'sub_toggle'            => null,
            'alignment'             => false,
            'priority'              => 30,
            'tab_slug'              => 'general',
            'show_if'               => array(),
            'alignment_show_not'    => array()
        );
        $args = wp_parse_args( $options, $default );
        extract($args); // phpcs:ignore WordPress.PHP.DontExtract
        $fields = array();
        $max_width = $key . '_maxwidth';
        $fields[$max_width] = array(
            'label'             => esc_html__( 'Max Width', 'et_builder' ),
            'type'              => 'range',
            'toggle_slug'       => $toggle_slug,
            'sub_toggle'        => $sub_toggle,
            'tab_slug'          => $tab_slug,
            'default'           => '100%',
            'default_unit'      => '%',
            'default_on_front'  => '100%',
            'hover'             => 'tabs',
            'responsive'        => true,
            'mobile_options'    => true,
            'range_settings'    => array(
                'min'  => '1',
                'max'  => '100',
                'step' => '1',
            ),
        );
        if (!empty($show_if)) {
            $fields[$max_width]['show_if'] = $show_if;
        }
        if ($alignment === true) {
            $alignment_key = $key . '_alignment';
            $fields[$alignment_key] = array(
                'label'             => esc_html__( 'Alignment', 'et_builder' ),
                'type'              => 'text_align',
                'toggle_slug'       => $toggle_slug,
                'sub_toggle'        => $sub_toggle,
                'tab_slug'          => $tab_slug,
                'mobile_options'   => true,
                'options'           =>  et_builder_get_text_orientation_options( array( 'justified' ) ),
            );
            if (!empty($show_if)) {
                $fields[$alignment_key]['show_if'] = $show_if;
            }
        }

        return $fields;
    }
    /**
     * add text clip options
     */
    function dg_text_clip($options = array()) {
        $default = array(
            'key'                   => '',
            'toggle_slug'           => '',
            'sub_toggle'            => null,
            'priority'              => 30,
            'tab_slug'              => 'general'
        );
        $args = wp_parse_args( $options, $default );
        extract($args); // phpcs:ignore WordPress.PHP.DontExtract
        $fields = array();
        $fields[$key . '_enable_clip'] = array(
            'label'             => esc_html__( 'Enable Clip', 'et_builder' ),
            'type'              => 'yes_no_button',
            'options'           => array(
                'off' => esc_html__( 'No', 'et_builder' ),
                'on'  => esc_html__( 'Yes', 'et_builder' ),
            ),
            'default'           => 'off',
            'toggle_slug'       => $toggle_slug,
            'tab_slug'          => $tab_slug
        );
        $fields[$key . '_enable_bg_clip'] = array(
            'label'             => esc_html__( 'Enable Background Clip', 'et_builder' ),
            'type'              => 'yes_no_button',
            'options'           => array(
                'off' => esc_html__( 'No', 'et_builder' ),
                'on'  => esc_html__( 'Yes', 'et_builder' ),
            ),
            'default'           => 'off',
            'toggle_slug'       => $toggle_slug,
            'tab_slug'          => $tab_slug,
            'show_if'           => array(
                $key . '_enable_clip'       => 'on'
            )
        );
        $fields[$key . '_fill_color'] = array(
            'label'             => esc_html__( 'Fill Color', 'et_builder' ),
            'type'              => 'color-alpha',
            'toggle_slug'       => $toggle_slug,
            'tab_slug'          => $tab_slug,
            'hover'             => 'tabs',
            'default'           => 'rgba(255,255,255,0)',
            'show_if'           => array(
                $key . '_enable_clip'       => 'on'
            )
        );
        $fields[$key . '_stroke_color'] = array(
            'label'             => esc_html__( 'Stroke Color', 'et_builder' ),
            'type'              => 'color-alpha',
            'toggle_slug'       => $toggle_slug,
            'tab_slug'          => $tab_slug,
            'hover'             => 'tabs',
            'show_if'           => array(
                $key . '_enable_clip'       => 'on'
            )
        );
        $fields[$key . '_stroke_width'] = array(
            'label'             => esc_html__( 'Stroke Width', 'et_builder' ),
            'type'              => 'range',
            'toggle_slug'       => $toggle_slug,
            'tab_slug'          => $tab_slug,
            'default'           => '1px',
            'hover'             => 'tabs',
            'mobile_options'    => true,
            'default_unit'      => 'px',
            'default_on_front'  => '',
            'range_settings'    => array(
                'min'  => '1',
                'max'  => '100',
                'step' => '1',
            ),
            'show_if'           => array(
                $key . '_enable_clip'       => 'on'
            )
        );
        return $fields;
    }
    function dg_process_text_clip($options = array()) {
        $default = array(
            'render_slug'       => '',
            'slug'              => '',
            'selector'          => '',
            'hover'             => '',
            'important'         => true
        );
        $options        = wp_parse_args( $options, $default );
        extract($options); // phpcs:ignore WordPress.PHP.DontExtract
        if ($this->props[$slug . '_enable_clip'] === 'on') {
            $this->dg_process_color(array(
                'render_slug'       => $render_slug,
                'slug'              => $slug.'_fill_color',
                'type'              => '-webkit-text-fill-color',
                'selector'          => $selector,
                'hover'             => $hover
            ));
            $this->dg_process_color(array(
                'render_slug'       => $render_slug,
                'slug'              => $slug.'_stroke_color',
                'type'              => '-webkit-text-stroke-color',
                'selector'          => $selector,
                'hover'             => $hover
            ));
            $this->apply_single_value(array(
                'render_slug'       => $render_slug,
                'slug'              => $slug.'_stroke_width',
                'type'              => '-webkit-text-stroke-width',
                'selector'          => $selector,
                'unit'              => 'px',
                'hover'             => $hover,
                'default'           => '1'
            ));
            if ($this->props[$slug . '_enable_bg_clip'] === 'on') {
                ET_Builder_Element::set_style($render_slug, array(
                    'selector' => $selector,
                    'declaration' => '-webkit-background-clip: text;'
                ));
            }
        }
    }
    /**
     * Process max-width and alignment values
     */
    function dg_process_maxwidth($options = array()) {
        $default = array(
            'render_slug'       => '',
            'slug'              => '',
            'selector'          => '',
            'hover'             => '',
            'alignment'         => false,
            'important'         => true
        );
        $options        = wp_parse_args( $options, $default );
        extract($options); // phpcs:ignore WordPress.PHP.DontExtract
        $max_width = $slug . '_maxwidth';
        $desktop = $this->props[$max_width];
        $tablet = $this->dg_check_values($desktop, $this->props[$max_width.'_tablet']);
        $phone = $this->dg_check_values($desktop, $this->props[$max_width.'_phone']);

        ET_Builder_Element::set_style($render_slug, array(
            'selector' => $selector,
            'declaration' => sprintf('max-width:%1$s;', $desktop),
        ));
        ET_Builder_Element::set_style($render_slug, array(
            'selector' => $selector,
            'declaration' => sprintf('max-width:%1$s;', $tablet),
            'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
        ));
        ET_Builder_Element::set_style($render_slug, array(
            'selector' => $selector,
            'declaration' => sprintf('max-width:%1$s;', $phone),
            'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
        ));

        if ($alignment === true) {
            $align = $slug . '_alignment';
            $desktop_align = $this->props[$align];
            $tablet_align = $this->dg_check_values($desktop_align, $this->props[$align.'_tablet']);
            $phone_align = $this->dg_check_values($desktop_align, $this->props[$align.'_phone']);
            ET_Builder_Element::set_style($render_slug, array(
                'selector' => $selector,
                'declaration' => sprintf('%1$s', $this->dg_block_align($desktop_align)),
            ));
            ET_Builder_Element::set_style($render_slug, array(
                'selector' => $selector,
                'declaration' => sprintf('%1$s', $this->dg_block_align($tablet_align)),
                'media_query' => ET_Builder_Element::get_media_query('max_width_980')
            ));
            ET_Builder_Element::set_style($render_slug, array(
                'selector' => $selector,
                'declaration' => sprintf('%1$s', $this->dg_block_align($phone_align)),
                'media_query' => ET_Builder_Element::get_media_query('max_width_767')
            ));
        }
        // hover
        if (et_builder_is_hover_enabled( $max_width, $this->props ) && isset($this->props[$max_width.'__hover'])) {
            $hover_value = $this->props[$max_width.'__hover'];
            ET_Builder_Element::set_style($render_slug, array(
                'selector' => $hover,
                'declaration' => sprintf('max-width:%1$s;', $hover_value),
            ));
        }
    }
    /**
     * align center with margin
     */
    function dg_block_align($align) {
        if ($align === 'center') {
            return 'margin-left: auto; margin-right: auto;';
        } else if ($align === 'right') {
            return 'margin-left: auto; margin-right: 0;';
        } else if ($align === 'left') {
            return 'margin-right: auto; margin-left: 0;';
        }
    }
    /**
     * Checking values
     */
    function dg_check_values($desktop, $other){
        return isset($other) && '' !== $other ? $other : $desktop;
    }
    /**
     * Check the integer values
     */
    function dg_get_div_value($arg) {
        $value = intval($arg) / 2;
        $unit = str_replace(intval($arg), "", $arg);
        return $value . $unit; 
    }

    /**
     * Process Margin & Padding styles
     */
    function set_margin_padding_styles($options = array()) {
        $default = array(
            'module'            => '',
            'render_slug'       => '',
            'slug'              => '',
            'type'              => '',
            'selector'          => '',
            'hover'             => '',
            'important'         => true
        );
        $options        = wp_parse_args( $options, $default );
        extract($options); // phpcs:ignore WordPress.PHP.DontExtract
        $module = $this;
		$desktop 		= $module->props[$slug];
		$tablet 		= $module->props[$slug.'_tablet'];
        $phone 			= $module->props[$slug.'_phone'];
        
        if (class_exists('ET_Builder_Element')) {
            if(isset($desktop) && !empty($desktop)) {
                ET_Builder_Element::set_style($render_slug, array(
                    'selector' => $options['selector'],
                    'declaration' => et_builder_get_element_style_css($desktop, 
                        $type, $important),
                ));
            }
            if (isset($tablet) && !empty($tablet)) {
                ET_Builder_Element::set_style($render_slug, array(
                    'selector' => $options['selector'],
                    'declaration' => et_builder_get_element_style_css($tablet, 
                        $type, $important),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ));
            }
            if (isset($phone) && !empty($phone)) {
                ET_Builder_Element::set_style($render_slug, array(
                    'selector' => $options['selector'],
                    'declaration' => et_builder_get_element_style_css($phone, 
                        $type, $important),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ));
            }
			if (et_builder_is_hover_enabled( $slug, $module->props ) && isset($module->props[$slug.'__hover'])) {
				$hover = $module->props[$slug.'__hover'];
				ET_Builder_Element::set_style($render_slug, array(
					'selector' => $options['hover'],
                    'declaration' => et_builder_get_element_style_css($hover, 
                        $type, $important),
				));
			}
        }
    }

    /**
     * Process string attr
     */
    function process_string_attr($options = array()) {
        $default = array(
            'render_slug'       => '',
            'slug'              => '',
            'type'              => '',
            'selector'          => '',
            'hover'             => '',
            'important'         => false,
            'default'           => ''
        );
        $options        = wp_parse_args( $options, $default );
        extract($options); // phpcs:ignore WordPress.PHP.DontExtract
        $desktop  =  !empty($this->props[$slug]) ? $this->props[$slug] : $default;
        $tablet   =  !empty($this->props[$slug.'_tablet']) ?$this->props[$slug.'_tablet'] : $desktop;
        $phone   =  !empty($this->props[$slug.'_phone']) ? $this->props[$slug.'_phone'] : $tablet;
        $important_opt = $important === true ? '!important' : '';

        ET_Builder_Element::set_style($render_slug, array(
            'selector' => $selector,
            'declaration' => sprintf('%1$s:%2$s %3$s;', $type, $desktop, $important_opt),
        ));
        ET_Builder_Element::set_style($render_slug, array(
            'selector' => $selector,
            'declaration' => sprintf('%1$s:%2$s %3$s;', $type, $tablet,$important_opt),
            'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
        ));
        ET_Builder_Element::set_style($render_slug, array(
            'selector' => $selector,
            'declaration' => sprintf('%1$s:%2$s %3$s;', $type, $phone,$important_opt),
            'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
        ));
    }
    /**
     * Process single value
     */
    function apply_single_value($options = array()) {

        $default = array(
            'module'            => '',
            'render_slug'       => '',
            'slug'              => '',
            'type'              => '',
            'selector'          => '',
            'unit'              => '%',
            'hover'             => '',
            'decrease'          => false,
            'addition'          => true,
            'important'         => true,
            'default'           => '14'
        );
        $options        = wp_parse_args( $options, $default );
        extract($options); // phpcs:ignore WordPress.PHP.DontExtract
        $module = $this;
        $unit_value = !empty(str_replace(intval($module->props[$slug]), "", $module->props[$slug])) ? str_replace(intval($module->props[$slug]), "", $module->props[$slug]) : $unit;
        $unit_value_tab = str_replace(intval($module->props[$slug.'_tablet']), "", $module->props[$slug.'_tablet']) !== '' ? 
            str_replace(intval($module->props[$slug.'_tablet']), "", $module->props[$slug.'_tablet']) : $unit_value;
        $unit_value_ph = str_replace(intval($module->props[$slug.'_phone']), "", $module->props[$slug.'_phone']) !== '' ? 
            str_replace(intval($module->props[$slug.'_phone']), "", $module->props[$slug.'_phone']) : $unit_value_tab;

        $desktop_value  =  !empty($module->props[$slug]) ? $module->props[$slug] : $default;
        $tablet_value   =  !empty($module->props[$slug.'_tablet']) ?$module->props[$slug.'_tablet'] : $desktop_value;
        $mobile_value   =  !empty($module->props[$slug.'_phone']) ? $module->props[$slug.'_phone'] : $tablet_value;

		$desktop 	= $decrease === false ? intval($desktop_value) : 100 - intval($desktop_value);
		$tablet 	= $decrease === false ? intval($tablet_value) : 100 - intval($tablet_value);
		$phone 		= $decrease === false ? intval($mobile_value) : 100 - intval($mobile_value);
		$negative   = $addition == false ? '-' : '';

		$desktop    .= $unit_value;
		$tablet     .= $unit_value_tab;
		$phone      .= $unit_value_ph;
		// $desktop    .= $unit;
		// $tablet     .= $unit;
		// $phone      .= $unit;

		if(isset($desktop) && !empty($desktop)) {
			ET_Builder_Element::set_style($render_slug, array(
				'selector' => $selector,
				'declaration' => sprintf('%1$s:%2$s;', $type, $desktop, $negative),
			));
		}
		if (isset($tablet) && !empty($tablet)) {
			ET_Builder_Element::set_style($render_slug, array(
				'selector' => $selector,
				'declaration' => sprintf('%1$s:%3$s%2$s !important;', $type, $tablet,$negative),
				'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
			));
		}
		if (isset($phone) && !empty($phone)) {
			ET_Builder_Element::set_style($render_slug, array(
				'selector' => $selector,
				'declaration' => sprintf('%1$s:%3$s%2$s !important;', $type, $phone,$negative),
				'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
			));
        }
        if (et_builder_is_hover_enabled( $slug, $module->props ) && isset($module->props[$slug.'__hover'])) {
            $hover_value = $module->props[$slug.'__hover'];
            if ( !empty($hover_value)) {
                $hover_value 	= $decrease === false ? intval($hover_value) : 100 - intval($hover_value) ;
                $hover_value .= $unit_value;
                ET_Builder_Element::set_style($render_slug, array(
                    'selector' => $options['hover'],
                    'declaration' => sprintf('%1$s:%2$s %3$s;', $type, $hover_value, $negative),
                ));
            }
            
        }
    }

    /**
     * Process color
     */
    function dg_process_color( $options = array() ) {
        $default = array(
            'module'            => '',
            'render_slug'       => '',
            'slug'              => '',
            'type'              => '',
            'selector'          => '',
            'hover'             => '',
            'important'         => true
        );
        $options        = wp_parse_args( $options, $default );
        extract($options); // phpcs:ignore WordPress.PHP.DontExtract
        $module = $this;
		$key = $module->props[$slug];
        $important_text = true === $important ? '!important' : '';
        
		if ('' !== $key) {
			ET_Builder_Element::set_style($render_slug, array(
				'selector' => $selector,
				'declaration' => sprintf('%2$s: %1$s %3$s;', $key, $type, $important_text),
			));
		}
		if ( et_builder_is_hover_enabled( $slug, $module->props ) && isset($module->props[$slug . '__hover']) ) {
			$slug_hover = $module->props[$slug . '__hover'];
			ET_Builder_Element::set_style($render_slug, array(
				'selector' => $hover,
				'declaration' => sprintf('%2$s: %1$s %3$s;', $slug_hover, $type, $important_text),
			));
		}
    }


    /**
     * check hover option
     */
    function dg_check_hover_enable($key, $module) {
        if ( isset($module->props[$key . '__hover'])  && et_builder_is_hover_enabled( $key, $module->props ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
	 * Custom transition to elements
	 */
	function apply_custom_transition($module, $render_slug, $selector, $type = 'all') {
		ET_Builder_Element::set_style($render_slug, array(
			'selector' => $selector,
			'declaration' => sprintf('transition:%1$s %2$s %3$s %4$s !important;', 
				$type, 
				$module->props['hover_transition_duration'],
				$module->props['hover_transition_speed_curve'],
				$module->props['hover_transition_delay']
			),
		));
    }

    /**
     * Process values
     */
    function dg_process_values($value) {
        $array = array(
            'center'        => 'center',
            'top_left'      => 'top left',
            'top_center'    => 'top center',
            'center_top'    => 'center top',
            'top'           => 'top',
            'top_right'     => 'top right',
            'right'         => 'right',
            'center_right'  => 'center right',
            'bottom_right'  => 'bottom right',
            'bottom'        => 'bottom',
            'bottom_center' => 'bottom center',
            'bottom_left'   => 'bottom left',
            'left'          => 'left',
            'center_left'   => 'center left',
            'no_repeat'     => 'no-repeat',
            'repeat'        => 'repeat',
            'repeat_x'      => 'repeat-x',
            'repeat_y'      => 'repeat-y',
            'space'         => 'space',
            'round'         => 'round',
            'cover'         => 'cover',
            'fit'           => 'contain',
            'actual_size'   => 'initial'
        );
        return $array[$value];
    }

    /**
     * Process custom background
     */
    function dg_process_background( $options = array() ) {
        $default = array(
            'module'            => '',
            'render_slug'       => '',
            'slug'              => '',
            'selector'          => '',
            'hover'             => '',
            'important'         => true
        );

        $options        = wp_parse_args( $options, $default );
        extract($options); // phpcs:ignore WordPress.PHP.DontExtract
        $module = $this;
        $background_image = '';
        $gradient = '';
        $important_text = true === $important ? '!important' : '';

        if ( $module->props[$slug . '_bgcolor'] !== '' ) {
            ET_Builder_Element::set_style($render_slug, array(
				'selector' => $selector,
                'declaration' => sprintf( 'background-color: %1$s %2$s;',
                $module->props[$slug . '_bgcolor'], $important_text ),
			));
        }

        if ($module->props[$slug . '_use_gradient'] === 'on' ) {
            $color_1 = $module->props[$slug . '_color_gradient_1'] != '' ? 
                $module->props[$slug . '_color_gradient_1'] : "#2b87da";
            $color_2 = $module->props[$slug . '_color_gradient_2'] != '' ? 
                $module->props[$slug . '_color_gradient_2'] : "#29c4a9";
            $linear_direction = $module->props[$slug . '_gradient_direction'] != '' ? 
                $module->props[$slug . '_gradient_direction'] : "180deg";
            $start_position = $module->props[$slug . '_start_position'] != '' ? 
                $module->props[$slug . '_start_position'] : "0%";
            $end_position = $module->props[$slug . '_end_position'] != '' ? 
                $module->props[$slug . '_end_position'] : "100%";
            $radial_direction = $module->props[$slug . '_radial_direction'] ? 
                $module->props[$slug . '_radial_direction'] : 'center';

            if ( $module->props[ $slug . '_gradient_type'] !== 'radial') {
                $gradient = sprintf('linear-gradient( %3$s, %1$s %4$s, %2$s %5$s)', 
                    $color_1,
                    $color_2,
                    $linear_direction,
                    $start_position,
                    $end_position
                );
            } else {
                $gradient = sprintf('radial-gradient( circle at %3$s, %1$s %4$s, %2$s %5$s)', 
                    $color_1,
                    $color_2,
                    $this->dg_process_values($radial_direction),
                    $start_position,
                    $end_position
                );
            }
        }
        // background image
        if ( $module->props[$slug . '_background_image'] !== '' || $gradient  !== '' ) {
            $separator = $module->props[$slug . '_background_image'] !== '' && $gradient  !== '' ? ',' : '';
            $background_image = !empty($module->props[$slug . '_background_image']) ? 
                sprintf('url(%1$s)', $module->props[$slug . '_background_image']) : '';
            ET_Builder_Element::set_style($render_slug, array(
				'selector' => $selector,
                'declaration' => $module->props[$slug . '_above_image'] === 'on' ?
                    sprintf( 'background-image:%1$s%4$s %2$s %3$s;',
                        $gradient, 
                        $background_image,
                        $important_text,
                        $separator 
                    ) : sprintf( 'background-image:%2$s%4$s %1$s %3$s;',
                        $gradient, 
                        $background_image,
                        $important_text,
                        $separator
                    ),
			));
        }
        if ( $background_image !== '' ) {
        
            $background_size = $module->props[$slug . '_background_image_size'] !== '' ? 
                $module->props[$slug . '_background_image_size'] : 'cover';
            $background_position = $module->props[$slug . '_background_image_position'] !== '' ?
                $module->props[$slug . '_background_image_position'] : 'center';
            $background_repeat = $module->props[$slug . '_background_image_repeat'] !== '' ?
                $module->props[$slug . '_background_image_repeat'] : 'no_repeat';

            ET_Builder_Element::set_style($render_slug, array(
				'selector' => $selector,
                'declaration' => sprintf( 'background-size:%1$s; background-position:%2$s; background-repeat:%3$s;',
                    $this->dg_process_values($background_size), 
                    $this->dg_process_values($background_position),
                    $this->dg_process_values($background_repeat)
                ) ,
            ));
        } 

        // hover styles
        if ( $this->dg_check_hover_enable( $slug.'_bgcolor', $module) === true ) {
            ET_Builder_Element::set_style($render_slug, array(
				'selector' => $hover,
                'declaration' => sprintf( 'background-color: %1$s !important;',
                    $module->props[$slug.'_bgcolor__hover']
                ) ,
            ));
        }
        if ( $this->dg_check_hover_enable( $slug.'_background_image_size', $module) === true ) {
            ET_Builder_Element::set_style($render_slug, array(
				'selector' => $hover,
                'declaration' => sprintf( 'background-size: %1$s !important;',
                    $module->props[$slug.'_background_image_size__hover']
                ) ,
            ));
        }
        if ( $this->dg_check_hover_enable( $slug.'_background_image_position', $module) === true ) {
            ET_Builder_Element::set_style($render_slug, array(
				'selector' => $hover,
                'declaration' => sprintf( 'background-position: %1$s !important;',
                    $module->props[$slug.'_background_image_position__hover']
                ) ,
            ));
        }
        if ( $this->dg_check_hover_enable( $slug.'_background_image_repeat', $module) === true ) {
            ET_Builder_Element::set_style($render_slug, array(
				'selector' => $hover,
                'declaration' => sprintf( 'background-repeat: %1$s !important;',
                    $module->props[$slug.'_background_image_repeat__hover']
                ) ,
            ));
        }
    }

}
