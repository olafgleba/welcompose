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
var helpClass				= 'iHelp';
var helpClassRemove			= 'iHelpRemove';
var helpClassLvTwo			= 'iHelpLvTwo';
var helpClassRemoveLvTwo	= 'iHelpRemoveLvTwo';

/**
 * Define the used tbl upload class names
 * defines cascading styles to fit background images
 * used: oak.core.js
 */
var uploadClass		= 'upload showtbl';
var uploadClassHide	= 'uploadhide hidetbl';

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
            return new Effect.Fade('rp', {duration: 0.8, delay: 2.5})
       }
   }
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
function mFocus (inst, bgcolor, bcolor, bstyle)
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
 function mBlur (inst, bgcolor, bcolor, bstyle)
{
	inst.style.backgroundColor = bgcolor;
	inst.style.borderColor = bcolor;
	inst.style.borderStyle = bstyle;
}

/**
 * Set Focus on Form Element
 *
 * used : oak.events.js
 *
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process (e.g. ID)
 */
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
	process_id = elem.parentNode.parentNode.getAttribute(attr);
	
	//get id from parent form
	form_id = elem.parentNode.parentNode.parentNode.parentNode.getAttribute('id');
	
	// build target for func xhr()
	target_id = process_id;
	
	// tbl handling (e.g. _digits) to avoid multiple help files
	var _fetch = process_id.replace(/(_(\d+))/, '');	
	if (_fetch) {
		process_id = _fetch;
	}
	
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
	process_id_remove = elem.parentNode.parentNode.getAttribute(attr);
	process_id_after = $(process_id_remove).parentNode.nextSibling;	
	elem.className = helpClass;
	Element.update(elem, helpHtmlShow);
	Effect.Fade(process_id_after,{duration: 0.5});
	Behaviour.apply();
}

/**
 * Get help IDs and print string in html templates
 * Level 2
 * used : oak.events.js
 *
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process (e.g. ID)
 */
function getHelpLvTwo (elem, attr)
{	
	process_id = elem.parentNode.parentNode.getAttribute(attr);
	
	//get id from parent form
	form_id = elem.parentNode.parentNode.parentNode.parentNode.parentNode.getAttribute('id');
	
	// build target for func xhr()
	target_id = process_id;
	
	// tbl handling (e.g. _digits) to avoid multiple help files
	var _fetch = process_id.replace(/(_(\d+))/, '');	
	if (_fetch) {
		process_id = _fetch;
	}
	
	elem.className = helpClassRemoveLvTwo;
	Element.update(elem, helpHtmlHide);
	Behaviour.apply();
}

/**
 * Get help IDs and print string in html templates
 * Level 2
 * used : oak.events.js
 *
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process (e.g. ID)
 */
function removeHelpLvTwo (elem, attr)
{	
	process_id_remove = elem.parentNode.parentNode.getAttribute(attr);
	process_id_after = $(process_id_remove).parentNode.nextSibling;	
	elem.className = helpClassLvTwo;
	Element.update(elem, helpHtmlShow);
	Effect.Fade(process_id_after,{duration: 0.5});
	Behaviour.apply();
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
 function showtbl (elem)
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