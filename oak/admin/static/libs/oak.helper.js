/**
 * Project: Oak
 * File: oak.helper.js
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
 * @fileoverview This file comprised javascript helper functions.
 * It contains functions that may be used application wide.
 * 
 * @author Olaf Gleba og@creatics.de
 * @version $Id$ 
 */



/**
 * Constructs the Helper class
 * 
 * @class The Mediamanager class miscellaneous is the appropriate class for
 * the help enviroment. The scope is application wide.
 *
 * Prototype methods:
 * 
 * launchPopup()
 * 
 *
 * closePopup()
 * 
 *
 * closeLinksPopup()
 * 
 *
 * lowerOpacity()
 * 
 *
 * lowerOpacityOnUpload()
 * 
 *
 * unsupportsEffects()
 * 
 *
 * unsupportsElems()
 * 
 * 
 * defineWindowX()
 * 
 *
 * defineWindowY()
 * 
 *
 * showNextNode()
 * 
 *
 * insertInternalLink()
 * 
 *
 * insertInternalLinkGlobalTemplates()
 * 
 *
 * insertInternalLinkGlobalFiles()
 * 
 *
 * getDelimiterValue()
 * 
 *
 * getPagerPage()
 * 
 *
 * confirmDelNavAction()
 * 
 *
 * changeBlogCommentStatus()
 * 
 *
 * showFileUploadMessage()
 * 
 *
 * getAttrParentNode()
 * 
 *
 * getAttr()
 * 
 *
 * getAttrNextSibling()
 * 
 *
 * getNextSiblingFirstChild()
 * 
 *
 * getDataParentNode()
 * 
 *
 * applyBehaviour()
 * 
 *
 *
 * @see Base
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
Helper.prototype.closePopup = Helper_closePopup;
Helper.prototype.closeLinksPopup = Helper_closeLinksPopup;
Helper.prototype.lowerOpacity = Helper_lowerOpacity;
Helper.prototype.lowerOpacityOnUpload = Helper_lowerOpacityOnUpload;
Helper.prototype.unsupportsEffects = Helper_unsupportsEffects;
Helper.prototype.unsupportsElems = Helper_unsupportsElems;
Helper.prototype.defineWindowX = Helper_defineWindowX;
Helper.prototype.defineWindowY = Helper_defineWindowY;
Helper.prototype.showNextNode = Helper_showNextNode;
Helper.prototype.insertInternalLink = Helper_insertInternalLink;
Helper.prototype.insertInternalLinkGlobalTemplates = Helper_insertInternalLinkGlobalTemplates;
Helper.prototype.insertInternalLinkGlobalFiles = Helper_insertInternalLinkGlobalFiles;
Helper.prototype.getDelimiterValue = Helper_getDelimiterValue;
Helper.prototype.getPagerPage = Helper_getPagerPage;
Helper.prototype.confirmDelNavAction = Helper_confirmDelNavAction;
Helper.prototype.changeBlogCommentStatus = Helper_changeBlogCommentStatus;
Helper.prototype.showFileUploadMessage = Helper_showFileUploadMessage;
Helper.prototype.getAttrParentNode = Helper_getAttrParentNode;
Helper.prototype.getAttr = Helper_getAttr;
Helper.prototype.getAttrNextSibling = Helper_getAttrNextSibling;
Helper.prototype.getNextSiblingFirstChild = Helper_getNextSiblingFirstChild;
Helper.prototype.getDataParentNode = Helper_getDataParentNode;
Helper.prototype.applyBehaviour = Helper_applyBehaviour;


function Helper_launchPopup (width, height, nname, trigger, elem)
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
			case 'globaltemplates_internal_links' :
					Helper.getDelimiterValue();
					this.url = this.parseGlobalTemplatesLinksUrl + '?target=' + this.elem.name + '&delimiter=' + val;
				break;
			case 'globalfiles_internal_links' :
					Helper.getDelimiterValue();
					this.url = this.parseGlobalFilesLinksUrl + '?target=' + this.elem.name + '&delimiter=' + val;
				break;
		}
		// properties
		this.ttargetUrl = this.url;
		this.ttargetName = nname;
		this.ttargetWidth = width;
		this.ttargetHeight = height;
		this.ttarget = window.open(this.ttargetUrl, this.ttargetName, 
				"scrollbars=yes,width="+this.ttargetWidth+",height="+this.ttargetHeight+"");
		this.resWidth = Helper.defineWindowX(this.ttargetWidth);
		this.resHeight = Helper.defineWindowY();
		
		this.ttarget.moveBy(this.resWidth, this.resHeight);
		this.ttarget.focus();
		
		//popTarget = this.ttarget;
	} catch (e) {
		_applyError(e);
	}
}

function Helper_closePopup ()
{       
	try {
		/* disable all elements */
		var form_id = document.forms[0].getAttribute('id');
	
		e = Form.getElements(form_id);
			for(i = 0; i < e.length; i++) {
    			e[i].disabled = true;
			}

		/* invoke function in parent window */
		self.opener.$('lyLowerOpacity').style.display = 'none';
		self.opener.Mediamanager.invokePager('', pager_page);
		
		/* set a timeout since the opened window has
		 to be present til process function in parent is executed */
		setTimeout ("self.close()", 1200);
		
	} catch (e) {
		_applyError(e);
	}
}


