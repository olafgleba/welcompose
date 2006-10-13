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
Mediamanager.prototype.toggleExtendedView = Mediamanager_toggleExtendedView;
Mediamanager.prototype.checkElemsMyLocal = Mediamanager_checkElemsMyLocal;
Mediamanager.prototype.preserveElementStatusMyLocal = Mediamanager_preserveElementStatusMyLocal;
Mediamanager.prototype.setCurrentElementStatusMyLocal = Mediamanager_setCurrentElementStatusMyLocal;
Mediamanager.prototype.mediaToPodcast = Mediamanager_mediaToPodcast;
Mediamanager.prototype.invokeInputs = Mediamanager_invokeInputs;
Mediamanager.prototype.invokeTags = Mediamanager_invokeTags;
Mediamanager.prototype.initializeTagSearch = Mediamanager_initializeTagSearch;
Mediamanager.prototype.deleteMediaItem = Mediamanager_deleteMediaItem;
Mediamanager.prototype.insertImageItem = Mediamanager_insertImageItem;
Mediamanager.prototype.insertDocumentItem = Mediamanager_insertDocumentItem;
Mediamanager.prototype.discardPodcast = Mediamanager_discardPodcast;

Mediamanager.prototype.invokeTagsMyFlickr = Mediamanager_invokeTagsMyFlickr;
Mediamanager.prototype.initializeTagSearchMyFlickr = Mediamanager_initializeTagSearchMyFlickr;
Mediamanager.prototype.checkElemsMyFlickr = Mediamanager_checkElemsMyFlickr;
Mediamanager.prototype.preserveElementStatusMyFlickr = Mediamanager_preserveElementStatusMyFlickr;
Mediamanager.prototype.setCurrentElementStatusMyFlickr = Mediamanager_setCurrentElementStatusMyFlickr;
Mediamanager.prototype.initializeUserMyFlickr = Mediamanager_initializeUserMyFlickr;

/**
 * Implements method of prototype class Mediamanager
 * Show Mediamanager Element
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
		// IE fails on func getAttribute() with argument 'class'
		if (Helper.unsupportsElems('safari_exception')) {
			this.ttarget = String(this.elem.parentNode.parentNode.attributes[this.attr].value + '_wrap');
		} else {
			this.ttarget = String(this.elem.parentNode.parentNode.getAttribute(this.attr) + '_wrap');
		}
		
		Element.show(this.ttarget);
		
		this.elem.className = this.mediamanagerClassHide;
		Element.update(this.elem, this.elementHtmlHide);
		Behaviour.reapply('.' + this.elem.className);
	
		// needed to set appropriate height of content of div to populate
		var myLocal = Element.getStyle('lyMediamanagerMyLocal', 'display');
		var myFlickr = Element.getStyle('lyMediamanagerMyFlickr', 'display');

		// myLocal
		if (myLocal == 'block') {
			var includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
			var tagsElem = Element.getStyle('mm_tags_wrap', 'display');
			var timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');
			
			var collectElems = String(includeTypesElem + tagsElem + timeframeElem);
			
			if (includeTypesElem == 'block') {
				var rows = 1;
			}
		}
		// myFlickr
		else if (myFlickr == 'block') {
			var userElem = Element.getStyle('mm_user_wrap', 'display');
			var flickrtagsElem = Element.getStyle('mm_flickrtags_wrap', 'display');
			var photosetElem = Element.getStyle('mm_photoset_wrap', 'display');
			
			var collectElems = String(userElem + flickrtagsElem + photosetElem);
			
			// do nothing on var
			var rows = '';
		}
		// set appropriate height and width of surrounding divs
		_checkOccurrences (collectElems, rows);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Hide Mediamanager Element
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
		// IE fails on func getAttribute() with argument 'class'
		if (Helper.unsupportsElems('safari_exception')) {
			this.ttarget = String(this.elem.parentNode.parentNode.attributes[this.attr].value + '_wrap');
		} else {
			this.ttarget = String(this.elem.parentNode.parentNode.getAttribute(this.attr) + '_wrap');
		}

		Element.hide(this.ttarget);
		
		this.elem.className = this.mediamanagerClassShow;
		Element.update(this.elem, this.elementHtmlShow);
		Behaviour.reapply('.' + this.elem.className);
		
		// needed to set appropriate height of content of div to populate
		var myLocal = Element.getStyle('lyMediamanagerMyLocal', 'display');
		var myFlickr = Element.getStyle('lyMediamanagerMyFlickr', 'display');

		// myLocal
		if (myLocal == 'block') {
			var includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
			var tagsElem = Element.getStyle('mm_tags_wrap', 'display');
			var timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');
			
			var collectElems = String(includeTypesElem + tagsElem + timeframeElem);
			
			if (includeTypesElem == 'block') {
				var rows = 1;
			}
		}
		// myFlickr
		else if (myFlickr == 'block') {
			var userElem = Element.getStyle('mm_user_wrap', 'display');
			var flickrtagsElem = Element.getStyle('mm_flickrtags_wrap', 'display');
			var photosetElem = Element.getStyle('mm_photoset_wrap', 'display');
			
			var collectElems = String(userElem + flickrtagsElem + photosetElem);
			
			// do nothing on var
			var rows = '';
		}
		// set appropriate height and width of surrounding divs
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
 * Toggle Extended View on Podcasts (show Details)
 *
 * @param {var} elem Actual elem to toggle 
 * @throws applyError on exception
 */
