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
 * trigger functions depending on CSS class DOM events
 *
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process (e.g. ID)
 * @return {string} processId build ID for further manipulation, used in func xhrHelp()
 */
var definitions = {
	'.con' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=contents', navLyOne);
			return false;
		}
	},	
	'.conon' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=contents', navLyOne);
			return false;
		}
	},
	'.med' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=library', navLyOne);
			return false;
		}
	},
	'.medon' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=library', navLyOne);
			return false;
		}
	},
	'.com' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=community', navLyOne);
			return false;
		}
	},	
	'.comon' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=community', navLyOne);
			return false;
		}
	},
	'.usr' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=users', navLyOne);
			return false;
		}
	},
	'.usron' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=users', navLyOne);
			return false;
		}
	},	
	'.tem' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=templates', navLyOne);
			return false;
		}
	},	
	'.temon' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=templates', navLyOne);
			return false;
		}
	},
	'.set' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=settings', navLyOne);
			return false;
		}
	},
	'.seton' : function(el){
		el.onclick = function(){ 
			xhrNav(parseNavUrl + '?page=settings', navLyOne);
			return false;
		}
	},
	'.iHelp' : function(el){
		el.onclick = function(){
			getHelp(this, 'for');
			xhrHelp(parseHelpUrl + '?page=' + form_name + '_' + processId, processId);
			setCorrespondingFocus(this, 'for');
			return false;
		}
	},
	'.iHelpRemove' : function(el){
		el.onclick = function(){
			removeHelp(this, 'for');
			setCorrespondingFocus(this, 'for');
			return false;
		}
	},
	'#main input' : function(el){
		el.onfocus = function(){
			mFocus(this, '','#0c3','dotted');
			return false;
		}
		el.onblur = function(){
			mBlur(this, '','#000','solid');
			return false;
		}
		el.onkeyup = function(){
			validate(this);
		}
	},
	'#main textarea' : function(el){
		el.onfocus = function(){
			mFocus(this, '','#0c3','dotted');
			return false;
		}
		el.onblur = function(){
			mBlur(this, '','#000','solid');
			return false;
		}
		el.onkeyup = function(){
			validate(this);
		}
	},
	'.back .noLine' : function(el){
		el.onclick = function(){
			top.window.history.go(-1);
			return false;
		}
	},
	'#lFloatInitSubmit' : function(el){
		el.onclick = function(){
			Element.show("lFloatIndicator");
		}
	}

};
Behaviour.register(definitions);
Behaviour.addLoadEvent(initLoad); 