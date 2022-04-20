var ThemifyStyles;
(function (window, document, undefined) {
    'use strict';
    if (typeof String.prototype.trimRight !== 'function') {
        String.prototype.trimRight = function () {
            return this.replace(/\s+$/, '');
        };
    }
    var isVisual = null,
        Rules={},
        cacheGSProp={},
        AllFields={};
    ThemifyStyles = {
        styleName: 'tb_component_customize_',
        storeAgekey: 'tb_styles_rules',
        breakpoint: null,
        builder_id:null,
        saving: null,
        disableNestedSel:null,
        fonts: {},
        cf_fonts: {},
        GS:{},
        init: function (data, breakpointsReverse,bid) {
            this.breakpointsReverse = breakpointsReverse;
            AllFields = data;
            this.builder_id=bid;
            isVisual = typeof tb_app !== 'undefined' && tb_app.mode === 'visual';
            if (isVisual===true) {
                this.InitInlineStyles();
            }
        },
        getRules:function(module){
            return module?Rules[Rules]:Rules;
        },
        getStorageRules: function () {
            if (themifyBuilder.debug) {
                return false;
            }
            var record = localStorage.getItem(this.storeAgekey);
            if (!record) {
                return false;
            }
            record = JSON.parse(record);
            return record;
        },
        setStorageRules: function (v) {

        },
        getStyleData:function(styles,p){
            var res={};
            for(var i in styles){
                if(i!=='label' && i!=='units' && i!=='description' && i!=='after' && i!=='options' && i!=='wrap_class' && i!=='option_js' && i!=='class' && i!=='binding'){
                    res[i]=styles[i];
                }
            }
            res['p']=p;
            return res;
        },
        parseFontName:function(font){
			if(font){
				font = font.split(',');
				var res='';
				for(var i=0,len=font.length;i<len;++i){
					var v = font[i].trim();
					if(v!=='serif' && v!=='sans-serif' && v!=='monospace' && v!=='fantasy' && v!=='cursive' && v[0]!=='"' && v[0]!=="'"){
						res+='"'+v+'"';
					}
					else{
						res+=v;
					}
					if(i!==(len-1)){
						res+=', ';
					}
				}
			}
			else{
				return font;
			}
            return res;
        },
        extend: function () {
            // Variables
            var extended = {},
                    deep = false,
                    self = this,
                    i = 0,
                    length = arguments.length;
            // Check if a deep merge
            if (arguments[0] === true) {
                deep = arguments[0];
                ++i;
            }
            // Merge the object into the extended object
            var merge = function (obj) {
                for (var prop in obj) {
                    if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                        // If deep merge and property is an object, merge properties
                        if (deep && Object.prototype.toString.call(obj[prop]) === '[object Object]') {
                            extended[prop] = self.extend(true, extended[prop], obj[prop]);
                        } else {
                            extended[prop] = obj[prop];
                        }
                    }
                }
            };
            // Loop through each object and conduct a merge
            for (; i < length; ++i) {
                var obj = arguments[i];
                merge(obj);
            }
            return extended;
        },
        InitInlineStyles: function () {
            var points = this.breakpointsReverse,
                f = document.createDocumentFragment();
            if(tb_app.GS.isGSPage===false){
                for (var i = points.length - 1; i > -1; --i) {
                    var style = document.createElement('style');
                    style.type= 'text/css';
                    style.id = this.styleName+points[i]+'_global';
                    if (points[i] !== 'desktop') {
                        style.media = 'screen and (max-width:' + tb_app.Utils.getBPWidth(points[i]) + 'px)';
                    }
                    f.appendChild(style);
                }
            }
            for (i = points.length - 1; i > -1; --i) {
                style = document.createElement('style');
                style.type= 'text/css';
                style.id = this.styleName + points[i];
                if (points[i] !== 'desktop') {
                    style.media = 'screen and (max-width:' + tb_app.Utils.getBPWidth(points[i]) + 'px)';
                }
                f.appendChild(style);
            }
            var el = document.getElementById('tb_active_style_'+this.builder_id);
            if(el!==null){
                el.parentNode.replaceChild(f, el);
            }
            else{
                document.body.appendChild(f);
            }
        },
        getSheet: function (breakpoint,isGlobal) {
            if(isGlobal===true){
               breakpoint+='_global'; 
            }
            return  document.getElementById(this.styleName+breakpoint).sheet;
        },
        getBaseSelector: function (type, id) {
            var selector= '.themify_builder_content-'+this.builder_id+' .tb_' + id + '.module';
                selector += type === 'row' || type === 'column' || type === 'subrow' ? '_' : '-';
                selector += type;
            return selector;
        },
        getNestedSelector: function (selectors) {
            if(this.disableNestedSel===null){
                var nested = ['p', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'span'],
                        nlen = nested.length;
                selectors = selectors.slice(0);
                for (var j = selectors.length - 1; j > -1; --j) {
                    if (selectors[j].indexOf('.tb_text_wrap') !== -1) {
                        var s = selectors[j].trimRight();
                        if (s.endsWith('.tb_text_wrap')) {//check if after .tb_text_wrap is empty 
                            for (var k = 0; k < nlen; ++k) {
                                selectors.push(s + ' ' + nested[k]);
                            }
                        }
                    }
                }
            }
            return selectors;
        },
        toRGBA: function (color) {
            if (color !== undefined && color !== '' && color !== '#') {
                if(color.indexOf('rgba') >= 0){
                    return color;
                }
                var colorArr = color.split('_'),
                        patt = /^([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/;
                if (colorArr[0] !== undefined) {
                    var matches = patt.exec(colorArr[0].replace('#', '')),
                            opacity = colorArr[1] !== undefined && colorArr[1] != '0.99' ? colorArr[1] : 1;
                    return matches ? 'rgba(' + parseInt(matches[1], 16) + ', ' + parseInt(matches[2], 16) + ', ' + parseInt(matches[3], 16) + ', ' + opacity + ')' : (color[0]!=='#'?('#'+color):color);
                }
                else if(color[0]!=='#'){
                        color = '#'+color;
                }
            }
            else {
                color = '';
            }
            return color;
        },
        getStyleVal: function (id, data) {
            if (this.breakpoint === 'desktop') {
                return data[id] !== '' ? data[id] : undefined;
            }
            var breakpoints = this.breakpointsReverse,
                    index = breakpoints.indexOf(this.breakpoint);
            for (var i = index, len = breakpoints.length; i < len; ++i) {
                if (breakpoints[i] !== 'desktop') {
                    if (data['breakpoint_' + breakpoints[i]] !== undefined && data['breakpoint_' + breakpoints[i]][id] !== undefined && data['breakpoint_' + breakpoints[i]][id] !== '') {
                        return data['breakpoint_' + breakpoints[i]][id];
                    }
                }
                else if (data[id] !== '') {
                    return data[id];
                }
            }
            return undefined;
        },
        generateGSstyles:function(gsItems,elType,gsClass){
            if(cacheGSProp[elType]===undefined){
                cacheGSProp[elType]={};
            }
            var elOptions=this.getStyleOptions(elType),
                points = this.breakpointsReverse,
                data={},
                check=function(option,id,gsType){
                    if(cacheGSProp[elType][id]!==undefined){
                        return cacheGSProp[elType][id];
                    }
                    if(option['is_overlay']===undefined && option['type']!=='frame' && option['type']!=='video' && (option['type']!=='radio' || option['prop']!==undefined)){
                        var tab=option['p'],
                            t=option['type'],
                            r=option['is_responsive'],
                            o=null,
                            h=option['is_hover'],
                            p=option['prop'];
                            if(t==='select' || t==='icon_radio' || t==='radio' || t==='checkbox' || t==='icon_checkbox'){
                                for(var i in option){
                                    if(option[i]===true && i!=='option_js'){
                                        o=i;
                                        break;
                                    }
                                }
                            }
                            var reChechk=function(){
                                for(var i in elOptions){
                                    if(tab===elOptions[i]['p'] && p===elOptions[i]['prop'] && t===elOptions[i]['type'] &&  h===elOptions[i]['is_hover'] && (o===null || elOptions[i][o]===true) && r===elOptions[i]['is_responsive']){
                                        cacheGSProp[elType][id]=i;
                                        return true;
                                    }
                                }
                                return false;
                            };
                            if(reChechk()===true){
                                return cacheGSProp[elType][id];
                            }
                            if(p==='background-image' && (t==='image' || t==='imageGradient')){
                                t=t==='image'?'imageGradient':'imageGradient';
                                if(reChechk()===true){
                                    return cacheGSProp[elType][id];
                                }
                            }
                            else if(p==='margin-top' || p==='margin-bottom'){
                                if(elType==='row' || elType==='column'){
                                        if(t==='margin'  &&  gsType!=='row' && gsType!=='column'){
                                            t='range';
                                            if(reChechk()===true){
                                                return cacheGSProp[elType][id];
                                            }
                                        }
                                }
                                else if(t==='range' && (gsType==='row' || gsType==='column')){
                                    t='margin';
                                    if(reChechk()===true){
                                        return cacheGSProp[elType][id];
                                    }
                                }
                            }
                    }
                    cacheGSProp[elType][id]=false;
                    return false;
                },
                len = points.length;
                for(var k=0,len2=gsItems.length;k<len2;++k){
                    var cl=gsItems[k].trim();
                    if(cl!=='' && gsClass[cl]!==undefined){  
                            var args=gsClass[cl]['data'][0],
                                type=gsClass[cl]['type'];
                            if(type!=='row' && type!=='subrow'){
                                args=args['cols'][0];
                            }
                            if(type==='column' || type==='row' ||  type==='subrow'){
                                args=args['styling'];
                            }
                            else{
                                args=args['modules'][0];
                                type=args['mod_name'];
                                args=args['mod_settings'];
                            }
                            if(args!==undefined){
                                var opt=elType===type?elOptions:this.getStyleOptions(type);     
                                for (var i = len - 1; i > -1; --i) {
                                    if (points[i] === 'desktop') {
                                        for(var j in args){        
                                            if(opt[j]!==undefined  && (data[j]===undefined || data[j]==='' || data[j]===false)){
                                                var index=elOptions[j]!==undefined?j:check(opt[j],j,type);
                                                if(index===j ||(index!==false && !data[index] && data[index]!='0')){
                                                    data[index]=args[j];
                                                }
                                            }
                                        }
                                    }
                                    else if(args['breakpoint_'+points[i]]!==undefined){
                                        var found=true,
                                            bp = 'breakpoint_'+points[i];
                                        if(data[bp]===undefined){
                                            data[bp]={};
                                            found=false;
                                        }
                                        for(var j in args[bp]){
                                            if(opt[j]!==undefined && opt[j]['is_responsive']===undefined && (data[bp][j]===undefined || data[bp][j]==='' || data[bp][j]===false)){
                                                var index=elOptions[j]!==undefined?j:check(opt[j],j,type);
                                                if(index===j || (index!==false && !data[bp][index] && data[bp][index]!='0')){
                                                    data[bp][index]=args[bp][j];
                                                    found=true;
                                                }
                                            }
                                        }
                                        if(found===false){
                                            delete data[bp];
                                        }
                                    }
                                }
                            }
                    }
                }
                return data;
        },
        createCss: function (data, elType, saving,gsClass,isGSCall) {
            if (!elType) {
                elType = 'row';
            }
            this.saving = saving;
            var points = this.breakpointsReverse,
                    len = points.length,
                    self = this,
                    css = {},
                    builder_id=this.builder_id,
                    recursiveLoop=function(data,type){
                        var getCustomCss = function (component, elementId, st) {
                                if (st !== undefined) {
                                    var styles = self.extend(true, {}, st);
                                    for (var i = len - 1; i > -1; --i) {
                                        var res = null;
                                        self.breakpoint = points[i];
                                        if (points[i] === 'desktop') {
                                            res = self.getFieldCss(elementId, component, styles);
                                        }
                                        else if (styles['breakpoint_' + points[i]] !== undefined && Object.keys(styles['breakpoint_' + points[i]]).length > 0) {
                                            res = self.getFieldCss(elementId, component, styles['breakpoint_' + points[i]]);
                                        }
                                        if (res && Object.keys(res).length > 0) {
                                            if (css[points[i]] === undefined) {
                                                css[points[i]] = {};
                                            }
                                            for (var j in res) {
                                                if (css[points[i]][j] === undefined) {
                                                    css[points[i]][j] = res[j];
                                                }
                                                else {
                                                    for (var k = res[j].length - 1; k > -1; --k) {
                                                        css[points[i]][j].push(res[j][k]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            setData=function(elType,element_id,styling){
                                if(gsClass!==undefined && styling['global_styles']!==undefined && styling['global_styles']!=='' ){
                                    var gsData = self.generateGSstyles(styling['global_styles'].split(' '),elType,gsClass);
                                        self.GS = self.extend(true,{}, self.GS,self.createCss([{'styling':gsData,'element_id':element_id}],elType,self.saving,undefined,true));
                                }
                                if ( styling !== undefined && styling!==null && styling['builder_content'] !== undefined ) {
                                    self.builder_id=element_id;
                                    if(typeof styling['builder_content']==='string'){
                                        styling['builder_content'] =JSON.parse(styling['builder_content']);
                                    }
                                    loop(styling['builder_content'],'row');
                                    self.builder_id=builder_id;
                                }
                                getCustomCss(elType, element_id, styling);
                            },     
                            loop=function(data,type){
                                for (var i in data) {
                                    var row = data[i],
                                        styling = row['styling'] ? row['styling'] : row['mod_settings'];
                                    if(styling!==undefined){
                                        setData(type,row['element_id'],styling);
                                    }
                                    if (row['cols'] !== undefined) {
                                        for (var j in row['cols']) {
                                            var col = row['cols'][j];
                                            if(col['styling']!==undefined){
                                                setData('column',col['element_id'], col['styling']);
                                            }
                                            if (col['modules'] !== undefined) {
                                                for (var m in col['modules']) {
                                                    var mod = col['modules'][m];
                                                    if (mod['mod_name'] !== undefined) {
                                                        if(mod['mod_settings']!==undefined){
                                                            setData(mod['mod_name'],mod['element_id'],mod['mod_settings']);
                                                        }
                                                    }
                                                    else {
                                                        loop([mod], 'subrow');
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            };
                        loop(data,type);
                    };
            recursiveLoop(data,elType);
            if(isGSCall===undefined){
                if(Object.keys(this.fonts).length>0){
                    css['fonts'] = this.fonts;
                }
                if(Object.keys(this.cf_fonts).length>0){
                    css['cf_fonts'] = this.cf_fonts;
                }
                if(Object.keys(this.GS).length>0){
                    css['gs'] = this.GS;
                }	
                this.fonts={};
                this.GS={};
                this.cf_fonts = {};
                this.saving = null;
            }
            points=len=self=builder_id=recursiveLoop=null;
            return css;
        },
        getStyleOptions: function (module) {
            if (Rules[module] === undefined) {
                var all_fields = AllFields;
                if (all_fields[module] !== undefined) {
                    Rules[module] = {};
                    var self = this,
                            getStyles = function (styles,parent) {
                                for (var i in styles) {
                                    if(styles[i]!==null){
                                        var type = styles[i].type;
                                        if (type === 'expand' || type === 'multi' || type === 'group') {
                                            var p =parent;
                                            if(type==='expand' && styles[i]['label']!==undefined){
                                                p=parent+'_'+styles[i].label.replace(/\s/g,'');
                                        }
                                            getStyles(styles[i].options,p);
                                        }
                                        else if (type === 'tabs') {
                                            for (var j in styles[i].options) {
                                                var p = '';
                                                if(parent===undefined){
                                                        p=j;
                                                }
                                                else{
                                                        p=parent+'_'+j;
                                                }
                                                getStyles(styles[i].options[j].options,p);
                                            }
                                        }
                                        else if(styles[i]['id']!==undefined){ 
                                            var id = styles[i]['id'];
                                            if (styles[i]['prop'] !== undefined) {
                                                Rules[module][id] = self.getStyleData(styles[i],parent);
                                                var prop = styles[i]['prop'];

                                                if(prop === 'font-size' || prop === 'line-height' || prop==='letter-spacing'){
                                                    Rules[module][id+'_unit']={'type':'select','p':parent};
                                                }
                                                if (type === 'box_shadow' || type === 'text_shadow') {
                                                    var vals = type === 'box_shadow' ? ['hOffset', 'vOffset', 'blur','spread', 'color'] : ['hShadow', 'vShadow', 'blur', 'color'];
                                                    for (var j = vals.length - 1; j > -1; --j) {
                                                        var k = id + '_' + vals[j];
                                                        Rules[module][k] = self.getStyleData(styles[i],parent);
                                                        if(vals[j]!=='color'){
                                                            Rules[module][k+'_unit']={'type':'select','p':parent};
                                                        }
                                                    }
                                                    if(type === 'box_shadow'){
                                                        Rules[module][id + '_inset']={type:'checkbox','p':parent};
                                                    }
                                                }
                                                else if (type === 'fontColor') {
                                                    if(Rules[module][styles[i].s] === undefined){
                                                        Rules[module][styles[i].s] = {type: 'color', prop: 'color', isFontColor: true, selector: styles[i].selector, origId: id,'p':parent};
                                                    }
                                                    if(Rules[module][styles[i].g] === undefined){
                                                        Rules[module][styles[i].g] = {type: 'gradient', 'p':parent};
                                                        Rules[module][styles[i].g]=Rules[module][styles[i].g + '-gradient-angle'] = Rules[module][styles[i].g + '-circle-radial'] = Rules[module][styles[i].g + '-gradient-type'] = {type: 'gradient','p':parent};
                                                    }
                                                }
                                                else if (type === 'padding' || type === 'margin' || type === 'border' || type === 'border_radius') {
                                                    var vals = ['top', 'right', 'bottom', 'left'],
                                                            is_border = type === 'border',
                                                            is_border_radius = is_border === false && type === 'border_radius';
                                                    if (is_border === true) {
                                                        Rules[module][id + '-type'] = {type: 'radio','p':parent};
                                                    }
                                                    else{
                                                        Rules[module]['checkbox_' + id + '_apply_all'] ={'type':'checkbox','p':parent};
                                                        if(is_border_radius===false){
                                                                Rules[module][id + '_opp_top'] ={'type':'checkbox','p':parent};
                                                                Rules[module][id + '_opp_left'] ={'type':'checkbox','p':parent};
                                                        }
                                                    }
                                                    for (var j = 3; j > -1; --j) {
                                                        var k = id + '_' + vals[j];
                                                        if (is_border === true) {
                                                            Rules[module][k+'_style'] =  self.getStyleData(styles[i],parent);
                                                            Rules[module][k+'_color'] =self.getStyleData(styles[i],parent);
                                                            Rules[module][k+'_style']['prop'] = prop + '-' + vals[j]+'-style';
                                                            Rules[module][k+'_color']['prop'] = prop + '-' + vals[j]+'-color';
                                                            k += '_width';
                                                        }
                                                        Rules[module][k] = self.getStyleData(styles[i],parent);
                                                        Rules[module][k+'_unit']={'type':'select','p':parent};
                                                        if(is_border_radius === true){
                                                           var tmpProp='border-';
                                                            if(vals[j]==='top'){
                                                                tmpProp+='top-left-radius';
                                                            }
                                                            else if(vals[j]==='right'){
                                                                tmpProp+='top-right-radius';  
                                                            }
                                                            else if(vals[j]==='left'){
                                                                tmpProp+='bottom-left-radius';  
                                                            }
                                                            else if(vals[j]==='bottom'){
                                                                tmpProp+='bottom-right-radius';  
                                                            }
                                                            Rules[module][k]['prop']=tmpProp;
                                                        }
                                                        else{
                                                            Rules[module][k]['prop']=prop + '-' + vals[j];
                                                        }
                                                    }
                                                }
                                                else if (type === 'gradient' || type === 'imageGradient') {
                                                    Rules[module][id + '-gradient'] = self.getStyleData(styles[i],parent);
                                                    Rules[module][id + '-gradient-angle'] = Rules[module][id + '-circle-radial'] = Rules[module][id + '-gradient-type'] = {type: 'gradient','p':parent};
                                                    if (type === 'imageGradient') {
                                                        Rules[module][id + '-type'] = {type: 'radio','p':parent};
                                                        //bg
                                                        Rules[module][styles[i].colorId] = self.getStyleData(styles[i],parent);
                                                        Rules[module][styles[i].colorId]['prop'] = 'background-color';
                                                        Rules[module][styles[i].colorId]['type'] = 'color';
                                                        Rules[module][styles[i].colorId]['id'] = styles[i].colorId;

                                                    }
                                                }
                                                else if (type === 'multiColumns') {
                                                    Rules[module][id + '_gap'] = Rules[module][id + '_divider_color'] = Rules[module][id + '_divider_width'] = Rules[module][id + '_divider_style'] = {type: type,'p':parent};
                                                }
                                                else if (type === 'font_select') {
                                                    Rules[module][id + '_w'] = {type: 'font_weight'};
                                                }
                                                else if (type === 'filters') {
                                                    var vals = ['hue', 'saturation', 'brightness', 'contrast','invert','sepia','opacity','blur'];
                                                    for (var j = vals.length - 1; j > -1; --j) {
                                                        var k = id + '_' + vals[j];
                                                        Rules[module][k] = self.getStyleData(styles[i],parent);
                                                    }
                                                }
                                                else if(type==='width'){
                                                    Rules[module]['min_'+id] =  {prop:'min-width', selector: styles[i].selector,type: 'width','p':parent};
                                                    Rules[module]['max_'+id] =  {prop:'max-width', selector: styles[i].selector,type: 'width','p':parent};
                                                    Rules[module][id+'_auto_width'] =  {prop:'width', selector: styles[i].selector,type: 'width','p':parent};
                                                    Rules[module][id+'_unit']={'type':'select','p':parent};
                                                }
                                                else if(type==='height'){
                                                        Rules[module][id+'_auto_height'] =  {prop:'width', selector: styles[i].selector,type: 'width','p':parent};
                                                        Rules[module][id+'_unit']={'type':'select','p':parent};
                                                }
                                            }
                                            else {
                                                Rules[module][id] = self.getStyleData(styles[i],parent);
                                            }
                                        }
                                        else if(type==='margin_opposity'){
                                            Rules[module][styles[i].topId] =  {prop:'margin-top', selector: styles[i].selector,type: 'range','p':parent}; 
                                            Rules[module][styles[i].bottomId] =  {prop:'margin-bottom', selector: styles[i].selector,type: 'range','p':parent}; 
                                            Rules[module][styles[i].topId+'_unit']={'type':'select','p':parent};
                                            Rules[module][styles[i].bottomId+'_unit']={'type':'select','p':parent};
                                            Rules[module][styles[i].topId+'_opp_top']={'type':'checkbox','p':parent};
                                        }
                                    }
                                }
                            };
                    if (all_fields[module].styling !== undefined) {
                        if (all_fields[module].styling.options.length !== undefined) {
                            getStyles(all_fields[module].styling.options);
                        }
                        else {
                            getStyles(all_fields[module].styling);
                        }
                    }
                    else {
                        getStyles(all_fields[module].type === undefined ? all_fields[module] : [all_fields[module]]);
                    }

                }
                else {
                    return false;
                }
            }
            return Rules[module];
        },
        getFieldCss: function (elementId, module, settings) {
            if (AllFields[module] !== undefined) {

                var styles = {},
                        rules = this.getStyleOptions(module),
                        prefix = this.getBaseSelector(module, elementId);
                /*
                 settings=this.cleanUnusedStyles(settings);
                 if(this.breakpoint==='desktop'){
                 }
                 */
                var isSaving = this.saving === true;
                for (var i in settings) {
                    if (rules[i] !== undefined && rules[i].selector !== undefined) {
                        var type = rules[i].type;
                        if (type === 'margin') {
                            type = 'padding';
                        }
                        var st = this.fields[type].call(this, i, module, rules[i], settings);			
                        if (st !== false) {
                            var selectors = Array.isArray(rules[i].selector) ? rules[i].selector : [rules[i].selector],
                                    isHover = rules[i].ishover === true,
                                    res = [];
                            selectors = this.getNestedSelector(selectors);
                            for (var j = 0, len = selectors.length; j < len; ++j) {
                                var sel = selectors[j];
                                if (isHover === true) {
                                    sel += ':hover';
                                }
                                if (isVisual === true) {
                                    if (isSaving === false) {
                                        if (isHover === true || sel.indexOf(':hover') !== -1) {
                                            sel += ',' + prefix + sel.replace(':hover', '.tb_visual_hover');
                                        }
                                    }
                                    else if (sel.indexOf('.tb_visual_hover') !== -1) {
                                        var s = sel.split(',');
                                        for (var k = s.length - 1; k > -1; --k) {
                                            if (s[k].indexOf('.tb_visual_hover') !== -1) {
                                                s.splice(k, 1);
                                            }
                                        }
                                        sel = s.join(',');
                                        s = null;
                                    }
                                }
                                res.push(prefix + sel);
                            }
                            res = res.join(',').trim().replace(/\s\s+/g, ' ');
                            if (styles[res] === undefined) {
                                styles[res] = [];
                            }
                            if (styles[res].indexOf(st) === -1) {
                                styles[res].push(st);
                            }
                        }
                        else if (st === null) {
                            delete settings[i];
                        }
                    }
                }
                return styles;
            }
            return false;
        },
        fields: {
            frameCache: {},
            imageGradient: function (id, type, args, data) {
                var selector = false,
                        is_gradient = id.indexOf('-gradient', 3) !== -1,
                        checked = is_gradient === true ? id.replace('-gradient', '-type') : id + '-type';
                checked = this.getStyleVal(checked, data);
                if (checked === 'gradient') {
                    if (is_gradient === true) {
                        selector = this.fields['gradient'].call(this, id, type, args, data);
                        selector += 'background-color:transparent;';
                    }
                }
                else if (is_gradient === false) {
                    selector = this.fields['image'].call(this, id, type, args, data);
                    if (selector !== false && this.getStyleVal(id, data) !== '') {
                        var v = this.fields['select'].call(this, args.repeatId, type, {prop: 'background-mode','origId':args.origId}, data);
                        if (v !== false) {
                            selector += v;
                        }
                        v = this.fields['position_box'].call(this, args.posId, type, {prop: 'background-position','origId':args.origId}, data);
                        if (v !== false) {
                            selector += v;
                        } else {
                            selector += 'background-position:50% 50%;';
                        }
                    }
                }
                return selector;
            },
            image: function (id, type, args, data) {
                var v = this.getStyleVal(id, data),
                        selector = false;
                if (v !== undefined) {
                    if (id === 'background_image' || id === 'bg_i_h') {
                        var checked = id === 'background_image' ? 'background_type' : 'b_t_h';
                        checked = this.getStyleVal(checked, data);
                        if (checked && 'image' !== checked && 'video' !== checked ) {
                            return false;
                        }
                    }
                    if (v === '') {
                        if (this.breakpoint !== 'desktop') {
                            selector = args.prop + ':none;';
                        }
                    }
                    else {
                        selector = args.prop + ':url(' + v + ');';
                        }
                    }
                return selector;
            },
            gradient: function (id, type, args, data) {
                var selector = false,
                    origId = args.id,
                    v = this.getStyleVal(id, data);
                if (origId === 'background_gradient' || origId === 'b_g_h' || origId === 'cover_gradient' || origId === 'cover_gradient_hover') {
                    var checked;
                    if (origId === 'background_gradient') {
                        checked = 'background_type';
                    }
                    else if (origId === 'b_g_h') {
                        checked = 'b_t_h';
                    }
                    else if (origId === 'cover_gradient') {
                        checked = 'cover_color-type';
                    }
                    else {
                        checked = 'cover_color_hover-type';
                    }
                    checked = this.getStyleVal(checked, data);
                    if (checked !== 'gradient' && checked !== 'hover_gradient' && checked !== 'cover_gradient') {
                        return false;
                    }
                }
                if (v){
                    var gradient = v.split('|'),
                            type = this.getStyleVal(origId + '-gradient-type', data),
                            angle;
                    if (!type) {
                        type = 'linear';
                    }
                    if (type === 'radial') {
                        angle = this.getStyleVal(origId + '-circle-radial', data) ? 'circle' : '';
                    }
                    else {
                        angle = this.getStyleVal(origId + '-gradient-angle', data);
                        if (!angle) {
                            angle = '180';
                        }
                        angle += 'deg';
                    }
                    if (angle !== '') {
                        angle += ',';
                    }
                    var res = [];
                    for (var i = 0, len = gradient.length; i < len; ++i) {
                        var p = parseInt(gradient[i]) + '%',
                                color = gradient[i].replace(p, '').trim();
                        res.push(color + ' ' + p);
                    }
                    res = res.join(',');

                    selector = args.prop + ':' + type + '-gradient(' + angle + res + ');';
                }
                return selector;
            },
            icon_radio: function (id, type, args, data) {
                var v = this.getStyleVal(id, data);
                if (!v) {
                    return false;
                }
                return args.prop + ':' + v + ';';
            },
            color: function (id, type, args, data) {
                if (args.prop === 'column-rule-color') {
                    return false;
                }

                var v = this.getStyleVal(id, data);
                if (v === '' || v === undefined) {
                    delete data[id];
                    return false;
                }
                var c = this.toRGBA(v);
                if (c==='' || c === '_') {
                    delete data[id];
                    return false;
                }
                if (args.isFontColor === true) {
                    return this.fields['fontColor'].call(this, args.origId, type, {s: id}, data);
                }
                var selector = args.prop + ':' + c + ';';

                if (args.colorId === id && args.origId !== undefined && !this.getStyleVal(args.origId, data)) {
                    if (this.getStyleVal(args.origId + '-type', data) === 'gradient') {
                        return false;
                    }
                    selector += 'background-image:none;';
                }
                else if ((id === 'b_c_h' || id === 'b_c_i_h') && (type === 'row' || type === 'column' || type === 'subrow' || type === 'sub-column')) {
                    var imgId = id === 'b_c_h' ? 'bg_i_h' : 'b_i_i_h';
                    if (!this.getStyleVal(imgId, data)) {
                        if (id !== 'b_c_h' || (id === 'b_c_h' && this.getStyleVal('b_t_h', data) !== 'gradient')) {
                            selector += 'background-image:none;';
                        }
                    }
                }
                return selector;
            },
            fontColor: function (id, type, args, data) {
                var v = this.getStyleVal(id, data),
                        selector = false;
                if (v === undefined || v.indexOf('_gradient') === -1) {
                    selector = this.fields['color'].call(this,args.s!==undefined?args.s:v.replace(/_solid$/ig, ''), type, {prop: 'color'}, data);

                    if (selector !== false) {
                        selector += '-webkit-background-clip:border-box;background-clip:border-box;background-image:none;';
                    }
                }
                else if (v !== undefined) {
                    selector = this.fields['gradient'].call(this, v.replace(/_gradient$/ig, '-gradient'), type, {prop: 'background-image','id':args.g}, data);
                    if (selector !== false) {
                        selector += '-webkit-background-clip:text;background-clip:text;color:transparent;';
                    }
                }
                return selector;
            },
            padding: function (id, type, args, data) {
                var prop = args.prop,
                        propName = prop.indexOf('padding') !== -1 ? 'padding' : 'margin',
                        origId = args.id,
                        v = this.getStyleVal(id, data);
                if (v===undefined || v==='') {
                    delete data[id + '_unit'];
                    return false;
                }
                if (data['checkbox_' + origId + '_apply_all'] && data['checkbox_' + origId + '_apply_all'] !== '|' && data['checkbox_' + origId + '_apply_all'] !== 'false') {
                    if (prop !== propName + '-top') {
                        return false;
                    }
                    prop = propName;
                }
                var unit = this.getStyleVal(id + '_unit', data);
                if (!unit) {
                    unit = 'px';
                }
                return prop + ':' + v + unit + ';';
            },
            box_shadow: function (id, type, args, data) {
                var prop = args.prop,
                    origId = args.id,
                    v = this.getStyleVal(id, data);
                if (v===undefined || v==='') {
                    delete data[id + '_unit'];
                    return false;
                }
                var subSets = prop==='box-shadow'?['hOffset', 'vOffset', 'blur','spread']:['hShadow', 'vShadow', 'blur'],
                    cssValue = '',
                    allIsempty=true;
                for (var i = 0, len = subSets.length; i < len; ++i) {
                var tid=origId + '_' + subSets[i],
                    val = this.getStyleVal(tid,data);
                    if(val===undefined || val===''){
                        val='0';
                    }
                    else{
                        allIsempty=false;
                    }
                    var unit = this.getStyleVal(tid+ '_unit', data);
                    if (!unit) {
                        unit = 'px';
                    }
                    cssValue += val + unit + ' ';
                }
                if(allIsempty===false){
                    cssValue += this.toRGBA( this.getStyleVal(origId + '_color',data));
                    if (prop==='box-shadow' && data[origId + '_inset'] === 'inset') {
                        cssValue = 'inset ' + cssValue;
                    }
                    return prop + ':' + cssValue + ';';
                }
                return false;
            },
            text_shadow: function (id, type, args, data) {
                return this.fields['box_shadow'].call(this,id, type, args, data);
            },
            border_radius: function (id, type, args, data) {
                var origId = args.id,
                    apply_all = data['checkbox_' + origId + '_apply_all'],
                    prop=args.prop;
                if (apply_all === '1') {
                    id = origId + '_top';
                    prop = 'border-radius';
                } 
                var v = this.getStyleVal(id, data);
                if (v===undefined || v==='') {
                    delete data[id + '_unit'];
                    return false;
                }
                var unit = this.getStyleVal(id + '_unit', data);
                if (!unit) {
                    unit = 'px';
                }
                return prop + ':' + v + unit + ';';
            },
            border: function (id, type, args, data) {
                var prop = args.prop,
                        origId = args.id,
                        val,
                        v = this.getStyleVal(id, data);
                if(id.indexOf('_color')!==-1 || ('none' !== v && id.indexOf('_style') !== -1)){
                   return false;
                }
                var all = this.getStyleVal(origId + '-type', data);
                if (all === undefined) {
                    all = 'top';
                }else if (all === 'all') {
                    if (prop.indexOf('border-top')===-1) {
                        return false;
                    }
                    prop = 'border';
                }
                var style = this.getStyleVal(id.replace('_width', '_style'), data),
                    colorId = id.replace('_width', '_color');
                if (style === 'none') {
                    val = style;
                }
                else {
                    if (!style) {
                        style = 'solid';
                    }
                    val = v + 'px ' + style;
                    var color = this.getStyleVal(colorId, data);
                    if (color !== '' && color !== undefined) {
                        val += ' ' + this.toRGBA(color);
                    }
                    else {
                        delete data[colorId];
                    }
                }
                return prop + ':' + val + ';';
            },
            select: function (id, type, args, data) {
                var prop = args.prop,
                        selector = '',
                        v = this.getStyleVal(id, data);

                if (v === undefined || v === '' || prop === 'column-rule-style') {
                    return false;
                }
                if (prop === 'background-mode' || prop === 'background-repeat' || prop === 'background-attachment') {
                    if (data[args['origId']] === undefined || data[args['origId']] === '') {
                        return false;
                    }
                    if (prop === 'background-mode') {
                        var bg_values = {
                            'repeat': 'repeat',
                            'repeat-x': 'repeat-x',
                            'repeat-y': 'repeat-y',
                            'repeat-none': 'no-repeat',
                            'no-repeat': 'no-repeat',
                            'fullcover': 'cover',
                            'best-fit-image': 'contain',
                            'builder-parallax-scrolling': 'cover',
                            'builder-zoom-scrolling': '100%',
                            'builder-zooming': '100%'
                        };
                        if (bg_values[v] !== undefined) {
                            if (v.indexOf('repeat') !== -1) {
                                prop = 'background-repeat';
                            }
                            else {
                                prop = 'background-size';
                                selector = 'background-repeat:no-repeat;';
                            }
                            v = bg_values[v];
                        }
                    }
                    else if (prop === 'background-repeat' && v === 'fullcover') {
                        prop = 'background-size';
                        v = 'cover';
                    }
                }
                else if (prop === 'column-count') {
                    if (v == '0') {
                        var opt = [id, id + '_gap', id + '_divider_color', id + '_width', id + '_divider_style'];
                        for (var i = opt.length - 1; i > -1; --i) {
                            delete data[opt[i]];
                        }
                        return false;
                    }
                    var gap = this.getStyleVal(id + '_gap', data);
                    if (gap) {
                        selector = 'column-gap:' + gap + 'px;';
                    }
                    var style = this.getStyleVal(id + '_divider_style', data),
                            width = this.getStyleVal(id + '_width', data);
                    if (style === 'none') {
                        delete data[id + '_divider_color'];
                        delete data[id + '_width'];
                        selector += 'column-rule:none;';
                    }
                    else {
                        if (width === '' || width === undefined) {
                            delete data[id + '_divider_color'];
                            delete data[id + '_width'];
                            delete data[id + '_divider_style'];
                        }
                        else {
                            if (!style) {
                                style = 'solid';
                            }
                            selector += 'column-rule:' + width + 'px ' + style;
                            var color = this.getStyleVal(id + '_divider_color', data);
                            if (color !== '' && color !== undefined) {
                                selector += ' ' + this.toRGBA(color);
                            }
                            selector += ';';
                        }
                    }

                }else if ('vertical-align' === prop ) {
                    if('inline-block' !== data[args.origID]){
                        delete data[id];
                        return false;
                    }else if(''!==v && true !== this.saving && undefined !== themifyBuilder ){
                        var flexVal;
                        if('top' === v){
                            flexVal = 'flex-start';
                        }else if('middle' === v){
                            flexVal = 'center';
                        }else{
                            flexVal = 'flex-end';
                        }
                        selector += 'align-self:'+flexVal+';' ;
                    }

                }else if(true === args.display && true !== this.saving && undefined !== themifyBuilder){
                    if('none' === v){
                        return false;
                    }else{
                        selector += 'inline-block' === v ? 'width:auto;' : 'width:100%;' ;
                    }
                }
                selector += prop + ':' + v + ';';
                return selector;
            },
            position_box: function (id, type, args, data) {
                var prop = args.prop,
                    selector = '',
                    v = this.getStyleVal(id, data),
                    bp = '';

                if (v === undefined || v === '') {
                    return false;
                }
                if (prop === 'background-position') {
                    if (data[args['origId']] === undefined || data[args['origId']] === '') {
                        return false;
                    }
                    if (v.indexOf('-')!==-1) {
                            v = v.replace('-', ' ');
                        } else {
                            bp = v.split(',');
                            v = bp[0] + '% ' + bp[1] + '%';
                        }
                    }
                selector += prop + ':' + v + ';';
                return selector;
            },
            font_select: function (id, type, args, data) {
                var v = data[id],
                        selector = '';
                if (v === 'default' || v === '' || v === undefined) {
                    delete data[id];
                    delete data[id + '_w'];
                    return false;
                }
                var is_google_font = (typeof ThemifyConstructor !== 'undefined' && ThemifyConstructor.font_select.google[v] !== undefined) || (typeof ThemifyBuilderStyle !== 'undefined' && ThemifyBuilderStyle.google[v] !== undefined),
                    is_cf_font = true === is_google_font ? false : (typeof ThemifyConstructor !== 'undefined' && ThemifyConstructor.font_select.cf[v] !== undefined) || (typeof ThemifyBuilderStyle !== 'undefined' && ThemifyBuilderStyle.cf[v] !== undefined);
                if(!is_google_font && !is_cf_font){
                    is_google_font = typeof themifyBuilder !== 'undefined' && null !== themifyBuilder.google && themifyBuilder.google[v] !== undefined;
                    is_cf_font = true === is_google_font ? false : typeof themifyBuilder !== 'undefined' && null !== themifyBuilder.cf && themifyBuilder.cf[v] !== undefined;
                    }
                if (is_google_font || is_cf_font) {
                    var w = data[id + '_w'],
                        type = true === is_google_font ? 'fonts' : 'cf_fonts';
                    if (this[type][v] === undefined) {
                        this[type][v] = [];
                    }
                    if (w) {
                        var def = {
                            normal: 'normal',
                            regular: 400,
                            italic: 400,
                            bold: 700
                        };
                        if (this[type][v].indexOf(w) === -1) {
                            this[type][v].push((def[w] !== undefined ? def[w] : w));
                        }
                        var italic = w.indexOf('italic') !== -1 ? ';font-style:italic' : '';
                        w = def[w] !== undefined ? def[w] : w.replace(/[^0-9]/g, '');
                        w += italic;
                        selector = 'font-weight:' + w + ';';
                    }
                }
                else {
                    delete data[id + '_w'];
                }
                selector += args.prop + ':'+this.parseFontName(v)+';';
                return selector;
            },
            frame: function (id, type, args, data) {
                return false;
            },
            range: function (id, type, args, data) {
                if (args.prop === 'column-gap' || args.prop === 'column-rule-width') {
                    return false;
                }
                var v = this.getStyleVal(id, data);
                if (v === '' || v === undefined) {
                    delete data[id];
                    delete data[id + '_unit'];
                    return false;
                }
                var unit = this.getStyleVal(id + '_unit', data);
                if (!unit) {
                    unit = 'px';
                }
                return args.prop + ':' + v + unit + ';';
            },
            radio: function (id, type, args, data) {
                if (args.prop === 'frame-custom') {
                    var side = id.split('-')[0],
                        layout,
                        v = this.getStyleVal(id, data);
                    if (v === side + '-presets') {
                        layout = this.getStyleVal(side + '-frame_layout', data);
                    }
                    else {
                        layout = this.getStyleVal(side + '-frame_custom', data);
                    }
                    if (!layout || layout==='none') {
                        if(!layout){
                                return false;
                        }
                        return this.breakpoint==='desktop'?false:'background-image:none;';
                    }
                    var selector = '';
                    if (v === side + '-presets') {
                        if (side === 'left' || side === 'right') {
                            layout += '-l';
                        }
                        var key = Themify.hash(layout),
                                self = this,
                                callback = function (svg) {
                                    var color = self.getStyleVal(side + '-frame_color', data);
                                    if (color !== undefined && color !== '') {
                                        svg = svg.replace(/\#D3D3D3/ig, self.toRGBA(color));
                                    }
                                    selector = 'background-image:url("data:image/svg+xml;utf8,' + encodeURIComponent(svg) + '");';
                                };
                        if (self.fields.frameCache[key] !== undefined) {
                            callback(self.fields.frameCache[key]);
                        }
                        else {
                            var frame = document.getElementById('tmpl-frame_'+layout);
                            if(frame!==null){
                                self.fields.frameCache[key] = frame.textContent.trim()
                                callback(self.fields.frameCache[key]);
                            }
                            else{
                                var url = isVisual !== true && typeof themifyBuilder !== 'undefined' ? themifyBuilder.builder_url : tbLocalScript.builder_url,
                                    xhr = new XMLHttpRequest();
                                url += '/img/row-frame/' + layout + '.svg';
                                xhr.open('GET', url, false);
                                xhr.onreadystatechange = function () {
                                    if (this.readyState === 4 && (this.status === 200 || xhr.status === 0)) {
                                        self.fields.frameCache[key] = this.responseText;
                                        callback(this.responseText);
                                    }
                                };
                                xhr.send(null);
                            }
                        }
                    }
                    else {
                        selector = 'background-image:url("' + layout + '");';
                    }
                    var w = this.getStyleVal(side + '-frame_width', data),
                            h = this.getStyleVal(side + '-frame_height', data);
                    if (w) {
                        var unit = this.getStyleVal(side + '-frame_width_unit', data);
                        if (!unit) {
                            unit = '%';
                        }
                        selector += 'width:' + w + unit + ';';
                    }
                    else {
                        delete data[side + '-frame_width'];
                        delete data[side + '-frame_width_unit'];
                    }
                    if (h) {
                        var unit = this.getStyleVal(side + '-frame_height_unit', data);
                        if (!unit) {
                            unit = '%';
                        }
                        selector += 'height:' + h + unit + ';';
                    }
                    else {
                        delete data[side + '-frame_height'];
                        delete data[ side + '-frame_height_unit'];
                    }
                    var repeat = this.getStyleVal(side + '-frame_repeat', data);
                    if (repeat) {
                        var rep = 0.1 + (100 / repeat);

                        if (side === 'left' || side === 'right') {
                            selector += 'background-size:100% ' + rep + '%;';
                        }
                        else {
                            selector += 'background-size:' + rep + '% 100%;';
                        }
                    }
                    else {
                        delete data[side + '-frame_repeat'];
                    }
                    return selector;
                }

            },
            multiColumns: function (id, type, args, data) {
                if (args.prop !== 'column-count') {
                    return false;
                }
                var v = this.getStyleVal(id, data),
                        selector = false;
                if (v) {
                    selector = args.prop + ':' + v + ';';
                    var gap = this.getStyleVal(id + '_gap', data),
                            w = this.getStyleVal(id + '_divider_width', data);
                    if (gap !== '' && gap !== undefined) {
                        selector += 'column-gap:' + gap + 'px;';
                    }
                    else {
                        delete data[id + '_gap'];
                    }
                    if (w) {
                        var s = this.getStyleVal(id + '_divider_style', data),
                                c = this.getStyleVal(id + '_divider_color', data);
                        selector += 'column-rule:' + w + 'px ';
                        selector += s ? s : 'solid';
                        selector += c !== '' && c !== undefined ? ' ' + this.toRGBA(c) : '';
                        selector += ';';
                    }
                    else {
                        delete data[id + '_divider_color'];
                        delete data[id + '_divider_width'];
                        delete data[id + '_divider_style'];
                    }
                }
                else {
                    delete data[id];
                    delete data[id + '_gap'];
                    delete data[id + '_divider_color'];
                    delete data[id + '_divider_width'];
                    delete data[id + '_divider_style'];
                }
                return selector;
            },
            height: function (id, type, args, data) {
                var prop = 'height', v, selector;
                if ('auto' === this.getStyleVal(id + '_auto_height', data)) {
                    selector = prop + ':auto;';
                } else {
                    v = this.getStyleVal(id, data);
                    if (!v) {
                        return false;
                    }
                    var unit = this.getStyleVal(id + '_unit', data);
                    if (!unit) {
                        unit = 'px';
                    }
                    selector = prop + ':' + v + unit + ';';
                }
                return selector;
            },
            filters: function (id, type, args, data) {
                var ranges = {
                        hue: {
                            unit: 'deg',
                            prop: 'hue-rotate'
                        },
                        saturation: {
                            unit: '%',
                            prop: 'saturate'
                        },
                        brightness: {
                            unit: '%',
                            prop: 'brightness'
                        },
                        contrast: {
                            unit: '%',
                            prop: 'contrast'
                        },
                        invert: {
                            unit: '%',
                            prop: 'invert'
                        },
                        sepia: {
                            unit: '%',
                            prop: 'sepia'
                        },
                        opacity: {
                            unit: '%',
                            prop: 'opacity'
                        },
                        blur: {
                            unit: 'px',
                            prop: 'blur'
                        }
                    },
                    cssValue = '';
                var subSets = Object.keys(ranges);
                for (var i = 0, len = subSets.length; i < len; ++i) {
                    var v = this.getStyleVal(args.id + '_' + subSets[i], data);
                    if (!v) {
                        delete data[args.id + '_' + subSets[i]];
                        continue;
                    }
                    cssValue += ranges[subSets[i]].prop + '(' + v + ranges[subSets[i]].unit + ') ';
                }
                if('' === cssValue){
                    return false;
                }
                return 'filter:' + cssValue + ';';
            },
            text: function (id, type, args, data) {
                var v = this.getStyleVal(id, data),
                    selector = false;
                if (v !== undefined && v !== '') {
                    selector = args.prop + ':' + v + ';';
                }
                return selector;
            },
            number:function (id, type, args, data) {
                return  this.fields['text'].call(this,id, type, args, data);
            },
            width: function (id, type, args, data) {
                var prop = args.prop, v, selector,
                    v = this.getStyleVal(id , data);
                if ('auto' === v) {
                    selector = 'width:auto;';
                } else {
                    if (!v || ('width' === prop && 'auto' === this.getStyleVal(id+'_auto_width' , data))) {
                    return false;
                }
                    var unit = this.getStyleVal(id + '_unit', data);
                    if (!unit) {
                        unit = 'px';
                    }
                    selector = prop + ':' + v + unit + ';';
                }
                return selector;
            },
            position: function (id, type, args, data) {
                var result,
                    v = this.getStyleVal(id, data);
                if('' === v ){
                    return false;
                }
                result = 'position:' + v + ';';
                if('absolute' === v || 'fixed' === v){
                    var pos = ['top','right','bottom','left'],
                        auto,
                        val;
                    for(var i = pos.length-1;i>=0;--i){
                        auto = this.getStyleVal(id+'_'+pos[i]+'_auto', data);
                        if('auto' === auto){
                            val = 'auto';
                        }else{
                            val = this.getStyleVal(id+'_'+pos[i], data);
                            val = '' !== val && !isNaN(val) ? val + this.getStyleVal(id+'_'+pos[i] + '_unit', data) : '';
                        }
                        result += '' !== val ? pos[i] + ':' + val + ';':'';
                    }
                }
                return result;
            }
        },
        cleanUnusedStyles: function (items) {
            for (var i in items) {
                var suffix,
                        opt = [],
                        type = Rules[i] !== undefined ? Rules[i].type : null,
                        replaceHover = function (str) {
                            if (suffix !== '') {
                                str = str.split('_');
                                var nstr = [];
                                for (var i = 0, len = str.length; i < len; ++i) {
                                    nstr.push(str[0]);
                                }
                                return nstr.join('_') + '_' + suffix;
                            }
                            return str;
                        };
                if (i === 'background_type' || i === 'b_t_h' || i === 'background_image' || i === 'b_i_h') {
                    if (i === 'background_image' || i === 'b_i_h') {
                        suffix = i === 'b_i_h' ? 'h' : '';
                        if (items[replaceHover('background_image')]) {
                            continue;
                        }
                        else {
                            i = suffix === 'h' ? 'b_t_h' : 'background_type';
                            items[i] = 'image';
                        }
                    }
                    suffix = i === 'b_t_h' ? 'h' : '';

                    if (items[i] !== 'gradient') {
                        var prefix = replaceHover('background_gradient');
                        opt = [prefix + '-circle-radial', prefix + '-gradient', prefix + '-gradient-angle', prefix + '-gradient-type'];
                    }
                    else {
                        opt = [replaceHover('background_color')];
                    }
                    if (items[i] !== 'slider') {
                        opt.push(replaceHover('background_slider'));
                        opt.push(replaceHover('background_slider_size'));
                        opt.push(replaceHover('background_slider_mode'));
                        opt.push(replaceHover('background_slider_speed'));
                    }
                    if (items[i] !== 'video') {
                        opt.push('background_video_options');
                        opt.push('background_video');
                    }
                    var img = replaceHover('background_image');
                    if (items[i] !== 'image' || !items[img]) {
                        if (items[i] !== 'video') {
                            opt.push(img);
                        }
                        opt.push(replaceHover('background_repeat'));
                        opt.push(replaceHover('background_zoom'));
                        opt.push(replaceHover('background_position'));
                        opt.push(replaceHover('background_attachment'));
                    }
                    for (var j = opt.length - 1; j > -1; --j) {
                        delete items[opt[j]];
                    }
                }
                else if (i === 'background_attachment_inner' || i === 'b_a_i_h') {
                    suffix = i === 'b_a_i_h' ? 'h' : '';
                    var prefix = replaceHover('background_image_inner');
                    if (!items[prefix]) {
                        opt = [prefix, replaceHover('background_repeat_inner'), replaceHover('background_position_inner'), i];
                        for (var j = opt.length - 1; j > -1; --j) {
                            delete items[opt[j]];
                        }
                    }
                }
                else if (i === 'cover_color-type' || i === 'cover_color_hover-type') {
                    var is_hover = i === 'cover_color_hover-type';
                    if ((is_hover === true && items[i] === 'hover_color') || (is_hover === false && items[i] === 'color')) {
                        var prefix = 'cover_gradient';
                        if (is_hover === true) {
                            prefix += '_hover';
                        }
                        opt = [prefix + '-circle-radial', prefix + '-gradient', prefix + '-gradient-angle', prefix + '-gradient-type'];
                    }
                    else {
                        opt = is_hover === true ? ['cover_color_hover'] : ['cover_color'];
                    }
                    for (var j = opt.length - 1; j > -1; --j) {
                        delete items[opt[j]];
                    }
                }
                else if (type === 'radio' && i.indexOf('-frame_type') !== -1) {
                    var id = i.replace('-frame_type', ''),
                            found = false;
                    if (items[i] === id + '-presets') {
                        opt = [id + '-frame_custom'];
                        found = !items[id + '-frame_layout'] || items[id + '-frame_layout'] === 'none';
                    }
                    else {
                        opt = [id + '-frame_layout'];
                        opt.push(id + '-frame_color');
                        found = !items[id + '-frame_custom'];

                    }
                    if (found) {
                        opt.push(id + '-frame_color');
                        opt.push(id + '-frame_width');
                        opt.push(id + '-frame_width_unit');
                        opt.push(id + '-frame_height');
                        opt.push(id + '-frame_height_unit');
                        opt.push(id + '-frame_repeat');
                        opt.push(id + '-frame_location');
                    }
                    for (var j = opt.length - 1; j > -1; --j) {
                        delete items[opt[j]];
                    }
                }
                else if (i === 'background_image-type' || i === 'b_i_h-type') {
                    suffix = i === 'b_i_h-type' ? 'h' : '';
                    var prefix,
                            img = replaceHover('background_image');
                    if (items[i] === 'image') {
                        prefix = replaceHover('background_image');
                        opt = [prefix + '-circle-radial', prefix + '-gradient', prefix + '-gradient-angle', prefix + '-gradient-type'];
                    }
                    if (items[i] !== 'image' || !items[img]) {
                        opt = [img, replaceHover('background_repeat'), replaceHover('background_position')];
                        if (items[i] !== 'image') {
                            opt.push(replaceHover('background_color'));
                        }
                    }
                    for (var j = opt.length - 1; j > -1; --j) {
                        delete items[opt[j]];
                    }
                }
                else if (type === 'multiColumns' && i.indexOf('_count') !== -1) {
                    var id = i.replace('_count', '');
                    if (!items[id]) {
                        opt = [id, id + '_gap', id + '_divider_color', id + '_divider_width', id + '_divider_style'];
                        for (var j = opt.length - 1; j > -1; --j) {
                            delete items[opt[j]];
                        }
                    }
                }
                else if (items[i] && i.indexOf('_apply_all') !== -1 && i.indexOf('checkbox_') === 0) {
                    var id = i.replace('_apply_all', '').replace('checkbox_', '');
                    if (Rules[id] !== undefined && Rules[id].type === id) {
                        opt = ['right', 'bottom', 'left'];
                        for (var j = 0; j < 3; ++j) {
                            delete items[id + '_' + opt[j]];
                        }
                    }
                }
                else if (items[i] === 'all' && i.indexOf('-type') !== -1) {
                    var id = i.replace('-type', ''),
                            options = ['color', 'style', 'width'],
                            len2 = options.length;
                    opt = ['right', 'bottom', 'left'];
                    for (var j = opt.length - 1; j > -1; --j) {
                        for (var k = len2 - 1; k > -1; --k) {
                            delete items[id + '_' + opt[j] + '_' + options[k]];
                        }
                    }
                }
                else if ((i === 'element_font_weight' || i === 'e_f_w_h') && items[i] == 400) {
                    delete items[i];
                }
                else if (type === 'font_select' && (!items[i] || items[i] === 'default')) {
                    delete items[i];
                    delete items[i + '_w'];
                }
                else if (i.indexOf('breakpoint_') !== -1) {
                    items[i] = this.cleanUnusedStyles(items[i]);
                }
            }
            return items;
        }
    };
    if (typeof ThemifyBuilderStyle !== 'undefined') {
        var points = Object.keys(ThemifyBuilderStyle.points).reverse();
        points.push('desktop');
        ThemifyStyles.init(ThemifyBuilderStyle.styles, points);
        ThemifyBuilderStyle.styles = points = null;
        if (ThemifyBuilderStyle.google !== undefined) {
            var fonts = ThemifyBuilderStyle.google;
            ThemifyBuilderStyle.google = {};
            for (var i = fonts.length - 1; i > -1; --i) {
                if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                    ThemifyBuilderStyle.google[fonts[i].value] = {'n': fonts[i].name, 'v': fonts[i].variant};
                }
            }
            fonts = null;
        }
        if (ThemifyBuilderStyle.cf !== undefined) {
            var fonts = ThemifyBuilderStyle.cf;
            ThemifyBuilderStyle.cf = {};
            for (var i = fonts.length - 1; i > -1; --i) {
                if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                    ThemifyBuilderStyle.cf[fonts[i].value] = {'n': fonts[i].name, 'v': fonts[i].variant};
                }
            }
            fonts = null;
        }
        var Regenerate = function () {
            for (var k in window) {
                if (k.indexOf('themify_builder_data_') === 0 && window[k]!==null) {
                        
                    var id = k.replace('themify_builder_data_', '');
                        ThemifyStyles.builder_id=id;
                    var css = ThemifyStyles.createCss(window[k], null, true,window['themify_builder_gs_'+id]),
                            cssFonts = {fonts:[],cf_fonts:[]},
                            item = document.getElementById('themify_builder_content-' + id),
                            d = document.createDocumentFragment();
                    window[k] = null;
                    if(css['gs']!==undefined){
                        for (var i in css['gs']) {
                            var st = document.createElement('style'),
                                cssText = '';
                                st.type = 'text/css';
                                st.id = 'tb_temp_global_styles_' + id;
                                if (i !== 'desktop') {
                                    var w = ThemifyBuilderStyle.points[i];
                                    if (i !== 'mobile') {
                                            w = w[1];
                                    }
                                    st.media = 'screen and (max-width:' + w + 'px)';
                                }
                                for (var j  in css['gs'][i]) {
                                        cssText += j + '{' + css['gs'][i][j].join(' ') + '}';
                                }
                                st.appendChild(document.createTextNode(cssText));
                                d.appendChild(st);
                         }
                    }
                    for (var i in css) {
                        if(i!=='gs'){
                            if (i !== 'fonts' && i !== 'cf_fonts') {
                                var st = document.createElement('style'),
                                    cssText = '';
                                st.type = 'text/css';
                                st.id = 'tb_temp_styles_' + id;
                                if (i !== 'desktop') {
                                        var w = ThemifyBuilderStyle.points[i];
                                        if (i !== 'mobile') {
                                                w = w[1];
                                        }
                                        st.media = 'screen and (max-width:' + w + 'px)';
                                }
                                for (var j  in css[i]) {
                                        cssText += j + '{' + css[i][j].join(' ') + '}';
                                }
                                st.appendChild(document.createTextNode(cssText));
                                d.appendChild(st);
                            }
                                else {
                                    for (var j in css[i]) {
                                        var f = j.split(' ').join('+');
                                        if (css[i][j].length > 0) {
                                                f += ':' + css[i][j].join(',');
                                        }
                                        cssFonts[i].push(f);
                                    }
                                }
                        }
                    }
                    var fontKeys = Object.keys(cssFonts);
                    for(var key = fontKeys.length-1;key>=0;--key){
                        if (cssFonts[fontKeys[key]].length > 0) {
                            var url = 'fonts' === fontKeys[key] ? '//fonts.googleapis.com/css?family=' + cssFonts[fontKeys[key]].join('|') + '&subset=latin': tbLocalScript.cf_api_url + cssFonts[fontKeys[key]].join('|');
                            Themify.LoadCss(url, false);
                        }else {
                            delete css[fontKeys[key]];
                    }
                    }
                    cssFonts = null;
                    document.head.appendChild(d);
                    if (item !== null) {
                        item.style['visibility'] = item.style['opacity'] = '';
                        item.classList.remove('tb_generate_css');
                    }
                    var xhr = new XMLHttpRequest(),
                            data = {
                                css: JSON.stringify(css),
                                action: 'tb_generate_on_fly',
                                tb_load_nonce: ThemifyBuilderStyle.nonce,
                                id: id
                            },
                    body = '';
                    for (var i in data) {
                        if (body !== '') {
                            body += '&';
                        }
                        body += encodeURIComponent(i) + '=' + encodeURIComponent(data[i]);
                    }
                    data = null;
                    xhr.open('POST', ThemifyBuilderStyle.ajaxurl);
                    xhr.responseType = 'json';
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    xhr.send(body);
                }
            }

        };
        document.addEventListener('DOMContentLoaded',Regenerate );
        document.addEventListener('tb_regenerate_css',Regenerate );
    }else if(window['themifyBuilder']!==undefined){
        if (themifyBuilder.google !== undefined) {
            var fonts = themifyBuilder.google;
            themifyBuilder.google = {};
            for (var i = fonts.length - 1; i > -1; --i) {
                if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                    themifyBuilder.google[fonts[i].value] = {'n': fonts[i].name, 'v': fonts[i].variant};
                }
            }
            fonts = null;
        }
        if (themifyBuilder.cf !== undefined) {
            var fonts = themifyBuilder.cf;
            themifyBuilder.cf = {};
            for (var i = fonts.length - 1; i > -1; --i) {
                if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                    themifyBuilder.cf[fonts[i].value] = {'n': fonts[i].name, 'v': fonts[i].variant};
                }
            }
            fonts = null;
        }
    }
})(window, document, undefined);
