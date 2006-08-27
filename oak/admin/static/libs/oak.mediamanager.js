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
Mediamanager.prototype.hideModal = Mediamanager_hideModal;
Mediamanager.prototype.setHide = Mediamanager_setHide;

Mediamanager.prototype.invokeInputs = Mediamanager_invokeInputs;
Mediamanager.prototype.checkMyLocalElems = Mediamanager_checkMyLocalElems;

Mediamanager.prototype.uploadMedia = Mediamanager_uploadMedia;
Mediamanager.prototype.processUploadMedia = Mediamanager_processUploadMedia;

Mediamanager.prototype.lowerOpacity = Mediamanager_lowerOpacity;

Mediamanager.prototype.submitAjaxForm = Mediamanager_submitAjaxForm;
Mediamanager.prototype.checkSubmittedFormElems = Mediamanager_checkSubmittedFormElems;


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
			Element.setStyle(prefix + 'mm_content', {height: '385px'});
			Element.setStyle(prefix + 'mm_contentToPopulate', {height: '376px'});
		} else {
			switch (res.length) {
				case 1 :
						if (exception) {
							var cHeight = '344px';
							var pHeight = '335px';
						} else {
							var cHeight = '364px';
							var pHeight = '355px';
						}
						Element.setStyle(prefix + 'mm_content', {height: cHeight});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: pHeight});
					break;
				case 2 :
						if (exception) {
							var cHeight = '323px';
							var pHeight = '314px';
						} else {
							var cHeight = '343px';
							var pHeight = '334px';
						}
						Element.setStyle(prefix + 'mm_content', {height: cHeight});
						Element.setStyle(prefix + 'mm_contentToPopulate', {height: pHeight});
					break;
				case 3 :
						if (exception) {
							var cHeight = '302px';
							var pHeight = '293px';
						} else {
							var cHeight = '322px';
							var pHeight = '313px';
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
 *
 * @throws applyError on exception
 */
function Mediamanager_setHide (elem)
{
	try {
		// properties
		this.elem = elem;
		
		Element.hide($(this.elem));
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Mediamanager
 *
 * @throws applyError on exception
 */
function Mediamanager_hideModal ()
{
	try {
		// properties		
		this.modalWindow = $('mm_modalContainer');
		this.lyLowerOpacity = 'lyLowerOpacity';
		
		if (Mediamanager.unsupportsEffects()) {
			Element.hide(this.modalWindow);
		} else {
			Effect.Fade(this.modalWindow,{duration: 0.7});
		}

		setTimeout("Mediamanager.setHide('"+ this.lyLowerOpacity +"')", 900);

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

		if (Mediamanager.unsupportsEffects()) {
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
function Mediamanager_invokeInputs ()
{
	try {
		var elems = Mediamanager.checkMyLocalElems();
		var url = '../mediamanager.ajax.php';
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onComplete : showResponseInvokeInputs
			});	
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
function showResponseInvokeInputs(req)
{
	try {
		$('myLocal_mm_contentToPopulate').innerHTML = req.responseText;
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
			mm_timeframe : $F('mm_timeframe')
		};
	
		var o = $H(getElems);
		return o.toQueryString();
	} catch (e) {
		_applyError(e);
	}
}




function Mediamanager_uploadMedia ()
{
	try {
		// properties
		this.url = '../mediamanager/mediamanager_upload.php';
		this.ttarget = $('mm_modalContainer');
		
		Mediamanager.lowerOpacity();
		
		if (typeof this.req != 'undefined') {
		
			var _url		= this.url;
			var _ttarget	= this.ttarget;
		
			_req.open('GET', _url, true);
			_req.onreadystatechange = function () { Mediamanager.processUploadMedia(_url,_ttarget);};
			_req.send('');
		}
	} catch (e) {
		_applyError(e);
	}
}



function Mediamanager_processUploadMedia (url, ttarget)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {				
				Element.update (ttarget, _req.responseText);
				if (Mediamanager.unsupportsEffects('safari')) {
					Element.show(ttarget);
				} else {
					Element.hide(this.ttarget);
					Effect.Appear(this.ttarget,{duration: 0.7});
				}
				Behaviour.apply();
			} else {
	  			throw new DevError(_req.statusText);
			}
		}
	} catch (e) {
		_applyError(e);
	}
}


function Mediamanager_lowerOpacity ()
{       
	try {
 		// properties
        this.cLeft = '0px';
        this.cTop = '0px';
        this.cPosition = 'absolute';
        this.cDisplay = 'block';
        this.imagePath = '../static/img/bg_overlay.png';
		this.lyContainer = $("container");   
        this.buildHeight = this.lyContainer.offsetHeight;
        this.buildWidth = this.lyContainer.offsetWidth;
		this.imageStr = '<img src="' + this.imagePath + '" width="' + this.buildWidth + '" height="' + this.buildHeight +'" alt="" />';
		this.ttarget_lower = $('lyLowerOpacity');

        if (this.ttarget_lower) {

		 	this.ttarget_lower.style.display = this.cDisplay;
			this.ttarget_lower.style.position = this.cPosition;
			this.ttarget_lower.style.top = this.cTop;
			this.ttarget_lower.style.left = this.cLeft;
			this.ttarget_lower.style.height = this.buildHeight + 'px';
			this.ttarget_lower.style.width = this.buildWidth + 'px';
			
			Element.update(this.ttarget_lower, this.imageStr);
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
function Mediamanager_submitAjaxForm ()
{
	try {
		var elems = Mediamanager.checkSubmittedFormElems();
		var url = '../mediamanager/mediamanager_upload.php';
		var pars = elems;
	
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onComplete : showResponseSubmitAjaxForm
			});	
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
function showResponseSubmitAjaxForm(req)
{
	try {
		// was gefüllt soll, ist noch offen, abhängig vom processing
		$('mm_modalBody').innerHTML = req.responseText;
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
function Mediamanager_checkSubmittedFormElems ()
{
	try {
		var getElems = {
			types : $F('types'),
			file : $F('file'),
			description : $F('description'),
			tags : $F('tags')
		};
	
		var o = $H(getElems);
		return o.toQueryString();
	} catch (e) {
		_applyError(e);
	}
}



/**
 * Building new instance for @class Mediamanager
 */
Mediamanager = new Mediamanager();
