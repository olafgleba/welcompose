/**
 * Project: Welcompose
 * File: wcom.update.updater.js
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
 * $Id$
 *
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

/** 
 * @fileoverview This file comprised javascript the database tasks update functions.
 */

/**
 * Constructs the Updater class
 * 
 * @class The Helper class defines a bunch of functions which doesn't 
 * belongs as regards content to just one class. Several functions be in use
 * within every Welcompose class. The scope is application wide.
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

		// id and safari need extra treatment
		// they stupidly handles #text nodes differently
		if (this.browser == 'ie') {
			el = el.childNodes[0];
			el = el.childNodes[1];
		}
		else if (this.browser == 'sa') {
			el = el.childNodes[1];
			el = el.childNodes[1];
		}
		else {
			el = el.childNodes[1];
			el = el.childNodes[3];
		}		
		el = el.childNodes;
		
		// make target row (status) global for further use in the ajax.loader
		if (this.browser == 'ie' || this.browser == 'sa') {
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
		this.currentTarget = Helper.getParentNodeNextSibling(this.lastTarget, 2);
	
		if (Updater.isNull(this.currentTarget) === false) {
			if (this.browser == 'ie' || this.browser == 'sa') {
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
		$(target).innerHTML = req.responseText;
		var rimg = document.createElement("img");
		rimg.setAttribute('src', 'static/img/icons/success.gif');
		rimg.setAttribute('width', '17');
		rimg.setAttribute('height', '17');
		rimg.setAttribute('alt', '');
		$(target).appendChild(rimg);
		
		// process following tasks
		setTimeout ("Updater.processTasks(target)", 500);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Tasks
 */
Updater = new Updater();
