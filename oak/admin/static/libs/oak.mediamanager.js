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
 * $Id: oak.core.js 327 2006-08-13 09:36:41Z olaf $
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
 * @version $Id: oak.core.js 327 2006-08-13 09:36:41Z olaf $ 
 */


/**
 * Construct a new Mediamanager object
 * @class This is the basic Mediamanager class 
 * @constructor
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Mediamanager ()
{
	try {
				
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Mediamanager.prototype = new Base();


/**
 * Instance Methods from prototype @class Mediamanager
 */
Mediamanager.prototype.showElement = Mediamanager_showElement;
Mediamanager.prototype.hideElement = Mediamanager_hideElement;
Mediamanager.prototype.switchLayer = Mediamanager_switchLayer;


/**
 * Implements private method of prototype class Mediamanager
 * Check Option Occurrences and adjust height of content div
 * @private
 * @param {string} elems actual element
 * @param {string} prefix actual divs to process (myLocal, myFlickr)
 * @throws applyError on exception
 */
function _checkOccurrences (elems, prefix)
{
	try {
		var res = elems.match(/block/gi);
	
		if (Mediamanager.isNull(res)) {
			Element.setStyle(prefix + 'mmwrapcontent', {height: '386px'});
			Element.setStyle(prefix + 'mmwrapcontentToPopulate', {height: '379px'});
		} else {
			switch (res.length) {
				case 1 :
						Element.setStyle(prefix + 'mmwrapcontent', {height: '365px'});
						Element.setStyle(prefix + 'mmwrapcontentToPopulate', {height: '358px'});
					break;
				case 2 :
						Element.setStyle(prefix + 'mmwrapcontent', {height: '344px'});
						Element.setStyle(prefix + 'mmwrapcontentToPopulate', {height: '337px'});
					break;
				case 3 :
						Element.setStyle(prefix + 'mmwrapcontent', {height: '323px'});
						Element.setStyle(prefix + 'mmwrapcontentToPopulate', {height: '316px'});
					break;
			}
		}
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Implements method of prototype class Mediamanager
 * @param {string} elem actual element
 * @throws applyError on exception
 */
function Mediamanager_hideElement (elem)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'class';
		this.ttarget = String(this.elem.parentNode.parentNode.getAttribute(this.attr) + '_wrap');
		
		Element.hide(this.ttarget);
		//Effect.Fade(this.ttarget,{duration: 0.6});
		this.elem.className = this.mediamanagerClass;
		Element.update(this.elem, this.elementHtmlShow);
		Behaviour.apply();
				
		var res = this.ttarget.match(/^myLocal/i);
		if (res) {
			var prefix = 'myLocal_';
			var tagsElem = Element.getStyle(prefix + 'tags_wrap', 'display');
			var timeframeElem = Element.getStyle(prefix + 'timeframe_wrap', 'display');
			var includeTypesElem = Element.getStyle(prefix + 'include_types_wrap', 'display');
		} else {
			var prefix = 'myFlickr_';
			var tagsElem = Element.getStyle(prefix + 'tags_wrap', 'display');
			var timeframeElem = Element.getStyle(prefix + 'timeframe_wrap', 'display');
			var includeTypesElem = Element.getStyle(prefix + 'include_types_wrap', 'display');
		}
		
		var collectElems = String(tagsElem + timeframeElem + includeTypesElem);
		
		_checkOccurrences (collectElems, prefix);
	
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * @param {string} elem actual element
 * @throws applyError on exception
 */
function Mediamanager_showElement (elem)
{	
	try {
		// properties
		this.elem = elem;
		this.attr = 'class';
		this.ttarget = String(this.elem.parentNode.parentNode.getAttribute(this.attr) + '_wrap');

		Element.show(this.ttarget);
	//	Effect.Appear(this.ttarget,{duration: 0.6});
		this.elem.className = this.mediamanagerClassHide;
		Element.update(this.elem, this.elementHtmlHide);
		Behaviour.apply();
	
		var res = this.ttarget.match(/^myLocal/i);
		if (res) {
			var prefix = 'myLocal_';
			var tagsElem = Element.getStyle(prefix + 'tags_wrap', 'display');
			var timeframeElem = Element.getStyle(prefix + 'timeframe_wrap', 'display');
			var includeTypesElem = Element.getStyle(prefix + 'include_types_wrap', 'display');
		} else {
			var prefix = 'myFlickr_';
			var tagsElem = Element.getStyle(prefix + 'tags_wrap', 'display');
			var timeframeElem = Element.getStyle(prefix + 'timeframe_wrap', 'display');
			var includeTypesElem = Element.getStyle(prefix + 'include_types_wrap', 'display');
		}
		
		var collectElems = String(tagsElem + timeframeElem + includeTypesElem);
		
		_checkOccurrences (collectElems, prefix);
	
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements private method of prototype class Mediamanager
 * Check Option Occurrences and adjust height of content div
 * @private
 * @param {string} elems actual element
 * @param {string} prefix actual divs to process (myLocal, myFlickr)
 * @throws applyError on exception
 */
function Mediamanager_switchLayer (elem)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'id';
		this.ttarget = this.elem.parentNode.parentNode.parentNode.getAttribute(this.attr);
		this.sel = this.elem.getAttribute('class');
		
		Element.hide(this.ttarget);
		
		var res = this.ttarget.match(/myLocal$/gi);
		
		if (res) {
			Effect.Appear(this.helpLyMediamanagerMyFlickr,{duration: 0.7});
		} else {
			Effect.Appear(this.helpLyMediamanagerMyLocal,{duration: 0.7});
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for @class Mediamanager
 */
Mediamanager = new Mediamanager();
