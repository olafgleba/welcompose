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
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */
 
/** 
 * @fileoverview This file is the essential base of the oak javascript processing.
 * It describes all core functions. 
 * Need call to oak.strings.js for i18n separation
 *
 * @author Olaf Gleba og@creatics.de
 * @version $Id$ 
 */
 
 
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
 * Define debug output
 * @static
 * defined values:
 * 1 = development
 * 2 = production
 */
var debug = 1;


/**
 * Construct the base class
 * @class This is the basic class 
 * @constructor
 * @throws MemoryException if there is no more memory 
 * @throws applyError on exception
 */
function Base ()
{
	try {
		// Properties

		/**
		 * Define the used help class names
		 */
		this.helpClass = 'iHelp';
		
		/**
		 * Define the used help class names
		 */
		this.helpClassRemove = 'iHelpRemove';
		
		/**
		 * Define the used help class names
		 */
		this.helpClassLevelTwo = 'iHelpLevelTwo';
		
		/**
		 * Define the used help class names
		 */
		this.helpClassRemoveLevelTwo = 'iHelpRemoveLevelTwo';
		
		/**
		 * Define the div IDs for the navigation layers
		 */
		this.navLyOne = 'ly1';

		/**
		 * Define the div IDs for the navigation layers
		 */
		this.navLyTwo = 'ly2';
		
		/**
		 * Define the used table upload class names.
 		 * Cascading styles to fit background images
		 */
		this.uploadClass = 'upload showTableRow';

		/**
		 * Define the used table upload class names.
 		 * Cascading styles to fit background images
		 */
		 this.uploadClassHide = 'upload hideTableRow';
		
		/**
		 * Comprehensive colors application wide
		 */
		this.applicationTextColor = '#009a26';
		
		/**
		 * Build help strings delivered within DOM.
		 * Must corresponding to html notation
		 */
		this.helpHtmlShow = '<a href="#" title="' + showHelp + '"><img src="../static/img/icons/help.gif" alt="" /></a>';

		/**
		 * Build help strings delivered within DOM.
		 * Must corresponding to html notation
		 */
		 this.helpHtmlHide = '<a href="#" title="' + hideHelp + '"><img src="../static/img/icons/help_off.gif" alt="" /></a>';		

		/**
		 * Path for XHMLHTTPRequest imported files
		 */
		this.parseHelpUrl = '../parse.help.php';

		/**
		 * Path for XHMLHTTPRequest imported files
		 */
		this.parseNavUrl = '../parse.navigation.php';
		
	} catch (e) {
		_applyError(e);
	}
}


// Base Class Methods
Base.prototype.isArray = Base_isArray;
Base.prototype.isBoolean = Base_isBoolean;
Base.prototype.isString = Base_isString;
Base.prototype.isObject = Base_isObject;
Base.prototype.isFunction = Base_isFunction;
Base.prototype.isUndefined = Base_isUndefined;
Base.prototype.isNumber = Base_isNumber;
Base.prototype.isEmpty = Base_isEmpty;
Base.prototype.isNull = Base_isNull;


