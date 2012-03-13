/**
 * Project: Welcompose
 * File: wcom.mediamanager.js
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
 * @fileoverview The Mediamanager javascript enviroment.
 */

/**
 * Constructs the Mediamanager class
 * 
 * @class The Mediamanager class embed all media maintenance.
 * <br />
 * The <em>Mediamanager</em> is a component within content pages
 * both to maintain all sorts of media (upload, edit, delete) and a repository
 * to pick up from while building content pages.
 * <br /><br />
 * At present there are two layers (<em>myLocal</em> and <em>myFlickr</em>)
 * with different treatments implemented.
 *<br />
 * <em>myLocal</em> handles all the media formerly uploaded into the local structure.
 * <br />
 * <em>myFlickr</em> is a integration of some API methods of the WebService Flickr.com to 
 * allow to integrate Flickr.com photos into sides contents.
 *
 * @constructor
 * @throws applyError on exception
 */
function Mediamanager ()
{
	try {
		// instance XMLHttpRequest object
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

/**
 * Common methods
 */
Mediamanager.prototype.showElement = Mediamanager_showElement;
Mediamanager.prototype.hideElement = Mediamanager_hideElement;
Mediamanager.prototype.switchLayer = Mediamanager_switchLayer;
Mediamanager.prototype.checkOccurrences = Mediamanager_checkOccurrences;
Mediamanager.prototype.applyBehaviour = Mediamanager_applyBehaviour;

/**
 * MyLocal methods
 */
Mediamanager.prototype.checkElemsMyLocal = Mediamanager_checkElemsMyLocal;
Mediamanager.prototype.preserveElementStatusMyLocal = Mediamanager_preserveElementStatusMyLocal;
Mediamanager.prototype.setCurrentElementStatusMyLocal = Mediamanager_setCurrentElementStatusMyLocal;
Mediamanager.prototype.toggleExtendedView = Mediamanager_toggleExtendedView;
Mediamanager.prototype.mediaToPodcast = Mediamanager_mediaToPodcast;
Mediamanager.prototype.mediaToPodcastOnLoad = Mediamanager_mediaToPodcastOnLoad;
Mediamanager.prototype.discardPodcast = Mediamanager_discardPodcast;
Mediamanager.prototype.showResponseMediaToPodcast = Mediamanager_showResponseMediaToPodcast;
Mediamanager.prototype.showResponseDiscardPodcast = Mediamanager_showResponseDiscardPodcast;
Mediamanager.prototype.loaderMediaToPodcast = Mediamanager_loaderMediaToPodcast;
Mediamanager.prototype.initializeTagSearch = Mediamanager_initializeTagSearch;
Mediamanager.prototype.invokeInputs = Mediamanager_invokeInputs;
Mediamanager.prototype.invokeTags = Mediamanager_invokeTags;
Mediamanager.prototype.invokePager = Mediamanager_invokePager;
Mediamanager.prototype.showResponseInvokeInputs = Mediamanager_showResponseInvokeInputs;
Mediamanager.prototype.showResponseInvokeTagInputs = Mediamanager_showResponseInvokeTagInputs;
Mediamanager.prototype.loaderMyLocal = Mediamanager_loaderMyLocal;
Mediamanager.prototype.deleteMediaItem = Mediamanager_deleteMediaItem;
Mediamanager.prototype.processMediaCallbacks = Mediamanager_processMediaCallbacks;
Mediamanager.prototype.insertMediaCallbacks = Mediamanager_insertMediaCallbacks;
Mediamanager.prototype.showResponseProcessMediaCallbacks = Mediamanager_showResponseProcessMediaCallbacks;

/**
 * MyFlickr methods
 */
Mediamanager.prototype.checkElemsMyFlickr = Mediamanager_checkElemsMyFlickr;
Mediamanager.prototype.preserveElementStatusMyFlickr = Mediamanager_preserveElementStatusMyFlickr;
Mediamanager.prototype.setCurrentElementStatusMyFlickr = Mediamanager_setCurrentElementStatusMyFlickr;
Mediamanager.prototype.invokeTagsMyFlickr = Mediamanager_invokeTagsMyFlickr;
Mediamanager.prototype.invokeInputsMyFlickr = Mediamanager_invokeInputsMyFlickr;
Mediamanager.prototype.invokePagerMyFlickr = Mediamanager_invokePagerMyFlickr;
Mediamanager.prototype.initializeTagSearchMyFlickr = Mediamanager_initializeTagSearchMyFlickr;
Mediamanager.prototype.initializeUserMyFlickr = Mediamanager_initializeUserMyFlickr;
Mediamanager.prototype.showResponseInvokeInputsMyFlickr = Mediamanager_showResponseInvokeInputsMyFlickr;
Mediamanager.prototype.showResponseInvokeTagsMyFlickr = Mediamanager_showResponseInvokeTagsMyFlickr;
Mediamanager.prototype.loaderMyFlickr = Mediamanager_loaderMyFlickr;
Mediamanager.prototype.processMediaCallbacksFlickr = Mediamanager_processMediaCallbacksFlickr;
Mediamanager.prototype.showResponseProcessMediaCallbacksFlickr = Mediamanager_showResponseProcessMediaCallbacksFlickr;


/**
 * Display Media Manager element.
 * <br />
 * Beside simply showing the element, the display styles of 
 * all other elements be temporarily saved and populated into
 * func {@link #checkOccurrences} to ensure that the
 * Media Manager content(s) container always adapt to the show/hide
 * display status of the elements. 
 * <br />
 * In addition to that we perform conditions to distinguish which 
 * layer (<em>myLocal</em>, <em>myFlickr</em>) is active.
 * 
 * @see #checkOccurrences
 * @param {string} elem Current element
 * @throws applyError on exception
 */
function Mediamanager_showElement (elem)
{	
	try {
		this.elem = elem;
		this.attr = 'class';
		this.ttarget = Helper.getAttrParentNode(this.attr, this.elem, 2) + '_wrap';
		
		Element.show(this.ttarget);

		var myLocal = Element.getStyle(this.lyMediamanagerMyLocal, 'display');
		var myFlickr = Element.getStyle(this.lyMediamanagerMyFlickr, 'display');

		//init
		var collectElems;
		var rows;
		
		// myLocal
		if (myLocal == 'block') {
			this.elem.className = this.mediamanagerClassHideMyLocal;
			
			var includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
			var tagsElem = Element.getStyle('mm_tags_wrap', 'display');
			var currentTagsElem = Element.getStyle('mm_current_tags_wrap', 'display');
			var timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');
			
			collectElems = String(includeTypesElem + tagsElem + currentTagsElem + timeframeElem);
			
			// give option 'Include Types' a little more space since we have two rows here
			if (includeTypesElem == 'block') {
				rows = 1;
			}
		}
		// myFlickr
		else if (myFlickr == 'block') {
			this.elem.className = this.mediamanagerClassHideMyFlickr;
			
			var userElem = Element.getStyle('mm_user_wrap', 'display');
			var flickrtagsElem = Element.getStyle('mm_flickrtags_wrap', 'display');
			var photosetElem = Element.getStyle('mm_photoset_wrap', 'display');
			
			collectElems = String(userElem + flickrtagsElem + photosetElem);
			
			// do nothing, not needed here
			rows = '';
		}
		Element.update(this.elem, this.elementHtmlHide);
		Behaviour.reapply('.' + this.elem.className);
		
		Mediamanager.checkOccurrences(collectElems, rows);
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Hide Media Manager element.
 * <br />
 * Beside simply hiding the element, the display styles of 
 * all other elements be temporarily saved and populated into
 * func {@link #checkOccurrences} to ensure that the
 * Media Manager content(s) container always adapt to the show/hide
 * display status of the elements.
 * <br />
 * In addition to that we perform conditions to distinguish which 
 * layer (<em>myLocal</em>, <em>myFlickr</em>) is active.
 *
 * @see #checkOccurrences
 * @param {string} elem Current element
 * @throws applyError on exception
 */
function Mediamanager_hideElement (elem)
{
	try {
		this.elem = elem;
		this.attr = 'class';
		this.ttarget = Helper.getAttrParentNode(this.attr, this.elem, 2) + '_wrap';

		Element.hide(this.ttarget);

		var myLocal = Element.getStyle(this.lyMediamanagerMyLocal, 'display');
		var myFlickr = Element.getStyle(this.lyMediamanagerMyFlickr, 'display');

		// init
		var collectElems;
		var rows;
		
		// myLocal
		if (myLocal == 'block') {
			this.elem.className = this.mediamanagerClassShowMyLocal;			
			var includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
			var tagsElem = Element.getStyle('mm_tags_wrap', 'display');
			var currentTagsElem = Element.getStyle('mm_current_tags_wrap', 'display');
			var timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');			
			collectElems = String(includeTypesElem + tagsElem + timeframeElem);
			
			// give option 'Include Types' a little more space since we have two rows here
			if (includeTypesElem == 'block') {
				rows = 1;
			}
		}
		// myFlickr
		else if (myFlickr == 'block') {
			this.elem.className = this.mediamanagerClassShowMyFlickr;			
			var userElem = Element.getStyle('mm_user_wrap', 'display');
			var flickrtagsElem = Element.getStyle('mm_flickrtags_wrap', 'display');
			var photosetElem = Element.getStyle('mm_photoset_wrap', 'display');			
			collectElems = String(userElem + flickrtagsElem + photosetElem);
			
			// do nothing, not needed here
			rows = '';
		}
		
		Element.update(this.elem, this.elementHtmlShow);
		Behaviour.reapply('.' + this.elem.className);
		
		Mediamanager.checkOccurrences(collectElems, rows);
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Simply switch layer (<em>myLocal</em>, <em>myFlickr</em>).
 *
 * @param {string} toShow Layer to display
 * @param {string} toHide Layer to hide
 * @throws applyError on exception
 */
function Mediamanager_switchLayer (toShow, toHide)
{
	try {
		this.toShow = $(toShow);
		this.toHide = $(toHide);
	
		Element.hide(this.toHide);
		Effect.Appear(this.toShow,{duration: 0.4});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Adjust height of Media Manager content(s) div.
 * <br />
 * Evaluates the params (array) and perfoms a switch condition
 * with array length index as the attribute. Used everytime there
 * is a need for a asynchron refresh launched within other functions.
 *
 * @see #showElement
 * @see #hideElement
 * @see #setCurrentElementStatusMyLocal
 * @see #setCurrentElementStatusMyFlickr
 * @param {string} elem Current element
 * @param {string} row Track if we need to provide extra rows, see {@link #hideElement} and {@link #showElement}
 * @throws applyError on exception
 */
function Mediamanager_checkOccurrences (elems, row)
{
	try {				
		var myLocal = Element.getStyle('lyMediamanagerMyLocal', 'display');
		var prefix;
		if (myLocal == 'block') {
			prefix = 'myLocal_';
		} else {
			prefix = 'myFlickr_';
		}
		
		// init
		var cHeight;
		var pHeight;
		
		var res = elems.match(/block/gi);
				
		if (!res) {
			Element.setStyle(prefix + 'mm_content', {height: '412px'});
			Element.setStyle(prefix + 'mm_contentToPopulate', {height: '409px'});
			countItems = 8;
		} else {
			switch (res.length) {
				case 1 :
						if (Mediamanager.isNumber(row) === true) {
							cHeight = '371px';
							pHeight = '368px';
							countItems = 7;
						} else {
							cHeight = '392px';
							pHeight = '389px';
							countItems = 8;
						}
					break;
				case 2 :
						if (Mediamanager.isNumber(row) === true) {
							cHeight = '351px';
							pHeight = '348px';
							countItems = 7;
						} else {
							cHeight = '372px';
							pHeight = '369px';
							countItems = 7;
						}
					break;
				case 3 :
						cHeight = '331px';
						pHeight = '328px';
						countItems = 6;
					break;		
			}
			Element.setStyle(prefix + 'mm_content', {height: cHeight});
			Element.setStyle(prefix + 'mm_contentToPopulate', {height: pHeight});
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Reapply event handler.
 * <br />
 * Because we often need to refresh the Media Manager, we reapply
 * the relevant event handler on class/id basis.
 *
 * @throws applyError on exception
 */
function Mediamanager_applyBehaviour ()
{
	try {
		Behaviour.reapply('input');
		Behaviour.reapply('a.mm_edit');
		Behaviour.reapply('a.mm_insert');
		Behaviour.reapply('a.mm_insertFlickr');
		Behaviour.reapply('a.mm_upload');
		Behaviour.reapply('a.mm_delete');
		Behaviour.reapply('a.mm_cast');
		Behaviour.reapply('a.pager');
		Behaviour.reapply('a.pager_myFlickr');
		Behaviour.reapply('a.mm_myLocal');
		Behaviour.reapply('a.mm_myFlickr');
		Behaviour.reapply('#mm_include_types_wrap');
		Behaviour.reapply('#mm_current_tags_wrap');
		Behaviour.reapply('#mm_timeframe');
		Behaviour.reapply('#mm_user');
		Behaviour.reapply('#mm_photoset');
		Behaviour.reapply('#mm_flickrtags');
		Behaviour.reapply('#submit55');
		Behaviour.reapply('.submit140');
		Behaviour.reapply('.submit200');
		Behaviour.reapply('.submit240');
		Behaviour.reapply('.showMediamanagerElementMyLocal');
		Behaviour.reapply('.hideMediamanagerElementMyLocal');
		Behaviour.reapply('.showMediamanagerElementMyFlickr');
		Behaviour.reapply('.hideMediamanagerElementMyFlickr');
		Behaviour.reapply('.iHelpMediamanager');
		Behaviour.reapply('.iHelpRemoveMediamanager');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Collects the media manager form elements values as a hash
 * and returns it as a query string.
 * <br />
 * var <em>mm_limit</em> is filled within {@link #checkOccurrences}.
 * <br />
 * var <em>mm_pagetype</em> comes from definition in the
 * html markup. Distinguish to show/hide action icon <em>UseAsPodcast</em>.
 * 
 * @see #invokeInputs
 * @see #invokePager
 * @see #invokeTags
 * @see #checkOccurrences
 * @throws applyError on exception
 */
function Mediamanager_checkElemsMyLocal ()
{
	try {
		if (typeof countItems == 'undefined') {
			// initialize global with 'save' display
			countItems = 0;
		}
		
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
		return o.toQueryString();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Save elements display status.
 * <br />
 * This method is used everytime there is a XMLHttpRequest Call.
 * Counterpart of {@link #setCurrentElementStatusMyLocal}.
 *
 * @see #setCurrentElementStatusMyLocal
 * @throws applyError on exception
 */
function Mediamanager_preserveElementStatusMyLocal ()
{	
	try {	
		var current_includeTypesElem = Element.getStyle('mm_include_types_wrap', 'display');
		var current_tagsElem = Element.getStyle('mm_tags_wrap', 'display');
		var current_currentTagsElem = Element.getStyle('mm_current_tags_wrap', 'display');
		var current_timeframeElem = Element.getStyle('mm_timeframe_wrap', 'display');
	
		// make global -> use in func setCurrentElementStatusMyLocal
		previousElemsStatus = new Array (current_includeTypesElem, current_tagsElem, current_currentTagsElem, current_timeframeElem);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Sets elements display status.
 * <br />
 * This method is used everytime there is a XMLHttpRequest Response.
 * It takes the received values from Counterpart method {@link #preserveElementStatusMyLocal}
 * to reconstitute the Media Manager Elements display.
 *
 * @see #preserveElementStatusMyLocal
 * @see #checkOccurrences
 * @throws applyError on exception
 */
function Mediamanager_setCurrentElementStatusMyLocal ()
{	
	try {		
		Element.setStyle('mm_include_types_wrap', {display: previousElemsStatus[0]});
		Element.setStyle('mm_tags_wrap', {display: previousElemsStatus[1]});
		Element.setStyle('mm_current_tags_wrap', {display: previousElemsStatus[2]});
		Element.setStyle('mm_timeframe_wrap', {display: previousElemsStatus[3]});
		
		collectElems = String(previousElemsStatus[0] + previousElemsStatus[1] + previousElemsStatus[2] + previousElemsStatus[3]);

		// give option 'Include Types' a little more space since we have two rows here
		if (previousElemsStatus[0] == 'block') {
			var rows = 1;
		}
		// set appropriate height and width of surrounding divs
		Mediamanager.checkOccurrences(collectElems, rows);
				
		// get all relevant spans
		var parentElem = $$('#lyMediamanagerMyLocal .bez');
		
		// corresponding DOM chang on class Names
		if (previousElemsStatus[0] == 'block') {
			parentElem[0].lastChild.className = this.mediamanagerClassHideMyLocal;
			parentElem[0].lastChild.innerHTML = this.elementHtmlHide;
		}
		if (previousElemsStatus[1] == 'none') {
			parentElem[1].lastChild.className = this.mediamanagerClassShowMyLocal;
			parentElem[1].lastChild.innerHTML = this.elementHtmlShow;
		}
		if (previousElemsStatus[2] == 'block') {
			parentElem[2].lastChild.className = this.mediamanagerClassHideMyLocal;
			parentElem[2].lastChild.innerHTML = this.elementHtmlHide;
		}
		if (previousElemsStatus[3] == 'block') {
			parentElem[3].lastChild.className = this.mediamanagerClassHideMyLocal;
			parentElem[3].lastChild.innerHTML = this.elementHtmlHide;
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Toggle podcast show/hide podast details.
 *
 * @param {var} elem Current elem to toggle 
 * @throws applyError on exception
 */
function Mediamanager_toggleExtendedView (elem)
{
	try {
		if (elem.value == showDetails) {
			elem.value = hideDetails;
			$('podcast_details_display').value = '1';
			Effect.Appear('extendedView',{duration: 0.4});
		} else {
			elem.value = showDetails;
			$('podcast_details_display').value = '';
			Effect.Fade('extendedView',{duration: 0.4});
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Get element id and populate podcast layer.
 *
 * @see #loaderMediaToPodcast
 * @see #showResponseMediaToPodcast
 * @param {string} elem element (id) to process
 * @throws applyError on exception
 */
function Mediamanager_mediaToPodcast (elem)
{
	try {
		this.toShow = $('podcast_container');
		
		Element.show(this.toShow);
		Element.scrollTo(this.toShow);
		
		// set hidden field value
		$('podcast_media_object').value = elem.id;

		var url = this.parseMedCastsPath;
		var pars = 'id=' + elem.id;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMediaToPodcast,
				parameters : pars,
				onComplete : Mediamanager.showResponseMediaToPodcast
			});	
			
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate podcast layer via given hidden value.
 * <br />
 * Used if the podcast layer is formerly populated and we get
 * into form errors on submit of page.
 *
 * @see #loaderMediaToPodcast
 * @see #showResponseMediaToPodcast
 * @param {string} elem element (id) to process
 * @throws applyError on exception
 */
function Mediamanager_mediaToPodcastOnLoad ()
{
	try {
		this.toShow = $('podcast_container');
		
		Element.show(this.toShow);

		// get hidden field value
		var podcast_media_object = $('podcast_media_object').value;

		var url = this.parseMedCastsPath;
		var pars = 'id=' + podcast_media_object;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMediaToPodcast,
				parameters : pars,
				onComplete : Mediamanager.showResponseMediaToPodcast
			});	
			
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Discard podcast media item from page.
 *
 * @see #loaderMediaToPodcast
 * @see #showResponseDiscardPodcast
 * @param {var} elem Current elem to delete
 * @throws applyError on exception
 */
function Mediamanager_discardPodcast (elem)
{
	try {
		// get hidden field value
		var podcast_id = $('podcast_id').value;

		var url = this.parseMedDiscCastsPath;
		var pars = 'id=' + podcast_id;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMediaToPodcast,
				parameters : pars,
				onComplete : Mediamanager.showResponseDiscardPodcast
			});	
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest response.
 *
 * @see #mediaToPodcast
 * @see #mediaToPodcastOnLoad
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Mediamanager_showResponseMediaToPodcast(req)
{
	try {		
		$('mediafile_container').innerHTML = req.responseText;
			
		// dont be able to use appear here, because
		// Effect.Opacity don't use display: block
		Element.hide('indicatorPodcast');
		Element.show('podcast_container_loader');
		
		if ($('podcast_details_display').value == 1) {
			document.getElementsByName('toggleExtendedView')[0].value = hideDetails;
			$('extendedView').style.display = 'block';
		}		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest response.
 *
 * @see #discardPodcast
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Mediamanager_showResponseDiscardPodcast(req)
{
	try {
		// set hidden field value
		$('podcast_id').value = '';
		$('podcast_media_object').value = '';	
	
		Effect.Fade('podcast_container',{duration: 0.4});
					
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display indicator while XMLHttpRequest processing.
 *
 * @see #mediaToPodcast
 * @see #mediaToPodcastOnLoad
 * @see #discardPodcast
 * @throws applyError on exception
 */
function Mediamanager_loaderMediaToPodcast ()
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
 * Initialize tag search.
 * <br />
 * Call {@link #invokeTags} with a delay of 1 second.
 * <br />
 * To avoid firing {@link #invokeTags} on every single keyboard
 * stroke, we have to deal with the var keyPressDelay.
 * 
 * @see #invokeTags
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
			this.keyPressDelay = window.setTimeout("Mediamanager.invokeTags()", 1000);
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display media items related to selected option(s).
 * <br />
 * First of all we preserve the current display element status,
 * then fill var <em>elems</em> with value hash from {@link #checkElemsMyLocal}.
 * 
 * @see #preserveElementStatusMyLocal
 * @see #checkElemsMyLocal
 * @see #loaderMyLocal
 * @see #showResponseInvokeInputs
 * @throws applyError on exception
 */
function Mediamanager_invokeInputs ()
{
	try {
		Mediamanager.preserveElementStatusMyLocal();
		
		var elems = Mediamanager.checkElemsMyLocal();
		var url = this.parseMedLocalPath;
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMyLocal,
				parameters : pars,
				onComplete : Mediamanager.showResponseInvokeInputs
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display media items related to tag(s) input.
 * <br />
 * First of all we preserve the current display element status,
 * then fill var <em>elems</em> with value hash from {@link #checkElemsMyLocal}.
 * 
 * @see #initializeTagSearch
 * @see #preserveElementStatusMyLocal
 * @see #checkElemsMyLocal
 * @see #loaderMyLocal
 * @see #showResponseInvokeTagInputs
 * @throws applyError on exception
 */
function Mediamanager_invokeTags ()
{
	try {
		Mediamanager.preserveElementStatusMyLocal();
		
		var elems = Mediamanager.checkElemsMyLocal();
		var url = this.parseMedLocalPath;
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMyLocal,
				parameters : pars,
				onComplete : Mediamanager.showResponseInvokeTagInputs
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Initialize pager.
 * <br />
 * First of all we preserve the current display element status,
 * then fill var <em>elems</em> with value hash from {@link #checkElemsMyLocal}.
 * The function attribute <em>pager_page</em> is only needed (and used), when its 
 * called within a popup to close ({@link Helper#closePopup}). That because we
 * want to get back on exact the pager page on which we started the popup.
 * 
 * @see #preserveElementStatusMyLocal
 * @see #checkElemsMyLocal
 * @see #loaderMyLocal
 * @see #showResponseInvokeInputs
 * @see Helper#closePopup
 * @param {object} elem Current element to process
 * @param {string} pager_page Saved pager page count
 * @throws applyError on exception
 */
function Mediamanager_invokePager (elem, pager_page)
{
	try {
		Mediamanager.preserveElementStatusMyLocal();
		
		var pars = '';
		var elems = Mediamanager.checkElemsMyLocal();
		var url = this.parseMedLocalPath;
		if (typeof pager_page != 'undefined') {
			pars = 'mm_start=' + pager_page + '&' + elems;
		} else {
			pars = 'mm_start=' + elem.id + '&' + elems;
		}

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMyLocal,
				parameters : pars,
				onComplete : Mediamanager.showResponseInvokeInputs
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest response.
 * <br />
 * Re-observe the tag search on both layers (<em>myLocal</em>, <em>myFlickr</em>).
 *
 * @see #setCurrentElementStatusMyLocal
 * @see #initializeTagSearch
 * @see #initializeTagSearchMyFlickr
 * @see #invokeInputs
 * @see #invokePager
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Mediamanager_showResponseInvokeInputs(req)
{
	try {
		$('column').innerHTML = req.responseText;
		
		Mediamanager.setCurrentElementStatusMyLocal();
		
		Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		Event.observe($('mm_flickrtags'), 'keyup', Mediamanager.initializeTagSearchMyFlickr);

		// 30.11.10 disabled temporarily because i have no clue why this is set
		//$('hiddenFocus').focus();
		
		Mediamanager.applyBehaviour();

	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest response.
 * <br />
 * Re-observe the tag search on both layers (<em>myLocal</em>, <em>myFlickr</em>).
 *
 * @see #setCurrentElementStatusMyLocal
 * @see #initializeTagSearch
 * @see #initializeTagSearchMyFlickr
 * @see #invokeTags
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Mediamanager_showResponseInvokeTagInputs(req)
{
	try {	
		$('column').innerHTML = req.responseText;
		
		// refering to https://bugzilla.mozilla.org/show_bug.cgi?id=236791
		$('mm_tags').setAttribute("autocomplete","off");
		$('mm_flickrtags').setAttribute("autocomplete","off");
		
		Mediamanager.setCurrentElementStatusMyLocal();
		
		Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		Event.observe($('mm_flickrtags'), 'keyup', Mediamanager.initializeTagSearchMyFlickr);
		
		Forms.setOnEvent($('mm_tags'), '','#ff620d','dotted');	
		$('mm_tags').focus();
			
		Mediamanager.applyBehaviour();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display indicator while XMLHttpRequest processing.
 *
 * @see #invokeInputs
 * @see #invokePager
 * @see #invokeTags
 * @throws applyError on exception
 */
function Mediamanager_loaderMyLocal ()
{
	try {
		var hideContentTable = $$('.mm_content').first();
		Element.hide(hideContentTable);
		Element.show('indicator_local');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Delete media item.
 * <br />
 * When deleting completed {@link #invokeInputs} is called.
 * 
 * @see #loaderMyLocal
 * @see #invokeInputs
 * @param {var} elem Current elem to delete
 * @throws applyError on exception
 */
function Mediamanager_deleteMediaItem (elem)
{
	try {
		var url = this.parseMedDeletePath;
		var pars = 'id=' + elem.id;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMyLocal,
				parameters : pars,
				onComplete : function () {Mediamanager.invokeInputs();}
			});		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Process and handle media callbacks.
 * <br />
 * Depending on the delivered mime_type we need to launch a popup or 
 * simple fire a ajax.request to return the wcom_plugin callbacks.
 * 
 * @see #showResponseProcessMediaCallbacks
 * @param {string} elem Current elem to process
 * @throws applyError on exception
 */
function Mediamanager_processMediaCallbacks (elem)
{
	try {
		// properties
		this.elem = elem;
		this.elName = this.elem.name;
		this.popup = false;
		this.targetWidth = this.callbacksPopupWindowWidth745;
		this.targetHeight = this.callbacksPopupWindowHeight664;
		this.targetName = 'mm_' + this.elName;
		
		/*
		* This array is the only var to populate
		* if the mime_type requires a popup window
		*/ 
		var mime_types = new Array ('image','application-x-shockwave-flash');
	
		// execute popup bool
		for (var i = 0; i < mime_types.length; i++) {
			if(mime_types[i] == this.elName) {
				this.popup = true;
			}
		}
					
		// process the callbacks	
		if (typeof stored_focus == 'undefined') {
			alert(selectTextarea); 
		} else {
			form_target = stored_focus;
			
			// exclude form textarea targets which does not include text_converter processing
			if (form_target == 'blog_posting_feed_summary' || form_target == 'blog_posting_tags') {
				alert(alertOnExcludedTxtareas);
			} else {
			
				// grab enviroment variables 	
				// hash the returned variables
				var getElems = {
					id : this.elem.id,
					text : Helper.getSelectionText(),
					text_converter : Helper.getTextConverterValue(),
					form_target : form_target,
					pager_page : Helper.getPagerPage()
				};
				var o = $H(getElems);
				var reqString = o.toQueryString();
			
				if (this.popup) {
					Helper.lowerOpacity();				
					this.url = this.parseMedCallbacksFilePath + this.elem.name + '.php' + '?' + reqString;
					this.targetUrl = this.url;
					this.target = window.open(this.targetUrl, this.targetName, 
							"scrollbars=yes,width="+this.targetWidth+",height="+this.targetHeight+"");
					this.resWidth = Helper.defineWindowX(this.targetWidth);
					this.resHeight = Helper.defineWindowY();
		
					this.target.moveBy(this.resWidth, this.resHeight);
					this.target.focus();
				} else {		
					this.url = this.parseMedCallbacksFilePath + this.elem.name + '.php';
					var url = this.url;
					var pars = reqString;
	
					var myAjax = new Ajax.Request(
						url,
						{
							method : 'post',
							parameters : pars,
							onComplete : Mediamanager.showResponseProcessMediaCallbacks
						});
				}
			}	
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest response.
 * <br />
 * Insert the callback result string.
 *
 * @see #processMediaCallbacks
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Mediamanager_showResponseProcessMediaCallbacks(req)
{
	try {
		Helper.insertTagsCallbacks(form_target, req.responseText);
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Process and handle media callbacks from a popup.
 * <br />
 * Return and insert the wcom_plugin callbacks.
 * 
 * @see Helper#insertTagsFromPopupCallbacks
 * @param {string} elem Current elem to process
 * @param {global} form_target Current saved form target
 * @param {global} callback_media_result Current result set
 * @throws applyError on exception
 */
/*function Mediamanager_processFromPopupMediaCallbacks (elem)
{
	try {
		Helper.insertTagsFromPopupCallbacks(form_target, callback_media_result);
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}*/

function Mediamanager_insertMediaCallbacks (elem)
{
	try {
		Helper.insertTagsFromPopupCallbacks(form_target, callback_media_result);
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Collects the media manager form elements values as a hash
 * and returns it as a query string.
 * <br />
 * var <em>mm_limit</em> is filled within {@link #checkOccurrences}.
 * <br />
 * var <em>mm_pagetype</em> comes from definition in the
 * html markup. Distinguish to show/hide action icon <em>UseAsPodcast</em>.
 *
 * @see #invokeInputsMyFlickr
 * @see #invokePagerMyFlickr
 * @see #invokeTagsMyFlickr
 * @see #checkOccurrences
 * @throws applyError on exception
 */
function Mediamanager_checkElemsMyFlickr ()
{
	try {
		if (typeof countItems == 'undefined') {
			// initialize global with 'save' display
			countItems = 0;
		}
		
		var getElems = {
			mm_user : $F('mm_user'),
			mm_flickrtags : $F('mm_flickrtags'),
			mm_photoset : $F('mm_photoset'),
			mm_limit : countItems,
			mm_pagetype : pagetype
		};
		var o = $H(getElems);
		return o.toQueryString();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Save elements display status.
 * <br />
 * This method is used everytime there is a XMLHttpRequest Call.
 * Counterpart of {@link #setCurrentElementStatusMyFlickr}.
 * 
 * @see #setCurrentElementStatusMyFlickr
 * @throws applyError on exception
 */
function Mediamanager_preserveElementStatusMyFlickr ()
{	
	try {	
		var current_userElem = Element.getStyle('mm_user_wrap', 'display');
		var current_photosetElem = Element.getStyle('mm_photoset_wrap', 'display');
		var current_flickrtagsElem = Element.getStyle('mm_flickrtags_wrap', 'display');	
		
		// make global -> use in func setCurrentElementStatusMyFlickr
		previousElemsStatus = new Array (current_userElem, current_photosetElem, current_flickrtagsElem);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Sets elements display status.
 * <br />
 * This method is used everytime there is a XMLHttpRequest Response.
 * It takes the received values from Counterpart method {@link #preserveElementStatusMyFlickr}
 * to reconstitute the Media Manager Elements display.
 *
 * @see #preserveElementStatusMyFlickr
 * @see #checkOccurrences
 * @throws applyError on exception
 */
function Mediamanager_setCurrentElementStatusMyFlickr ()
{	
	try {		
		Element.setStyle('mm_user_wrap', {display: previousElemsStatus[0]});
		Element.setStyle('mm_photoset_wrap', {display: previousElemsStatus[1]});
		Element.setStyle('mm_flickrtags_wrap', {display: previousElemsStatus[2]});
		
		collectElems = String(previousElemsStatus[0] + previousElemsStatus[1] + previousElemsStatus[2]);
		
		// do nothing on var
		var rows = '';
			
		// set appropriate height and width of surrounding divs
		Mediamanager.checkOccurrences(collectElems, rows);
				
		// get all relevant spans
		var parentElem = $('lyMediamanagerMyFlickr').getElementsByClassName('bez');
		
		// corresponding DOM chang on class Names
		if (previousElemsStatus[0] == 'none') {
			parentElem[0].lastChild.className = this.mediamanagerClassShowMyFlickr;
			parentElem[0].lastChild.innerHTML = this.elementHtmlShow;
		}
		if (previousElemsStatus[1] == 'block') {
			parentElem[1].lastChild.className = this.mediamanagerClassHideMyFlickr;
			parentElem[1].lastChild.innerHTML = this.elementHtmlHide;
		}
		if (previousElemsStatus[2] == 'block') {
			parentElem[2].lastChild.className = this.mediamanagerClassHideMyFlickr;
			parentElem[2].lastChild.innerHTML = this.elementHtmlHide;
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display media items related to tag(s) input.
 * <br />
 * First of all we preserve the current display element status,
 * then fill var <em>elems</em> with value hash from {@link #checkElemsMyFlickr}.
 *  
 * @see #preserveElementStatusMyFlickr
 * @see #checkElemsMyFlickr
 * @see #loaderMyFlickr
 * @see #showResponseInvokeTagsMyFlickr
 * @throws applyError on exception
 */
function Mediamanager_invokeTagsMyFlickr ()
{
	try {
		Mediamanager.preserveElementStatusMyFlickr();
		
		var elems = Mediamanager.checkElemsMyFlickr();
		var url = this.parseMedFlickrPath;
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMyFlickr,
				parameters : pars,
				onComplete : Mediamanager.showResponseInvokeTagsMyFlickr
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display media items related to selected option(s).
 * <br />
 * First of all we preserve the current display element status,
 * then fill var <em>elems</em> with value hash from {@link #checkElemsMyFlickr}.
 * 
 * @see #preserveElementStatusMyFlickr
 * @see #checkElemsMyFlickr
 * @see #loaderMyFlickr
 * @see #showResponseInvokeInputsMyFlickr
 * @throws applyError on exception
 */
function Mediamanager_invokeInputsMyFlickr ()
{
	try {
		Mediamanager.preserveElementStatusMyFlickr();
		
		var elems = Mediamanager.checkElemsMyFlickr();
		var url = this.parseMedFlickrPath;
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMyFlickr,
				parameters : pars,
				onComplete : Mediamanager.showResponseInvokeInputsMyFlickr
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Initialize pager.
 * <br />
 * First of all we preserve the current display element status,
 * then fill var <em>elems</em> with value hash from {@link #checkElemsMyFlickr}.
 * 
 * @see #preserveElementStatusMyFlickr
 * @see #checkElemsMyFlickr
 * @see #loaderMyFlickr
 * @see #showResponseInvokeInputsMyFlickr
 * @param {object} elem Current element to process
 * @throws applyError on exception
 */
function Mediamanager_invokePagerMyFlickr (elem)
{
	try {
		Mediamanager.preserveElementStatusMyFlickr();
		
		var elems = Mediamanager.checkElemsMyFlickr();
		var url = this.parseMedFlickrPath;
		var pars = 'mm_start=' + elem.id + '&' + elems;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Mediamanager.loaderMyFlickr,
				parameters : pars,
				onComplete : Mediamanager.showResponseInvokeInputsMyFlickr
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Initialize tag search.
 * <br />
 * Call {@link #invokeTagsMyFlickr} with a delay of 1 second.
 * <br />
 * To avoid firing {@link #invokeTagsMyFlickr} on every single keyboard
 * stroke, we have to deal with the var keyPressDelay.
 * 
 * @see #invokeTagsMyFlickr
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
			this.keyPressDelay = window.setTimeout("Mediamanager.invokeTagsMyFlickr()", 1000);
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Prepare display with given user.
 * <br />
 * First of all we preserve the current display element status,
 * then fill var <em>elems</em> with value hash from {@link #checkElemsMyFlickr}.
 * 
 * @see #preserveElementStatusMyFlickr
 * @see #checkElemsMyFlickr
 * @see #loaderMyFlickr
 * @see #showResponseInvokeInputsMyFlickr
 * @throws applyError on exception
 */
function Mediamanager_initializeUserMyFlickr ()
{
	try {
		Mediamanager.preserveElementStatusMyFlickr();
		
		var elems = Mediamanager.checkElemsMyFlickr();
		var url = this.parseMedFlickrPath;
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onComplete : Mediamanager.showResponseInvokeInputsMyFlickr
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest response.
 * <br />
 * Re-observe the tag search on both layers (<em>myLocal</em>, <em>myFlickr</em>).
 *
 * @see #setCurrentElementStatusMyFlickr
 * @see #initializeTagSearch
 * @see #initializeTagSearchMyFlickr
 * @see #initializeUserMyFlickr
 * @see #invokePagerMyFlickr
 * @see #invokeInputsMyFlickr
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Mediamanager_showResponseInvokeInputsMyFlickr(req)
{
	try {
		$('column').innerHTML = req.responseText;
		Element.show('lyMediamanagerMyFlickr');
		Element.hide('lyMediamanagerMyLocal');
		
		// show option inputs
		var mm_flickrtags = $$('.mm_flickrtags');
		var mm_photoset = $$('.mm_photoset');
		Element.setStyle(mm_flickrtags[0], {visibility: 'visible'});
		Element.setStyle(mm_photoset[0], {visibility: 'visible'});	
		
		Mediamanager.setCurrentElementStatusMyFlickr();
								
		Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		Event.observe($('mm_flickrtags'), 'keyup', Mediamanager.initializeTagSearchMyFlickr);
		
		$('hiddenFocus').focus();
		
		Mediamanager.applyBehaviour();	

	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest response.
 * <br />
 * Re-observe the tag search on both layers (<em>myLocal</em>, <em>myFlickr</em>).
 *
 * @see #setCurrentElementStatusMyFlickr
 * @see #initializeTagSearch
 * @see #initializeTagSearchMyFlickr
 * @see #invokeInputsMyFlickr
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Mediamanager_showResponseInvokeTagsMyFlickr(req)
{
	try {
		$('column').innerHTML = req.responseText;
		Element.show('lyMediamanagerMyFlickr');
		Element.hide('lyMediamanagerMyLocal');
		
		// show option inputs
		var mm_flickrtags = $$('.mm_flickrtags');
		var mm_photoset = $$('.mm_photoset');
		Element.setStyle(mm_flickrtags[0], {visibility: 'visible'});
		Element.setStyle(mm_photoset[0], {visibility: 'visible'});
		
		// refering to https://bugzilla.mozilla.org/show_bug.cgi?id=236791
		$('mm_tags').setAttribute("autocomplete","off");
		$('mm_flickrtags').setAttribute("autocomplete","off");
		
		Mediamanager.setCurrentElementStatusMyFlickr();
								
		Event.observe($('mm_tags'), 'keyup', Mediamanager.initializeTagSearch);
		Event.observe($('mm_flickrtags'), 'keyup', Mediamanager.initializeTagSearchMyFlickr);
		
		Forms.setOnEvent($('mm_flickrtags'), '','#ff620d','dotted');	
		$('mm_flickrtags').focus();
		
		Mediamanager.applyBehaviour();

	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display indicator while XMLHttpRequest processing.
 *
 * @see #invokeInputsMyFlickr
 * @see #invokePagerMyFlickr
 * @see #invokeTagsMyFlickr
 * @throws applyError on exception
 */
function Mediamanager_loaderMyFlickr ()
{
	try {
		var hideContentTable = $$('.mm_content')[1];
		Element.hide(hideContentTable);
		Element.show('indicator_flickr');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Insert image media item into content form field.
 * <br />
 * First we have a look, if global var <em>stored_focus</em> ({@link Forms#storeFocus})
 * is defined. Then we build a string with the needed syntax to deliver it to {@link Helper#insertTags}.
 * 
 * @see Forms#storeFocus
 * @see Helper#insertTags
 * @throws applyError on exception
 */
function Mediamanager_processMediaCallbacksFlickr (elem)
{
	try {
		// properties
		this.elem = elem;
		this.elName = this.elem.name;
		
		// process the callbacks
		if (typeof stored_focus == 'undefined') {
			alert(selectTextarea); 
		} else {
			form_target = stored_focus;
			
			// collect values
			var identify = this.elem.parentNode.parentNode;
			
			// get sizes
			var sel_select = identify.getElementsByTagName('select')[0];			
			var sel_select_value = sel_select.options[sel_select.selectedIndex].value;
			
			// URL to flickr photo page
			var sel_url_photo_page = identify.getElementsByTagName('input')[0].value;
					
			// build strings 			
			// preview URL
			var source = this.elem.firstChild.src;
			
			// get rid off size suffix
			var splitSource = source.split('_s');
			
			// hash the returned variables
			var getElems = {
				text : Helper.getSelectionText(),
				text_converter : Helper.getTextConverterValue(),
				src : splitSource[0] + sel_select_value + splitSource[1],
				href : sel_url_photo_page
				
			};
			var o = $H(getElems);
			var reqString = o.toQueryString();
			
			// ensure selection has used
			if (sel_select_value != 1) {
		
				this.url = this.parseMedCallbacksFilePath + this.elem.name + '.php';
				var url = this.url;
				var pars = reqString;
	
				var myAjax = new Ajax.Request(
					url,
					{
						method : 'post',
						parameters : pars,
						onComplete : Mediamanager.showResponseProcessMediaCallbacksFlickr
					});
				
			 } else {
				alert (alertOnSelectImageSize);
			}
		}
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest response.
 * <br />
 * Insert the callback result string.
 *
 * @see #processMediaCallbacks
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Mediamanager_showResponseProcessMediaCallbacksFlickr (req)
{
	try {
		Helper.insertTagsCallbacks(form_target, req.responseText);
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new object instance of class Mediamanager
 */
Mediamanager = new Mediamanager();
