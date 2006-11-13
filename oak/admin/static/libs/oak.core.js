/**
 * Project: Oak
 * File: oak.core.js
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
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */
 
/** 
 * @fileoverview This is the core Oak javascript file. 
 * 
 * @author Olaf Gleba og@creatics.de
 * @version $Id$ 
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
 * @link See oak.string.js for the strings
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
 * processXXX functions to track errors on xhr state, which
 * are not processed by the try/catch structure.
 *
 * expample:
 * < throw new Errors(object); > 
 *
 * Prototype Methods:
 * 
 * Right now there are not methods defined
 *
 *
 * 
 * @constructor
 * @param {string} name exception error message presented by catch statement
 * @param {string} msg exception error message presented by catch statement
 */
function Errors(msg) 
{
	//this.name = 'Error';
	this.message = msg;
}

/**
 * Building new instance for @class Errors
 */
Errors.prototype = new Error();




/**
 * Constructs the Base class
 * 
 * @class This class is the most important class in the oak
 * javascript enviroment, cause all other classes derived from that class.
 * It predefines properties which are supposed to be used application wide.
 *
 * Prototype Methods:
 * 
 * Right now there are not methods defined
 *
 *
 * @constructor
 * @throws applyError on exception
 * @see Base
 */
function Base ()
{
	try {
		/**
		 * Define application wide help classes
		 */
		this.helpClass = 'iHelp';
		this.helpClassRemove = 'iHelpRemove';
		this.helpClassMediamanager = 'iHelpMediamanager';
		this.helpClassRemoveMediamanager = 'iHelpRemoveMediamanager';
		
		/**
		 * Define help class for mediamanager
		 * Define divs (id) for mediamanager layers
		 * Must corresponding to html notation
		 */
		this.helpLyMediamanager = 'lyMediamanager';
		this.lyMediamanagerMyLocal = 'lyMediamanagerMyLocal';		
		this.lyMediamanagerMyFlickr = 'lyMediamanagerMyFlickr';
		
		/**
		 * Define mediamanager element classes
		 */
		this.mediamanagerClassShow = 'showMediamanagerElement';
		this.mediamanagerClassHide = 'hideMediamanagerElement';
		this.mediamanagerClassShowMyFlickr = 'showMediamanagerElementMyFlickr';
		this.mediamanagerClassHideMyFlickr = 'hideMediamanagerElementMyFlickr';
		
		/**
		 * Define divs (id) for navigation layers
		 * Must corresponding to html notation
		 */
		this.navLyOne = 'ly1';
		this.navLyTwo = 'ly2';
		
		/**
		 * Define used table action (upload) class names.
 		 * Cascading styles to fit background images
		 * Must corresponding to html notation
		 */
		this.uploadClassShow = 'upload showTableRow';
		this.uploadClassHide = 'uploadhide hideTableRow';

		/**
		 * Comprehensive color application wide
		 */
		this.applicationTextColor = '#009a26';
		
		/**
		 * Help strings supposed to delivered within DOM.
		 * Must corresponding to html notation
		 */
		this.helpHtmlShow = '<a href="#" title="' + showHelp + '"><img src="../static/img/icons/help.gif" alt="" /></a>';
		this.helpHtmlHide = '<a href="#" title="' + hideHelp + '"><img src="../static/img/icons/help_off.gif" alt="" /></a>';
		this.elementHtmlShow = '<a href="#" title="' + showElement + '"><img src="../static/img/icons/open.gif" alt="" /></a>';
		this.elementHtmlHide = '<a href="#" title="' + hideElement + '"><img src="../static/img/icons/close.gif" alt="" /></a>';

		/**
		 * Paths for dynamically imported files
		 */
		this.parseHelpUrl = '../parse/parse.help.php';
		this.parseNavUrl = '../parse/parse.navigation.php';
		this.parseMedLocalUrl = '../mediamanager/mediamanager_local.php';
		this.parseMedFlickrUrl = '../mediamanager/mediamanager_flickr.php';
		this.parseMedUploadUrl = '../mediamanager/mediamanager_upload.php';
		this.parseMedEditUrl = '../mediamanager/mediamanager_edit.php';
		this.parseMedDeleteUrl = '../mediamanager/mediamanager_delete.php';
		this.parseMedCastsUrl = '../mediamanager/mediamanager_media_to_podcast.php';
		this.parseMedDiscCastsUrl = '../mediamanager/mediamanager_discard_podcast.php';
		this.parsePagesLinksUrl = '../content/pages_links_select.php';
		this.parseGlobalTemplatesLinksUrl = '../templating/globaltemplates_links_select.php';
		this.parseGlobalFilesLinksUrl = '../templating/globalfiles_links_select.php';
		this.parseBlogCommmentStatusChangeUrl = '../community/blogcomments_statuschange.php';
		
		/**
		 * Reset formerly value
		 * Used in func Mediamanager.initializeTagSearch()
		 */
		this.keyPressDelay = null;
		
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
 * Examine the giving var is an array
 *
 * @requires Base The Oak core javascript class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isArray(elem) {
    return Base.prototype.isObject(elem) && elem.constructor == Array;
}
/**
 * Examine the giving var is true oder false
 *
 * @requires Base The Oak core javascript class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isBoolean(elem) {
    return typeof elem == 'boolean';
}
/**
 * Examine the giving var is a string
 *
 * @requires Base The Oak core javascript class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
 function Base_isString(elem) {
    return typeof elem == 'string';
}
/**
 * Examine the giving var is an object
 *
 * @requires Base The Oak core javascript class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isObject(elem) {
    return (elem && typeof elem == 'object') || Base.prototype.isFunction(elem);
}
/**
 * Examine the giving var is a function
 *
 * @requires Base The Oak core javascript class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isFunction(elem) {
    return typeof elem == 'function';
}
/**
 * Examine the giving var is undefined
 *
 * @requires Base The Oak core javascript class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isUndefined(elem) {
    return typeof elem == 'undefined';
}
/**
 * Examine the giving var is a number
 *
 * @requires Base The Oak core javascript class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isNumber(elem) {
    return typeof elem == 'number' && isFinite(elem);
}
/**
 * Examine the giving var is empty
 *
 * @requires Base The Oak core javascript class
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
 * @requires Base The Oak core javascript class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Base_isNull(elem) {
    return typeof elem == 'object' && !elem;
}
/**
 * Deletes whitespaces in given string
 *
 * @requires Base The Oak core javascript class
 * @param {var} elem Actual element
 * @return elem
 */
function Base_trim(elem) {
  return elem.replace(/^\s*|\s*$/g, "");
}

	


/**
 * Constructs the Init class
 * 
 * @class The Init class is supposed to used on load of page
 *
 * Prototype methods:
 * 
 * load()
 * Init function of the class. All functions supposed to be called
 * on load must take place here.
 *
 * getVars()
 * Getter function for different actions to be executed depending
 * on delivered variables defined in the html markup.
 *
 * getCbxStatus()
 * Show/hide a group of form elements and color their labels.
 * Depending on delivered variable in the html markup.
 *
 * processInit()
 * Update content with XMLHttpRequest response.
 *
 *
 * @see Base
 * @constructor
 * @throws applyError on exception
 */
function Init ()
{
	try {
		// new XMLHttpRequest object
		this.req = _buildXMLHTTPRequest();
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base class */
Init.prototype = new Base();

/* Methods of prototype @class Init */
Init.prototype.load = Init_load;
Init.prototype.getVars = Init_getVars;
Init.prototype.getCbxStatus = Init_getCbxStatus;
Init.prototype.processInit = Init_processInit;

/**
 * Implements method of prototype class Init.
 * All functions supposed to be called on load must take place here.
 * 
 * @param {global} checkbox_status
 * @see Base
 * @throws applyError on exception
 */
function Init_load ()
{	
	try {
		Init.getVars();
		
		if (typeof checkbox_status != 'undefined' && Init.isArray(checkbox_status)) {
			Init.getCbxStatus(checkbox_status);
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Init.
 * Getter function for different actions to be executed depending
 * on delivered variables defined in the html markup.
 * 
 * @param {var} response 
 * @param {var} selection
 * @param {var} mediamanager
 * @param {var} pagetype
 * @requires Base The Oak core javascript class
 * @see Base
 * @throws applyError on exception
 */
function Init_getVars ()
{
	try {
		if (typeof response != 'undefined') {
			if (response == 1) {
				Effect.Fade('rp', {duration: 0.6, delay: 1.2});
			}
		}
		if (typeof podcast != 'undefined' && Init.isNumber(podcast)) {
			if (podcast == 1) {
				Mediamanager.mediaToPodcastOnLoad();
			}
		}
		if ($('podcast_media_object')) {
			if ($('podcast_media_object').value != '') {
				Mediamanager.mediaToPodcastOnLoad();
			}
		}
	   if (typeof mediamanager != 'undefined' && Init.isNumber(mediamanager)) {
			if (mediamanager == 1) {
						
				this.url = this.parseMedLocalUrl + '?page=mediamanager' + '&mm_pagetype=' + pagetype;
				if (typeof this.req != 'undefined') {
		
					var _url		= this.url;
					var _ttarget	= 'column';
		
					_req.open('GET', _url, true);
					_req.onreadystatechange = function () { Init.processInit(_ttarget);};
					_req.send('');
				}
			}	
		}	
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Init.
 * Show/hide a group of form elements and color their labels.
 * Depending on delivered variable in the html markup.
 *
 * @param {array} elem array of element(s)
 * @requires Base The Oak core javascript class
 * @see Base Base is the base class for this
 * @throws applyError on exception
 */
function Init_getCbxStatus (elems)
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
 * Implements method of prototype class Init.
 * Update content with XMLHttpRequest response.
 *
 * @param {string} ttarget Wich layer div should be used
 * @requires Base The Oak core javascript class
 * @throws Errors on req object status code other than 200
 * @throws applyError on exception
 */
function Init_processInit (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				Element.update(ttarget, _req.responseText);
				
				Helper.applyBehaviour();
			
				// refering to https://bugzilla.mozilla.org/show_bug.cgi?id=236791
				$('mm_tags').setAttribute("autocomplete","off");
				$('mm_flickrtags').setAttribute("autocomplete","off");
			
				Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);	
				Event.observe($('mm_flickrtags'), 'keyup', Mediamanager.initializeTagSearchMyFlickr);
			
			} else {
	  			throw new Errors(_req.statusText);
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
 *
 * Prototype methods:
 * 
 * show()
 * 
 *
 * hide()
 * 
 *
 * processHelp()
 * 
 *
 * showMediamanager()
 * 
 *
 * hideMediamanager()
 * 
 *
 * processMediamanager()
 * 
 *
 *
 * setCorrespondingFocus()
 * 
 *
 * @see Base
 * @constructor
 * @throws applyError on exception
 */
function Help ()
{
	try {
		// new XMLHttpRequest object
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
 * Implements method of prototype class Help
 * The param 'level' ist the most important part of this method
 * because it distincts how the DOM Tree will be processed
 *
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class; can be empty/non set (= level 1)
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
 * Implements method of prototype class Help.
 * Update content with XMLHttpRequest response.
 *
 * @param {string} ttarget Wich layer div should be used
 * @requires Base The Oak core javascript class
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
		this.elem.className = this.helpClassRemoveMediamanager;
		this.attr = 'id';
		this.formId = Helper.getAttrParentNode(this.attr, this.elem, 3);
		this.processId = this.formId;
		this.ttarget = this.helpLyMediamanager;
		this.url = this.parseHelpUrl + '?page=' + this.formId + '_' + this.processId;
				
		// which layer to process
		var catchMyLocal = Element.getStyle(this.lyMediamanagerMyLocal, 'display');		
		if( catchMyLocal == 'block') {
			this.toHide = this.lyMediamanagerMyLocal;
		} else {
			this.toHide = this.lyMediamanagerMyFlickr;
		}
			
		if (typeof this.req != 'undefined') {
		
			var _url		= this.url;
			var _ttarget	= this.ttarget;
		
			_req.open('GET', _url, true);
			_req.onreadystatechange = function () { Help.processMediamanager(_ttarget);};
			_req.send('');
		}
	
		Element.hide(this.toHide);
		Element.update(this.elem, this.helpHtmlHide);

		Behaviour.reapply('.' + this.elem.className);
		
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

		Behaviour.reapply('.' + this.elem.className);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help.
 * Update content with XMLHttpRequest response.
 *
 * @param {string} ttarget Wich layer div should be used
 * @requires Base The Oak core javascript class
 * @throws Errors on req object status code other than 200
 * @throws applyError on exception
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
	  			throw new Errors(_req.statusText);
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
	this.inst = Helper.getAttrParentNode(attr, elem, 2);
	$(this.inst).focus();
}

/**
 * Building new object instance of class Help
 */
Help = new Help();







/**
 * Constructs the Navigation class
 * 
 * @class The Navigation class do all the navigation XMLHTTPRequest
 * processing stuff. The scope is application wide.
 *
 * Prototype methods:
 * 
 * show()
 * 
 *
 * processNavigation()
 *
 *
 * @see Base
 * @constructor
 * @throws applyError on exception
 */
function Navigation ()
{
	try {
		// new XMLHttpRequest object
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
Navigation.prototype.processNavigation = Navigation_processNavigation;

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
			_req.onreadystatechange = function () { Navigation.processNavigation(_ttarget);};
			_req.send('');
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Navigation.
 * Update content with XMLHttpRequest response.
 *
 * @param {string} ttarget Wich layer div should be used
 * @requires Base The Oak core javascript class
 * @throws Errors on req object status code other than 200
 * @throws applyError on exception
 */
 function Navigation_processNavigation (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				Element.hide($('topsubnavconstatic'));
				Element.update(ttarget, _req.responseText);
				
				/*
				var range = $('topsubnavdynamic').getElementsByTagName('a');
				
				for (i = 0; i < range.length; i++) {
					range[i].style.color = 'red';
				}
				*/
				new Effect.Highlight(document.getElementsByClassName('highlight')[0], 
					{duration: 1.5, startcolor:'#ff0000', endcolor:'#f9f9f9', restorecolor: '#f9f9f9'});
			} else {
	  			throw new Errors(_req.statusText);
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Navigation
 */
Navigation = new Navigation();








/**
 * Constructs the Forms class
 * 
 * @class The Forms class is supposed be used for forms processing.
 * The scope is application wide.
 *
 * Prototype methods:
 * 
 * setOnEvent()
 * 
 *
 * storeFocus()
 *
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
Forms.prototype.storeFocus = Forms_storeFocus;

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
 * Store focus for later use
 * define as global
 * @param {string} elem Actual element
 * @throws applyError on exception
 */
function Forms_storeFocus (elem)
{
	try {
		storedFocus = elem.id;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Forms
 */
Forms = new Forms();








/**
 * Constructs the Status class
 * 
 * @class The Status class is supposed to be used to handle
 * all change of status events within the interface.
 * The scope is application wide.
 *
 * Prototype methods:
 * 
 * getCbx()
 * Show/hide a group of form elements and color their labels.
 * Depending on delivered variable in the html markup.
 *
 *
 * @see Base
 * @constructor
 * @throws applyError on exception
 */
function Status ()
{
	try {
		// no properties		
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
						Element.hide($(range));
						Effect.Appear($(range),{duration: 0.6});
					} else {
						Effect.Fade($(range),{duration: 0.6});
					}
				}
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Status
 */
Status = new Status();







/**
 * Constructs the Tables class
 * 
 * @class The Tables class faces all action that are related 
 * to html tables, because the mostly needs extra treatment.
 * The scope is application wide.
 *
 * Prototype methods:
 * 
 * showTableRow()
 *  
 *
 * hideTableRow()
 *
 *
 * collapseTableRow()
 *
 *
 * @see Base
 * @constructor
 * @throws applyError on exception
 */
function Tables ()
{
	try {
		// no properties		
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Tables.prototype = new Base();


/**
 * Instance Methods from prototype @class Tables
 */
Tables.prototype.showTableRow = Tables_showTableRow;
Tables.prototype.hideTableRow = Tables_hideTableRow;
Tables.prototype.collapseTableRow = Tables_collapseTableRow;

/**
 * Implements method of prototype class Tables
 * @param {string} elem actual element to process
 * @throws applyError on exception
 */
 function Tables_collapseTableRow (elem)
{
	try {
		// properties
		this.elem = elem;
		
		// process inner div
		if (Helper.unsupportsEffects('safari_exception')) {
			return false;
		} else {
			// process outer table tr
			$(this.elem).style.visibility = 'collapse';
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Tables
 * @param {string} elem actual element
 * @throws applyError on exception
 */
function Tables_showTableRow (elem)
{	
	try {
		// properties
		this.elem = elem;
		this.id = this.elem.getAttribute('id');
		this.bid = this.id.split('e_');
		this.obid = String('o_' + this.bid[1]);
		this.ibid = String('i_' + this.bid[1]);
		
		// process outer table tr
		$(this.obid).style.visibility = 'visible';
		
		// process inner div
		Element.hide(this.ibid);
		Effect.Appear(this.ibid,{duration: 0.7});

		// coloring top row
		this.elem.parentNode.parentNode.style.color = this.applicationTextColor;
				
		this.elem.className = this.uploadClassHide;
		
		//Behaviour.apply();
		Behaviour.reapply('.hideTableRow');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Tables
 * @param {string} elem actual element
 * @throws applyError on exception
 */
function Tables_hideTableRow (elem)
{
	try {
		// properties
		this.elem = elem;
		this.id = this.elem.getAttribute('id');
		this.bid = this.id.split('e_');
		this.obid = String('o_' + this.bid[1]);
		this.ibid = String('i_' + this.bid[1]);
		
		// process inner div
		Effect.Fade(this.ibid,{duration: 0.8});
			
		// process outer table tr
		setTimeout("Tables.collapseTableRow('"+ this.obid +"')", 800);
		
		// coloring top row
		this.elem.parentNode.parentNode.style.color = '';

		this.elem.className = this.uploadClassShow;
		
		//Behaviour.apply();
		Behaviour.reapply('.showTableRow');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Tables
 */
Tables = new Tables();