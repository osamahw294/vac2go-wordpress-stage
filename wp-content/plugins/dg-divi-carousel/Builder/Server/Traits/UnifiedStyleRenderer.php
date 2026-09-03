<?php

namespace DICA\Server\Traits;


use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use SimplePie\Exception;

/**
 * Main trait that combines all style rendering functionality
 */
trait UnifiedStyleRenderer
{
    protected function evaluateDisplayConditions($displayCondition, $attrs, $elements, $parentKey)
    {
        // If no display condition, always show
        if (!$displayCondition) {
            return true;
        }

        $checkConditions = function ($conditions, $shouldMatch = true) use ($attrs, $elements, $parentKey) {
            foreach ($conditions as $key => $expectedVal) {
                if ($key === "function.isTBLayout") {
                    continue;
                }

                // Get current setting value from attrs
                $settingVal = $attrs[$parentKey]['innerContent']['desktop']['value'][$key] ?? null;

                if ($settingVal === null) {
                    try {
                        $settingVal = $this->props[$key];
                    } catch (\Throwable $e) {
                        $settingVal = '';
                    }

                }

                // Check if value matches expected value(s)
                $matches = is_array($expectedVal)
                    ? in_array($settingVal, $expectedVal)
                    : $settingVal === $expectedVal;

                if ($shouldMatch ? !$matches : $matches) {
                    return false;
                }
            }

            return true;
        };

        // Evaluate show_if conditions (must match)
        $showIf = isset($displayCondition['show_if'])
            ? $checkConditions($displayCondition['show_if'], true)
            : true;

        // Evaluate show_if_not conditions (must NOT match)
        $showIfNot = isset($displayCondition['show_if_not'])
            ? $checkConditions($displayCondition['show_if_not'], false)
            : true;

        // Both conditions must be true for the style to render
        return $showIf && $showIfNot;
    }

    protected function buildCommonStyleSettings($styleElements)
    {
        $commonStyleSettings = [];

        if (!is_array($styleElements)) {
            return $commonStyleSettings;
        }
        foreach ($styleElements as $parentKey => $values) {
            $innerContentItems = $values['settings']['innerContent']['items'] ?? null;

            // Guard clause: ensure innerContentItems exists and is an array
            if (!$innerContentItems || !is_array($innerContentItems)) {
                continue;
            }

            // Iterate through each sub-item in innerContent
            foreach ($innerContentItems as $subKey => $subValue) {

                $customCssAttr = $subValue['customCssAttr'] ?? null;

                // Guard clause: ensure required CSS attributes exist
                if (
                    !isset($customCssAttr['css_type']) ||
                    !isset($customCssAttr['selectors']['selector'])
                ) {
                    continue;
                }

                // Build style configuration array
                $styleConfig = [
                    'selectors' => $customCssAttr['selectors'],
                    'parentKey' => $parentKey,
                    'css_type' => $customCssAttr['css_type'],
                    'subKey' => $subKey,
                ];
                if(!empty($customCssAttr['css_function_name'])){
                    $styleConfig['css_function_name'] = $customCssAttr['css_function_name'];
                }

                // Add display condition if it exists
                if (isset($subValue['displayCondition'])) {
                    $styleConfig['displayCondition'] = $subValue['displayCondition'];
                }

                $commonStyleSettings[] = $styleConfig;
            }
        }

        return $commonStyleSettings;
    }

    protected function buildDecorativeStyleSettings($styleElements, $elements)
    {
        $styleSettings = [];

        if (!is_array($styleElements)) {
            return $styleSettings;
        }


        foreach ($styleElements as $key => $values) {

            if (!isset($values['settings']['decoration'])) {
                continue;
            }

            $styleSettings[] = [
                'attrName' => $key,
            ];
        }

        return $styleSettings;
    }


