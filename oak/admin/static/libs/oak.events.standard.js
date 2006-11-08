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
 * $Id: oak.events.js 517 2006-10-18 17:48:05Z olaf $
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
	'input' : function(el){
		el.onfocus = function(){
			Forms.setOnEvent(this, '','#0c3','dotted');
			return false;
		}
		el.onblur = function(){
			if (this.type != 'reset' && this.type != 'submit' && this.type != 'button') {
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
	'.act_internalLinkGlobalTemplates' : function(el){
		el.onclick = function(){
			Helper.launchPopup('420','604','globaltemplates_links_select','globaltemplates_internal_links', this);
			return false;
		}
	},
	'.act_setInternalLinkGlobalTemplates' : function(el){
		el.onclick = function(){
			Helper.insertInternalLinkGlobalTemplates(this);
			return false;
		}
	},
	'.act_internalLinkGlobalFiles' : function(el){
		el.onclick = function(){
			Helper.launchPopup('420','604','globalfiles_links_select','globalfiles_internal_links', this);
			return false;
		}
	},
	'.act_setInternalLinkGlobalFiles' : function(el){
		el.onclick = function(){
			Helper.insertInternalLinkGlobalFiles(this);
			return false;
		}
	},
	'.close' : function(el){
		el.onclick = function(){
			Helper.closeLinksPopup(this);
			return false;
		}
	},
	'.showNextNode' : function(el){
		el.onclick = function(){
			Helper.showNextNode(this);
			return false;
		}
	},
	'.confirmDelNav' : function(el){
		el.onclick = function(){
			Helper.confirmDelNavAction(this);
			return false;
		}
	},
	'.changeBlogCommentStatus' : function(el){
		el.onchange = function(){
			Helper.changeBlogCommentStatus(this);
			return false;
		}
	},
	'.submit90' : function(el){
		el.onclick = function(){
			this.style.background = '#666 url(../static/img/submitindicator90.gif) no-repeat';
		}
	},
	'.submit140' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator140.gif) no-repeat';
		}
	},
	'.submit200upload' : function(el){
		el.onclick = function(){
			Helper.showFileUploadMessage();
			this.style.background = '#0c3 url(../static/img/submitindicator200.gif) no-repeat';
		}
	},
	'.submit200' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator200.gif) no-repeat';
		}
	},
	'.submit200bez260' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator200.gif) no-repeat';
		}
	},
	'.cancel200' : function(el){
		el.onclick = function(){
			Helper.closePopup(this);
			return false;
		}
	},
	'.submit240' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator240.gif) no-repeat';
		}
	},
	'.submit240bez260' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator240.gif) no-repeat';
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