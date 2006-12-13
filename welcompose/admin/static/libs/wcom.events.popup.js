/**
 * Project: Welcompose
 * File: wcom.events.popup.js
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
 * @fileoverview Defines event handling through thirdparty lib Behaviour. Used for all content pages which contains popups.
 */

/**
 * trigger class methods depending on CSS class DOM events
 */
var definitions = {
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
	'input' : function(el){
		el.onfocus = function(){
			if (this.type != 'reset' && this.type != 'submit' && this.type != 'button' && this.type != 'checkbox') {
				Forms.setOnEvent(this, '','#0c3','dotted');
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
			Forms.setOnEvent(this, '','#0c3','dotted');
			Forms.storeFocus(this);
			return false;
		}
		el.onblur = function(){
			Forms.setOnEvent(this, '','#000','solid');
			return false;
		}
	},
	'.act_setInternalLink' : function(el){
		el.onclick = function(){
			Helper.insertInternalLink(this);
			return false;
		}
	},
	'.act_setInternalLinkNoHref' : function(el){
		el.onclick = function(){
			Helper.insertInternalLinkNoHref(this);
			return false;
		}
	},
	'.act_setInternalLinkGlobalTemplates' : function(el){
		el.onclick = function(){
			Helper.insertInternalLinkGlobalTemplates(this);
			return false;
		}
	},
	'.act_setInternalLinkGlobalFiles' : function(el){
		el.onclick = function(){
			Helper.insertInternalLinkGlobalFiles(this);
			return false;
		}
	},
	'.act_setInternalLinkStructuralTemplates' : function(el){
		el.onclick = function(){
			Helper.insertInternalLinkStructuralTemplates(this);
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
	'.submit200upload' : function(el){
		el.onfocus = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator200.gif) no-repeat';
		}
		el.onclick = function(){
			/* needed for func closePopupTrack */
			submitted = true;
			Helper.showFileUploadMessage();
		}
	},
	'.submit200' : function(el){
		el.onfocus = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator200.gif) no-repeat';
		}
		el.onclick = function(){
			/* needed for func closePopupTrack */
			submitted = true;
		}
	},
	'.cancel200' : function(el){
		el.onfocus = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator200.gif) no-repeat';
		}
		el.onclick = function(){
			Helper.closePopup(this);
			return false;
		}
	}
};
Behaviour.register(definitions);
Behaviour.addLoadEvent(Init.load);