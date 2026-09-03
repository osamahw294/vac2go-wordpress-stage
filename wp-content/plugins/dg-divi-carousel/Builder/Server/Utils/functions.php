<?php
use ET\Builder\FrontEnd\Assets\DynamicAssetsUtils;

/**
 * Add et_icons_fa (icons_fa_all.css) to the given assets list.
 * Same structure as Divi: feature_state->early_global_asset_list['et_icons_fa'].
 *
 * @param array $assets Current assets list (passed by filter).
 * @return array Modified assets list.
 */
function dica_global_fa_icon_asset( $assets ) {


	if ( isset( $assets['et_icons_fa'] ) ) {
		return $assets;
	}

	$assets_prefix = null;
	if ( function_exists( 'et_get_dynamic_assets_path' ) ) {
		$assets_prefix = DynamicAssetsUtils::get_dynamic_assets_path();
	} elseif ( class_exists( DynamicAssetsUtils::class ) ) {
		$assets_prefix = DynamicAssetsUtils::get_dynamic_assets_path();
	}

	if ( $assets_prefix ) {
		$assets['et_icons_all'] = [
			'css' => $assets_prefix . '/css/icons_all.css',
		];

        // font-awesome icon asset.
		$assets['et_icons_fa'] = [
			'css' => $assets_prefix . '/css/icons_fa_all.css',
		];
	}

	return $assets;
}

/**
 * Add et_icons_fa to the early global assets list (Divi 5).
 * Filter passes ( $early_global_asset_list, $assets_args, $list_builder ).
 */
function dica_early_global_fa_icon_asset( $early_global_asset_list, $assets_args, $list_builder ) {
	return dica_global_fa_icon_asset( $early_global_asset_list );
}

/**
 * Register FontAwesome asset globally. We cannot set feature_state->use_fa_icons (private);
 * we add the same asset via Divi's early and late list filters instead.
 */

function dica_register_global_fa_icon_asset() {
	// Divi 5 early asset filter.
	add_filter( 'divi_frontend_assets_dynamic_assets_global_assets_list', 'dica_early_global_fa_icon_asset', 10, 3 );

	// Divi 5 late asset filter.
	add_filter( 'divi_frontend_assets_dynamic_assets_late_global_assets_list', 'dica_global_fa_icon_asset', 10, 1 );
}

add_action( 'init', 'dica_register_global_fa_icon_asset', 5 );


function DiCa_generateModuleInnerContentConversionOutline( $module_json ) {
    $attributes            = $module_json['attributes'] ?? [];
    $module_attribute_keys = [];

    foreach ( $attributes as $parent_key => $parent_value ) {
        if ( $parent_key === 'module' ) {
            continue;
        }

        if ( isset( $parent_value['settings']['innerContent']['items'] ) ) {
            $child_elements = $parent_value['settings']['innerContent']['items'];
            foreach ( $child_elements as $child_key => $child_value ) {
                $key_to_use                           = $child_value['subName'] ?? $child_key;
                $module_attribute_keys[ $key_to_use ] = "{$parent_key}.innerContent.*.{$key_to_use}";
            }
        } elseif ( isset( $parent_value['settings']['innerContent']['item'] ) ) {
            $key_to_use                           = $parent_key;
            $module_attribute_keys[ $key_to_use ] = "{$parent_key}.innerContent.*";
        }
    }

    return $module_attribute_keys;
}

function DiCa4ToDiCa5HandleGradientColorPosition( $value ) {
    if ( is_string( $value ) && strpos( $value, '%' ) !== false ) {
        return str_replace( '%', '', $value );
    }

    return $value;
}

function DiCa4ToDiCa5HandleGradientType( $value ) {
    if ( $value === 'radial' ) {
        $value = 'circular';
    }

    return $value;
}

