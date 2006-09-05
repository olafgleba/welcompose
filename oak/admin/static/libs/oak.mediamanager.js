/**
 * Project: Oak
 * File: oak.mediamanager.js
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
 * @fileoverview This file is the essential Mediamanager javascript enviroment.
 * It describes all core classes and functions. It is needed to call oak.strings.js before embedding this file,
 * to make it unnecessary to loop this core file through the i18n parser.
 *
 * @author Olaf Gleba og@creatics.de
 * @version $Id$ 
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
		
		/**
		 * Get new XMLHttpRequest Object by private function
		 */
		this.req = _buildXMLHTTPRequest();
				
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

Mediamanager.prototype.deleteMediaItem = Mediamanager_deleteMediaItem;
Mediamanager.prototype.invokeInputs = Mediamanager_invokeInputs;
Mediamanager.prototype.invokeTags = Mediamanager_invokeTags;
Mediamanager.prototype.initializeTagSearch = Mediamanager_initializeTagSearch;

Mediamanager.prototype.checkMyLocalElems = Mediamanager_checkMyLocalElems;


/**
 * Implements private method of prototype class Mediamanager
 * Check Option Occurrences and adjust height of content div
 * @private
 * @param {string} elems actual element
 * @param {string} prefix actual divs to process (myLocal, myFlickr)
 * @throws applyError on exception
 */
function _checkOccurrences (elems, exception)
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
			Element.setStyle(prefix + 'mm_contentToPopulate', {height: '383px'});
		} else {
			switch (res.length) {
				case 1 :
						if (exception) {
							var cHeight = '345px';
							var pHeight = '342px';
						} else {
							var cHeight = '365px';
							var pHeight = '362px';
						}
						Element.setStyle(prefix + 'mm_content', {height: cHeight});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: pHeight});
					break;
				case 2 :
						if (exception) {
							var cHeight = '324px';
							var pHeight = '321px';
						} else {
							var cHeight = '344px';
							var pHeight = '341px';
						}
						Element.setStyle(prefix + 'mm_content', {height: cHeight});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: pHeight});
					break;
				case 3 :
						if (exception) {
							var cHeight = '303px';
							var pHeight = '300px';
						} else {
							var cHeight = '323px';
							var pHeight = '320px';
						}
						Element.setStyle(prefix + 'mm_content', {height: cHeight});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: pHeight});
					break;		
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Hide Formfield Element
 *
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
		
		// needed to set appropriate height of content of div to populate
		var includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
		var tagsElem = Element.getStyle('mm_tags_wrap', 'display');
		var timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');

		var collectElems = String(includeTypesElem + tagsElem + timeframeElem);
		
		if (includeTypesElem == 'block') {
			var rows = '1';
		} else {
			var rows = '';
		}
		_checkOccurrences (collectElems, rows);	
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Display Formfield Element
 * 
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
	
		// needed to set appropriate height of content of div to populate
		var includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
		var tagsElem = Element.getStyle('mm_tags_wrap', 'display');
		var timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');
		
		var collectElems = String(includeTypesElem + tagsElem + timeframeElem);
		
		if (includeTypesElem == 'block') {
			var rows = '1';
		} else {
			var rows = '';
		}
		_checkOccurrences (collectElems, rows);
	
		
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Implements method of prototype class Mediamanager
 * Switch layer on a(link) event e.g. a.mm_myLocal, a.mm_myFlickr
 *
 * @param {string} toShow div to display
 * @param {string} toHide div to hide
 * @throws applyError on exception
 */
