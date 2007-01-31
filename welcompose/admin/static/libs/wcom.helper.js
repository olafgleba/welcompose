/**
 * Project: Welcompose
 * File: wcom.helper.js
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
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

/** 
 * @fileoverview Comprised javascript helper functions.
 * It contains functions that may be used application wide.
 */



/**
 * Constructs the Helper class
 * 
 * @class The Helper class defines a bunch of functions which doesn't 
 * belongs as regards content to just one class. Several functions be in use
 * within every Welcompose class. The scope is application wide.
 *
 * @constructor
 * @throws applyError on exception
 */
function Helper ()
{
	try {
		// no properties		
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Helper.prototype = new Base();


/**
 * Instance Methods from prototype @class Helper
 */
Helper.prototype.launchPopup = Helper_launchPopup;
Helper.prototype.launchPopupCallback = Helper_launchPopupCallback;
Helper.prototype.insertCallback = Helper_insertCallback;
Helper.prototype.getTextConverterValue = Helper_getTextConverterValue;
Helper.prototype.getSelectionText = Helper_getSelectionText;
Helper.prototype.closePopup = Helper_closePopup;
Helper.prototype.closeLinksPopup = Helper_closeLinksPopup;
Helper.prototype.closePopupTrack = Helper_closePopupTrack;
Helper.prototype.closePopupTrackNoAlert = Helper_closePopupTrackNoAlert;
Helper.prototype.lowerOpacity = Helper_lowerOpacity;
Helper.prototype.lowerOpacityOnUpload = Helper_lowerOpacityOnUpload;
Helper.prototype.isBrowser = Helper_isBrowser;
Helper.prototype.defineWindowX = Helper_defineWindowX;
Helper.prototype.defineWindowY = Helper_defineWindowY;
Helper.prototype.definePageY = Helper_definePageY;
Helper.prototype.showNextNode = Helper_showNextNode;
Helper.prototype.showNextNodeBoxes = Helper_showNextNodeBoxes;
Helper.prototype.loaderPagesLinks = Helper_loaderPagesLinks;
Helper.prototype.showResponsePagesSecondLinks = Helper_showResponsePagesSecondLinks;
Helper.prototype.showResponsePagesThirdLinks = Helper_showResponsePagesThirdLinks;
Helper.prototype.insertTagsFromPopup = Helper_insertTagsFromPopup;
Helper.prototype.insertTags = Helper_insertTags;
Helper.prototype.insertInternalLink = Helper_insertInternalLink;
Helper.prototype.insertInternalLinkNoHref = Helper_insertInternalLinkNoHref;
Helper.prototype.insertInternalLinkGlobalTemplates = Helper_insertInternalLinkGlobalTemplates;
Helper.prototype.insertInternalLinkGlobalFiles = Helper_insertInternalLinkGlobalFiles;
Helper.prototype.insertInternalLinkGlobalBoxes = Helper_insertInternalLinkGlobalBoxes;
Helper.prototype.insertInternalLinkBoxes = Helper_insertInternalLinkBoxes;
Helper.prototype.insertInternalLinkStructuralTemplates = Helper_insertInternalLinkStructuralTemplates;
Helper.prototype.showResponseStructuralTemplates = Helper_showResponseStructuralTemplates;
Helper.prototype.changeBlogCommentStatus = Helper_changeBlogCommentStatus;
Helper.prototype.loaderChangeBlogCommentStatus = Helper_loaderChangeBlogCommentStatus;
Helper.prototype.showResponseChangeBlogCommentStatus = Helper_showResponseChangeBlogCommentStatus;
Helper.prototype.showFileUploadMessage = Helper_showFileUploadMessage;
Helper.prototype.validate = Helper_validate;
Helper.prototype.confirmDelNavAction = Helper_confirmDelNavAction;
Helper.prototype.confirmDelTplTypeAction = Helper_confirmDelTplTypeAction;
Helper.prototype.confirmDelTplSetsAction = Helper_confirmDelTplSetsAction;
Helper.prototype.confirmDelTplGlobalAction = Helper_confirmDelTplGlobalAction;
Helper.prototype.confirmDelTplGlobalfileAction = Helper_confirmDelTplGlobalfileAction;
Helper.prototype.getDelimiterValue = Helper_getDelimiterValue;
Helper.prototype.getPagerPage = Helper_getPagerPage;
Helper.prototype.getAttrParentNode = Helper_getAttrParentNode;
Helper.prototype.getAttrParentNodeNextNode = Helper_getAttrParentNodeNextNode;
Helper.prototype.getAttr = Helper_getAttr;
Helper.prototype.getAttrNextSibling = Helper_getAttrNextSibling;
Helper.prototype.getNextSiblingFirstChild = Helper_getNextSiblingFirstChild;
Helper.prototype.getDataParentNode = Helper_getDataParentNode;
Helper.prototype.convertFieldValuesToValidUrl = Helper_convertFieldValuesToValidUrl;
Helper.prototype.URLify = Helper_URLify;


/**
 * Launch popup.
 * <br />
 * On the basis of parameter <em>trigger</em> we builds the url string
 * for later use in func <em>window.open</em>.
 * According to the popup launch, the parent window opacity will be lowered
 * to eye focus onto the launched window. 
 * 
 * <br /><br />Example:
 * <pre><code>
Helper.launchPopup('745','634','pages_links_select','pages_internal_links', this);
</code></pre>
 * 
 * @see #lowerOpacity
 * @param {string} width Width for the window to launch 
 * @param {string} height Height for the window to launch
 * @param {string} wname The Name for the window to launch
 * @param {string} trigger Switch case condition which url to use
 * @param {object} elem Current element
 * @throws applyError on exception
 */
function Helper_launchPopup (width, height, wname, trigger, elem)
{
	try {
		// properties
		this.elem = elem;
		this.trigger = trigger;
		
		Helper.lowerOpacity();
		
		switch (this.trigger) {
			case 'mm_upload' :
					Helper.getPagerPage();
					this.url = this.parseMedUploadUrl + '?pager_page=' + pager_page;
				break;
			case 'mm_edit' :
					Helper.getPagerPage();
					this.url = this.parseMedEditUrl + '?id=' + this.elem.id + '&pager_page=' + pager_page;
				break;
			case 'pages_internal_links' :
					this.url = this.parsePagesLinksUrl + '?target=' + this.elem.name;
				break;
			case 'pages_internal_links_NoHref' :
					this.url = this.parsePagesLinksUrl + '?target=' + this.elem.name + '&control=NoHref';
				break;
			case 'pages_boxes_internal_links' :
					this.url = this.parsePagesBoxesLinksUrl + '?target=' + this.elem.name;
				break;
			case 'globaltemplates_internal_links' :
					Helper.getDelimiterValue();
					this.url = this.parseGlobalTemplatesLinksUrl + '?target=' + this.elem.name + '&delimiter=' + delimiter;
				break;
			case 'globalfiles_internal_links' :
					Helper.getDelimiterValue();
					this.url = this.parseGlobalFilesLinksUrl + '?target=' + this.elem.name + '&delimiter=' + delimiter;
				break;
			case 'globalboxes_internal_links' :
					this.url = this.parseGlobalBoxesLinksUrl + '?target=' + this.elem.name;
				break;
			case 'structuraltemplates_internal_links' :
					this.url = this.parseStructuralTemplatesLinksUrl + '?target=' + this.elem.name;
				break;
		}
		// properties
		this.targetUrl = this.url;
		this.targetName = wname;
		this.targetWidth = width;
		this.targetHeight = height;
		this.target = window.open(this.targetUrl, this.targetName, 
				"scrollbars=yes,width="+this.targetWidth+",height="+this.targetHeight+"");
		this.resWidth = Helper.defineWindowX(this.targetWidth);
		this.resHeight = Helper.defineWindowY();
		
		this.target.moveBy(this.resWidth, this.resHeight);
		this.target.focus();
	} catch (e) {
		_applyError(e);
	}
}


function Helper_insertCallback (elem)
{
	try {
	/*
		// properties
		this.elem = elem;
		this.popup = true;
		
		var callback_types_without_popups = new Array (
			'document'
		);
		
		switch (this.elem.name) {
			case 'image' :
					Helper.getPagerPage();
					Helper.getTextConverterValue();
					Helper.getSelectionText();		
					this.url = this.parseMedCallbackInsertImageUrl + '?id=' + this.elem.id +
					 	'&text=' + text + 
						'&text_converter=' + text_converter + 
						'&pager_page=' + pager_page + 
						'&form_target=' + form_target;
				break;
			case 'document' :
					Helper.getPagerPage();
					this.url = this.parseMedCallbackInsertDocumentUrl + '?id=' + this.elem.id + 
						'&pager_page=' + pager_page;
				break;
		}
		*/
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Launch popup.
 * <br />
 * On the basis of parameter <em>trigger</em> we builds the url string
 * for later use in func <em>window.open</em>.
 * According to the popup launch, the parent window opacity will be lowered
 * to eye focus onto the launched window. 
 * 
 * <br /><br />Example:
 * <pre><code>
Helper.launchPopup('745','634','pages_links_select', this);
</code></pre>
 * 
 * @see #lowerOpacity
 * @param {string} width Width for the window to launch 
 * @param {string} height Height for the window to launch
 * @param {string} wname The Name for the window to launch
 * @param {object} elem Current element
 * @throws applyError on exception
 */
function Helper_launchPopupCallback (width, height, wname, elem)
{
	try {
		// properties
		this.elem = elem;
		
		if (typeof storedFocus == 'undefined') {
			alert(selectTextarea); 
		} else {
			form_target = storedFocus;
			Helper.lowerOpacity();
		
			switch (this.elem.name) {
				case 'image' :
						Helper.getTextConverterValue();
						Helper.getSelectionText();		
						this.url = this.parseMedCallbackInsertImageUrl + '?id=' + this.elem.id +
						 	'&text=' + text + 
							'&text_converter=' + text_converter + 
							'&form_target=' + form_target;
					break;
				case 'document' :
						Helper.getPagerPage();
						this.url = this.parseMedCallbackInsertDocumentUrl + '?id=' + this.elem.id + 
							'&pager_page=' + pager_page;
					break;
			}
			
			// properties
			this.targetUrl = this.url;
			this.targetName = wname;
			this.targetWidth = width;
			this.targetHeight = height;
			this.target = window.open(this.targetUrl, this.targetName, 
					"scrollbars=yes,width="+this.targetWidth+",height="+this.targetHeight+"");
			this.resWidth = Helper.defineWindowX(this.targetWidth);
			this.resHeight = Helper.defineWindowY();
		
			this.target.moveBy(this.resWidth, this.resHeight);
			this.target.focus();	
		}
	} catch (e) {
		_applyError(e);
	}
}

function Helper_getTextConverterValue ()
{
	try {		
		var form_name = Helper.getAttr('id', document.getElementsByClassName('botbg')[0]);
		var el_name = 'text_converter';
		var el_id = form_name + '_text_converter';
			
		if ($(el_id)) {
			text_converter = document.forms[form_name].elements[el_name].options.selectedIndex;
		} else {
			text_converter = '';
		}
	} catch (e) {
		_applyError(e);
	}
}

function Helper_getSelectionText()
{
	try {
		var txtarea = $(form_target);

		if(document.selection) {
			var theSelection = document.selection.createRange().text;
		} else if(txtarea.selectionStart || txtarea.selectionStart == '0') {
	 		var startPos = txtarea.selectionStart;
			var endPos = txtarea.selectionEnd;
			text = (txtarea.value).substring(startPos, endPos);
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Close popup and refresh Media Manager contents.
 * <br />
 * In addition to close the popup, we invoke functions in the parent window,
 * like revoke the lowering of opacity and refresh the Media Manager to reflect
 * the modified contents.
 * <br />
 * Used in popups where it is expected to close the window with a button.
 * 
 * @see Mediamanager#invokePager
 * @throws applyError on exception
 */
function Helper_closePopup ()
{       
	try {
		/* invoke function in parent window */
		self.opener.$('lyLowerOpacity').style.display = 'none';
		self.opener.Mediamanager.invokePager('', pager_page);
		
		/* Needed for func closePopupTrack(noAlert) */
		audit = true;
		
		/* set a timeout since the opened window has
		 to be present til process function in parent is executed */
		setTimeout ("self.close()", 1200);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Close popup.
 * <br />
 * In addition to close the popup, we revoke the lowering of opacity.
 * Used in popups where it is expected to close the window with a href link.
 * 
 * @throws applyError on exception
 */
function Helper_closeLinksPopup ()
{       
	try {
		/* invoke function in parent window */
		self.opener.$('lyLowerOpacity').style.display = 'none';

		/* Needed for func closePopupTrack(NoAlert) */
		audit = true;
					
		/* set a timeout since the opened window has
		 to be present til process function in parent is executed */
		setTimeout ("self.close()", 100);
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Track the close of popups with expected refresh of Media Manager.
 * <br />
 * We need a handling if the user do not close the popups with the
 * appropriate buttons. For example, the user could use
 * keyboard shortcuts or close the popup by mouse click on the
 * window interface close button (depends on operating system).
 * <br />
 * So this function is added to the popups html body event <em>onunload</em>,
 * where its expected to close <em>and</em> refresh the Media Manager (with {@link #closePopup}).
 * There is a condition, that compares wether several control vars
 * (<em>audit</em>, <em>submitted</em>) are set (respectively are <em>bool</em> true)
 * or not. On the basis of the result, we invoke functions in the parent window,
 * like revoke the lowering of opacity and fire a alert demanding to close the
 * popup with the appropriate button or link. 
 * 
 * @param {global} audit Defined in {@link #closePopup}.
 * @param {global} submitted Avoid the execute of the onunload event.
 * @throws applyError on exception
 */
function Helper_closePopupTrack (elem)
{       
	try {
		// define global vars als false if not set
		if (typeof audit == 'undefined') {
			audit = false;
		}
		if (typeof submitted == 'undefined') {
			submitted = false;
		}
		if (audit !== true && submitted !== true) {
			self.opener.$('lyLowerOpacity').style.display = 'none';
			self.opener.alert(alertOnClosePopup);
			// reset global vars
			audit = false;
			submitted = false;
		}	
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Track the close of popups which contains href links as close action.
 * <br />
 * We need a handling if the user do not close the popups with the
 * appropriate link. For example, the user could use
 * keyboard shortcuts or close the popup by mouse click on the
 * window interface close button (depends on operating system).
 * <br />
 * So this function is added to the popups html body event <em>onunload</em>.
 * There is a condition, that compares wether the control var
 * (<em>audit</em>) are set (respectively are <em>bool</em> true)
 * or not. On the basis of the result, we revoke the lowering of opacity in the parent window.
 * 
 * @param {global} audit Defined in {@link #closeLinksPopup}.
 * @throws applyError on exception
 */
function Helper_closePopupTrackNoAlert (elem)
{       
	try {
		// define global vars als false if not set		
		if (typeof audit == 'undefined') {
			audit = false;
		}
		if (typeof submitted == 'undefined') {
			submitted = false;
		}
		if (audit !== true && submitted !== true) {
			self.opener.$('lyLowerOpacity').style.display = 'none';
			// reset global vars
			audit = false;
			submitted = false;
		}	
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Lower opacity via DOM build Layer.
 * <br />
 * Here we build a layer per DOM, which use a transparent PNG image.
 * 
 * @throws applyError on exception
 */
function Helper_lowerOpacity ()
{       
	try {
 		// properties
        this.cLeft = '0px';
        this.cTop = '0px';
        this.cPosition = 'absolute';
        this.cDisplay = 'block';
        this.imagePath = '../static/img/bg_overlay.png';
		this.lyContainer = $('container');   
        this.buildHeight = this.lyContainer.offsetHeight;
        this.buildWidth = this.lyContainer.offsetWidth;
		this.imageStr = '<img src="' + this.imagePath + '" width="' + this.buildWidth + '" height="' + this.buildHeight +'" alt="" />';
		this.targetToLower = $('lyLowerOpacity');

        if (this.targetToLower) {
		 	this.targetToLower.style.display = this.cDisplay;
			this.targetToLower.style.position = this.cPosition;
			this.targetToLower.style.top = this.cTop;
			this.targetToLower.style.left = this.cLeft;
			this.targetToLower.style.height = this.buildHeight + 'px';
			this.targetToLower.style.width = this.buildWidth + 'px';
			
			Element.update(this.targetToLower, this.imageStr);
        }
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Lower opacity via DOM build Layer on load of page.
 * <br />
 * Here we build a layer per DOM, which use a transparent PNG image.
 * Used within popups.
 * 
 * @throws applyError on exception
 */
function Helper_lowerOpacityOnUpload ()
{       
	try {
 		// properties
        this.cLeft = '0px';
        this.cTop = '0px';
        this.cPosition = 'absolute';
        this.cDisplay = 'block';
        this.imagePath = '../static/img/bg_overlay.png';
		this.lyContainer = $('modalWindow');   
        this.buildHeight = this.lyContainer.offsetHeight;
        this.buildWidth = this.lyContainer.offsetWidth;
		this.imageStr = '<img src="' + this.imagePath + '" width="' + this.buildWidth + '" height="' + this.buildHeight +'" alt="" />';
		this.targetToLower = $('lyLowerOpacity');

        if (this.targetToLower) {
		 	this.targetToLower.style.display = this.cDisplay;
			this.targetToLower.style.position = this.cPosition;
			this.targetToLower.style.top = this.cTop;
			this.targetToLower.style.left = this.cLeft;
			this.targetToLower.style.height = this.buildHeight + 'px';
			this.targetToLower.style.width = this.buildWidth + 'px';
				
			Element.update(this.targetToLower, this.imageStr);
        }
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Check browser and his version.
 * <br />
 * Sometimes we need this to distinguish between browser
 * and versions to intercept their different abilities.
 *
 * @returns Boolean
 * @throws applyError on exception
 */
function Helper_isBrowser(_browser, _version)
{	
	try {
		this.browser = _setBrowserString();
		this.version = _setBrowserStringVersion();
			
		if (this.browser == _browser) {
			if (_version) {
				if(this.version == _version) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		} else { 
			return false;
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Process given navigator.agent string to find out browser name.
 * Helper for {@link #isBrowser}.
 *
 * @private
 * @returns var
 * @throws applyError on exception
 */
function _compare (string)
{
	try {
		res = detect.indexOf(string) + 1;
		thestring = string;
		return res;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Get navigator.agent, compare it, fill global var and return it.
 * Helper for {@link #isBrowser}.
 *
 * @private
 * @returns var browser
 * @throws applyError on exception
 */
function _setBrowserString ()
{
	try {			
		detect = navigator.userAgent.toLowerCase();
		var browser;

		if (_compare('safari')) {
			browser = 'sa';
		}
		else if (_compare('msie')) {
			browser = 'ie';
		}
		else if (_compare('firefox')) {
			browser = 'ff';
		}
		else {
			browser = '';
		}
		return browser;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Process given navigator.agent string to find out agent version.
 * Helper for {@link #isBrowser}.
 *
 * @private
 * @returns var browser
 * @throws applyError on exception
 */
function _setBrowserStringVersion ()
{
	try {			
		_setBrowserString();
		var version;		
		version = detect.charAt(res + thestring.length);
		return version;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Define and resize window width.
 * <br />
 * Center the new window depending on giving Width.
 * 
 * @see #launchPopup
 * @param {var} elemWidth Given width
 * @return {number} x
 * @throws applyError on exception
 */
function Helper_defineWindowX (elemWidth)
{
	try {
		//properties
		this.el = elemWidth;
		var x;
		
		// all except Explorer
		if (self.innerHeight) {
			x = Math.round(self.innerWidth) - (Math.round(this.el));
		}
		// Explorer 6 Strict Mode
		else if (document.documentElement && document.documentElement.clientHeight) {
			x = Math.round(document.documentElement.clientWidth) - (Math.round(this.el));
		}
		// other Explorers
		else if (document.body) {
			x = Math.round(document.body.clientWidth) - (Math.round(this.el));
		}
		x = Math.round(x/2);
		return x;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Define and resize window height.
 * <br />
 * Center the new window depending on calculated height.
 * 
 * @see #launchPopup
 * @throws applyError on exception
 * @return {number} y
 */
function Helper_defineWindowY ()
{
	try {
		//properties
		var y;
	
		// all except Explorer
		if (self.innerHeight) { 
			y = Math.round(self.innerHeight/6);
		}
		// Explorer 6 Strict Mode
		else if (document.documentElement && document.documentElement.clientHeight) {
			y = Math.round(document.documentElement.clientHeight/6);
		}
		// other Explorers
		else if (document.body) {
			y = Math.round(document.body.clientHeight/6);
		}
		return y;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Define window scroll offset.
 * <br />
 * Returns the scroll offset of page.
 * Adapted from http://www.quirksmode.org/
 * 
 * @see #loaderChangeBlogCommentStatus
 * @throws applyError on exception
 * @return {number} y
 */
function Helper_definePageY ()
{
	try {
		var y;
		
		// all except Explorer
		if (self.pageYOffset) {
			y = self.pageYOffset;
		}
		// Explorer 6 Strict
		else if (document.documentElement && document.documentElement.scrollTop) {
			y = document.documentElement.scrollTop;
		}
		// all other Explorers
		else if (document.body) {
			y = document.body.scrollTop;
		}
		return y;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Show next appropriate DOM node.
 * <br />
 * Used in internal links popups to reflect sitemap structure.
 * 
 * @see #loaderPagesLinks
 * @see #showResponsePagesSecondLinks
 * @see #showResponsePagesThirdLinks
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_showNextNode(elem)
{
	try {
		// get source node id from element
		var sourceNode = elem.parentNode.parentNode.parentNode.parentNode.parentNode;
		
		// get next possible node id
		if (sourceNode.id != 'thirdNode') {
			var nextNode = Helper.getAttrNextSibling('id', sourceNode, 2);
		}
		var url = this.parsePagesLinksUrl;
		var pars = 'id=' + elem.id +'&nextNode=' + nextNode + '&control=' + control;
		
		if (nextNode == 'secondNode') {
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onLoading : Helper.loaderPagesLinks,
				onComplete : Helper.showResponsePagesSecondLinks
			});
		}
		else if (nextNode == 'thirdNode') {
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onLoading : Helper.loaderPagesLinks,
				onComplete : Helper.showResponsePagesThirdLinks
			});
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Show next appropriate DOM node.
 * <br />
 * Used in insert boxes popups to reflect sitemap structure.
 * 
 * @see #loaderPagesLinks
 * @see #showResponsePagesSecondLinks
 * @see #showResponsePagesThirdLinks
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_showNextNodeBoxes(elem)
{
	try {
		// get source node id from element
		var sourceNode = elem.parentNode.parentNode.parentNode.parentNode.parentNode;
		
		// get next possible node id
		if (sourceNode.id != 'thirdNode') {
			var nextNode = Helper.getAttrNextSibling('id', sourceNode, 2);
		}	
		var url = this.parsePagesBoxesLinksUrl;
		var pars = 'id=' + elem.id +'&nextNode=' + nextNode;
		
		if (nextNode == 'secondNode') {
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onLoading : Helper.loaderPagesLinks,
				onComplete : Helper.showResponsePagesSecondLinks
			});
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display indicator while XMLHttpRequest processing.
 *
 * @see #showNextNode
 * @throws applyError on exception
 */
function Helper_loaderPagesLinks ()
{
	try {
		Element.show('indicator_pagesLinks');
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest Response.
 *
 * @see #showNextNode
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Helper_showResponsePagesSecondLinks(req)
{
	try {
		Effect.Fade('indicator_pagesLinks', {duration: 0.4});
		Effect.Appear('secondNode',{duration: 0.6});
		$('secondNode').innerHTML = req.responseText;		
		Behaviour.reapply('.act_setInternalLink');
		Behaviour.reapply('.act_setInternalLinkNoHref');
		Behaviour.reapply('.act_setInternalLinkBoxes');
		Behaviour.reapply('.showNextNode');
		Behaviour.reapply('.showNextNodeBoxes');
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Populate on XMLHttpRequest Response.
 *
 * @see #showNextNode
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Helper_showResponsePagesThirdLinks(req)
{
	try {
		Effect.Fade('indicator_pagesLinks', {duration: 0.4});
		Effect.Appear('thirdNode',{duration: 0.6});	
		$('thirdNode').innerHTML = req.responseText;		
		Behaviour.reapply('.act_setInternalLink');
		Behaviour.reapply('.act_setInternalLinkNoHref');
		Behaviour.reapply('.showNextNode');
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Process inserting content from a popup.
 * <br />
 * Adapted from http://sourceforge.net/projects/wikipedia
 * 
 * @see #insertInternalLink
 * @see #insertInternalLinkNoHref
 * @see #insertInternalLinkGlobalTemplates
 * @see #insertInternalLinkGlobalFiles
 * @param {string} id Form element to populate
 * @param {string} tagOpen Opening part of build string
 * @param {string} tagClose Closing part of build string
 * @param {string} sampleText Text to set, when we use tagOpen and tagClose
 * @throws applyError on exception
 */
function Helper_insertTagsFromPopup(id, tagOpen, tagClose, sampleText)
{
	try {
		/*
		We have to distinguish here, because the IE is obviously too dumb to differ between elements
		which has the same value on different attributes (name, id). Here we have a conflict with
		the form hidden field attribute. So we serve IE by object forms[elements], while all others
		are able to use the standards (pointing the element by document.getElementById().
		*/
	 	if (Helper.isBrowser('ie')) {
			var _form_name = id.replace(/(.+)(_.+$)/, '$1');
			var txtarea = opener.document.forms[_form_name].elements[id];
		} else {
			var txtarea = opener.$(id);
		}
		
		// IE
		if(opener.document.selection) {
			var theSelection = opener.document.selection.createRange().text;
			if(!theSelection) { theSelection=sampleText;}
			txtarea.focus();
			if(theSelection.charAt(theSelection.length - 1) == " "){// exclude ending space char, if any
				theSelection = theSelection.substring(0, theSelection.length - 1);
				opener.document.selection.createRange().text = tagOpen + theSelection + tagClose + " ";
			} else {
				opener.document.selection.createRange().text = tagOpen + theSelection + tagClose;
			}
		// Mozilla -- disabled because it induces a scrolling bug which makes it virtually unusable
		} else if(txtarea.selectionStart || txtarea.selectionStart == '0') {
	 		var startPos = txtarea.selectionStart;
			var endPos = txtarea.selectionEnd;
			var scrollTop=txtarea.scrollTop;
			var myText = (txtarea.value).substring(startPos, endPos);
			if(!myText) { myText=sampleText;}
			if(myText.charAt(myText.length - 1) == " "){ // exclude ending space char, if any
				subst = tagOpen + myText.substring(0, (myText.length - 1)) + tagClose + " "; 
			} else {
				subst = tagOpen + myText + tagClose; 
			}
			txtarea.value = txtarea.value.substring(0, startPos) + subst + txtarea.value.substring(endPos, txtarea.value.length);
			txtarea.focus();
			var cPos=startPos+(tagOpen.length+myText.length+tagClose.length);
			txtarea.selectionStart=cPos;
			txtarea.selectionEnd=cPos;
			//txtarea.scrollTop=scrollTop;
		// All others
		} else {
			// Append at the end: Some people find that annoying
			//txtarea.value += tagOpen + sampleText + tagClose;
			//txtarea.focus();
			var re=new RegExp("\\n","g");
			tagOpen=tagOpen.replace(re,"");
			tagClose=tagClose.replace(re,"");
			opener.document.infoform.infobox.value=tagOpen+sampleText+tagClose;
			txtarea.focus();
		}
		// reposition cursor if possible
		if (txtarea.createTextRange) txtarea.caretPos = opener.document.selection.createRange().duplicate();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Process inserting content.
 * <br />
 * Adapted from http://sourceforge.net/projects/wikipedia
 * 
 * @see #insertInternalLink
 * @see #insertInternalLinkNoHref
 * @see #insertInternalLinkGlobalTemplates
 * @see #insertInternalLinkGlobalFiles
 * @param {string} id Form element to populate
 * @param {string} tagOpen Opening part of build string
 * @param {string} tagClose Closing part of build string
 * @param {string} sampleText Text to set, when we use tagOpen and tagClose
 * @throws applyError on exception
 */
function Helper_insertTags(id, tagOpen, tagClose, sampleText)
{
	try {
		/*
		We have to distinguish here, because the IE is obviously too dumb to differ between elements
		which has the same value on different attributes (name, id). Here we have a conflict with
		the form hidden field attribute. So we serve IE by object forms[elements], while all others
		are able to use the standards (pointing the element by document.getElementById().
		*/
	 	if (Helper.isBrowser('ie')) {
			var _form_name = id.replace(/(.+)(_.+$)/, '$1');
			var txtarea = document.forms[_form_name].elements[id];
		} else {
			var txtarea = $(id);
		}
		// IE
		if(document.selection) {
			var theSelection = document.selection.createRange().text;
			if(!theSelection) { theSelection=sampleText;}
			txtarea.focus();
			if(theSelection.charAt(theSelection.length - 1) == " "){// exclude ending space char, if any
				theSelection = theSelection.substring(0, theSelection.length - 1);
				document.selection.createRange().text = tagOpen + theSelection + tagClose + " ";
			} else {
				document.selection.createRange().text = tagOpen + theSelection + tagClose;
			}
		// Mozilla -- disabled because it induces a scrolling bug which makes it virtually unusable
		} else if(txtarea.selectionStart || txtarea.selectionStart == '0') {
	 		var startPos = txtarea.selectionStart;
			var endPos = txtarea.selectionEnd;
			var scrollTop=txtarea.scrollTop;
			var myText = (txtarea.value).substring(startPos, endPos);
			if(!myText) { myText=sampleText;}
			if(myText.charAt(myText.length - 1) == " "){ // exclude ending space char, if any
				subst = tagOpen + myText.substring(0, (myText.length - 1)) + tagClose + " "; 
			} else {
				subst = tagOpen + myText + tagClose; 
			}
			txtarea.value = txtarea.value.substring(0, startPos) + subst + txtarea.value.substring(endPos, txtarea.value.length);
			txtarea.focus();
			var cPos=startPos+(tagOpen.length+myText.length+tagClose.length);
			txtarea.selectionStart=cPos;
			txtarea.selectionEnd=cPos;
			txtarea.scrollTop=scrollTop;
		// All others
		} else {
			// Append at the end: Some people find that annoying
			//txtarea.value += tagOpen + sampleText + tagClose;
			//txtarea.focus();
			var re=new RegExp("\\n","g");
			tagOpen=tagOpen.replace(re,"");
			tagClose=tagClose.replace(re,"");
			document.infoform.infobox.value=tagOpen+sampleText+tagClose;
			txtarea.focus();
		}
		// reposition cursor if possible
		if (txtarea.createTextRange) txtarea.caretPos = document.selection.createRange().duplicate();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Insert internal page reference into content.
 * <br />
 * We build a string with required syntax to deliver it to {@link #insertTagsFromPopup}
 * and close the popup window afterwards.
 * 
 * @see #insertTagsFromPopup
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_insertInternalLink(elem)
{
	try {
		var build;
		build = '<a href="';
		build += elem.id;
		build += '">';
		
		strStart = build;
		strEnd = '</a>';
		
		// purge global form var
		var _formTarget = formTarget.replace(/^(_)(.+$)/, '$2');
		
		Helper.insertTagsFromPopup(_formTarget, strStart, strEnd, describeLink);
	
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Insert internal page reference into content.
 * <br />
 * Get the element id content, deliver it to {@link #insertTagsFromPopup}
 * and close the popup window afterwards.
 * var <em>formTarget</em> comes from the html markup.
 * 
 * @see #insertTagsFromPopup
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_insertInternalLinkNoHref(elem)
{
	try {
		// purge global form var
		var _formTarget = formTarget.replace(/^(_)(.+$)/, '$2');
		
		Helper.insertTagsFromPopup(_formTarget, elem.id, '', '');
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Insert internal globaltemplates reference into content.
 * <br />
 * Get the element id content, deliver it to {@link #insertTagsFromPopup}
 * and close the popup window afterwards.
 * var <em>formTarget</em> comes from the html markup.
 * 
 * @see #insertTagsFromPopup
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_insertInternalLinkGlobalTemplates(elem)
{
	try {
		// purge global form var
		var _formTarget = formTarget.replace(/^(_)(.+$)/, '$2');
		
		Helper.insertTagsFromPopup(_formTarget, elem.id, '', '');	
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Insert internal globalfiles reference into content.
 * <br />
 * Get the element id content, deliver it to {@link #insertTagsFromPopup}
 * and close the popup window afterwards.
 * var <em>formTarget</em> comes from the html markup.
 * 
 * @see #insertTagsFromPopup
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_insertInternalLinkGlobalFiles(elem)
{
	try {
		// purge global form var
		var _formTarget = formTarget.replace(/^(_)(.+$)/, '$2');
		
		Helper.insertTagsFromPopup(_formTarget, elem.id, '', '');
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Insert internal global box reference into content.
 * <br />
 * Get the element id content, deliver it to {@link #insertTagsFromPopup}
 * and close the popup window afterwards.
 * var <em>formTarget</em> comes from the html markup.
 * 
 * @see #insertTagsFromPopup
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_insertInternalLinkGlobalBoxes(elem)
{
	try {
		// purge global form var
		var _formTarget = formTarget.replace(/^(_)(.+$)/, '$2');
		
		Helper.insertTagsFromPopup(_formTarget, elem.id, '', '');
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Insert internal page box reference into content.
 * <br />
 * Get the element id content, deliver it to {@link #insertTagsFromPopup}
 * and close the popup window afterwards.
 * var <em>formTarget</em> comes from the html markup.
 * 
 * @see #insertTagsFromPopup
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_insertInternalLinkBoxes(elem)
{
	try {
		// purge global form var
		var _formTarget = formTarget.replace(/^(_)(.+$)/, '$2');
		
		Helper.insertTagsFromPopup(_formTarget, elem.id, '', '');
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Get internal structure templates reference.
 * <br />
 * Get the appropriate content and populate it unseen into a special,
 * original tag (see {@link #showResponseStructuralTemplates}) within
 * the html markup.
 * 
 * @see #showResponseStructuralTemplates
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_insertInternalLinkStructuralTemplates(elem)
{
	try {		
		var url = this.parseStructuralTemplatesLinksUrl;
		var pars = 'id=' + elem.id;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onComplete : Helper.showResponseStructuralTemplates
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Process and insert XMLHttpRequest response into content.
 * <br />
 * First we fetch the response with the surrounding html comment tag.
 * This is because we are working with markup, so this has to be distinctive.
 * Then we extract the proper content, deliver it to {@link #insertTagsFromPopup}
 * and close the popup window afterwards.
 * var <em>formTarget</em> comes from the html markup.
 *
 * @see #insertInternalLinkStructuralTemplates
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Helper_showResponseStructuralTemplates(req)
{
	try {		
		// filter responseText on html element
		var extract = req.responseText.match(/(<!--\s+<span id="template_content">)((.|\t|\r|\n)*)(<\/span>\s+\/\/-->)/gm);
		
		// strip the html element, get raw content
		var raw_content = String(extract).replace(/(.*?)(>)((.|\t|\r|\n)*)(<\/span>\s+\/\/-->)/, "$3");
		
		// purge global form var
		var _formTarget = formTarget.replace(/^(_)(.+$)/, '$2');
		
		Helper.insertTagsFromPopup(_formTarget, raw_content, '', '');
		Helper.closeLinksPopup();					
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Change blog comment status.
 * <br/>
 * Get the form select values with corresponding comment id
 * and edit on page.
 * 
 * @see #loaderChangeBlogCommentStatus
 * @see #showResponseChangeBlogCommentStatus
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_changeBlogCommentStatus (elem)
{	
	try {
		var statusId;
		var commentId;
		
		// get status value
		statusId = elem.options[elem.selectedIndex].value;
	
		// find blog comment id
		commentId = elem.parentNode.parentNode.parentNode;
		commentId = Helper.getNextSiblingFirstChild(commentId, 4);
		commentId = String(commentId.href);
		commentId = commentId.replace(/(.*?)(id\=+)(\d+)/g, "$3");		

		// properties
		var url = this.parseBlogCommmentStatusChangeUrl;
		var pars = 'status_id=' + statusId + '&comment_id=' + commentId;

		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				onLoading : Helper.loaderChangeBlogCommentStatus,
				parameters : pars,
				onComplete : Helper.showResponseChangeBlogCommentStatus
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display indicator layer while XMLHttpRequest processing.
 * Triggers {@link #lowerOpacity}.
 *
 * @see #lowerOpacity
 * @throws applyError on exception
 */
function Helper_loaderChangeBlogCommentStatus ()
{
	try {
		Helper.lowerOpacity();

		this.statuschange = $('statuschange');
		var winHeight = Helper.defineWindowY();
		var scrollPos = Helper.definePageY();		
		this.statuschange.style.top = winHeight + scrollPos + 140 + 'px';
		
		Effect.Appear(this.statuschange, {duration: 0.6, delay: 0.2});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Layer fadeout on XMLHttpRequest response.
 *
 * @see #changeBlogCommentStatus
 * @param {object} req XMLHttpRequest response
 * @throws applyError on exception
 */
function Helper_showResponseChangeBlogCommentStatus(req)
{
	try {
		setTimeout("Effect.Fade('statuschange', {duration: 0.6})", 2000);
		setTimeout("$('lyLowerOpacity').style.display = 'none';", 2800);
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Display upload indicator.
 * Trigger {@link #lowerOpacityOnUpload}. This is used on submit
 * within the media upload popup to indicate the upload still remains.
 *
 * @see #lowerOpacityOnUpload
 * @throws applyError on exception
 */
function Helper_showFileUploadMessage()
{
	try {
		Helper.lowerOpacityOnUpload();
		Effect.Appear('uploadMessage', {duration: 0.4});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Validate form elements on the fly.
 * <br />
 * Get the form element id attribute value and populate the
 * regex processed response into the layer <em>container</em>.
 * This assumes that we have corresponding html markup/css.
 * <br />
 * For in depths explanation please have a look on the online
 * project support area.
 *
 * @param {object} elem Current element
 * @throws applyError on exception
 */
function Helper_validate(elem)
{	
	var url		= this.validateUrl;
	elemID		= $(elem).getAttribute('id');
	var elemVal	= $F(elem);
	var pars	= 'elemID=' + elemID + '&elemVal=' + elemVal;
	var container = elemID + '_container';
	
	var myAjax = new Ajax.Updater ( 
		{
			failure: container,
			success: container
		},
		url,
		{
			method: 'post',
			parameters: pars
		});		
}

/**
 * Confirm navigation delete.
 * 
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_confirmDelNavAction(elem)
{
	try {	
		var v = confirm(confirmMsgDelNav);

		if (v == true) {
			window.location.href = elem.href;
		}
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Confirm template type delete.
 * 
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_confirmDelTplTypeAction(elem)
{
	try {	
		var v = confirm(confirmMsgDelTplType);

		if (v == true) {
			window.location.href = elem.href;
		}
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Confirm template sets delete.
 * 
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_confirmDelTplSetsAction(elem)
{
	try {	
		var v = confirm(confirmMsgDelTplSets);

		if (v == true) {
			window.location.href = elem.href;
		}
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Confirm global template delete.
 * 
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_confirmDelTplGlobalAction(elem)
{
	try {	
		var v = confirm(confirmMsgDelTplGlobal);

		if (v == true) {
			window.location.href = elem.href;
		}
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Confirm global file delete.
 * 
 * @param {var} elem Current element
 * @throws applyError on exception
 */
function Helper_confirmDelTplGlobalfileAction(elem)
{
	try {	
		var v = confirm(confirmMsgDelTplGlobalfile);

		if (v == true) {
			window.location.href = elem.href;
		}
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Change delimiter on given form value.
 * <br />
 * In some cases it is required to change the delimiter to avoid syntax
 * conflicts while inserting references (e.g. working with cascading
 * style sheets or javascript, which uses brackets also).
 * 
 * @see #launchPopup
 * @throws applyError on exception
 */
function Helper_getDelimiterValue()
{
	try {
		if ($('global_template_change_delimiter')) {
			delimiter = $F('global_template_change_delimiter');
		} else {
			delimiter = '';
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Get the current pager_page value.
 * <br />
 * This is needed for Media Manager action popups (<em>upload</em>, <em>edit</em>)
 * which supposed to refresh the content display on close of popup.
 * 
 * @see #launchPopup
 * @throws applyError on exception
 */
function Helper_getPagerPage()
{
	try {
		if($('pager_page_container')) {
			pager_page = $('pager_page_container').firstChild.nodeValue;
		} else {
			pager_page = '';
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Getter for parent node attribute.
 *
 * @param {string} attr Given attribute to use
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Parent node attribute of element
 * @throws applyError on exception
 */
function Helper_getAttrParentNode (attr, elem, level)
{
	this.browser = _setBrowserString();

	for (var a = elem; level > 0; level--) {
		a = a.parentNode;
	}
		
	if (this.browser == 'ie')
		return a.attributes[attr].value;
	else
		return a.getAttribute(attr);
}

/**
 * Getter for current element parent node next node attribute.
 *
 * @param {string} attr Given attribute to use
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Next node attribute of parent node of the current element
 * @throws applyError on exception
 */
function Helper_getAttrParentNodeNextNode (attr, elem, level)
{
	this.browser = _setBrowserString();

	for (var a = elem; level > 0; level--) {
		a = a.parentNode;
	}	
	a = a.nextSibling.nextSibling;
	
	if (this.browser == 'ie')
		return a.attributes[attr].value;
	else
		return a.getAttribute(attr);
}

/**
 * Getter for node attribute.
 *
 * @param {string} attr Given attribute to use
 * @param {object} elem Current element
 * @return object Attribute of element
 * @throws applyError on exception
 */
function Helper_getAttr (attr, elem)
{
	this.browser = _setBrowserString();
		
	if (this.browser == 'ie')
		return elem.attributes[attr].value;
	else
		return elem.getAttribute(attr);
}


/**
 * Getter for next sibling node attribute.
 *
 * @param {string} attr Given attribute to use
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Next sibling attribute
 * @throws applyError on exception
 */
function Helper_getAttrNextSibling (attr, elem, level)
{
	this.browser = _setBrowserString();
	
	if (this.browser == 'ie')
		level-- ;

	for (var a = elem; level > 0; level--) {
		a = a.nextSibling;
	}
		
	if (this.browser == 'ie')
		return a.attributes[attr].value;
	else
		return a.getAttribute(attr);
}

/**
 * Getter for next sibling first child node attribute.
 *
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Next sibling first child value
 * @throws applyError on exception
 */
function Helper_getNextSiblingFirstChild (elem, level)
{
	this.browser = _setBrowserString();
	
	if (this.browser == 'ie' || this.browser == 'sa')
		level-- ;

	for (var a = elem; level > 0; level--) {
		a = a.nextSibling;
	}
	return a.firstChild;
}

/**
 * Getter for parent node data.
 *
 * @param {object} elem Current element
 * @param {string} level Depth of parent node search
 * @return object Parent node data
 * @throws applyError on exception
 */
function Helper_getDataParentNode (elem, level)
{
	for (var a = elem; level > 0; level--) {
		a = a.parentNode;
	}
	return Helper.trim(a.firstChild.nodeValue.toLowerCase());	
}

/**
 * Wrapper to transform form field values to valid urls.
 * <br />
 * Fills the related url form field with transformed values
 * ({link #URLify}) of the current form field element.
 *
 * @see #URLify
 * @param {string} elem Current Element
 * @throws applyError on exception
 */
function Helper_convertFieldValuesToValidUrl (elem, attr)
{
	try {	
		//properties
		this.elem = elem;
		this.attr = attr;
		
		// get next node (e.g. label) attribute
		var el = Helper.getAttrParentNodeNextNode (this.attr, this.elem, 1);
		
		// fill related form field with tranformed results
		$(el).value = Helper.URLify(this.elem.value);

	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Transforms form field values to valid urls.
 * 
 * @see #convertFieldValuesToValidUrl
 * @param {string} string given string
 * @return string processed string
 * @throws applyError on exception
 */
function Helper_URLify (string)
{
	try {	
		// convert string to lowercase so that we always operate on lowercases
		string = string.toLowerCase();

		// trim whitespaces
		Helper.trim(string);
	
		// definitions for char normalization
		normalizations = {
			// german
			'ä': 'ae',
			'ö': 'oe',
			'ü': 'ue',
			'ß': 'ss'
			// french
		
			// spanish
		
			// swedish
		
			// other
		}
	
		// execute char normalization
		for (char in normalizations) {
			r = new RegExp(char, 'gi');
			string = string.replace(r, normalizations[char]);
		}
	
		// remove everything except a-z, 0-9 and hyphens and replace it with a hyphen 
		string = string.replace(/[^a-z0-9\-]/g, '-');
	
		// remove unnecessary hyphens
		string = string.replace(/-{2,}/g, '-');
	
		// remove hyphens at the beginning and at the end of the string
		string = string.replace(/^(-+)/, '');
		string = string.replace(/(-+)$/, '');
	
	    return string;
	} catch (e) {
		_applyError(e);
	}	
}

/**
 * Building new object instance of class Helper
 */
Helper = new Helper();
