/**
 * Project: Oak
 * File: oak.setup.helper.js
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
 * $Id: oak.helper.js 654 2006-11-21 16:24:40Z olaf $
 *
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

/** 
 * @fileoverview This file comprised javascript helper functions.
 * It contains functions that may be used application wide.
 * 
 * @author Olaf Gleba og@creatics.de
 * @version $Id: oak.helper.js 654 2006-11-21 16:24:40Z olaf $ 
 */



/**
 * Constructs the Helper class
 * 
 * @class The Mediamanager class miscellaneous is the appropriate class for
 * the help enviroment. The scope is application wide.
 *
 * Prototype methods:
 * 
 *
 * unsupportsEffects()
 * 
 *
 * unsupportsElems()
 * 
 * 
 * defineWindowX()
 * 
 *
 * defineWindowY()
 * 
 *
 * getAttrParentNode()
 * 
 *
 * getAttr()
 * 
 *
 * getAttrNextSibling()
 * 
 *
 * getNextSiblingFirstChild()
 * 
 *
 * getDataParentNode()
 * 
 *
 *
 * @see Base
 * @constructor
 * @throws applyError on exception
 */
function Helper ()
{
	try {
		// no properties		
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Helper.prototype = new Base();


/**
 * Instance Methods from prototype @class Helper
 */
Helper.prototype.unsupportsEffects = Helper_unsupportsEffects;
Helper.prototype.unsupportsElems = Helper_unsupportsElems;
Helper.prototype.getAttrParentNode = Helper_getAttrParentNode;
Helper.prototype.getAttr = Helper_getAttr;
Helper.prototype.getAttrNextSibling = Helper_getAttrNextSibling;
Helper.prototype.getNextSiblingFirstChild = Helper_getNextSiblingFirstChild;
Helper.prototype.getDataParentNode = Helper_getDataParentNode;
Helper.prototype.showDependingFormfield = Helper_showDependingFormfield;


/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @requires Helper The Helper Class
 * @throws applyError on exception
 */
function Helper_unsupportsEffects(exception)
{	
	try {
		//properties
		this.browser = _setBrowserString();
		this.version = _setBrowserStringVersion();
		this.exception = exception;
			
		if ((this.browser == "Internet Explorer") || (this.browser == "Safari" && !this.exception)) {
			return true;
		} else { 
			return false;
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @requires Helper The Helper Class
 * @throws applyError on exception
 */
function Helper_unsupportsElems(exception)
{	
	try {
		//properties
		this.browser = _setBrowserString();
		this.version = _setBrowserStringVersion();
		this.exception = exception;
		
		if ((this.browser == "Internet Explorer") || (this.browser == "Safari" && !this.exception)) {
			return true;
		} else { 
			return false;
		}
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
 * @throws applyError on exception
 */
function _compare (string)
{
	try {
		res = detect.indexOf(string) + 1;
		thestring = string;
		return res;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
 */
function _setBrowserString ()
{
	try {			
		detect = navigator.userAgent.toLowerCase();
		var browser;

		if (_compare('safari')) {
			browser = 'Safari';
		}
		else if (_compare('msie')) {
			browser = 'Internet Explorer';
		}
		else {
			browser = 'Unknown Browser';
		}
		return browser;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
 */
function _setBrowserStringVersion ()
{
	try {			
		_setBrowserString();
		var version;
		
		version = detect.charAt(res + thestring.length);

		return version;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
 */
function _setBrowserStringOS ()
{
	try {			
		detect = navigator.userAgent.toLowerCase();
		var os;
		
		if (_compare('linux')) {
			os = 'Linux';
		}
		else if (_compare('x11')) {
			os = 'Unix';
		}
		else if (_compare('win')) {
			os = 'Windows';
		}
		else if (_compare('mac')) {
			os = 'Mac';
		}
		else {
			os = 'Unknown operating system';
		}
		return os;
	} catch (e) {
		_applyError(e);
	}
}

function Helper_getAttrParentNode (attr, elem, level)
{
	this.browser = _setBrowserString();

	for (var a = elem; level > 0; level--) {
		a = a.parentNode;
	}
		
	if (this.browser == 'Internet Explorer')
		return a.attributes[attr].value;
	else
		return a.getAttribute(attr);
}

function Helper_getAttr (attr, elem)
{
	this.browser = _setBrowserString();
		
	if (this.browser == 'Internet Explorer')
		return elem.attributes[attr].value;
	else
		return elem.getAttribute(attr);
}

function Helper_getAttrNextSibling (attr, elem, level)
{
	this.browser = _setBrowserString();
	
	if (this.browser == 'Internet Explorer')
		level-- ;

	for (var a = elem; level > 0; level--) {
		a = a.nextSibling;
	}
		
	if (this.browser == 'Internet Explorer')
		return a.attributes[attr].value;
	else
		return a.getAttribute(attr);
}

function Helper_getNextSiblingFirstChild (elem, level)
{
	this.browser = _setBrowserString();
	
	if (this.browser == 'Internet Explorer' || this.browser == 'Safari')
		level-- ;

	for (var a = elem; level > 0; level--) {
		a = a.nextSibling;
	}
	return a.firstChild;
}

function Helper_getDataParentNode (elem, level)
{
	for (var a = elem; level > 0; level--) {
		a = a.parentNode;
	}
	return Helper.trim(a.firstChild.nodeValue.toLowerCase());	
}


function Helper_showDependingFormfield (elem)
{
	var f;
		
	// get status value
	f = elem.options[elem.selectedIndex].value;
	
	if (f == 'socket') {	
		$('_host').style.display = 'none';
		$('_port').style.display = 'none';
		$('_socket').style.display = 'block';
	} else {
		$('_host').style.display = 'block';
		$('_port').style.display = 'block';
		$('_socket').style.display = 'none';
	}
}


/**
 * Building new object instance of class Helper
 */
Helper = new Helper();
