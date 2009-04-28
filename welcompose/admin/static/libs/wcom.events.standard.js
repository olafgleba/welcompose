/**
 * Project: Welcompose
 * File: wcom.events.standard.js
 *
 * Copyright (c) 2008 creatics media.systems
 *
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * $Id$
 *
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */
 
/** 
 * @fileoverview Defines event handling through thirdparty lib Behaviour.
 * Used for all content pages exclude the mediamanager. 
 */

/**
 * trigger class methods depending on CSS class DOM events
 */
var definitions = {
	'.con' : function(el){
		el.onclick = function(){ 
			Navigation.show('contents');
			return false;
		}
	},	
	'.conon' : function(el){
		el.onclick = function(){ 
			Navigation.show('contents');
			return false;
		}
	},
	'.med' : function(el){
		el.onclick = function(){ 
			Navigation.show('library');
			return false;
		}
	},
	'.medon' : function(el){
		el.onclick = function(){ 
			Navigation.show('library');
			return false;
		}
	},
	'.com' : function(el){
		el.onclick = function(){ 
			Navigation.show('community');
			return false;
		}
	},	
	'.comon' : function(el){
		el.onclick = function(){ 
			Navigation.show('community');
			return false;
		}
	},
	'.usr' : function(el){
		el.onclick = function(){ 
			Navigation.show('users');
			return false;
		}
	},
	'.usron' : function(el){
		el.onclick = function(){ 
			Navigation.show('users');
			return false;
		}
	},	
	'.tem' : function(el){
		el.onclick = function(){ 
			Navigation.show('templates');
			return false;
		}
	},	
	'.temon' : function(el){
		el.onclick = function(){ 
			Navigation.show('templates');
			return false;
		}
	},
	'.set' : function(el){
		el.onclick = function(){ 
			Navigation.show('settings');
			return false;
		}
	},
	'.seton' : function(el){
		el.onclick = function(){ 
			Navigation.show('settings');
			return false;
		}
	},
	'.iHelp' : function(el){
		el.onclick = function(){
			Help.show(this);
			return false;
		}
	},
	'.iHelpRemove' : function(el){
		el.onclick = function(){
			Help.hide(this);
			return false;
		}
	},
	'.showFormElements' : function(el){
		el.onclick = function(){
			Forms.showFormElements(this);
			return false;
		}
	},
	'.hideFormElements' : function(el){
		el.onclick = function(){
			Forms.hideFormElements(this);
			return false;
		}
	},
	'input' : function(el){
		el.onfocus = function(){
			if (this.type != 'reset' && this.type != 'submit' && this.type != 'button' && this.type != 'checkbox') {
				Forms.setOnEvent(this, '','#ff620d','dotted');
			}
			return false;
		}
		el.onblur = function(){
			if (this.type != 'reset' && this.type != 'submit' && this.type != 'button' && this.type != 'checkbox') {
				Forms.setOnEvent(this, '','#666','solid');
			}
			return false;
		}
	},
	'textarea' : function(el){
		el.onfocus = function(){
			Forms.setOnEvent(this, '','#ff620d','dotted');
			Forms.storeFocus(this);
			return false;
		}
		el.onblur = function(){
			Forms.setOnEvent(this, '','#666','solid');
			return false;
		}
	},
	'input.validate' : function(el){
		el.onkeyup = function(){
			Helper.validate(this);
		}
	},
	'textarea.validate' : function(el){
		el.onkeyup = function(){
			Helper.validate(this);
		}
	},
	'input.urlify' : function(el){
		el.onkeyup = function(){
			Helper.convertFieldValuesToValidUrl(this, 'for');
			return false;
		}
	},
	'a.insert' : function(el){
		el.onclick = function(){
			Helper.processCallbacks(this);
			return false;
		}
	},
	'a.adopt' : function(el){
		el.onclick = function(){
			Helper.adoptBox(this);
			return false;
		}
	},
	'a.runaction' : function(el){
		el.onclick = function(){
			Helper.runAction(this);
			return false;
		}
	},
	'.confirmDelNav' : function(el){
		el.onclick = function(){
			Helper.confirmDelNavAction(this);
			return false;
		}
	},
	'.confirmDelPage' : function(el){
		el.onclick = function(){
			Helper.confirmDelPageAction(this);
			return false;
		}
	},
	'.confirmDelProject' : function(el){
		el.onclick = function(){
			Helper.confirmDelProjectAction(this);
			return false;
		}
	},
	'.confirmDelTplType' : function(el){
		el.onclick = function(){
			Helper.confirmDelTplTypeAction(this);
			return false;
		}
	},
	'.confirmDelTplSets' : function(el){
		el.onclick = function(){
			Helper.confirmDelTplSetsAction(this);
			return false;
		}
	},
	'.confirmDelTplGlobal' : function(el){
		el.onclick = function(){
			Helper.confirmDelTplGlobalAction(this);
			return false;
		}
	},
	'.confirmDelTplGlobalfile' : function(el){
		el.onclick = function(){
			Helper.confirmDelTplGlobalfileAction(this);
			return false;
		}
	},
	'.confirmDelAbbreviation' : function(el){
		el.onclick = function(){
			Helper.confirmDelAbbreviationAction(this);
			return false;
		}
	},
	'.changeBlogCommentStatus' : function(el){
		el.onchange = function(){
			Helper.changeBlogCommentStatus(this);
			return false;
		}
	},
	'.hideTableRow' : function(el){
		el.onclick = function(){
			Tables.hideTableRow(this);
			return false;
		}
	},
	'.showTableRow' : function(el){
		el.onclick = function(){
			Tables.showTableRow(this);
			return false;
		}
	},
	'.toggleElemNavigation' : function(el){
		el.onclick = function(){
			Tables.toggleElem(this);
			return false;
		}
	},
	'.toggleElemBoxes' : function(el){
		el.onclick = function(){
			Tables.toggleElem(this);
			return false;
		}
	},
	'.toggleViewByChbx' : function(el){
		el.onclick = function(){
			Init.toggleViewByChbx(this, 'target_toggleView');
			//return false;
		}
	},
	'.submit90' : function(el){
		el.onfocus = function(){
			this.style.background = '#666 url(../static/img/submitindicator90.gif) no-repeat';
		}
	},
	'.submit140' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(../static/img/submitindicator140.gif) no-repeat';
		}
	},
	'.submit200' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(../static/img/submitindicator200.gif) no-repeat';
		}
	},
	'.submit200go' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(../static/img/submitindicator200.gif) no-repeat';
		}
	},
	'.submit200bez260' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(../static/img/submitindicator200.gif) no-repeat';
		}
	},
	'.submit240' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(../static/img/submitindicator240.gif) no-repeat';
		}
	},
	'.submit240bez260' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(../static/img/submitindicator240.gif) no-repeat';
		}
	},
	'#simple_page_meta_use' : function(el){
		el.onclick = function(){
			Status.getCbx(new Array('simple_page_meta_use'));
		}
	}
};
Behaviour.register(definitions);
Behaviour.addLoadEvent(Init.load);