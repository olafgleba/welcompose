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
	'.submit240' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator240.gif) no-repeat';
		}
	},
	'.submit240bez260' : function(el){
		el.onclick = function(){
			this.style.background = '#0c3 url(../static/img/submitindicator240.gif) no-repeat';
		}
	}
};
Behaviour.register(definitions);
Behaviour.addLoadEvent(OakInit.load);