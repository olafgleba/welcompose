/**
 * Project: Welcompose
 * File: wcom.setup.core.js
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
 * @fileoverview The main Welcompose javascript enviroment.
 */


/**
 * Define debug output string. 
 *
 * Switch differs how try/catch will handle exceptions.<br />
 * 0 = No debug output<br />
 * 1 = Development<br />
 * 2 = Production<br />
 * <br />
 * Scope is application wide.
 *
 * @type Number
 */
function _debug ()
{
	debug = 1;
}

/**
 * Build new XMLHTTPRequest object instance.
 * Used in classes constructor wherever an XMLHTTPRequest is needed.
 *
 * <br /><br />Example:
 * <pre><code>
// instance XMLHttpRequest object
this.req = _buildXMLHTTPRequest();
 * </code></pre>
 *
 * @throws applyError on exception
 * @return Object XMLHTTPRequest object instance
 * @type Object
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
 * Display exception alert within the try/catch handling
 * relating to given debug variable value.
 * 
 * <br /><br />Example:
 * <pre><code>
try {
	<contents>
} catch (e) {
	_applyError(e);
}</code></pre>
 * 
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
 * errors. It is inherited from the standard javascript error class prototype.
 * Scope application wide. Mainly used in all
 * process_xxx functions to track errors on xhr state, which
 * are not traped by the try/catch structure.
 *
 * <br /><br />Example:
 * <pre><code>throw new Errors(object);</code></pre>
 *
 * @constructor
 * @param {string} msg Exception error message
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
 * @class The base class is the most important class of the wcom
 * javascript enviroment, cause all other classes (exclude Errors) are inherited from that class.
 * It predefines properties and methods which are supposed to be used application wide.
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
		this.applicationTextColor = '#ff620d';
		
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
		this.parseHelpPath = 'parse/parse.help.php';
		this.validatePath = 'validate.js.php';
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
 * Constructs the Init class
 * 
 * @class The Init class is supposed to be used on load of page.
 * <br />
 * Right now the function {@link #load} is used as an argument for the thirdparty lib method
 * <em>Behaviour.addLoadEvent(Init.load);</em> and is not supposed to called manually. If you
 * you want something to happen on load of page, add another prototype function (e.g. {@link #getVars}) to class Init
 * and call it within the {@link #load} function instead.
 *
 * <br /><br />Example (Schema):
 * <pre><code>
function Init_load ()
{	
	try {
		// Do something while page load
		Init.getVars();
		...
		}
	} catch (e) {
		_applyError(e);
	}
}
 * </code></pre>
 *
 * @constructor
 * @throws applyError on exception
 */
function Init ()
{
	try {

	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base class */
Init.prototype = new Base();

/* Methods of prototype @class Init */
Init.prototype.load = Init_load;
Init.prototype.getVars = Init_getVars;

/**
 * All functions supposed to be called on load must take place within this function.
 * 
 * @throws applyError on exception
 */
function Init_load ()
{	
	try {
		// DONT EVER CHANGE THIS FUNCTION CALL
		// set global debug var first
		_debug();
		
		Init.getVars();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Getter function for several actions to be executed on load of page.
 * Depends on delivered variables in the html markup.
 * 
 * @throws applyError on exception
 */
function Init_getVars ()
{
	try {
		if (typeof r != 'undefined' && Init.isNumber(r)) {
			if (r == 1) {
				Helper.setFormfieldGroup();
			}
		}	
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Init
 */
Init = new Init();


/**
 * Constructs the Help class
 * 
 * @class The Help class is the appropriate class for
 * the help enviroment. The scope is application wide.
 * For consistent reason, it comprises the help handling of the Mediamanger too.
 *
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
 * Show help html element.
 * Import related help file depending on current element pointer.
 *
 * @see #processHelp
 * @param {string} elem Current element
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
		
		if ($$('.botbg').first() && !i) {
			this.formId = Helper.getAttr('id', $$('.botbg').first());
		} 
		
		else if ($$('.botbg').first() && i) {
			this.processId = this.processId.replace(/_\d+/, '');
			this.formId = Helper.getAttr('id', $$('.botbg').first());
		} else {
			this.processId = this.processId.replace(/_\d+/, '');
			this.formId = Helper.getDataParentNode(this.elem, 1);
		}	
			
		this.url = this.parseHelpPath + '?page=' + this.formId + '_' + this.processId;
			
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
 * @param {string} elem Current element
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
 * @see #show
 * @param {string} ttarget Layer to process
 * @throws Errors on req object status code other than 200
 * @throws applyError on exception
 */
function Help_processHelp (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				Element.insert($(ttarget).parentNode, {'after': _req.responseText});
				var ttarget_after = $(ttarget).parentNode.nextSibling;			
				Element.hide(ttarget_after);
				Effect.Appear(ttarget_after,{delay: 0, duration: 0.3});		
			} else {
	  			throw new Errors(_req.statusText);
			}
		}
	} catch (e) {
		_applyError(e, alertOnMissingHelpFiles);
	}
}

/**
 * Set Focus related to pointed element.
 *
 * @param {string} elem Current element
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
 * Constructs the Forms class.
 * 
 * @class The Forms class handles all actions related to html form elements.
 * The scope is application wide.
 *
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
 * Style elements depending on given params
 *
 * <br /><br />Example:
 * <pre><code>
Forms.setOnEvent(this, '','#0c3','dotted');
 * </code></pre>
 * 
 * @param {string} elem Current element
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