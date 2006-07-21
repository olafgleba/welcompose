/**
 * Project: Oak
 * File: oak.core.js
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
 * Define some essential vars
 *
 * used in almost every js file
 */

/**
 * Url for xhr parse imported files
 * used: definition.js
 */
var parseNavUrl		= '../parse.navigation.php';
var parseHelpUrl	= '../parse.help.php';

/**
 * Defines the div IDs for the navigation layers
 * find: html templates
 * used: oak.events.js
 */
var navLyOne	= 'ly1';
var navLyTwo	= 'ly2';
var navLyThree	= 'ly3';

/**
 * Define the used help class names
 * used: oak.events.js
 */
 helpClass					= 'iHelp';
var helpClassRemove				= 'iHelpRemove';
var helpClassLevelTwo			= 'iHelpLevelTwo';
var helpClassRemoveLevelTwo		= 'iHelpRemoveLevelTwo';

/**
 * Define the used tbl upload class names
 * defines cascading styles to fit background images
 * used: oak.core.js
 */
var uploadClass		= 'upload showTableRow';
var uploadClassHide	= 'uploadhide hideTableRow';

/**
 * Comprehensive colors application wide
 * used: oak.core.js
 */
 var applicationTextColor = '#009a26';

/**
 * Build help strings delivered within DOM (innerHTML)
 * must corresponding to html notation -> html templates
 * find: html templates
 * used: oak.core.js
 */
var helpHtmlShow	= '<a href="#" title="' + showHelp + '"><img src="../static/img/icons/help.gif" alt="" /></a>';
var helpHtmlHide	= '<a href="#" title="' + hideHelp + '"><img src="../static/img/icons/help_off.gif" alt="" /></a>';

/**
 * Define debug output
 *
 * defined values:
 * 1 = development
 * 2 = production
 */
var debug = 1;



/**
 * Collect loads for init onLoad
 * called by Behaviour.addLoadEvent
 */
function initLoad ()
{	
	getHeaderVars();
	if (typeof checkbox_status != 'undefined') {
		getCheckboxStatusOnLoad(checkbox_status);
	}
}

/**
 * Get header Vars on initialize
 * used: initLoad()
 *
 * return string
 */
function getHeaderVars ()
{
   if (typeof response != 'undefined' && $('rp')) {
       if (response == 1) {
            return new Effect.Fade('rp', {duration: 0.8, delay: 2.0})
       }
   }
}

/**
 * Error handling
 * String contains exception param with different provided debug information
 *
 * @param {string} exception error presented by catch statement
 */
function applyError (exception)
{
	var errStr;
	
	switch (debug) {
		case 1 :
			errStr = exception + '\r\n' 
					+ exception.fileName + '\r\n' 
					+ exception.lineNumber;
		break;
		case 2 :
			errStr = e_msg_str_prefix + '\r\n\r\n' 
					+ exception + '\r\n' 
					+ exception.fileName + '\r\n' 
					+ exception.lineNumber + '\r\n\r\n' 
					+ e_msg_str_suffix;
		break;
		default :
			errStr = exception;
	}
	alert (errStr);
}

/**
 * Obj for throwing errors at specific points
 *
 * @param {string} msg error presented by predefined var
 * @return obj
 */
function devError(msg) 
{
	this.name = 'devError';
	this.message = msg;
}

/**
 * Building new instance for obj devError to throw errors
 * at specific points within functions
 *
 * example use:
 * throw new devError(msg);
 *
 */
devError.prototype = new Error;




// constructor
function Help ()
{
	try {
		// properties
		this.helpClass = helpClass;
		this.helpClassRemove = helpClassRemove;
		this.helpClassLevelTwo = helpClassLevelTwo;
		this.helpClassRemoveLevelTwo = helpClassRemoveLevelTwo;
		this.parseHelpUrl = parseHelpUrl;
		
		// methods
		this.req = _buildXMLHTTPRequest();
		
	} catch (e) {
		applyError(e);
	}
}

Cn = Help.prototype;