    /**
     * Generate unified styles (both decorative and common styles)
     *
     * @param array $elements
     * @param array $attrs
     * @param string $orderClass
     * @param string $moduleSlug
     * @return array Array of style strings
     */
    public function renderUnifiedStyles($elements, $attrs, $orderClass, $moduleSlug = '')
    {
        $styles = [];
        $styleElements = [];

        $closure = $elements->module_metadata->render_callback;

        if ($closure instanceof \Closure) {
            $reflection = new \ReflectionFunction($closure);
            $staticVars = $reflection->getStaticVariables();
            $styleElementsObj = $staticVars['metadata'];
            $styleElements = $styleElementsObj['attributes'];
        }


        if (empty($styleElements)) {
            return $styles;
        }
        // Build decorative and common style settings
        $decorativeStyles = $this->buildDecorativeStyleSettings($styleElements, $elements);
        $commonStyleSettings = $this->buildCommonStyleSettings($styleElements);

        // Render Decorative Styles
        foreach ($decorativeStyles as $styleConfig) {
            $styles[] = $elements->style(
                [
                    'attrName' => $styleConfig['attrName']
                ]
            );

            if (!empty($styleElements[$styleConfig['attrName']]['settings']['decoration']['border'])) {

                $styles[] = CommonStyle::style([
                    'selector' => str_replace('{{selector}}', $orderClass, $styleElements[$styleConfig['attrName']]['selector']),
                    'attr' => $attrs[$styleConfig['attrName']]['decoration']['border'] ?? [],
                    'declarationFunction' => function ($props) {
                        $radius = $props['attrValue']['radius'] ?? [];
                        $hasAllCornersUndefined =
                            !isset($radius['bottomLeft']) &&
                            !isset($radius['bottomRight']) &&
                            !isset($radius['topLeft']) &&
                            !isset($radius['topRight']);
                        return $hasAllCornersUndefined ? '' : 'overflow: hidden;';
                    },
                ]);
            }


        }
        // Render Common Styles with Conditions
        foreach ($commonStyleSettings as $index => $styleConfig) {
            $shouldRender = $this->evaluateDisplayConditions(
                $styleConfig['displayCondition'] ?? null,
                $attrs,
                $elements,
                $styleConfig['parentKey']
            );

            if (!$shouldRender) {
                continue;
            }
            $selector = str_replace('{{selector}}', $orderClass, $styleConfig['selectors']['selector']);


            if(isset($styleConfig['selectors']['hover'])){
                $styles[] = CommonStyle::style([
                    'selector' => $selector,
                    'selectorFunction' => function ($props) use ($styleConfig, $orderClass, $selector) {
                        if ($props['state'] === 'hover' && isset($styleConfig['selectors']['hover'])) {
                            return str_replace('{{selector}}', $orderClass, $styleConfig['selectors']['hover']);
                        }
                        return $selector;
                    },
                    'attr' => $attrs[$styleConfig['parentKey']]['innerContent'] ?? null,
                    'declarationFunction' => function ($props) use ($styleConfig) {
                        //RESOLVE VALUE FROM PROPS
                        if (!empty($props['attr'][$props['breakpoint']][$props['state']][$styleConfig['subKey']])) {
                            if (!empty($styleConfig['css_function_name'])) {
                                return $styleConfig['css_type'] . ':' . $styleConfig['css_function_name'] . '(' . ($props['attr'][$props['breakpoint']][$props['state']][$styleConfig['subKey']] ?? '') . ') !important;';
                            } else {
                                return $styleConfig['css_type'] . ':' . ($props['attr'][$props['breakpoint']][$props['state']][$styleConfig['subKey']] ?? '') . ' !important;';
                            }
                        }
                        //RESOLVE FALLBACK VALUE ONLY APPLICABLE FOR --DESKTOP--
                        else if ($props['state'] !== 'hover' && $props['breakpoint'] === 'desktop') {
                            if (!empty($styleConfig['css_function_name'])) {
                                return $styleConfig['css_type'] . ':' . $styleConfig['css_function_name'] . '(' . ($this->props[$styleConfig['subKey']] ?? '') . ') !important;';
                            } else {
                                return $styleConfig['css_type'] . ':' . ($this->props[$styleConfig['subKey']] ?? '') . ' !important;';
                            }
                        }
                        return '';
                    },
                ]);
            }
            else{
                $styles[] = CommonStyle::style([
                    'selector' => $selector,
                    'attr' => $attrs[$styleConfig['parentKey']]['innerContent'] ?? null,
                    'declarationFunction' => function ($props) use ($styleConfig) {
                        //RESOLVE VALUE FROM PROPS
                        if (!empty($props['attr'][$props['breakpoint']][$props['state']][$styleConfig['subKey']])) {
                            if (!empty($styleConfig['css_function_name'])) {
                                return $styleConfig['css_type'] . ':' . $styleConfig['css_function_name'] . '(' . ($props['attr'][$props['breakpoint']][$props['state']][$styleConfig['subKey']] ?? '') . ') !important;';
                            } else {
                                return $styleConfig['css_type'] . ':' . ($props['attr'][$props['breakpoint']][$props['state']][$styleConfig['subKey']] ?? '') . ' !important;';
                            }
                        }
                        //RESOLVE FALLBACK VALUE ONLY APPLICABLE FOR --DESKTOP--
                        else if ($props['state'] !== 'hover' && $props['breakpoint'] === 'desktop') {
                            if (!empty($styleConfig['css_function_name'])) {
                                return $styleConfig['css_type'] . ':' . $styleConfig['css_function_name'] . '(' . ($this->props[$styleConfig['subKey']] ?? '') . ') !important;';
                            } else {
                                return $styleConfig['css_type'] . ':' . ($this->props[$styleConfig['subKey']] ?? '') . ' !important;';
                            }
                        }
                        return '';
                    },
                ]);
            }
        }

        return $styles;
    }

}
