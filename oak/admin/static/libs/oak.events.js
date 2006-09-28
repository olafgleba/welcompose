/**
 * Project: Oak
 * File: oak.events.js
 *
 * Copyright (c) 2004-2005 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id$
 *
 * @copyright 2004-2005 creatics media.systems
 * @author Olaf Gleba
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
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
	'.iHelpLevelTwo' : function(el){
		el.onclick = function(){
			Help.show(this, '2');
			return false;
		}
	},
	'.iHelpLevelThree' : function(el){
		el.onclick = function(){
			Help.show(this, '3');
			return false;
		}
	},
	'.iHelpRemoveLevelTwo' : function(el){
		el.onclick = function(){
			Help.hide(this, '2');
			return false;
		}
	},
	'.iHelpRemoveLevelThree' : function(el){
		el.onclick = function(){
			Help.hide(this, '3');
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
	'.hideTableRow' : function(el){
		el.onclick = function(){
			Tables.hideRow(this);
			return false;
		}
	},
	'.showTableRow' : function(el){
		el.onclick = function(){
			Tables.showRow(this);
			return false;
		}
	},
	'.showMediamanagerElement' : function(el){
		el.onclick = function(){
			Mediamanager.showElement(this);
			return false;
		}
	},
	'.hideMediamanagerElement' : function(el){
		el.onclick = function(){
			Mediamanager.hideElement(this);
			return false;
		}
	},
	'a.mm_upload' : function(el){
		el.onclick = function(){
			Helper.launchPopup('740','604','media_upload','mm_upload', this);
			return false;
		}
	},
	'a.mm_edit' : function(el){
		el.onclick = function(){
			Helper.launchPopup('740','604','media_edit','mm_edit', this);
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
	'a.mm_insertImageItem' : function(el){
		el.onclick = function(){
			Mediamanager.insertImageItem(this);
			return false;
		}
	},
	'a.mm_insertDocumentItem' : function(el){
		el.onclick = function(){
			Mediamanager.insertDocumentItem(this);
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
		}
	},
	'#mm_timeframe' : function(el){
		el.onchange = function(){
			Mediamanager.invokeInputs(this);
		}
	},
	'input' : function(el){
		el.onfocus = function(){
			Forms.setOnEvent(this, '','#0c3','dotted');
			return false;
		}
		el.onblur = function(){
			if (this.type != 'reset' && this.type != 'submit') {
				Forms.setOnEvent(this, '','#000','solid');
			}
			return false;
		}
	},
	'textarea' : function(el){
		el.onfocus = function(){
			Forms.setOnEvent(this, '','#0c3','dotted');
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
			validate(this);
		}
	},
	'textarea.validate' : function(el){
		el.onkeyup = function(){
			validate(this);
		}
	},
	'.act_internalLink' : function(el){
		el.onclick = function(){
			Helper.launchPopup('740','604','pages_links_select','pages_internal_links', this);
			return false;
		}
	},
	'.act_setInternalLink' : function(el){
		el.onclick = function(){
			Helper.insertInternalLink(this);
			return false;
		}
	},
	'.showNextNode' : function(el){
		el.onclick = function(){
			Helper.showNextNode(this);
			return false;
		}
	},
	'.toggleExtendedView120' : function(el){
		el.onclick = function(){
			Mediamanager.toggleExtendedView(this);
			return false;
		}
	},
	'.close' : function(el){
		el.onclick = function(){
			Helper.closeLinksPopup(this);
			return false;
		}
	},
	'.submit90' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator90.gif) no-repeat';
		}
	},
	'.submit200' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator200.gif) no-repeat';
		}
	},
	'.submit140' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator140.gif) no-repeat';
		}
	},
	'.cancel200' : function(el){
		el.onclick = function(){
			Helper.closePopup(this);
			return false;
		}
	},
	'#simple_page_meta_use' : function(el){
		el.onclick = function(){
			Status.getCbx(new Array('simple_page_meta_use'));
		}
	}
};
Behaviour.register(definitions);
Behaviour.addLoadEvent(OakInit.load);