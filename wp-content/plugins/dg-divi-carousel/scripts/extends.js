const _ex = {

    extend: function(defaults, options) {
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
    process_icon_font_style: function(options = {}) {
        const defaults = {
            'props'             : {},
            'key'               : '',
            'additionalCss'     : '',
            'selector'          : ''
        };
        const settings = this.extend(defaults, options);
        var {props, key, additionalCss, selector} = settings;
        
        if(!props[key]) return;

        const utils = window.ET_Builder.API.Utils;

        if (!utils.processIconFontData) return;
        const iconData = utils.processIconFontData(props[key]);

        if(!iconData) return;

        if(iconData.iconFontFamily !== "ETmodules") {
            additionalCss.push([{
                selector:    selector,
                declaration: `font-family: ${iconData.iconFontFamily} !important;`,
            }]);
        }
        additionalCss.push([{
            selector:    selector,
            declaration: `font-weight: ${iconData.iconFontWeight} !important;`,
        }]);
    }, 
    // fix builder css selector issue
    // remove ".et-db #et-boc .et-l" selectors from VB
    df_fix_builder_css_issue: function(wrapper, styleContainer) {
        const _styles = styleContainer.querySelectorAll('.et-fb-custom-css-output');

        if(_styles.length !== 0) {
            _styles.forEach(ele => {
                var new_style = ele.innerHTML.replace(/.et-db/g, "");
                    new_style = new_style.replace(/#et-boc/g, "");
                    new_style = new_style.replace(/.et-l/g, "");

                ele.innerHTML = new_style;
            })
        }
    },
    apply_element_color : function ( props, key, additionalCss, type, eleSelector, eleSelectorHover, important ) {
        const slug = props[key];
        const slugHover = props[key + '__hover'];
        const importantText = true === important ? '!important' : '';
        if('' !== slug) {
            additionalCss.push([{
                selector:    eleSelector,
                declaration: `${type}: ${slug + importantText};`,
            }]);
        }

        if (props[key + '__hover_enabled']) {
            if ( props['hover_enabled'] && props['hover_enabled'] == 1) {
                if ( props[key + '__hover'] ) {
                    additionalCss.push([{
                        selector:    eleSelector,
                        declaration: `${type}: ${slugHover + importantText};`,
                    }]);
                } 
            }
        }
    },

    adding_margin_padding : function (props,key, additionalCss, eleSelector, hoverSelector, attr) {
        const desktop = props[key];
        const tablet = props[key + '_tablet'];
        const mobile = props[key + '_phone'];
        
        if (desktop && '' !== desktop) {
            const desktopValue = desktop.split('|');      
            additionalCss.push([{
                selector:    eleSelector,
                declaration: `${attr}-top: ${desktopValue[0]}!important;
                ${attr}-right: ${desktopValue[1]}!important;
                ${attr}-bottom: ${desktopValue[2]}!important;
                ${attr}-left: ${desktopValue[3]}!important;`,
            }]);
        }
        if (tablet && '' !== tablet) {
            const tabletValue = tablet.split('|');      
            additionalCss.push([{
                selector:    eleSelector,
                declaration: `${attr}-top: ${tabletValue[0]}!important;
                ${attr}-right: ${tabletValue[1]}!important;
                ${attr}-bottom: ${tabletValue[2]}!important;
                ${attr}-left: ${tabletValue[3]}!important;`,
                'device':'tablet',
            }]);
        }
        if (mobile && '' !== mobile) {
            const mobileValue = mobile.split('|');      
            additionalCss.push([{
                selector:    eleSelector,
                declaration: `${attr}-top: ${mobileValue[0]}!important;
                ${attr}-right: ${mobileValue[1]}!important;
                ${attr}-bottom: ${mobileValue[2]}!important;
                ${attr}-left: ${mobileValue[3]}!important;`,
                'device':'phone'
            }]);
        }
        if (props[key + '__hover_enabled']) {
            if ( props['hover_enabled'] && props['hover_enabled'] == 1 ) {
                if ( props[key + '__hover'] ) {
                    const hover = props[key + '__hover'];
                    const hoverValue = hover.split('|');  
                    additionalCss.push([{
                        selector:    eleSelector,
                        declaration: `${attr}-top: ${hoverValue[0]}!important;
                        ${attr}-right: ${hoverValue[1]}!important;
                        ${attr}-bottom: ${hoverValue[2]}!important;
                        ${attr}-left: ${hoverValue[3]}!important;`,
                    }]);
                } 
            }
        }
    },

    apply_single_value : function (props, key, additionalCss, eleSelector, attr, unit = '%', $default = '', decrease = false, addition = true) {
        let itemValue = !props[key] && $default ? $default: parseInt(props[key]);
        let desktop = decrease === false ? itemValue : 100 - itemValue;
        let tablet = decrease === false ? parseInt(props[key + '_tablet']) : 100 - parseInt(props[key + '_tablet']);
        let mobile = decrease === false ? parseInt(props[key + '_phone']) : 100 - parseInt(props[key + '_phone']);
        const negative = addition === false ? '-' : '';
        desktop = negative + desktop + unit;
        tablet = negative + tablet + unit;
        mobile = negative + mobile + unit;
        if (desktop && '' !== desktop) {
            additionalCss.push([{
                selector:    eleSelector,
                declaration: `${attr}: ${desktop};`,
            }]);
        }
        if (tablet && '' !== tablet) {
            additionalCss.push([{
                selector:    eleSelector,
                declaration: `${attr}: ${tablet};`,
                'device':'tablet',
            }]);
        }
        if (mobile && '' !== mobile) {
            additionalCss.push([{
                selector:    eleSelector,
                declaration: `${attr}: ${mobile};`,
                'device':'phone'
            }]);
        }
    },

    control_width_and_spacing: function(props,key, additionalCss, eleSelector, attr) {
        const desktop = props[key];
        const tablet = props[key + '_tablet'];
        const mobile = props[key + '_phone'];
    
        if (desktop && '' !== desktop) {
            additionalCss.push([{
              selector:    eleSelector,
              declaration: `${attr}: ${desktop}!important;`,
            }]);
        }
        if (tablet && '' !== tablet) {
            additionalCss.push([{
              selector:    eleSelector,
              declaration: `${attr}: ${tablet}!important;`,
              'device':'tablet',
            }]);
        }
        if (mobile && '' !== mobile) {
            additionalCss.push([{
              selector:    eleSelector,
              declaration: `${attr}: ${mobile}!important;`,
              'device':'phone'
            }]);
        }
    
    },

    render_title: function (props) {
        let title = props.title;
        if(props.url) {
          title = `<a href="${props.url}" target="${props.url_new_window}">${title}</a>`;
        }
        return {__html : title};
    },

    render_subtitle: function (props) {
        return {__html : props.sub_title};
    },

    process_single_value : function (options = {}) {
        const defaults = {
            'props'             : {},
            'key'               : '',
            'additionalCss'     : '',
            'selector'          : '',
            'type'              : '',
            'unit'              : '%',
            'default_value'     : '',
            'decrease'          : false,
            'addition'          : true,
            'no_unit'           : false,
            'unit_type'         : true
        };
        const settings = this.extend(defaults, options);
        var {props,key, additionalCss, selector, type, 
                unit, default_value, decrease, addition, unit_type} = settings;

        const unit_value = props[key].replace(parseInt(props[key]), "") !== '' ? props[key].replace(parseInt(props[key]), "") : unit;
        const unit_value_tab = props[key + '_tablet'] ? props[key + '_tablet'].replace(parseInt(props[key + '_tablet']), "") : unit_value;
        const unit_value_ph = props[key + '_phone'] ? props[key + '_phone'].replace(parseInt(props[key + '_phone']), "") : unit_value_tab;
        
        let itemValue = !props[key] && default_value ? default_value: parseInt(props[key]);
        let desktop = decrease === false ? itemValue : 100 - itemValue;
        let tablet = decrease === false ? 
            parseInt(props[key + '_tablet']) : 100 - parseInt(props[key + '_tablet']);
        let mobile = decrease === false ? 
            parseInt(props[key + '_phone']) : 100 - parseInt(props[key + '_phone']);
        const negative = addition === false ? '-' : '';

        desktop = negative + desktop;
        tablet = negative + tablet;
        mobile = negative + mobile;

        if (unit_type === true) {
            desktop = desktop + unit_value;
            tablet = tablet + unit_value_tab;
            mobile = mobile + unit_value_ph;
        }
        
        
        if (desktop && '' !== desktop) {
            additionalCss.push([{
                selector:    selector,
                declaration: `${type}: ${desktop};`,
            }]);
        }
        if (tablet && '' !== tablet) {
            additionalCss.push([{
                selector:    selector,
                declaration: `${type}: ${tablet};`,
                'device':'tablet',
            }]);
        }
        if (mobile && '' !== mobile) {
            additionalCss.push([{
                selector:    selector,
                declaration: `${type}: ${mobile};`,
                'device':'phone'
            }]);
        }
        if (props[key + '__hover_enabled']) {
            if ( props['hover_enabled'] && props['hover_enabled'] == 1 ) {
                if ( props[key + '__hover'] ) {
                    const hover = props[key + '__hover'];
                    additionalCss.push([{
                        selector:    selector,
                        declaration: `${type}: ${hover}!important;`,
                    }]);
                } 
            }
        }
    },

    

}
export default _ex;