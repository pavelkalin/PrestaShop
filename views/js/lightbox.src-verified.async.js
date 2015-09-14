/*
 * This module hooks into the newOrder to add the customers
 * @author	 GetResponse
 * @copyright  GetResponse
 * @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

APP.core.define('lightbox',function(box){"use strict";var win=window,doc=win.document,create,style,templateBuilder,darkLayer,lightBoxElementPrototype,lightBox,isIE8=!!(doc.selection&&!win.getSelection&&!(/opera/i.test(win.navigator.userAgent))),isIE9=!!(doc.selection&&win.getSelection&&!(/opera/i.test(win.navigator.userAgent))),players,api,galleryProto,imageDir='../modules/getresponse/views/img/',$=box.dom||win.jQuery||win.IX.element,toArray,parseConfig;toArray=function(){var i,ln=this.length,arr=[];for(i=0;i<ln;i+=1){arr[i]=this[i];}
return arr;};parseConfig=function(configString){var activator=this;configString=configString.trim();if(-1===configString.indexOf(':')){return{selector:configString};}
return eval('('+configString+')');};function getBodyScrollPosition(){var documentElement=doc.documentElement,documentBody=self.document.body,position={left:0,top:0};if(documentBody&&documentBody.scrollTop){position.left=documentBody.scrollLeft;position.top=documentBody.scrollTop;}
if(win.pageYOffset){position.left=win.pageXOffset;position.top=win.pageYOffset;}
if(documentElement&&documentElement.scrollTop){position.left=documentElement.scrollLeft;position.top=documentElement.scrollTop;}
return position;}
function createImage(data){var div=doc.createElement('div'),image=new Image(),end=function(){api.hideLoader();div.style.cssText='width: '+this.width+'px; height: '+this.height+'px; margin: '+(data.margin||5)+'px;';api.refresh();};api.showLoader();image.onload=end;image.onerror=end;image.src=data.src;if(data.clickClose){$(div).addClass('clickClose').click(function(){api.close(undefined,arguments);});}
div.appendChild(image);return[div];}
function createIframe(config){var iframe,div=doc.createElement('div'),readyState=isIE8?'onreadystatechange':'onload',defaultConfig={src:'about:blank',name:'lightbox-frame',id:'lightbox-frame',width:'100%',height:'100%',frameborder:'0',marginheight:undefined,marginwidth:undefined,scrolling:'auto',style:'overflow: auto; display: block;'},k;iframe=['<iframe'];for(k in defaultConfig){if(defaultConfig.hasOwnProperty(k)){if(undefined===config[k]){if(undefined===defaultConfig[k]){continue;}
config[k]=defaultConfig[k];}
iframe.push(' '+k+'="'+config[k]+'"');}}
iframe.push('></iframe>');div.innerHTML=iframe.join('');iframe=div.firstChild;if('function'===typeof config.onLoad){iframe[readyState]=function(){if(this.readyState&&!(/loaded|complete/.test(this.readyState))){return;}
config.onLoad(iframe);};}
iframe.src=config.src;if(config.parent){config.parent.appendChild(iframe);}
return[iframe];}
players={players:[],searchPlayerElements:function(elements){var ln,i,playerElements=[],qs,qLn,j;for(i=0,ln=elements.length;i<ln;i+=1){qs=elements[i].querySelectorAll('[data-ytplayer]');for(j=0,qLn=qs.length;j<qLn;j+=1){playerElements.push(qs[j]);}}
return playerElements;},addPlayerToElements:function(playerElements){var i,ln,el,that=this,add=function(el,i){win.setTimeout(function(){var config=eval('('+el.getAttribute('data-ytplayer')+')');config.noLoader=true;that.addPlayer(el,config);el.removeAttribute('data-ytplayer');},i);};for(i=0,ln=playerElements.length;i<ln;i+=1){el=playerElements[i];add(el,i*300);}},addPlayer:function(container,config){var that=this;this.init(function(){var player,onReady;config.events=config.events||{};onReady=config.events.onReady||function(){};if(!config.noLoader){api.showLoader();}
config.events.onReady=function(e){api.hideLoader();return onReady.apply(this,toArray.call(arguments));};win.setTimeout(function(){api.hideLoader();},1000);try{player=new win.YT.Player(container,config);that.players.push(player);}catch(e){if(win.console&&'function'===typeof console.log){console.log(e);}}});},pause:function(){var i,ln=this.players.length,player;for(i=0;i<ln;i+=1){player=this.players[i];if('function'===typeof player.pauseVideo){player.pauseVideo();}}},init:function(callback){var readyFunction=win.onYouTubeIframeAPIReady||function(){},script,firstScript,that=this;if(!(win.YT&&'function'===typeof win.YT.Player)){win.onYouTubeIframeAPIReady=function(){if('function'===typeof callback){callback.call(that);}
readyFunction();};script=doc.createElement('script');script.src=(win.location.protocol||'http:')+"//www.youtube.com/iframe_api";firstScript=doc.getElementsByTagName('script')[0];firstScript.parentNode.insertBefore(script,firstScript);}else if('function'===typeof callback){callback.call(that);}}};function createYoutube(config){var container=doc.createElement('div'),element=doc.createElement('div'),data=config.youtube,margin=config.margin||5;container.appendChild(element);if('auto'===config.width&&undefined!==data.width){config.width=data.width;}
if('auto'===config.height&&undefined!==data.height){config.height=data.height;}
if(margin){config.width=Number(config.width)+2*margin;config.height=Number(config.height)+2*margin;}
element.style.margin=margin+'px '+margin+'px '+margin+'px '+margin+'px';data.playerVars=data.playerVars||{};data.playerVars.rel=data.playerVars.rel||0;data.playerVars.wmode=data.playerVars.wmode||'opaque';data.playerVars.showinfo=data.playerVars.showinfo||0;if(config.preloading){if(data.playerVars&&undefined!==data.playerVars.autoplay){data.autoPlay=data.playerVars.autoplay;delete data.playerVars.autoplay;}
if(data.autoPlay){data.events=data.events||{};data.events.onReady=function(e){container.ytPlayer=e.target;if(data.forceAutoPlay&&e.target&&'function'===typeof e.target.playVideo){win.setTimeout(function(){if(api.isOpen()){e.target.playVideo();}},10);}};}}else{data.playerVars=data.playerVars||{};if(data.autoPlay){data.playerVars.autoplay=1;}}
container.afterAppendCallback=function(){players.addPlayer(element,data);};return[container];}
function createSwf(config){var element=doc.createElement('div'),margin=config.margin||5,data=config.swf,http_var='http://',defAttr={type:'application/x-shockwave-flash',width:0,height:0},defParam={quality:'high',wmode:'transparent',pluginspage:http_var+'www.macromedia.com/go/getflashplayer'},k,attr=data.attributes||defAttr,param=data.parameters||defParam,movie,swf=[];if(isIE8){defAttr.classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000';defAttr.style='visibility: visible;';}
for(k in defAttr){if(defAttr.hasOwnProperty(k)&&undefined===attr[k]){attr[k]=defAttr[k];}}
for(k in defParam){if(defParam.hasOwnProperty(k)&&undefined===param[k]){param[k]=defParam[k];}}
movie=config.src||data.src||param.movie||attr.data;param.movie=attr.data=movie;swf.push('<object');for(k in attr){if(attr.hasOwnProperty(k)){swf.push(' '+k+'="'+attr[k]+'"');}}
swf.push('>');for(k in param){if(param.hasOwnProperty(k)){swf.push('<param name="'+k+'" value="'+param[k]+'" />');}}
swf.push('</object>');if('auto'===config.width&&undefined!==data.attributes.width){config.width=data.attributes.width;}
if(!data.attributes.width){data.attributes.width=config.width;}
if('auto'===config.height&&undefined!==data.attributes.height){config.height=data.attributes.height;}
if(!data.attributes.height){data.attributes.height=config.height;}
if(margin){config.width=Number(config.width)+2*margin;config.height=Number(config.height)+2*margin;}
element.style.margin=margin+'px';element.innerHTML=swf.join('');return[element];}
function prefix(property){return['','-webkit-','-moz-','-o-',''].join(property);}
create=Object.create||function(proto){function F(){}
F.prototype=proto;F.prototype.constructor=F;return new F();};style=(function(){var head=doc.getElementsByTagName('head')[0],css=doc.createElement('style'),sheet;css.type='text/css';head.appendChild(css);sheet=css.sheet||css.styleSheet;return{addRules:function(rules,prefix){var k;prefix=prefix||'';for(k in rules){if(rules.hasOwnProperty(k)){if(sheet.insertRule){sheet.insertRule(prefix+k+' {'+rules[k]+'}',0);}else if(sheet.addRule){sheet.addRule(prefix+k,rules[k],0);}}}}};}());style.addRules({'.lightboxContainer':'position: fixed;'
+'width: 100%;'
+'height: 100%;'
+'top: 0px;'
+'z-index: 100001;'
+'background-color: transparent;'
+'visibility: hidden;'
+'left: -99999em;','.lightboxContainer .lightboxElement > div.close':'position: absolute;'
+'right:0;'
+'z-index: 10000;'
+'display: block;'
+'margin: -11px -11px auto auto;'
+'width: 23px;'
+'height: 23px;'
+'background-image: url("'+imageDir+'closeNoShadow.png");'
+'background-repeat: no-repeat;'
+'cursor: pointer;'
+prefix('border-radius: 11px;')
+prefix('box-shadow: 6px -6px 20px #444;'),'.lightboxContainer .lightboxElement > div.close.hide':'display: none;','.lightboxContainer img.loader':'position: absolute; left: 50%; top: 50%; margin: -16px 0 0 -16px; z-index: 1;','.lightboxContainer img.loader.hide':'display: none;','.lightboxContainer .lightboxElement':'position: absolute;'
+'left: 50%;'
+'top: 50%;'
+'max-width: 100%;'
+'background-color: white;'
+prefix('border-radius: 8px;')
+prefix('box-shadow: 0px 0px 50px #000;')
+(isIE8?'outline: 1px solid black;':''),'.lightboxContainer .lightboxElement > iframe':prefix('border-radius: 8px;'),'.lightboxContainer .lightboxElement > *':'z-index: 2; position: relative;','.lightboxContainer .lightboxElement .hideContent':'visibility: hidden; overflow: hidden; width: 0 !important; height: 0 !important; position: absolute !important;','.lightboxContainer.open':'visibility: visible;'
+'overflow: auto;'
+'left: 0;','.lightboxContainer.fadeIn':'opacity: 0;','.lightboxContainer.open.fadeIn':'opacity: 1;'
+prefix('transition: opacity 0.5s ease;'),'div.lightboxContainer.slideUp':'overflow: hidden;','.lightboxContainer.slideUp .lightboxElement':'top: 5% !important;'
+'min-height: 95%;'
+'bottom: auto;'
+'border-bottom: 0px;'
+prefix('border-radius: 8px 8px 0 0;')
+prefix('transition: margin-top 1.2s;'),'.lightboxContainer.open.slideUp .lightboxElement':'margin-top: -1px !important;','.lightboxDarkLayer':'position: fixed; width: 100%; height: 100%; top: 0px; z-index: 100000; background-color: #000; opacity: 0.4;'+(isIE8?'-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=40)";zoom:1;':''),'.lightboxDarkLayer.close':'display: none;','.lightboxContainer .lightboxElement .gallery':'position: relative; padding: 10px 120px;','.lightboxContainer .lightboxElement .gallery .arrow':'position: absolute;'
+'top: 50%;'
+'margin-top: -25px;'
+'cursor: pointer;'
+'width: 50px;'
+'height: 50px;'
+'display: block;'
+'background-image: url("'+imageDir+'arrows.png");'
+'background-repeat: no-repeat;','.lightboxContainer .lightboxElement .clickClose':'cursor: zoom-out; cursor: -webkit-zoom-out;','.lightboxContainer .lightboxElement .gallery .arrow.back':'left: 35px; background-position: 0 -4px;','.lightboxContainer .lightboxElement .gallery .arrow.foward':'right: 35px; background-position: 0 -104px;','.lightboxContainer .lightboxElement .gallery .arrow.hide':'visibility: hidden;','.lightboxContainer .lightboxElement .gallery > div':'min-height: 50px;','.lightboxHtmlContent':'max-height: 100%;'
+'overflow: auto;'
+'display: none;','.lightboxElement .lightboxHtmlContent':'display: block !important;'});templateBuilder=box.templateBuilder.getInstance({lightBox:'<div class="lightboxContainer" data-define="container" data-close="layer">'
+'<div class="lightboxElement" data-define="element">'
+'<div class="close" data-define="close" data-close="x"></div>'
+'<img class="loader hide" src="'+imageDir+'loader.big.white.gif" data-define="loader" />'
+'</div>'
+'</div>',darkLayer:'<div class="lightboxDarkLayer close"></div>',gallery:'<div class="gallery" data-define="gallery">'
+'<span class="arrow back hide" data-define="back" data-event="back"></span>'
+'<span class="arrow foward" data-define="foward" data-event="foward"></span>'
+'<div data-define="container"></div>'
+'</div>'});darkLayer={builded:false,template:undefined,show:function(){if(this.builded){$(this.template.wrapper).removeClass('close');}
return this;},hide:function(){if(this.builded){$(this.template.wrapper).addClass('close');this.unsetAlmostTransparent();}
return this;},build:function(){if(this.builded){return this;}
this.builded=true;this.template=templateBuilder.build('darkLayer');this.template.insert(doc.body);return this;},setAlmostTransparent:function(){if(this.builded){this.template.wrapper.style.cssText='opacity: 0.01';}
return this;},unsetAlmostTransparent:function(){if(this.builded){this.template.wrapper.style.cssText='';}
return this;}};galleryProto={elements:undefined,template:undefined,actual:undefined,builded:undefined,setElements:function(elements){var container=this.template.container,el,i,ln=elements.length;this.elements=elements;$(elements).addClass('hideContent');$(elements[0]).removeClass('hideContent');this.actual=0;for(i=0;i<ln;i+=1){el=elements[i];container.appendChild(el);}
return this;},attachEvents:function(){var gallery=this.template,galleryContainer=gallery.wrapper,that=this;$(galleryContainer).bind({click:function(e){var target=e.target,type,elements=that.elements,ln=elements.length,actual=that.actual;if(target.hasAttribute('data-event')){type=target.getAttribute('data-event');}else{while(target&&target!==galleryContainer){target=target.parentNode;if(target.hasAttribute('data-event')){type=target.getAttribute('data-event');}}}
if(type){e.stopPropagation();e.preventDefault();actual=Math.max(Math.min(actual,ln-1),0);switch(type){case'foward':$(elements[actual]).addClass('hideContent');actual+=1;actual=Math.max(Math.min(actual,ln-1),0);$(elements[actual]).removeClass('hideContent');if(actual===ln-1){$(target).addClass('hide');}
if(1===actual){$(gallery.back).removeClass('hide');}
api.refresh();break;case'back':$(elements[actual]).addClass('hideContent');actual-=1;actual=Math.max(Math.min(actual,ln-1),0);$(elements[actual]).removeClass('hideContent');if(!actual){$(target).addClass('hide');}
if(actual===ln-2){$(gallery.foward).removeClass('hide');}
api.refresh();break;}
that.actual=actual;}}});return this;},getHtmlElement:function(){return this.template.wrapper;},build:function(){if(this.builded){return this;}
this.builded=true;this.template=templateBuilder.build('gallery');this.attachEvents();return this;}};lightBoxElementPrototype={config:undefined,elements:undefined,galleryInstance:undefined,defaultConfig:{layer:false,gallery:undefined,effect:'none',width:'auto',height:'auto',defaultHeight:50,hideOuterScroll:undefined,additionalClass:undefined,preloading:false,close:['x','layer','esc'],clearCacheOnClose:false,onBeforeOpen:undefined,onOpen:undefined,onBeforeClose:undefined,onClose:undefined,onInit:undefined},setConfig:function(config){var k,thisConfig=this.config;if(!config||'object'!==typeof config){return this;}
if(!thisConfig){this.config={};this.setConfig(this.defaultConfig);thisConfig=this.config;}
for(k in config){if(config.hasOwnProperty(k)){thisConfig[k]=config[k];}}
return this;},init:function(){(this.config.onInit||function(){}).call(this);return this;},prepareElements:function(elements){var config=this.config;if(undefined===config.gallery){config.gallery=elements.length>1;}
if(config.gallery){return[(this.galleryInstance||create(galleryProto)).build().setElements(elements)];}
return elements;},getFragment:function(callback){var that=this,config=this.config,elements=this.elements,afterGetElements,data;if(elements&&elements[0]){if('function'===typeof callback){callback.call(elements);}
return;}
afterGetElements=function(elementsToPrepare){elementsToPrepare=elementsToPrepare||elements;if('function'===typeof callback){if(elementsToPrepare!==that.elements){elements=that.prepareElements(elementsToPrepare);}
that.elements=elements;callback.call(elements);}};if(config.hasOwnProperty('callback')){data=config.callback;if('function'===typeof data){data.call(this,afterGetElements);}}else if(config.hasOwnProperty('image')){elements=createImage(config.image);afterGetElements();}else if(config.hasOwnProperty('youtube')){if(undefined===config.hideOuterScroll){config.hideOuterScroll=true;}
elements=createYoutube(config);afterGetElements();}else if(config.hasOwnProperty('swf')){if(undefined===config.hideOuterScroll){config.hideOuterScroll=true;}
elements=createSwf(config);api.showLoader();afterGetElements();}else if(config.hasOwnProperty('iframe')){data=config.iframe;api.showLoader();data.onLoad=function(){api.hideLoader();api.refresh();if('function'===typeof config.onLoad){config.onLoad();}
win.setTimeout(function(){api.refresh();},1);};elements=createIframe(data);afterGetElements();}else if(config.hasOwnProperty('ajax')){data=config.ajax;elements=[$('<div class="lightboxHtmlContent"><div class="highslide-body"></div></div>')[0]];$.ajax({url:data.url,type:data.method||'get',data:data.data||{},success:function(responseData){var textElements,div,i,ln,el,container=elements[0].firstChild;if(!data.type||'json'===data.type){textElements=responseData.table||responseData||[];}else if('text'===data.type){textElements=[responseData.replace(/^[\s\S]*?<\s*body[\s\S]*?>([\s\S]*)<\s*\/\s*body\s*>[\s\S]*$/mi,function(all,content){return content;})];}
ln=textElements.length;div=doc.createElement('div');for(i=0;i<ln;i+=1){el=textElements[i];div.innerHTML=el;if(!div.hasChildNodes()){continue;}
if(div.childNodes.length>1){el=doc.createDocumentFragment();while(div.hasChildNodes()){el.appendChild(div.firstChild);}}else{el=div.firstChild;}
container.appendChild(el);}
api.refresh();},dataType:data.type||'json'});afterGetElements();}else{data=config.selector;if('string'===typeof data){elements=doc.querySelectorAll(data);if(elements&&elements[0]){elements=toArray.call(elements);}}else{elements=data;}
players.addPlayerToElements(players.searchPlayerElements(elements));afterGetElements();}},clearCache:function(){var elements=this.elements,el,parent,i,ln;if(elements&&elements.length){for(i=0,ln=elements.length;i<ln;i+=1){el=elements[i];parent=el.parentNode;if(parent){parent.removeChild(el);}}
this.elements=[];}},beforeOpen:function(){var config=this.config;if(config.onBeforeOpen){return config.onBeforeOpen.apply(this,toArray.call(arguments));}
return true;},beforeClose:function(){var config=this.config;if(config.onBeforeClose){return config.onBeforeClose.apply(this,toArray.call(arguments));}
return true;},open:function(){var config=this.config,elements=this.elements,el,frame,height,ln,i,playVideo;if(config.youtube){if(isIE9){for(i=0,ln=elements.length;i<ln;i+=1){el=elements[i];frame=el.querySelector('iframe');if(frame){height=Number(frame.height);if(height){frame.height=height+1;frame.height=height;}}}}
if(config.preloading&&config.youtube.autoPlay){for(i=0,ln=elements.length;i<ln;i+=1){el=elements[i];if(el.ytPlayer){if('function'===typeof el.ytPlayer.playVideo){playVideo=el;}}else{config.youtube.forceAutoPlay=true;}}
if(playVideo){win.setTimeout(function(){if(api.isOpen()){playVideo.ytPlayer.playVideo();}},10);}}}
if(config.onOpen){config.onOpen.apply(this,toArray.call(arguments));}},close:function(){var config=this.config;if(config.clearCacheOnClose){this.clearCache();}
if(config.onClose){config.onClose.call(this);}}};lightBox={builded:false,removeOpenEffect:undefined,outerScrollPosition:undefined,actualInstance:undefined,lastElements:undefined,template:undefined,lightboxes:{},effects:{fadeIn:function(){var template=this,wrapper=template.wrapper;$(wrapper).addClass('fadeIn');win.setTimeout(function(){$(wrapper).addClass('open');},20);return function(){$(wrapper).removeClass('fadeIn');$(wrapper).removeClass('open');};},slideUp:function(){var template=this,wrapper=template.wrapper,element=template.element;win.setTimeout(function(){element.style.marginTop='100%';wrapper.style.overflow='hidden';win.setTimeout(function(){$(wrapper).addClass('slideUp');win.setTimeout(function(){$(wrapper).addClass('open');win.setTimeout(function(){wrapper.style.cssText='';},1200);},20);},20);},20);return function(){$(wrapper).removeClass('open');$(wrapper).removeClass('slideUp');};}},build:function(){var that=this;if(this.builded){return this;}
this.builded=true;this.template=templateBuilder.build('lightBox');$(this.template.wrapper).bind({'click.lightbox':function(e){var type=e.target.getAttribute('data-close'),args=toArray.call(arguments);if(type){args.unshift(type);that.close.apply(that,args);}}});return this;},open:function(){var that=this,template=this.template,effect,element=template.element,config,i,ln,applyArg=toArray.call(arguments),instance=applyArg.shift(),escClose;if(!instance.config&&'function'!==typeof instance.getFragment){instance=that.set(instance);}
instance=instance||this.actualInstance;this.actualInstance=instance;if(!instance||false===instance.beforeOpen.apply(instance,applyArg)){return;}
config=instance.config;ln=config.close.length;template.container.removeAttribute('data-close');$(doc).unbind('keydown.lightbox');escClose=function(){$(doc).bind({'keydown.lightbox':function(e){var args=toArray.call(arguments);if(27===e.keyCode){args.unshift('esc');that.close.apply(that,args);}}});};$(template.close).addClass('hide');for(i=0;i<ln;i+=1){switch(config.close[i]){case'layer':template.container.setAttribute('data-close','layer');break;case'esc':escClose();break;case'x':$(template.close).removeClass('hide');break;}}
instance.getFragment(function(){var width,height,i,ln=this.length,el;if(config.layer||isIE8||isIE9){darkLayer.build().show();if(isIE9){darkLayer.setAlmostTransparent();}}
if(config.hideOuterScroll){that.hideBodyScroll();}
for(i=0;i<ln;i+=1){el=this[i];if('function'===typeof el.getHtmlElement){el=el.getHtmlElement();}
if(el.parentNode!==element){element.appendChild(el);if('function'===typeof el.afterAppendCallback){el.afterAppendCallback();}}else{$(el).removeClass('hideContent');}}
that.lastElements=this;width=config.width||640;height=config.height||480;element.style.width='number'===typeof width?width+'px':width;element.style.height='number'===typeof height?height+'px':height;if(template.wrapper.parentNode!==doc.body){template.insert(doc.body);}
element.style.width=element.offsetWidth+'px';element.style.marginLeft=-(element.offsetWidth/2)+'px';element.style.marginTop=-(element.offsetHeight/2)+'px';effect=config.effect;if(config.additionalClass&&'string'===typeof config.additionalClass){$(element).addClass(config.additionalClass);}
if(effect&&'string'===typeof effect&&that.effects[effect]){that.removeOpenEffect=that.effects[effect].call(template);}else{$(template.wrapper).addClass('open');}
that.refresh('string'===typeof config.defaultHeight?config.defaultHeight:config.defaultHeight+'px');instance.open.apply(instance,applyArg);$(win).bind('resize.lightbox',function(){that.refresh();});});},preload:function(instance){var template=this.template;instance.getFragment(function(){var i,ln=this.length,el,element=template.element;for(i=0;i<ln;i+=1){el=this[i];if('function'===typeof el.getHtmlElement){el=el.getHtmlElement();}
if(el.parentNode!==element){element.appendChild(el);if('function'===typeof el.afterAppendCallback){el.afterAppendCallback();}
$(el).addClass('hideContent');}}
if(template.wrapper.parentNode!==doc.body){template.insert(doc.body);}});},close:function(type){var template=this.template,instance=this.actualInstance,config,elements,i,ln,el,applyArg=toArray.call(arguments);if(!instance){return false;}
config=instance.config;if((type&&-1===config.close.indexOf(type))||false===instance.beforeClose.apply(instance,applyArg)){return true;}
darkLayer.hide();players.pause();this.hideLoader();if(this.removeOpenEffect){this.removeOpenEffect.call(template);this.removeOpenEffect=undefined;}else{$(template.wrapper).removeClass('open');}
if(config.additionalClass&&'string'===typeof config.additionalClass){$(template.element).removeClass(config.additionalClass);}
this.showBodyScroll();$(doc).unbind('keydown.lightbox');$(win).unbind('resize.lightbox');elements=this.lastElements;ln=elements.length;for(i=0;i<ln;i+=1){el=elements[i];if('function'===typeof el.getHtmlElement){el=el.getHtmlElement();}
$(el).addClass('hideContent');}
instance.close();return true;},hideBodyScroll:function(){var documentBody=doc.body;this.outerScrollPosition=getBodyScrollPosition();if(isIE8){documentBody.parentNode.style.overflow='hidden';documentBody.scroll='no';}
documentBody.style.overflow='hidden';},showBodyScroll:function(){var documentBody=doc.body,scrollPosition=this.outerScrollPosition;if(scrollPosition){(self||win).scrollTo(scrollPosition.left,scrollPosition.top);}else{return;}
if(isIE8){documentBody.parentNode.style.overflow='';documentBody.removeAttribute('scroll');}
documentBody.style.overflow='';this.outerScrollPosition=undefined;},showLoader:function(){$(this.template.loader).removeClass('hide');},hideLoader:function(){$(this.template.loader).addClass('hide');},refresh:function(defaultHeight){var template=this.template,wrapper=template.wrapper,element=template.element,sourceElement=element,el,cDocument,instance=this.actualInstance,config,width,height,type='offset',widthMax,xButtonDiff;if(!instance){return;}
config=instance.config;width=config.width||640;height=config.height||480;if(instance.elements&&instance.elements[0]){el=instance.elements[0];if(el.tagName&&'iframe'===el.tagName.toLowerCase()&&el.contentDocument){cDocument=el.contentDocument;if(cDocument.lastChild&&cDocument.lastChild.scrollHeight){sourceElement=cDocument.lastChild;}
if(cDocument.body&&cDocument.body.scrollHeight&&cDocument.body.scrollHeight>sourceElement.scrollHeight){sourceElement=cDocument.body;}
type='scroll';}}
xButtonDiff=(element===sourceElement&&-1!==config.close.indexOf('x'))?-11:0;if('auto'===width){element.style.width='auto';widthMax=Math.max(sourceElement.offsetWidth,sourceElement.scrollWidth+xButtonDiff,sourceElement.offsetWidth);element.style.width=Math.round(Math.min(0.97*wrapper.offsetWidth,widthMax))+'px';}
if('auto'===height){if('scroll'===type){element.style.height=defaultHeight||'50px';}else{element.style.height='auto';}
element.style.height=Math.round(Math.min(0.97*wrapper.offsetHeight,sourceElement[type+'Height']))+'px';}
win.clearTimeout(instance.widthAdjTimeout);instance.widthAdjTimeout=win.setTimeout(function(){if('auto'===width){widthMax=Math.max(sourceElement.offsetWidth,sourceElement.scrollWidth+xButtonDiff,sourceElement.offsetWidth);widthMax+=sourceElement.scrollLeftMax||sourceElement.scrollWidth+xButtonDiff-sourceElement.offsetWidth||0;element.style.width=Math.round(Math.min(0.97*wrapper.offsetWidth,widthMax))+'px';}},1);element.style.marginLeft=-Math.round(Math.min(0.97*wrapper.offsetWidth,sourceElement[type+'Width'])/2)+'px';element.style.marginTop=-Math.round(Math.min(0.97*wrapper.offsetHeight,sourceElement[type+'Height'])/2)+'px';},generateName:function(){var name='generateName'+new Date(),i=0,newName;newName=name;while(this.lightboxes[newName]){newName=name+'_'+i;i+=1;}
return newName;},set:function(config,instance){config.name=config.name||(instance&&instance.config&&instance.config.name)||this.generateName();if(!this.lightboxes[config.name]){this.lightboxes[config.name]=(instance||create(lightBoxElementPrototype)).setConfig(config).init();}
return this.lightboxes[config.name];},get:function(name){return this.lightboxes[name];},createEffect:function(name,fn){if('function'===typeof fn){this.effects[name]=fn;}},getApi:function(){var that=this;return{isOpen:function(){return $(that.template.wrapper).hasClass('open');},open:function(configOrLightboxInstance,applyArg){applyArg=(applyArg&&toArray.call(applyArg))||[];applyArg.unshift(configOrLightboxInstance);that.open.apply(that,applyArg);return this;},preload:function(instance){that.preload(instance);return this;},close:function(type,arg){var applyArg=(arg&&toArray.call(arg))||[];applyArg.unshift(type);that.close.apply(that,applyArg);return this;},getLastInstance:function(){return that.actualInstance;},getInstance:function(name){return that.get(name);},setInstance:function(config,instance,callback){instance=that.set(config,instance);if(instance&&'function'===typeof callback){callback.call(instance);}
return instance;},changeInstance:function(name,callback){var instance=that.get(name);if(instance&&'function'===typeof callback){callback.call(instance);}},createInstance:function(object){var instance=create(lightBoxElementPrototype),k;if(object&&'object'===typeof object){for(k in object){if(object.hasOwnProperty(k)){instance[k]=object[k];}}}
return instance;},createGalleryInstance:function(object){var instance=create(galleryProto),k;if(object&&'object'===typeof object){for(k in object){if(object.hasOwnProperty(k)){instance[k]=object[k];}}}
return instance;},showLoader:function(){that.showLoader();return this;},hideLoader:function(){that.hideLoader();return this;},refresh:function(){return that.refresh();},createEffect:function(name,fn){that.createEffect(name,fn);return this;},attachToObject:function(objectsHtmlOrSelector,configOrParamName){var objects='string'===typeof objectsHtmlOrSelector?doc.querySelectorAll(objectsHtmlOrSelector):objectsHtmlOrSelector,i,ln,el,elConfig,addEvents;addEvents=function(lightBoxInstance){$(this).bind({click:function(e){e.preventDefault();e.stopPropagation();try{api.open(lightBoxInstance,arguments);}catch(ex){if(win.console&&console.log){console.log(ex);if(ex.stack){console.log(ex.stack);}}}
return false;},mouseover:function(){var config=lightBoxInstance.config;if(config.preloading){api.preload(lightBoxInstance);}}});};if(undefined===objectsHtmlOrSelector){return;}
if('string'===typeof configOrParamName){if(!/^data-/.test(configOrParamName)){configOrParamName='data-'+configOrParamName;}
for(i=0,ln=objects.length;i<ln;i+=1){el=objects[i];elConfig=parseConfig.call(el,el.getAttribute(configOrParamName));el.removeAttribute(configOrParamName);addEvents.call(el,api.setInstance(elConfig));}}else{addEvents.call(objects,api.setInstance(configOrParamName));}},attachByDataParam:function(param){var that=this;param=param||'lightbox';$(function(){that.attachToObject('[data-'+param+']',param);});},pausePlayers:function(){players.pause();}};}}.build();win.lightbox=api=lightBox.getApi();return api;});