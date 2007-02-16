/**
 * Project: Welcompose
 * File: wcom.events.extended.js
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id: wcom.events.js 517 2006-10-18 17:48:05Z olaf $
 *
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

/** 
 * @fileoverview Defines event handling through thirdparty lib Behaviour.
 * Used for all content pages which includes the mediamanager.
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
	'.iHelpMediamanager' : function(el){
		el.onclick = function(){
			Help.showMediamanager(this);
			return false;
		}
	},
	'.iHelpRemoveMediamanager' : function(el){
		el.onclick = function(){
			Help.hideMediamanager(this);
			return false;
		}
	},
	'.showMediamanagerElementMyLocal' : function(el){
		el.onclick = function(){
			Mediamanager.showElement(this);
			Mediamanager.invokePager(this);
			return false;
		}
	},
	'.hideMediamanagerElementMyLocal' : function(el){
		el.onclick = function(){
			Mediamanager.hideElement(this);
			Mediamanager.invokePager(this);
			return false;
		}
	},
	'.showMediamanagerElementMyFlickr' : function(el){
		el.onclick = function(){
			Mediamanager.showElement(this);
			return false;
		}
	},
	'.hideMediamanagerElementMyFlickr' : function(el){
		el.onclick = function(){
			Mediamanager.hideElement(this);
			return false;
		}
	},
	'a.mm_upload' : function(el){
		el.onclick = function(){
			Helper.launchPopup('mm_upload', this);
			return false;
		}
	},
	'a.mm_edit' : function(el){
		el.onclick = function(){
			Helper.launchPopup('mm_edit', this);
			return false;
		}
	},
	'a.mm_insert' : function(el){
		el.onclick = function(){
			Mediamanager.processMediaCallbacks(this);
			return false;
		}
	},
	'a.mm_insertFlickr' : function(el){
		el.onclick = function(){
			Mediamanager.processMediaCallbacksFlickr(this);
			return false;
		}
	},
	'a.mm_delete' : function(el){
		el.onclick = function(){
			Mediamanager.deleteMediaItem(this);
			return false;
		}
	},
	'a.mm_cast' : function(el){
		el.onclick = function(){
			Mediamanager.mediaToPodcast(this);
			return false;
		}
	},
	'a.mm_myLocal' : function(el){
		el.onclick = function(){
			Mediamanager.switchLayer('lyMediamanagerMyLocal', 'lyMediamanagerMyFlickr');
			return false;
		}
	},
	'a.mm_myFlickr' : function(el){
		el.onclick = function(){
			Mediamanager.switchLayer('lyMediamanagerMyFlickr', 'lyMediamanagerMyLocal');
			return false;
		}
	},
	'#mm_include_types_wrap' : function(el){
		el.onclick = function(){
			Mediamanager.invokeInputs(this);
			return false;
		}
	},
	'#mm_timeframe' : function(el){
		el.onchange = function(){
			Mediamanager.invokeInputs(this);
			return false;
		}
	},
	'#mm_photoset' : function(el){
		el.onchange = function(){
			Mediamanager.invokeInputsMyFlickr(this);
			return false;
		}
	},
	'a.pager' : function(el){
		el.onclick = function(){
			Mediamanager.invokePager(this);
			return false;
		}
	},
	'a.pager_myFlickr' : function(el){
		el.onclick = function(){
			Mediamanager.invokePagerMyFlickr(this);
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
				Forms.setOnEvent(this, '','#000','solid');
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
			Forms.setOnEvent(this, '','#000','solid');
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
	'.toggleExtendedView120' : function(el){
		el.onclick = function(){
			Mediamanager.toggleExtendedView(this);
			return false;
		}
	},
	'.discardPodcast120' : function(el){
		el.onclick = function(){
			Mediamanager.discardPodcast(this);
			return false;
		}
	},
	'#submit55' : function(el){
		el.onfocus = function(){
			Mediamanager.initializeUserMyFlickr(this);
			//return false;
			this.style.background = '#666 url(../static/img/submitindicator55.gif) no-repeat';
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