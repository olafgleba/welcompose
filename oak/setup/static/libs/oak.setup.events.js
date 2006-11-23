/**
 * Project: Oak
 * File: oak.setup.events.js
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
	'input.validate' : function(el){
		el.onkeyup = function(){
			validate(this);
		}
	},
	'.submit90' : function(el){
		el.onfocus = function(){
			this.style.background = '#666 url(static/img/submitindicator90.gif) no-repeat';
		}
	},
	'.submit200' : function(el){
		el.onfocus = function(){
			this.style.background = '#0c3 url(static/img/submitindicator200.gif) no-repeat';
		}
	},
	'.submit200nomargin' : function(el){
		el.onfocus = function(){
			this.style.background = '#0c3 url(static/img/submitindicator200.gif) no-repeat';
		}
	}
};
Behaviour.register(definitions);