<?php
/**
 * Global preset attrs map for all DICA modules.
 * Ensures every attribute (including "render": false in module.json) is included
 * when creating/saving presets, so "New Preset From Current Styles" stores all values.
 *
 * @package DICA\Server
 */

namespace DICA\Server;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\ModuleOptionsPresetAttrs;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block_Type_Registry;

/**
 * Class PresetAttrsMapGlobal
 *
 * For any module whose name starts with dica/, traverses the block's attribute
 * settings and adds preset map entries for every group-item and group (spacing,
 * background, border, etc.) so that render:false fields are also saved in presets.
 */
class PresetAttrsMapGlobal {

	const DICA_PREFIX = 'dica/';

	/**
	 * Filter: extend the preset attrs map for all dica modules.
	 *
	 * @param array  $map         Existing preset attributes map.
	 * @param string $module_name Module name (e.g. dica/advanced-person).
	 * @return array Merged map including all attributes (including render:false).
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( strpos( $module_name, self::DICA_PREFIX ) !== 0 ) {
			return $map;
		}

		$block = WP_Block_Type_Registry::get_instance()->get_registered( $module_name );
		if ( ! $block ) {
			$block = ModuleRegistration::get_module_settings( $module_name );
		}
		$block_attrs = $block->attributes ?? [];
		if ( empty( $block_attrs ) ) {
			return $map;
		}
		$block_attrs = is_array( $block_attrs ) ? $block_attrs : (array) $block_attrs;

		$extra = self::collect_preset_map_from_attributes( $block_attrs );
		return array_merge( $map, $extra );
	}

	/**
	 * Recursively traverse block attributes and collect preset map entries for every group-item/group.
	 *
	 * @param array $attributes Block attributes (from module metadata).
	 * @return array Map key => [ attrName, preset, subName? ].
	 */
	private static function collect_preset_map_from_attributes( array $attributes ) {
		$result = [];

		foreach ( $attributes as $attr_name => $attr_data ) {
			if ( $attr_name === 'module' ) {
				continue;
			}
			$attr_data = is_array( $attr_data ) ? $attr_data : (array) $attr_data;
			if ( empty( $attr_data ) ) {
				continue;
			}
			$settings = $attr_data['settings'] ?? [];
			if ( empty( $settings ) ) {
				continue;
			}

			foreach ( $settings as $attrs_type => $setting_items ) {
				$setting_items = is_array( $setting_items ) ? $setting_items : (array) $setting_items;
				if ( empty( $setting_items ) ) {
					continue;
				}
				foreach ( $setting_items as $setting_item_key => $setting_item ) {
					$setting_item = is_array( $setting_item ) ? $setting_item : (array) $setting_item;
					if ( empty( $setting_item ) ) {
						continue;
					}
					$full_attr_name = $attr_name . '.' . $attrs_type . '.' . $setting_item_key;
					$group_type     = $setting_item['groupType'] ?? null;

					if ( $group_type === 'group-item' ) {
						$item = $setting_item['item'] ?? null;
						$item = is_array( $item ) ? $item : (array) $item;
						$component = $item['component'] ?? [];
						$component = is_array( $component ) ? $component : (array) $component;
						if ( ! empty( $item['attrName'] ) && ! empty( $component['name'] ) ) {
							$item_attr_name = $item['attrName'];
							$group_name     = $component['name'];
							$group_map      = ModuleOptionsPresetAttrs::get_preset_attrs_from_group( $group_name, $item_attr_name );
							$result         = array_merge( $result, $group_map );
						}
					} elseif ( $group_type === 'group-items' ) {
						$items = $setting_item['items'] ?? [];
						$items = is_array( $items ) ? $items : (array) $items;
						foreach ( $items as $item ) {
							$item = is_array( $item ) ? $item : (array) $item;
							if ( empty( $item ) ) {
								continue;
							}
							$component = $item['component'] ?? [];
							$component = is_array( $component ) ? $component : (array) $component;
							if ( ! empty( $item['attrName'] ) && ! empty( $component['name'] ) ) {
								$group_map = ModuleOptionsPresetAttrs::get_preset_attrs_from_group(
									$component['name'],
									$item['attrName']
								);
								$result = array_merge( $result, $group_map );
							}
						}
					} elseif ( $group_type === 'group' ) {
						$group_name = $setting_item['groupName'] ?? ModuleOptionsPresetAttrs::get_the_group_name_by_key( $attrs_type, $setting_item_key );
						if ( ! empty( $group_name ) ) {
							$args       = [];
							$group_map  = ModuleOptionsPresetAttrs::get_preset_attrs_from_group( $group_name, $full_attr_name, $args );
							$result     = array_merge( $result, $group_map );
						}
					} else {
						$group_name = ModuleOptionsPresetAttrs::get_the_group_name_by_key( $attrs_type, $setting_item_key );
						if ( ! empty( $group_name ) ) {
							$group_map = ModuleOptionsPresetAttrs::get_preset_attrs_from_group( $group_name, $full_attr_name );
							$result    = array_merge( $result, $group_map );
						}
					}
				}
			}
		}

		return $result;
	}
}
