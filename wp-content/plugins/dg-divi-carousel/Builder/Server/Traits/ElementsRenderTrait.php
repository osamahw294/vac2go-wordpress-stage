<?php
/**
 * Elements Render Trait for Divi Carousel Modules
 *
 * This trait provides reusable methods for modules that support
 * the "Add Element" functionality (nested Divi modules).
 *
 * Usage:
 * 1. Add `use ElementsRenderTrait;` in your module class
 * 2. Use the provided methods for encoding data and handling elements HTML
 *
 * @package DICA\Server\Traits
 */

namespace DICA\Server\Traits;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

trait ElementsRenderTrait {
    /**
     * Encode data array for safe HTML attribute storage
     *
     * Uses JSON encoding with flags to safely escape special characters
     * that could break HTML attributes or cause XSS issues.
     *
     * @param array $data The data array to encode
     * @return string JSON encoded string safe for HTML attributes
     */
    protected static function encodeDataForAttribute(array $data): string {
        return wp_json_encode(
            $data,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
    }

    /**
     * Get child elements HTML from block content
     *
     * The $content parameter in render_callback contains the rendered
     * HTML of child blocks (nested modules added via Add Element).
     *
     * @param string|null $content The content from render_callback
     * @return string The elements HTML or empty string
     */
    protected static function getElementsHtml($content): string {
        return $content ?: '';
    }

    /**
     * Build a data attribute value containing elements and other content data
     *
     * This is a convenience method that combines content data with elements HTML
     * and returns an escaped attribute value ready for use in HTML.
     *
     * @param array $contentData Array of content data (title, description, etc.)
     * @param string|null $content The block content containing child elements HTML
     * @param string $elementsKey The key to use for elements HTML (default: 'elements_html')
     * @return string Escaped attribute value
     */
    protected static function buildContentDataAttribute(
        array $contentData,
        $content = null,
        string $elementsKey = 'elements_html'
    ): string {
        $contentData[$elementsKey] = self::getElementsHtml($content);
        return esc_attr(self::encodeDataForAttribute($contentData));
    }

    /**
     * Check if module has child elements
     *
     * @param string|null $content The block content
     * @return bool Whether the module has child elements
     */
    protected static function hasChildElements($content): bool {
        return !empty(trim($content ?? ''));
    }

    /**
     * Wrap elements HTML in a container div
     *
     * @param string|null $content The elements HTML content
     * @param string $className The CSS class for the wrapper (default: 'dica_elements')
     * @return string The wrapped HTML or empty string if no content
     */
    protected static function wrapElementsHtml($content, string $className = 'dica_elements'): string {
        $html = self::getElementsHtml($content);

        if (empty(trim($html))) {
            return '';
        }

        return sprintf(
            '<div class="%s">%s</div>',
            esc_attr($className),
            $html
        );
    }
}