function Mediamanager_switchLayer (toShow, toHide)
{
	try {
		// properties
		this.toShow = $(toShow);
		this.toHide = $(toHide);
	
		Element.hide(this.toHide);

		if (Helper.unsupportsEffects()) {
			Element.show(this.toShow);
		} else {
			Effect.Appear(this.toShow,{duration: 0.4});
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Fires the ajax request
 * 
 * @throws applyError on exception
 */
function Mediamanager_deleteMediaItem (elem)
{
	try {
		// properties
		var url = '../mediamanager/mediamanager_delete.php';
		var pars = 'id=' + elem.name;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : _loader,
				parameters : pars,
				onComplete : Mediamanager.invokeInputs
			});	
					
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Fires the ajax request
 * 
 * @throws applyError on exception
 */
function Mediamanager_invokeInputs ()
{
	try {
		_preserveElementStatus ();
/*	
	var neu = $('mm_include_types_wrap').parentNode.getAttribute('class');
	
	alert (neu);
*/
		
		var elems = Mediamanager.checkMyLocalElems();
		var url = '../mediamanager/mediamanager.php';
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : _loader,
				parameters : pars,
				onComplete : _showResponseInvokeInputs
			});
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Implements method of prototype class Tables
 * @param {string} elem actual element to process
 * @throws applyError on exception
 */
function Mediamanager_initializeTagSearch ()
{
	try {
		// clear the keyPressDelay if it exists from before
		if (this.keyPressDelay) {
			window.clearTimeout(this.keyPressDelay);
		}
	
		//if ($('mm_tags').value != '') {
		if ($('mm_tags').value >= '') {
			this.keyPressDelay = window.setTimeout("Mediamanager.invokeTags()", 800);
		}
		//return true;
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Implements method of prototype class Mediamanager
 * Fires the ajax request
 * 
 * @throws applyError on exception
 */
function Mediamanager_invokeTags ()
{
	try {
		var elems = Mediamanager.checkMyLocalElems();
		var url = '../mediamanager/mediamanager.php';
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : _loader,
				parameters : pars,
				onComplete : _showResponseInvokeTagInputs
			});
	} catch (e) {
		_applyError(e);
	}
}


function _preserveElementStatus ()
{	
	try {
	
		current_includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
		current_tagsElem = Element.getStyle('mm_tags_wrap', 'display');
		current_timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');
	
	//	previousElems = new Array (previous_includeTypesElem, previous_tagsElem, previous_timeframeElem);
		//return previousElems;
		//alert (includeTypesElem + ', ' + tagsElem + ', ' + timeframeElem);
		
		/* dom processing
		var includeType = $('mm_include_types_wrap').parentNode.firstChild;	
		var inner = includeType.lastChild.getAttribute('class');
		var tag = includeType.lastChild.nodeName;
		*/
		
		// by ID with index
		includeType = document.getElementsByClassName('showMediamanagerElement')[0];
		//alert (includeType.innerHTML);
		
	//	includeType.className = this.mediamanagerClassHide;
		//Element.update($(includeType), this.elementHtmlHide);
		
		$(includeType).innerHTML = '<a href="#" title="' + hideElement + '"><img src="../static/img/icons/close.gif" alt="" /></a>';
		Behaviour.apply();
		
		//alert (inner); 
		
		
	} catch (e) {
		_applyError(e);
	}
}


function _showElementAfterAjaxInvoke (ttarget)
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
	
		// needed to set appropriate height of content of div to populate
		var includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
		var tagsElem = Element.getStyle('mm_tags_wrap', 'display');
		var timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');
		
		var collectElems = String(includeTypesElem + tagsElem + timeframeElem);
		
		if (includeTypesElem == 'block') {
			var rows = '1';
		} else {
			var rows = '';
		}
		_checkOccurrences (collectElems, rows);
	
		
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Implements method of prototype class Mediamanager
 * Populate on JSON response
 *
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _showResponseInvokeInputs(req)
{
	try {
		
		//alert ('set: ' +previousElems);
		
		$('column').innerHTML = req.responseText;
		
	/*	Element.setStyle('mm_include_types_wrap', {display: previousElems[0]});
		Element.setStyle('mm_tags_wrap', {display: previousElems[1]});
		Element.setStyle('mm_timeframe_wrap', {display: previousElems[3]});
	*/	

	/*	
		for (var i = 0; i < previousElems.length; i++) {		
			var range = previousElems[i];
			if (range == 'block') {
				//Mediamanager.showElement();
				this.elem.className = this.mediamanagerClassHide;
				Element.update(this.elem, this.elementHtmlHide);
				alert ('zeigen');
			} else {
				//Mediamanager.hideElement();
				alert ('verstecken');
			}
		}
*/
		Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		
		$('column').focus();
		
		Behaviour.apply();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Populate on JSON response
 *
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _showResponseInvokeTagInputs(req)
{
	try {
		
		//alert ('set: ' +previousElems);
		
		$('column').innerHTML = req.responseText;
		
	/*	Element.setStyle('mm_include_types_wrap', {display: previousElems[0]});
		Element.setStyle('mm_tags_wrap', {display: previousElems[1]});
		Element.setStyle('mm_timeframe_wrap', {display: previousElems[3]});
	*/	

	/*	
		for (var i = 0; i < previousElems.length; i++) {		
			var range = previousElems[i];
			if (range == 'block') {
				//Mediamanager.showElement();
				this.elem.className = this.mediamanagerClassHide;
				Element.update(this.elem, this.elementHtmlHide);
				alert ('zeigen');
			} else {
				//Mediamanager.hideElement();
				alert ('verstecken');
			}
		}
*/
		
		// refering to https://bugzilla.mozilla.org/show_bug.cgi?id=236791
		$('mm_tags').setAttribute("autocomplete","off");
		
		$('mm_tags').focus();	
		
		Forms.setOnEvent($('mm_tags'), '','#0c3','dotted');
		
		Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		
		Behaviour.apply();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Fires the ajax request
 * 
 * @throws applyError on exception
 */
function Mediamanager_checkMyLocalElems ()
{
	try {
		var getElems = {
			mm_include_types_img : $F('mm_include_types_img'),
			mm_include_types_doc : $F('mm_include_types_doc'),
			mm_include_types_audio : $F('mm_include_types_audio'),
			mm_include_types_video : $F('mm_include_types_video'),
			mm_include_types_other : $F('mm_include_types_other'),
			mm_tags : $F('mm_tags'),
			mm_timeframe : $F('mm_timeframe'),
			mm_limit : 500
		};
	
		var o = $H(getElems);
		return o.toQueryString();
	} catch (e) {
		_applyError(e);
	}
}

function _loader ()
{
	try {
		var hideContentTable = document.getElementsByClassName('mm_content')[0];
		Element.hide(hideContentTable);
		Element.show('indicator');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for @class Mediamananager
 */
Mediamanager = new Mediamanager();