function Helper_closeLinksPopup ()
{       
	try {
		/* invoke function in parent window */
		self.opener.$('lyLowerOpacity').style.display = 'none';
			
		/* set a timeout since the opened window has
		 to be present til process function in parent is executed */
		setTimeout ("self.close()", 100);
		
	} catch (e) {
		_applyError(e);
	}
}


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
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @requires Helper The Helper Class
 * @throws applyError on exception
 */
function Helper_unsupportsEffects(exception)
{	
	try {
		//properties
		this.browser = _setBrowserString();
		this.version = _setBrowserStringVersion();
		this.exception = exception;
			
		if ((this.browser == "Internet Explorer") || (this.browser == "Safari" && !this.exception)) {
			return true;
		} else { 
			return false;
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @requires Helper The Helper Class
 * @throws applyError on exception
 */
function Helper_unsupportsElems(exception)
{	
	try {
		//properties
		this.browser = _setBrowserString();
		this.version = _setBrowserStringVersion();
		this.exception = exception;
		
		if ((this.browser == "Internet Explorer") || (this.browser == "Safari" && !this.exception)) {
			return true;
		} else { 
			return false;
		}
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
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
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
 */
function _setBrowserString ()
{
	try {			
		detect = navigator.userAgent.toLowerCase();
		var browser;

		if (_compare('safari')) {
			browser = 'Safari';
		}
		else if (_compare('msie')) {
			browser = 'Internet Explorer';
		}
		else {
			browser = 'Unknown Browser';
		}
		return browser;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
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
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
 */
function _setBrowserStringOS ()
{
	try {			
		detect = navigator.userAgent.toLowerCase();
		var os;
		
		if (_compare('linux')) {
			os = 'Linux';
		}
		else if (_compare('x11')) {
			os = 'Unix';
		}
		else if (_compare('win')) {
			os = 'Windows';
		}
		else if (_compare('mac')) {
			os = 'Mac';
		}
		else {
			os = 'Unknown operating system';
		}
		return os;
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Implements method of prototype class Helper
 * Center the new window depending on giving Width (elemWith)
 * @param {var} elemWidth Actual element
 * @throws applyError on exception
 * @return number calculated width
 */
function Helper_defineWindowX (elemWidth)
{
	try {
		//properties
		this.el = elemWidth;
		var x;
		
		if (self.innerHeight) {
			// all except Explorer {
			x = Math.round(self.innerWidth) - (Math.round(this.el));
		}
		else if (document.documentElement && document.documentElement.clientHeight) {
			// Explorer 6 Strict Mode
			x = Math.round(document.documentElement.clientWidth) - (Math.round(this.el));
		}
		else if (document.body) {
			// other Explorers
			x = Math.round(document.body.clientWidth) - (Math.round(this.el));
		}
		x = Math.round(x/2);
		return x;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Center the new window depending on browser window Height
 * @throws applyError on exception
 * @return number calculated height
 */
function Helper_defineWindowY ()
{
	try {
		//properties
		var y;
	
		if (self.innerHeight) { 
		// all except Explorer
			y = Math.round(self.innerHeight/6);
		}
		else if (document.documentElement && document.documentElement.clientHeight) {
			// Explorer 6 Strict Mode
			y = Math.round(document.documentElement.clientHeight/6);
		}
		else if (document.body) {
			// other Explorers
			y = Math.round(document.body.clientHeight/6);
		}
		return y;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Display next url level
 * @param {var} elem Actual element
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
		var pars = 'id=' + elem.id +'&nextNode=' + nextNode;
		
		if (nextNode == 'secondNode') {
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onLoading : _loaderPagesLinks,
				onComplete : _showResponsePagesSecondLinks
			});
		}
		else if (nextNode == 'thirdNode') {
		var myAjax = new Ajax.Request(
			url,
			{
				method : 'get',
				parameters : pars,
				onLoading : _loaderPagesLinks,
				onComplete : _showResponsePagesThirdLinks
			});
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Populate on JSON response
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _showResponsePagesSecondLinks(req)
{
	try {
		Effect.Fade('indicator_pagesLinks', {duration: 0.4});
		Effect.Appear('secondNode',{duration: 0.6});
		$('secondNode').innerHTML = req.responseText;		
		Behaviour.reapply('.act_setInternalLink');
		Behaviour.reapply('.showNextNode');
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Populate on JSON response
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _showResponsePagesThirdLinks(req)
{
	try {
		Effect.Fade('indicator_pagesLinks', {duration: 0.4});
		Effect.Appear('thirdNode',{duration: 0.6});	
		$('thirdNode').innerHTML = req.responseText;		
		Behaviour.reapply('.act_setInternalLink');
		Behaviour.reapply('.showNextNode');
		
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
function _loaderPagesLinks ()
{
	try {
		Element.show('indicator_pagesLinks');
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Implements method of prototype class Helper
 * Insert internal link string
 * @requires Helper The Helper Class
 */
function Helper_insertInternalLink(elem)
{
	try {
		// delivered from within smarty assign
		var ttarget = formTarget;
		
		var build;
		build = '<a href="';
		build += elem.id;
		build += '">';
		
		strStart = build;
		strEnd = '</a>';
			
		// alert message(describeLink) is defined in oak.strings.js
		_insertTagsFromPopup(ttarget, strStart, strEnd, describeLink);
	
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Insert internal link string
 * @requires Helper The Helper Class
 */
function Helper_insertInternalLinkGlobalTemplates(elem)
{
	try {
		// delivered from within smarty assign
		var target = formTarget;
		
		_insertTagsFromPopup(target, elem.id, '', '');
	
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Insert internal link string
 * @requires Helper The Helper Class
 */
function Helper_insertInternalLinkGlobalFiles(elem)
{
	try {
		// delivered from within smarty assign
		var target = formTarget;
		
		_insertTagsFromPopup(target, elem.id, '', '');
	
		Helper.closeLinksPopup();
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Insert Content into Textareas from Popup
 * taken from http://sourceforge.net/projects/wikipedia
 * @private
 * @throws applyError on exception
 */
function _insertTagsFromPopup(id, tagOpen, tagClose, sampleText)
{
	try {
		/*
		We have to separate here, because the IE6 seems to be too dump to differ between elements
		which has the same value on different attributes (name, id)	
		So we serve IE by object forms[elements], while Mozilla be able to use 
		the standard (pointing the element by document.getElementById()
		*/
	 	if (Helper.unsupportsElems('safari_exception')) {
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
			txtarea.scrollTop=scrollTop;
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
 * Insert Content into Textareas
 * taken from http://sourceforge.net/projects/wikipedia
 * @private
 * @throws applyError on exception
 */
function _insertTags(id, tagOpen, tagClose, sampleText)
{
	try {
		/*
		We have to separate here, because the IE6 seems to be too dump to differ between elements
		which has the same value on different attributes (name, id)	
		So we serve IE by object forms[elements], while Mozilla be able to use 
		the standard (pointing the element by document.getElementById()
		*/
	 	if (Helper.unsupportsElems('safari_exception')) {
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
 * Implements method of prototype class Helper
 * Insert internal link string
 * @requires Helper The Helper Class
 */
function Helper_getDelimiterValue()
{
	try {
		// make global for further use in func Helper.launchPopup()
		if ($('global_template_change_delimiter')) {
			val = $F('global_template_change_delimiter');
		} else {
			val = '';
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Insert internal link string
 * @requires Helper The Helper Class
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
 * Implements method of prototype class Helper
 * Confirm action
 * If true, use the giving href to process
 * @param {var} elem Actual element
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
* get select values onchange handler
*
* return string
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
				onLoading : _loaderChangeBlogCommentStatus,
				parameters : pars,
				onComplete : _showResponseChangeBlogCommentStatus
			});
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Populate on JSON response
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _showResponseChangeBlogCommentStatus(req)
{
	try {
		setTimeout("Effect.Fade('statuschange', {duration: 0.6})", 2000);
		setTimeout("$('lyLowerOpacity').style.display = 'none';", 2800);
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * fires temporary actions while processing the ajax call
 *
 * @private
 * @param {object} req JSON response
 * @throws applyError on exception
 */
function _loaderChangeBlogCommentStatus ()
{
	try {
		Helper.lowerOpacity();
		Effect.Appear('statuschange', {duration: 0.6, delay: 0.2});
	} catch (e) {
		_applyError(e);
	}
}

function Helper_showFileUploadMessage()
{
	try {
		Helper.lowerOpacityOnUpload();
		Effect.Appear('uploadMessage', {duration: 0.4});
	} catch (e) {
		_applyError(e);
	}
}

function Helper_getAttrParentNode (attr, elem, level)
{
	this.browser = _setBrowserString();

	for (var a = elem; level > 0; level--) {
		a = a.parentNode;
	}
		
	if (this.browser == 'Internet Explorer')
		return a.attributes[attr].value;
	else
		return a.getAttribute(attr);
}

function Helper_getAttr (attr, elem)
{
	this.browser = _setBrowserString();
		
	if (this.browser == 'Internet Explorer')
		return elem.attributes[attr].value;
	else
		return elem.getAttribute(attr);
}

function Helper_getAttrNextSibling (attr, elem, level)
{
	this.browser = _setBrowserString();
	
	if (this.browser == 'Internet Explorer')
		level-- ;

	for (var a = elem; level > 0; level--) {
		a = a.nextSibling;
	}
		
	if (this.browser == 'Internet Explorer')
		return a.attributes[attr].value;
	else
		return a.getAttribute(attr);
}

function Helper_getNextSiblingFirstChild (elem, level)
{
	this.browser = _setBrowserString();
	
	if (this.browser == 'Internet Explorer' || this.browser == 'Safari')
		level-- ;

	for (var a = elem; level > 0; level--) {
		a = a.nextSibling;
	}
	return a.firstChild;
}

function Helper_getDataParentNode (elem, level)
{
	for (var a = elem; level > 0; level--) {
		a = a.parentNode;
	}
	return Helper.trim(a.firstChild.nodeValue.toLowerCase());	
}

function Helper_applyBehaviour ()
{
		Behaviour.reapply('input');
		Behaviour.reapply('a.mm_edit');
		Behaviour.reapply('a.mm_upload');
		Behaviour.reapply('a.mm_delete');
		Behaviour.reapply('a.mm_cast');
		Behaviour.reapply('a.pager');
		Behaviour.reapply('a.pager_myFlickr');
		Behaviour.reapply('a.mm_insertImageItem');
		Behaviour.reapply('a.mm_insertImageItemFlickr');
		Behaviour.reapply('a.mm_insertDocumentItem');
		Behaviour.reapply('a.mm_myLocal');
		Behaviour.reapply('a.mm_myFlickr');
		Behaviour.reapply('#mm_include_types_wrap');
		Behaviour.reapply('#mm_timeframe');
		Behaviour.reapply('#mm_user');
		Behaviour.reapply('#mm_photoset');
		Behaviour.reapply('#mm_flickrtags');
		Behaviour.reapply('#submit55');
		Behaviour.reapply('.showMediamanagerElement');
		Behaviour.reapply('.hideMediamanagerElement');
		Behaviour.reapply('.showMediamanagerElementMyFlickr');
		Behaviour.reapply('.hideMediamanagerElementMyFlickr');
		Behaviour.reapply('.iHelpMediamanager');
		Behaviour.reapply('.iHelpRemoveMediamanager');	
}

/**
 * Building new object instance of class Helper
 */
Helper = new Helper();
