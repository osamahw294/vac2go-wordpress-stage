import process_background from './dg_process_bg';
import process_new_background from './dg_new_background';
const utility = {
    active_child: {},
    extend: function (defaults, options) {
        var extended = {};
        var prop;
        for (prop in defaults) {
            if (Object.prototype.hasOwnProperty.call(defaults, prop)) {
                extended[prop] = defaults[prop];
            }
        }
        for (prop in options) {
            if (Object.prototype.hasOwnProperty.call(options, prop)) {
                extended[prop] = options[prop];
            }
        }
        return extended;
    },
    process_icon_font_style: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': ''
        };
        const settings = this.extend(defaults, options);
        var {props, key, additionalCss, selector} = settings;

        if (!props[key]) return;

        const utils = window.ET_Builder.API.Utils;
        if (!utils.processIconFontData) return;
        const iconData = utils.processIconFontData(props[key]);

        if (!iconData) return;

        if (iconData.iconFontFamily !== "ETmodules") {
            additionalCss.push([{
                selector: selector,
                declaration: `font-family: ${iconData.iconFontFamily} !important;`,
            }]);
        }
        additionalCss.push([{
            selector: selector,
            declaration: `font-weight: ${iconData.iconFontWeight} !important;`,
        }]);
    },
    process_margin_padding: function (options = {}) {
        // props, key, additionalCss, eleSelector, attr
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'type': '',
            'important': true
        };
        const settings = this.extend(defaults, options);
        var {props, key, additionalCss, selector, type, important} = settings;
        var imText = important ? '!important' : '';

        const desktop = props[key];
        const tablet = props[key + '_tablet'];
        const mobile = props[key + '_phone'];

        if (desktop && '' !== desktop) {
            const desktopValue = desktop.split('|');
            additionalCss.push([{
                selector: selector,
                declaration: `${type}-top: ${desktopValue[0]}${imText};
                ${type}-right: ${desktopValue[1]}${imText};
                ${type}-bottom: ${desktopValue[2]}${imText};
                ${type}-left: ${desktopValue[3]}${imText};`,
            }]);
        }
        if (tablet && '' !== tablet) {
            const tabletValue = tablet.split('|');
            additionalCss.push([{
                selector: selector,
                declaration: `${type}-top: ${tabletValue[0]}${imText};
                ${type}-right: ${tabletValue[1]}${imText};
                ${type}-bottom: ${tabletValue[2]}${imText};
                ${type}-left: ${tabletValue[3]}${imText};`,
                'device': 'tablet',
            }]);
        }
        if (mobile && '' !== mobile) {
            const mobileValue = mobile.split('|');
            additionalCss.push([{
                selector: selector,
                declaration: `${type}-top: ${mobileValue[0]}${imText};
                ${type}-right: ${mobileValue[1]}${imText};
                ${type}-bottom: ${mobileValue[2]}${imText};
                ${type}-left: ${mobileValue[3]}${imText};`,
                'device': 'phone'
            }]);
        }
        if (props[key + '__hover_enabled']) {
            if (props['hover_enabled'] && props['hover_enabled'] === 1) {
                if (props[key + '__hover']) {
                    const hover = props[key + '__hover'];
                    const hoverValue = hover.split('|');
                    additionalCss.push([{
                        selector: selector,
                        declaration: `${type}-top: ${hoverValue[0]}${imText};
                        ${type}-right: ${hoverValue[1]}${imText};
                        ${type}-bottom: ${hoverValue[2]}${imText};
                        ${type}-left: ${hoverValue[3]}${imText};`,
                    }]);
                }
            }
        }
    },

    dg_get_div_value: function (arg) {
        const value = parseInt(arg) / 2;
        const unit = arg.replace(parseInt(arg), "")
        return value + unit;
    },

    dg_process_transition: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'properties': []
        };
        const settings = this.extend(defaults, options);
        var {props, key, additionalCss, selector, properties} = settings;

        const transition_fn = this.dg_transition(props[key + '_transition_curve']);
        const transition_delay = props[key + '_transition_delay'];
        const transition_duration = props[key + '_transition_duration'];
        let transition = '';

        for (let i = 0; i < properties.length; i++) {
            const s = (i + 1) !== properties.length ? ',' : '';
            const t = `${properties[i]} ${transition_duration} ${transition_fn} ${transition_delay} ${s}`;
            transition += t;
        }

        additionalCss.push([{
            selector: selector,
            declaration: `transition: ${transition};`,
        }]);

    },

    dg_transition: function (arg) {
        const transition = {
            'ease': 'ease',
            'ease_in': 'ease-in',
            'ease_in_out': 'ease-in-out',
            'ease_out': 'ease-out',
            'linear': 'linear',
            'bounce': 'cubic-bezier(.2,.85,.4,1.275)'
        }
        return transition[arg];
    },

    dg_process_text_clip: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
        };
        const settings = this.extend(defaults, options);
        var {props, key, additionalCss, selector, alignment} = settings;
        if (props[key + '_enable_clip'] === 'on') {
            this.process_color({
                'props': props,
                'key': key + '_fill_color',
                'additionalCss': additionalCss,
                'selector': selector,
                'type': '-webkit-text-fill-color',
                'important': false,
            });
            this.process_color({
                'props': props,
                'key': key + '_stroke_color',
                'additionalCss': additionalCss,
                'selector': selector,
                'type': '-webkit-text-stroke-color',
                'important': false,
            });
            this.apply_single_value({
                'props': props,
                'key': key + '_stroke_width',
                'additionalCss': additionalCss,
                'selector': selector,
                'type': '-webkit-text-stroke-width',
                'unit': 'px',
                'default_value': '1'
            });
            if (props[key + '_enable_bg_clip'] === 'on') {
                additionalCss.push([{
                    selector: selector,
                    declaration: `-webkit-background-clip: text;`,
                }]);
            }
        }
    },

    dg_process_string_attr: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'type': '',
            'default_value': ''
        };
        const settings = this.extend(defaults, options);
        var {props, key, additionalCss, selector, type, default_value} = settings;

        let desktop = props[key] ?
            this.process_values(props[key]) : default_value;
        let tablet = props[key + '_tablet'] ?
            this.process_values(props[key + '_tablet']) : desktop;
        let mobile = props[key + '_phone'] ?
            this.process_values(props[key + '_phone']) : tablet;

        if (desktop && '' !== desktop) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${desktop};`,
            }]);
        }
        if (tablet && '' !== tablet) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${tablet};`,
                'device': 'tablet',
            }]);
        }
        if (mobile && '' !== mobile) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${mobile};`,
                'device': 'phone'
            }]);
        }
    },

    dg_process_maxwidth: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'alignment': false,
            'selector': '',
        };
        const settings = this.extend(defaults, options);
        var {props, key, additionalCss, selector, alignment} = settings;
        const max_width = key + '_maxwidth';
        const desktop = props[max_width];
        const tablet = this.dg_check_values(desktop, props[max_width + '_tablet'])
        const phone = this.dg_check_values(desktop, props[max_width + '_phone'])

        additionalCss.push([{
            selector: selector,
            declaration: `max-width: ${desktop};`,
        }]);
        additionalCss.push([{
            selector: selector,
            declaration: `max-width: ${tablet};`,
            'device': 'tablet',
        }]);
        additionalCss.push([{
            selector: selector,
            declaration: `max-width: ${phone};`,
            'device': 'phone'
        }]);

        if (alignment === true) {
            const align = key + '_alignment';
            const desktop_align = props[align];
            const tablet = this.dg_check_values(desktop_align, props[align + '_tablet']);
            const phone = this.dg_check_values(desktop_align, props[align + '_phone']);

            additionalCss.push([{
                selector: selector,
                declaration: `${this.dg_block_align(desktop_align)};`,
            }]);
            additionalCss.push([{
                selector: selector,
                declaration: `${this.dg_block_align(tablet)};`,
                'device': 'tablet',
            }]);
            additionalCss.push([{
                selector: selector,
                declaration: `${this.dg_block_align(phone)};`,
                'device': 'phone'
            }]);
        }
    },

    dg_block_align: function (align) {
        if (align === 'center') {
            return 'margin-left: auto; margin-right: auto;';
        } else if (align === 'right') {
            return 'margin-left: auto; margin-right: 0;';
        } else if (align === 'left') {
            return 'margin-right: auto; margin-left: 0;';
        }
    },

    dg_check_values: function (desktop, other) {
        return other && '' !== other ? other : desktop;
    },

    process_icon_styles: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'image_selector': '',
            'align_container': ''
        };
        const settings = this.extend(defaults, options);
        var {props, key, additionalCss, selector, align_container, image_selector} = settings;

        if (props[key + '_use_icon'] === 'on') {
            this.process_color({
                'props': props,
                'additionalCss': additionalCss,
                'key': key + '_icon_color',
                'selector': selector,
                'type': 'color'
            });
            this.apply_single_value({
                'props': props,
                'key': key + '_icon_size',
                'additionalCss': additionalCss,
                'selector': selector,
                'type': 'font-size',
                'unit': 'px',
                'default_value': '48'
            });
        }

        // icon alignment
        if (props[key + '_icon_align'] && props[key + '_use_icon'] === 'on') {
            const icon_align = props[key + '_icon_align'] !== '' ? props[key + '_icon_align'] : 'left';
            align_container = align_container !== '' ? align_container : selector;
            additionalCss.push([{
                selector: align_container,
                declaration: `text-align: ${icon_align};`,
            }]);
        }
        // image styles
        if (props[key + '_image'] && props[key + '_use_icon'] !== 'on' && props[key + '_image'] !== '') {
            const image_align = props[key + '_image_align'] !== '' ? props[key + '_image_align'] : 'left';
            align_container = align_container !== '' ? align_container : image_selector;
            additionalCss.push([{
                selector: align_container,
                declaration: `text-align: ${image_align};`,
            }]);
            if (props[key + '_full_width'] && props[key + '_full_width'] === 'on') {
                additionalCss.push([{
                    selector: image_selector,
                    declaration: `width: 100%;`,
                }]);
            }
            if (props[key + '_max_width'] && props[key + '_max_width'] !== '' && props[key + '_full_width'] !== 'on') {
                this.process_range_value({
                    'props': props,
                    'key': key + '_max_width',
                    'additionalCss': additionalCss,
                    'selector': image_selector,
                    'type': 'max-width',
                    'unit': '%',
                    'default_value': '100'
                })
            }
        }
        // icon background
        if (props[key + '_icon_bg'] && props[key + '_icon_bg'] !== '' && props[key + '_use_icon'] === 'on') {
            this.process_color({
                'props': props,
                'additionalCss': additionalCss,
                'key': key + '_icon_bg',
                'selector': selector,
                'type': 'background-color'
            });
        }
        // circle icon
        if (props[key + '_circle_icon'] && props[key + '_circle_icon'] === 'on' && props[key + '_use_icon'] === 'on') {
            additionalCss.push([{
                selector: selector,
                declaration: `border-radius: 50%;`,
            }]);
        }
    },

    // old version 
    apply_single_value: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'type': '',
            'unit': '%',
            'default_value': '',
            'decrease': false,
            'addition': true
        };
        const settings = this.extend(defaults, options);
        var {
            props, key, additionalCss, selector, type,
            unit, default_value, decrease, addition
        } = settings;

        // if ( !props[key] ) {
        //     return
        // }
        const unit_value = props[key] && props[key].replace(parseInt(props[key]), "") !== '' ? props[key].replace(parseInt(props[key]), "") : unit;
        const unit_value_tab = props[key + '_tablet'] ? props[key + '_tablet'].replace(parseInt(props[key + '_tablet']), "") : unit_value;
        const unit_value_ph = props[key + '_phone'] ? props[key + '_phone'].replace(parseInt(props[key + '_phone']), "") : unit_value_tab;

        let itemValue = !props[key] && default_value ? default_value : parseInt(props[key]);
        let desktop = decrease === false ? itemValue : 100 - itemValue;
        let tablet = decrease === false ?
            parseInt(props[key + '_tablet']) : 100 - parseInt(props[key + '_tablet']);
        let mobile = decrease === false ?
            parseInt(props[key + '_phone']) : 100 - parseInt(props[key + '_phone']);
        const negative = addition === false ? '-' : '';

        desktop = negative + desktop + unit_value;
        tablet = negative + tablet + unit_value_tab;
        mobile = negative + mobile + unit_value_ph;
        // desktop = negative + desktop + unit;
        // tablet = negative + tablet + unit;
        // mobile = negative + mobile + unit;
        if (desktop && '' !== desktop) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${desktop};`,
            }]);
        }
        if (tablet && '' !== tablet) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${tablet};`,
                'device': 'tablet',
            }]);
        }
        if (mobile && '' !== mobile) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${mobile};`,
                'device': 'phone'
            }]);
        }
        if (props[key + '__hover_enabled']) {
            if (props['hover_enabled'] && props['hover_enabled'] === 1) {
                if (props[key + '__hover']) {
                    const hover = props[key + '__hover'];
                    additionalCss.push([{
                        selector: selector,
                        declaration: `${type}: ${hover}!important;`,
                    }]);
                }
            }
        }
    },

    process_range_value: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'type': '',
            'unit': '',
            'default_value': '',
            'important': false,
            'negative': false,
            'fixed_unit': ''
        };
        const settings = this.extend(defaults, options);
        var {
            props, key, additionalCss, selector, type,
            unit, default_value, important, negative, fixed_unit
        } = settings;

        var desktop = props[key] && props[key] !== '' ? props[key] : default_value;
        var tablet = props[key + '_tablet'] && props[key + '_tablet'] !== '' ? props[key + '_tablet'] : desktop;
        var phone = props[key + '_phone'] && props[key + '_phone'] !== '' ? props[key + '_phone'] : tablet;

        if (fixed_unit !== '') {
            desktop = parseInt(desktop) + fixed_unit;
            tablet = parseInt(tablet) + fixed_unit;
            phone = parseInt(phone) + fixed_unit;
        }

        const _important = important === true ? '!important' : '';
        const ng_value = negative === true ? '-' : '';

        if (desktop && '' !== desktop) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${ng_value}${desktop}${_important};`,
            }]);
        }
        if (tablet && '' !== tablet) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${ng_value}${tablet}${_important};`,
                'device': 'tablet',
            }]);
        }
        if (phone && '' !== phone) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${ng_value}${phone}${_important};`,
                'device': 'phone'
            }]);
        }

        if (props[key + '__hover_enabled']) {
            if (props['hover_enabled'] && props['hover_enabled'] === 1) {
                if (props[key + '__hover']) {
                    const hover = props[key + '__hover'];
                    additionalCss.push([{
                        selector: selector,
                        declaration: `${type}: ${ng_value}${hover}${_important};`,
                    }]);
                }
            }
        }
    },

    dg_process_oposite_value: function (value, ops = false) {
        if (ops) {
            if (parseInt(value) >= 0) {
                value = '-' + value;
            } else {
                value = value.substring(1);
            }
        }
        return value;
    },

    process_transform_props: function (options = {}) {
        // transform: key, type, unit, default_value, default_unit
        // utility.process_transform_props({
        //     'props'             : props,
        //     'additionalCss'     : additionalCss,
        //     'selector'          : '%%order_class%% .dg_im_container',
        //     'transforms'        : [
        //         {
        //             'type' : 'rotate',
        //             'key'  : 'mask_rotate',
        //             'unit' : 'px'
        //         }
        //     ]
        // });
        const defaults = {
            'props': {},
            'additionalCss': '',
            'selector': '',
            'oposite': false,
            'transforms': []
        };
        const settings = this.extend(defaults, options);
        var {props, additionalCss, selector, transforms, oposite} = settings;

        let transform_desktop = '';
        let transform_tablet = '';
        let transform_phone = '';
        let transform_hover = '';

        transforms.forEach(ele => {
            const desktop = props[ele.key] ?
                this.dg_process_oposite_value(props[ele.key], oposite) : ele.default_value;
            const tablet = props[ele.key + '_tablet'] ?
                this.dg_process_oposite_value(props[ele.key + '_tablet'], oposite) : desktop;
            const phone = props[ele.key + '_phone'] ?
                this.dg_process_oposite_value(props[ele.key + '_phone'], oposite) : tablet;
            const hover = props[ele.key + '__hover'] ?
                this.dg_process_oposite_value(props[ele.key + '__hover'], oposite) : desktop;

            transform_desktop = `${transform_desktop} ${ele.type}(${desktop})`;
            transform_tablet = `${transform_tablet} ${ele.type}(${tablet})`;
            transform_phone = `${transform_phone} ${ele.type}(${phone})`;
            transform_hover = `${transform_hover} ${ele.type}(${hover})`;
        })

        if (transform_desktop && '' !== transform_desktop) {
            additionalCss.push([{
                selector: selector,
                declaration: `transform: ${transform_desktop};`,
            }]);
        }

        if (transform_tablet && '' !== transform_tablet) {
            additionalCss.push([{
                selector: selector,
                declaration: `transform: ${transform_tablet};`,
                'device': 'tablet',
            }]);
        }

        if (transform_phone && '' !== transform_phone) {
            additionalCss.push([{
                selector: selector,
                declaration: `transform: ${transform_phone};`,
                'device': 'phone'
            }]);
        }

        if (props['hover_enabled'] && props['hover_enabled'] === 1) {
            additionalCss.push([{
                selector: selector,
                declaration: `transform: ${transform_hover}!important;`,
            }]);
        }

    },

    process_filter_props: function (options = {}) {
        // filters: key, type, unit, default_value, default_unit
        const defaults = {
            'props': {},
            'additionalCss': '',
            'selector': '',
            'filters': []
        };
        const settings = this.extend(defaults, options);
        var {props, additionalCss, selector, filters} = settings;

        let filter_desktop = '';
        let filter_tablet = '';
        let filter_phone = '';
        let filter_hover = '';

        filters.forEach(ele => {
            const desktop = props[ele.key] ? props[ele.key] : ele.default_value;
            const tablet = props[ele.key + '_tablet'] ? props[ele.key + '_tablet'] : desktop;
            const phone = props[ele.key + '_phone'] ? props[ele.key + '_phone'] : tablet;
            const hover = props[ele.key + '__hover'] ? props[ele.key + '__hover'] : desktop;

            filter_desktop = `${filter_desktop} ${ele.type}(${desktop})`;
            filter_tablet = `${filter_tablet} ${ele.type}(${tablet})`;
            filter_phone = `${filter_phone} ${ele.type}(${phone})`;
            filter_hover = `${filter_hover} ${ele.type}(${hover})`;
        })

        if (filter_desktop && '' !== filter_desktop) {
            additionalCss.push([{
                selector: selector,
                declaration: `filter: ${filter_desktop};`,
            }]);
        }

        if (filter_tablet && '' !== filter_tablet) {
            additionalCss.push([{
                selector: selector,
                declaration: `filter: ${filter_tablet};`,
                'device': 'tablet',
            }]);
        }

        if (filter_phone && '' !== filter_phone) {
            additionalCss.push([{
                selector: selector,
                declaration: `filter: ${filter_phone};`,
                'device': 'phone'
            }]);
        }

        if (props['hover_enabled'] && props['hover_enabled'] === 1) {
            additionalCss.push([{
                selector: selector,
                declaration: `filter: ${filter_hover}!important;`,
            }]);
        }

    },

    process_color: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'type': '',
            'important': false,
        };
        const settings = this.extend(defaults, options);
        var {
            props, key, additionalCss, selector, type,
            important
        } = settings;
        const slug = props[key];
        const slugHover = props[key + '__hover'];
        const importantText = true === important ? '!important' : '';
        if ('' !== slug) {
            additionalCss.push([{
                selector: selector,
                declaration: `${type}: ${slug + importantText};`,
            }]);
        }

        if (props[key + '__hover_enabled']) {
            if (props['hover_enabled'] && props['hover_enabled'] === 1) {
                if (props[key + '__hover']) {
                    additionalCss.push([{
                        selector: selector,
                        declaration: `${type}: ${slugHover + importantText};`,
                    }]);
                }
            }
        }
    },

    process_header_level: function (content, header_level, className = '') {
        const classSelector = className ? `class="${className}"` : '';
        const heading = `<${header_level} ${classSelector}>${content}</${header_level}>`;
        return {__html: heading};
    },

    background_image_options: function (key, hover = false) {
        const options = [
            'bg_enable_image',
            'bg_image',
            'bg_position',
            'bg_repeat',
            'bg_size',
            'bg_blend'
        ];
        var value = {};
        options.map(function (ele) {
            if (hover === true) {
                value[ele] = key + '_' + ele + '__hover';
            } else {
                value[ele] = key + '_' + ele;
            }
        })
        return value;
    },

    background_position_values: function (key) {
        return key ? key.replace('_', ' ') : key;
    },

    process_bg_props: function (props, options = {}, important) {
        return `background-image: url(${props[options.bg_image]}) ${important};
            background-size: ${props[options.bg_size]} ${important};
            background-position: ${this.background_position_values(props[options.bg_position])} ${important};
            background-repeat: ${props[options.bg_repeat]} ${important};
            background-blend-mode: ${props[options.bg_blend]} ${important};`;
    },

    fix_background_image: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'important': false,
        }
        const settings = this.extend(defaults, options);
        const {
            props, key, additionalCss, selector,
            important
        } = settings;

        const bg_options = this.background_image_options(key);
        const bg_options_hover = this.background_image_options(key, true);
        const importantText = true === important ? '!important' : '';

        if ('on' === props[bg_options.bg_enable_image]) {
            additionalCss.push([{
                selector: selector,
                declaration: this.process_bg_props(
                    props, bg_options, importantText),
            }]);
        }
        if (props['hover_enabled'] && props['hover_enabled'] === 1) {
            additionalCss.push([{
                selector: selector,
                declaration: this.process_bg_props(
                    props, bg_options_hover, importantText),
            }]);
        }
    },

    /**
     * Process button styles
     *
     * @param {*} options
     */
    dg_process_btn_styles: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'align_container': ''
        };
        const settings = this.extend(defaults, options);
        var {props, key, additionalCss, selector, align_container} = settings;

        // button alignment
        if (props[key + '_button_align']) {
            const btn_align = props[key + '_button_align'] !== '' ? props[key + '_button_align'] : 'left';
            align_container = align_container !== '' ? align_container : selector;
            additionalCss.push([{
                selector: align_container,
                declaration: `text-align: ${btn_align};`,
            }]);
        }
        // button full width
        if (props[key + '_button_fullwidth'] && props[key + '_button_fullwidth'] === 'on') {
            additionalCss.push([{
                selector: selector,
                declaration: `width: 100%;`,
            }]);
        }
    },

    /**
     * Process background styles
     *
     * @param {*} options
     */
    dg_process_bg: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'hover': '',
            'important': false,
        }
        const settings = this.extend(defaults, options);

        const background = new process_background(settings);
        background.set_style();
    },

    /**
     * Process background styles
     *
     * @deprecated
     *
     * @param {*} options
     *
     * @return void
     */
    dg_process_background: function (options = {}) {
        const defaults = {
            'props': {},
            'key': '',
            'additionalCss': '',
            'selector': '',
            'important': false,
        }
        const settings = this.extend(defaults, options);
        const {
            props, key, additionalCss, selector,
            important
        } = settings;
        const _important = true === important ? '!important' : '';

        let gradient = '';
        const image = props[key + '_background_image'] ? props[key + '_background_image'] : '';
        let background_image = '';

        if (image !== '') {
            background_image = `url(${image})`;
        }
        if (props[key + '_use_gradient'] === 'on') {
            const color_1 = props[key + '_color_gradient_1'] ?
                props[key + '_color_gradient_1'] : '#2b87da';
            const color_2 = props[key + '_color_gradient_2'] ?
                props[key + '_color_gradient_2'] : '#29c4a9';
            const linear_direction = props[key + '_gradient_direction'] ?
                props[key + '_gradient_direction'] : '180deg';
            const start_position = props[key + '_start_position'] ?
                props[key + '_start_position'] : '0%';
            const end_position = props[key + '_end_position'] ?
                props[key + '_end_position'] : '100%';
            const radial_direction = props[key + '_radial_direction'] ?
                props[key + '_radial_direction'] : 'center';

            if (props[key + '_gradient_type'] !== 'radial') {
                gradient = `linear-gradient(
                    ${linear_direction},
                    ${color_1} ${start_position},
                    ${color_2} ${end_position}
                  )`;
            } else {
                gradient = `radial-gradient(
                    circle at ${this.process_values(radial_direction)},
                    ${color_1} ${start_position},
                    ${color_2} ${end_position})`;
            }
        }
        const background_above_image = props[key + '_above_image'];

        // background color
        if (props[key + '_bgcolor']) {
            additionalCss.push([{
                selector: selector,
                declaration: `background-color: ${props[key + '_bgcolor']} ${_important}`,
            }]);
        }
        // background image
        if (background_image !== '' || gradient !== '') {
            const seperator = background_image !== '' && gradient !== '' ? ',' : '';
            additionalCss.push([{
                selector: selector,
                declaration: (background_above_image === 'on') ?
                    `background-image: ${gradient}${seperator} ${background_image} ${_important}` :
                    `background-image: ${background_image}${seperator} ${gradient} ${_important}`,
            }]);
        }
        if (background_image !== '') {
            const background_size = props[key + '_background_image_size'] ?
                props[key + '_background_image_size'] : 'cover';
            const background_position = props[key + '_background_image_position'] ?
                props[key + '_background_image_position'] : 'center';
            const background_repeat = props[key + '_background_image_repeat'] ?
                props[key + '_background_image_repeat'] : 'no_repeat';


            var bg_size = this.process_values(background_size);

            additionalCss.push([{
                selector: selector,
                declaration: `background-size: ${bg_size}; 
                    background-position: ${this.process_values(background_position)}; 
                    background-repeat: ${this.process_values(background_repeat)};`,
            }]);
        }
        // hover styles
        if (props['hover_enabled'] && props['hover_enabled'] === 1) {
            const background_color_on_hover = this.process_keys(props, key, '_bgcolor__hover', '');
            const background_size_on_hover = this.process_keys(props, key, '_background_image_size__hover', '');
            const background_position_hover = this.process_keys(props, key, '_background_image_position__hover', '');
            const background_repeat_hover = this.process_keys(props, key, '_background_image_repeat__hover', '');
            if (background_color_on_hover !== null) {
                additionalCss.push([{
                    selector: selector,
                    declaration: `background-color: ${background_color_on_hover} ${_important}`,
                }]);
            }
            if (background_size_on_hover !== null) {
                additionalCss.push([{
                    selector: selector,
                    declaration: `background-size: ${background_size_on_hover} ${_important}`,
                }]);
            }
            if (background_position_hover !== null) {
                additionalCss.push([{
                    selector: selector,
                    declaration: `background-position: ${background_position_hover} ${_important}`,
                }]);
            }
            if (background_repeat_hover !== null) {
                additionalCss.push([{
                    selector: selector,
                    declaration: `background-repeat: ${background_repeat_hover} ${_important}`,
                }]);
            }

        }
    },
    // fix builder css selector issue
    // remove ".et-db #et-boc .et-l" selectors from VB
    dg_fix_builder_css_issue: function (wrapper, styleContainer) {
        const _styles = styleContainer.querySelectorAll('.et-fb-custom-css-output');

        if (_styles.length !== 0) {
            _styles.forEach(ele => {
                var new_style = ele.innerHTML.replace(/.et-db/g, "");
                new_style = new_style.replace(/#et-boc/g, "");
                new_style = new_style.replace(/.et-l/g, "");

                ele.innerHTML = new_style;
            })
        }
    },
    // process keys
    process_keys: function (props, key, suffix, defaultValue = null) {
        let value = null;
        if (props[key + suffix]) {
            value = props[key + suffix];
        } else {
            value = defaultValue;
        }
        return value;
    },

    // process values
    process_values: function (value) {
        const list = {
            'center': 'center',
            'top_left': 'top left',
            'top_center': 'top center',
            'center_top': 'center top',
            'top': 'top',
            'top_right': 'top right',
            'right': 'right',
            'center_right': 'center right',
            'bottom_right': 'bottom right',
            'bottom': 'bottom',
            'bottom_center': 'bottom center',
            'bottom_left': 'bottom left',
            'left': 'left',
            'center_left': 'center left',
            'no_repeat': 'no-repeat',
            'repeat': 'repeat',
            'repeat_x': 'repeat-x',
            'repeat_y': 'repeat-y',
            'space': 'space',
            'round': 'round',
            'cover': 'cover',
            'fit': 'contain',
            'actual_size': 'initial',
            'flex_left': 'row',
            'flex_top': 'column',
            'flex_right': 'row-reverse',
            'flex_bottom': 'column-reverse',
            'flex_start': 'flex-start',
            'flex_end': 'flex-end',
            'flex_center': 'center'
        };
        return list.hasOwnProperty(value) ? list[value] : value;
    },

    process_flex_values: function (value) {
        const list = {
            'flex_left': 'raw',
            'flex_top': 'column',
            'flex_right': 'raw-reverse',
            'flex_bottom': 'column-reverse',

        };
        return list.hasOwnProperty(value) ? list[value] : value;
    },

    _renderDynamicContent: function (propValue, key, textContent = true) {
        const field = propValue.dynamic[key];
        if (key === 'content') {
            return field.render('full');
        }
        let fieldContent = textContent ? field.render() : field;

        if (field.loading) {
            // Let Divi render the loading placeholder.
            return textContent ? fieldContent : fieldContent.render();
        }
        return textContent ? fieldContent : fieldContent.value;
    },


    /* Custom functions for icon list module */
    /**
     * Parsed field value and props, determine wheter the passed string means it has responsive value or not
     * *_tablet, *_phone holds two values (responsive status and last opened tabs) in the following format:
     * status|last_opened_tab
     *
     * @since 1.0.0
     *
     * @param {string} field Field name for current module
     * @param {Object} props All props data for current module
     *
     * @return {string[]} Responsive values for all devices
     */
    dg_collect_dynamic_content: function (field, props) {

        // Dynamic content for hover mode
        if ((props['hover_enabled'] && typeof props['hover_enabled'] === 'number')
            && props[`${field}__hover_enabled`] === "on|hover") {
            // dynamic hover content
            if (!!props['dynamic'][field]) {
                if (!!props['dynamic'][`${field}__hover`] && props['dynamic'][`${field}__hover`].hasValue) {
                    return props['dynamic'][`${field}__hover`];
                } else {
                    return props['dynamic'][field];
                }
            } else {
                if (!!props[`${field}__hover`]) {
                    return props[`${field}__hover`];
                } else {
                    return props[field];
                }
            }
        }

        // Dynamic content for tablet mode
        if (window.ET_Builder.API.State.View_Mode.isTablet()) {
            if (!!props['dynamic'][field]) {
                if (!!props['dynamic'][`${field}_tablet`] && props['dynamic'][`${field}_tablet`].hasValue) {
                    return props['dynamic'][`${field}_tablet`];
                } else {
                    return props['dynamic'][field];
                }
            } else {
                if (!!props[`${field}_tablet`]) {
                    return props[`${field}_tablet`];
                } else {
                    return props[field];
                }
            }
        }

        // Dynamic content for phone mode
        if (window.ET_Builder.API.State.View_Mode.isPhone()) {
            if (!!props['dynamic'][field]) {
                if (!!props['dynamic'][`${field}_phone`] && props['dynamic'][`${field}_phone`].hasValue) {
                    return props['dynamic'][`${field}_phone`];
                } else if (!!props['dynamic'][`${field}_tablet`] && props['dynamic'][`${field}_tablet`].hasValue) {
                    return props['dynamic'][`${field}_tablet`];
                } else {
                    return props['dynamic'][field];
                }
            } else {
                if (!!props[`${field}_phone`]) {
                    return props[`${field}_phone`];
                } else if (!!props[`${field}_tablet`]) {
                    return props[`${field}_tablet`];
                } else {
                    return props[field];
                }
            }
        }

        // Dynamic content for desktop mode
        return !!props['dynamic'] && !!props['dynamic'][field] ? props['dynamic'][field] : props[field];
    },

    /**
     * Render button text with dynamic text
     *
     * @since 1.2.4
     *
     * @param {*} fieldValue Dynamic value of current field.
     * @param {*} callback Callback function for render field value.
     * @param {string|null} type Render full content
     *
     * @return {*} Render button text
     */
    dg_render_dynamic_content: function (fieldValue, callback =null, type = null) {
        // Get rendered component or Let Divi handle all the rendering.
        let component = (type === 'full') ? fieldValue.render('full') : fieldValue.render();

        // Show preloader for title text
        if (fieldValue.loading) {
            return component;
        }

        // Show rendered html for title text
        if (undefined !== component && fieldValue.hasValue) {
            if(null !==callback){
                return callback(component);
            }else{
                return component;
            }
        }

        return null;
    },

    /**
     * Render image with dynamic
     *
     * @since 1.24
     *
     * @param {*} fieldValue Dynamic value of current field.
     * @param {*} callback Callback function for render field value.
     *
     * @return {*} Render button text
     */
    dg_render_dynamic_image: function (fieldValue, callback) {
        // Show preloader for title text
        if (fieldValue.loading) {
            return fieldValue.render();
        }

        // Show rendered html for title text
        if (fieldValue.hasValue) {
            return callback(fieldValue.value);
        }

        return null;
    },

    /**
     * Set actual position for icon or image in show on hover effect for current element with default, responsive and hover
     *
     * @since 1.2.4
     *
     * @param {Object} options All options which are provided by module
     *
     * @return void
     */
    dg_iconlist_show_icon_on_hover_styles: function (options = {}) {
        // default Unit for margin replacement
        options.defaults.unitValue = !!options.defaults.unitValue ? options.defaults.unitValue : 4;

        const lastEditedSuffix = 'last_edited';
        // const hoverEnabledSuffix = 'hover_enabled';

        let defaultWidth;
        let allowedUnits = ['%', 'em', 'rem', 'px', 'vh', 'vw'];
        let replacedUnits = {'%': '', 'em': '', 'rem': '', 'px': '', 'vh': '', 'vw': ''};

        const lastModifiedData = options.props[`${options.field}_${lastEditedSuffix}`];
        // const fieldHoveredData = options.props[`${options.field}__${hoverEnabledSuffix}`];

        // Determine image use or not.
        const is_image_icon_used = options.props[options.trigger] === 'off' || (options.props[options.trigger] === 'image' || options.props[options.trigger] === 'lottie');

        if (is_image_icon_used) {
            defaultWidth = !!options.props[options.dependsOn.image] ? options.props[options.dependsOn.image] : options.defaults.image;
        } else {
            defaultWidth = !!options.props[options.dependsOn.icon] ? options.props[options.dependsOn.icon] : options.defaults.icon;
        }

        let get_responsive_device = (lastModifiedData) => typeof lastModifiedData === 'string' ? lastModifiedData.split('|') : ['off', 'desktop'];
        let get_responsive_status = (lastModifiedData) => !!get_responsive_device(lastModifiedData)[0] ? get_responsive_device(lastModifiedData)[0] : 'off';

        // default and responsive styles
        if (!!lastModifiedData && get_responsive_status(lastModifiedData) === 'on') {
            // Responsive
            const device = get_responsive_device(lastModifiedData)[1];

            const currentDeviceData = options.props[`${options.field}_${device}`] ? options.props[`${options.field}_${device}`] : options.props[options.field];
            let dependsCurrentDeviceData = options.props[`${options.dependsOn.icon}_${device}`];
            const responsiveData = this.dg_iconlist_collect_prop_mapping_value(options, currentDeviceData);

            if (undefined === dependsCurrentDeviceData) {
                dependsCurrentDeviceData = defaultWidth;
            }

            const replacedData = Number.parseInt(this.dg_iconlist_replace_bulk_string(dependsCurrentDeviceData, allowedUnits, replacedUnits)) + options.defaults.unitValue;
            const actualResponsiveData = responsiveData.replace(/(#)/ig, replacedData)

            options.additionalCSS.push([{
                'selector': options.selector,
                'declaration': `${options.type}: ${actualResponsiveData};`,
                device, // desktop, tablet, phone
            }]);
        } else {
            const fieldDefaultData = !!options.props[options.field] ? options.props[options.field] : options.defaults.field;
            const collectedFieldDefaultData = this.dg_iconlist_collect_prop_mapping_value(options, fieldDefaultData);

            const replacedDefaultData = Number.parseInt(this.dg_iconlist_replace_bulk_string(defaultWidth, allowedUnits, replacedUnits)) + options.defaults.unitValue
            const actualDefaultData = collectedFieldDefaultData.replace(/(#)/ig, replacedDefaultData);

            // Default
            options.additionalCSS.push([{
                'selector': options.selector,
                'declaration': `${options.type}: ${actualDefaultData};`,
            }]);
        }

        // Set default style for show on hover effect
        options.additionalCSS.push([{'selector': options.selector, 'declaration': 'opacity: 0;',}]);
        options.additionalCSS.push([{
            'selector': !!options.hover ? options.hover : `${options.selector}:hover`,
            'declaration': 'opacity: 1;margin: 0 0 0 0 !important;',
        }]);
    },

    /**
     * Collect current field value when set container.
     *
     * @since 1.2.4
     *
     * @param {Object} options All options which are provided by module
     * @param {string} currentValue Current value of this field are provided by module
     *
     * @return {string} Field value
     */
    dg_iconlist_collect_prop_mapping_value: function (options, currentValue) {
        if (!!options['mappingValues'] && typeof options['mappingValues'] === 'object') {
            return options['mappingValues'][currentValue];
        }
        if (!!options['mappingValues'] && typeof options['mappingValues'] === 'function') {
            return options['mappingValues'](currentValue);
        } else {
            return currentValue;
        }
    },

    /**
     * Determine icon font weight
     *
     * @since 1.2.4
     *
     * @param {string} string Icon font value
     * @param {Array} queryItems Icon font value
     * @param {Object} matchObject Icon font value
     *
     * @return {string} Return replace string
     */
    dg_iconlist_replace_bulk_string: function (string, queryItems, matchObject) {
        let joinedQueryObject = queryItems.join('|')
        let replaceRegex = new RegExp(`(?:${joinedQueryObject})`, "gi")

        return undefined !== string ? string.replace(replaceRegex, matched => matchObject[matched]) : '';
    },
     /**
     * Set filter styles for module
     *
     * @since 1.2.6
     *
     * @param {Object} options Icon font value
     *
     * @return void
     */
      dg_iconlist_process_child_filter : function (options) {
        const filtersData = [];
        const defaults = {
            'props': {},
            'additionalCss': '',
            'selector': ''
        };
        const settings = utility.extend(defaults, options);
        const {props, additionalCss, selector} = settings;
    
        if (!!props['child_filter_blur'] && props['child_filter_blur'] !== '0px') {
            filtersData.push(`blur(${props['child_filter_blur']})`);
        }
        if (!!props['child_filter_brightness'] && props['child_filter_brightness'] !== '100%') {
            filtersData.push(`brightness(${props['child_filter_brightness']})`);
        }
        if (!!props['child_filter_contrast'] && props['child_filter_contrast'] !== '100%') {
            filtersData.push(`contrast(${props['child_filter_contrast']})`);
        }
        if (!!props['child_filter_hue_rotate'] && props['child_filter_hue_rotate'] !== '0deg') {
            filtersData.push(`hue-rotate(${props['child_filter_hue_rotate']})`);
        }
        if (!!props['child_filter_invert'] && props['child_filter_invert'] !== '0%') {
            filtersData.push(`invert(${props['child_filter_invert']})`);
        }
        if (!!props['child_filter_opacity'] && props['child_filter_opacity'] !== '100%') {
            filtersData.push(`opacity(${props['child_filter_opacity']})`);
        }
        if (!!props['child_filter_saturate'] && props['child_filter_saturate'] !== '100%') {
            filtersData.push(`saturate(${props['child_filter_saturate']})`);
        }
        if (!!props['child_filter_sepia'] && props['child_filter_sepia'] !== '0%') {
            filtersData.push(`sepia(${props['child_filter_sepia']})`);
        }
    
        // set default style for `${selector}`
        additionalCss.push([{
            'selector': selector,
            'declaration': `backface-visibility: hidden;`
        }]);
    
        // set blend mode style for `${selector}`
        if (!!props['child_mix_blend_mode'] && props['child_mix_blend_mode'] !== 'normal') {
            additionalCss.push([{
                'selector': selector,
                'declaration': `mix-blend-mode: ${props['child_mix_blend_mode']}};`
            }]);
        }
    
        // set filter style for `${selector}`
        if (filtersData.length !== 0) {
            additionalCss.push([{
                'selector': selector,
                'declaration': `filter: ${filtersData.join(' ')};`
            }]);
        }
    },
     /**
     * Process background styles
     *
     * @param {*} options
     */
     process_new_background: function (options = {}) {
        const defaults = {
            'props': {},
            'base_name': '',
            'context': '',
            'additionalCSS': '',
            'selector': '',
            'important' : false
        }
        const settings = this.extend(defaults, options);

        process_new_background.generateStyles(settings);
    },
};

export default utility;