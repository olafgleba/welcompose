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
 * @fileoverview This file is the essential Mediamanager javascript enviroment.
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
		// properties
				
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

Mediamanager.prototype.invokeInputs = Mediamanager_invokeInputs;
Mediamanager.prototype.checkElems = Mediamanager_checkElems;


/**
 * Implements private method of prototype class Mediamanager
 * Check Option Occurrences and adjust height of content div
 * @private
 * @param {string} elems actual element
 * @param {string} prefix actual divs to process (myLocal, myFlickr)
 * @throws applyError on exception
 */
function _checkOccurrences (elems)
{
	try {
				
		var myLocal = Element.getStyle('lyMediamanagerMyLocal', 'display');

		if (myLocal == 'block') {
			var prefix = 'myLocal_';
		} else {
			var prefix = 'myFlickr_';
		}
		
		var res = elems.match(/block/gi);	
	
		if (Mediamanager.isNull(res)) {
			Element.setStyle(prefix + 'mm_content', {height: '386px'});
			Element.setStyle(prefix + 'mm_contentToPopulate', {height: '379px'});
		} else {
			switch (res.length) {
				case 1 :
						Element.setStyle(prefix + 'mm_content', {height: '365px'});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: '358px'});
					break;
				case 2 :
						Element.setStyle(prefix + 'mm_content', {height: '344px'});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: '337px'});
					break;
				case 3 :
						Element.setStyle(prefix + 'mm_content', {height: '323px'});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: '316px'});
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
		
		this.elem.className = this.mediamanagerClass;
		Element.update(this.elem, this.elementHtmlShow);
		Behaviour.apply();
		
		// needed to set appropriate height of content to populate div
		var includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
		var tagsElem = Element.getStyle('mm_tags_wrap', 'display');
		var timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');

		var collectElems = String(includeTypesElem + tagsElem + timeframeElem);		
		_checkOccurrences (collectElems);
	
		
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
		this.elem.className = this.mediamanagerClassHide;
		Element.update(this.elem, this.elementHtmlHide);
		Behaviour.apply();
	
		// needed to set appropriate height of content to populate div
		var includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
		var tagsElem = Element.getStyle('mm_tags_wrap', 'display');
		var timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');
		
		var collectElems = String(includeTypesElem + tagsElem + timeframeElem);
		_checkOccurrences (collectElems);
	
		
	} catch (e) {
		_applyError(e);
	}
}


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
			if (Mediamanager.unsupportsEffects()) {
				Element.show(this.helpLyMediamanagerMyFlickr);
			} else {
				Effect.Appear(this.helpLyMediamanagerMyFlickr,{duration: 0.6});
			}
		} else {
			if (Mediamanager.unsupportsEffects()) {
				Element.show(this.helpLyMediamanagerMyLocal);
			} else {
				Effect.Appear(this.helpLyMediamanagerMyLocal,{duration: 0.6});
			}
		}
	} catch (e) {
		_applyError(e);
	}
}




function Mediamanager_invokeInputs ()
{
	var elems = Mediamanager.checkElems();
	
	//alert (elems);
	var url = '../mediamanager.ajax.php';
	var pars = elems;
	
	var myAjax = new Ajax.Request(
		url,
		{
			method : 'get',
			parameters : pars,
			onComplete : showResponse
		});	
}

function showResponse(req)
{
	$('myLocal_mm_contentToPopulate').innerHTML = req.responseText;
}


function Mediamanager_checkElems ()
{
	var getElems = {
		mm_include_types_doc : $F('mm_include_types_doc'),
		mm_include_types_img : $F('mm_include_types_img'),
		mm_include_types_cast : $F('mm_include_types_cast'),
		mm_tags : $F('mm_tags'),
		mm_timeframe : $F('mm_timeframe')
	};
	
	var o = $H(getElems);
	return o.toQueryString();
}

/**
 * Building new instance for @class Mediamanager
 */
Mediamanager = new Mediamanager();
