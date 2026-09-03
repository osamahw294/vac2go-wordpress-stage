<?php
/**
 * DiviCarousel Module class.
 *
 * @package DICA\Modules\DiviCarousel;
 */

namespace DICA\Server\Modules\DiviCarousel;

if (!defined('ABSPATH')) {
	die('Direct access forbidden.');
}

use DICA\Server\Traits\GetPropsTrait;
use DICA\Server\Traits\UnifiedStyleRenderer;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;


/**
 * Class DiviCarousel
 *
 * @package DICA\Modules\DiviCarousel
 */
class DiviCarousel implements DependencyInterface
{

	use GetPropsTrait;
	use UnifiedStyleRenderer;

	protected $styles;
	private function customGetInnerContentResponsiveValueForSubAttr($attr, $parentField, $field, $fallback = null, $device = '') {
		$breakpoints = [
			'ultraWide',
			'widescreen',
			'desktop',
			'tabletWide',
			'tablet',
			'phoneWide',
			'phone'
		];

		$value     = null;
		$fromIndex = 0;

		if ($device) {
			if($device === 'desktop') {
				return $fallback;
			}
			$idx = array_search($device, $breakpoints);
			if ($idx !== false) {
				$fromIndex = $idx;
			}
		}

		for ($i = $fromIndex; $i < count($breakpoints); $i++) {
			$bp = $breakpoints[$i];
			if (isset($attr[$parentField]['innerContent'][$bp]['value'][$field])) {
				$value = $attr[$parentField]['innerContent'][$bp]['value'][$field];
				break;
			}
		}

		if ($value === null && $fromIndex > 0) {
			for ($i = $fromIndex - 1; $i >= 0; $i--) {
				$bp = $breakpoints[$i];
				if (isset($attr[$parentField]['innerContent'][$bp]['value'][$field])) {
					$value = $attr[$parentField]['innerContent'][$bp]['value'][$field];
					break;
				}
			}
		}

		return $value ?? $fallback;
	}
	protected static function get_font_style( $icon ) {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => [
					'font-family' => true,
				],
			]
		);

		// Normalize legacy string (e.g. "||fa||\uf00c") so font-family is always set.
		if ( is_string( $icon ) ) {
			$font_family = ( strpos( $icon, '||fa||' ) !== false ) ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add( 'font-family', $font_family );
			return $style_declarations->value();
		}

		if ( ! is_array( $icon ) ) {
			return $style_declarations->value();
		}

		if ( isset( $icon['type'] ) ) {
			$font_family = 'fa' === $icon['type'] ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add( 'font-family', $font_family );
		}

		if ( ! empty( $icon['weight'] ) ) {
			$style_declarations->add( 'font-weight', "{$icon['weight']}!important" );
		}

		if ( ! empty( $icon['unicode'] ) ) {
			$font_icon = Utils::escape_font_icon( Utils::process_font_icon( $icon ) );
			if ( $font_icon ) {
				$style_declarations->add( 'content', "'" . $font_icon . "'" );
			}
		}

		return $style_declarations->value();
	}
	public static function custom_css()
	{
		$registered = \WP_Block_Type_Registry::get_instance()->get_registered('dica/divi-carousel');
		return $registered ? $registered->customCssFields : [];
	}

	public static function module_classnames($args)
	{
		$classnames_instance = $args['classnamesInstance'];
		$attrs = $args['attrs'];

		// Text Options.
		$classnames_instance->add(
			TextClassnames::text_options_classnames(
				$attrs['module']['advanced']['text'] ?? [],
				[
					'orientation' => false,
				]
			),
			true
		);

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => $attrs['module']['decoration'] ?? [],
					'attrs' => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'link' => $attrs['module']['advanced']['link'] ?? [],
						]
					),
				]
			)
		);
	}

	public static function module_script_data($args)
	{
		$elements = $args['elements'];

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);
	}

	protected function getDefaultKeys($args)
	{
		$attrs = $args['attrs'];

		$metaData = $args['elements']->module_metadata->attributes;
		foreach ($metaData as $key => $value) {
			if ( isset( $attrs[ $key ] ) && ! is_array( $attrs[ $key ] ) ) {
				continue;
			}
			if ( ! isset( $attrs[ $key ] ) ) {
				$attrs[ $key ] = array();
			}
			if (empty($attrs[$key]['innerContent'])) {
				$attrs[$key]['innerContent'] = [
					'desktop' => [
						'value' => []
					]
				];
			}
			if(empty($attrs[$key]['decoration'])) {
				$attrs[$key]['decoration'] = [
					'desktop' => [
						'value' => []
					]
				];
			}
		}
		return $attrs;
	}


	public static function module_styles($args)
	{
		$instance = new static();
		$attrs = $instance->getDefaultKeys($args);
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];
		$orderClass = $args['orderClass'];

		$instance->initializeProps($elements, $attrs);
		$instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $orderClass, 'dica/divi-carousel');
		$styles = [];

		// overlay icon
		if ($instance->props['use_overlay_icon'] === "on") {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:after",
				"attr" => $instance->props['overlay_obj_settings']['innerContent'],
				"declarationFunction" => function ($props) use ($instance) {
					$overlay_icon = empty($props['attrValue']['overlay_icon']) ? $instance->props['overlay_icon'] : $props['attrValue']['overlay_icon'];
					// if (is_string($overlay_icon)) {
					// 	$overlay_icon = json_decode($overlay_icon, true) ?: [];
					// }
					$overlay_icon = is_array($overlay_icon) ? $overlay_icon : [];
					$decoded = Utils::process_font_icon($overlay_icon);
					if ($decoded === null) {
						return '';
					}
					$content = '"' . Utils::escape_font_icon($decoded) . '"';
					if (isset($overlay_icon['type']) && $overlay_icon['type'] !== 'divi') {
						return 'content:' . $content . ' !important;font-family:"FontAwesome" !important';
					}
					return 'content:' . $content . ' !important;font-family:"ETmodules" !important';
				}
			]);
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel .overlay-image .dica-item .dica-image-container .image:after",
				"attr" => $instance->props['overlay_obj_settings']['innerContent'],
				"declarationFunction" => function ($props) use ($instance) {
					$overlay_icon = empty($props['attrValue']['overlay_icon']) ? $instance->props['overlay_icon'] : $props['attrValue']['overlay_icon'];
					return self::get_font_style($overlay_icon);
				}
			]);
		}

		if ($instance->props['use_prev_icon'] === "on") {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass} .swiper-button-prev:before",
				"attr" => $instance->props['prev_obj_settings']['innerContent'],
				"declarationFunction" => function ($props) use ($instance) {
					$prev_icon = empty($props['attrValue']['prev_icon']) ? $instance->props['prev_icon'] : $props['attrValue']['prev_icon'];
					return self::get_font_style($prev_icon);
				}
			]);
		}

		if ($instance->props['use_next_icon'] === "on") {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass} .swiper-button-next:before",
				"attr" => $instance->props['next_obj_settings']['innerContent'],
				"declarationFunction" => function ($props) use ($instance) {
					$next_icon = empty($props['attrValue']['next_icon']) ? $instance->props['next_icon'] : $props['attrValue']['next_icon'];
					return self::get_font_style($next_icon);
				}
			]);
		}

		if (!empty($instance->props['arrow_font_size'])) {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass} .dica-container .swiper-button-next, {$orderClass} .dica-container .swiper-button-prev",
				"attr" => $instance->props['arrow_obj_settings']['innerContent'],
				"declarationFunction" => function ($props) use ($instance) {
					$arrow_font_size = empty($props['attrValue']['arrow_font_size']) ? $instance->props['arrow_font_size'] : $props['attrValue']['arrow_font_size'];
					return sprintf('width:%1$s !important;height:%1$s !important;', $arrow_font_size);
				}
			]);
		}

		if ($instance->props['image_force_fullwidth'] !== "on") {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass} .dica_divi_carouselitem .dica-image-container .image",
				"attr" => $instance->props['image_obj_settings']['innerContent'],
				"declarationFunction" => function ($props) use ($instance) {
					$image_width = ! empty($instance->props['image_sizing']) ? $instance->props['image_sizing'] : '100%';
					return sprintf('max-width:%1$s !important', $image_width);
				}
			]);
		}

		// force image fullwidth
		if ($instance->props['image_force_fullwidth'] === "on") {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass} .dica_divi_carouselitem .dica-image-container img",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => fn($props) => "width:100% !important"
			]);
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass} .dica_divi_carouselitem .dica-image-container .image",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => fn($props) => "width:100% !important"
			]);
		}

		if ($instance->props['field_enable_container_shadow_effect'] === "on") {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel:has(.dica-container.has_container_shadow) .swiper-container::before",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => function ($props) use ($instance) {
					$primary_color = ! empty($instance->props['field_container_shadow_primary_color']) ? $instance->props['field_container_shadow_primary_color'] : 'rgba(255,255,255,0.85)';
					$secondary_color = ! empty($instance->props['field_container_shadow_secondary_color']) ? $instance->props['field_container_shadow_secondary_color'] : 'rgba(255,255,255,0.0)';
					$shadow_width = ! empty($instance->props['field_container_shadow_width']) ? $instance->props['field_container_shadow_width'] : '70%';

					return sprintf(
						'right:%1$s;background-image:linear-gradient(to right, %2$s, %3$s);',
						$shadow_width,
						$primary_color,
						$secondary_color
					);
				}
			]);
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel:has(.dica-container.has_container_shadow) .swiper-container::after",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => function ($props) use ($instance) {
					$primary_color = ! empty($instance->props['field_container_shadow_primary_color']) ? $instance->props['field_container_shadow_primary_color'] : 'rgba(255,255,255,0.85)';
					$secondary_color = ! empty($instance->props['field_container_shadow_secondary_color']) ? $instance->props['field_container_shadow_secondary_color'] : 'rgba(255,255,255,0.0)';
					$shadow_width = ! empty($instance->props['field_container_shadow_width']) ? $instance->props['field_container_shadow_width'] : '70%';

					return sprintf(
						'left:%1$s;background-image:linear-gradient(to left, %2$s, %3$s);',
						$shadow_width,
						$primary_color,
						$secondary_color
					);
				}
			]);
		}

		// large active bullet
		if ($instance->props['dot_nav'] === "on" && $instance->props['field_large_active_bullet'] === "on") {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel .swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => fn($props) => "width: 40px; border-radius: 20px !important;"
			]);
		}

		if ($instance->props['coverflow_shadow'] === "off") {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel .dica-container .swiper-slide-shadow-left,{$orderClass}.dica_divi_carousel .dica-container .swiper-slide-shadow-right",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => fn($props) => "opacity:0 !important;"
			]);
		}

		if ($instance->props['field_enable_inactive_slide_design'] === "on" && $instance->props['multislide'] !== "on") {
			if ($instance->props['field_disable_hover_click'] === "on") {
				$styles[] = CommonStyle::style([
					"selector" => "{$orderClass}.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem:not(.swiper-slide-active)",
					"attr" => $instance->props['hover_click_obj_settings']['innerContent'],
					"declarationFunction" => fn($props) => "pointer-events:none;"
				]);
			}

			if ($instance->props['field_inactive_item_scaling'] === "on" && $instance->props['advanced_effect'] !== "coverflow") {
				$transition_time = !empty($instance->props['transition_duration']) ? (int) $instance->props['transition_duration'] : 1000;
				if ($instance->props['scroller_effect'] === "on") {
					$transition_time = !empty($instance->props['transition_duration_scroll']) ? (int) $instance->props['transition_duration_scroll'] : 4000;
					$transition_time -= 1000;
				}

				$styles[] = CommonStyle::style([
					"selector" => "{$orderClass}.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem.swiper-slide-active,{$orderClass}.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem:not(.swiper-slide-active)",
					"attr" => $instance->props['inactive_slide_obj_settings']['innerContent'],
					"declarationFunction" => fn($props) => sprintf('transition: transform %1$sms ease-out;', $transition_time)
				]);

				$styles[] = CommonStyle::style([
					"selector" => "{$orderClass}.dica_divi_carousel .dica-container .swiper-container .dica_divi_carouselitem:not(.swiper-slide-active)",
					"attr" => $instance->props['inactive_slide_obj_settings']['innerContent'],
					"declarationFunction" => function ($props) use ($instance) {
						$scale = !empty($instance->props['field_inactive_item_scaling_range']) ? $instance->props['field_inactive_item_scaling_range'] : '1';
						return sprintf('transform: scale(%1$s);', $scale);
					}
				]);
			}
		}

		if ($instance->props['scroller_effect'] === "on") {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel .swiper-wrapper",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => fn($props) => "-webkit-transition-timing-function:linear!important;
				-o-transition-timing-function:linear!important;
				transition-timing-function: linear !important;
				transition-duration:2000ms;"
			]);
		}
		// Equal Height (match module-styles.jsx logic)
		if (
			$instance->props['equal_height'] === "on"
			&& $instance->props['multiple_slide_row'] !== "on"
		) {
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel .dica-container .swiper-wrapper .dica_divi_carouselitem",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => fn($props) => "height:100%;"
			]);
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel .dica-container .swiper-container",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => fn($props) => "display:flex;"
			]);
			$styles[] = CommonStyle::style([
				"selector" => "{$orderClass}.dica_divi_carousel .dica_divi_carouselitem .dica-item-content",
				"attr" => $instance->props['settings_obj_settings']['innerContent'],
				"declarationFunction" => fn($props) => "flex-grow: 1;"
			]);
		}


		Style::add(
			[
				'id' => $args['id'],
				'name' => $args['name'],
				'orderIndex' => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles' => [
					// Element: Module.
					$elements->style(
						[
							'attrName' => 'module',
							'styleProps' => [
								'disabledOn' => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
							],
						]
					),
					CssStyle::style(
						[
							'selector' => $args['orderClass'],
							'attr' => $attrs['css'] ?? [],
							'cssFields' => self::custom_css(),
						]
					),

					...$instance->styles,
					...$styles,
				],
			]
		);
	}

	public function return_data_value($value)
	{
		return (!empty($value)) ? $value : '';
	}

	public function before_render()
	{
		global $dg_carousel_helper_data;

		$use_overlay_animated_icon = $this->props['use_overlay_animated_icon'];
		$animated_overlay_icon_style = $this->props['animated_overlay_icon_style'];

		$dg_carousel_helper_data = [
			'use_overlay_animated_icon' => $use_overlay_animated_icon,
			'animated_overlay_icon_style' => $animated_overlay_icon_style
		];

	}

	public function render($attrs, $content)
	{
		$classes = '';
		$item_spacing = $this->props['item_spacing'];
		$item_spacing_tablet = $this->customGetInnerContentResponsiveValueForSubAttr($attrs,'settings_obj_settings','item_spacing',$this->props['item_spacing'],'tablet');
		$item_spacing_phone = $this->customGetInnerContentResponsiveValueForSubAttr($attrs,'settings_obj_settings','item_spacing',$this->props['item_spacing'],'phone');
		$arrow_show_hover_class = 'on' === $this->props['arrow_show_hover'] ? ' arrow-show-hover' : '';

		$multiple_slide_row = ("on" !== $this->props['equal_height'] && 'coverflow' !== $this->props['advanced_effect']) ? $this->props['multiple_slide_row'] : 'off';

		$slide_row = $this->props['slide_row'];
		$slide_row_tablet = $this->customGetInnerContentResponsiveValueForSubAttr($attrs,'settings_obj_settings','slide_row',$slide_row,'tablet');
		$slide_row_phone = $this->customGetInnerContentResponsiveValueForSubAttr($attrs,'settings_obj_settings','slide_row',$slide_row,'phone');
		$keyboard_control = $this->props['keyboard_control'];
		$mousewheel_control = $this->props['mousewheel_control'];
		$autoplay_viewport = $this->props['autoplay_viewport'];

		$order_number = wp_rand(0, 9999);

		$coverflow = sprintf(
			'cover-rotate="%1$s" ',
			$this->return_data_value($this->props['coverflow_rotate'])
		);

		$data_attr = [
			'desktop' => $this->return_data_value($this->props['show_items_desktop']),
			'tablet' => $this->return_data_value($this->props['show_items_tablet']),
			'mobile' => $this->return_data_value($this->props['show_items_mobile']),
			'speed' => $this->return_data_value($this->props['transition_duration']),
			'arrow' => $this->return_data_value($this->props['arrow_nav']),
			'dots' => $this->return_data_value($this->props['dot_nav']),
			'autoplay' => $this->return_data_value($this->props['autoplay']),
			'autoSpeed' => $this->return_data_value($this->props['autoplay_speed']),
			'loop' => 'off' !== $multiple_slide_row ? '' : $this->return_data_value($this->props['loop']),
			'item_spacing' => $this->props['item_spacing'],
			'center_mode' => 'off' !== $multiple_slide_row ? '' : $this->return_data_value($this->props['centermode']),
			'slider_effec' => $this->return_data_value($this->props['advanced_effect']),
			'cover_rotate' => $this->return_data_value($this->props['coverflow_rotate']),
			'pause_onhover' => $this->return_data_value($this->props['hoverpause']),
			'multislide' => 'off' !== $multiple_slide_row ? '' : $this->return_data_value($this->props['multislide']),
			'cfshadow' => $this->props['coverflow_shadow'],
			'order' => $order_number,
			'lazyload' => $this->props['lazy_loading'],
			'lazybefore' => $this->props['load_before_transition'],
			'scroller_effect' => $this->props['scroller_effect'],
			'autowidth' => $this->props['item_width_auto'],
			'item_spacing_tablet' => $item_spacing_tablet,
			'item_spacing_phone' => $item_spacing_phone,
			'scroller_speed' => $this->props['transition_duration_scroll'],
			'hashNavigation' => $this->props['dg_hash_nav'],
			'simulatetouch' => $this->props['simulatetouch'],
			'allowtouchmove' => 'on' === $this->props['simulatetouch'] ? $this->props['allowtouchmove'] : 'off',
			'slide_row' => 'off' !== $multiple_slide_row ? $slide_row : '1',
			'slide_row_tablet' => 'off' !== $multiple_slide_row ? esc_attr($slide_row_tablet) : '1',
			'slide_row_phone' => 'off' !== $multiple_slide_row ? esc_attr($slide_row_phone) : '1',
			'keyboard' => $this->return_data_value($keyboard_control),
			'mousewheel' => $this->return_data_value($mousewheel_control),
			'autoplay_viewport' => 'off' !== $this->props['autoplay'] && '' !== $autoplay_viewport ? esc_attr($autoplay_viewport) : '',
		];

		$pagination = ('on' === $this->props['dot_nav']) ?
			sprintf('<div class="swiper-pagination dica-paination-%1$s"></div>', $order_number) : '';

		// add overlay classes
		if ('on' === $this->props['overlay_image']) {
			if ("off" === $this->props['overlay_on_hover']) {
				$classes = $this->add_class($classes, 'overlay_image_default');
			}
			$classes = $this->add_class($classes, 'overlay-image');
		}
		// add arrow class
		$classes = $this->add_class($classes, $this->carousel_arrow_classes($attrs));
		// arrow show on hover
		if ('on' === $this->props['arrow_show_hover']) {
			$classes = $this->add_class($classes, 'arrow-on-hover');
		}
		$container_shadow_class = 'on' === $this->props['field_enable_container_shadow_effect'] ? ' has_container_shadow' : '';

		add_filter('et_global_assets_list', [$this, 'dgcm_load_required_divi_assets'], 10, 3);
		add_filter('et_late_global_assets_list', [$this, 'dgcm_load_required_divi_assets'], 10, 3);

		$rtl = 'off';
		if ('on' === $this->props['field_enable_rtl'] && 'on' !== $this->props['multiple_slide_row'] && 'on' !== $this->props['lazy_loading']) {
			$rtl = 'on';
		}

		$output = sprintf(
			'<div class="dica-container %5$s%7$s" data-props=\'%2$s\'>
							 	<div class="swiper-container" %6$s>
									<div class="swiper-wrapper">%1$s</div>
								</div>
								%3$s
								%4$s
							</div>',
			et_core_sanitized_previously($content),
			wp_json_encode($data_attr),
			$this->carousel_arraow_markup($order_number),
			$pagination,
			$classes,
			('on' === $rtl) ? esc_attr('dir=rtl') : '',
			$container_shadow_class
		);

		return $output;
	}

	/**
	 * Add module Classes
	 */
	public function add_class($classes, $class = '')
	{
		return $classes .= ' ' . $class;
	}

	/**
	 * Return the arraow markup depending on settings
	 */
	public function carousel_arraow_markup($order_number)
	{
		$arrow_navigation = '';
		$data_prev_icon = 'on' === $this->props['use_prev_icon'] ?
			sprintf('data-icon="%1$s"', esc_attr(Utils::process_font_icon($this->props['prev_icon']))) : 'data-icon="4"';
		$data_next_icon = 'on' === $this->props['use_next_icon'] ?
			sprintf('data-icon="%1$s"', esc_attr(Utils::process_font_icon($this->props['next_icon']))) : 'data-icon="5"';

		if ('on' === $this->props['arrow_nav']) {
			$arrow_navigation = sprintf(
				'<div class="swiper-button-prev dica-prev-btn-%1$s" %2$s></div><div class="swiper-button-next dica-next-btn-%1$s" %3$s></div>',
				$order_number,
				$data_prev_icon,
				$data_next_icon
			);

			$arrow_navigation = sprintf('<div class="swiper-buttton-container">%1$s</div>', $arrow_navigation);
		}

		return $arrow_navigation;
	}

	/**
	 * Generate arrow position classes
	 */
	public function carousel_arrow_classes($attrs)
	{
		$arrow_position_desktop = $attrs['arrow_obj_settings']['innerContent']['desktop']['value']['arrow_position'] ?? $this->props['arrow_position'];
		$arrow_position_tablet = $attrs['arrow_obj_settings']['innerContent']['tablet']['value']['arrow_position'] ?? $this->props['arrow_position'];
		$arrow_position_mobile = $attrs['arrow_obj_settings']['innerContent']['phone']['value']['arrow_position'] ?? $arrow_position_tablet;

		$arrow_position_desktop = 'desktop_' . $arrow_position_desktop;
		$arrow_position_tablet = 'tablet_' . $arrow_position_tablet;
		$arrow_position_mobile = 'mobile_' . $arrow_position_mobile;

		return $arrow_position_desktop . ' ' . $arrow_position_tablet . ' ' . $arrow_position_mobile;
	}

	//	public function maybe_inherit_values() {
	//		if ( '3.0.0' <= DICA_VERSION && ! empty( $this->props['overlay_color'] ) && empty( $this->props['overlay_color_field_bgcolor'] ) ) {
	//			$this->props['overlay_color_field_bgcolor'] = $this->props['overlay_color'];
	//			$this->props['overlay_color']               = "";
	//		}
	//	}


	public static function render_callback($attrs, $content, $block, $elements)
	{
		wp_enqueue_script( 'dica-frontend-script' );
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function ($inner_block) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];
		$instance = new static();
		$instance->initializeProps($elements, $attrs);

		return Module::render(
			[
				// FE only.
				'orderIndex' => $block->parsed_block['orderIndex'],
				'storeInstance' => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs' => $attrs,
				'elements' => $elements,
				'id' => $block->parsed_block['id'],
				'moduleClassName' => '',
				'name' => $block->block_type->name,
				'classnamesFunction' => [DiviCarousel::class, 'module_classnames'],
				'moduleCategory' => $block->block_type->category,
				'stylesComponent' => [DiviCarousel::class, 'module_styles'],
				'scriptDataComponent' => [DiviCarousel::class, 'module_script_data'],
				'parentAttrs' => $parent->attrs ?? [],
				'parentId' => $parent->id ?? '',
				'parentName' => $parent->blockName ?? '',
				'children' => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $instance->render($attrs, $content),
			]
		);
	}

	public function load()
	{
		$module_json_folder_path = DICA_MODULES_JSON_PATH . '/divi-carousel';

		add_action(
			'init',
			function () use ($module_json_folder_path) {
				ModuleRegistration::register_module(
					$module_json_folder_path,
					[
						'render_callback' => [DiviCarousel::class, 'render_callback'],
					]
				);
			}
		);
	}
}
