<?php
require_once( DICA_MAIN_DIR . '/functions/extends.php' );
if ( ! trait_exists( 'DIVIGEAR\UTILS\UTILS' ) ) {
	require_once ( DICA_MAIN_DIR . '/includes/Utils/Utils.php');
}

class DiviCarousel extends ET_Builder_Module {

	public $slug = 'dica_divi_carousel';
	public $vb_support = 'on';
	public $child_slug = 'dica_divi_carouselitem';
	public $icon_path;
	use DIVIGEAR\UTILS\UTILS;

	protected $module_credits = [
		'module_uri' => 'https://www.divigear.com/plugins/carousel-module/',
		'author'     => 'DiviGear',
		'author_uri' => 'https://www.divigear.com',
	];

	public function init() {
		$this->name             = esc_html__( 'Divi Carousel', 'dica-divi-carousel' );
		$this->icon_path        = plugin_dir_path( __FILE__ ) . 'icon.svg';
		$this->main_css_element = '%%order_class%%';
	}

	public function get_settings_modal_toggles() {
		return [
			'general'  => [
				'toggles' => [
					'main_content'    => esc_html__( 'Main Content', 'dica-divi-carousel' ),
					'slider_settings' => esc_html__( 'Slider Settings', 'dica-divi-carousel' ),
					'advanced_slider' => esc_html__( 'Advanced Slider Settings', 'dica-divi-carousel' ),
				],
			],
			'advanced' => [
				'toggles' => [
					'image_overlay'    => esc_html__( 'Overlay', 'dica-divi-carousel' ),
					'image_style'      => esc_html__( 'Image', 'dica-divi-carousel' ),
					'image_border'     => esc_html__( 'Image Border', 'dica-divi-carousel' ),
					'image_shaodow'    => esc_html__( 'Image Box Shadow', 'dica-divi-carousel' ),
					'title_style'      => esc_html__( 'Title Text', 'dica-divi-carousel' ),
					'subtitle_style'   => esc_html__( 'Subtitle Text', 'dica-divi-carousel' ),
					'body_text_style'  => esc_html__( 'Body Text', 'dica-divi-carousel' ),
					'next_prev_button' => esc_html__( 'Next & Previous Button', 'dica-divi-carousel' ),
					'color_settings'   => esc_html__( 'Color Settings', 'dica-divi-carousel' ),
					'design_active_slide'       => esc_html__( 'Active Slide', 'dica-divi-carousel' ),
					'design_inactive_slide'     => esc_html__( 'Inactive Slide', 'dica-divi-carousel' ),
					'design_container_style'    => esc_html__( 'Container Style', 'dica-divi-carousel' ),
					'zindex_settings'  => esc_html__( 'Z-index', 'dica-divi-carousel' ),
					'custom_spacing'   => [
						'title'             => esc_html__( 'Custom Spacing', 'dica-divi-carousel' ),
						'tabbed_subtoggles' => true,
						// 'priority' => 50,
						'sub_toggles'       => [
							'container' => [
								'name' => 'Container',
							],
							'content'   => [
								'name' => 'Content',
							]
						],
					],
					'item_border'      => esc_html__( 'Item Border', 'dica-divi-carousel' ),
				]
			],
		];
	}

	public function get_advanced_fields_config() {
		$advanced_fields = [];

		$advanced_fields['text']           = false;
		$advanced_fields['fonts']          = [
			// Title
			'title'    => [
				'label'            => esc_html__( 'Title', 'dica-divi-carousel' ),
				'toggle_slug'      => 'title_style',
				'tab_slug'         => 'advanced',
				'hide_text_shadow' => true,
				'line_height'      => [
					'default' => '1em',
				],
				'font_size'        => [
					'default' => '20px',
				],
				'css'              => [
					'main'      => "%%order_class%% .dica_divi_carouselitem .dica-item-content .item-title",
					'hover'     => "%%order_class%% .dica_divi_carouselitem:hover .dica-item-content .item-title",
					'important' => 'all',
				],
			],
			// Subtitle
			'subtitle' => [
				'label'            => esc_html__( 'Subtitle', 'dica-divi-carousel' ),
				'toggle_slug'      => 'subtitle_style',
				'tab_slug'         => 'advanced',
				'hide_text_shadow' => true,
				'line_height'      => [
					'default' => '1em',
				],
				'font_size'        => [
					'default' => '16px',
				],
				'css'              => [
					'main'      => "%%order_class%% .dica_divi_carouselitem .dica-item-content .item-subtitle",
					'hover'     => "%%order_class%% .dica_divi_carouselitem:hover .dica-item-content .item-subtitle",
					'important' => 'all',
				],
			],
			// Body Text
			'body'     => [
				'label'            => esc_html__( 'Body', 'dica-divi-carousel' ),
				'toggle_slug'      => 'body_text_style',
				'tab_slug'         => 'advanced',
				'hide_text_shadow' => true,
				'line_height'      => [
					'default' => '1.7em',
				],
				'font_size'        => [
					'default' => '14px',
				],
				'css'              => [
					'main'      => "%%order_class%% .dica_divi_carouselitem .dica-item-content .content,
								%%order_class%% .dica_divi_carouselitem .dica-item-content .content p",
					'hover'     => "%%order_class%% .dica_divi_carouselitem:hover .dica-item-content .content,
								%%order_class%% .dica_divi_carouselitem:hover .dica-item-content .content p",
					'important' => 'all',
				],
			],

			'nav_icon' => [
				// 'label'         => esc_html__( 'Nav', 'dica-divi-carousel' ),
				'toggle_slug'         => 'next_prev_button',
				'tab_slug'            => 'advanced',
				'hide_text_shadow'    => true,
				'hide_font'           => true,
				'hide_font_size'      => true,
				'hide_letter_spacing' => true,
				'hide_text_color'     => true,
				'hide_text_align'     => true,
				'line_height'         => [
					'default' => '0.96em',
				],
				'font_size'           => [
					'default' => '53px',
				],
				'css'                 => [
					'main'      => "%%order_class%% .dica-container .swiper-button-next,
					%%order_class%% .dica-container .swiper-button-prev",
					'important' => 'all',
				],
			],

		];
		$advanced_fields['borders']        = [
			'default' => false,
			'css'     => [ 'important' => 'all' ],
			'item'    => [
				'css'          => [
					'main'      => [
						'border_styles' => "
						#et-boc {$this->main_css_element}.dica_divi_carousel .dica_divi_carouselitem > div:first-of-type,
						{$this->main_css_element}.dica_divi_carousel .dica_divi_carouselitem > div:first-of-type
						",
						'border_radii'  => "
						#et-boc {$this->main_css_element}.dica_divi_carousel .dica_divi_carouselitem > div,
						{$this->main_css_element}.dica_divi_carousel .dica_divi_carouselitem > div
						",
					],
					'important' => 'all'
				],
				'label_prefix' => esc_html__( 'Border', 'dica-divi-carousel' ),
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'item_border'
			],
			'image'   => [
				'label'        => esc_html__( 'Image Border', 'dica-divi-carousel' ),
				'css'          => [
					'main'      => [
						'border_radii'  => "
						{$this->main_css_element}.dica_divi_carousel .dica_divi_carouselitem .dica-image-container img.dica-item-image,
						#et-boc {$this->main_css_element}.dica_divi_carousel .dica_divi_carouselitem .dica-image-container img.dica-item-image,
						{$this->main_css_element} .dica_divi_carouselitem .dica-image-container .image
						",
						'border_styles' => "
						{$this->main_css_element}.dica_divi_carousel .dica_divi_carouselitem .dica-image-container img.dica-item-image,
						#et-boc {$this->main_css_element}.dica_divi_carousel .dica_divi_carouselitem .dica-image-container img.dica-item-image
						",
					],
					'important' => 'all'
				],
				'label_prefix' => esc_html__( 'Image', 'dica-divi-carousel' ),
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'image_border'
			]
		];
		$advanced_fields['margin_padding'] = [
			'css' => [
				'main'      => '%%order_class%%.dica_divi_carousel',
				'important' => 'all',
			],
		];
		$advanced_fields['background']     = [
			'css'                    => [
				'main'  => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content',
				'hover' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content'
			],
			'use_background_pattern' => false,
			'use_background_mask'    => false
		];
		$advanced_fields['box_shadow']     = [
			'default' => false,
			'image'   => [
				'css'          => [
					'main' => "{$this->main_css_element} .dica_divi_carouselitem .dica-image-container .image"
				],
				'label_prefix' => esc_html__( 'Image Box Shadow', 'dica-divi-carousel' ),
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'image_shaodow',
			]
		];
		$advanced_fields['overflow']       = false;
		$advanced_fields['button']         = false;
		$advanced_fields['link_options']   = false;
		$advanced_fields['animation']      = false;
		$advanced_fields['filters']        = false;

		return $advanced_fields;
	}

