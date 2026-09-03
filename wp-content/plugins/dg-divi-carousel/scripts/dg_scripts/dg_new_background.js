export default class ModuleCustomBackgroundHelper {
    /**
     * Parsed *_last_edited value and determine whether the passed string means it has responsive value or not
     * *_last_edited holds two values (responsive status and last opened tabs) in the following format:
     * status|last_opened_tab
     *
     * @since 1.0.0
     *
     * @param {string} lastEdited Last edited value for module
     *
     * @return {string[]} Responsive values for all devices
     */
    static get_responsive_device(lastEdited) {
        return typeof lastEdited === 'string' ? lastEdited.split('|') : ['off', 'desktop'];
    }

    /**
     * Parsed *_last_edited value and determine wheter the passed string means it has responsive value or not
     * *_last_edited holds two values (responsive status and last opened tabs) in the following format:
     * status|last_opened_tab
     *
     * @since 1.0.0
     *
     * @param {string} lastEdited
     *
     * @return {string} Pass is responsive is on or not
     */
    static get_responsive_status(lastEdited) {
        return !!this.get_responsive_device(lastEdited)[0] ? this.get_responsive_device(lastEdited)[0] : 'off';
    }

    /**
     * Parsed offset type value and pass the offset list for the type
     *
     * @since 1.0.0
     *
     * @param {string} type
     *
     * @return {Array} Pass is the offset array list
     */
    static get_offset_properties(type) {

        const background_offset = {
            'horizontal': ['center_left', 'center_right'],
            'vertical': ['top_center', 'bottom_center'],
            'both': ['top_left', 'top_right', 'bottom_left', 'bottom_right'],
        }

        if (undefined !== background_offset[type]) {
            return background_offset[type];
        }

        return [];
    }

    /**
     * Parsed *_position and *_horizontal_offset value and generate sting value with the passed string
     *
     * @since 1.0.0
     *
     * @param {string} position The position value for background image
     * @param {string} offset The offset value for background image
     *
     * @return {string} Pass the actual value for horizontal offset
     */
    static get_horizontal_offset_only(position, offset) {
        return position.replace('_', `_${offset}_`).split('_').reverse().join(' ');
    }

    /**
     * Parsed *_position and *_vertical_offset value and generate sting value with the passed string
     *
     * @since 1.0.0
     *
     * @param {string} position The position value for background image
     * @param {string} offset The offset value for background image
     *
     * @return {string} Pass the actual value for vertical offset
     */
    static get_vertical_offset_only(position, offset) {
        let background_offset_values = position.split('_').reverse();

        // Update value
        background_offset_values.push(offset);

        return background_offset_values.join(' ');
    }

    /**
     * Parsed *_position, *_horizontal_offset and *_vertical_offset value and generate sting value with the passed string
     *
     * @since 1.0.0
     *
     * @param {string} position The position value for background image
     * @param {string} hOffset The horizontal offset value for background image
     * @param {string} vOffset The vertical offset value for background image
     *
     * @return {string} Pass the actual value for vertical offset
     */
    static get_background_image_offset(position, hOffset, vOffset) {
        let background_offset_values = position.replace('_', `_${hOffset}_`);
        background_offset_values = background_offset_values.split('_').reverse();
        background_offset_values.push(vOffset);

        return background_offset_values.join(' ');
    }

    /**
     * Set background color styles for current field with default, responsive and hover
     *
     * @since 1.0.0
     *
     * @param {Object} options All options which are provided by module
     *
     * @return void
     */
    static generateStyles_backgroundColor(options = {}) {
        const baseProp = options.base_name;
        const _color_last_edited = options.props[`${baseProp}_color_last_edited`];
        const _enable_color_tablet = options.props[`${baseProp}_enable_color_tablet`];
        const _enable_color_phone = options.props[`${baseProp}_enable_color_phone`];
        const __hover_enabled = options.props[`${baseProp}_color__hover_enabled`];
        const _enable_color__hover = options.props[`${baseProp}_enable_color__hover`];

        // Additional properties
        const additionalProp = options.important ? '!important' : '';

        // Working with default background color in default (Desktop) mode
        if (!!options.props[`${baseProp}_color`]) {
            options.additionalCSS.push([{
                'selector': options.selector,
                'declaration': `background-color: ${options.props[`${baseProp}_color`]} ${additionalProp};`
            }]);
        }

        // Working with background color in tablet device mode
        if (this.get_responsive_status(_color_last_edited) === 'on'
            && _enable_color_tablet === 'on') {
            options.additionalCSS.push([{
                'selector': options.selector,
                'declaration': `background-color: ${options.props[`${baseProp}_color_tablet`]} ${additionalProp};`,
                'device': 'tablet', // tablet
            }]);
        }

        // Working with background color in phone device mode
        if (this.get_responsive_status(_color_last_edited) === 'on'
            && _enable_color_phone === 'on') {
            options.additionalCSS.push([{
                'selector': options.selector,
                'declaration': `background-color: ${options.props[`${baseProp}_color_phone`]} ${additionalProp};`,
                'device': 'phone', // phone
            }]);
        }


        // Working with hover background color
        if (options.props['hover_enabled']
            && typeof options.props['hover_enabled'] === 'number'
            && __hover_enabled === 'on|hover'
            && _enable_color__hover === 'on') {
            options.additionalCSS.push([{
                'selector': !!options.hover ? options.hover : options.selector,
                'declaration': `background-color: ${options.props[`${baseProp}_color__hover`]} ${additionalProp};`
            }]);
        }
    }

    /**
     * Set background gradient color styles for the current field with default, responsive and hover
     *
     * @since 1.0.0
     *
     * @param {Object} options All options which are provided by module
     *
     * @return void
     */
    static generateStyles_backgroundImage(options = {}) {
        // Initiate variables
        let _gradient = {
            'type_property': '',
            'parsed_value': '',
            'declaration': '',
            'type': 'linear',
            'direction': '180deg',
            'unit': '%',
            'direction_radial': 'center',
            'stops': '#2b87da 0%|#29c4a9 100%'
        };

        const baseProp = options.base_name;
        const _color_last_edited = options.props[`${baseProp}_color_last_edited`];

        const _use_color_gradient_tablet = options.props[`${baseProp}_use_color_gradient_tablet`];
        const _enable_image_tablet = options.props[`${baseProp}_enable_image_tablet`];

        const _use_color_gradient_phone = options.props[`${baseProp}_use_color_gradient_phone`];
        const _enable_image_phone = options.props[`${baseProp}_enable_image_phone`];

        const __hover_enabled = options.props[`${baseProp}_color__hover_enabled`];
        const _use_color_gradient__hover = options.props[`${baseProp}_use_color_gradient__hover`];
        const _enable_image__hover = options.props[`${baseProp}_enable_image__hover`];

        // Working with default background gradient color and image
        this.process_gradient_properties(options, _gradient);
        this.process_background_image(options, _gradient);

        // Working with tablet background gradient color and image
        if (this.get_responsive_status(_color_last_edited) === 'on'
            && (_use_color_gradient_tablet === 'on' || _enable_image_tablet === 'on')) {
            // Working with tablet mode background gradient color and image
            this.process_gradient_properties(options, _gradient, 'tablet');
            this.process_background_image(options, _gradient, 'tablet')
        }

        // Working with phone background gradient color and image
        if (this.get_responsive_status(_color_last_edited) === 'on'
            && (_use_color_gradient_phone === 'on' || _enable_image_phone === 'on')) {
            // Working with mobile background gradient color and image
            this.process_gradient_properties(options, _gradient, 'phone');
            this.process_background_image(options, _gradient, 'phone')
        }

        // Working with hover background gradient color and image
        if (options.props['hover_enabled']
            && typeof options.props['hover_enabled'] === 'number'
            && __hover_enabled === 'on|hover'
            && (_use_color_gradient__hover === 'on' || _enable_image__hover === 'on')) {
            // Working with hover background gradient color and image
            this.process_gradient_properties(options, _gradient, '_hover');
            this.process_background_image(options, _gradient, '_hover')
        }
    }

    /**
     * Process the background gradient property for current background field
     *
     * @since 1.0.0
     *
     * @param {Object} options All options which are provided by module
     * @param {Object} _gradient The property object for Gradient Color
     * @param {String} device current device name, it will be empty when device is desktop
     *
     * @return {Object} The property object for Gradient Color
     */
    static process_gradient_properties(options = {}, _gradient = {}, device = '') {
        const _current_device = device !== '' ? `_${device}` : device;

        if (!!options.props[`${options.context}_gradient_stops${_current_device}`]) {
            _gradient.stops = options.props[`${options.context}_gradient_stops${_current_device}`];
        }

        if (!!options.props[`${options.context}_gradient_type${_current_device}`]) {
            _gradient.type = options.props[`${options.context}_gradient_type${_current_device}`];
        }

        if (!!options.props[`${options.context}_gradient_direction${_current_device}`]) {
            _gradient.direction = options.props[`${options.context}_gradient_direction${_current_device}`];
        }

        if (!!options.props[`${options.context}_gradient_direction_radial${_current_device}`]) {
            _gradient.direction_radial = options.props[`${options.context}_gradient_direction_radial${_current_device}`];
        }

        // download_referral_label_background_color_gradient_unit
        if (!!options.props[`${options.context}_gradient_unit${_current_device}`] && _gradient.type !== 'conic') {
            _gradient.unit = options.props[`${options.context}_gradient_unit${_current_device}`];
            _gradient.stops = _gradient.stops.replace(/%/ig, _gradient.unit);
        }

        _gradient.type_property_prefix = _gradient.type === 'circular' || _gradient.type === 'elliptical' ? 'radial' : _gradient.type;
        _gradient.type_property = `${_gradient.type_property_prefix}-gradient`;
        _gradient.parsed_value = _gradient.stops.split('|').join(', ');

        // background_color_gradient_repeat = on
        if (options.props[`${options.base_name}_color_gradient_repeat${_current_device}`] === 'on') {
            _gradient.type_property = `repeating-${_gradient.type_property}`;
        }

        return _gradient;
    }

    /**
     * Process the background gradient property and image for current background field
     *
     * @since 1.0.0
     *
     * @param {Object} options All options which are provided by module
     * @param {Object} _gradient The property object for Gradient Color
     * @param {String} device current device name, it will be empty when device is desktop
     *
     * @return void
     */
    static process_background_image(options = {}, _gradient = {}, device = '') {
        let backgroundImage = '';
        let backgroundGradientImage = '';
        let generatedBackgroundImage = '';
        const _current_device = device !== '' ? `_${device}` : device;
        const additionalProp = options.important ? '!important' : '';

        // prevent default gradient color, when gradient color is turn off but image is enabled by default.
        if (options.props[`${options.base_name}_use_color_gradient`] === 'on'){

            // default linear gradient declaration
            if (_gradient.type === 'linear') {
                _gradient.declaration = `${_gradient.type_property}(${_gradient.direction}, ${_gradient.parsed_value})`;
            }

            // default conic gradient declaration
            if (_gradient.type === 'conic') {
                _gradient.declaration = `${_gradient.type_property}(from ${_gradient.direction} at ${_gradient.direction_radial}, ${_gradient.parsed_value})`;
            }

            // default circular gradient declaration
            if (_gradient.type === 'circular') {
                _gradient.declaration = `${_gradient.type_property}(circle at ${_gradient.direction_radial}, ${_gradient.parsed_value})`;
            }

            // default elliptical gradient declaration
            if (_gradient.type === 'elliptical') {
                _gradient.declaration = `${_gradient.type_property}(ellipse at ${_gradient.direction_radial}, ${_gradient.parsed_value})`;
            }

            backgroundGradientImage = _gradient.declaration;
        }

        // Set parallax effect for background image
        // download_referral_label_background_parallax : "off"
        if (options.props[`${options.base_name}_parallax`] !== 'on') {
            // Set image background size
            if (!!options.props[`${options.base_name}_size${_current_device}`]) {
                if (options.props[`${options.base_name}_size${_current_device}`] === 'custom') {
                    let _image_width_prop = `${options.base_name}_image_width${_current_device}`;
                    let _image_height_prop = `${options.base_name}_image_height${_current_device}`;

                    if (!!options.props[_image_width_prop] || !!options.props[_image_height_prop]) {
                        let _image_width = !!options.props[_image_width_prop] ? options.props[_image_width_prop] : 'auto';
                        let _image_height = !!options.props[_image_height_prop] ? options.props[_image_height_prop] : 'auto';

                        this.merge_additional_css(options, device, {
                            'selector': options.selector,
                            'declaration': `background-size: ${_image_width} ${_image_height} ${additionalProp};`,
                        })
                    } else {
                        this.merge_additional_css(options, device, {
                            'selector': options.selector,
                            'declaration': `background-size: initial ${additionalProp};`,
                        })
                    }
                } else {
                    this.merge_additional_css(options, device, {
                        'selector': options.selector,
                        'declaration': `background-size: ${options.props[`${options.base_name}_size${_current_device}`]} ${additionalProp};`,
                    })
                }
            }

            // Set image background position
            if (!!options.props[`${options.base_name}_position${_current_device}`]) {
                const _position = options.props[`${options.base_name}_position${_current_device}`];
                let _horizontal_offset_prop = `${options.base_name}_horizontal_offset${_current_device}`;
                let _vertical_offset_prop = `${options.base_name}_vertical_offset${_current_device}`;

                // Horizontal Offset only
                if (this.get_offset_properties('horizontal').includes(options.props[`${options.base_name}_position${_current_device}`])) {
                    if (!!options.props[_horizontal_offset_prop]) {
                        let _horizontal_offset = options.props[_horizontal_offset_prop];
                        let generated_value = this.get_horizontal_offset_only(_position, _horizontal_offset);

                        this.merge_additional_css(options, device, {
                            'selector': options.selector,
                            'declaration': `background-position: ${generated_value} ${additionalProp};`
                        })
                    }
                }

                // Vertical Offset only
                if (this.get_offset_properties('vertical').includes(options.props[`${options.base_name}_position${_current_device}`])) {
                    if (!!options.props[_horizontal_offset_prop]) {
                        let _vertical_offset = options.props[_vertical_offset_prop];
                        let generated_value = this.get_vertical_offset_only(_position, _vertical_offset);

                        this.merge_additional_css(options, device, {
                            'selector': options.selector,
                            'declaration': `background-position: ${generated_value} ${additionalProp};`
                        })
                    }
                }

                // Horizontal and Vertical Offset bpth
                if (this.get_offset_properties('both').includes(options.props[`${options.base_name}_position${_current_device}`])) {
                    let _horizontal_offset = !!options.props[_horizontal_offset_prop] ? options.props[_horizontal_offset_prop] : '0px';
                    let _vertical_offset = !!options.props[_vertical_offset_prop] ? options.props[_vertical_offset_prop] : '0px';

                    const generated_value = this.get_background_image_offset(_position, _horizontal_offset, _vertical_offset)
                    this.merge_additional_css(options, device, {
                        'selector': options.selector,
                        'declaration': `background-position: ${generated_value} ${additionalProp};`
                    })
                }

                // set background position for default state
                if (_position === 'center') {
                    let background_position = options.props[`${options.base_name}_position${_current_device}`].split('_').reverse().join(' ');
                    this.merge_additional_css(options, device, {
                        'selector': options.selector,
                        'declaration': `background-position: ${background_position} ${additionalProp};`
                    })
                }
            }

            // Set background repeat option
            if (!!options.props[`${options.base_name}_repeat${_current_device}`]) {
                this.merge_additional_css(options, device, {
                    'selector': options.selector,
                    'declaration': `background-repeat: ${options.props[`${options.base_name}_repeat${_current_device}`]} ${additionalProp};`
                })
            }
        }

        // Set background blend option
        if (!!options.props[`${options.base_name}_blend${_current_device}`]) {
            this.merge_additional_css(options, device, {
                'selector': options.selector,
                'declaration': `background-blend-mode: ${options.props[`${options.base_name}_blend${_current_device}`]} ${additionalProp};`,
            })
        }

        // download_referral_label_background__image = undefined, url
        if (!!options.props[`${options.base_name}_image${_current_device}`]) {
            backgroundImage = `url(${options.props[`${options.base_name}_image${_current_device}`]})`;
        }

        // download_referral_label_background_color_gradient_overlays_image = undefined, off
        if (!!backgroundGradientImage && !!backgroundImage){
            if (options.props[`${options.base_name}_color_gradient_overlays_image${_current_device}`] === 'on') {
                generatedBackgroundImage = `${backgroundGradientImage}, ${backgroundImage}`;
            } else {
                generatedBackgroundImage = `${backgroundImage}, ${backgroundGradientImage}`;
            }
        } else {
            if (!!backgroundGradientImage){
                generatedBackgroundImage = backgroundGradientImage;
            } else {
                generatedBackgroundImage = backgroundImage;
            }
        }

        // Create default background color declaration
        if (!!generatedBackgroundImage){
            this.merge_additional_css(options, device, {
                'selector': options.selector,
                'declaration': `background-image: ${generatedBackgroundImage} ${additionalProp};`,
            });
        }
    }

    /**
     * Merge the current custom style object into the Additional CSS object for Divi
     *
     * @since 1.0.0
     *
     * @param {Object} options All options which are provided by module
     * @param {String} device current device name, it will be empty when device is desktop
     * @param {Object} customStyle All options which are provided by module
     *
     * @return void
     */
    static merge_additional_css(options, device = '', customStyle = {}) {
        // Add a device into a style object when a current device is tablet or phone
        if (device !== '' && device !== '_hover') {
            customStyle.device = device;
        }

        options.additionalCSS.push([customStyle]);
    }

    /**
     * Set background styles for a current element with default, responsive and hover
     *
     * @since 1.0.0
     *
     * @param {Object} options All options which are provided by module
     *
     * @return void
     */
    static generateStyles(options) {
        // Working with background color
        this.generateStyles_backgroundColor(options);

        // Working with background gradient color and image
        this.generateStyles_backgroundImage(options);
    }
}