/**
 * Implements method of prototype class Base
 * Examine the giving var is an array
 * @requires Base The Base Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isArray(elem) {
    return Base.prototype.isObject(elem) && elem.constructor == Array;
}
/**
 * Implements method of prototype class Base
 * Examine the giving var is true oder false
 * @requires Base The Base Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isBoolean(elem) {
    return typeof elem == 'boolean';
}
/**
 * Implements method of prototype class Base
 * Examine the giving var is a string
 * @requires Base The Base Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
 function Base_isString(elem) {
    return typeof elem == 'string';
}
/**
 * Implements method of prototype class Base
 * Examine the giving var is an object
 * @requires Base The Base Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isObject(elem) {
    return (elem && typeof elem == 'object') || Base.prototype.isFunction(elem);
}
/**
 * Implements method of prototype class Base
 * Examine the giving var is a function
 * @requires Base The Base Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isFunction(elem) {
    return typeof elem == 'function';
}
/**
 * Implements method of prototype class Base
 * Examine the giving var is undefined
 * @requires Base The Base Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isUndefined(elem) {
    return typeof elem == 'undefined';
}
/**
 * Implements method of prototype class Base
 * Examine the giving var is a number
 * @requires Base The Base Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isNumber(elem) {
    return typeof elem == 'number' && isFinite(elem);
}
/**
 * Implements method of prototype class Base
 * Examine the giving var is empty
 * @requires Base The Base Class
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
 * Implements method of prototype class Base
 * Examine the giving var is Null
 * @requires Base The Base Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isNull(elem) {
    return typeof elem == 'object' && !elem;
}




/**
 * Construct a new OakInit object
 * @class This is the Init class to call on load of page  
 * @constructor
 * @throws MemoryException if there is no more memory 
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function OakInit ()
{
	try {
		// methods

		
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
OakInit.prototype = new Base();

/**
 * Instance Methods from prototype @class OakInit
 */
OakInit.prototype.load = OakInit_load;
OakInit.prototype.getVars = OakInit_getVars;
OakInit.prototype.getCbxStatus = OakInit_getCbxStatus;


/**
 * Implements method <show> of prototype class Help
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class
 * @throws applyError on exception
 */
function OakInit_load ()
{	
	OakInit.getVars();
	
	if (typeof checkbox_status != 'undefined' && OakInit.isArray(checkbox_status)) {
		OakInit.getCbxStatus(checkbox_status);
	}
}


/**
 * Implements method <show> of prototype class Help
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class
 * @throws applyError on exception
 */
function OakInit_getVars ()
{
   if (typeof response != 'undefined' && $('rp')) {
       if (response == 1) {
            return new Effect.Fade('rp', {duration: 0.8, delay: 2.0})
       }
   }
   if (typeof selection != 'undefined' && $('sel')) {
       if (response == 1) {
            return new Effect.Fade('sel', {duration: 0.8, delay: 2.0})
       }
   }
}

/**
 * Implements method <show> of prototype class Help
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class
 * @throws applyError on exception
 */
function OakInit_getCbxStatus (elems)
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
 * Building new instance for class Help
 */
OakInit = new OakInit();



/**
 * Construct a new DevError object
 * @class This is the basic Error class wich is throwed by explicit setting  
 * @constructor
 * @param {string} msg exception error message presented by catch statement
 * @throws MemoryException if there is no more memory 
 */
function DevError(msg) 
{
	this.name = 'DevError';
	this.message = msg;
}

/**
 * Building new instance for obj DevError to throw errors
 * at specific points within functions
 */
DevError.prototype = new Error;


/**
 * Construct a new Help object
 * @class This is the basic Help class 
 * @constructor
 * @throws MemoryException if there is no more memory 
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Help ()
{
	try {
		// methods

		/**
		 * Get new XMLHttpRequest Object by private function
		 */
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

/**
 * Implements method <show> of prototype class Help
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class
 * @throws applyError on exception
 */
function Help_show (elem, level)
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
	
		Element.update(this.elem, this.helpHtmlHide);
		Behaviour.apply();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method <hide> of prototype class Help
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class
 * @throws applyError on exception
 */
function Help_hide (elem, level)
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
		Element.update(this.elem, this.helpHtmlShow);
		Behaviour.apply();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for class Help
 */
Help = new Help();


/**
 * Construct a new Navigation object
 * @class This is the basic Navigation class 
 * @constructor
 * @throws MemoryException if there is no more memory 
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Navigation ()
{
	try {		
		// methods
		
		/**
		 * Get new XMLHttpRequest Object by private function
		 */
		this.req = _buildXMLHTTPRequest();
		
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Navigation.prototype = new Base();

/**
 * Instance Methods from prototype @class Navigation
 */
Navigation.prototype.show = Navigation_show;

