<?php

namespace DICA\Server\Traits;

use ET\Builder\Packages\GlobalData\GlobalPreset;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

/**
 * Trait for processing module attributes with preset values
 */
trait ProcessAttrWithPreset
{
    /**
     * Process module attributes with preset values
     *
     * @param array $attrs Original module attributes
     * @param string $module_slug Module slug (e.g., 'dica/ratingbox')
     * @return array Merged attributes with preset values
     */
    public static function processAttrWithPreset($attrs, $module_slug)
    {
        try {
            // Check if we have preset data to merge
            if (empty($attrs['modulePreset'])) {
                return $attrs; // No presets to merge
            }

            // Get merged attributes from GlobalPreset system
            $merged_attrs = GlobalPreset::get_merged_attrs([
                'moduleName'  => $module_slug,
                'moduleAttrs' => $attrs
            ]);

            // If preset system returned merged data, use it; otherwise fallback to original
            if (!empty($merged_attrs) && is_array($merged_attrs)) {
                // Merge with original attrs, giving priority to original values
                return array_replace_recursive($merged_attrs, $attrs);
            }

            return $attrs;

        } catch (\Exception $e) {
            // Log error and return original attributes as fallback
            error_log('ProcessAttrWithPreset Error: ' . $e->getMessage());
            return $attrs;
        }
    }
}