function Mediamanager_toggleExtendedView (elem)
{
	try {

		if (elem.value == showDetails) {
			elem.value = hideDetails;
			if (Helper.unsupportsEffects('safari_exception')) {
				Element.show('extendedView');
			} else {
				Effect.Appear('extendedView',{duration: 0.4});
			}
		} else {
			elem.value = showDetails;
			if (Helper.unsupportsEffects('safari_exception')) {
				Element.hide('extendedView');
			} else {
				Effect.Fade('extendedView',{duration: 0.4});
			}
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements private method of prototype class Mediamanager
 * Check Option Occurrences and adjust height of content div
 *
 * @private
 * @param {string} elems actual element
 * @param {string} exception 
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
			Element.setStyle(prefix + 'mm_content', {height: '413px'});
			Element.setStyle(prefix + 'mm_contentToPopulate', {height: '410px'});
		} else {
			switch (res.length) {
				case 1 :
						if (Mediamanager.isNumber(exception) === true) {
							var cHeight = '372px';
							var pHeight = '369px';
							countItems = 7;
						} else {
							var cHeight = '392px';
							var pHeight = '389px';
							countItems = 8;
						}
						Element.setStyle(prefix + 'mm_content', {height: cHeight});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: pHeight});
					break;
				case 2 :
						if (Mediamanager.isNumber(exception) === true) {
							var cHeight = '351px';
							var pHeight = '348px';
							countItems = 7;
						} else {
							var cHeight = '371px';
							var pHeight = '368px';
							countItems = 7;
						}
						Element.setStyle(prefix + 'mm_content', {height: cHeight});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: pHeight});
					break;
				case 3 :
						if (Mediamanager.isNumber(exception) === true) {
							var cHeight = '330px';
							var pHeight = '327px';
							countItems = 6;
						} else {
							var cHeight = '350px';
							var pHeight = '347px';
							countItems = 6;
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
 * Implements private method of prototype class Mediamanager
 * Check elements display status for further use in func setCurrentElementStatusMyLocal
 * @private
 * @throws applyError on exception
 */
function Mediamanager_preserveElementStatusMyLocal ()
{	
	try {
	
		var current_includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
		var current_tagsElem = Element.getStyle('mm_tags_wrap', 'display');
		var current_timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');
	
		// make global -> use in func setCurrentElementStatusMyLocal
		previousElemsStatus = new Array (current_includeTypesElem, current_tagsElem, current_timeframeElem);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements private method of prototype class Mediamanager
 * Sets elements class and html correponding the previous status
 * @private
 * @throws applyError on exception
 */
function Mediamanager_setCurrentElementStatusMyLocal ()
{	
	try {
		
		Element.setStyle('mm_include_types_wrap', {display: previousElemsStatus[0]});
		Element.setStyle('mm_tags_wrap', {display: previousElemsStatus[1]});
		Element.setStyle('mm_timeframe_wrap', {display: previousElemsStatus[2]});
		
		collectElems = String(previousElemsStatus[0] + previousElemsStatus[1] + previousElemsStatus[2]);

		// give first field includeTypes a little more space
		// since we have two rows here
		// bool
		if (previousElemsStatus[0] == 'block') {
			var rows = 1;
		}
		// set appropriate height and width of surrounding divs
		_checkOccurrences (collectElems, rows);
				
		// get all relevant spans
		var parentElem = $('lyMediamanagerMyLocal').getElementsByClassName('bez');
		
		if (previousElemsStatus[0] == 'block') {
			parentElem[0].lastChild.className = this.mediamanagerClassHide;
			parentElem[0].lastChild.innerHTML = this.elementHtmlHide;
		}
		if (previousElemsStatus[1] == 'none') {
			parentElem[1].lastChild.className = this.mediamanagerClassShow;
			parentElem[1].lastChild.innerHTML = this.elementHtmlShow;
		}
		if (previousElemsStatus[2] == 'block') {
			parentElem[2].lastChild.className = this.mediamanagerClassHide;
			parentElem[2].lastChild.innerHTML = this.elementHtmlHide;
		}
		
		// observe if needed at least
		//Behaviour.apply();

	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Show Podcast layer and fill media player
 *
 * @param {string} elem element (id) to process
 * @throws applyError on exception
 */
function Mediamanager_mediaToPodcast (elem)
{
	try {
		// properties
		this.toShow = $('podcast_container');
		
		Element.show(this.toShow);
		Element.scrollTo(this.toShow);
		
		// set hidden field value
		$('mediafile_id').value = elem.id;

		var url = this.parseMedCastsUrl;
		var pars = 'id=' + elem.id;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : _loaderMediaToPodcast,
				parameters : pars,
				onComplete : _showResponseMediaToPodcast
			});	
			
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Populate on JSON response
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _showResponseMediaToPodcast(req)
{
	try {		
		$('mediafile_container').innerHTML = req.responseText;
			
		// dont be able to use appear here, because Effect.Opacity don't use display: block
		Element.hide('indicatorPodcast');
		Element.show('podcast_container_loader');		
		// observe if needed at least
		//Behaviour.apply();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * fires temporary actions while processing the ajax call
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _loaderMediaToPodcast ()
{
	try {
		var hideContentTable = $('podcast_container_loader');
		Element.hide(hideContentTable);
		Element.show('indicatorPodcast');
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
		Mediamanager.preserveElementStatusMyLocal();
		
		var elems = Mediamanager.checkElemsMyLocal();
		var url = this.parseMedLocalUrl;
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
 * Implements method of prototype class Mediamanager
 * set a delay for firing the ajax search invoke
 * special handling for tag search
 * 
 * @throws applyError on exception
 */
function Mediamanager_initializeTagSearch ()
{
	try {
		// clear the keyPressDelay if it exists from before
		if (this.keyPressDelay) {
			window.clearTimeout(this.keyPressDelay);
		}
		if ($('mm_tags').value >= '') {
			this.keyPressDelay = window.setTimeout("Mediamanager.invokeTags()", 800);
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
function Mediamanager_invokeTags ()
{
	try {
		Mediamanager.preserveElementStatusMyLocal();
		
		var elems = Mediamanager.checkElemsMyLocal();
		var url = this.parseMedLocalUrl;
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


/**
 * Implements method of prototype class Mediamanager
 * Populate on JSON response
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _showResponseInvokeInputs(req)
{
	try {
		$('column').innerHTML = req.responseText;
		
		Mediamanager.setCurrentElementStatusMyLocal();
		
		Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		Event.observe($('mm_flickrtags'), 'keyup', Mediamanager.initializeTagSearchMyFlickr);
		
		$('column').focus();
		
		Behaviour.reapply('input');
		Behaviour.reapply('a.mm_edit');
		Behaviour.reapply('a.mm_upload');
		Behaviour.reapply('a.mm_delete');
		Behaviour.reapply('a.mm_cast');
		Behaviour.reapply('a.mm_insertImageItem');
		Behaviour.reapply('a.mm_insertDocumentItem');
		Behaviour.reapply('a.mm_myLocal');
		Behaviour.reapply('a.mm_myFlickr');
		Behaviour.reapply('#mm_include_types_wrap');
		Behaviour.reapply('#mm_timeframe');
		Behaviour.reapply('#mm_user');
		Behaviour.reapply('#mm_photoset');
		Behaviour.reapply('#mm_flickrtags');
		Behaviour.reapply('.showMediamanagerElement');
		Behaviour.reapply('.hideMediamanagerElement');
		Behaviour.reapply('.iHelpMediamanager');
		Behaviour.reapply('.iHelpRemoveMediamanager');

	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Populate on JSON response
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _showResponseInvokeTagInputs(req)
{
	try {	
		$('column').innerHTML = req.responseText;
		
		// refering to https://bugzilla.mozilla.org/show_bug.cgi?id=236791
		$('mm_tags').setAttribute("autocomplete","off");
		$('mm_flickrtags').setAttribute("autocomplete","off");
		
		Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		Event.observe($('mm_flickrtags'), 'keyup', Mediamanager.initializeTagSearchMyFlickr);
		
		Mediamanager.setCurrentElementStatusMyLocal();
		Forms.setOnEvent($('mm_tags'), '','#0c3','dotted');	
		$('mm_tags').focus();
		
		Behaviour.reapply('input');
		Behaviour.reapply('a.mm_edit');
		Behaviour.reapply('a.mm_upload');
		Behaviour.reapply('a.mm_delete');
		Behaviour.reapply('a.mm_cast');
		Behaviour.reapply('a.mm_insertImageItem');
		Behaviour.reapply('a.mm_insertDocumentItem');
		Behaviour.reapply('a.mm_myLocal');
		Behaviour.reapply('a.mm_myFlickr');
		Behaviour.reapply('#mm_include_types_wrap');
		Behaviour.reapply('#mm_timeframe');
		Behaviour.reapply('#mm_user');
		Behaviour.reapply('#mm_photoset');
		Behaviour.reapply('#mm_flickrtags');
		Behaviour.reapply('.showMediamanagerElement');
		Behaviour.reapply('.hideMediamanagerElement');
		Behaviour.reapply('.iHelpMediamanager');
		Behaviour.reapply('.iHelpRemoveMediamanager');
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * fires temporary actions while processing the ajax call
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _loader ()
{
	try {
		var hideContentTable = document.getElementsByClassName('mm_content')[0];
		Element.hide(hideContentTable);
		Element.show('indicator_local');
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
function Mediamanager_checkElemsMyLocal ()
{
	try {
		if (typeof countItems == 'undefined') {
			// initialize global with 'save' display
			countItems = 0;
		};
		
		var getElems = {
			mm_include_types_img : $F('mm_include_types_img'),
			mm_include_types_doc : $F('mm_include_types_doc'),
			mm_include_types_audio : $F('mm_include_types_audio'),
			mm_include_types_video : $F('mm_include_types_video'),
			mm_include_types_other : $F('mm_include_types_other'),
			mm_tags : $F('mm_tags'),
			mm_timeframe : $F('mm_timeframe'),
			mm_limit : countItems,
			mm_pagetype : pagetype
		};
		var o = $H(getElems);
		//countItems = null;
		return o.toQueryString();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Fires the ajax request to delete an item
 * 
 * @throws applyError on exception
 */
function Mediamanager_deleteMediaItem (elem)
{
	try {
		// properties
		var url = this.parseMedDeleteUrl;
		var pars = 'id=' + elem.id;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : _loader,
				parameters : pars,
				onComplete : function () {Mediamanager.invokeInputs();}
			});		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Fires the ajax request to delete an item
 * 
 * @throws applyError on exception
 */
function Mediamanager_insertImageItem (elem)
{
	try {
		// global var comes from Forms.storeFocus() and oak.strings.js
		if (typeof storedFocus == 'undefined') {
			alert(selectTextarea); 
		} else {
			var target = storedFocus;
	
			var build;
			build = '{get_media id="';
			build += elem.id;
			build += '"}';
		
			strStart = build;
			
			_insertTags(target, strStart, '' , '');
		}
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Fires the ajax request to delete an item
 * 
 * @throws applyError on exception
 */
function Mediamanager_insertDocumentItem (elem)
{
	try {
		// global var comes from Forms.storeFocus() and oak.strings.js
		if (typeof storedFocus == 'undefined') {
			alert(selectTextarea); 
		} else {
			var target = storedFocus;
	
			var build;
			build = '<a href="';
			build += '{get_media id="';
			build += elem.id;
			build += '"}';
			build += '">';
		
			strStart = build;
			strEnd = '</a>';
			
			_insertTags(target, strStart, strEnd , describeLink);
		}
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * Get rid off podcast
 *
 * @param {var} elem Actual elem to get rid off 
 * @throws applyError on exception
 */
function Mediamanager_discardPodcast (elem)
{
	try {
		// discard hidden field value
		$('mediafile_id').value = '';
		
			if (Helper.unsupportsEffects('safari_exception')) {
				Element.hide('podcast_container');
			} else {
				Effect.Fade('podcast_container',{duration: 0.4});
			}
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * set a delay for firing the ajax search invoke
 * special handling for tag search
 * 
 * @throws applyError on exception
 */
function Mediamanager_initializeTagSearchMyFlickr ()
{
	try {
		// clear the keyPressDelay if it exists from before
		if (this.keyPressDelay) {
			window.clearTimeout(this.keyPressDelay);
		}
		if ($('mm_flickrtags').value >= '') {
			this.keyPressDelay = window.setTimeout("Mediamanager.invokeTagsMyFlickr()", 800);
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
function Mediamanager_invokeTagsMyFlickr ()
{
	try {
		Mediamanager.preserveElementStatusMyFlickr();
		
		var elems = Mediamanager.checkElemsMyFlickr();
		var url = this.parseMedFlickrUrl;
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : _loaderMyFlickr,
				parameters : pars,
				onComplete : _showResponseInvokeTagsMyFlickr
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
function Mediamanager_initializeUserMyFlickr ()
{
	try {
		Mediamanager.preserveElementStatusMyFlickr();
		
		var elems = Mediamanager.checkElemsMyFlickr();
		var url = this.parseMedFlickrUrl;
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : _loaderInitializeUserMyFlickr,
				parameters : pars,
				onComplete : _showResponseInvokeTagsMyFlickr
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * fires temporary actions while processing the ajax call
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _loaderInitializeUserMyFlickr ()
{
	try {
		$('submitFlickrFindByUsername').style.background = '#666 url(../static/img/submitindicator90.gif) no-repeat';
	} catch (e) {
		_applyError(e);
	}
}



/**
 * Implements method of prototype class Mediamanager
 * Populate on JSON response
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _showResponseInvokeTagsMyFlickr(req)
{
	try {
		$('column').innerHTML = req.responseText;
		Element.show('lyMediamanagerMyFlickr');
		Element.hide('lyMediamanagerMyLocal');
		
		// reset button
		$('submitFlickrFindByUsername').style.background = '#666 url(../static/img/submit90.gif) no-repeat';
		
		// show option inputs
		var mm_flickrtags = document.getElementsByClassName('mm_flickrtags');
		var mm_photoset = document.getElementsByClassName('mm_photoset');
		Element.setStyle(mm_flickrtags[0], {visibility: 'visible'});
		Element.setStyle(mm_photoset[0], {visibility: 'visible'});
		
		// refering to https://bugzilla.mozilla.org/show_bug.cgi?id=236791
		$('mm_tags').setAttribute("autocomplete","off");
		$('mm_flickrtags').setAttribute("autocomplete","off");
		
		Mediamanager.setCurrentElementStatusMyFlickr();
		Forms.setOnEvent($('mm_flickrtags'), '','#0c3','dotted');
		$('mm_flickrtags').focus();
								
		Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		Event.observe($('mm_flickrtags'), 'keyup', Mediamanager.initializeTagSearchMyFlickr);
		
		Behaviour.reapply('input');
		Behaviour.reapply('a.mm_edit');
		Behaviour.reapply('a.mm_upload');
		Behaviour.reapply('a.mm_delete');
		Behaviour.reapply('a.mm_cast');
		Behaviour.reapply('a.mm_insertImageItem');
		Behaviour.reapply('a.mm_insertDocumentItem');
		Behaviour.reapply('a.mm_myLocal');
		Behaviour.reapply('a.mm_myFlickr');
		Behaviour.reapply('#mm_include_types_wrap');
		Behaviour.reapply('#mm_timeframe');
		Behaviour.reapply('#mm_user');
		Behaviour.reapply('#mm_photoset');
		Behaviour.reapply('#mm_flickrtags');
		Behaviour.reapply('#submitFlickrFindByUsername');
		Behaviour.reapply('.showMediamanagerElement');
		Behaviour.reapply('.hideMediamanagerElement');
		Behaviour.reapply('.iHelpMediamanager');
		Behaviour.reapply('.iHelpRemoveMediamanager');

	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 * fires temporary actions while processing the ajax call
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _loaderMyFlickr ()
{
	try {
		var hideContentTable = document.getElementsByClassName('mm_content')[1];
		Element.hide(hideContentTable);
		Element.show('indicator_flickr');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements private method of prototype class Mediamanager
 * Check elements display status for further use in func setCurrentElementStatusMyLocal
 * @private
 * @throws applyError on exception
 */
function Mediamanager_preserveElementStatusMyFlickr ()
{	
	try {
	
		var current_userElem = Element.getStyle('mm_user_wrap', 'display');
		var current_flickrtagsElem = Element.getStyle('mm_flickrtags_wrap', 'display');
		var current_photosetElem = Element.getStyle('mm_photoset_wrap', 'display');
	
		// make global -> use in func setCurrentElementStatusMyLocal
		previousElemsStatus = new Array (current_userElem, current_flickrtagsElem, current_photosetElem);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements private method of prototype class Mediamanager
 * Sets elements class and html correponding the previous status
 * @private
 * @throws applyError on exception
 */
function Mediamanager_setCurrentElementStatusMyFlickr ()
{	
	try {
		
		Element.setStyle('mm_user_wrap', {display: previousElemsStatus[0]});
		Element.setStyle('mm_flickrtags_wrap', {display: previousElemsStatus[1]});
		Element.setStyle('mm_photoset_wrap', {display: previousElemsStatus[2]});
		
		collectElems = String(previousElemsStatus[0] + previousElemsStatus[1] + previousElemsStatus[2]);
		
		// do nothing on var
		var rows = '';
			
		// set appropriate height and width of surrounding divs
		_checkOccurrences (collectElems, rows);
				
		// get all relevant spans
		var parentElem = $('lyMediamanagerMyFlickr').getElementsByClassName('bez');
		
		if (previousElemsStatus[0] == 'none') {
			parentElem[0].lastChild.className = this.mediamanagerClassShow;
			parentElem[0].lastChild.innerHTML = this.elementHtmlShow;
		}
		if (previousElemsStatus[1] == 'block') {
			parentElem[1].lastChild.className = this.mediamanagerClassHide;
			parentElem[1].lastChild.innerHTML = this.elementHtmlHide;
		}
		if (previousElemsStatus[2] == 'block') {
			parentElem[2].lastChild.className = this.mediamanagerClassHide;
			parentElem[2].lastChild.innerHTML = this.elementHtmlHide;
		}
		
		// observe if needed at least
		//Behaviour.apply();

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
function Mediamanager_checkElemsMyFlickr ()
{
	try {
		if (typeof countItems == 'undefined') {
			// initialize global with 'save' display
			countItems = 0;
		};
		
		var getElems = {
			mm_user : $F('mm_user'),
			mm_flickrtags : $F('mm_flickrtags'),
			mm_photoset : $F('mm_photoset'),
			mm_limit : countItems,
			mm_pagetype : pagetype
		};
		var o = $H(getElems);
		//countItems = null;
		return o.toQueryString();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for @class Mediamananager
 */
Mediamanager = new Mediamanager();
