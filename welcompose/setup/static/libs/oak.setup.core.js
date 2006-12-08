/**
 * Project: Oak
 * File: oak.setup.core.js
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
 * $Id: oak.core.js 654 2006-11-21 16:24:40Z olaf $
 *
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */
 
/** 
 * @fileoverview This is the core Oak javascript file. 
 * 
 * @author Olaf Gleba og@creatics.de
 * @version $Id: oak.core.js 654 2006-11-21 16:24:40Z olaf $ 
 */
 


/**
 * Define debug output string
 *
 * Switch differs how try/catch will handle exceptions
 * 0 = no debug output
 * 1 = development
 * 2 = production
 *
 * @static
 * @link See oak.string.js for the strings content
 */
var debug = 1;

/**
 * Build new XMLHTTPRequest object instance
 *
 * @private
 * @throws applyError on exception
 * @return XMLHTTPRequest object instance
 * @type object
 */
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
		_applyError(e);
	}
}

/**
 * Alerted String (errStr) contains exception params with different
 * provided debug information.
 *
 * @private
 * @param {object} exception error obj presented by catch statement
 */
function _applyError (exception)
{
	var errStr;
	
	switch (debug) {
		case 0 :
			return false;
		break;
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
 * Constructs the Errors class
 *
 * @class The Errors class tracks all manually thrown
 * errors. Scope application wide. Mainly used in all
 * process_xxx functions to track errors on xhr state, which
 * are not processed by the try/catch structure.
 *
 * example:
 * < throw new Errors(object); > 
 *
 * Prototype Methods:
 * 
 * **Right now there are not methods defined**
 *
 *
 * @constructor
 * @param {string} msg Exception error message presented by catch statement
 */
function Errors(msg) 
{
	this.message = msg;
}

/**
 * Building new instance for @class Errors
 */
Errors.prototype = new Error();






/**
 * Constructs the Base class
 * 
 * @class This class is the most important class of the oak
 * javascript enviroment, cause all other classes derived from that class.
 * It predefines properties and methods which are supposed to be used application wide.
 *
 * Prototype Methods:
 * 
 * isArray()
 * Examine the giving var is of type Array
 *
 * isBoolean()
 * Examine the giving var is of type Bool
 *
 * isString()
 * Examine the giving var is of type String
 *
 * isObject()
 * Examine the giving var is of type Object
 *
 * isFunction()
 * Examine the giving var is a function
 *
 * isUndefined()
 * Examine the giving var is undefined
 *
 * isNumber()
 * Examine the giving var is of type Number
 *
 * isEmpty()
 * Examine the giving var has no values
 *
 * isNull()
 * Examine the giving var is Null
 *
 * trim()
 * Delete whitspaces before and after the giving string
 *
 *
 * @constructor
 * @throws applyError on exception
 */
function Base ()
{
	try {
		/**
		 * Help class
		 */
		this.helpClass = 'iHelp';

		/**
		 * Help class
		 */
		this.helpClassRemove = 'iHelpRemove';

		/**
		 * Comprehensive color application wide
		 */
		this.applicationTextColor = '#009a26';
		
		/**
		 * Help string supposed to delivered within the DOM.
		 */
		this.helpHtmlShow = '<a href="#" title="' + showHelp + '"><img src="static/img/icons/help.gif" alt="" /></a>';

		/**
		 * Help string supposed to delivered within the DOM.
		 */
		this.helpHtmlHide = '<a href="#" title="' + hideHelp + '"><img src="static/img/icons/help_off.gif" alt="" /></a>';
		
		/**
		 * Path for dynamically imported file
		 */
		this.parseHelpUrl = 'parse/parse.help.php';
	} catch (e) {
		_applyError(e);
	}
}

/* Methods of prototype @class Base */
Base.prototype.isArray = Base_isArray;
Base.prototype.isBoolean = Base_isBoolean;
Base.prototype.isString = Base_isString;
Base.prototype.isObject = Base_isObject;
Base.prototype.isFunction = Base_isFunction;
Base.prototype.isUndefined = Base_isUndefined;
Base.prototype.isNumber = Base_isNumber;
Base.prototype.isEmpty = Base_isEmpty;
Base.prototype.isNull = Base_isNull;
Base.prototype.trim = Base_trim;

/**
 * Examine the giving var is of type Array
 *
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isArray(elem) {
    return Base.prototype.isObject(elem) && elem.constructor == Array;
}
/**
 * Examine the giving var is of type Bool
 *
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isBoolean(elem) {
    return typeof elem == 'boolean';
}
/**
 * Examine the giving var is of type String
 *
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
 function Base_isString(elem) {
    return typeof elem == 'string';
}
/**
 * Examine the giving var is of type Object
 *
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isObject(elem) {
    return (elem && typeof elem == 'object') || Base.prototype.isFunction(elem);
}
/**
 * Examine the giving var is a function
 *
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isFunction(elem) {
    return typeof elem == 'function';
}
/**
 * Examine the giving var is undefined
 *
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isUndefined(elem) {
    return typeof elem == 'undefined';
}
/**
 * Examine the giving var has no values
 *
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isNumber(elem) {
    return typeof elem == 'number' && isFinite(elem);
}
/**
 * Examine the giving var is empty
 *
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isEmpty(elem) {
    var i, v;
    if (Base.prototype.isObject(o)) {
        for (i in elem) {
            v = elem[i];
            if (Base.prototype.isUndefined(v) && Base.prototype.isFunction(v)) {
                return false;
            }
        }
    }
    return true;
}
/**
 * Examine the giving var is Null
 *
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isNull(elem) {
    return typeof elem == 'object' && !elem;
}
/**
 * Delete whitspaces before and after the giving string
 *
 * @param {var} elem Actual element
 * @return elem
 * @type String
 */
function Base_trim(elem) {
  return elem.replace(/^\s*|\s*$/g, "");
}



/**
 * Constructs the Help class
 * 
 * @class The Help class is the appropriate class for
 * the help enviroment. The scope is application wide.
 *
 * Prototype methods:
 * 
 * show()
 * Import related help file depending on actual element pointer.
 * Show help html element.
 *
 * hide()
 * Hide help html element.
 *
 * processHelp()
 * Update content with XMLHttpRequest response.
 *
 * setCorrespondingFocus()
 * Set Focus related to pointed element.
 *
 * @see Base
 * @constructor
 * @throws applyError on exception
 */
function Help ()
{
	try {
		// instance XMLHttpRequest object
		this.req = _buildXMLHTTPRequest();
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Help.prototype = new Base();

/**
 * Instance Methods from prototype @class Help
 */
Help.prototype.show = Help_show;
Help.prototype.hide = Help_hide;
Help.prototype.processHelp = Help_processHelp;
Help.prototype.setCorrespondingFocus = Help_setCorrespondingFocus;

/**
 * Import related help file depending on actual element pointer.
 * Show help html element.
 *
 * @param {string} elem Actual element
 * @throws applyError on exception
 */
function Help_show (elem)
{
	try {
		// properties
		this.elem = elem;
		this.elem.className = this.helpClassRemove;
		this.attr = 'for';
		this.processId = Helper.getAttrParentNode(this.attr, this.elem, 2);
		this.ttarget = this.processId;
				
		var i = this.processId.match(/_\d+/);
		
		if (document.getElementsByClassName('botbg')[0] && !i) {
			this.formId = Helper.getAttr('id', document.getElementsByClassName('botbg')[0]);
		} 
		
		else if (document.getElementsByClassName('botbg')[0] && i) {
			this.processId = this.processId.replace(/_\d+/, '');
			this.formId = Helper.getAttr('id', document.getElementsByClassName('botbg')[0]);
		
		} else {
			this.processId = this.processId.replace(/_\d+/, '');
			this.formId = Helper.getDataParentNode(this.elem, 1);
		}	
			
		this.url = this.parseHelpUrl + '?page=' + this.formId + '_' + this.processId;
			
		if (typeof this.req != 'undefined') {
		
			var _url		= this.url;
			var _ttarget	= this.ttarget;
		
			_req.open('GET', _url, true);
			_req.onreadystatechange = function () { Help.processHelp(_ttarget);};
			_req.send('');
		}
		
		Help.setCorrespondingFocus(this.elem, this.attr);
		Element.update(this.elem, this.helpHtmlHide);

		Behaviour.reapply('.' + this.elem.className);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Hide help html element.
 *
 * @param {string} elem Actual element
 * @throws applyError on exception
 */
function Help_hide (elem)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'for';
		this.processId = Helper.getAttrParentNode(this.attr, this.elem, 2);
		this.processIdAfter = $(this.processId).parentNode.nextSibling;
		
		this.elem.className = this.helpClass;
		
		Effect.Fade(this.processIdAfter,{delay: 0, duration: 0.4});

		Help.setCorrespondingFocus(this.elem, this.attr);
		Element.update(this.elem, this.helpHtmlShow);
	
		Behaviour.reapply('.' + this.elem.className);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Update content with XMLHttpRequest response.
 *
 * @param {string} ttarget Layer to process
 * @throws Errors on req object status code other than 200
 * @throws applyError on exception
 */
function Help_processHelp (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				new Insertion.After($(ttarget).parentNode, _req.responseText);				
				var ttarget_after = $(ttarget).parentNode.nextSibling;
				Element.hide(ttarget_after);
				Effect.Appear(ttarget_after,{delay: 0, duration: 0.5});
			} else {
	  			throw new Errors(_req.statusText);
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Set Focus related to pointed element.
 *
 * @param {string} elem Actual element
 * @param {string} attr Node Attribute
 */
function Help_setCorrespondingFocus (elem, attr)
{
	this.inst = Helper.getAttrParentNode(attr, elem, 2);
	$(this.inst).focus();
}

/**
 * Building new object instance of class Help
 */
Help = new Help();






/**
 * Constructs the Forms class
 * 
 * @class The Forms class is the appropriate class for forms processing.
 * The scope is application wide.
 *
 * Prototype methods:
 * 
 * setOnEvent()
 * Used to style elements depending on given params
 *
 * storeFocus()
 * Tracks the actual Focus and makes it available application wide.
 * This is actual used to fire alerts within mediamanager.
 *
 * @see Base
 * @constructor
 * @throws applyError on exception
 */
function Forms ()
{
	try {		
		// no properties
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Forms.prototype = new Base();

/**
 * Instance Methods from prototype @class Forms
 */
Forms.prototype.setOnEvent = Forms_setOnEvent;

/**
 * Used to style elements depending on given params
 * 
 * @param {string} elem Actual element
 * @param {string} bgcolor Define background color
 * @param {string} bcolor Define border color
 * @param {string} bstyle Define border style attribute
 * @throws applyError on exception
 */
function Forms_setOnEvent (elem, bgcolor, bcolor, bstyle)
{
	try {
		this.elem = elem;
		this.bgcolor = bgcolor;
		this.bcolor = bcolor;
		this.bstyle = bstyle;
		
		this.elem.style.backgroundColor = this.bgcolor;
		this.elem.style.borderColor = this.bcolor;
		this.elem.style.borderStyle = this.bstyle;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Forms
 */
Forms = new Forms();