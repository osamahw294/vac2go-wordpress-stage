<?php

namespace DICA\Server\Traits;

/**
 * Trait GetPropsTrait
 *
 * Creates a getSettings function bound to specific elements and attrs context
 * This trait provides functionality to search for properties in nested objects
 */
trait GetPropsTrait
{
    use ProcessAttrWithPreset;

    /**
     * The props array that will be populated with resolved values
     * @var array
     */
    protected $props = [];

    /**
     * Elements data containing moduleMetadata
     * @var array|object
     */
    protected $elements;

    /**
     * Attributes data to search in as primary source
     * @var array|object
     */
    protected $attrs;

    /**
     * Initialize the props resolver with elements and attrs context
     *
     * @param array|object $elements The elements array/object containing moduleMetadata and other properties
     * @param array|object $attrs The attributes array/object to search in as primary source
     * @param string $moduleSlug The module slug identifier
     */
    public function initializeProps($elements, $attrs, $moduleSlug = null)
    {
        $this->elements = $elements;
        $this->attrs = $this->processAttrWithPreset($attrs, $elements->name);
        $this->props = new PropsResolver($this);
    }

    /**
     * Get a property value using the resolver logic
     *
     * @param string $key The property key to resolve
     * @return mixed The resolved value
     */
    public function getProp(string $key)
    {
        $targetedAttrs = $this->attrs;
        $retrieveValueFromAttr = $this->searchObject($targetedAttrs, $key);

        if ($retrieveValueFromAttr !== null) {
            return $retrieveValueFromAttr;
        } else {
            $retrieveValueFromMeta = null;

            try {
                $closure = $this->elements->module_metadata->render_callback;
                $elementsArray = [];
                if ($closure instanceof \Closure) {
                    $reflection = new \ReflectionFunction($closure);
                    $staticVars = $reflection->getStaticVariables();
                    $elementsArrayObj = $staticVars['metadata'];
                    $elementsArray = $elementsArrayObj['attributes'];
                }


                if (isset($elementsArray)) {
                    $metaAttributes = $elementsArray;
                    $searchResult = $this->searchObject($metaAttributes, $key);

                    if ($searchResult !== null &&
                        isset($searchResult['defaultAttr']['desktop']['value'][$key])) {
                        $retrieveValueFromMeta = $searchResult['defaultAttr']['desktop']['value'][$key];
                    }
                }
            } catch (Exception $e) {
                // Silent catch - equivalent to empty catch in JS
            } catch (\ReflectionException $e) {
            }

            return $retrieveValueFromMeta;
        }
    }

    /**
     * Recursively searches for a target key in nested arrays/objects
     *
     * @param mixed $obj The array/object to search in
     * @param string $targetKey The key to search for
     * @return mixed The value if found, null otherwise
     */
    private function searchObject($obj, string $targetKey)
    {
        // Handle null or non-array/object values
        if ((!is_array($obj) && !is_object($obj))) {
            return null;
        }

        // Convert object to array for consistent handling
        $array = is_object($obj) ? (array)$obj : $obj;

        // Check if target key exists directly
        if (array_key_exists($targetKey, $array)) {
            return $array[$targetKey];
        }

        // Recursively search in nested arrays/objects
        foreach ($array as $prop => $value) {
            if ($prop!== 'displayCondition' && $prop !== 'decoration' && $prop !== 'hover' && $prop !== 'phone' && $prop !== 'tablet' &&  $prop !== 'tabletWide' && $prop !== 'ultraWide' && $prop !== 'phoneWide' && $prop !== 'widescreen') {
                $result = $this->searchObject($value, $targetKey);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }
}

/**
 * Class PropsResolver
 *
 * Implements ArrayAccess to allow array-like access to resolved properties
 */
class PropsResolver implements \ArrayAccess
{
    private $trait;
    private array $cache = [];

    public function __construct($trait)
    {
        $this->trait = $trait;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return true; // Always return true since we can attempt to resolve any key
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        // Use cache to avoid repeated resolution
        if (!isset($this->cache[$offset])) {
            $this->cache[$offset] = $this->trait->getProp($offset);
        }
        return $this->cache[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        // Allow manual setting of props
        $this->cache[$offset] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->cache[$offset]);
    }
}
