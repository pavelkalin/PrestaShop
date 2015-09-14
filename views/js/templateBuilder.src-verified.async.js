/*
 * This module hooks into the newOrder to add the customers
 * @author	 GetResponse
 * @copyright  GetResponse
 * @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

(function(name,fn){var win=window;if(win.APP&&APP.core&&APP.sandbox){APP.sandbox.plugin(name,fn);}
win[name]=fn();}('templateBuilder',function(){"use strict";var win=window,doc=win.document,create=Object.create||function(proto){function F(){}
F.prototype=proto;F.prototype.constructor=F;return new F();},translationRun=false,templateBuilderProto,templateProto,methods;templateBuilderProto={getInstance:function(variables){var templateBuilder=create(templateBuilderProto),k;for(k in variables){if(variables.hasOwnProperty(k)){templateBuilder[k]=variables[k];}}
return templateBuilder;},getItem:function(variableName){var item=this,name,i,ln;variableName=variableName.split('.');for(i=0,ln=variableName.length;i<ln;i+=1){name=variableName[i];if(!item.hasOwnProperty(name)){return undefined;}
item=item[name];}
return item;},setItem:function(variableName,variable){var item=this,name,i,ln;variableName=variableName.split('.');for(i=0,ln=variableName.length;i<ln;i+=1){name=variableName[i];if(i===ln-1){item[name]=variable;}else if(!item.hasOwnProperty(name)){item[name]={};}
item=item[name];}
return this;},replaceItemVariables:function(variableName,variables){var item=this.getItem(variableName);if(undefined===item){throw new Error('Zmienna o nazwie: '+variableName+' nie istnieje');}
if('string'!==typeof item){throw new Error('Zmienna o nazwie: '+variableName+' nie jest type string');}
item=item.replace(/\{\{([^:]+?)\}\}/g,function(all,matchWord){var replace=(variables.hasOwnProperty(matchWord)&&variables[matchWord]);if('string'===typeof replace){return replace;}
return all;});this.setItem(variableName,item);return this;},create:function(variableName){var item=this;if(variableName){item=this.getItem(variableName);}
return this.getInstance(item);},build:function(variableName,decorator){var item,template=create(templateProto),fragment,div=doc.createElement('div'),i,ln,buildElements,element,attrib,predecorator,variables;if('object'===typeof variableName){decorator=variableName.decorator;predecorator=variableName.predecorator;variables=variableName.variables;variableName=variableName.name;}
if(decorator&&'object'===typeof decorator){variables=decorator;}
item=this.getItem(variableName);if(undefined===item){throw new Error('Zmienna o nazwie: '+variableName+' nie istnieje');}
if('string'!==typeof item){throw new Error('Zmienna o nazwie: '+variableName+' nie jest type string');}
if(/\{\{translate\.[^:]+?\}\}/.test(item)&&win.APP){if(!translationRun){win.APP.i18n();translationRun=true;}
item=item.replace(/\{\{translate\.([^:]+?)\}\}/g,function(all,matchKey){return APP().pKey.getItem(matchKey,matchKey);});}
if(variables&&'function'!==typeof predecorator){item=item.replace(/\{\{([^:]+?)\}\}/g,function(all,matchWord){var replace=(variables.hasOwnProperty(matchWord)&&variables[matchWord]),type=typeof replace;if('string'===type||'number'===type){return replace;}
return all;});}
if('function'===typeof predecorator){item=predecorator.call(item);}
div.insertAdjacentHTML('beforeEnd',item);if(div.children.length>1){fragment=doc.createDocumentFragment();while(div.children.length){fragment.appendChild(div.children[0]);}}else{fragment=div.children[0];}
template.wrapper=fragment;if(!fragment||!fragment.querySelectorAll){return template;}
if(fragment.getAttribute){attrib=fragment.getAttribute('data-define');if(attrib){template[attrib]=fragment;fragment.removeAttribute('data-define');}}
buildElements=fragment.querySelectorAll('[data-define]');for(i=0,ln=buildElements.length;i<ln;i+=1){element=buildElements[i];attrib=element.getAttribute('data-define');element.removeAttribute('data-define');template[attrib]=element;}
if('function'===typeof decorator){return decorator.call(fragment,template);}
return template;}};templateProto={wrapper:undefined,insert:function(target,where){where=where||'beforeEnd';if(!this.wrapper){return target;}
switch(where){case'afterBegin':target=target.insertBefore(this.wrapper,target.firstChild);break;case"beforeBegin":target=target.parentNode.insertBefore(this.wrapper,target);break;case"afterEnd":target=target.parentNode.insertBefore(this.wrapper,target.nextSibling);break;default:target=target.appendChild(this.wrapper);break;}
return target;},remove:function(){var wrapper=this.wrapper,parent;if(wrapper){parent=wrapper.parentNode;if(parent){wrapper=parent.removeChild(wrapper);}}
return wrapper;}};methods=function(template,variables){var config={};if(variables){if('function'===typeof variables.decorator||'function'===typeof variables.predecorator||(variables.variables&&'object'===typeof variables.variables)){config={decorator:variables.decorator,predecorator:variables.predecorator,variables:variables.variables};}else{config.variables=variables;}}
config.name='template';return templateBuilderProto.getInstance({template:template}).build(config);};methods.augment({getInstance:templateBuilderProto.getInstance});return methods;}));