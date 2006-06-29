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
var helpClass		= 'iHelp';
var helpClassRemove	= 'iHelpRemove';

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
function mFocus (inst, bgcolor, bcolor, bstyle)
{
	inst.style.backgroundColor = bgcolor;
	inst.style.borderColor = bcolor;
	inst.style.borderStyle = bstyle;
}

function mBlur (inst, bgcolor, bcolor, bstyle)
{
	inst.style.backgroundColor = bgcolor;
	inst.style.borderColor = bcolor;
	inst.style.borderStyle = bstyle;
}

function setCorrespondingFocus (elem, attr)
{
	instId = elem.parentNode.parentNode.getAttribute(attr);
	$(instId).focus();
}

/**
 * Get help IDs and print string in html templates
 * used : oak.events.js
 *
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process (e.g. ID)
 */
function getHelp (elem, attr)
{	
	processId = elem.parentNode.parentNode.getAttribute(attr);
	elem.className = helpClassRemove;
	Element.update(elem, helpHtmlHide);
	Behaviour.apply();	
}

/**
 * Get help IDs and print string in html templates
 * used : oak.events.js
 *
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process (e.g. ID)
 */
function removeHelp (elem, attr)
{	
	processIdRemove = elem.parentNode.parentNode.getAttribute(attr);
	processId_after = $(processIdRemove).parentNode.nextSibling;	
	elem.className = helpClass;
	Element.hide(processId_after);
	Element.update(elem, helpHtmlShow);
	Behaviour.apply();
}

/**
 * Collect loads for init onLoad
 * called by Behaviour.addLoadEvent
 */
function initLoad ()
{	
	getHeaderVars();
	getFormName(form_attr);	
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
            return new Effect.Fade('rp', {duration: 0.8, delay: 2.5})
       }
   }
}
   
/**
 * Get actual form name 
 * used: initLoad()
 *
 */
 function getFormName (attr)
{	
	if (typeof attr != 'undefined') {
		var content = attr.match(/(id="(\w+))/g);
	
		// object -> string conversion
		content = "" + content + "";
		
		// push var to the global scope
		form_id = content.substring(4);
	}
}


/**
 * Hide table(s) row(s)
 * used : oak.events.js
 *
 * @param {var} elem actual element to process
 */
 function hidetblsettime (elem)
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
 function hidetbl (elem)
{	
	var getHref = elem.nextSibling.nextSibling.getAttribute('href');
	
	// e.g. <name_of_file>?id=<var>
	var bid = getHref.split('id=');
	
	// push var to the global scope
	obid = $('o_' + bid[1]);	

	// process inner div
	Effect.Fade('i_' + bid[1],{duration: 1.0});

}


/**
 * Show table(s) row(s) and inner div
 * used : oak.events.js
 *
 * @param {string} elem actual element
 */
 function showtbl (elem)
{	
	var getHref = elem.nextSibling.nextSibling.getAttribute('href');
	
	// e.g. <name_of_file>?id=<var>
	var bid = getHref.split('id=');
	
	// process outer table tr
	$('o_' + bid[1]).style.visibility = 'visible';
	
	// process inner div
	Element.hide('i_' + bid[1]);
	Effect.Appear('i_' + bid[1],{duration: 1.0});

}




























