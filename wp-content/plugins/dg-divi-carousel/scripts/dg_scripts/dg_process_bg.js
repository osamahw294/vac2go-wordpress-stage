
class process_background {
    
    constructor ( options ) {
        this.props = options.props;
        this.options = options;
        this.slug = options.key;
        this.additionalCss = options.additionalCss;
        this.separator = '';
        this.imortant_text = '';
        this.settings = {
            '_bgcolor'                     : '',
            '_use_gradient'                : 'off',
            '_above_image'                 : 'off',
            '_color_gradient_1'            : '#2b87da',
            '_color_gradient_2'            : '#29c4a9',
            '_gradient_type'               : 'leniar',
            '_gradient_direction'          : '180deg',
            '_start_position'              : '0%',
            '_end_position'                : '100%',
            '_radial_direction'            : 'center',
            '_background_image'            : '',
            '_background_image_size'       : 'cover',
            '_background_image_position'   : 'center',
            '_background_image_repeat'     : 'no_repeat',
            '_position_horizontal'         : '0%',
            '_position_vertical'           : '0%',
            '_size_width'                  : '50%',
            '_size_height'                 : '50%'
        };
        this.settings_hover = {};
    }

    set_important_text() {
        if ( this.options['important'] ) {
            this.imortant_text = '!important';
        }
    }

    set_style() {
        this.set_important_text();
        this.set_settings_values();
        var background_size = this.process_values(this.settings['_background_image_size']) !== '' ? this.process_values(this.settings['_background_image_size']) : 'center';
        var backgroun_position = this.process_values(this.settings['_background_image_position']);
        this.separator = this.settings['_background_image'] !== '' && this.settings['_use_gradient'] === 'on' ? ',' : '';

        if (this.process_values(this.settings['_background_image_position']) === 'custom') {
            backgroun_position = this.settings['_position_horizontal'] + ' ' + this.settings['_position_vertical'];
        }
        if (this.process_values(this.settings['_background_image_size']) === 'custom') {
            background_size = this.settings['_size_width'] + ' ' + this.settings['_size_height'];
        }

        // background color
        if ( this.settings['_bgcolor'] !== '' ) {
            this.additionalCss.push([{
                selector:    this.options['selector'],
                declaration: `background-color: ${this.settings['_bgcolor']} ${this.imortant_text};`,
            }]);
        }

        // backgroun iamge and gradient
        if( this.settings['_use_gradient'] === 'on' || this.settings['_background_image'] !== '' ) {
            this.additionalCss.push([{
                selector: this.options['selector'],
                declaration: `background-image: ${this.dg_background()};
                background-size: ${background_size} ${this.imortant_text};
                background-position: ${backgroun_position} ${this.imortant_text};
                background-repeat: ${this.process_values(this.settings['_background_image_repeat'])} ${this.imortant_text};`,
            }]);
        }

        // set hover styles
        this.set_hover_settings_value();
        this.set_hover_style();
    }

    set_hover_style() {
        var background_size = this.process_values(this.settings_hover['_background_image_size']) !== '' ? this.process_values(this.settings_hover['_background_image_size']) : 'center';
        const hover = this.options['hover'] !== '' ? this.options['hover'] : this.options['selector'];
        var backgroun_position = this.process_values(this.settings_hover['_background_image_position']);

        if (this.process_values(this.settings_hover['_background_image_position']) === 'custom') {
            backgroun_position = this.settings_hover['_position_horizontal'] + ' ' + this.settings_hover['_position_vertical'];
        }
        if (this.process_values(this.settings_hover['_background_image_size']) === 'custom') {
            background_size = this.settings_hover['_size_width'] + ' ' + this.settings_hover['_size_height'];
        }
        // background color hover
        if ( this.props['hover_enabled'] && this.props['hover_enabled'] === 1) {
            if ( this.settings_hover['_bgcolor'] !== '' ) {
                this.additionalCss.push([{
                    selector:    hover,
                    declaration: `background-color: ${this.settings_hover['_bgcolor']} ${this.imortant_text};`,
                }]);
            }
            // gradient and background-image
            if( this.settings['_use_gradient'] === 'on' || this.settings['_background_image'] !== '' ) {
                this.additionalCss.push([{
                    selector: hover,
                    declaration: `background-image: ${this.dg_background('hover')};
                    background-size: ${background_size} ${this.imortant_text};
                    background-position: ${backgroun_position} ${this.imortant_text};
                    background-repeat: ${this.process_values(this.settings_hover['_background_image_repeat'])} ${this.imortant_text};`,
                }]);
            }
        }

    }

    set_settings_values() {
        Object.keys(this.settings).forEach(element => {
            if (this.props[this.slug + element]) {
                this.settings[element] = this.props[this.slug + element]
            }
        })
    }

    set_hover_settings_value() {
        Object.keys(this.settings).forEach(element => {
            if (this.props[this.slug + element + '__hover']) {
                this.settings_hover[element] = this.props[this.slug + element + '__hover']
            } else {
                this.settings_hover[element] = this.settings[element]
            }
        })
    }

    dg_background(type = 'default') {
        return this.settings['_above_image'] === 'on' ?
            `${this.dg_background_gradient(type)}${this.separator} ${this.dg_background_image(type)} ${this.imortant_text}` :
            `${this.dg_background_image(type)}${this.separator} ${this.dg_background_gradient(type)} ${this.imortant_text}`;
    }

    dg_background_image(type) {
        const settings = type === 'default' ? this.settings : this.settings_hover;

        if ( settings['_background_image'] !== '') {
            return `url(${settings['_background_image']})`;
        } else {
            return '';
        }
    }

    dg_background_gradient(type) {
        const settings = type === 'default' ? this.settings : this.settings_hover;

        if (settings['_use_gradient'] === 'on') {
            if ( settings['_gradient_type'] !== 'radial' ) {
                return `linear-gradient( ${settings['_gradient_direction']}, ${settings['_color_gradient_1']} ${settings['_start_position']}, 
                 ${settings['_color_gradient_2']} ${settings['_end_position']} )`;
            } else {
                return `radial-gradient( circle at ${this.process_values(settings['_radial_direction'])}, 
                ${settings['_color_gradient_1']} ${settings['_start_position']}, 
                ${settings['_color_gradient_2']} ${settings['_end_position']} )`;
            }
        } else {
            return '';
        }
    }

    // process values
    process_values (value) {
        const list = {
            'center'        : 'center',
            'top_left'      : 'top left',
            'top_center'    : 'top center',
            'center_top'    : 'center top',
            'top'           : 'top',
            'top_right'     : 'top right',
            'right'         : 'right',
            'center_right'  : 'center right',
            'bottom_right'  : 'bottom right',
            'bottom'        : 'bottom',
            'bottom_center' : 'bottom center',
            'bottom_left'   : 'bottom left',
            'left'          : 'left',
            'center_left'   : 'center left',
            'no_repeat'     : 'no-repeat',
            'repeat'        : 'repeat',
            'repeat_x'      : 'repeat-x',
            'repeat_y'      : 'repeat-y',
            'space'         : 'space',
            'round'         : 'round',
            'cover'         : 'cover',
            'fit'           : 'contain',
            'actual_size'   : 'initial',
            'custom'        : 'custom'
        };
        return list[value];
    }

}

export default process_background;