	public function get_fields() {
		$_ex                       = "DICA_Extends";
		$general                   = array(
			// Sliider Settings
			'item_width_auto'        => array(
				'label'       => esc_html__( 'Item width control', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'description' => esc_html__( 'Control the fixed with for each carousel item for multiple devices.', 'dica-divi-carousel' ),
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off'
			),

			// item width
			'item_width'             => array(
				'label'           => esc_html__( 'Item width', 'dica-divi-carousel' ),
				'type'            => 'range',
				'toggle_slug'     => 'slider_settings',
				'description'     => esc_html__( 'Specify the width for devices.', 'dica-divi-carousel' ),
				'mobile_options'  => true,
				'range_settings ' => array(
					'min'  => '50px',
					'max'  => '550px',
					'step' => '1',
				),
				'default'         => '550px',
				'default_unit'    => 'px',
				'show_if'         => array(
					'item_width_auto' => 'on',
				)
			),
			'item_width_tablet'      => array(
				'type'        => 'skip',
				// 'tab_slug'        	=> 'advanced',
				'toggle_slug' => 'slider_settings',
			),
			'item_width_phone'       => array(
				'type'        => 'skip',
				// 'tab_slug'        	=> 'advanced',
				'toggle_slug' => 'slider_settings',
			),
			'item_width_last_edited' => array(
				'type'        => 'skip',
				// 'tab_slug'        	=> 'advanced',
				'toggle_slug' => 'slider_settings',
			),

			'show_items_desktop'         => array(
				'label'       => esc_html__( 'Show item Desktop', 'dica-divi-carousel' ),
				'type'        => 'text',
				'default'     => '4',
				'toggle_slug' => 'slider_settings',
				'show_if_not' => array(
					'item_width_auto' => 'on',
				),
			),
			'show_items_tablet'          => array(
				'label'            => esc_html__( 'Show item Tablet', 'dica-divi-carousel' ),
				'type'             => 'text',
				'default_on_front' => '3',
				'toggle_slug'      => 'slider_settings',
				'show_if_not'      => array(
					'item_width_auto' => 'on',
				),
			),
			'show_items_mobile'          => array(
				'label'       => esc_html__( 'Show item Mobile', 'dica-divi-carousel' ),
				'type'        => 'text',
				'default'     => '1',
				'toggle_slug' => 'slider_settings',
				'show_if_not' => array(
					'item_width_auto' => 'on',
				),
			),
			'multislide'                 => array(
				'label'       => esc_html__( 'Multislide', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off',
				'show_if_not' => array(
					'item_width_control' => 'on',
					'scroller_effect'    => 'on',
					'multiple_slide_row' => 'on',
				),
			),
			'multiple_slide_row'         => [
				'label'            => esc_html__( 'Use Multiple Row', 'dica-divi-carousel' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => [
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				],
				'toggle_slug'      => 'slider_settings',
				'description'      => esc_html__( 'To use multirow layout. It will only work on default slider effect', 'dica-divi-carousel' ),
				'default'          => 'off',
				'default_on_front' => 'off',
				'show_if_not'          => [
					'multislide'        => 'on'
				],
			],
			'slide_row'                  => [
				'label'            => esc_html__( 'Row Per Slide', 'dica-divi-carousel' ),
				'type'             => 'range',
				'option_category'  => 'configuration',
				'default'          => '1',
				'default_on_front' => '1',
				'default_unit'     => '',
				'validate_unit'    => false,
				'mobile_options'   => true,
				'responsive'       => true,
				'range_settings'   => [
					'min'  => '1',
					'max'  => '5',
					'step' => '1',
				],
				'show_if'          => [
					'multiple_slide_row' => 'on',
				],
				'toggle_slug'      => 'slider_settings',
			],
			'transition_duration'        => array(
				'label'       => esc_html__( 'Transition Duration (ms)', 'dica-divi-carousel' ),
				'type'        => 'text',
				'default'     => '500',
				'toggle_slug' => 'slider_settings',
				'show_if_not' => array(
					'scroller_effect' => 'on',
				)
			),
			'centermode'                 => array(
				'label'       => esc_html__( 'Center Slide', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off',
				'show_if_not' => array(
					'multiple_slide_row' => 'on',
				),
			),
			'loop'                       => array(
				'label'       => esc_html__( 'Loop', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off',
				'show_if_not' => array(
					'multiple_slide_row' => 'on',
				),
			),
			'autoplay'                   => array(
				'label'       => esc_html__( 'AutoPlay', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off'
			),
			'hoverpause'                 => array(
				'label'       => esc_html__( 'Pause on hover', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off',
				'show_if'     => array(
					'autoplay' => 'on',
				),
			),
			'scroller_effect'            => array(
				'label'       => esc_html__( 'Scroller Effect', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off',
				'show_if'     => array(
					'autoplay' => 'on',
				),
			),
			'transition_duration_scroll' => array(
				'label'       => esc_html__( 'Transition Duration for Scroll Effect', 'dica-divi-carousel' ),
				'type'        => 'select',
				'options'     => array(
					'1000'  => esc_html__( '1 Second', 'dica-divi-carousel' ),
					'2000'  => esc_html__( '2 Seconds', 'dica-divi-carousel' ),
					'3000'  => esc_html__( '3 Seconds', 'dica-divi-carousel' ),
					'4000'  => esc_html__( '4 Seconds', 'dica-divi-carousel' ),
					'5000'  => esc_html__( '5 Seconds', 'dica-divi-carousel' ),
					'6000'  => esc_html__( '6 Seconds', 'dica-divi-carousel' ),
					'7000'  => esc_html__( '7 Seconds', 'dica-divi-carousel' ),
					'8000'  => esc_html__( '8 Seconds', 'dica-divi-carousel' ),
					'9000'  => esc_html__( '9 Seconds', 'dica-divi-carousel' ),
					'10000' => esc_html__( '10 Seconds', 'dica-divi-carousel' ),
					'11000' => esc_html__( '11 Seconds', 'dica-divi-carousel' ),
					'12000' => esc_html__( '12 Seconds', 'dica-divi-carousel' ),
					'13000' => esc_html__( '13 Seconds', 'dica-divi-carousel' ),
					'14000' => esc_html__( '14 Seconds', 'dica-divi-carousel' ),
					'15000' => esc_html__( '15 Seconds', 'dica-divi-carousel' ),
				),
				'default'     => '4000',
				'toggle_slug' => 'slider_settings',
				'show_if'     => array(
					'autoplay'        => 'on',
					'scroller_effect' => 'on',
				),
			),
			'autoplay_speed'             => array(
				'label'       => esc_html__( 'Auto Play Delay', 'dica-divi-carousel' ),
				'type'        => 'text',
				'default'     => '1000',
				'toggle_slug' => 'slider_settings',
				'show_if'     => array(
					'autoplay' => 'on',
				),
				'show_if_not' => array(
					'scroller_effect' => 'on',
				),
			),
			'autoplay_viewport'          => array(
				'label'            => esc_html__( 'Autoplay Only On Viewport', 'dica-divi-carousel' ),
				'description'      => esc_html__( 'Autoplay when in viewport.', 'dica-divi-carousel' ),
				'type'             => 'range',
				'option_category'  => 'layout',
				'toggle_slug'      => 'slider_settings',
				'default'          => '80%',
				'default_on_front' => '80%',
				'unitless'         => false,
				'allow_empty'      => false,
				'range_settings'   => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'responsive'       => false,
				'mobile_options'   => false,
				'show_if'          => array(
					'autoplay' => 'on',
				),
			),
			'arrow_nav'                  => array(
				'label'       => esc_html__( 'Arrow Navigation', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off'
			),
			'dot_nav'                    => array(
				'label'       => esc_html__( 'Dot Navigation', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off'
			),
			'dot_alignment'              => array(
				'label'            => esc_html__( 'Dots Alignment', 'dica-divi-carousel' ),
				'type'             => 'text_align',
				'options'          => et_builder_get_text_orientation_options( array( 'justified' ) ),
				'toggle_slug'      => 'slider_settings',
				'default'          => 'center',
				'default_on_front' => 'center',
				'show_if'          => array(
					'dot_nav' => 'on',
				),
			),
			'field_large_active_bullet'     => [
				'label'       => esc_html__( 'Large Active Dot', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => [
					'off' => esc_html__( 'Off', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'On', 'dica-divi-carousel' ),
				],
				'default'     => 'off',
				'toggle_slug' => 'slider_settings',
				'show_if'     => [
					'dot_nav' => 'on',
				],
			],
			'item_spacing'               => array(
				'label'           => esc_html__( 'Item Spacing', 'dica-divi-carousel' ),
				'type'            => 'range',
				'toggle_slug'     => 'slider_settings',
				'mobile_options'  => true,
				'range_settings ' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'default'         => '30',
				'default_unit'    => ''
			),
			'item_spacing_tablet'        => array(
				'type'        => 'skip',
				'toggle_slug' => 'slider_settings',
			),
			'item_spacing_phone'         => array(
				'type'        => 'skip',
				'toggle_slug' => 'slider_settings',
			),
			'item_spacing_last_edited'   => array(
				'type'        => 'skip',
				'toggle_slug' => 'slider_settings',
			),
			'equal_height'               => array(
				'label'       => esc_html__( 'Equal Height Item', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off',
				'show_if_not' => [
					'multiple_slide_row' => 'on'
				]
			),
			'item_vertical_align'        => array(
				'label'       => esc_html__( 'Vertical Align', 'dica-divi-carousel' ),
				'type'        => 'select',
				'options'     => array(
					'flex-start' => esc_html__( 'Top', 'dica-divi-carousel' ),
					'center'     => esc_html__( 'Center', 'dica-divi-carousel' ),
					'flex-end'   => esc_html__( 'Bottom', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'show_if'     => array(
					'equal_height' => 'off',
				),
			),
			'field_enable_rtl'           => [
				'label'       => esc_html__( 'RTL', 'dica-divi-carousel' ),
				'description' => esc_html__( 'It will not work if Multi-Row and Lazy Loading are enabled.', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => [
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				],
				'default'     => 'off',
				'toggle_slug' => 'slider_settings'
			],
			'lazy_loading'               => array(
				'label'       => esc_html__( 'Lazy Loading', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off'
			),
			'load_before_transition'     => array(
				'label'       => esc_html__( 'Start Loading before transition Start', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off',
				'show_if'     => array(
					'lazy_loading' => 'on',
				),
			),
			'dg_hash_nav'                => array(
				'label'       => esc_html__( 'Hash Navigation', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off'
			),
			'keyboard_control'           => [
				'label'            => esc_html__( 'Keyboard Control', 'dica-divi-carousel' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => [
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				],
				'toggle_slug'      => 'slider_settings',
				'description'      => esc_html__( '', 'dica-divi-carousel' ),
				'default'          => 'off',
				'default_on_front' => 'off',
			],
			'mousewheel_control'              => [
				'label'            => esc_html__( 'Mouse Wheel Control', 'dica-divi-carousel' ),
				'type'             => 'yes_no_button',
				'option_category'  => 'configuration',
				'options'          => [
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				],
				'toggle_slug'      => 'slider_settings',
				'description'      => esc_html__( '', 'dica-divi-carousel' ),
				'default'          => 'off',
				'default_on_front' => 'off',
			],
			'simulatetouch'              => array(
				'label'       => esc_html__( 'Disable Mouse Drag Event on Desktop', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off'
			),
			'allowtouchmove'             => array(
				'label'       => esc_html__( 'Disable Touch Event on Mobile/Tablet', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'slider_settings',
				'default'     => 'off',
				'show_if'     => array(
					'simulatetouch' => 'on'
				)
			),
			// Advanced Settings
			'advanced_effect'            => array(
				'default'          => 'default',
				'default_on_front' => 'default',
				'label'            => esc_html__( 'Slider Effect', 'dica-divi-carousel' ),
				'type'             => 'select',
				'options'          => array(
					'default'   => esc_html__( 'Default', 'dica-divi-carousel' ),
					'coverflow' => esc_html__( 'Coverflow', 'dica-divi-carousel' ),
				),
				'toggle_slug'      => 'advanced_slider',
			),
			'coverflow_rotate'           => array(
				'label'           => esc_html__( 'Rotate', 'dica-divi-carousel' ),
				'type'            => 'range',
				'toggle_slug'     => 'advanced_slider',
				'range_settings ' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'default'         => '50',
				'show_if'         => array(
					'advanced_effect' => 'coverflow',
				),
			),
			'coverflow_shadow'           => array(
				'label'       => esc_html__( 'Coverflow Shadow', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'toggle_slug' => 'advanced_slider',
				'default'     => 'on',
				'show_if'     => array(
					'advanced_effect' => 'coverflow',
				),
			),
			// Image
			'overlay_image'              => array(
				'label'            => esc_html__( 'Image Overlay', 'dica-divi-carousel' ),
				'type'             => 'yes_no_button',
				'options'          => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' )
				),
				'default_on_front' => 'off',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'image_overlay',
			),
			'overlay_on_hover'           => array(
				'label'            => esc_html__( 'Overlay on Hover', 'dica-divi-carousel' ),
				'type'             => 'yes_no_button',
				'options'          => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' )
				),
				'default_on_front' => 'on',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'image_overlay',
				'show_if'         => array(
					"overlay_image" => "on"
				)
			),
			'overlay_color'              => array(
				'label'       => esc_html__( 'Overlay Color', 'dica-divi-carousel' ),
				'type'        => 'skip',
				'toggle_slug' => 'image_overlay',
				'default'     => 'rgba(255,255,255,0.85)',
				'tab_slug'    => 'advanced',
				'show_if'     => array(
					'overlay_image' => 'on',
				),
			),
			'overlay_icon_color'         => array(
				'label'       => esc_html__( 'Overlay Icon Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'toggle_slug' => 'image_overlay',
				'default'     => '#0c71c3',
				'tab_slug'    => 'advanced',
				'show_if'     => array(
					'overlay_image' => 'on',
					'use_overlay_animated_icon' => 'off',
				)
			),
			'overlay_icon_size'          => array(
				'label'           => esc_html__( 'Icon Size', 'dica-divi-carousel' ),
				'type'            => 'range',
				'mobile_options'  => true,
				'responsive'      => true,
				'default'         => '26px',
				'default_unit'    => 'px',
				'range_settings ' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'image_overlay',
				'show_if'     => array(
					'overlay_image' => 'on',
					'use_overlay_animated_icon' => 'off',
				)
			),


			// Color
			'arrow_nav_color'            => array(
				'label'       => esc_html__( 'Arrow Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'toggle_slug' => 'color_settings',
				'default'     => '#0c71c3',
				'tab_slug'    => 'advanced',
				'hover'       => 'tabs'
			),
			'arrow_bg_color'             => array(
				'label'       => esc_html__( 'Arrow Background Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'toggle_slug' => 'color_settings',
				'default'     => '#ffffff',
				'tab_slug'    => 'advanced',
				'hover'       => 'tabs'
			),
			'dots_color'                 => array(
				'label'       => esc_html__( 'Dots Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'toggle_slug' => 'color_settings',
				'default'     => '#e0e0e0',
				'tab_slug'    => 'advanced',
			),
			'dots_active_color'          => array(
				'label'       => esc_html__( 'Dots Active Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'toggle_slug' => 'color_settings',
				'default'     => '#0c71c3',
				'tab_slug'    => 'advanced',
			),
		);
		$general = array_merge(
			$general,
			$this->add_bg_field([
				'label'             => 'Overlay Color',
				'key'               => 'overlay_color_field',
				'image'             => false,
				'toggle_slug'       => 'image_overlay',
				'tab_slug'          => 'advanced',
				'show_if'     => [
					'overlay_image' => 'on'
				],
			]),
			[
				'use_overlay_icon'           => array(
					'label'       => esc_html__( 'Use Custom Overlay Icon', 'dica-divi-carousel' ),
					'type'        => 'yes_no_button',
					'options'     => array(
						'off' => esc_html__( 'No', 'dica-divi-carousel' ),
						'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'image_overlay',
					'default'     => 'off',
					'show_if'     => array(
						'overlay_image' => 'on',
						'use_overlay_animated_icon' => 'off'
					),
				),
				'use_overlay_animated_icon'  => array(
					'label'       => esc_html__( 'Use Animated Overlay Icon', 'dica-divi-carousel' ),
					'type'        => 'yes_no_button',
					'options'     => array(
						'off' => esc_html__( 'No', 'dica-divi-carousel' ),
						'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'image_overlay',
					'default'     => 'off',
					'show_if'     => array(
						'overlay_image' => 'on',
						'use_overlay_icon' => 'off'
					),
				),
				'animated_overlay_icon_style' => array(
					'default'          => 'anim-none',
					'default_on_front' => 'anim-none',
					'label'            => esc_html__( 'Animated Icon Effect', 'dica-divi-carousel' ),
					'type'             => 'select',
					'options'          => array(
						'anim-none'   => esc_html__( 'None', 'dica-divi-carousel' ),
						'anim-ripple'   => esc_html__( 'Ripple', 'dica-divi-carousel' ),
						'anim-pulse-border'   => esc_html__( 'Pulse Border', 'dica-divi-carousel' ),
					),
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'image_overlay',
					'show_if'     => array(
						'overlay_image' => 'on',
						'use_overlay_icon' => 'off',
						'use_overlay_animated_icon' => 'on',
					),
				),
				'overlay_icon'               => array(
					'label'           => esc_html__( 'Select overlay icon', 'dica-divi-carousel' ),
					'type'            => 'select_icon',
					'option_category' => 'basic_option',
					'class'           => array( 'et-pb-font-icon' ),
					'default'         => '&#x31;||divi',
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'image_overlay',
					'show_if'         => array(
						'use_overlay_icon' => 'on',
						'overlay_image'    => 'on'
					),
				),
				'overlay_anim_icon_color'    => array(
					'label'       => esc_html__( 'Animated Icon Color', 'dica-divi-carousel' ),
					'type'        => 'color-alpha',
					'toggle_slug' => 'image_overlay',
					'default'     => '#ffffff',
					'tab_slug'    => 'advanced',
					'show_if'     => array(
						'overlay_image' => 'on',
						'use_overlay_animated_icon' => 'on',
					)
				),
				'overlay_anim_icon_ripple_color'    => array(
					'label'       => esc_html__( 'Animated Icon Ripple Color', 'dica-divi-carousel' ),
					'type'        => 'color-alpha',
					'toggle_slug' => 'image_overlay',
					'tab_slug'    => 'advanced',
					'show_if'     => array(
						'overlay_image' => 'on',
						'use_overlay_animated_icon' => 'on',
					)
				),
			],
			$this->add_bg_field([
				'label'             => 'Animated Icon Background Color',
				'key'               => 'overlay_anim_icon_bg',
				'image'             => false,
				'toggle_slug'       => 'image_overlay',
				'tab_slug'          => 'advanced',
				'show_if'     => [
					'overlay_image' => 'on',
					'use_overlay_animated_icon' => 'on',
				],
			]),
			[
				'overlay_anim_icon_border_width'  => array(
					'label'            => esc_html__( 'Animated Icon Border Width', 'dica-divi-carousel' ),
					'description'      => esc_html__( '', 'dica-divi-carousel' ),
					'type'             => 'range',
					'option_category'  => 'layout',
					'tab_slug'    => 'advanced',
					'toggle_slug'      => 'image_overlay',
					'default'          => 0,
					'default_on_front' => 0,
					'default_unit'     => 'px',
					'allowed_units'          => array( 'px' ),
					'unitless'         => false,
					'validate_unit'    => true,
					'allow_empty'      => false,
					'range_settings'   => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
						'min_limit' => 0,
					),
					'responsive'       => false,
					'mobile_options'   => false,
					'show_if'          => array(
						'overlay_image' => 'on',
						'use_overlay_animated_icon' => 'on',
					),
				),
				'overlay_anim_icon_border_color'  => array(
					'label'       => esc_html__( 'Animated Icon Border Color', 'dica-divi-carousel' ),
					'type'        => 'color-alpha',
					'toggle_slug' => 'image_overlay',
					'default'     => '#ffffff',
					'default_on_front'     => '#ffffff',
					'tab_slug'    => 'advanced',
					'show_if'     => array(
						'overlay_image' => 'on',
						'use_overlay_animated_icon' => 'on',
					)
				),
			]
		);
		$image_settings            = array(
			'align'                 => array(
				'label'            => esc_html__( 'Image Alignment', 'dica-divi-carousel' ),
				'type'             => 'text_align',
				'options'          => et_builder_get_text_orientation_options( array( 'justified' ) ),
				'default_on_front' => 'center',
				'tab_slug'         => 'advanced',
				'toggle_slug'      => 'image_style',
				'description'      => esc_html__( 'Here you can choose the image alignment.', 'dica-divi-carousel' ),
			),
			'image_force_fullwidth' => array(
				'label'       => esc_html__( 'Force full width', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'image_style',
				'default'     => 'off'
			),
			'image_sizing'          => array(
				'label'           => esc_html__( 'Image Max Width', 'dica-divi-carousel' ),
				'type'            => 'range',
				'toggle_slug'     => 'image_style',
				'tab_slug'        => 'advanced',
				'range_settings ' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'default'         => '100%',
				'default_unit'    => '%',
				'allowed_units'   => array( '%', 'px', 'em' ),
				'show_if_not'     => array(
					'image_force_fullwidth' => 'on'
				)
			),
		);
		$next_prev_button_settings = array(
			'arrow_position'   => array(
				'label'          => esc_html__( 'Position', 'dica-divi-carousel' ),
				'type'           => 'select',
				'options'        => array(
					'middle-inside'  => esc_html__( 'Middle & Inside the container', 'dica-divi-carousel' ),
					'middle-outside' => esc_html__( 'Middle & Outside the container', 'dica-divi-carousel' ),
					'top'            => esc_html__( 'Top', 'dica-divi-carousel' ),
					'bottom'         => esc_html__( 'Bottom', 'dica-divi-carousel' ),
				),
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'next_prev_button',
				'default'        => 'middle-inside',
				'mobile_options' => true,
			),
			'arrow_show_hover' => array(
				'label'       => esc_html__( 'Show on hover (Only for middle position)', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'next_prev_button',
				'default'     => 'off',
			),
			'arrow_alignment'  => array(
				'label'       => esc_html__( 'Alignment for top & bottom position', 'dica-divi-carousel' ),
				'type'        => 'select',
				'options'     => array(
					'space-between' => esc_html__( 'default', 'dica-divi-carousel' ),
					'flex-start'    => esc_html__( 'Left', 'dica-divi-carousel' ),
					'center'        => esc_html__( 'Center', 'dica-divi-carousel' ),
					'flex-end'      => esc_html__( 'Right', 'dica-divi-carousel' ),
					'space-between' => esc_html__( 'Justify', 'dica-divi-carousel' ),
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'next_prev_button',
				'default'     => 'space-between',
			),

			// Next & Previous Button
			'use_prev_icon'    => array(
				'label'       => esc_html__( 'Use previous custom icon', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'next_prev_button',
				'default'     => 'off',
			),
			'prev_icon'        => array(
				'label'           => esc_html__( 'Select previous icon', 'dica-divi-carousel' ),
				'type'            => 'select_icon',
				'option_category' => 'basic_option',
				'class'           => array( 'et-pb-font-icon' ),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'next_prev_button',
				'show_if'         => array(
					'use_prev_icon' => 'on',
				),
			),
			'use_next_icon'    => array(
				'label'       => esc_html__( 'Use next custom icon', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => array(
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'next_prev_button',
				'default'     => 'off',
			),
			'next_icon'        => array(
				'label'           => esc_html__( 'Select next icon', 'dica-divi-carousel' ),
				'type'            => 'select_icon',
				'option_category' => 'basic_option',
				'class'           => array( 'et-pb-font-icon' ),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'next_prev_button',
				'show_if'         => array(
					'use_next_icon' => 'on',
				),
			),
			'arrow_font_size'  => array(
				'label'           => esc_html__( 'Font Size', 'dica-divi-carousel' ),
				'type'            => 'range',
				'mobile_options'  => true,
				'responsive'      => true,
				'default'         => '53px',
				'default_unit'    => 'px',
				'range_settings ' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'next_prev_button',
			),
			'arrow_icon_spacing'  => array(
				'label'           => esc_html__( 'Spacing Around', 'dica-divi-carousel' ),
				'type'            => 'range',
				'mobile_options'  => false,
				'responsive'      => false,
				'default'         => '0px',
				'default_on_front'         => '0px',
				'default_unit'    => 'px',
				'range_settings ' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'next_prev_button',
			),
		);
		$design_active_slide = array_merge(
			[
				'field_enable_active_slide_design' => [
					'label'       => esc_html__( 'Enable Active Slide Styling', 'dica-divi-carousel' ),
					'type'        => 'yes_no_button',
					'options'     => [
						'off' => esc_html__( 'No', 'dica-divi-carousel' ),
						'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
					],
					'default'     => 'off',
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'design_active_slide',
					'show_if_not' => [
						'multislide' => 'on'
					]
				],
			],
			$this->add_bg_field([
				'label'             => 'Background Color',
				'key'               => 'active_slide_bg_color',
				'image'             => false,
				'toggle_slug'       => 'design_active_slide',
				'tab_slug'          => 'advanced',
				'show_if'     => [
					'field_enable_active_slide_design' => 'on'
				],
			])
		);
		$design_inactive_slide   = array_merge(
			[
				'field_enable_inactive_slide_design' => [
					'label'       => esc_html__( 'Enable Inactive Slide Styling', 'dica-divi-carousel' ),
					'type'        => 'yes_no_button',
					'options'     => [
						'off' => esc_html__( 'No', 'dica-divi-carousel' ),
						'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
					],
					'default'     => 'off',
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'design_inactive_slide',
					'show_if_not' => [
						'multislide' => 'on'
					]
				],
			],
			$this->add_bg_field([
				'label'             => 'Background Color',
				'key'               => 'inactive_slide_bg_color',
				'image'             => false,
				'toggle_slug'       => 'design_inactive_slide',
				'tab_slug'          => 'advanced',
				'show_if'     => [
					'field_enable_inactive_slide_design' => 'on'
				],
			]),
			[
				'field_enable_inactive_slide_blur'   => [
					'label'       => esc_html__( 'Enable Blur Effect', 'dica-divi-carousel' ),
					'type'        => 'yes_no_button',
					'options'     => [
						'off' => esc_html__( 'No', 'dica-divi-carousel' ),
						'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
					],
					'default'     => 'off',
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'design_inactive_slide',
					'show_if'     => [
						'field_enable_inactive_slide_design' => 'on'
					],
					'show_if_not' => [
						'multislide' => 'on'
					]
				],
				'field_inactive_slide_blur'          => [
					'label'           => esc_html__( 'Blur Effect', 'dica-divi-carousel' ),
					'type'            => 'range',
					'mobile_options'  => false,
					'responsive'      => false,
					'default'         => '2px',
					'default_unit'    => 'px',
					'range_settings ' => [
						'min'  => '0',
						'max'  => '100',
						'step' => '1',
					],
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'design_inactive_slide',
					'show_if'         => [
						'field_enable_inactive_slide_design' => 'on',
						'field_enable_inactive_slide_blur'   => 'on'
					],
					'show_if_not'     => [
						'multislide' => 'on'
					]
				],
				'field_disable_hover_click'          => [
					'label'       => esc_html__( 'Disable Click', 'dica-divi-carousel' ),
					'type'        => 'yes_no_button',
					'options'     => [
						'off' => esc_html__( 'No', 'dica-divi-carousel' ),
						'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
					],
					'default'     => 'off',
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'design_inactive_slide',
					'show_if'     => [
						'field_enable_inactive_slide_design' => 'on'
					],
					'show_if_not' => [
						'multislide' => 'on'
					]
				],
				'field_inactive_item_scaling'        => [
					'label'       => esc_html__( 'Enable Scaling', 'dica-divi-carousel' ),
					'type'        => 'yes_no_button',
					'options'     => [
						'off' => esc_html__( 'No', 'dica-divi-carousel' ),
						'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
					],
					'default'     => 'off',
					'tab_slug'    => 'advanced',
					'toggle_slug' => 'design_inactive_slide',
					'show_if'     => [
						'field_enable_inactive_slide_design' => 'on',
						'advanced_effect'               => 'default'
					],
					'show_if_not' => [
						'multislide' => 'on'
					]
				],
				'field_inactive_item_scaling_range'  => [
					'label'           => esc_html__( 'Item Scaling Range', 'dica-divi-carousel' ),
					'type'            => 'range',
					'mobile_options'  => false,
					'responsive'      => false,
					'default'         => 0.75,
					'default_unit'    => '',
					'range_settings' => [
						'min'       => -1,
						'max'       => 1,
						'step'      => 0.01,
						'min_limit' => -1,
						'max_limit' => 1,
					],
					'tab_slug'        => 'advanced',
					'toggle_slug'     => 'design_inactive_slide',
					'show_if'         => [
						'field_enable_inactive_slide_design' => 'on',
						'field_inactive_item_scaling'        => 'on',
						'advanced_effect'               => 'default'
					],
					'show_if_not'     => [
						'multislide' => 'on'
					]
				],
			]
		);
		$design_container_style  = [
			'field_enable_container_shadow_effect'   => [
				'label'       => esc_html__( 'Enable Fade Effect', 'dica-divi-carousel' ),
				'type'        => 'yes_no_button',
				'options'     => [
					'off' => esc_html__( 'No', 'dica-divi-carousel' ),
					'on'  => esc_html__( 'Yes', 'dica-divi-carousel' ),
				],
				'default'     => 'off',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'design_container_style',
			],
			'field_container_shadow_primary_color'   => [
				'label'       => esc_html__( 'Primary Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'default'     => 'rgba(255,255,255,0.85)',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'design_container_style',
				'show_if'     => [
					'field_enable_container_shadow_effect' => 'on'
				]
			],
			'field_container_shadow_secondary_color' => [
				'label'       => esc_html__( 'Secondary Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'default'     => 'rgba(255,255,255,0.0)',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'design_container_style',
				'show_if'     => [
					'field_enable_container_shadow_effect' => 'on'
				]
			],
			'field_container_shadow_width'           => [
				'label'           => esc_html__( 'Fade Spacing in Middle', 'dica-divi-carousel' ),
				'type'            => 'range',
				'default'         => '70%',
				'default_unit'    => '%',
				'range_settings ' => [
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				],
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'design_container_style',
				'show_if'         => [
					'field_enable_container_shadow_effect' => 'on'
				]
			],
		];
		$zindex                    = array(
			'image_container_zindex'   => array(
				'label'           => esc_html__( 'Image Container', 'dica-divi-carousel' ),
				'type'            => 'range',
				'mobile_options'  => true,
				'responsive'      => true,
				'default'         => '10',
				// 'default_unit'      => ' ',
				'unitless'        => true,
				'range_settings ' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'zindex_settings',
			),
			'content_container_zindex' => array(
				'label'           => esc_html__( 'Content Container', 'dica-divi-carousel' ),
				'type'            => 'range',
				'mobile_options'  => true,
				'responsive'      => true,
				'default'         => '10',
				// 'default_unit'      => ' ',
				'unitless'        => true,
				'range_settings ' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'tab_slug'        => 'advanced',
				'toggle_slug'     => 'zindex_settings',
			),
		);

		$title_background    = array(
			'title_background' => array(
				'label'       => esc_html__( 'Background Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'title_style',
				// 'default'		  => 'rgba(51, 51, 51, 0.59)',
				'hover'       => 'tabs'
			),
		);
		$subtitle_background = array(
			'subtitle_background' => array(
				'label'       => esc_html__( 'Background Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'subtitle_style',
				'hover'       => 'tabs'
			),
		);
		$content_background  = array(
			'content_background' => array(
				'label'       => esc_html__( 'Background Color', 'dica-divi-carousel' ),
				'type'        => 'color-alpha',
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'body_text_style',
				// 'default'		  => 'rgba(51, 51, 51, 0.59)',
				'hover'       => 'tabs'
			),
		);

		$item_margin               = $_ex::add_margin_padding_field(
			'item_margin',
			'Carousel Item Margin',
			'custom_spacing',
			'container'
		);
		$item_padding              = $_ex::add_margin_padding_field(
			'item_padding',
			'Carousel Item Padding',
			'custom_spacing',
			'container'
		);
		$image_container_margin    = $_ex::add_margin_padding_field(
			'image_container_margin',
			'Image Container Margin',
			'custom_spacing',
			'container'
		);
		$image_container_padding   = $_ex::add_margin_padding_field(
			'image_container_padding',
			'Image Container Padding',
			'custom_spacing',
			'container'
		);
		$image_padding             = $_ex::add_margin_padding_field(
			'image_padding',
			'Image Padding',
			'custom_spacing',
			'content'
		);
		$content_container_margin  = $_ex::add_margin_padding_field(
			'content_container_margin',
			'Content Container Margin',
			'custom_spacing',
			'container'
		);
		$content_container_padding = $_ex::add_margin_padding_field(
			'content_container_padding',
			'Content Container Padding',
			'custom_spacing',
			'container'
		);
		$title_margin              = $_ex::add_margin_padding_field(
			'title_margin',
			'Title Margin',
			'custom_spacing',
			'content'
		);
		$title_padding             = $_ex::add_margin_padding_field(
			'title_padding',
			'Title Padding',
			'custom_spacing',
			'content'
		);
		$subtitle_margin           = $_ex::add_margin_padding_field(
			'subtitle_margin',
			'Subtitle Margin',
			'custom_spacing',
			'content'
		);
		$subtitle_padding          = $_ex::add_margin_padding_field(
			'subtitle_padding',
			'Subtitle Padding',
			'custom_spacing',
			'content'
		);
		$text_margin               = $_ex::add_margin_padding_field(
			'text_margin',
			'Text Margin',
			'custom_spacing',
			'content'
		);
		$text_padding              = $_ex::add_margin_padding_field(
			'text_padding',
			'Text Padding',
			'custom_spacing',
			'content'
		);
		$button_margin             = $_ex::add_margin_padding_field(
			'button_margin',
			'Button Margin',
			'custom_spacing',
			'content'
		);
		$button_padding            = $_ex::add_margin_padding_field(
			'button_padding',
			'Button Padding',
			'custom_spacing',
			'content'
		);

		$social_container_margin    = $_ex::add_margin_padding_field(
			'social_container_margin',
			'Social Media Container Margin',
			'custom_spacing',
			'container'
		);
		$social_container_padding   = $_ex::add_margin_padding_field(
			'social_container_padding',
			'Social Media Container Padding',
			'custom_spacing',
			'container'
		);
		$social_item_margin         = $_ex::add_margin_padding_field(
			'social_item_margin',
			'Social Media Item Margin',
			'custom_spacing',
			'content'
		);
		$social_item_padding        = $_ex::add_margin_padding_field(
			'social_item_padding',
			'Social Media Item Padding',
			'custom_spacing',
			'content'
		);
		$rating_container_margin    = $_ex::add_margin_padding_field(
			'rating_container_margin',
			'Rating Container Margin',
			'custom_spacing',
			'container'
		);
		$innercontent_padding       = $_ex::add_margin_padding_field(
			'innercontent_padding',
			'Inner Wrapper Padding',
			'custom_spacing',
			'container'
		);
		$carousel_container_margin  = $_ex::add_margin_padding_field(
			'carousel_container_margin',
			'Carousel Container Margin',
			'custom_spacing',
			'container'
		);
		$carousel_container_padding = $_ex::add_margin_padding_field(
			'carousel_container_padding',
			'Carousel Container Padding',
			'custom_spacing',
			'container'
		);

		return array_merge(
			$general,
			$zindex,
			$title_background,
			$subtitle_background,
			$content_background,
			$image_settings,
			$next_prev_button_settings,
			$carousel_container_margin,
			$carousel_container_padding,
			$innercontent_padding,
			$item_padding,
			$image_container_margin,
			$image_container_padding,
			$image_padding,
			$title_margin,
			$title_padding,
			$subtitle_margin,
			$subtitle_padding,
			$text_margin,
			$text_padding,
			$button_margin,
			$button_padding,
			$content_container_margin,
			$content_container_padding,
			$social_container_margin,
			$social_container_padding,
			$social_item_margin,
			$social_item_padding,
			$rating_container_margin,
			$design_active_slide,
			$design_inactive_slide,
			$design_container_style
		);
	}

	public function return_data_value( $value ) {
		return ( ! empty( $value ) ) ? $value : '';
	}

	public function additional_css_styles( $render_slug ) {
		$image_width           = $this->props['image_sizing'];
		$image_align           = $this->props['align'];
		$inner_content_padding = array_diff( explode( "|", $this->props['innercontent_padding'] ), [
			'true',
			'false'
		] );
		$order_class           = self::get_module_order_class( $render_slug );
		$_ex                   = "DICA_Extends";

		// add transition values to items
		// ET_Builder_Element::set_style($render_slug, array(
		// 	'selector' => '%%order_class%% .dica_divi_carouselitem > div, %%order_class%% .dica_divi_carouselitem > div *, %%order_class%% .dica_divi_carouselitem .dica-rating span:before',
		// 	'declaration' => sprintf('transition: all %1$s %2$s %3$s !important;',
		// 		$this->props['hover_transition_duration'],
		// 		$this->props['hover_transition_speed_curve'],
		// 		$this->props['hover_transition_delay']
		// 	),
		// ));
		// ET_Builder_Element::set_style($render_slug, array(
		// 	'selector' => '%%order_class%% .dica_divi_carouselitem > div, %%order_class%% .dica_divi_carouselitem > div *, %%order_class%% .dica_divi_carouselitem .dica-rating span:before',
		// 	'declaration' => sprintf('transition: all %1$s %2$s %3$s !important;',
		// 		$this->props['hover_transition_duration'],
		// 		$this->props['hover_transition_speed_curve'],
		// 		$this->props['hover_transition_delay']
		// 	),
		// ));

		// Items Margin and Padding
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'item_padding',
			'padding',
			'%%order_class%% .dica_divi_carouselitem > div',
			'%%order_class%% .dica_divi_carouselitem > div:hover'
		);
		// Image container Margin and Padding
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'image_container_margin',
			'margin',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-image-container',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-image-container'
		);
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'image_container_padding',
			'padding',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-image-container',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-image-container'
		);
		// Image Margin and Padding
		// $this->apply_margin_padding(
		// 	$render_slug,
		// 	'image_margin',
		// 	'margin',
		// 	'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-image-container img',
		// 	'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-image-container img'
		// );
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'image_padding',
			'padding',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-image-container img',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-image-container img'
		);
		// Content Container Margin and Padding
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'content_container_margin',
			'margin',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content'
		);
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'content_container_padding',
			'padding',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content'
		);
		// Title Margin and Padding
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'title_margin',
			'margin',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > .item-title',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content > .item-title'
		);
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'title_padding',
			'padding',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > .item-title',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content > .item-title'
		);
		// Subtitle Margin and Padding
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'subtitle_margin',
			'margin',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > .item-subtitle',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content > .item-subtitle'
		);
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'subtitle_padding',
			'padding',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > .item-subtitle',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content > .item-subtitle'
		);
		// Text Margin and Padding
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'text_margin',
			'margin',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content .content',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content .content'
		);
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'text_padding',
			'padding',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content .content',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content .content'
		);
		// Button Margin and Padding
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'button_margin',
			'margin',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > div:not(.content):not(.dica-image-container):not(.social-media-container) a',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content > div:not(.content):not(.dica-image-container):not(.social-media-container) a'
		);
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'button_padding',
			'padding',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > div:not(.content):not(.dica-image-container):not(.social-media-container) a',
			'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem:hover .dica-item-content > div:not(.content):not(.dica-image-container):not(.social-media-container) a'
		);
		// social media container spacing
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'social_container_margin',
			'margin',
			'%%order_class%% .dica_divi_carouselitem .social-media-container',
			'%%order_class%% .dica_divi_carouselitem:hover .social-media-container'
		);
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'social_container_padding',
			'padding',
			"%%order_class%% .dica_divi_carouselitem .social-media-container",
			"%%order_class%% .dica_divi_carouselitem:hover .social-media-container"
		);
		// social media item spacing
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'social_item_margin',
			'margin',
			'%%order_class%% .dica_divi_carouselitem .social-media-container ul li',
			'%%order_class%% .dica_divi_carouselitem .social-media-container ul li:hover'
		);
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'social_item_padding',
			'padding',
			'%%order_class%% .dica_divi_carouselitem .social-media-container ul li a',
			'%%order_class%% .dica_divi_carouselitem .social-media-container ul li:hover a'
		);
		// rating container spacing
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'rating_container_margin',
			'margin',
			'%%order_class%% .dica_divi_carouselitem .dica-rating-container',
			'%%order_class%% .dica_divi_carouselitem:hover .dica-rating-container'
		);
		// Carousel container margin
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'carousel_container_margin',
			'margin',
			'%%order_class%%.dica_divi_carousel .dica-container',
			'%%order_class%%.dica_divi_carousel .dica-container:hover'
		);
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'carousel_container_padding',
			'padding',
			'%%order_class%%.dica_divi_carousel .dica-container',
			'%%order_class%%.dica_divi_carousel .dica-container:hover'
		);
		// Inner container padding
		$_ex::apply_margin_padding(
			$this,
			$render_slug,
			'innercontent_padding',
			'padding',
			'%%order_class%% .swiper-container',
			'%%order_class%% .swiper-container:hover'
		);
		// title background color
		$_ex::apply_element_color(
			$this,
			$render_slug,
			'title_background',
			'background-color',
			'%%order_class%% .dica-item .dica-item-content > .item-title',
			'%%order_class%% .dica_divi_carouselitem:hover .dica-item .dica-item-content > .item-title',
			true
		);
		// title background color
		$_ex::apply_element_color(
			$this,
			$render_slug,
			'subtitle_background',
			'background-color',
			'%%order_class%% .dica-item .dica-item-content > .item-subtitle',
			'%%order_class%% .dica_divi_carouselitem:hover .dica-item .dica-item-content > .item-subtitle',
			true
		);
		// content background color
		$_ex::apply_element_color(
			$this,
			$render_slug,
			'content_background',
			'background-color',
			'%%order_class%% .dica-item .dica-item-content > .content',
			'%%order_class%% .dica_divi_carouselitem:hover .dica-item .dica-item-content > .content',
			true
		);
		// Apply item width
		if ( 'on' === $this->props['item_width_auto'] ) {
			$_ex::control_width_and_spacing(
				$this,
				$render_slug,
				'item_width',
				'width',
				'%%order_class%%.dica_divi_carousel .dica_divi_carouselitem' );
		}
		// if ('on' === $this->props['item_width_auto'] && 'on' === $this->props['width_auto']) {
		// 	ET_Builder_Element::set_style( $render_slug, array(
		//         'selector'    => '%%order_class%% .dica_divi_carouselitem',
		//         'declaration' =>'width:auto;',
		//     ) );
		// }
		// image width control
		$image_width = '' == $image_width ? '100%' : $image_width;
		if ( '' !== $image_width && 'on' !== $this->props['image_force_fullwidth'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica_divi_carouselitem .dica-image-container .image',
				'declaration' => sprintf(
					'max-width:%1$s;', $image_width ),
			] );
		}
		if ( 'on' === $this->props['image_force_fullwidth'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica_divi_carouselitem .dica-image-container img',
				'declaration' => 'width: 100%;',
			] );
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica_divi_carouselitem .dica-image-container .image',
				'declaration' => 'width: 100%;',
			] );
		}
		// image alignment
		if ( '' !== $image_align ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica_divi_carouselitem .dica-image-container',
				'declaration' => sprintf(
					'text-align: %1$s!important;', $image_align ),
			] );
		} else {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica_divi_carouselitem .dica-image-container',
				'declaration' => 'text-align: center!important;',
			] );
		}
		// arrow an dot color
		$_ex::apply_element_color(
			$this,
			$render_slug,
			'arrow_nav_color',
			'color',
			'%%order_class%% .swiper-button-next:before,%%order_class%% .swiper-button-prev:before',
			'%%order_class%% .swiper-button-next:hover:before,%%order_class%% .swiper-button-prev:hover:before',
			true
		);
		// if('' !== $this->props['arrow_nav_color']) {
		//     ET_Builder_Element::set_style( $render_slug, array(
		//         'selector'    => '%%order_class%% .swiper-button-next:before,%%order_class%% .swiper-button-prev:before',
		//         'declaration' => sprintf(
		//             'color:%1$s!important;', $this->props['arrow_nav_color']),
		//     ) );
		// }
		$_ex::apply_element_color(
			$this,
			$render_slug,
			'arrow_bg_color',
			'background-color',
			'%%order_class%%.dica_divi_carousel .swiper-button-next,%%order_class%%.dica_divi_carousel .swiper-button-prev',
			'%%order_class%%.dica_divi_carousel .swiper-button-next:hover,%%order_class%%.dica_divi_carousel .swiper-button-prev:hover',
			true
		);
		// if('' !== $this->props['arrow_bg_color']) {
		//     ET_Builder_Element::set_style( $render_slug, array(
		//         'selector'    => '%%order_class%%.dica_divi_carousel .swiper-button-next,%%order_class%%.dica_divi_carousel .swiper-button-prev',
		//         'declaration' => sprintf(
		//             'background-color:%1$s!important;', $this->props['arrow_bg_color']),
		//     ) );
		// }
		if ( '' !== $this->props['dots_color'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .swiper-pagination-bullet',
				'declaration' => sprintf(
					'background-color:%1$s!important;', $this->props['dots_color'] ),
			] );
		}
		if ( '' !== $this->props['dots_active_color'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .swiper-pagination-bullet.swiper-pagination-bullet-active',
				'declaration' => sprintf(
					'background-color:%1$s!important;', $this->props['dots_active_color'] ),
			] );
		}
		// equal height
		if ( 'on' === $this->props['equal_height'] && 'on' !== $this->props['multiple_slide_row'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .dica-container .swiper-wrapper .dica_divi_carouselitem',
				'declaration' => 'height:100%;',
			] );
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .dica-container .swiper-container',
				'declaration' => 'display:flex;',
			] );
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content',
				'declaration' => 'flex-grow: 1;',
			] );
		}
		if ( 'on' !== $this->props['equal_height'] && '' !== $this->props['item_vertical_align'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .dica-container .swiper-wrapper .dica_divi_carouselitem',
				'declaration' => sprintf( 'align-self:%1$s;', $this->props['item_vertical_align'] ),
			] );
		}
		if ( '' !== $this->props['dot_alignment'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .dica-container .swiper-pagination',
				'declaration' => sprintf( 'text-align:%1$s;', $this->props['dot_alignment'] ),
			] );
		}
		// overlay custom icon style
		if ( 'on' === $this->props['use_overlay_icon'] ) {
			$overlay_icon = html_entity_decode( esc_attr( et_pb_process_font_icon( $this->props['overlay_icon'] ) ) );
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:after',
				'declaration' => sprintf( 'content:"%1$s" !important;', $overlay_icon ),
			] );
		}
		if ( 'on' === $this->props['overlay_image'] ) {
			if ( !empty( $this->props['overlay_color'] ) && empty($this->props['overlay_color_field_bgcolor']) ) {
				ET_Builder_Element::set_style(
					$render_slug,
					array(
						'selector'    => '%%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:before',
						'declaration' => sprintf( 'background-color:%1$s !important;', $this->props['overlay_color'] ),
					)
				);
			} else {
				$this->process_bg(
					array(
						'render_slug' => $render_slug,
						'slug'        => 'overlay_color_field',
						'selector'    => '%%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:before',
						'hover'       => '%%order_class%%.dica_divi_carousel .overlay-image .dica-item:hover .dica-image-container .image:before',
					)
				);
			}

			if("on" === $this->props['use_overlay_animated_icon']){
				ET_Builder_Element::set_style( $render_slug, [
					'selector'    => '%%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:has(.animated_icon) .animated_icon span',
					'declaration' => sprintf( 'border-left-color:%1$s !important;', $this->props['overlay_anim_icon_color'] ),
				] );
				$this->process_bg([
					'render_slug' => $render_slug,
					'slug'        => 'overlay_anim_icon_bg',
					'selector'    => "%%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:has(.animated_icon) .animated_icon:after",
					'hover'       => "%%order_class%%.dica_divi_carousel .overlay-image .dica-item:hover .dica-image-container .image:has(.animated_icon) .animated_icon:after",
				]);
				// Animated Icon Border
				ET_Builder_Element::set_style( $render_slug, [
					'selector'    => '%%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:has(.animated_icon) .animated_icon:after, %%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:has(.animated_icon) .animated_icon.anim-pulse-border:before',
					'declaration' => sprintf( 'border-width:%1$s !important;', $this->props['overlay_anim_icon_border_width'] ),
				] );
				ET_Builder_Element::set_style( $render_slug, [
					'selector'    => '%%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:has(.animated_icon) .animated_icon:after',
					'declaration' => sprintf( 'border-color:%1$s !important;', $this->props['overlay_anim_icon_border_color'] ),
				] );

				// Animated Icon Ripple
				ET_Builder_Element::set_style( $render_slug, [
					'selector'    => '%%order_class%%.dica_divi_carousel',
					'declaration' => sprintf( '--dica-border-ripple-color:%1$s', $this->props['overlay_anim_icon_ripple_color'] ),
				] );
			}

			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:after',
				'declaration' => sprintf( 'color:%1$s !important;', $this->props['overlay_icon_color'] ),
			] );
			$_ex::process_single_value( [
				'module'      => $this,
				'render_slug' => $render_slug,
				'slug'        => 'overlay_icon_size',
				'selector'    => '%%order_class%%.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:after',
				'type'        => 'font-size',
				'unit_type'   => true,
				'unit'        => 'px',
				'default'     => '26px',
			] );
		}

		// arrow width and height and font-size
		if ( isset( $this->props['arrow_font_size'] ) && '' !== $this->props['arrow_font_size'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica-container .swiper-button-next, %%order_class%% .dica-container .swiper-button-prev',
				'declaration' => sprintf( 'font-size:%1$s; width:%1$s; height:%1$s;',
					$this->props['arrow_font_size'] ),
			] );
		}
		if ( isset( $this->props['arrow_font_size_tablet'] ) && '' !== $this->props['arrow_font_size_tablet'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica-container .swiper-button-next, %%order_class%% .dica-container .swiper-button-prev',
				'declaration' => sprintf( 'font-size:%1$s; width:%1$s; height:%1$s;',
					$this->props['arrow_font_size_tablet'] ),
				'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
			] );
		}
		if ( isset( $this->props['arrow_font_size_phone'] ) && '' !== $this->props['arrow_font_size_phone'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica-container .swiper-button-next, %%order_class%% .dica-container .swiper-button-prev',
				'declaration' => sprintf( 'font-size:%1$s; width:%1$s; height:%1$s;',
					$this->props['arrow_font_size_phone'] ),
				'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
			] );
		}
		if ( isset( $this->props['arrow_icon_spacing'] ) && '' !== $this->props['arrow_icon_spacing'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica-container .swiper-button-next, %%order_class%% .dica-container .swiper-button-prev',
				'declaration' => sprintf( 'padding:%1$s;',
					$this->props['arrow_icon_spacing'] ),
			] );
		}
		// arrow alignment
		if ( isset( $this->props['arrow_alignment'] ) ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica-container .swiper-buttton-container',
				'declaration' => sprintf( 'justify-content:%1$s;',
					$this->props['arrow_alignment'] ),
			] );
		}

		// smooth scroll effect
		if ( 'on' === $this->props['scroller_effect'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .swiper-wrapper',
				'declaration' => '-webkit-transition-timing-function:linear!important;
				-o-transition-timing-function:linear!important;
				transition-timing-function: linear !important;
				transition-duration:2000ms;',
			] );
		}
		// add transition values to items
		// ET_Builder_Element::set_style($render_slug, array(
		// 	'selector' => sprintf('%1$s', implode(',', array_unique($this->hoverTransitionSelector))),
		// 	'declaration' => sprintf('transition: all %1$s %2$s %3$s,
		// 	width 0ms, height 0ms, flex 0ms, flex-shrink 0ms, flex-grow 0ms !important;
		// 	animation-duration: 0ms !important;',
		// 		$this->props['hover_transition_duration'],
		// 		$this->props['hover_transition_speed_curve'],
		// 		$this->props['hover_transition_delay']
		// 	),
		// ));
		$_ex::process_single_value( [
			'module'      => $this,
			'render_slug' => $render_slug,
			'slug'        => 'image_container_zindex',
			'selector'    => '%%order_class%% .dica_divi_carouselitem .dica-image-container',
			'type'        => 'z-index',
			'unit_type'   => false,
			'default'     => '10',
		] );
		$_ex::process_single_value( [
			'module'      => $this,
			'render_slug' => $render_slug,
			'slug'        => 'content_container_zindex',
			'selector'    => '%%order_class%% .dica_divi_carouselitem .dica-item-content',
			'type'        => 'z-index',
			'unit_type'   => false,
			'default'     => '10',
		] );


		if ( 'on' !== $this->props['item_width_auto'] ) {
			$desktop_item = (int) $this->props['show_items_desktop'];
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%% .dica_divi_carouselitem',
				'declaration' => "width:calc(100%/{$desktop_item});"
			] );
		}
		// icon font family
		if ( method_exists( 'ET_Builder_Module_Helper_Style_Processor', 'process_extended_icon' ) ) {
			$this->generate_styles(
				[
					'utility_arg'    => 'icon_font_family',
					'render_slug'    => $render_slug,
					'base_attr_name' => 'prev_icon',
					'important'      => true,
					'selector'       => '%%order_class%% .swiper-button-prev:before',
					'processor'      => [
						'ET_Builder_Module_Helper_Style_Processor',
						'process_extended_icon',
					],
				]
			);
			$this->generate_styles(
				[
					'utility_arg'    => 'icon_font_family',
					'render_slug'    => $render_slug,
					'base_attr_name' => 'next_icon',
					'important'      => true,
					'selector'       => '%%order_class%% .swiper-button-next:before',
					'processor'      => [
						'ET_Builder_Module_Helper_Style_Processor',
						'process_extended_icon',
					],
				]
			);
			$this->generate_styles(
				[
					'utility_arg'    => 'icon_font_family',
					'render_slug'    => $render_slug,
					'base_attr_name' => 'overlay_icon',
					'important'      => true,
					'selector'       => '%%order_class%% .overlay-image .dica-item .dica-image-container .image:after',
					'processor'      => [
						'ET_Builder_Module_Helper_Style_Processor',
						'process_extended_icon',
					],
				]
			);
		}

		/*----------- Active Slide -----------*/
		if ( 'on' === $this->props['field_enable_active_slide_design'] && 'on' !== $this->props['multislide'] ) {
			$this->process_bg([
				'render_slug' => $render_slug,
				'slug'        => 'active_slide_bg_color',
				'selector'    => "%%order_class%%.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem.swiper-slide-active  > div:first-of-type",
				'hover'       => "%%order_class%%.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem.swiper-slide-active:hover > div:first-of-type",
				'important' => true
			]);
		}
		/*----------- Active Slide -----------*/

		/*----------- Inactive Slide -----------*/
		if ( 'on' === $this->props['field_enable_inactive_slide_design'] && 'on' !== $this->props['multislide'] ) {
			$this->process_bg([
				'render_slug' => $render_slug,
				'slug'        => 'inactive_slide_bg_color',
				'selector'    => "%%order_class%%.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem:not(.swiper-slide-active)  > div:first-of-type",
				'hover'       => "%%order_class%%.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem:not(.swiper-slide-active):hover > div:first-of-type",
				'important' => true
			]);
			if ( 'on' === $this->props['field_enable_inactive_slide_blur'] ) {
				$field_inactive_slide_blur = ! empty( $this->props['field_inactive_slide_blur'] ) ? $this->props['field_inactive_slide_blur'] : '2px';
				ET_Builder_Element::set_style( $render_slug, [
					'selector'    => '%%order_class%%.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem:not(.swiper-slide-active)',
					'declaration' => sprintf( 'filter: blur(%1$spx);', (int) $field_inactive_slide_blur )
				] );
			}

			if ( 'on' === $this->props['field_disable_hover_click'] ) {
				ET_Builder_Element::set_style( $render_slug, [
					'selector'    => '%%order_class%%.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem:not(.swiper-slide-active)',
					'declaration' => 'pointer-events: none;'
				] );
			}

			if ( 'on' === $this->props['field_inactive_item_scaling'] && 'coverflow' !== $this->props['advanced_effect'] ) {
				$transition_time = ! empty( $this->props['transition_duration'] ) ? (int) $this->props['transition_duration'] : 1000;
				if ( 'on' === $this->props['scroller_effect'] ) {
					$transition_time = ! empty( $props['transition_duration_scroll'] ) ? (int) $props['transition_duration_scroll'] : 4000;
					$transition_time -= 1000;
				}
				ET_Builder_Element::set_style( $render_slug, [
					'selector'    => '%%order_class%%.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem.swiper-slide-active,%%order_class%%.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem:not(.swiper-slide-active)',
					'declaration' => sprintf( 'transition: transform %1$sms ease-out;', $transition_time )
				] );
				$field_inactive_item_scaling_range = ! empty( $this->props['field_inactive_item_scaling_range'] ) ? $this->props['field_inactive_item_scaling_range'] : '1';
				ET_Builder_Element::set_style( $render_slug, [
					'selector'    => '%%order_class%%.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem:not(.swiper-slide-active)',
					'declaration' => sprintf( 'transform: scale(%1$s);', $field_inactive_item_scaling_range )
				] );

			}

		}

		/*----------- Lardge active dot -----------*/
		if ( 'on' === $this->props['dot_nav'] && 'on' === $this->props['field_large_active_bullet'] ) {
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active',
				'declaration' => 'width: 40px; border-radius: 20px !important;'
			] );
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .dica-container.pag-horizontal .swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active',
				'declaration' => 'height: 40px; width: var(--swiper-pagination-bullet-width, var(--swiper-pagination-bullet-size, 8px)); border-radius: 20px !important;'
			] );

			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel .swiper-pagination .swiper-pagination-bullet',
				'declaration' => "transition: all .5s ease-out;"
			] );
		}

		/*----- Container Style -------*/
		if ( 'on' === $this->props['field_enable_container_shadow_effect'] ) {
			$container_shadow_primary_color   = ! empty( $this->props['field_container_shadow_primary_color'] ) ? $this->props['field_container_shadow_primary_color'] : 'rgba(255,255,255,0.85)';
			$container_shadow_secondary_color = ! empty( $this->props['field_container_shadow_secondary_color'] ) ? $this->props['field_container_shadow_secondary_color'] : 'rgba(255,255,255,0.0)';
			$container_shadow_width           = ! empty( $this->props['field_container_shadow_width'] ) ? $this->props['field_container_shadow_width'] : '70%';
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel:has(.dica-container.has_container_shadow) .swiper-container::before',
				'declaration' => sprintf( 'right: %1$s;background-image: linear-gradient(to right, %2$s, %3$s);', $container_shadow_width, $container_shadow_primary_color, $container_shadow_secondary_color )
			] );
			ET_Builder_Element::set_style( $render_slug, [
				'selector'    => '%%order_class%%.dica_divi_carousel:has(.dica-container.has_container_shadow) .swiper-container::after',
				'declaration' => sprintf( 'left: %1$s;background-image: linear-gradient(to left, %2$s, %3$s);', $container_shadow_width, $container_shadow_primary_color, $container_shadow_secondary_color )
			] );
		}
	}

	public function get_transition_fields_css_props() {
		$fields = parent::get_transition_fields_css_props();

		$fields['title_background']    = [ 'background-color' => '%%order_class%% .dica-item .dica-item-content > .item-title' ];
		$fields['subtitle_background'] = [ 'background-color' => '%%order_class%% .dica-item .dica-item-content > .item-subtitle' ];
		$fields['content_background']  = [ 'background-color' => '%%order_class%% .dica-item .dica-item-content > .content' ];
		$fields['arrow_font_size']     = [ 'font-size' => '%%order_class%% .dica-container .swiper-button-next, %%order_class%% .dica-container .swiper-button-prev' ];

		$fields['arrow_nav_color'] = [ 'color' => '%%order_class%% .swiper-button-next:before,%%order_class%% .swiper-button-prev:before' ];
		$fields['arrow_bg_color']  = [ 'background-color' => '%%order_class%%.dica_divi_carousel .swiper-button-next:before,%%order_class%%.dica_divi_carousel .swiper-button-prev:before' ];

		$fields['item_margin']  = [ 'margin' => '%%order_class%% .dica_divi_carouselitem > div' ];
		$fields['item_padding'] = [ 'padding' => '%%order_class%% .dica_divi_carouselitem > div' ];

		$fields['image_container_margin']  = [ 'margin' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-image-container' ];
		$fields['image_container_padding'] = [ 'padding' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-image-container' ];
		$fields['image_padding']           = [ 'padding' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-image-container img' ];

		$fields['content_container_margin']  = [ 'margin' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content' ];
		$fields['content_container_padding'] = [ 'padding' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content' ];

		$fields['title_margin']  = [ 'margin' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > .item-title' ];
		$fields['title_padding'] = [ 'padding' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > .item-title' ];

		$fields['subtitle_margin']  = [ 'margin' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > .item-subtitle' ];
		$fields['subtitle_padding'] = [ 'padding' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > .item-subtitle' ];

		$fields['text_margin']  = [ 'margin' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content .content' ];
		$fields['text_padding'] = [ 'padding' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content .content' ];

		$fields['button_margin']  = [ 'margin' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > div:not(.content):not(.dica-image-container):not(.social-media-container) a' ];
		$fields['button_padding'] = [ 'padding' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .dica-item-content > div:not(.content):not(.dica-image-container):not(.social-media-container) a' ];

		$fields['social_container_margin']  = [ 'margin' => '%%order_class%% .dica_divi_carouselitem .social-media-container' ];
		$fields['social_container_padding'] = [ 'padding' => '%%order_class%% .dica_divi_carouselitem .social-media-container' ];
		$fields['social_item_margin']       = [ 'margin' => '%%order_class%% .dica_divi_carouselitem .social-media-container ul li' ];
		$fields['social_item_padding']      = [ 'padding' => '%%order_class%% .dica_divi_carouselitem .social-media-container ul li a' ];

		$fields['rating_container_margin'] = [ 'margin' => '%%order_class%% .dica_divi_carouselitem .dica-rating-container' ];

		$fields['innercontent_padding']       = [ 'padding' => '%%order_class%% .swiper-container' ];
		$fields['carousel_container_margin']  = [ 'margin' => '%%order_class%%.dica_divi_carousel .dica-container' ];
		$fields['carousel_container_padding'] = [ 'padding' => '%%order_class%%.dica_divi_carousel .dica-container' ];

		$fields['border_width_all_item'] = [ 'border-width' => '%%order_class%% .dica_divi_carouselitem > div' ];
		$fields['border_radii_item']     = [ 'border-radius' => '%%order_class%% .dica_divi_carouselitem > div' ];
		$fields['border_color_all_item'] = [ 'border-color' => '%%order_class%% .dica_divi_carouselitem > div' ];
		$fields['border_style_all_item'] = [ 'border-style' => '%%order_class%% .dica_divi_carouselitem > div' ];

		return $fields;
	}

	public function get_custom_css_fields_config() {
		return [
			'carousel_item' => [
				'label'    => esc_html__( 'Carousel Item', 'dica-divi-carousel' ),
				'selector' => '%%order_class%%.dica_divi_carousel .dica-container .swiper-wrapper .dica_divi_carouselitem',
			],
			'title'         => [
				'label'    => esc_html__( 'Title', 'dica-divi-carousel' ),
				'selector' => '%%order_class%%.dica_divi_carousel .dica-container .swiper-wrapper .dica_divi_carouselitem .item-title',
			],
			'content'       => [
				'label'    => esc_html__( 'Content', 'dica-divi-carousel' ),
				'selector' => '%%order_class%%.dica_divi_carousel .dica-container .swiper-wrapper .dica_divi_carouselitem .content',
			],
			'image'         => [
				'label'    => esc_html__( 'Image', 'dica-divi-carousel' ),
				'selector' => '%%order_class%%.dica_divi_carousel .dica-container .swiper-wrapper .dica_divi_carouselitem .dica-image-container img',
			],
			'button'        => [
				'label'    => esc_html__( 'Button', 'dica-divi-carousel' ),
				'selector' => '%%order_class%%.dica_divi_carousel .dica-container .swiper-wrapper .dica_divi_carouselitem .et_pb_button',
			],
			'social_media'  => [
				'label'    => esc_html__( 'Social Icon', 'dica-divi-carousel' ),
				'selector' => '%%order_class%%.dica_divi_carousel .dica_divi_carouselitem .social-media-container .social-media li a',
			],
		];
	}

	function before_render() {
		global $dg_carousel_helper_data;

		$use_overlay_animated_icon   = $this->props['use_overlay_animated_icon'];
		$animated_overlay_icon_style = $this->props['animated_overlay_icon_style'];

		$dg_carousel_helper_data = [
			'use_overlay_animated_icon'   => $use_overlay_animated_icon,
			'animated_overlay_icon_style' => $animated_overlay_icon_style
		];

	}

	public function render( $attrs, $content, $render_slug ) {
		global $dg_carousel_helper_data;
		$classes                = '';
		$item_spacing           = $this->props['item_spacing'];
		$item_spacing_tablet    = isset( $this->props['item_spacing_tablet'] ) && $this->props['item_spacing_tablet'] !== '' ?
			$this->props['item_spacing_tablet'] : $item_spacing;
		$item_spacing_phone     = isset( $this->props['item_spacing_phone'] ) && $this->props['item_spacing_phone'] !== '' ?
			$this->props['item_spacing_phone'] : $item_spacing;
		$arrow_show_hover_class = 'on' === $this->props['arrow_show_hover'] ? ' arrow-show-hover' : '';

		$multiple_slide_row = "on" !== $this->props['equal_height'] && 'coverflow' !== $this->props['advanced_effect'] ? $this->props['multiple_slide_row'] : 'off';
		$slide_row          = $this->props['slide_row'];
		$slide_row_tablet   = $this->props['slide_row_tablet'] ? $this->props['slide_row_tablet'] : $slide_row;
		$slide_row_phone    = $this->props['slide_row_phone'] ? $this->props['slide_row_phone'] : $slide_row_tablet;
		$keyboard_control   = $this->props['keyboard_control'];
		$mousewheel_control = $this->props['mousewheel_control'];
		$autoplay_viewport  = $this->props['autoplay_viewport'];

		$order_class  = self::get_module_order_class( $render_slug );
		$order_number = str_replace( '_', '', str_replace( $this->slug, '', $order_class ) );

		$this->additional_css_styles( $render_slug );
		$coverflow = sprintf( 'cover-rotate="%1$s" ',
			$this->return_data_value( $this->props['coverflow_rotate'] )
		);


		// filter for main container
		if ( array_key_exists( 'image', $this->advanced_fields ) && array_key_exists( 'css', $this->advanced_fields['image'] ) ) {
			$this->add_classname( $this->generate_css_filters(
				$render_slug,
				'child_',
				self::$data_utils->array_get( $this->advanced_fields['image']['css'], 'main', '%%order_class%%' )
			) );
		}

		$data_attr = [
			'desktop'             => $this->return_data_value( $this->props['show_items_desktop'] ),
			'tablet'              => $this->return_data_value( $this->props['show_items_tablet'] ),
			'mobile'              => $this->return_data_value( $this->props['show_items_mobile'] ),
			'speed'               => $this->return_data_value( $this->props['transition_duration'] ),
			'arrow'               => $this->return_data_value( $this->props['arrow_nav'] ),
			'dots'                => $this->return_data_value( $this->props['dot_nav'] ),
			'autoplay'            => $this->return_data_value( $this->props['autoplay'] ),
			'autoSpeed'           => $this->return_data_value( $this->props['autoplay_speed'] ),
			'loop'                => 'off' !== $multiple_slide_row ? '' : $this->return_data_value( $this->props['loop'] ),
			'item_spacing'        => $this->props['item_spacing'],
			'center_mode'         => 'off' !== $multiple_slide_row ? '' : $this->return_data_value( $this->props['centermode'] ),
			'slider_effec'        => $this->return_data_value( $this->props['advanced_effect'] ),
			'cover_rotate'        => $this->return_data_value( $this->props['coverflow_rotate'] ),
			'pause_onhover'       => $this->return_data_value( $this->props['hoverpause'] ),
			'multislide'          => 'off' !== $multiple_slide_row ? '' : $this->return_data_value( $this->props['multislide'] ),
			'cfshadow'            => $this->props['coverflow_shadow'],
			'order'               => $order_number,
			'lazyload'            => $this->props['lazy_loading'],
			'lazybefore'          => $this->props['load_before_transition'],
			'scroller_effect'     => $this->props['scroller_effect'],
			'autowidth'           => $this->props['item_width_auto'],
			'item_spacing_tablet' => $item_spacing_tablet,
			'item_spacing_phone'  => $item_spacing_phone,
			'scroller_speed'      => $this->props['transition_duration_scroll'],
			'hashNavigation'      => $this->props['dg_hash_nav'],
			'simulatetouch'       => $this->props['simulatetouch'],
			'allowtouchmove'      => 'on' === $this->props['simulatetouch'] ? $this->props['allowtouchmove'] : 'off',
			'slide_row'           => 'off' !== $multiple_slide_row ? $slide_row : '1',
			'slide_row_tablet'    => 'off' !== $multiple_slide_row ? esc_attr( $slide_row_tablet ) : '1',
			'slide_row_phone'     => 'off' !== $multiple_slide_row ? esc_attr( $slide_row_phone ) : '1',
			'keyboard'            => $this->return_data_value( $keyboard_control ),
			'mousewheel'          => $this->return_data_value( $mousewheel_control ),
			'autoplay_viewport'   => 'off' !== $this->props['autoplay'] && '' !== $autoplay_viewport ? esc_attr( $autoplay_viewport ) : '',
		];

		$pagination = ( 'on' === $this->props['dot_nav'] ) ?
			sprintf( '<div class="swiper-pagination dica-paination-%1$s"></div>', $order_number ) : '';

		// add overlay classes
		if ( 'on' === $this->props['overlay_image'] ) {
			if("off" === $this->props['overlay_on_hover']){
				$classes = $this->add_class( $classes, 'overlay_image_default' );
			}
			$classes = $this->add_class( $classes, 'overlay-image' );
		}
		// add arrow class
		$classes = $this->add_class( $classes, $this->carousel_arrow_classes() );
		// arrow show on hover
		if ( 'on' === $this->props['arrow_show_hover'] ) {
			$classes = $this->add_class( $classes, 'arrow-on-hover' );
		}
		$container_shadow_class = 'on' === $this->props['field_enable_container_shadow_effect'] ? ' has_container_shadow' : '';

		add_filter( 'et_global_assets_list', [ $this, 'dgcm_load_required_divi_assets' ], 10, 3 );
		add_filter( 'et_late_global_assets_list', [ $this, 'dgcm_load_required_divi_assets' ], 10, 3 );

		$rtl = 'off';
		if( 'on' === $this->props['field_enable_rtl'] && 'on' !== $this->props['multiple_slide_row'] && 'on' !== $this->props['lazy_loading'] ){
			$rtl = 'on';
		}

		$output = sprintf( '<div class="dica-container %5$s%7$s" data-props=\'%2$s\'>
							 	<div class="swiper-container" %6$s>
									<div class="swiper-wrapper">%1$s</div>
								</div>
								%3$s
								%4$s
							</div>',
			et_core_sanitized_previously( $this->content ),
			wp_json_encode( $data_attr ),
			$this->carousel_arraow_markup( $order_number ),
			$pagination,
			$classes,
			( 'on' === $rtl ) ? esc_attr( 'dir=rtl' ) : '',
			$container_shadow_class
		);

		return $output;
	}

	/**
	 * Add module Classes
	 */
	public function add_class( $classes, $class = '' ) {
		return $classes .= ' ' . $class;
	}

	/**
	 * Return the arraow markup depending on settings
	 */
	public function carousel_arraow_markup( $order_number ) {
		$arrow_navigation = '';
		$data_prev_icon   = 'on' === $this->props['use_prev_icon'] ?
			sprintf( 'data-icon="%1$s"', esc_attr( et_pb_process_font_icon( $this->props['prev_icon'] ) ) ) : 'data-icon="4"';
		$data_next_icon   = 'on' === $this->props['use_next_icon'] ?
			sprintf( 'data-icon="%1$s"', esc_attr( et_pb_process_font_icon( $this->props['next_icon'] ) ) ) : 'data-icon="5"';

		if ( 'on' === $this->props['arrow_nav'] ) {
			$arrow_navigation = sprintf( '<div class="swiper-button-prev dica-prev-btn-%1$s" %2$s></div><div class="swiper-button-next dica-next-btn-%1$s" %3$s></div>',
				$order_number,
				$data_prev_icon,
				$data_next_icon );

			$arrow_navigation = sprintf( '<div class="swiper-buttton-container">%1$s</div>', $arrow_navigation );
		}

		return $arrow_navigation;
	}

	/**
	 * Generate arrow position classes
	 */
	public function carousel_arrow_classes() {
		$arrow_position_desktop = empty( $this->props['arrow_position'] ) || 'on' === $this->props['arrow_position'] ? 'middle-inside' : $this->props['arrow_position'];
		$arrow_position_tablet  = isset( $this->props['arrow_position_tablet'] ) && '' !== $this->props['arrow_position_tablet'] ?
			$this->props['arrow_position_tablet'] : $arrow_position_desktop;
		$arrow_position_mobile  = isset( $this->props['arrow_position_phone'] ) && '' !== $this->props['arrow_position_phone'] ?
			$this->props['arrow_position_phone'] : $arrow_position_desktop;

		$arrow_position_desktop = 'desktop_' . $arrow_position_desktop;
		$arrow_position_tablet  = 'tablet_' . $arrow_position_tablet;
		$arrow_position_mobile  = 'mobile_' . $arrow_position_mobile;

		return $arrow_position_desktop . ' ' . $arrow_position_tablet . ' ' . $arrow_position_mobile;
	}

	public function dgcm_load_required_divi_assets( $assets_list, $assets_args, $instance ) {
		$assets_prefix  = et_get_dynamic_assets_path();
		$all_shortcodes = $instance->get_saved_page_shortcodes();

		if ( ! isset( $assets_list['et_icons_all'] ) ) {
			$assets_list['et_icons_all'] = [
				'css' => "{$assets_prefix}/css/icons_all.css",
			];
		}

		if ( ! isset( $assets_list['et_icons_fa'] ) ) {
			$assets_list['et_icons_fa'] = [
				'css' => "{$assets_prefix}/css/icons_fa_all.css",
			];
		}

		return $assets_list;
	}

	public function maybe_inherit_values() {
		if ( '3.0.0' <= DICA_VERSION && ! empty( $this->props['overlay_color'] ) && empty( $this->props['overlay_color_field_bgcolor'] ) ) {
			$this->props['overlay_color_field_bgcolor'] = $this->props['overlay_color'];
			$this->props['overlay_color']               = "";
		}
	}

}

new DiviCarousel;