/**
 * Implements method <show> of prototype class Navigation
 * @param {string} name The name of the file to catch
 * @param {string} target Wich depth of implementation to apply css class
 * @throws applyError on exception
 */
function Navigation_show (name, target)
{
	try {
		// properties
		this.name = name;
		this.url = this.parseNavUrl + '?page=' + this.name;	
		
		switch (this.target) {
			case '2' :
					this.target = this.navLyTwo;
				break;
			default :
					this.target = this.navLyOne;	
		}
		
		if (typeof this.req != 'undefined') {
		
			var url		= this.url;
			var target	= this.target;
		
			_req.open('GET', url, true);
			_req.onreadystatechange = function () { _processNavigation(url,target);};
			_req.send('');
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for @class Navigation
 */
Navigation = new Navigation();


/**
 * Construct a new Forms object
 * @class This is the basic class to process Forms/fields 
 * @constructor
 * @throws MemoryException if there is no more memory 
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Forms ()
{
	try {		
		// methods

		
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
 * DOM triggers to attach onEvent behaviours
 * @param {string} elem Actual element
 * @param {string} bgcolor Defined background color
 * @param {string} bcolor Defined border color
 * @param {string} bstyle Defined border style attr
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
 * Building new instance for @class Forms
 */
Forms = new Forms();


/**
 * Construct a new Status object
 * @class This is the basic Navigation class 
 * @constructor
 * @throws MemoryException if there is no more memory 
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Status ()
{
	try {		
		// methods
				
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Forms */
Status.prototype = new Base();

/**
 * Instance Methods from prototype @class Status
 */
Status.prototype.getCbx = Status_getCbx;

/**
 * Implements method <getCheckboxes> of prototype class Status
 * @param {array} elems actual elements
 * @throws applyError on exception
 */
function Status_getCbx (elems)
{
	try {
		// properties		
		this.elems = elems;
		
		for (var e = 0; e < this.elems.length; e++) {
			
			// build new div
			var range = String(this.elems[e])  + '_container';
			
			if ($(range)) {
				if ($(this.elems[e]).checked == true) {
	
				allNodes = document.getElementsByClassName("bez");
				
				for (var i = 0; i < allNodes.length; i++) {
					var _process = allNodes[i].parentNode.parentNode.getAttribute('id');		
					if (_process == range) {
						allNodes[i].style.color = this.applicationTextColor;
					}
				}
					Element.hide($(range));
					Effect.Appear($(range),{duration: 0.6});
				} else {
					Effect.Fade($(range),{duration: 0.6});
				}
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for @class Navigation
 */
Status = new Status();




/**
 * Error handling
 * String contains exception param with different provided debug information
 * @private
 * @param {string} exception error presented by catch statement
 */
function _applyError (exception)
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
 * Build new XMLHTTPRequest Instance
 * @private
 * @return _req
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
 * Get the corresponding help file and process it
 *
 * @private
 * @param {string} url path
 * @param {string} target Wich layer div should be used
 * @throws applyError on exception
 * @throws DevError on condition
 */
 function _processHelp (url, target)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				new Insertion.After($(target).parentNode, _req.responseText);				
				var target_after = $(target).parentNode.nextSibling;
				Element.hide(target_after);
				Effect.Appear(target_after, {duration: 0.8});
			} else {
	  			throw new DevError(_req.statusText);
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Get the corresponding help file and process it
 *
 * @private
 * @param {string} url path
 * @param {string} target Wich layer div should be used
 * @throws applyError on exception
 * @throws DevError on condition
 */
 function _processNavigation (url, target)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				Element.hide($('topsubnavconstatic'));
				Element.update(target, _req.responseText);
				Behaviour.apply();
			} else {
	  			throw new DevError(_req.statusText);
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Set Focus on Form Element
 * @private
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process
 */
 function _setCorrespondingFocus (elem, attr)
{
	var inst = elem.parentNode.parentNode.getAttribute(attr);
	$(inst).focus();
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