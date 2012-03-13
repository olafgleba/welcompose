/**
 * Project: Welcompose
 * File: wcom.core.js
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
	_applyError(e, additionalStr);
}</code></pre>
 * 
 * @param {object} exception error obj presented by catch statement
 */
function _applyError (exception, additionalStr)
{
	var errStr;
	
	switch (debug) {
		case 0 :
			return false;
		break;
		case 1 :
			if (additionalStr) {
				errStr = additionalStr;
			} else {
				errStr = exception + '\r\n' + exception.fileName + '\r\n' + exception.lineNumber;
			}
		break;
		case 2 :
			if (additionalStr) {
				errStr = additionalStr;
			} else {
				errStr = e_msg_str_prefix + '\r\n\r\n' + exception + '\r\n' + exception.fileName + '\r\n' + exception.lineNumber + '\r\n\r\n' + e_msg_str_suffix;
			}
		break;
		default :
			errStr = exception;
		break;
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
		 * Help class
		 */
		this.helpClassMediamanager = 'iHelpMediamanager';

		/**
		 * Help class
		 */
		this.helpClassRemoveMediamanager = 'iHelpRemoveMediamanager';
		
		/**
		 * Core class
		 */
		this.coreShowElemClass = 'showElem';

		/**
		 * Core class
		 */
		this.coreHideElemClass = 'hideElem';

		/**
		 * Help div for mediamanager
		 */
		this.helpLyMediamanager = 'lyMediamanager';

		/**
		 * MyLocal div (mediamanager)
		 */
		this.lyMediamanagerMyLocal = 'lyMediamanagerMyLocal';		

		/**
		 * MyFlickr div (mediamanager)
		 */
		this.lyMediamanagerMyFlickr = 'lyMediamanagerMyFlickr';
		
		/**
		 * Element class for myLocal (mediamanager)
		 */
		this.mediamanagerClassShowMyLocal = 'showMediamanagerElementMyLocal';

		/**
		 * Element class for myLocal (mediamanager)
		 */
		this.mediamanagerClassHideMyLocal = 'hideMediamanagerElementMyLocal';

		/**
		 * Element class for myFlickr (mediamanager)
		 */
		this.mediamanagerClassShowMyFlickr = 'showMediamanagerElementMyFlickr';

		/**
		 * Element class for myFlickr (mediamanager)
		 */
		this.mediamanagerClassHideMyFlickr = 'hideMediamanagerElementMyFlickr';
		
		/**
		 * Element class to show grouped form elements
		 */
		this.elementShowFormElements = 'showFormElements';

		/**
		 * Element class to hide grouped form elements
		 */
		this.elementHideFormElements = 'hideFormElements';
		
		/**
		 * First level div for dynamic xhr navigation
		 */
		this.navLyOne = 'ly1';

		/**
		 * Second level div for dynamic xhr navigation
		 */
		this.navLyTwo = 'ly2';
		
		/**
		 * Element class for upload button used in tables
		 */
		this.uploadClassShow = 'upload showTableRow';

		/**
		 * Element class for upload button used in tables
		 */
		this.uploadClassHide = 'uploadhide hideTableRow';

		/**
		 * Comprehensive color application wide
		 */
		this.applicationTextColor = '#ff620d';
		
		/**
		 * Comprehensive popup width application wide
		 */
		this.callbacksPopupWindowWidth745 = '745';
		
		/**
		 * Comprehensive width application wide
		 */
		this.callbacksPopupWindowWidth460 = '460';
		
		/**
		 * Comprehensive popup height application wide
		 */
		this.callbacksPopupWindowHeight664 = '666';
		
		/**
		 * Help string supposed to delivered within the DOM.
		 */
		this.helpHtmlShow = '<a href="#" title="' + showHelp + '"><img src="../static/img/icons/help.gif" alt="" /></a>';

		/**
		 * Help string supposed to delivered within the DOM.
		 */
		this.helpHtmlHide = '<a href="#" title="' + hideHelp + '"><img src="../static/img/icons/help_off.gif" alt="" /></a>';

		/**
		 * Element string supposed to delivered within the DOM (mediamanager).
		 */
		this.elementHtmlShow = '<a href="#" title="' + showElement + '"><img src="../static/img/icons/open.gif" alt="" /></a>';

		/**
		 * Element string supposed to delivered within the DOM (mediamanager).
		 */
		this.elementHtmlHide = '<a href="#" title="' + hideElement + '"><img src="../static/img/icons/close.gif" alt="" /></a>';

		/**
		 * Reset formerly value
		 * Used in {@link Mediamanager#initializeTagSearch}
		 */
		this.keyPressDelay = null;
		
		/**
		 * Paths for files to import
		 */
		this.parseHelpPath = '../parse/parse.help.php';
		this.parseNavPath = '../parse/parse.navigation.php';
		this.parseMedLocalPath = '../mediamanager/mediamanager_local.php';
		this.parseMedFlickrPath = '../mediamanager/mediamanager_flickr.php';
		this.parseMedUploadPath = '../mediamanager/mediamanager_upload.php';
		this.parseMedEditPath = '../mediamanager/mediamanager_edit.php';
		this.parseMedDeletePath = '../mediamanager/mediamanager_delete.php';
		this.parseMedCastsPath = '../mediamanager/mediamanager_media_to_podcast.php';
		this.parseMedDiscCastsPath = '../mediamanager/mediamanager_discard_podcast.php';
		this.parseMedCallbacksFilePath = '../mediamanager/callbacks_insert_';		
		this.parseCallbacksFilePath = 'callbacks_insert_';
		this.parseCallbacksPath = '../callbacks.php';
		this.parseBlogCommmentStatusChangePath = '../community/blogcomments_statuschange.php';
		this.validatePath = '../validate.js.php';
		this.previewPath = '../../index.php';
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
 * @param {var} elem Current element
 * @return Boolean true or false
 */
function Base_isArray(elem) {
    return Base.prototype.isObject(elem);
}
/**
 * Examine the giving var is of type Bool
 *
 * @param {var} elem Current element
 * @return Boolean true or false
 */
function Base_isBoolean(elem) {
    return typeof elem == 'boolean';
}
/**
 * Examine the giving var is of type String
 *
 * @param {var} elem Current element
 * @return Boolean true or false
 */
 function Base_isString(elem) {
    return typeof elem == 'string';
}
/**
 * Examine the giving var is of type Object
 *
 * @param {var} elem Current element
 * @return Boolean true or false
 */
function Base_isObject(elem) {
    return (elem && typeof elem == 'object') || Base.prototype.isFunction(elem);
}
/**
 * Examine the giving var is a function
 *
 * @param {var} elem Current element
 * @return Boolean true or false
 */
function Base_isFunction(elem) {
    return typeof elem == 'function';
}
/**
 * Examine the giving var is undefined
 *
 * @param {var} elem Current element
 * @return Boolean true or false
 */
function Base_isUndefined(elem) {
    return typeof elem == 'undefined';
}
/**
 * Examine the giving var has no values
 *
 * @param {var} elem Current element
 * @return Boolean true or false
 */
function Base_isNumber(elem) {
    return typeof elem == 'number' && isFinite(elem);
}
/**
 * Examine the giving var is empty
 *
 * @param {var} elem Current element
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
 * @param {var} elem Current element
 * @return Boolean true or false
 */
function Base_isNull(elem) {
    return typeof elem == 'object' && !elem;
}
/**
 * Delete whitspaces at begin and end of the delivered string
 *
 * @param {var} elem Current element
 * @return elem
 * @type String
 */
function Base_trim(elem) {
  return elem.replace(/^\s*|\s*$/g, '');
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
		// instance XMLHttpRequest object
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
Init.prototype.getRelatedPages = Init_getRelatedPages;
Init.prototype.showResponseGetRelatedPages = Init_showResponseGetRelatedPages;
Init.prototype.getNavigationPages = Init_getNavigationPages;
Init.prototype.showResponseGetNavigationPages = Init_showResponseGetNavigationPages;
Init.prototype.getCbxStatus = Init_getCbxStatus;
Init.prototype.processInit = Init_processInit;
Init.prototype.setCookie = Init_setCookie;
Init.prototype.getCookie = Init_getCookie;
Init.prototype.getToogleElem = Init_getToogleElem;
Init.prototype.getToogleElemSingle = Init_getToogleElemSingle;
Init.prototype.toggleViewByChbx = Init_toggleViewByChbx;



/**
 * All functions supposed to be called on load must take place within this function.
 * 
 * 
 * @throws applyError on exception
 */
function Init_load ()
{	
	try {		
		// DONT EVER CHANGE THIS FUNCTION CALL
		// set global debug var first
		_debug();
		
		// call init enviroment directly afterwards
		Init.getVars();

	} catch (e) {
		_applyError(e);
	}
}

/**
 * Getter function for several actions to be executed on load of page.
 * Depends on delivered variables in the html markup.
 * <br /><br />
 * Current conditions in order first to last:<br />
 * <em>var response</em>  Show response layer after save succeeded.<br />
 * <em>var podcast</em>  Triggers {@link Mediamanager#mediaToPodcastOnLoad}.<br />
 * <em>object podcast_media_object</em>  Triggers {@link Mediamanager#mediaToPodcastOnLoad}.<br />
 * <em>var mediamanager</em>  Triggers XMLHttpRequest for Media Manager Layer integration.
 * 
 * @throws applyError on exception
 */
function Init_getVars ()
{
	try {
		if ($$('.page a').first()) {
			// Tooltip
			$$(".page a").each( function(link) {
				new Tooltip(link, {
					mouseFollow: false, 
					opacity: 1, 
					backgroundColor: '#333',
					textColor: '#fff', 
					appearDuration: '0.15'
				});
			});
		}	
		if ($$('.botbg').first() && !document.getElementById('abbreviations')) {
			Form.focusFirstElement($$('.botbg').first());
		}		
		if (typeof response != 'undefined') {
			if (response == 1) {
				if($('rp')) Effect.Fade('rp', {duration: 0.4, delay: 1.5});
			}
		}
		if (typeof preview_ctrl != 'undefined') {
			if (preview_ctrl == 1) {
				Preview.reloadFrontendView();
			}
		}
		if (typeof callback_media_result !== 'undefined' && callback_media_result !== '') {
				Mediamanager.insertMediaCallbacks(callback_media_result);
		}
		if (typeof podcast != 'undefined' && Init.isNumber(podcast)) {
			if (podcast == 1) {
				Mediamanager.mediaToPodcastOnLoad();
			}
		}
		if ($('podcast_media_object')) {
			if ($('podcast_media_object').value !== '') {
				Mediamanager.mediaToPodcastOnLoad();
			}
		}
		if (typeof toggleElem != 'undefined') {
			if (toggleElem == 1) {
				Init.getToogleElem();
			}
			else if (toggleElem == 2) 
				Init.getToogleElemSingle();
		}
		if (typeof checkbox_status != 'undefined' && Init.isArray(checkbox_status)) {
			if (typeof disable != 'undefined' && disable == 1) {
				Init.getCbxStatus(checkbox_status, disable);
			} else {
				Init.getCbxStatus(checkbox_status);
			}
		}
		if ($('page_navigations')) {		
				Init.getNavigationPages();
		}
		if (typeof mediamanager != 'undefined' && Init.isNumber(mediamanager)) {
			if (mediamanager == 1) {
				
				// switch if page contains urlify form field
				if (pagetype !== '') {
					$('column').style.paddingTop = '142px';
				}
				// enhance padding on page type event
				if (pagetype == 'WCOM_EVENT') {
					$('column').style.paddingTop = '229px';
				}
						
				this.url = this.parseMedLocalPath + '?page=mediamanager' + '&mm_pagetype=' + pagetype;
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
 * Lazy load of related pages to apply on page creation.
 * <br />
 * Get pages which have content table fields. This
 * is used whenever the user chooses to prefill the
 * created page with appropriate content of already applied pages 
 * 
 * @see #showResponseGetRelatedPages
 * @throws applyError on exception
 */
function Init_getRelatedPages()
{
	try {	
		var url = '../content/pages_apply_content.php';
		
		var elem = $('page_type');
		var pars = 'type=' + elem.options[elem.selectedIndex].value;
		
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onComplete : Init.showResponseGetRelatedPages
			});
			
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Get related pages to apply on page creation xhr response.
 * <br />
 * Populate select form element with provided pages on load 
 * 
 * @see #getRelatedPages
 * @param {object} req XHR response
 * @throws applyError on exception
 */
function Init_showResponseGetRelatedPages(req)
{
	try {
		Element.update($('page_apply_content_selection'), req.responseText);
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Lazy load of related pages to apply on page creation.
 * <br />
 * Get pages which have content table fields. This
 * is used whenever the user chooses to prefill the
 * created page with appropriate content of already applied pages 
 * 
 * @see #showResponseGetNavigationPages
 * @throws applyError on exception
 */
function Init_getNavigationPages()
{
	try {			
		var url = '../content/pages_navigation_pages.php';		
		var elem = $('page_navigations');
		var elemVal = elem.options[elem.selectedIndex].value;
		var pars = 'navigation=' + elemVal;
		
		// assume hidden navigation field
		$('navigation').value = elemVal;
				
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onComplete : Init.showResponseGetNavigationPages
			});
			
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Get related pages to apply on page creation xhr response.
 * <br />
 * Populate select form element with provided pages on load 
 * 
 * @see #getRelatedPages
 * @param {object} req XHR response
 * @throws applyError on exception
 */
function Init_showResponseGetNavigationPages(req)
{
	try {		
		if (req.responseText != '') {			
			// init element
			var elem = $('page_pages');
			
			// update the select form element
			Element.update(elem, req.responseText);
		
			// assume hidden reference field
			var ref = elem.options[elem.selectedIndex].value;		
			$('reference').value = ref;
		}
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Show/hide a group of form elements and color their labels on load of page.
 * Depends on delivered variables in the html markup.
 * <br />
 * For in depths explanation please have a look at the online
 * project support area.
 *
 * @param {array} elems Array of element(s)
 * @throws applyError on exception
 */
function Init_getCbxStatus (elems, reverse)
{
	try {
		for (var e = 0; e < elems.length; e++) {
			
			var range = String(elems[e])  + '_container';
			
			if ($(range)) {
				if ($(elems[e]).checked === true) {
				
					allNodes = $$(".bez");	
				
					for (var i = 0; i < allNodes.length; i++) {
						var _process = allNodes[i].parentNode.parentNode.getAttribute('id');		
						if (_process == range) {
							allNodes[i].style.color = this.applicationTextColor;
						}
					}
					if (!reverse) {
						Element.show(range);
					} else {
						Element.hide(range);
					}
				} else {
					if (!reverse) {
						Element.hide(range);
					} else {
						Element.show(range);
					}
				}
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Update content with XMLHttpRequest response.
 * <br />
 * Beside of that the Media Manager Tag Search Events are initialized.
 *
 * @param {string} ttarget Layer to process
 * @throws Errors on req object status code other than 200
 * @throws applyError on exception
 */
function Init_processInit (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				Element.update(ttarget, _req.responseText);
		
				Mediamanager.applyBehaviour();
			
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
 * Sets a cookie
 * <br />
 *
 * @param {string} name 
 * @param {string} value 
 * @param {string} path 
 * @param {string} domain 
 * @param {boolean} secure 
 * @return cookie
 * @throws applyError on exception
 */
function Init_setCookie (name, value, path, domain, secure)
{
	try {
	    // one year
	    var expireDate = new Date ();
	    expireDate.setTime(expireDate.getTime() + (365 * 24 * 60 * 60 * 1000));
    
	    var currentCookie = name + "=" + escape(value) +
	        ((expireDate) ? "; expires=" + expireDate.toGMTString() : "") +
	        ((path) ? "; path =" + path : "") +
	        ((domain) ? "; domain=" + domain : "") +
	        ((secure) ? "; secure" : "");
	            document.cookie = currentCookie;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Gets a cookie
 * <br />
 *
 * @param {string} name
 * @return cookie
 * @throws applyError on exception
 */
function Init_getCookie (name)
{
	try {
	    var dc = document.cookie;
	    var prefix = name + "=";
	    var begin = dc.indexOf("; " + prefix);
	    if (begin == -1) {
	        begin = dc.indexOf(prefix);
	        if (begin !== 0) return null;
	    }
	    else begin += 2;
	    var end = document.cookie.indexOf(";", begin);
	    if (end == -1) end = dc.length;
    
	    return unescape(dc.substring(begin + prefix.length, end));
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Toogle display of the navigation table depending on current element pointer.
 * 
 * @throws applyError on exception
 */
function Init_getToogleElem ()
{
	try {
		if (navigator.cookieEnabled == true && document.cookie) {
			
			var tables = document.getElementsByTagName('table');
			
			for (var e = 0; e < tables.length; e++) {
				
				if (Init.getCookie(tables[e].id)) {
					tables[e].style.display = Init.getCookie(tables[e].id);
				}
				
				if (Init.getCookie(tables[e].id) == 'block' || Init.getCookie(tables[e].id) === null) {					
					if (Helper.isBrowser('ie')) {
						tables[e].previousSibling.childNodes[0].lastChild.innerHTML = '<img title="' + hideElement + '" src="../static/img/icons/close.gif" alt="" />';
					} else {
						tables[e].previousSibling.previousSibling.childNodes[1].lastChild.innerHTML = '<img title="' + hideElement + '" src="../static/img/icons/close.gif" alt="" />';
					}
				} else {
					if (Helper.isBrowser('ie')) {
						tables[e].previousSibling.childNodes[0].lastChild.innerHTML = '<img title="' + showElement + '" src="../static/img/icons/open.gif" alt="" />';
					} else {
						tables[e].previousSibling.previousSibling.childNodes[1].lastChild.innerHTML = '<img title="' + showElement + '" src="../static/img/icons/open.gif" alt="" />';
					}
				}
			}
		}		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Toogle display of the one single table depending on current element pointer.
 * 
 * @throws applyError on exception
 */
function Init_getToogleElemSingle ()
{
	try {
		if (navigator.cookieEnabled == true && document.cookie) {
			
			var table = $('allboxes');
				
			if (Init.getCookie(table.id)) {
				table.style.display = Init.getCookie(table.id);
			}
				
			if (Init.getCookie(table.id) == 'block') {					
				if (Helper.isBrowser('ie')) {
					table.previousSibling.childNodes[0].lastChild.innerHTML = '<img title="' + hideElement + '" src="../static/img/icons/close.gif" alt="" />';
				} else {
					table.previousSibling.previousSibling.childNodes[1].lastChild.innerHTML = '<img title="' + hideElement + '" src="../static/img/icons/close.gif" alt="" />';
				}
			} else {
				if (Helper.isBrowser('ie')) {
					table.previousSibling.childNodes[0].lastChild.innerHTML = '<img title="' + showElement + '" src="../static/img/icons/open.gif" alt="" />';
				} else {
					table.previousSibling.previousSibling.childNodes[1].lastChild.innerHTML = '<img title="' + showElement + '" src="../static/img/icons/open.gif" alt="" />';
				}
			}
		}		
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Simply show/hide elements by provided id
 * <br />
 *
 * @param {string} elem
 * @param {string} target id
 *
 * @throws applyError on exception
 */
function Init_toggleViewByChbx (elem, target)
{
	try {
	    // properties
		this.elem = elem;
		//this.target = document.getElementsByClassName(target)[0];
		this.target = $$('.' + target).first();
		
		if ($(this.elem).checked === true) {
			Effect.Appear(this.target,{delay: 0, duration: 0.5});
		} else {
			Effect.Fade(this.target,{delay: 0, duration: 0.4});
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
Help.prototype.showMediamanager = Help_showMediamanager;
Help.prototype.hideMediamanager = Help_hideMediamanager;
Help.prototype.processMediamanager = Help_processMediamanager;
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
		this.elem.className = this.helpClass;
		this.processId = Helper.getAttrParentNode(this.attr, this.elem, 2);
		var obj = $(this.processId).parentNode.nextSibling;
	
		Effect.Fade(obj,{delay: 0, duration: 0.3, 
			afterFinish: function() {
				obj.remove();
			}
		});

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
 * Show Mediamanager help html element.
 * Import related help file depending on current element pointer.
 * We have to return the hide state as a global var to provide
 * which layer we must show on method {@link #hideMediamanger} 
 *
 * @see #processMediamanager
 * @param {string} elem Current element
 * @throws applyError on exception
 * @returns {global} gMediamanagerLayer Layer needed for {@link #hideMediamanager}
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
		this.url = this.parseHelpPath + '?page=' + this.formId + '_' + this.processId;
				
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
		
		// global var as pointer for method hideMediamanager
		gMediamanagerLayer = this.toHide;
		return gMediamanagerLayer;
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Hide Mediamanager help html element.
 *
 * @param {string} elem Current element
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
	
		Element.hide(this.toHide);
		Element.hide(this.toShow);
		Effect.Appear(this.toShow,{duration: 0.7});
		
		Element.update(this.elem, this.helpHtmlShow);

		Behaviour.reapply('.' + this.elem.className);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Update content with XMLHttpRequest response.
 *
 * @see #showMediamanager
 * @param {string} ttarget Layer to process
 * @throws Errors on req object status code other than 200
 * @throws applyError on exception
 */
function Help_processMediamanager (ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {				
				Element.update (ttarget, _req.responseText);
				Element.hide(this.ttarget);
				Effect.Appear(this.ttarget,{duration: 0.7});
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
 * Constructs the Core class
 * 
 * @class The Core class is the appropriate class for
 * doing basic things like show/hide html elements.
 * The scope is application wide.
 *
 * @constructor
 * @throws applyError on exception
 */
function Core ()
{
	try {
		// construct empty
	} catch (e) {
		_applyError(e);
	}
}


/* Inherit from Base */
Core.prototype = new Base();

/**
 * Instance Methods from prototype @class Core
 */
Core.prototype.showElem = Core_showElem;
Core.prototype.hideElem = Core_hideElem;

/**
 * Show misc. html elements.
 *
 * @param {string} elem Current element
 * @param {string} depth what parent to identify
 * @throws applyError on exception
 */
function Core_showElem (elem, depth)
{
	try {
		// properties
		this.elem = elem;
		this.depth = depth;
		this.elem.className = this.coreHideElemClass;	
		this.processId = Helper.getAttrParentNodeNextNode('id', this.elem, depth);
		
		Effect.Appear($(this.processId),{delay: 0, duration: 0.5});	
		
		Element.update(this.elem, this.elementHtmlHide);
		Behaviour.reapply('.' + this.elem.className);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Hide misc. html elements.
 *
 * @param {string} elem Current element
 * @param {string} depth what parent to identify
 * @throws applyError on exception
 */
function Core_hideElem (elem, depth)
{
	try {
		// properties
		this.elem = elem;
		this.depth = depth;
		this.elem.className = this.coreShowElemClass;
		this.processId = Helper.getAttrParentNodeNextNode('id', this.elem, depth);
		
		Effect.Fade($(this.processId),{delay: 0, duration: 0.4});	

		Element.update(this.elem, this.elementHtmlShow);
		Behaviour.reapply('.' + this.elem.className);
	
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Core
 */
Core = new Core();




/**
 * Constructs the Navigation class
 * 
 * @class The Navigation class do all the navigation XMLHTTPRequest
 * processing stuff. The scope is application wide.
 * 
 * @constructor
 * @throws applyError on exception
 */
function Navigation ()
{
	try {
		// instance XMLHttpRequest object
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
 * Import related navigation file depending on current element pointer.
 * Here var <em>level</em> distinguish which markup layer to use.
 * 
 * @see #processNavigation
 * @param {string} name The name of page
 * @param {string} level Layer to process
 * @throws applyError on exception
 */
function Navigation_show (name, level)
{
	try {
		// properties
		this.name = name;
		this.url = this.parseNavPath + '?page=' + this.name;
		
		switch (this.level) {
			case '2' :
					this.ttarget = this.navLyTwo;
				break;
			default :
					this.ttarget = this.navLyOne;
				break;
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
 * Update content with XMLHttpRequest response.
 *
 * @see #show
 * @param {string} ttarget Layer to process
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
	
				new Effect.Fade($$('.highlight').first(),
					{duration: 0.5, delay: 0.4});

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
Forms.prototype.storeFocus = Forms_storeFocus;
Forms.prototype.showFormElements = Forms_showFormElements;
Forms.prototype.hideFormElements = Forms_hideFormElements;

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
 * Track the current content form field focus and makes it available application wide.
 * This is used to fire alerts within several Media Manager <em>_insertXXX</em> functions.
 *
 * @param {string} elem Current element
 * @throws applyError on exception
 */
function Forms_storeFocus (elem)
{
	try {
		stored_focus = elem.id;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display customized groups of form elements
 * Depends on HTML Markup.
 *<br />
 * For in depths explanation please have a look at the online
 * project support area.
 *
 * @param {string} elem Current element
 * @throws applyError on exception
 */
function Forms_showFormElements (elem)
{
	try {
		// properties
		this.elem = elem;
		this.elem.className = this.elementHideFormElements;
		
		Effect.Appear(this.elem.id + '_container',{delay: 0, duration: 0.5});
		Element.update(this.elem, this.elementHtmlHide);
		Behaviour.reapply('.' + this.elem.className);
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Hide customized groups of form elements
 * Depends on HTML Markup.
 *<br />
 * For in depths explanation please have a look at the online
 * project support area.
 *
 * @param {string} elem Current element
 * @throws applyError on exception
 */
function Forms_hideFormElements (elem)
{
	try {
		// properties
		this.elem = elem;
		this.elem.className = this.elementShowFormElements;
		
		Effect.Fade(this.elem.id + '_container',{delay: 0, duration: 0.4});
		Element.update(this.elem, this.elementHtmlShow);
		Behaviour.reapply('.' + this.elem.className);
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
 * @class The Status class is supposed to handle
 * all change of status events within the html interface.
 * The scope is application wide.
 *
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
 * Show/hide a group of form elements and color their labels.
 * Depends on delivered variable in the html markup.
 * <br />
 * For in depths explanation please have a look at the online
 * project support area.
 *
 * @param {array} elems Array of element(s)
 * @param {string} disable Switch to disable/enable bound form elements
 * @throws applyError on exception
 */
function Status_getCbx (elems, disable)
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
		
						allNodes = $$(".bez");
					
						for (var i = 0; i < allNodes.length; i++) {
							var _process = allNodes[i].parentNode.parentNode.getAttribute('id');		
							if (_process == range) {
								if(!disable) {
									allNodes[i].style.color = this.applicationTextColor;
								}
							}
						}
						if (disable) {
							obj = $$('#'+range+' input, select');
							$(obj).each(function(element) {
								element.style.backgroundColor = '#f4f4f4';
								element.style.borderColor = '#cccccc';
								element.disabled = true;
							});
						} else {
							$(range).hide();
							Effect.Appear($(range),{duration: 0.6});
						}
					} else {
						if (disable) {
							$(obj).each(function(element) {
								element.style.backgroundColor = '';
								element.style.borderColor = '';
								element.disabled = false;
							});							
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
 * Building new object instance of class Status
 */
Status = new Status();



/**
 * Constructs the Tables class
 * 
 * @class The Tables class faces all action that are related 
 * to html tables, because they mostly need extra treatment.
 * The scope is application wide.
 *
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
Tables.prototype.toggleElem = Tables_toggleElem;

/**
 * Show formely hidden table row depending on current element pointer.
 * 
 * @param {string} elem Current element
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
		$(this.obid).style.display = 'table-row';
		
		// process inner div
		Element.hide(this.ibid);
		Effect.Appear(this.ibid,{duration: 0.7});

		// coloring top row
		this.elem.parentNode.parentNode.style.color = this.applicationTextColor;
				
		this.elem.className = this.uploadClassHide;

		Behaviour.reapply('.hideTableRow');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Hide visible table row depending on current element pointer.
 * 
 * @param {string} elem Current element
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
		Effect.Fade(this.ibid,{duration: 0.6});
			
		// process outer table tr
		setTimeout("Tables.collapseTableRow('"+ this.obid +"')", 500);
		
		// coloring top row
		this.elem.parentNode.parentNode.style.color = '';

		this.elem.className = this.uploadClassShow;

		Behaviour.reapply('.showTableRow');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Gives some browsers some extra treatment.
 *
 * @param {string} elem Current element to process
 * @throws applyError on exception
 */
 function Tables_collapseTableRow (elem)
{
	try {
		// properties
		this.elem = elem;
		
		// process inner div
		if (Helper.isBrowser('ie')) {
			return false;
		} else {
			// process outer table tr
			$(this.elem).style.visibility = 'collapse';
			$(this.elem).style.display = 'none';
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Toogle display of a table depending on current element pointer.
 * 
 * @param {string} elem Current element
 * @throws applyError on exception
 */
function Tables_toggleElem (elem)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'id';
		this.processRow = Helper.getAttrParentNodeNextNode(this.attr, this.elem, 2);
		this.statusDisplay = Element.getStyle(this.processRow, 'display');
		
		// we need this for IE. He's to dumb to parse display value 'table'.
		if (this.statusDisplay == 'table' || this.statusDisplay === null)
			this.statusDisplay = 'block';
		
		if (this.statusDisplay == 'block') {
			if (navigator.cookieEnabled == true) {
				Init.setCookie(this.processRow, 'none', '/');
			}
			this.elem.innerHTML = '<img title="' + showElement + '" src="../static/img/icons/open.gif" alt="" />';
			Effect.Fade(this.processRow,{duration: 0.4});
		} else {
			if (navigator.cookieEnabled == true) {
				Init.setCookie(this.processRow, 'block', '/');
			}
			this.elem.innerHTML = '<img title="' + hideElement + '" src="../static/img/icons/close.gif" alt="" />';
			Effect.Appear(this.processRow,{duration: 0.4});
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Tables
 */
Tables = new Tables();



/**
 * Constructs the Preview class
 * 
 * @class The Preview Class provides a call to the rendered frontend page
 * The scope is application wide.
 *
 * @constructor
 * @throws applyError on exception
 */
function Preview ()
{
	try {
		// no properties		
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Preview.prototype = new Base();

/**
 * Instance Methods from prototype @class Preview
 */
Preview.prototype.getFrontendView = Preview_getFrontendView;
Preview.prototype.reloadFrontendView = Preview_reloadFrontendView;
Preview.prototype.closeFrontendView = Preview_closeFrontendView;

/**
 * Get rendered page frontend View.
 * 
 * @param {string} elem Current element
 * @throws applyError on exception
 */
function Preview_getFrontendView (elem)
{
	try {
		// properties
		this.elem = elem;
		this.elemPageId = this.elem.id;
		this.elemPostingId = this.elem.name;
		this.elemAction = this.elem.rel;
		
		// Save state in hidden field previewCtrl
		document.getElementsByName('preview')[0].value = 1;
		
		previewWin = window.open(this.previewPath + '?page='+this.elemPageId+'&posting_id='+this.elemPostingId+'&action='+this.elemAction+'','preview','width=1024,height=640,scrollbars=yes,resizable=yes');
		
		previewWin.focus();
		
		var insertPanel = function () {
			Element.insert(previewWin.document.body, {'top': '<div style="width:100%; height:52px; background-color: #EF5C19; border-bottom:2px solid #ccc;"><p style="float:right; margin:0px; padding:0px 15px 0px 0px; line-height: normal;"><a style="display:block; padding:18px; color:#fff; text-decoration:none;" href="javascript:self.opener.Preview.closeFrontendView();"><strong>'+closePreviewPopup+'</strong></a></div'});
		}
		
		// we have to set a big timeout cause the page (e.g. DOM) has
		// to finish loading before we are able to use this function
		setTimeout(insertPanel, 5000);
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Get rendered page frontend View when popup is still open.
 * 
 * @param {string} elem Current element
 * @throws applyError on exception
 */
function Preview_reloadFrontendView ()
{
	try {
		// properties
		this.elem = $$('.preview').first();
		this.elemPageId = Helper.getAttr('id', this.elem);
		this.elemPostingId = Helper.getAttr('name', this.elem);
		this.elemAction = Helper.getAttr('rel', this.elem);
		
		previewWin = window.open(this.previewPath + '?page='+this.elemPageId+'&posting_id='+this.elemPostingId+'&action='+this.elemAction+'','preview','scrollbars=yes,resizable=yes');
		
		var insertPanel = function () {
			Element.insert(previewWin.document.body, {'top': '<div style="width:100%; height:52px; background-color: #EF5C19; border-bottom:2px solid #ccc;"><p style="float:right; margin:0px; padding:0px 15px 0px 0px; line-height: normal;"><a style="display:block; padding:18px; color:#fff; text-decoration:none;" href="javascript:self.opener.Preview.closeFrontendView();"><strong>'+closePreviewPopup+'</strong></a></div'});
		}
		
		// we have to set a big timeout cause the page (e.g. DOM) has
		// to finish loading before we are able to use this function
		setTimeout(insertPanel, 5000);
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Close frontend View and set ctrl session var to null.
 * 
 * @param {string} elem Current element
 * @throws applyError on exception
 */
function Preview_closeFrontendView ()
{
	try {
		// Empty hidden field previewCtrl
		document.getElementsByName('preview')[0].value = '';
		
		// close window
		previewWin.close();

	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Navigation
 */
Preview = new Preview();