Cn.show = function (elem, level)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'for'
		this.level = level;
		this.setCorrespondingFocus = _setCorrespondingFocus(this.elem, this.attr);
		
		this.processId = this.elem.parentNode.parentNode.getAttribute(this.attr);
		
		switch (this.level) {
			case '2' :
					this.formId = this.elem.parentNode.parentNode.parentNode.parentNode.parentNode.getAttribute('id');
					this.elem.className = this.helpClassRemoveLevelTwo;
				break;
			default :
					this.formId = this.elem.parentNode.parentNode.parentNode.parentNode.getAttribute('id');
					this.elem.className = this.helpClassRemove;	
		}
	
		this.target = this.processId;
	
		this.fetch = this.processId.replace(/(_(\d+))/, '');	
		if (this.fetch) {
			this.processId = this.fetch;
		}
		this.url = this.parseHelpUrl + '?page=' + this.formId + '_' + this.processId;
			
		if (typeof this.req != 'undefined') {
		
			var url		= this.url;
			var target	= this.target;
		
			_req.open('GET', url, true);
			_req.onreadystatechange = function () { _processHelp(url,target);};
			_req.send('');
		}
	
		Element.update(this.elem, helpHtmlHide);
		Behaviour.apply();
		
	} catch (e) {
		applyError(e);
	}
}

Cn.hide = function (elem, level)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'for';
		this.level = level;
	
		this.processId = this.elem.parentNode.parentNode.getAttribute(this.attr);
		this.processIdAfter = $(this.processId).parentNode.nextSibling;
		
		switch (this.level) {
			case '2' :
					this.elem.className = this.helpClassLevelTwo;
				break;
			default :
					this.elem.className = this.helpClass;	
		}
		Effect.Fade(this.processIdAfter,{duration: 0.5});
		Element.update(this.elem, helpHtmlShow);
		Behaviour.apply();
		
	} catch (e) {
		applyError(e);
	}
}

// instance obj
Help = new Help();


// constructor
function Navigation ()
{
	try {
		// properties
		this.navLyOne = navLyOne;
		this.navLyTwo = navLyTwo;
		
		// methods
		this.req = _buildXMLHTTPRequest();
		
	} catch (e) {
		applyError(e);
	}
}

Cn = Navigation.prototype;

Cn.show = function (name, target)
{
	try {
		// properties
		this.name = name;
		this.url = parseNavUrl + '?page=' + this.name;	
		
		switch (this.target) {
			case '2' :
					this.target = navLyTwo;
				break;
			default :
					this.target = navLyOne;	
		}
		
		if (typeof this.req != 'undefined') {
		
			var url		= this.url;
			var target	= this.target;
		
			_req.open('GET', url, true);
			_req.onreadystatechange = function () { _processNavigation(url,target);};
			_req.send('');
		}
	} catch (e) {
		applyError(e);
	}
}

// instance obj
Navigation = new Navigation();


// private
function _buildXMLHTTPRequest ()
{
	try {
		if (window.XMLHttpRequest) {
			_req = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			_req = new ActiveXObject("Microsoft.XMLHTTP");
		}
		return _req;
	} catch (e) {
		applyError(e);
	}
} 

function _processHelp(url, target)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				new Insertion.After($(target).parentNode, _req.responseText);				
				var target_after = $(target).parentNode.nextSibling;
				Element.hide(target_after);
				Effect.Appear(target_after, {duration: 0.8});
			} else {
	  			throw new devError(_req.statusText);
			}
		}
	} catch (e) {
		applyError(e);
	}
}

function _processNavigation(url, target)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				Element.hide($('topsubnavconstatic'));
				Element.update(target, _req.responseText);
				Behaviour.apply();
			} else {
	  			throw new devError(_req.statusText);
			}
		}
	} catch (e) {
		applyError(e);
	}
}

/**
 * Set Focus on Form Element
 *
 * used : oak.events.js
 *
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process (e.g. ID)
 */
 function _setCorrespondingFocus (elem, attr)
{
	var inst = elem.parentNode.parentNode.getAttribute(attr);
	$(inst).focus();
}






