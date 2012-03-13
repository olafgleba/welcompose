/**
 * Project: Welcompose
 * File: wcom.events.popup.js
 *
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * @author Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */
 
/** 
 * @fileoverview Defines event handling through thirdparty lib Behaviour.
 * Used for all content pages which contains popups.
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
	'a.selAllCheckboxes' : function(el){
		el.onclick = function(){
			Helper.selAllCheckboxes(this);
			return false;
		}
	},
	'a.deselAllCheckboxes' : function(el){
		el.onclick = function(){
			Helper.deselAllCheckboxes(this);
			return false;
		}
	},
	'a.process_insert' : function(el){
		el.onclick = function(){
			Helper.callbacksInsert(this);
			return false;
		}
	},
	'.showElem' : function(el){
		el.onclick = function(){
			Core.showElem(this, 3);
			return false;
		}
	},
	'.hideElem' : function(el){
		el.onclick = function(){
			Core.hideElem(this, 3);
			return false;
		}
	},
	'a.insert_tags' : function(el){
		el.onclick = function(){
			Helper.insertContentIntoTextarea(this, 'tags');
			return false;
		}
	},
	'a.process_insert_array' : function(el){
		el.onclick = function(){
			Helper.callbacksInsertArray(this);
			return false;
		}
	},
	'.close' : function(el){
		el.onclick = function(){
			Helper.closeLinksPopup(this);
			return false;
		}
	},
	'a.showNextNode' : function(el){
		el.onclick = function(){
			Helper.showNextNode(this);
		}
	},
	'a.showNextNodeBoxes' : function(el){
		el.onclick = function(){
			Helper.showNextNodeBoxes(this);
		}
	},
	'#insert_as_reference' : function(el){
		el.onclick = function(){
			Status.getCbx(new Array('insert_as_reference'), 1);
		}
	},
	'.submit200upload' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(../static/img/submitindicator200.gif) no-repeat';
		}
		el.onclick = function(){
			/* needed for func closePopupTrack */
			submitted = true;
			Helper.showFileUploadMessage();
		}
	},
	'.submit200' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(../static/img/submitindicator200.gif) no-repeat';
		}
		el.onclick = function(){
			/* needed for func closePopupTrack */
			submitted = true;
		}
	},
	'.submit200insertcallback' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(../static/img/submitindicator200.gif) no-repeat';
		}
		el.onclick = function(){
			 /* needed for func closePopupTrack */
			 submitted = true;
		}
	},
	'.cancel140' : function(el){
		el.onfocus = function(){
			this.style.background = '#ccc url(../static/img/submitindicator140grey.gif) no-repeat';
		}
		el.onclick = function(){
			Helper.closePopup(this);
			return false;
		}
	},
	'.close140' : function(el){
		el.onfocus = function(){
			this.style.background = '#ccc url(../static/img/submitindicator140grey.gif) no-repeat';
		}
		el.onclick = function(){
			Helper.closeLinksPopup(this);
			/* needed for func closePopupTrackNoAlert */
			submitted = true;
			return false;
		}
	}
};
Behaviour.register(definitions);
Behaviour.addLoadEvent(Init.load);