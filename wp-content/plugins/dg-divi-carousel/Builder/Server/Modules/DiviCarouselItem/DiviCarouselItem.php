<?php
/**
 * DiviCarouselItem Module class.
 *
 * @package DICA\Modules\DiviCarouselItem;
 */

namespace DICA\Server\Modules\DiviCarouselItem;

if (!defined('ABSPATH')) {
	die('Direct access forbidden.');
}

use DICA\Server\Traits\GetPropsTrait;
use DICA\Server\Traits\UnifiedStyleRenderer;
use DICA\Server\Traits\ElementsRenderTrait;
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
 * Class DiviCarouselItem
 *
 * @package DICA\Modules\DiviCarouselItem
 */
class DiviCarouselItem implements DependencyInterface
{

	use GetPropsTrait;
	use UnifiedStyleRenderer;
	use ElementsRenderTrait;
	protected static function get_font_style($icon)
	{
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important' => [
					'font-family' => true,
				],
			]
		);

		// Normalize legacy string (e.g. "||fa||\uf00c") so font-family is always set.
		if (is_string($icon)) {
			$font_family = (strpos($icon, '||fa||') !== false) ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add('font-family', $font_family);
			return $style_declarations->value();
		}

		if (!is_array($icon)) {
			return $style_declarations->value();
		}

		if (isset($icon['type'])) {
			$font_family = 'fa' === $icon['type'] ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add('font-family', $font_family);
		}

		if (!empty($icon['weight'])) {
			$style_declarations->add('font-weight', "{$icon['weight']}!important");
		}

		if (!empty($icon['unicode'])) {
			$font_icon = Utils::escape_font_icon(Utils::process_font_icon($icon));
			if ($font_icon) {
				$style_declarations->add('content', "'" . $font_icon . "'");
			}
		}

		return $style_declarations->value();
	}

	protected $styles;

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered('dica/divi-carouselitem')->customCssFields;
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

		// Theme Builder support: tag the child item's root wrapper with its
		// layout area (header/body/footer) so its styles render correctly when
		// the carousel is used inside a Theme Builder template. Mirrors the
		// DiviFlash advanced-menu-item approach.
		$layout_type = BlockParserStore::get_layout_type();
		if ( 'et_header_layout' === $layout_type ) {
			$classnames_instance->add( 'dica_tb_header' );
		} elseif ( 'et_footer_layout' === $layout_type ) {
			$classnames_instance->add( 'dica_tb_footer' );
		} elseif ( 'et_body_layout' === $layout_type ) {
			$classnames_instance->add( 'dica_tb_body' );
		}
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
		;
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];
		$order_class = $args['orderClass'];

		$instance->initializeProps($elements, $attrs);
		$instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'dica/divi-carouselitem');
		$styles = [];
		if ($instance->props['use_icon'] === 'on') {
			$styles[] = CommonStyle::style(
				[
					'selector' => "{$order_class} .et-pb-icon",
					'attr' => $instance->props['icon_obj_settings']['innerContent'],
					'declarationFunction' => function ($props) use ($instance) {
						$font_icon = empty($props['attrValue']['font_icon']) ? $instance->props['font_icon'] : $props['attrValue']['font_icon'];
						return self::get_font_style($font_icon);
					},
				]
			);
		}
		if (in_array($instance->props['image_position'], ['image_left', 'image_right'], true)) {
			$styles[] = CommonStyle::style(
				[
					'selector' => "{$order_class}.dica_divi_carouselitem .dica-image-container",
					'attr' => $instance->props['image_obj_settings']['innerContent'],
					'declarationFunction' => function ($props) use ($instance) {
						$image_size = empty($props['attrValue']['image_size']) ? $instance->props['image_size'] : $props['attrValue']['image_size'];
						$image_size = !empty($image_size) ? $image_size : '50%';
						return sprintf('width:%1$s !important;', $image_size);
					},
				]
			);
			$styles[] = CommonStyle::style(
				[
					'selector' => "{$order_class}.dica_divi_carouselitem .dica-item-content",
					'attr' => $instance->props['image_obj_settings']['innerContent'],
					'declarationFunction' => function ($props) use ($instance) {
						$image_size = empty($props['attrValue']['image_size']) ? $instance->props['image_size'] : $props['attrValue']['image_size'];
						$image_size = !empty($image_size) ? $image_size : '50%';
						$numeric_size = floatval($image_size);
						$content_size = is_nan($numeric_size) ? 50 : 100 - $numeric_size;
						return sprintf('width:%1$s%% !important;', $content_size);
					},
				]
			);
			
		}
		if ( $instance->props['social_media_alignment'] === 'space-between' ) {
			$styles[] = CommonStyle::style(
				[
					'selector' => "{$order_class}.dica_divi_carouselitem .social-media li",
					'attr' => $instance->props['social_obj_settings']['innerContent'],
					'declarationFunction' => function ($props) use ($instance) {
						return "flex:1;";
					},
				]
			);
		}
		if ( isset( $attrs['button_obj_settings']['decoration']['button']['decoration'] ) ) {
			$styles[] = $elements->style(
				[
					'attrName'   => 'button_obj_settings',
					'styleProps' => [
						'type'  => 'button',
						'attrs' => $attrs['button_obj_settings']['decoration']['button']['decoration'],
					],
				]
			);
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

					//  CommonStyle::style(
					//      [
					//          'selector'            => "{$order_class} .df_text_reveal_main_container",
					//          'attr'                => $attrs['settings__reveal_color']['decoration'] ?? [],
					//          'property' => '--secondary-reveal-color'
					//      ]
					//  ),
				],
			]
		);
	}

	public function generate_embed_url_for_video_lightbox($url)
	{
		$videoURL = $url;
		if (et_pb_validate_youtube_url($url)) {
			$queryString = wp_parse_url($url, PHP_URL_QUERY);
			if ($queryString) {
				parse_str($queryString, $queryParams);
				$videoId = isset($queryParams['v']) ? basename($queryParams['v']) : '';
			} else {
				$path = wp_parse_url($url, PHP_URL_PATH);
				$videoId = basename($path);
			}
			$videoURL = "https://www.youtube.com/embed/" . $videoId ?? null;
		}

		return $videoURL;
	}

	public function render_button( $attrs = [] )
	{
		$text              = ! empty( $this->props['button_text'] ) ? $this->props['button_text'] : 'Click Here';
		$url               = isset( $this->props['button_url'] ) ? $this->props['button_url'] : '';
		$target            = 'on' === ( $this->props['button_url_new_window'] ?? '' ) ? ' target="_blank"' : '';
		$button_rel        = $attrs['buttonAttributes']['innerContent']['desktop']['value']['button_rel'] ?? ( $this->props['button_rel'] ?? '' );
		$button_icon_attr  = $attrs['button_obj_settings']['decoration']['button']['desktop']['value']['icon'] ?? [];
		$button_icon       = (
			is_array( $button_icon_attr ) &&
			'on' === ( $button_icon_attr['enable'] ?? 'off' ) &&
			! empty( $button_icon_attr['settings'] )
		) ? $button_icon_attr['settings'] : '';

		if ( is_array( $button_rel ) ) {
			$button_rel = implode( ' ', $button_rel );
		}

		$rel_attr       = '' !== $button_rel ? sprintf( ' rel="%s"', esc_attr( $button_rel ) ) : '';
		$icon_value     = ! empty( $button_icon ) ? Utils::process_font_icon( $button_icon ) : '';
		$data_icon_attr = '' !== $icon_value ? sprintf( ' data-icon="%s"', esc_attr( $icon_value ) ) : '';
		$button_class   = 'et_pb_button df_ic_button';

		if ( ! empty( $button_icon ) ) {
			$button_class .= ' et_pb_custom_button_icon';
		}

		if ( empty( $url ) ) {
			return '';
		}

		return sprintf(
			'<div class="et_pb_button_wrapper df_ic_button_wrapper"><a href="%1$s" class="%2$s"%3$s%4$s%5$s>%6$s</a></div>',
			esc_url( $url ),
			esc_attr( $button_class ),
			$rel_attr,
			$target,
			$data_icon_attr,
			esc_html( $text )
		);
	}
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
	public function render($attrs, $id, $elements, $parent_attrs, $order_class = '', $elements_content = '')
	{
		global $dg_carousel_helper_data;
		$_ex = "DICA_Extends";
		$classes = '';
		$title = $this->props['title'];
		$url = $this->props['url'];
		$url_new_window = $this->props['url_new_window'];
		$content = $this->props['content'];
		$image = $this->props['image'];
		$image_url = $this->props['image'];
		$social_media = '';
		$rating = '';
		$image_lightbox = $this->props['image_lightbox'];
		$video_lightbox = $this->props['video_lightbox'];
		$video_lightbox_url = $this->props['video_lightbox_url'];
		$video_lightbox_url_new_window = $this->props['video_lightbox_url_new_window'];

		$font_icon = $this->props['font_icon'];
		$use_icon = $this->props['use_icon'];
		$use_circle = $this->props['use_circle'];
		$use_circle_border = $this->props['use_circle_border'];
		$icon_color = $this->props['icon_color'];
		$circle_color = $this->props['circle_color'];
		$circle_border_color = $this->props['circle_border_color'];
		$use_icon_font_size = $this->props['use_icon_font_size'];
		$icon_font_size = $this->props['icon_font_size'];
		$icon_font_size_tablet = $this->props['icon_font_size_tablet'];
		$icon_font_size_phone = $this->props['icon_font_size_phone'];
		$icon_font_size_last_edited = $this->props['icon_font_size_last_edited'];

		$is_content_limit = 'on' === $this->props['field_enable_content_limit'];
		$readmore_text = !empty($this->props['field_read_more']) ? $this->props['field_read_more'] : __(' Read more', 'dica-divi-carousel');
		$showless_text = !empty($this->props['field_show_less']) ? $this->props['field_show_less'] : __(' Show less', 'dica-divi-carousel');
		$content_limit_fallback = $this->props['field_limit_content_length'] ?? 50;
		$content_limit = $this->customGetInnerContentResponsiveValueForSubAttr($attrs, 'content_obj_settings', 'field_limit_content_length', $content_limit_fallback, 'desktop');
		$content_limit_phone = $this->customGetInnerContentResponsiveValueForSubAttr($attrs, 'content_obj_settings', 'field_limit_content_length', $content_limit_fallback, 'phone');
		$content_limit_tablet = $this->customGetInnerContentResponsiveValueForSubAttr($attrs, 'content_obj_settings', 'field_limit_content_length', $content_limit_fallback, 'tablet');


		if ('off' !== $use_icon_font_size) {
			$font_size_responsive_active = et_pb_get_responsive_status($icon_font_size_last_edited);

			$font_size_values = [
				'desktop' => $icon_font_size,
				'tablet' => $font_size_responsive_active ? $icon_font_size_tablet : '',
				'phone' => $font_size_responsive_active ? $icon_font_size_phone : '',
			];
		}

		// Render button
		$button = $this->render_button($attrs);
		// get the parent module
//		$parent_module = self::get_parent_modules( 'page' )['dica_divi_carousel'];
		$lazy_loading = $parent_attrs['settings_obj_settings']['innerContent']['desktop']['value']['lazy_loading'] ?? 'off';
		if ('on' === $lazy_loading) {
			$src_attr = 'data-src';
			$srcset_attr = 'data-srcset';
			$lazy_class = ' swiper-lazy';
			$lazy_preloader = '<div class="swiper-lazy-preloader swiper-lazy-preloader-black"></div>';
			$loading = ' loading';
		} else {
			$src_attr = 'src';
			$srcset_attr = 'srcset';
			$lazy_class = '';
			$lazy_preloader = '';
			$loading = '';
		}

		$image = is_string($image) ? trim($image) : '';
		$srcset_sizes = $image !== '' ? et_get_image_srcset_sizes($image) : [];
		$srcset = "";
		$sizes = "";

		if (
			isset($srcset_sizes["srcset"], $srcset_sizes["sizes"]) &&
			$srcset_sizes["srcset"] &&
			$srcset_sizes["sizes"]
		) {
			$srcset = $srcset_sizes["srcset"];
			$sizes = $srcset_sizes["sizes"];
		}

		// create image markup
		if ('off' === $use_icon) {
			if ('' !== $image) {
				$animated_icon = "";
				$use_overlay_animated_icon = $parent_attrs['overlay_obj_settings']['innerContent']['desktop']['value']['use_overlay_animated_icon'] ?? 'off';
				$animated_overlay_icon_style = $parent_attrs['overlay_obj_settings']['innerContent']['desktop']['value']['animated_overlay_icon_style'] ?? '';
				if ("on" === $use_overlay_animated_icon) {
					$animated_icon = sprintf('<span class="animated_icon %1$s"><span></span></span>', $animated_overlay_icon_style);
				}
				$image_sizes = et_get_attachment_size_by_url($image_url);
				$image = sprintf(
					'<img %3$s="%1$s" %8$s="%9$s" alt="%2$s" class="dica-item-image%4$s" width="%6$s" height="%7$s"/>%5$s%10$s',
					esc_attr($image),
					esc_attr($this->props['image_alt']),
					$src_attr,
					$lazy_class,
					$lazy_preloader,
					$image_sizes[0],
					$image_sizes[1],
					$srcset_attr,
					$srcset,
					$animated_icon
				);
			} else {
				$image = '';
			}

		} else {
			$icon_style = sprintf('color: %1$s;', esc_attr($icon_color));
			if ('on' === $use_circle) {
				$icon_style .= sprintf(' background-color: %1$s;', esc_attr($circle_color));

				if ('on' === $use_circle_border) {
					$icon_style .= sprintf(' border-color: %1$s;', esc_attr($circle_border_color));
				}
			}
			$image = (!empty($font_icon) ) ? sprintf(
				'<span class="et-pb-icon %2$s%3$s" style="%4$s">%1$s</span>',
				Utils::process_font_icon($font_icon),
				('on' === $use_circle ? ' et-pb-icon-circle' : ''),
				('on' === $use_circle && 'on' === $use_circle_border ? ' et-pb-icon-circle-border' : ''),
				$icon_style
			) : '';
		}
		// create title markup
		if (!empty($title) && !empty($url)) {
			$title = sprintf(
				'<%4$s class="item-title"><a href="%1$s"%3$s>%2$s</a></%4$s>',
				esc_url($url),
				esc_html($title),
				('on' === $url_new_window ? ' target="_blank"' : ''),
				et_pb_process_header_level($attrs['title_obj_settings']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h4', 'h4')
			);
		} else if (!empty($title) && empty($url)) {
			$title = sprintf(
				'<%2$s class="item-title">%1$s</%2$s>',
				esc_html($title),
				et_pb_process_header_level($attrs['title_obj_settings']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h4', 'h4')
			);
		}
		// create image markup with lightbox
		if ('' !== $image && !empty($url) && 'off' === $use_icon && 'on' !== $image_lightbox && 'on' !== $video_lightbox) {
			$image = sprintf(
				'<a class="image" href="%1$s"%3$s>%2$s</a>',
				esc_url($url),
				$image,
				('on' === $url_new_window ? ' target="_blank"' : '')
			);
		} else if ('' !== $image && 'off' === $use_icon && 'on' !== $image_lightbox && 'on' !== $video_lightbox) {
			$image = sprintf(
				'<span class="image">%1$s</span>',
				$image
			);
		} else if ('' !== $image && 'off' === $use_icon && 'on' === $video_lightbox) {
			$popup_link_target = '';
			if ('on' === $video_lightbox_url_new_window) {
				$popup_link_target = 'target="_blank"';
			}
			// if the lightbox on
			$image = sprintf(
				'<a data-lightbox="on" data-lightbox_type="video" data-lightbox_target="%3$s" data-src="%2$s" class="image" href="%5$s" %4$s>%1$s</a>',
				$image,
				$this->generate_embed_url_for_video_lightbox($video_lightbox_url),
				$video_lightbox_url_new_window,
				$popup_link_target,
				('on' === $video_lightbox_url_new_window) ? $video_lightbox_url . "?autoplay=1" : "#"
			);

		} else if ('' !== $image && 'off' === $use_icon && 'on' === $image_lightbox) {
			$caption = '';
			if ('on' === $this->props['show_caption']) {
				$attachment_id = attachment_url_to_postid($image_url);
				// Get the attachment's alt, if its a valid ID
				if ($attachment_id) {
					$caption = wp_get_attachment_caption($attachment_id);
				}
			}
			// if the lightbox on
			$image = sprintf(
				'<a data-lightbox="on" data-lightbox_type="image" data-src="%2$s" data-caption="%3$s" class="image" href="#">%1$s</a>',
				$image,
				$image_url,
				esc_html__($caption)
			);

		} else if ('' !== $image && !empty($url) && 'on' === $use_icon) {
			$image = sprintf(
				'<a href="%1$s"%3$s>%2$s</a>',
				esc_url($url),
				$image,
				('on' === $url_new_window ? ' target="_blank"' : '')
			);
		}
		// social media content
		if ('on' === $this->props['use_social_media']) {
			$social_target = $this->props['social_target'] === 'on' ?
				'target="_blank"' : '';
			$facebook = '' !== $this->props['facebook_url'] && null !== $this->props['facebook_url'] ?
				sprintf('<li class="dg_facebook"><a %2$s href="%1$s"><span>Facebook</span></a></li>', $this->social_link_process($this->props['facebook_url']), $social_target) : '';
			$twitter = '' !== $this->props['twitter_url'] && null !== $this->props['twitter_url'] ?
				sprintf('<li class="dg_twitter"><a %2$s href="%1$s"><span>Twitter</span></a></li>', $this->social_link_process($this->props['twitter_url']), $social_target) : '';
			$linkedin = '' !== $this->props['linkedin_url'] && null !== $this->props['linkedin_url'] ?
				sprintf('<li class="dg_linkedin"><a %2$s href="%1$s"><span>Linkedin</span></a></li>', $this->social_link_process($this->props['linkedin_url']), $social_target) : '';
			$instagram = '' !== $this->props['instagram_url'] && null !== $this->props['instagram_url'] ?
				sprintf('<li class="dg_instagram"><a %2$s href="%1$s"><span>Instagram</span></a></li>', $this->social_link_process($this->props['instagram_url']), $social_target) : '';
			$email = '' !== $this->props['email_address'] && null !== $this->props['email_address'] ?
				sprintf('<li class="dg_email"><a %2$s href="mailto:%1$s"><span>Email</span></a></li>', $this->props['email_address'], $social_target) : '';
			$social_media = sprintf('<div class="social-media-container">
				<ul class="social-media">
					%1$s %2$s %3$s %4$s %5$s
				</ul>
			</div>', $facebook, $twitter, $linkedin, $instagram, $email);
		}
		// rating content
		if ('on' === $this->props['use_rating']) {
			$rating = sprintf('<div class="dica-rating-container">
				<div class="dica-rating">
					%1$s
				</div>
			</div>', $this->rating());
		}

		// image container
		$image_container = '' !== $image && 'image_under_title' !== $this->props['image_position'] ?
			sprintf('<div class="dica-image-container">%1$s</div>', $image) : '';
		// image position under title
		$image_under_title = '' !== $image && 'image_under_title' === $this->props['image_position'] ?
			sprintf('<div class="dica-image-container">%1$s</div>', $image) : '';

		// if the content is empty
		if (
			'' === $this->props['title'] &&
			'' === $this->props['content'] &&
			'' === $social_media &&
			'' === $rating &&
			'' === $this->props['sub_title']
		) {
			$classes = $this->add_class($classes, 'empty-content');
		}
		// adding image position class
		$classes = $this->add_class($classes, $this->props['image_position']);
		if ('on' === $this->props['tablet_on_top']) {
			$classes = $this->add_class($classes, 'tablet_on_top');
		}
		if ('on' === $this->props['mobile_on_top']) {
			$classes = $this->add_class($classes, 'mobile_on_top');
		}
		// social media position class
		if ('on' === $this->props['use_social_media']) {
			$classes = $this->add_class($classes, $this->props['social_media_position']);
		}
		// rating position class
		if ('on' === $this->props['use_rating']) {
			$classes = $this->add_class($classes, $this->props['rating_position']);
		}

		$content_settings = [
			'status' => $is_content_limit ? 'true' : 'false',
			'responsive' => 'on',
			'limit' => $is_content_limit ? esc_attr($content_limit) : '',
			'limit_tablet' => $is_content_limit ? esc_attr($content_limit_tablet) : '',
			'limit_phone' => $is_content_limit ? esc_attr($content_limit_phone) : '',
			'text_more' => $is_content_limit ? esc_attr($readmore_text) : '',
			'text_less' => $is_content_limit ? esc_attr($showless_text) : ''
		];
		$content = !empty($this->props['content']) ? sprintf(
			'<div
						class="content %2$s"
						data-settings=\'%3$s\'>
						%1$s
					</div>
					<noscript class="content_storage">%1$s</noscript>',
			$content,
			$is_content_limit ? 'dg_enable_content_limit' : '',
			!empty($this->props['content']) ? wp_json_encode($content_settings) : null
		) : '';
		
		
		$elements_html = self::wrapElementsHtml($elements_content);

		$content_container =
			!empty($title) || !empty($this->props['sub_title']) ||
			!empty($this->props['content']) ||
			!empty(et_core_sanitized_previously($button)) ||
			!empty($social_media) ||
			!empty($rating) ||
			!empty($elements_html) ?
			sprintf(
				'<div class="dica-item-content">%1$s %8$s %4$s %2$s %5$s %6$s %7$s %9$s %3$s %10$s</div>',
				$_ex::render_title($title, 'default', $this->props['title_position']),
				$content,
				et_core_sanitized_previously($button),
				$image_under_title,
				$social_media,
				$rating,
				$_ex::render_title($title, 'bottom', $this->props['title_position']),
				$_ex::render_subtitle($this->props['sub_title'], 'default', $this->props['subtitle_position'], $this->props['subtitle_tag']),
				$_ex::render_subtitle($this->props['sub_title'], 'bottom', $this->props['subtitle_position'], $this->props['subtitle_tag']),
				et_core_sanitized_previously($elements_html)
			)
			: null;

		$link_url = '' !== $this->props['link_option_url'] ?
			sprintf('data-link="%1$s"', $this->props['link_option_url']) :
			null;
		$link_target = 'on' !== $this->props['link_option_url_new_window'] ? null :
			'data-target="_blank"';

		$hash_id = isset($this->props['dg_hash_id']) && !empty($this->props['dg_hash_id']) ? $this->props['dg_hash_id'] : $id;
		$output = sprintf(
			' %9$s%10$s<div class="dica-item%3$s%4$s%5$s" %6$s %7$s data-hash="%8$s">
									%2$s
									%1$s
							</div>',
			$content_container,
			$image_container,
			$classes,
			$lazy_class,
			$loading,
			$link_url,
			$link_target,
			$hash_id,
			isset($this->props['background_enable_pattern_style']) ? $this->dica_render_pattern_or_mask_html($this->props['background_enable_pattern_style'], 'pattern') : '',
			isset($this->props['background_enable_mask_style']) ? $this->dica_render_pattern_or_mask_html($this->props['background_enable_mask_style'], 'mask') : ''
		);

		return $output;
	}

	/**
	 * Rating
	 */
	public function rating()
	{
		$rating = '';
		for ($i = 1; $i <= 5; $i++) {
			if ($i <= $this->props['rating_number']) {
				$rating .= '<span class="rate"></span>';
			} else {
				$rating .= '<span class="blank"></span>';
			}
		}

		return $rating;
	}

	/**
	 * Add module Classes
	 */
	private function add_class($classes, $class = '')
	{
		return $classes .= ' ' . $class;
	}

	/**
	 * Render title
	 */
	function render_title($title, $current, $position)
	{
		if ($current === $position) {
			return $title;
		} else {
			return null;
		}
	}

	/**
	 * Render subtitle
	 */
	function render_subtitle($subtitle, $current, $position)
	{
		if (!empty($subtitle)) {
			if ($current === $position) {
				return "<h6>{$subtitle}</h6>";
			} else {
				return null;
			}
		}
	}

	/**
	 * render pattern or mask markup
	 *
	 */
	public function dica_render_pattern_or_mask_html($props, $type)
	{
		$html = [
			'pattern' => '<span class="et_pb_background_pattern"></span>',
			'mask' => '<span class="et_pb_background_mask"></span>'
		];

		return 'on' === $props ? $html[$type] : '';
	}

	public function process_button($props, $render_slug)
	{
		$multi_view = et_pb_multi_view_options($this);


		$custom_icon_values = et_pb_responsive_options()->get_property_values($props, 'button_icon');
		$custom_icon = $this->props['button_icon'];
		$custom_icon_tablet = !empty($custom_icon_values['tablet']) ? $custom_icon_values['tablet'] : $custom_icon;
		$custom_icon_phone = !empty($custom_icon_values['phone']) ? $custom_icon_values['phone'] : $custom_icon_tablet;

		$button = $this->render_button();

		return $button;
	}


	public function social_link_process($link)
	{
		$content = '';
		if (!str_contains($link, 'http')) {
			$content = 'https://';
		}

		return $content . '' . $link;
	}

	public static function render_callback($attrs, $content, $block, $elements)
	{
		$parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
		$parent_attrs = $parent->attrs ?? [];
		$instance = new static();
		$instance->initializeProps($elements, $attrs);
		$child_class  = str_replace(
			array(
				'-',
				'/',
			),
			'_',
			$block->parsed_block['id']
		);
		if ( isset( $block->parsed_block['layout_type'] ) && $block->parsed_block['layout_type'] === 'et_header_layout' ) {
			$child_class .= '_tb_header';
		}
		if ( isset( $block->parsed_block['layout_type'] ) && $block->parsed_block['layout_type'] === 'et_footer_layout' ) {
			$child_class .= '_tb_footer';
		}
		if ( isset( $block->parsed_block['layout_type'] ) && $block->parsed_block['layout_type'] === 'et_body_layout' ) {
			$child_class .= '_tb_body';
		}
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
				'classnamesFunction' => [DiviCarouselItem::class, 'module_classnames'],
				'moduleCategory' => $block->block_type->category,
				'stylesComponent' => [DiviCarouselItem::class, 'module_styles'],
				'scriptDataComponent' => [DiviCarouselItem::class, 'module_script_data'],
				'parentAttrs' => $parent->attrs ?? [],
				'parentId' => $parent->id ?? '',
				'parentName' => $parent->blockName ?? '',
				'children' => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $instance->render(
					$attrs,
					str_replace(["-", "/"], "_", $block->parsed_block['id']),
					$elements,
					$parent_attrs,
					$child_class,
					$content
				),
			]
		);
	}

	public function load()
	{
		$module_json_folder_path = DICA_MODULES_JSON_PATH . '/divi-carousel-item';

		add_action(
			'init',
			function () use ($module_json_folder_path) {
				ModuleRegistration::register_module(
					$module_json_folder_path,
					[
						'render_callback' => [DiviCarouselItem::class, 'render_callback'],
					]
				);
			}
		);
	}
}
