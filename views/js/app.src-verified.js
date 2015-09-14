/*
 * This module hooks into the newOrder to add the customers
 * @author	 GetResponse
 * @copyright  GetResponse
 * @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if('undefined'===typeof global){var global=this;}
window.onerror=function(msg,url,line){'use strict';return!!(global.localStorage&&'on'===global.localStorage.support);};function log(){'use strict';var c=global.console;if(!c){return;}
if(c.log.apply){c.log.apply(c,arguments);}else{c.log(Array.prototype.join.call(arguments,', '));}}
(function(obj,fn,arr,str){'use strict';var hasOwn=obj.hasOwnProperty,toString=obj.toString,slice=arr.slice,define;fn.setter=function(){var that=this;return function(a,b){var k,ln;if(!a){return this;}
if('string'===typeof a){that.call(this,a,b);return this;}
if(a.length&&a.forEach){for(k=0,ln=a.length;k<ln;k+=1){that.call(this,a[k],k);}
return this;}
for(k in a){if(a.hasOwnProperty(k)){that.call(this,k,a[k]);}}
return this;};};fn.getter=function(){var that=this;return function(a){var args,result,i;if('string'!==typeof a){args=a;}else if(arguments.length>1){args=arguments;}
if(args){result={};for(i=0;i<args.length;i+=1){result[args[i]]=that.call(this,args[i]);}}else{result=that.call(this,a);}
return result;};};define=function(name,value){var isDescriptor;if(hasOwn.call(this,name)||this[name]||undefined===value){return this;}
isDescriptor=('[object Object]'===toString.call(value)&&undefined!==value.value);try{Object.defineProperty(this,name,(isDescriptor?value:{value:value,configurable:true,enumerable:false,writable:true}));}catch(ex){this[name]=(isDescriptor?value.value:value);}
return this;}.setter();define.call(fn,{assign:function(name,value){define.call(this,name,value);}.setter(),implement:function(name,value){define.call(this.prototype,name,value);}.setter()});fn.augment=fn.assign;Object.assign({is:function(){if(a===b){if(a===0){return 1/a===1/b;}
return true;}
return global.isNaN(a)&&global.isNaN(b);},assign:function(target,source){var keys=Object.keys(source);keys.forEach(function(key){target[key]=source[key];},target);return target;},mixin:function(target,source){var property;try{Object.keys(source).forEach(function(property){Object.defineProperty(target,property,Object.getOwnPropertyDescriptor(source,property));});}catch(ex){for(property in source){if(source.hasOwnProperty(property)){target[property]=source[property];}}}
return target;},create:function(proto){function F(){}
F.prototype=proto;if(proto){F.prototype.constructor=F;}
return new F();},keys:function(o){var ret=[],k;for(k in o){if(Object.prototype.hasOwnProperty.call(o,k)){ret.push(k);}}
return ret;}});Function.implement({bind:function(context){var that=this,args=slice.call(arguments,1);return function(){return that.apply(context,args.concat(slice.call(arguments)));};},memoize:function(){var oThis=this,cache={};return function(){var args=Array.prototype.slice.call(arguments),key=JSON.stringify(args);if(key in cache){return cache[key]}else{return cache[key]=oThis.apply(oThis,args);}};},pass:function(args,bind){var oThis=this;args=!args?[]:slice.call(args);return function(){return oThis.apply(bind||global,args||arguments);};},delay:function(time,bind,args){return setTimeout(this.pass((!args?[]:args),bind),time);},cyclic:function(time,bind,args){return setInterval(this.pass((!args?[]:args),bind),time);}});String.implement({trim:function(){return this.replace(/(^[\s\xA0]+|[\s\xA0]+$)/g,'');},trimToSpace:function(){return this.replace(/(\s+)/mg,' ').trim();},toCamelCase:function(){return String(this).replace(/-\D/g,function(match){return match.charAt(1).toUpperCase();});},toDash:function(){return this.replace(/([A-Z])/g,function(match){return'-'+match.toLowerCase();});},ucfirst:function(){var f=this.charAt(0).toUpperCase();return f+this.substr(1);},test:function(regexp){return regexp.test(this);},toInt:function(base){return parseInt(this,base||10);},toFloat:function(){return parseFloat(this);}});Array.assign({isArray:function(o){return Object.prototype.toString.call(o)==='[object Array]'||(o instanceof Array);},of:function(){return slice.call(arguments);},from:function(arrayLike,mapfn,thisArg){if(!Object(arrayLike).length){return[];}
return arr.map.call(arrayLike,('function'===typeof mapfn?mapfn:function(item){return item;}),thisArg);}});Array.implement({indexOf:function(item,start){var i=0,ln=this.length;start=start||0;for(i=start;i<ln;i+=1){if(this[i]===item){return i;}}
return-1;},forEach:function(fn,bind){var i,l;if('function'!==typeof fn){throw new TypeError();}
for(i=0,l=this.length;i<l;i+=1){if(this[i]){fn.call(bind,this[i],i,this);}}},filter:function(fn,bind){var ln=this.length,ret=[],i;if('function'!==typeof fn){throw new TypeError();}
for(i=0;i<ln;i+=1){if(fn.call(bind,this[i],i,this)){ret.push(this[i]);}}
return ret;},every:function(fn,bind){var i,ln=this.length;for(i=0;i<ln;i+=1){if((this.hasOwnProperty&&this.hasOwnProperty(i))&&!fn.call(bind,this[i],i,this)){return false;}}
return true;},map:function(fn,bind){var ln=this.length,results=[],i;results.length=ln;for(i=0;i<ln;i+=1){if(this.hasOwnProperty&&this.hasOwnProperty(i)){results[i]=fn.call(bind,this[i],i,this);}}
return results;},some:function(fn,bind){var i,ln=this.length;for(i=0;i<ln;i+=1){if((this.hasOwnProperty&&this.hasOwnProperty(i))&&fn.call(bind,this[i],i,this)){return true;}}
return false;},contains:{value:function(item){return this.indexOf(item)>-1;},writable:true,enumerable:false,configurable:true},empty:{value:function(){this.length=0;return this;},writable:true,enumerable:false,configurable:true}});Number.implement({limit:function(min,max){return Math.min(max,Math.max(min,this));}});Date.augment({now:function(){return+new Date();}});define.call(JSON,{evalToObject:function(str){try{if(/^[\{|\[].*[\]\}]/.test(str)){str='('+str+')';}else{str='({'+str+'})';}
return eval(str);}catch(ex){return'';}},splitToParse:function(str,glue){var sp,tmp,ln,i,ret={};if(!str){return null;}
sp=str.split(glue||',');for(i=0,ln=sp.length;i<ln;i+=1){tmp=sp[i].split(':');if(tmp[0]){ret[tmp[0].trim()]=(tmp.slice(1).join(':')||'').trim();}}
return ret;}});}(Object.prototype,Function.prototype,Array.prototype,String.prototype));function APP(){'use strict';var args=Array.prototype.slice.call(arguments),fn=args[args.length-1],box=APP.box;if(APP.beta){if(1<args.length&&'function'===typeof fn){fn=args.pop();args.push(fn.bind(box));return APP.require.apply(APP,args);}}
switch(APP.typeOf(args[0])){case'string':if(!args[0]){break;}
if(32===args[0].length&&/^[a-zA-Z0-9]+$/i.test(args[0])){APP.require.apply(APP,args);break;}
if(APP.core.hasModule(args[0])){APP.core.start.apply(APP.core,args);break;}
return APP.autoload.apply(APP,args);case'function':if(APP.beta){return fn.call(box);}
box.domready(args[0]);}
return box;}
APP.version='1.5 beta';(function(app){'use strict';var hasOwn=Object.prototype.hasOwnProperty,toString=Object.prototype.toString,slice=Array.prototype.slice,box,mediator,filesMD5={},filesMap={};function Namespace(context){if(!(this instanceof Namespace)){return new Namespace(context);}
this.context=context||this;if(this.context.length){this.length=this.context.length;}}
function Observer(){if(!(this instanceof Observer)){return new Observer();}}
function Sandbox(ext){if(!(this instanceof Sandbox)){return new Sandbox();}}
mediator=(function(){var register=new Namespace(),channels=new Namespace(Object.create(Array.prototype)),handles=[],mediator,ln,i;return{subscribe:function(name,handle){var ix,k;if(name&&handle&&'object'===typeof handle){for(k in handle){if(handle.hasOwnProperty(k)){this.subscribe((name+'.'+k),handle[k]);}}
return;}
if('function'!==typeof handle){return this;}
if(handles&&!handles.contains(handle)){ix=handles.push(handle);channels.setArrayItem(name,(ix-1));}
return this;}.setter(),publish:function(name){var args=slice.call(arguments,1),results=[],list;if(!name){log(channels);}
list=channels.getItem(name)||[];for(i=0,ln=list.length;i<ln;i+=1){try{results.push(handles[list[i]].apply(this,args));}catch(ex){log('Mediator publish error: '+ex+' | channel name: '+name);}}
return results.length>1?results:results[0];},setItem:function(name,value){return register.setItem(name,value);},getItem:function(name){return register.getItem(name);},removeItem:function(name){return register.removeItem(name);}};}());Namespace.implement({setItem:function(name,value){var parent=this,parts=name.split('.'),last=parts.pop(),ln=parts.length,i;for(i=0;i<ln;i+=1){parent=parent[parts[i]]=parent[parts[i]]||{};}
if((typeof parent).test(/string|number|boolean|undefined/)){throw new Error('Type is not object');}
parent[last]=value;}.setter(),getItem:function(name){var parts,parent=this,ln,i;if(!name){return parent;}
parts=name.split('.');for(i=0,ln=parts.length;i<ln;i+=1){parent=parent[parts[i]];if(!parent){return;}}
return parent;},removeItem:function(name){var parts=name.split('.'),last=parts.pop(),parent=Namespace.prototype.getItem.call(this,parts.join('.'));return delete parent[last];},hasItem:function(name){return!!Namespace.prototype.getItem.call(this,name);},setArrayItem:function(name,value){var parts=name.split('.'),parent=this,ln=parts.length,i;for(i=0;i<ln;i+=1){parent=parent[parts[i]]=parent[parts[i]]||[];if(!parent.contains(value)){parent.push(value);}}}});Observer.assign({handles:{}});Observer.implement({on:function(type,handle){var handles,ix,k;if(!this.uid){this.uid=APP.UID();handles=Observer.handles[this.uid]=[];}
if(type&&handle&&'object'===typeof handle){for(k in handle){if(handle.hasOwnProperty(k)){this.on((type+'.'+k),handle[k]);}}
return;}
if('function'!==typeof handle){return this;}
ix=handles.indexOf(handle);if(-1===ix){ix=handles.push(handle)-1;}
Namespace.prototype.setArrayItem.call(this,type,ix);return this;},off:function(type,handle){var handles=Observer.handles[this.uid],ix,list;if(!handles){return;}
if(!handle){Namespace.prototype.removeItem.call(this,type);return;}
ix=handles.indexOf(handle);if(-1!==ix){this[type].splice(this[type].indexOf(ix),1);}
if(0===this[type].length){Namespace.prototype.removeItem.call(this,type);}},notify:function(type){var args=slice.call(arguments,1),handles=Observer.handles[this.uid],results=[],list,ln,i;if(!handles){return;}
if(!type){log(this,this.handles);}
list=Namespace.prototype.getItem.call(this,type);for(i=0,ln=list.length;i<ln;i+=1){try{results.push(handles[list[i]].apply(this,args));}catch(ex){log('Observer '+(this.observername||'')+' notify error: '+ex+' | type : '+type);}}
return results.length>1?results:results[0];}});Observer.implement('trigger',Observer.prototype.notify);Sandbox.prototype=Object.create(mediator,{constructor:{value:Sandbox}});Sandbox.implement({plugin:function(name,value){Sandbox.implement(name,value);},define:function(name,value){Namespace.prototype.setItem.call(app.box,name,value);}.setter()});app.box=box=Object.create(Sandbox.prototype);box.plugin({namespace:{add:function(name,value,cx){return Namespace.prototype.setItem.call(cx||this,name,value);},grab:function(name,cx){return Namespace.prototype.getItem.call(cx||this,name);},remove:function(name){return Namespace.prototype.removeItem.call(cx||this,name);}}});app.mixin=app.extend=function(){var args=slice.call(arguments),ob=args[0],ln,i,prop;if('boolean'===typeof ob){ob=args[1];}else{args=slice.call(arguments,1);}
ob=ob||{};for(i=0,ln=args.length;i<ln;i+=1){for(prop in args[i]){if(Object.prototype.hasOwnProperty.call(args[i],prop)){ob[prop]=args[i][prop];}}}
return ob;};app.assign({files:(function(){var ret=Object.create(Array.prototype),a=document.createElement('a');ret.prepareList=function(list){var k;for(k in list){if(hasOwn.call(list,k)){app.files.push(k);filesMD5[k]=list[k];}}};ret.prepareLists=function(list){var k;for(k in list){if(hasOwn.call(list,k)){ret.prepareList(list[k]);}}};ret.getItem=function(name,nocompress){var src;if(name){src=filesMD5[name]||filesMap[name];if(src&&(nocompress||app.files.nocompress)){src=src.replace(/(-rev.*)\.(js|css)$/i,function($0,$1,$2){return[('js'===$2?'.src-verified.async':'src.async'),$2].join('.');});}
return src;}
return app.extend({},filesMD5,filesMap);};ret.setNamespace=function(src,name){var ext;if(32===src.length&&/^[a-zA-Z0-9]+$/i.test(src)){src=APP.files.getItem(src);}
if(!src){return;}
src=src.replace(/\?.*$/,'');ext=/[^.]+$/i.exec(src)[0];if(!ext){return;}
if('string'!==app.typeOf(name)){name=src.replace(/(.*)\/([^/]+)$/,function($0,$1,$2){var n=$1.split('/').pop();if(!/^(core|common)$/i.test(n)){n+='.';}else{n='';}
return n+$2.replace(/(\.src|-rev)(.*)\.(js|css)/i,'');});}
filesMap[name]=src;Namespace.prototype.setItem.call(app.files[ext],name,src);}.setter();ret.js={};ret.css={};ret.nocompress=Boolean(localStorage.filesNoCompress);return ret;}()),typeOf:function(el){var type=toString.call(el),match=/\[.*\s(.*)\]/ig.exec(type);if(match&&match[1]){type=match[1];}
return type.toLowerCase();},each:function(obj,func,context){var k;context=context||obj;if(Array.isArray(obj)){obj.forEach(func,context);}else{for(k in obj){if(hasOwn.call(obj,k)){if(func.call(context,k,obj[k],obj)===false){break;}}}}
return obj;},UID:function(){var now=Date.now(),ch=(now++).toString(36),ret=[],i;for(i=0;i<10;i+=1){ret.push(ch.charAt((Math.floor(Math.random()*10)+0)));}
return ret.join('');},exports:{},namespace:function(ext){return Object.create(Namespace.prototype,ext);},observer:function(ext){return Object.create(Observer.prototype,ext);}});app.assign(mediator);(function(modules){var module=function(name,builder){builder.prototype=box;return{require:function(req){var oThis=this;APP.require(req,function(){oThis.define(req);});},define:function(req){if(!name){app.extend(global.exports,builder.call(builder,box));return;}
modules.setItem(name,{name:name,require:req,define:builder,instance:null});}};},define;define=function(){var args=slice.call(arguments),callback=args.pop(),name=args.shift(),req=(args.length&&args[0]),mod;if(0===arguments.length){return;}
if(Array.isArray(name)&&!req){req=name;name='';}
mod=module(name,callback);if(req){mod.require(req);return;}
mod.define();};app.prototype.define=define;app.core={define:define,start:function(name,data,sandbox){var module=modules.getItem(name);sandbox=sandbox||box;if(module){module.instance=module.define(sandbox);if(module.instance&&module.instance.init){module.instance.init(data);return;}
box.subscribe(name,module.instance);}},startAll:function(){var mod=modules.getItem(),name;for(name in mod){if(mod.hasOwnProperty(name)){this.start(name);}}},stop:function(name){var module=modules.getItem(name);if(module&&module.instance&&module.instance.destroy){module.instance.destroy();module.instance=null;}},stopAll:function(){var mod=modules.getItem(),name;for(name in mod){if(mod.hasOwnProperty(name)){this.stop(name);}}},getSandbox:function(){return APP();},applyMethod:function(name,method,args){var module=modules.getItem(name),md;if(!module.instance){app.core.start(name);}
md=module.instance[method];if(!md){this.throwError('modul '+module.name+' nie ma metody '+method);return;}
return md.apply(module.instance,args);},hasModule:function(name){return modules.hasItem(name);}};}(Object.create(Namespace.prototype)));app.assign({sandbox:(function(){return{plugin:function(name,builder){box.define(name,builder());}};}()),autoload:function(){var args=slice.call(arguments),files=(APP.files&&APP.files.js),def=args.shift(),parts=def.split(':'),names=parts.pop().split('.'),module=names.shift();parts=parts.join('');names=names.join('.');if(!parts){if('js'===names){parts=module+'.'+names;}else{parts=files[module];}}else{if(!APP.files.getItem(parts)){parts+=(parts.lastIndexOf('/')!==(parts.length-1)?'/':'')+(module?module+'.js':'');}}
if(!parts){return;}
APP.require(parts,module?function(){var hasMod=APP.core.hasModule(module),md;if(!hasMod){if(!global[module]){if('function'===typeof module){global[module]=module();}}
md=APP().namespace.grab(names,global.module);if('function'===typeof md){md.apply(global.module,args);}
return;}
if(!names.length||/init|start/.test(names)){APP.core.start(module);return;}
return APP.core.applyMethod(module,names,args);}:undefined);}});}(APP));(function(){'use strict';function insert(where,content){switch(where){case'afterBegin':this.insertBefore(content,this.firstChild);break;case'beforeBegin':this.parentNode.insertBefore(content,this);break;case'afterEnd':this.parentNode.insertBefore(content,this.nextSibling);break;default:this.appendChild(content);break;}}
try{HTMLElement.implement({insertAdjacentHTML:function(where,html){var range,frg;range=document.createRange();frg=range.createContextualFragment(html);insert.call(this,where,frg);},insertAdjacentElement:insert,insertAdjacentText:function(where,text){var textNodeToInsert=document.createTextNode(text);insert.call(this,where,textNodeToInsert);}});}catch(ex){}}());(function(win,doc){'use strict';var slice=Array.prototype.slice,data=[],head=doc.head||doc.getElementsByTagName('head')[0],loc=location,origin=loc.origin||loc.protocol+'//'+loc.host,firstTime=false,require,app=APP();data.setItem=function(value){var ix,src=value.src.replace(origin,'');ix=this.push(src);ix-=1;value.index=ix;this['_'+ix]=value;return ix;};data.getItem=function(src){var ix;src=src.replace(origin,'');ix=this.indexOf(src);if(-1===ix){return;}
return this['_'+ix];};function getFileType(src){var ext;if(!src||'string'!==typeof src){return'';}
src=src.replace(/\?.*$/,'');ext=/[^.]+$/i.exec(src)[0];return'js'===ext?'javascript':'css';}
function applyCallbacks(cb){var fn=cb.shift();while(fn){if('function'===typeof fn){fn();}
fn=cb.shift();}}
function loadFile(reg){if(reg.loaded){applyCallbacks(reg.callbacks);return;}
if(loadFile[reg.type]){loadFile[reg.type](reg);}}
loadFile.javascript=function(reg){var s;s=doc.createElement("script");s.async=true;s[(null===s.onreadystatechange?'onreadystatechange':'onload')]=function(){if(this.readyState&&!(/loaded|complete/.test(this.readyState))){return;}
reg.loaded=true;applyCallbacks(reg.callbacks);};s.onerror=function(){applyCallbacks(reg.callbacks);};s.setAttribute("type","text/javascript");s.setAttribute("src",reg.src);head.appendChild(s);};loadFile.css=function(reg){var s=doc.createElement("link");s.setAttribute("type","text/css");s.setAttribute("rel","stylesheet");s.setAttribute("href",reg.src);if(data.lastCSS){data.lastCSS.parentNode.insertBefore(s,data.lastCSS.nextSibling);}else if(data.firstCSS){data.firstCSS=data.firstCSS.parentNode.insertBefore(s,data.firstCSS);}else{head.appendChild(s);}
data.lastCSS=s;reg.loaded=true;applyCallbacks(reg.callbacks);};function iterator(list,callback){var ln=list.length,iter=function(ix){var el=list[ix];if(ix===ln){if(callback){callback();}
return;}
if('object'===typeof el){if(el.defined&&win[el.defined]){return;}}
APP.require(el.src||el,function(){if(el.callback){el.callback();}
iter(ix+1);});};return iter;}
require=function(){var args=slice.call(arguments),src=args[0],callback=args[1],reg;if(!firstTime){APP.require.register(document.scripts||document.querySelectorAll('script'));APP.require.register(document.styleSheets||document.querySelectorAll('link'));firstTime=true;}
if(!src){return;}
if('string'===typeof src){src=APP.files.getItem(src)||src;reg=data.getItem(src);if(!reg){reg={loaded:false,src:src,origin:src.replace(origin,''),callbacks:[],type:getFileType(src)};if(callback){reg.callbacks.push(callback);}
data.setItem(reg);loadFile(reg);return;}
if(reg.loaded){if(callback){callback();}
return;}
reg.callbacks.push(callback);return;}
iterator(src,callback)(0);};APP.require=require;APP.box.plugin('require',require);APP.require.register=(function(cycle){function regFile(scripts){var ln=scripts.length,i;if(!ln){return;}
for(i=0;i<ln;i+=1){cycle((scripts[i].src||('stylesheet'===scripts[i].rel&&scripts[i].href)||'').replace(/\?.*$/,''));}
if(/link/i.test(scripts[ln-1])){data.firstCSS=scripts[0];data.lastCSS=scripts[ln-1];}else{data.firstJS=scripts[0];data.lastJS=scripts[ln-1];}}
return regFile;}(function(src){if(!src||'string'!==typeof src){return;}
data.setItem({loaded:true,src:src,'static':true,type:getFileType(src)});}));window.DATAFILE=data;}(window,window.document));(function(win,doc){'use strict';var readyBound=false,isReady=false,readyList=[],DOMContentLoaded;function domReady(){var ln,i;if(isReady){return;}
isReady=true;if(readyList){for(i=0,ln=readyList.length;i<ln;i+=1){readyList[i].call(window,[]);}
readyList=[];}}
function doScrollCheck(){if(isReady){return;}
try{doc.documentElement.doScroll('left');}catch(ex){win.setTimeout(doScrollCheck,1);return;}
domReady();}
DOMContentLoaded=doc.addEventListener?function(){doc.removeEventListener('DOMContentLoaded',DOMContentLoaded,false);domReady();}:function(){if('complete'===doc.readyState){doc.detachEvent("onreadystatechange",DOMContentLoaded);domReady();}};function bindReady(){var toplevel;if(readyBound){return;}
readyBound=true;if('complete'===doc.readyState){return domReady();}
if(doc.addEventListener){doc.addEventListener('DOMContentLoaded',DOMContentLoaded,false);win.addEventListener('load',domReady,false);}else if(doc.attachEvent){doc.attachEvent('onreadystatechange',DOMContentLoaded);win.attachEvent('onload',domReady);toplevel=false;try{toplevel=win.frameElement===null;}catch(ex){}
if(doc.documentElement.doScroll&&toplevel){doScrollCheck();}}}
APP.box.plugin('domready',function(fn){bindReady();if(isReady){fn.call(win,APP.box);}else{readyList.push(function(){return fn.call(win,APP.box);});}});APP.domready=APP.box.domready;}(window,window.document));(function(){'use strict';var i18nLoadedList=[];APP.box.define({pKey:(function(){return{translate:{},getItem:function(name,defaultValue){var args=Array.prototype.slice.call(arguments),context,value,isBetaTester=APP.getItem('isBetaTester');if(0===args.length){return this.translate;}
if(2<args.length){switch(APP.typeOf(args[0])){case'object':context=args[0];break;case'string':context=APP.box.namespace.grab(args[0],this.translate);break;}
name=args[1];defaultValue=args[2];value=context[name];}else{value=this.translate[name];}
if('undefined'===typeof value){if(isBetaTester&&log){log(name,'\t',defaultValue||'');}
return defaultValue||name;}
return value;},setItem:function(name,value){var k;if('object'===typeof name){for(k in name){if(Object.prototype.hasOwnProperty.call(name,k)){this.setItem(k,name[k]);}}
return;}
this.translate[name]=value;return this;}.setter(),create:function(ext){var tr=this.translate;this.translate=Object.create(tr);if(ext){this.setItem(ext);}
return this;}};}())});APP.augment({i18n:(function(){return(function(page,isBetaTester,async){var url=this.i18n.url||'';if('string'!==typeof page){page=(window.location.pathname||document.location.pathname).replace('/','');}
url+=page;APP.setItem('isBetaTester',!!isBetaTester);if(i18nLoadedList.contains(url)){return;}
i18nLoadedList.push(url);(function(xhr){var method=null===xhr.onload?'onload':'onreadystatechange';xhr[method]=function(){var data;if(this.readyState===4){if(this.status===200){try{data=JSON.parse(this.responseText);}catch(ex){return;}
if(data.error||!data.table){return;}
APP.box.pKey.setItem(data.table);}}};xhr.open('GET',url,!!async);xhr.send();}(new XMLHttpRequest()));}).assign({url:'/ajax_get_page_translations.html?short_name='});}())});}());if(!APP.getItem('pKey')){APP.setItem('pKey',APP().pKey);}
global.implementationRemoved=global.implementationRemoved||function(comment){"use strict";var stack;comment=comment||'Implementation removed. Remove the use of this function';try{throw global.Error('err');}catch(e){stack=e.stack;}
log('!!! '+comment+' !!!',stack);};(function(head){'use strict';var script;if(global.localStorage){if('on'===global.localStorage.appUpdate){script=head.querySelectorAll('script[src$="app.src.js"], script[src$="app.src-verified.js"], script[src*="app-rev-"]')[0];document.write('<script type="text/javascript" src="'+script.src.replace(/app(\.src|-rev)(.*)\.js/i,'app.new.js')+'"></script>');}}}(document.head||document.getElementsByTagName('head')[0]));