function DiCa4ToDiCa5Background( $d4_key, $d5_key ) {
    return [
        "{$d4_key}_bgcolor" => "{$d5_key}.decoration.background.*.color",
        "{$d4_key}_bg_color" => "{$d5_key}.decoration.background.*.color",
        "{$d4_key}_bg_enable_color" => "{$d5_key}.decoration.background.enable",
        "{$d4_key}_use_gradient" => "{$d5_key}.decoration.background.*.gradient.enabled",
        "{$d4_key}_bg_use_color_gradient" => "{$d5_key}.decoration.background.*.gradient.enabled",
        "{$d4_key}_color_gradient_1" => "{$d5_key}.decoration.background.*.gradient.stops.0.color",
        "{$d4_key}_color_gradient_stops" => "{$d5_key}.decoration.background.*.gradient.stops.0.color",
        "{$d4_key}_color_gradient_2" => "{$d5_key}.decoration.background.*.gradient.stops.1.color",
        "{$d4_key}_gradient_type" => "{$d5_key}.decoration.background.*.gradient.type",
        "{$d4_key}_radial_direction" => "{$d5_key}.decoration.background.*.gradient.directionRadial",
        "{$d4_key}_gradient_direction" => "{$d5_key}.decoration.background.*.gradient.direction",
        "{$d4_key}_start_position" => "{$d5_key}.decoration.background.*.gradient.stops.0.position",
        "{$d4_key}_end_position" => "{$d5_key}.decoration.background.*.gradient.stops.1.position",
        "{$d4_key}_above_image" => "{$d5_key}.decoration.background.*.gradient.overlaysImage",
        "{$d4_key}_background_image" => "{$d5_key}.decoration.background.*.image.url",
        "{$d4_key}_background_image_size" => "{$d5_key}.decoration.background.*.image.size",
        "{$d4_key}_size_width" => "{$d5_key}.decoration.background.*.image.width",
        "{$d4_key}_size_height" => "{$d5_key}.decoration.background.*.image.height",
        "{$d4_key}_background_image_position" => "{$d5_key}.decoration.background.*.image.position",
        "{$d4_key}_position_horizontal" => "{$d5_key}.decoration.background.*.image.horizontalOffset",
        "{$d4_key}_position_vertical" => "{$d5_key}.decoration.background.*.image.verticalOffset",
        "{$d4_key}_background_image_repeat" => "{$d5_key}.decoration.background.*.image.repeat",

    ];
}
function DiCa4ToDiCa5HandleBGImagePosition( $value ) {
    $position_map = [
        'top_left' => 'left top',
        'top_center' => 'center top',
        'top_right' => 'right top',
        'center_left' => 'left center',
        'center_center' => 'center center',
        'center_right' => 'right center',
        'bottom_left' => 'left bottom',
        'bottom_center' => 'center bottom',
        'bottom_right' => 'right bottom',
    ];

    return $position_map[ $value ] ?? $value;
}

function DiCa4ToDiCa5BackgroundBulkConversion( $obj ) {
    $bg_obj = [];
    foreach ( $obj as $d4_key => $d5_key ) {
        $bg_obj = array_merge( $bg_obj, DiCa4ToDiCa5Background( $d4_key, $d5_key ) );
    }

    return $bg_obj;
}



function DiCa4ToDiCa5Spacing( $value ) {
    $parts = explode( '|', $value );
    $sync_horizontal = $parts[4] ?? '';
    $sync_vertical   = $parts[5] ?? '';

    return [
        'top'            => $parts[0] ?? '',
        'right'          => $parts[1] ?? '',
        'bottom'         => $parts[2] ?? '',
        'left'           => $parts[3] ?? '',
        'syncHorizontal' => 'true' === $sync_horizontal ? 'on' : 'off',
        'syncVertical'   => 'true' === $sync_vertical ? 'on' : 'off',
    ];
}
function DiCa4ToDiCa5Convert_align( $value ) {
	if ( $value === 'justified' ) {
		return 'space-between';
	}
	return $value;
}
function DiCa4ToDiCa5Icon( $value ) {
    $parts = explode( '|', $value );

    return [
        'unicode' => $parts[0] ?? '',
        'type'    => $parts[2] ?? '',
        'weight'  => $parts[4] ?? '',
    ];
}


