/**
 * Project: Welcompose
 * File: wcom.update.events.js
 *
 * Copyright (c) 2004-2005 sopic GmbH
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
	'#database_connection_method' : function(el){
		el.onchange = function(){
			Helper.showDependingFormfield(this);
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
	'input.validate' : function(el){
		el.onkeyup = function(){
			validate(this);
		}
	},
	'input.startUpdate' : function(el){
		el.onclick = function(){
			Updater.processTasksInit();
		}
	},
	'.submit240' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(static/img/submitindicator240.gif) no-repeat';
		}
	},
	'.submit240nomargin' : function(el){
		el.onfocus = function(){
			this.style.background = '#ff620d url(static/img/submitindicator240.gif) no-repeat';
		}
	}
};
Behaviour.register(definitions);
Behaviour.addLoadEvent(Init.load);