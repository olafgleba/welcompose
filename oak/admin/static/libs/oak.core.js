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
 * @fileoverview This file is the essential Oak javascript enviroment.
 * It describes all core classes and functions. It is needed to call oak.strings.js before embedding this file,
 * to make it unnecessary to loop this core file through the i18n parser.
 *
 * @author Olaf Gleba og@creatics.de
 * @version $Id$ 
 */
 

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
		this.helpClassRemove = 'iHelpRemove';
		this.helpClassLevelTwo = 'iHelpLevelTwo';
		this.helpClassRemoveLevelTwo = 'iHelpRemoveLevelTwo';
		this.helpClassLevelThree = 'iHelpLevelThree';
		this.helpClassRemoveLevelThree = 'iHelpRemoveLevelThree';
		this.helpClassMediamanager = 'iHelpMediamanager';
		this.helpClassRemoveMediamanager = 'iHelpRemoveMediamanager';
		
		/**
		 * Define the div IDs for the navigation layers
		 */
		this.navLyOne = 'ly1';

		/**
		 * Define the div IDs for the navigation layers
		 */
		this.navLyTwo = 'ly2';
		
		/**
		 * Define the div ID for several help layers
		 */
		this.helpLyMediamanager = 'lyMediamanager';
		
		/**
		 * Define the div ID for several help layers
		 */
		this.helpLyMediamanagerMyLocal = 'lyMediamanagerMyLocal';
		
		/**
		 * Define the div ID for several help layers
		 */
		this.helpLyMediamanagerMyFlickr = 'lyMediamanagerMyFlickr';
		
		/**
		 * Define the used table upload class names.
 		 * Cascading styles to fit background images
		 */
		this.uploadClass = 'upload showTableRow';

		/**
		 * Define the used table upload class names.
 		 * Cascading styles to fit background images
		 */
		this.uploadClassHide = 'uploadhide hideTableRow';
		
		/**
		 * Define the used table upload class names.
 		 * Cascading styles to fit background images
		 */
		this.mediamanagerClass = 'showMediamanagerElement';

		/**
		 * Define the used table upload class names.
 		 * Cascading styles to fit background images
		 */
		this.mediamanagerClassHide = 'hideMediamanagerElement';
		
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
		 * Build help strings delivered within DOM.
		 * Must corresponding to html notation
		 */
		this.elementHtmlShow = '<a href="#" title="' + showElement + '"><img src="../static/img/icons/open.gif" alt="" /></a>';

		/**
		 * Build help strings delivered within DOM.
		 * Must corresponding to html notation
		 */
		 this.elementHtmlHide = '<a href="#" title="' + hideElement + '"><img src="../static/img/icons/close.gif" alt="" /></a>';		

		/**
		 * Path for XHMLHTTPRequest imported files
		 */
		this.parseHelpUrl = '../parse.help.php';

		/**
		 * Path for XHMLHTTPRequest imported files
		 */
		this.parseNavUrl = '../parse.navigation.php';
		
		/**
		 * Path for XHMLHTTPRequest imported files
		 */
		this.parseMedUrl = '../mediamanager/mediamanager.php';
		
		/**
		 * Path for XHMLHTTPRequest imported files
		 */
		this.keyPressDelay = null;
		
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
		/**
		 * Get new XMLHttpRequest Object by private function
		 */
		this.req = _buildXMLHTTPRequest();
		
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
OakInit.prototype.processOakInit = OakInit_processOakInit;

/**
 * Implements method of prototype class OakInit
 * @param {global} checkbox_status
 * @see Base Base is the base class for this
 * @throws applyError on exception
 */
