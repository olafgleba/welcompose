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
	'.iHelpRemoveLevelTwo' : function(el){
		el.onclick = function(){
			Help.hide(this, '2');
			return false;
		}
	},
	'#main input' : function(el){
		el.onfocus = function(){
			Forms.setOnEvent(this, '','#0c3','dotted');
			return false;
		}
		el.onblur = function(){
			Forms.setOnEvent(this, '','#000','solid');
			return false;
		}
		el.onkeyup = function(){
			validate(this);
		}
	},
	'#content textarea' : function(el){
		el.onfocus = function(){
			Forms.setOnEvent(this, '','#0c3','dotted');
			return false;
		}
		el.onblur = function(){
			Forms.setOnEvent(this, '','#000','solid');
			return false;
		}
		el.onkeyup = function(){
			validate(this);
		}
	},
	'.submitbut200' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator200.gif) no-repeat';
		}
	},
	'.submitbut140' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator140.gif) no-repeat';
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
	'#simple_page_meta_use' : function(el){
		el.onclick = function(){
			Status.getCbx(new Array('simple_page_meta_use'));
		}
	}
};
Behaviour.register(definitions);
Behaviour.addLoadEvent(OakInit.load); 