/**
 * DOM triggers to attach onEvent behaviours
 *
 * used : oak.events.js
 *
 * @param {string} inst actual ID
 * @param {string} bgcolor defined background color
 * @param {string} bcolor defined border color
 * @param {string} bstyle defined border attr (e.g. dotted)
 */
function doFocus (inst, bgcolor, bcolor, bstyle)
{
	inst.style.backgroundColor = bgcolor;
	inst.style.borderColor = bcolor;
	inst.style.borderStyle = bstyle;
}

/**
 * DOM triggers to attach onEvent behaviours
 *
 * used : oak.events.js
 *
 * @param {string} inst actual ID
 * @param {string} bgcolor defined background color
 * @param {string} bcolor defined border color
 * @param {string} bstyle defined border attr (e.g. dotted)
 */
 function doBlur (inst, bgcolor, bcolor, bstyle)
{
	inst.style.backgroundColor = bgcolor;
	inst.style.borderColor = bcolor;
	inst.style.borderStyle = bstyle;
}


/**
 * Get form field (Checkbox) status on user event
 * used : oak.events.js
 * find: html templates
 *
 * @param {array} elems actual elements
 */
 function getCheckboxStatus (elems)
{
	for (var e = 0; e < elems.length; e++) {
		
		// build new div
		var range = String(elems[e])  + '_container';
		
		if ($(range)) {
			if ($(elems[e]).checked == true) {

			allNodes = document.getElementsByClassName("bez");
			
			for (var i = 0; i < allNodes.length; i++) {
   				var _process = allNodes[i].parentNode.parentNode.getAttribute('id');		
   				if (_process == range) {
   					allNodes[i].style.color = applicationTextColor;
   				}
   			}
				Element.hide($(range));
				Effect.Appear($(range),{duration: 0.6});
			} else {
				Effect.Fade($(range),{duration: 0.6});
			}
		}
	}
}

/**
 * Get form field (Checkbox) status on onload event
 * used : oak.events.js
 * find: html templates
 *
 * @param {array} elems actual elements
 */
 function getCheckboxStatusOnLoad (elems)
{
	for (var e = 0; e < elems.length; e++) {
		
		// object -> string conversion
		var range = String(elems[e])  + '_container';
		
		if ($(range)) {
			if ($(elems[e]).checked == true) {

			allNodes = document.getElementsByClassName("bez");
			
			for (var i = 0; i < allNodes.length; i++) {
   				var _process = allNodes[i].parentNode.parentNode.getAttribute('id');		
   				if (_process == range) {
   					allNodes[i].style.color = applicationTextColor;
   				}
   			}
				Element.show(range);
			} else {
				Element.hide(range);
			}
		}
	}
}
   
/**
 * Hide table(s) row(s)
 * used : oak.events.js
 *
 * @param {var} elem actual element to process
 */
 function hideTableRowSetTime (elem)
{
	// process outer table tr
	elem.style.visibility = 'collapse';
}

/**
 * Hide table(s) row(s) and inner div
 * used : oak.events.js
 *
 * @param {string} elem actual element
 */
 function hideTableRow (elem)
{
	var getId = elem.getAttribute('id');
	
	// e.g. t_<var>
	var bid = getId.split('t_');
	
	// push var to the global scope
	obid = $('o_' + bid[1]);

	// process inner div
	Effect.Fade('i_' + bid[1],{duration: 0.6});
	
	elem.className = uploadClass;
	Behaviour.apply();

}

/**
 * Show table(s) row(s) and inner div
 * used : oak.events.js
 *
 * @param {string} elem actual element
 */
 function showTableRow (elem)
{	
	var getId = elem.getAttribute('id');
	
	// e.g. t_<var>
	var bid = getId.split('t_');
	
	// process outer table tr
	$('o_' + bid[1]).style.visibility = 'visible';
	
	// process inner div
	Element.hide('i_' + bid[1]);
	Effect.Appear('i_' + bid[1],{duration: 0.8});
	
	elem.className = uploadClassHide;
	Behaviour.apply();
}