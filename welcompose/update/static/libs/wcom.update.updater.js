/**
 * Project: Welcompose
 * File: wcom.update.updater.js
 *
 * Copyright (c) 2008 creatics
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * $Id$
 *
 * @copyright 2008 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/** 
 * @fileoverview This file comprised javascript the database tasks update functions.
 */

/**
 * Constructs the Updater class
 * 
 * @class The Updater class balance the database and shift it to the last state
 * depending on the tasks files in folder tasks.
 *
 * @see Base
 * @constructor
 * @throws applyError on exception
 */
function Updater ()
{
	try {
		// no properties		
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Updater.prototype = new Base();


/**
 * Instance Methods from prototype @class Updater
 */
Updater.prototype.processTasksInit = Updater_processTasksInit;
Updater.prototype.processTasks = Updater_processTasks;
Updater.prototype.loaderProcessTasks = Updater_loaderProcessTasks;
Updater.prototype.showResponseProcessTasks = Updater_showResponseProcessTasks;



function Updater_processTasksInit ()
{
	try {
		this.browser = _setBrowserString();
				
		// DOM processing
		// get table attributes
		var el = $("tasks");

		// IE need extra treatment cause 
		// it stupidly handles #text nodes differently
		if (this.browser == 'ie') {
			el = el.childNodes[0];
			el = el.childNodes[1];
		}
		else {
			el = el.childNodes[1];
			el = el.childNodes[3];
		}		
		el = el.childNodes;
		
		// make target row (status) global for further use in the ajax.loader
		if (this.browser == 'ie') {
			target = el[1].id;
		} else {
			target = el[3].id;
		}
		
		// fire ajax request
		var file = target.split('status_');
		var url = 'tasks/' + file[1] + '.php';

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Updater.loaderProcessTasks,
				onSuccess : Updater.showResponseProcessTasks
			});	
	} catch (e) {
		_applyError(e);
	}
}

function Updater_processTasks (lastTarget)
{
	try {
		this.lastTarget = $(lastTarget);
		this.browser = _setBrowserString();
		if (this.browser == 'sa') {
			this.currentTarget = Helper.getParentNodeNextSibling(this.lastTarget, 3);
		} else {
			this.currentTarget = Helper.getParentNodeNextSibling(this.lastTarget, 2);
		}
	
		if (Updater.isNull(this.currentTarget) === false) {
			if (this.browser == 'ie') {
				this.currentTarget = this.currentTarget.childNodes[1].id;
			} else {
				this.currentTarget = this.currentTarget.childNodes[3].id;
			}		
		
			// redefine global target var
			target = this.currentTarget;
		
			// fire ajax request
			var file = target.split('status_');
			var url = 'tasks/' + file[1] + '.php';

			var myAjax = new Ajax.Request(
				url,
				{
					method : 'get',
					onLoading : Updater.loaderProcessTasks,
					onSuccess : Updater.showResponseProcessTasks
				});	
		} else {
			$('startupdate').style.display = 'none';
			$('finishupdate').style.display = 'block';
		}
	} catch (e) {
		_applyError(e);
	}
}


function Updater_loaderProcessTasks ()
{
	try {
		var limg = document.createElement("img");
		limg.setAttribute('src', 'static/img/indicator.gif');
		limg.setAttribute('width', '17');
		limg.setAttribute('height', '17');
		limg.setAttribute('alt', '');
		$(target).appendChild(limg);
	} catch (e) {
		_applyError(e);
	}
}

function Updater_showResponseProcessTasks (req)
{
	try {
		var r = req.responseText.match(/\berror\b/gi);

		if (r) {
			$(target).innerHTML = req.responseText;
		} else {
			$(target).innerHTML = req.responseText;
			var rimg = document.createElement("img");
			rimg.setAttribute('src', 'static/img/icons/success.gif');
			rimg.setAttribute('width', '17');
			rimg.setAttribute('height', '17');
			rimg.setAttribute('alt', '');
			$(target).appendChild(rimg);
		
			// process following tasks
			setTimeout ("Updater.processTasks(target)", 800);
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Tasks
 */
Updater = new Updater();