function OakInit_load ()
{	
	try {
		OakInit.getVars();
		
		if (typeof checkbox_status != 'undefined' && OakInit.isArray(checkbox_status)) {
			OakInit.getCbxStatus(checkbox_status);
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class OakInit
 * @param {global} response 
 * @param {global} selection
 * @throws applyError on exception
 * @see Base Base is the base class for this
 * @return new Effect Instance
 */
function OakInit_getVars ()
{
	try {
		if (typeof response != 'undefined' && $('rp')) {
			if (response == 1) {
				return new Effect.Fade('rp', {duration: 0.8, delay: 2.0});
			}
		}
	   if (typeof selection != 'undefined' && $('sel')) {
			if (selection == 1) {
				return new Effect.Fade('sel', {duration: 0.8, delay: 2.0});
			}
		}
	   if (typeof mediamanager != 'undefined' && OakInit.isNumber(mediamanager)) {
			if (mediamanager == 1) {
						
				this.url = this.parseMedUrl + '?page=mediamanager';
				if (typeof this.req != 'undefined') {
		
					var _url		= this.url;
					var _ttarget	= 'column';
		
					_req.open('GET', _url, true);
					_req.onreadystatechange = function () { OakInit.processOakInit(_ttarget);};
					_req.send('');
				}
			}
			
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class OakInit
 * @param {array} elem Array of one or more Elements
 * @see Base Base is the base class for this
 * @throws applyError on exception
 */
function OakInit_getCbxStatus (elems)
{
	try {
		for (var e = 0; e < elems.length; e++) {
			
			// object -> string conversion
			var range = String(elems[e])  + '_container';
			
			if ($(range)) {
				if ($(elems[e]).checked === true) {
	
				allNodes = document.getElementsByClassName("bez");
				
				for (var i = 0; i < allNodes.length; i++) {
					var _process = allNodes[i].parentNode.parentNode.getAttribute('id');		
					if (_process == range) {
						allNodes[i].style.color = this.applicationTextColor;
					}
				}
					Element.show(range);
				} else {
					Element.hide(range);
				}
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help
 * Get and display the html help files
 *
 * @param {string} url path
 * @param {string} target Wich layer div should be used
 * @throws applyError on exception
 * @throws DevError on condition
 */
function OakInit_processOakInit (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				Element.update(ttarget, _req.responseText);
			Behaviour.apply();	
			
			// refering to https://bugzilla.mozilla.org/show_bug.cgi?id=236791
			$('mm_tags').setAttribute("autocomplete","off");
			
			Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		
			} else {
	  			throw new DevError(_req.statusText);
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for class Help
 */
OakInit = new OakInit();

/**
 * Construct a new Help object
 * @class This is the basic Help class 
 * @constructor 
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Help ()
{
	try {
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
Help.prototype.processHelp = Help_processHelp;
Help.prototype.showMediamanager = Help_showMediamanager;
Help.prototype.hideMediamanager = Help_hideMediamanager;
Help.prototype.processMediamanager = Help_processMediamanager;
Help.prototype.setCorrespondingFocus = Help_setCorrespondingFocus;

/**
 * Implements method of prototype class Help
 * This is for usual use of the help class within the normal <form> document flow
 * The param 'level' ist the most important part of this method
 * because it distincts how the DOM Tree will be processed
 *
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class; can be empty/not set (eg. level 1)
 * @throws applyError on exception
 */
function Help_show (elem, level)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'for';
		this.level = level;
		
		this.processId = this.elem.parentNode.parentNode.getAttribute(this.attr);
		
		switch (this.level) {
			case '2' :
					this.formId = this.elem.parentNode.parentNode.parentNode.parentNode.parentNode.getAttribute('id');
					this.elem.className = this.helpClassRemoveLevelTwo;
				break;
			case '3' :
					this.formId = this.elem.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.getAttribute('id');
					this.elem.className = this.helpClassRemoveLevelThree;
				break;
			default :
					this.formId = this.elem.parentNode.parentNode.parentNode.parentNode.getAttribute('id');
					this.elem.className = this.helpClassRemove;
		}
	
		this.ttarget = this.processId;
	
		// Are we within a foreach loop table (eg. with ascending ids)?
		// If true, erase digits, so there is no need to build separat help files on the same topic
		// example: name_3.html -> name.html
		this.fetch = this.processId.replace(/(_(\d+))/, '');	
		if (this.fetch) {
			this.processId = this.fetch;
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
		Behaviour.apply();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help
 * The param 'level' ist the most important part of this method
 * because it distincts how the DOM Tree will be processed
 *
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class; can be empty/non set (= level 1)
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
			case '3' :
					this.elem.className = this.helpClassLevelThree;
				break;
			default :
					this.elem.className = this.helpClass;	
		}
		if (Helper.unsupportsEffects('safari_exception')) {
			Element.hide(this.processIdAfter);
		} else {
			Effect.Fade(this.processIdAfter,{duration: 0.7});
		}
		Element.update(this.elem, this.helpHtmlShow);
		Behaviour.apply();	
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help
 * Get and display the html help files
 *
 * @param {string} target Wich layer div should be used
 * @throws applyError on exception
 * @throws DevError on condition
 */
function Help_processHelp (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				new Insertion.After($(ttarget).parentNode, _req.responseText);				
				var ttarget_after = $(ttarget).parentNode.nextSibling;
				if (Helper.unsupportsEffects('safari_exception')) {
					Element.show(ttarget_after);
				} else {
					Element.hide(ttarget_after);
					Effect.Appear(ttarget_after,{duration: 0.7});
				}
			} else {
	  			throw new DevError(_req.statusText);
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help
 * This method is used for the media manager
 *
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class; can be empty/not set (= level 1)
 * @throws applyError on exception
 * @return {string} gMediamanagerLayer
 */
function Help_showMediamanager (elem)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'id';
		this.formId = this.elem.parentNode.parentNode.parentNode.getAttribute(this.attr);
		this.processId = this.formId;
		this.ttarget = this.helpLyMediamanager;
		this.elem.className = this.helpClassRemoveMediamanager;
		this.url = this.parseHelpUrl + '?page=' + this.formId + '_' + this.processId;
				
		// which layer to process
		var catchMyLocal = Element.getStyle(this.helpLyMediamanagerMyLocal, 'display');		
		if( catchMyLocal == 'block') {
			this.toHide = this.helpLyMediamanagerMyLocal;
		} else {
			this.toHide = this.helpLyMediamanagerMyFlickr;
		}
			
		if (typeof this.req != 'undefined') {
		
			var _url		= this.url;
			var _ttarget	= this.ttarget;
		
			_req.open('GET', _url, true);
			_req.onreadystatechange = function () { Help.processMediamanager(_ttarget);};
			_req.send('');
		}
		if (Helper.unsupportsEffects()) {
			Element.hide(this.toHide);
		} else {
			Effect.Fade(this.toHide,{duration: 0.7});
		}
		Element.update(this.elem, this.helpHtmlHide);
		Behaviour.apply();
		
		// build global var as reference for method hideMediamanager
		gMediamanagerLayer = this.toHide;
		return gMediamanagerLayer;
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help
 * This method is used for the media manager
 *
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class; can be empty/non set (= level 1)
 * @throws applyError on exception
 */
function Help_hideMediamanager (elem)
{
	try {
		// properties
		this.elem = elem;
		this.toHide = this.helpLyMediamanager;
		this.toShow = gMediamanagerLayer;
		this.elem.className = this.helpClassMediamanager;
	
		if (Helper.unsupportsEffects()) {
			Element.hide(this.toHide);
			Element.show(this.toShow);
		} else {
			Element.hide(this.toHide);
			Element.hide(this.toShow);
			Effect.Appear(this.toShow,{duration: 0.7});
		}
		
		Element.update(this.elem, this.helpHtmlShow);
		Behaviour.apply();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help
 * Get and display the html help file for the media manager
 *
 * @param {string} url path
 * @param {string} target Wich layer div should be used
 * @throws applyError on exception
 * @throws DevError on condition
 */
function Help_processMediamanager (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {				
				Element.update (ttarget, _req.responseText);
				if (Helper.unsupportsEffects()) {
					Element.show(ttarget);
				} else {
					Element.hide(this.ttarget);
					Effect.Appear(this.ttarget,{duration: 0.7});
				}
			} else {
	  			throw new DevError(_req.statusText);
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help
 * @param {string} elem actual element
 * @param {string} attr attribute of DOM node to process
 */
 function Help_setCorrespondingFocus (elem, attr)
{
	this.inst = elem.parentNode.parentNode.getAttribute(attr);
	$(this.inst).focus();
}

/**
 * Building new instance for class Help
 */
Help = new Help();


/**
 * Construct a new Navigation object
 * @class This is the basic Navigation class 
 * @constructor
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Navigation ()
{
	try {
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
Navigation.prototype.process = Navigation_process;

/**
 * Implements method of prototype class Navigation
 * @param {string} name The name of the file to catch
 * @param {string} level Wich layer div should be used
 * @throws applyError on exception
 */
function Navigation_show (name, level)
{
	try {
		// properties
		this.name = name;
		this.url = this.parseNavUrl + '?page=' + this.name;
		
		switch (this.level) {
			case '2' :
					this.ttarget = this.navLyTwo;
				break;
			default :
					this.ttarget = this.navLyOne;	
		}
		
		if (typeof this.req != 'undefined') {
		
			var _url		= this.url;
			var _ttarget	= this.ttarget;
		
			_req.open('GET', _url, true);
			_req.onreadystatechange = function () { Navigation.process(_ttarget);};
			_req.send('');
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Navigation
 * @param {string} target Wich layer div should be used
 * @throws applyError on exception
 * @throws DevError on condition
 */
 function Navigation_process (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				Element.hide($('topsubnavconstatic'));
				Element.update(ttarget, _req.responseText);
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
 * Building new instance for @class Navigation
 */
Navigation = new Navigation();


/**
 * Construct a new Forms object
 * @class This is the basic class to process Forms/fields 
 * @constructor
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Forms ()
{
	try {		
	
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
 * DOM triggers by onEvent behaviours
 * @param {string} elem Actual element
 * @param {string} bgcolor Defined background color
 * @param {string} bcolor Defined border color
 * @param {string} bstyle Defined border style attr
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
 * Building new instance for @class Forms
 */
Forms = new Forms();


/**
 * Construct a new Status object
 * @class This is the basic Status class 
 * @constructor
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Status ()
{
	try {
				
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Status.prototype = new Base();

/**
 * Instance Methods from prototype @class Status
 */
Status.prototype.getCbx = Status_getCbx;

/**
 * Implements method of prototype class Status
 * @param {array} elems actual elements
 * @throws applyError on exception
 */
function Status_getCbx (elems)
{
	try {
		// properties		
		this.elems = elems;
		
		if (Status.isArray(this.elems)) {
			for (var e = 0; e < this.elems.length; e++) {
				
				// build new div
				var range = String(this.elems[e])  + '_container';
				
				if ($(range)) {
					if ($(this.elems[e]).checked === true) {
		
						allNodes = document.getElementsByClassName("bez");
					
						for (var i = 0; i < allNodes.length; i++) {
							var _process = allNodes[i].parentNode.parentNode.getAttribute('id');		
							if (_process == range) {
								allNodes[i].style.color = this.applicationTextColor;
							}
						}
						if (Helper.unsupportsEffects('safari_exception')) {
							Element.show($(range));
						} else {
							Element.hide($(range));
							Effect.Appear($(range),{duration: 0.6});
						}
					} else {
						if (Helper.unsupportsEffects('safari_exception')) {
							Element.hide($(range));
						} else {
							Effect.Fade($(range),{duration: 0.6});
						}
					}
				}
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for @class Status
 */
Status = new Status();


/**
 * Construct a new Tables object
 * @class This is the basic Table class 
 * @constructor
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Tables ()
{
	try {
				
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Tables.prototype = new Base();


/**
 * Instance Methods from prototype @class Tables
 */
Tables.prototype.showRow = Tables_showRow;
Tables.prototype.hideRow = Tables_hideRow;
Tables.prototype.collapseRow = Tables_collapseRow;

/**
 * Implements method of prototype class Tables
 * @param {string} elem actual element to process
 * @throws applyError on exception
 */
 function Tables_collapseRow (elem)
{
	try {
		// properties
		this.elem = elem;
		
		// process outer table tr
		$(this.elem).style.visibility = 'collapse';
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Tables
 * @param {string} elem actual element
 * @throws applyError on exception
 */
function Tables_hideRow (elem)
{
	try {
		// properties
		this.elem = elem;
		this.id = this.elem.getAttribute('id');
		this.bid = this.id.split('t_');
		this.obid = String('o_' + this.bid[1]);
		this.ibid = String('i_' + this.bid[1]);
	
		// process inner div
		if (Helper.unsupportsEffects('safari_exception')) {
			Element.hide(this.ibid);
		} else {
			Effect.Fade(this.ibid,{duration: 0.8});
		}
			
		// process outer table tr
		setTimeout("Tables.collapseRow('"+ this.obid +"')", 800);
		
		this.elem.className = this.uploadClass;
		Behaviour.apply();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Tables
 * @param {string} elem actual element
 * @throws applyError on exception
 */
function Tables_showRow (elem)
{	
	try {
		// properties
		this.elem = elem;
		this.id = this.elem.getAttribute('id');
		this.bid = this.id.split('t_');
		this.obid = String('o_' + this.bid[1]);
		this.ibid = String('i_' + this.bid[1]);
		
		// process outer table tr
		$(this.obid).style.visibility = 'visible';
		
		// process inner div
		if (Helper.unsupportsEffects('safari_exception')) {
			Element.show(this.ibid);
		} else {
			Element.hide(this.ibid);
			Effect.Appear(this.ibid,{duration: 0.7});
		}
		
		this.elem.className = this.uploadClassHide;
		Behaviour.apply();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for @class Tables
 */
Tables = new Tables();


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
 * Construct a new DevError object
 * @class This is the basic Error class wich is throwed by explicit setting  
 * @constructor
 * @param {string} msg exception error message presented by catch statement
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
DevError.prototype = new Error();

/**
 * Build new XMLHTTPRequest Instance
 * @private
 * @return _req
 * @throws applyError on exception
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