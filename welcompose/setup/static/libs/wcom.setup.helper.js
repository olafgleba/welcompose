/**
 * Project: Welcompose
 * File: wcom.setup.helper.js
 *
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * @author Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/** 
 * @fileoverview This file comprised javascript helper functions.
 * It contains functions that may be used application wide.
 */

/**
 * Constructs the Helper class
 * 
 * @class The Helper class defines a bunch of functions which doesn't 
 * belongs as regards content to just one class. Several functions be in use
 * within every Welcompose class. The scope is application wide.
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
Helper.prototype.isBrowser = Helper_isBrowser;
Helper.prototype.getAttrParentNode = Helper_getAttrParentNode;
Helper.prototype.getAttr = Helper_getAttr;
Helper.prototype.getAttrNextSibling = Helper_getAttrNextSibling;
Helper.prototype.getNextSiblingFirstChild = Helper_getNextSiblingFirstChild;
Helper.prototype.getDataParentNode = Helper_getDataParentNode;
Helper.prototype.getParentNodeNextSibling = Helper_getParentNodeNextSibling;
Helper.prototype.showDependingFormfield = Helper_showDependingFormfield;
Helper.prototype.setFormfieldGroup = Helper_setFormfieldGroup;
Helper.prototype.validate = Helper_validate;


/**
 * Check browser and his version.
 * <br />
 * Sometimes we need this to distinguish between browser
 * and versions to intercept their different abilities.
 *
 * @returns Boolean
 * @throws applyError on exception
 */
function Helper_isBrowser(_browser, _version)
{	
	try {
		this.browser = _setBrowserString();
		this.version = _setBrowserStringVersion();
			
		if (this.browser == _browser) {
			if (_version) {
				if(this.version == _version) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		} else { 
			return false;
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Process given navigator.agent string to find out browser name.
 * Helper for {@link #isBrowser}.
 *
 * @private
 * @returns var
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
 * Get navigator.agent, compare it, fill global var and return it.
 * Helper for {@link #isBrowser}.
 *
 * @private
 * @returns var browser
 * @throws applyError on exception
 */
function _setBrowserString ()
{
	try {			
		detect = navigator.userAgent.toLowerCase();
		var browser;

		if (_compare('safari')) {
			browser = 'sa';
		}
		else if (_compare('msie')) {
			browser = 'ie';
		}
		else if (_compare('firefox')) {
			browser = 'ff';
		}
		else {
			browser = '';
		}
		return browser;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Process given navigator.agent string to find out agent version.
 * Helper for {@link #isBrowser}.
 *
 * @private
 * @returns var browser
 * @throws applyError on exception
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
 * Getter for  parent node attribute.
 *
 * @param {string} attr Given attribute to use
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Parent node attribute of element
 * @throws applyError on exception
 */
function Helper_getAttrParentNode (attr, elem, level)
{
	this.browser = _setBrowserString();

	for (var a = elem; level > 0; level--) {
		a = a.parentNode;
	}
		
	if (this.browser == 'ie')
		return a.attributes[attr].value;
	else
		return a.getAttribute(attr);
}

/**
 * Getter for node attribute.
 *
 * @param {string} attr Given attribute to use
 * @param {object} elem Current element
 * @return object Attribute of element
 * @throws applyError on exception
 */
function Helper_getAttr (attr, elem)
{
	this.browser = _setBrowserString();
		
	if (this.browser == 'ie')
		return elem.attributes[attr].value;
	else
		return elem.getAttribute(attr);
}


/**
 * Getter for next sibling node attribute.
 *
 * @param {string} attr Given attribute to use
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Next sibling attribute
 * @throws applyError on exception
 */
function Helper_getAttrNextSibling (attr, elem, level)
{
	this.browser = _setBrowserString();
	
	if (this.browser == 'ie')
		level-- ;

	for (var a = elem; level > 0; level--) {
		a = a.nextSibling;
	}
		
	if (this.browser == 'ie')
		return a.attributes[attr].value;
	else
		return a.getAttribute(attr);
}

/**
 * Getter for next sibling first child node attribute.
 *
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Next sibling first child value
 * @throws applyError on exception
 */
function Helper_getNextSiblingFirstChild (elem, level)
{
	this.browser = _setBrowserString();
	
	if (this.browser == 'ie' || this.browser == 'sa')
		level-- ;

	for (var a = elem; level > 0; level--) {
		a = a.nextSibling;
	}
	return a.firstChild;
}

/**
 * Getter for parent node data.
 *
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Parent node data
 * @throws applyError on exception
 */
function Helper_getDataParentNode (elem, level)
{
	for (var a = elem; level > 0; level--) {
		a = a.parentNode;
	}
	return Helper.trim(a.firstChild.nodeValue.toLowerCase());	
}

/**
 * Getter for parent node next sibling.
 *
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Parent node next sibling
 * @throws applyError on exception
 */
function Helper_getParentNodeNextSibling (elem, level)
{
	this.browser = _setBrowserString();
	
	if (this.browser == 'ie' || this.browser == 'sa')
		level-- ;
		
	for (var a = elem.parentNode; level > 0; level--) {
		a = a.nextSibling;
	}
	if (typeof a != 'undefined')
		return a;
}

/**
 * Validate form elements on the fly.
 * <br />
 * Get the form element id attribute value and populate the
 * regex processed response into the layer <em>container</em>.
 * This assumes that we have corresponding html markup/css.
 * <br />
 * For in depths explanation please have a look on the online
 * project support area.
 *
 * @param {object} elem Current element
 * @throws applyError on exception
 */
function Helper_validate(elem)
{	
	var url		= this.validatePath;
	elemID		= $(elem).getAttribute('id');
	var elemVal	= $F(elem);
	var pars	= 'elemID=' + elemID + '&elemVal=' + elemVal;
	var container = elemID + '_container';
	
	var myAjax = new Ajax.Updater ( 
		{
			failure: container,
			success: container
		},
		url,
		{
			method: 'post',
			parameters: pars
		});		
}

/**
 * Switch form elements display relating to connection method.
 *
 * @param {object} elem Current element
 * @throws applyError on exception
 */
function Helper_showDependingFormfield (elem)
{
	var f;
		
	// get element value
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
	$('database_connection_method').focus();
}

/**
 * Switch form elements display relating to connection method.
 * Provide on load of page.
 *
 * @param {object} elem Current element
 * @throws applyError on exception
 */
function Helper_setFormfieldGroup ()
{		
	// get element value
	f = document.database.connection_method.options[document.database.connection_method.selectedIndex].value;
	
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
