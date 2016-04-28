/*
 * This module hooks into the newOrder to add the customers
 * @author	 GetResponse
 * @copyright  GetResponse
 * @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if(!Object.create){Object.create=function(proto){var F=function(){};F.prototype=proto;return new F();}}
if(!Object.keys){Object.keys=function(o){var ret=[],k;for(k in o){if(Object.prototype.hasOwnProperty.call(o,k)){ret.push(k);}}
return ret;}}
if(!Function.prototype.bind){Function.prototype.bind=function(context){var that=this,slice=Array.prototype.slice,args=slice.call(arguments,1);return function(){return that.apply(context,args.concat(slice.call(arguments)));};};}
(function(proto){if(!Array.isArray){Array.isArray=function(o){return Object.prototype.toString.call(o)==='[object Array]';};}
if(!proto.indexOf){proto.indexOf=function(item,start){var i=0,ln=this.length;start=start||0;for(i=start;i<ln;i+=1){if(this[i]===item){return i;}}
return-1;};}
proto.contains=function(item){return this.indexOf(item)>-1;};})(Array.prototype);(function(proto){if(!Date.now){Date.now=function(o){var d=new Date;return d.getTime();};}})(Date.prototype);(function(proto){if(!proto.trim){proto.trim=function(){return this.replace(/(^[\s\xA0]+|[\s\xA0]+$)/g,'');};}})(String.prototype);if(!String.UID){String.UID=function(){var now=Date.now(),ch=(now++).toString(36),ret=[],i;for(i=0;i<10;i+=1){ret.push(ch.charAt((Math.floor(Math.random()*10)+0)));}
return ret.join('');};}
(function(){var head=document.getElementsByTagName('head')[0],css=document.createElement('style'),sheet,rules={'.getresponse-growler':'position:fixed;z-index:999999;','.growler-cloud':'position:relative;margin:3px 0;opacity:0;-moz-transition:height 1s, opacity 0.25s;-webkit-transition:height 1s, opacity 0.25s;-o-transition:height 1s, opacity 0.25s;-ms-transition:height 1s, opacity 0.25s;transition:height 1s, opacity 0.25s;','.growler-content':'position:relative;padding:11px;color:#fff;font-size: 16px;z-index:1;text-align:center;','.growler-backdrop':'position:absolute;top:0;left:0;background-color:#000;width: 100%;height:100%;opacity:0.56;border:0;-webkit-border-radius:8px;-moz-border-radius:8px;border-radius:8px;z-index:0;'};head.appendChild(css);css.type='text/css';sheet=css.sheet?css.sheet:css.styleSheet;for(k in rules){if(sheet.insertRule){sheet.insertRule(k+' {'+rules[k]+'}',0);}
else if(sheet.addRule){sheet.addRule(k,rules[k],0);}}})();(function(app){var doc=document,hasOwn=Object.prototype.hasOwnProperty,pos=['top','right','bottom','left'],preferences,proto,cloudProto,msgProto,defProto,growler,tools,observer,wrap='<div class="getresponse-growler"><div class="growler-clouds" var="clouds"></div></div>',cloudwrap='<div class="growler-cloud"><div class="growler-content" var="cloud"></div><div class="growler-backdrop"></div></div>';observer={listeners:{},addListener:function(name,fn,overwrite){var ix;if(!this.listeners[name]){this.listeners[name]=[];}
ix=this.listeners[name].indexOf(fn);if('function'===typeof fn&&(ix===-1||overwrite)){ix=ix>-1?ix:this.listeners[name].length;this.listeners[name].splice(ix,1,fn);}
return this;},addListeners:function(ob){var k;if('object'===typeof ob){for(k in ob){this.addListener(k,ob[k]);}}
return this;},removeListener:function(name,fn){var ix;if(this.listeners[name]){ix=this.listeners[name].indexOf(fn);if(ix>-1){this.listeners[name].splice(ix,1);}}
return this;},notify:function(name){var i,ln,list=[],ret;if(!this.listeners[name]){return;}
for(i=0,ln=this.listeners[name].length;i<ln;i+=1){ret=this.listeners[name][i].apply(this,Array.prototype.slice.call(arguments,1));ret&&list.push(ret);}
return list;}};tools={apn:function(str){var div=doc.createElement('div');div.innerHTML=str;return div.children[0];},create:function(html){var dom='string'===typeof html?this.apn(html):html,q=dom.getElementsByTagName('*'),i,ln=q.length,ret={},name;for(i=0;i<ln;i+=1){name=q[i].getAttribute('var');if(name){ret[name]=q[i];q[i].removeAttribute('var');}}
ret.wrapper=dom;return ret;},extend:function(){var arg=arguments,overwrite=false,a=arg[0],ln=arg.length,i=1,prop,b;if('boolean'===typeof a){overwrite=a;a=arg[1];i=2;}
for(;i<ln;i+=1){b=arguments[i];for(prop in b){if(hasOwn.call(b,prop)){a[prop]=b[prop];}}}
return a;},append:function(target,where,insert){var range,frg;if(!target){return;}
where=where||'beforeEnd';if('string'===typeof insert){if(target&&target.insertAdjacentHTML){target.insertAdjacentHTML(where,insert);return target;}
else{range=document.createRange();frg=range.createContextualFragment(insert);}}
else{frg=insert;}
switch(where){case'afterBegin':target.insertBefore(frg,target.firstChild);break;case"beforeBegin":target.parentNode.insertBefore(frg,target);break;case"afterEnd":target.parentNode.insertBefore(frg,target.nextSibling);break;default:target.appendChild(frg);break;}
return target;},removeElement:function(el){el.parentNode.removeChild(el);}};preferences={duration:3600,position:'bottom right',insertPosition:'afterBegin',offset:'0 0 0 0',width:230,btnClose:'hide',appendTo:document.body};defProto=tools.extend(Object.create(observer),{preferences:function(){return Object.create(preferences);}(),changePreferences:function(name,value){var k;if('object'===typeof name){for(k in name){if(hasOwn.call(name,k)){this.preferences[k]=name[k];}}}
else{this.preferences[name]=value;}
return this;},setDefaultPreferences:function(){this.preferences=Object.create(preferences);}});msgProto=tools.extend(Object.create(defProto),{addMessage:function(name,msg,overwrite){if(!this.msg){this.msg={};}
if(this.msg[name]&&!overwrite){throw new Error('Message name: '+name+' exist and is readonly');}
this.msg[name]=msg;},removeMessage:function(name){if(this.msg&&this.msg[name]){delete this.msg[name];}}});cloudProto=tools.extend(Object.create(msgProto),{createCloud:function(msg,css){var cw=tools.create(cloudwrap);cw.cloud.innerHTML=msg;if(css){try{cw.cloud.style.setAttribute('cssText',css);}
catch(ex){cw.cloud.style.cssText=css;}}
return cw.cloud;}});proto=function(target){return{show:function(msg){var oThis=this,cloud,wrap,def;if('object'===typeof msg){def=msg;msg=(def.name&&this.msg&&this.msg[def.name])||def.msg;if(def.value){msg+=def.value;}}
if(!msg){throw new Error('! Growler -- No Message -- !');}
cloud=this.createCloud(msg,def&&def.css);wrap=cloud.parentNode;tools.append(target,this.preferences.insertPosition||'afterBegin',wrap);this.notify('onShow');setTimeout(function(){wrap.style.opacity='1';oThis.hide(cloud);wrap.style.height=wrap.offsetHeight+'px';},10);return this;},hide:function(cloud){var oThis=this,wrap=cloud.parentNode;setTimeout(function(){wrap.style.opacity='0';wrap.style.height='0';oThis.notify('onHide');setTimeout(function(){tools.removeElement(wrap);},1800);},this.preferences.duration);return this;}};};function createWrap(uid,def){var w=tools.create(wrap),p,offset,k;w.wrapper.id='uid-'+uid;if(def){for(k in def){if(hasOwn.call(def,k)){switch(k){case'position':p=def[k].split(' ');offset=function(o){var ln,i;for(i=0,ln=pos.length;i<ln;i+=1){if(p.contains(pos[i])){w.wrapper.style[pos[i]]=o[i]+'px';}}}(def.offset.split(' '));break
case'width':w.wrapper.style.width=def[k]+'px';break;case'className':w.wrapper.className=w.wrapper.className+' '+def.clasName;break;}}}}
return w;}
function growler(def){var ob,uid,pref,container;def.name=def.name||'single';if(def.name&&growler.created.contains(def.name)){return growler.created[def.name];}
ob=Object.create(cloudProto);uid=String.UID();if(def){if(def.position){pref=def.position.split(' ');if(pref.contains('top')){def.insertPosition='beforeEnd';}
else if(pref.contains('bottom')){def.insertPosition='afterBegin';}}
ob.changePreferences(def);}
pref=ob.preferences;container=createWrap(uid,pref);tools.extend(ob,proto(container.clouds));tools.append(ob.preferences.appendTo||document.body,'beforeEnd',container.wrapper);growler.created.push(def.name);growler.created[def.name]=ob;return ob;}
growler.created=[];app.growler=growler;})(jQuery||APP||IX||this);