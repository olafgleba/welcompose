/**
 * Project: Oak
 * File: oak.core.ie.js
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
* Duplicated Function related to oak.core.js
* Single comments indicates the concerning lines
*/


/**
 * Construct a new Help object
 * @class This is the basic Help class 
 * @constructor 
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Help ()
{
	try {
		/**
		 * Get new XMLHttpRequest Object by private function
		 */
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
Help.prototype.process = Help_process;
Help.prototype.setCorrespondingFocus = Help_setCorrespondingFocus;

/**
 * Implements method of prototype class Help
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class; can be empty/not set (= level 1)
 * @throws applyError on exception
 */
function Help_show (elem, level)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'for';
		this.level = level;
		
		this.processId = this.elem.parentNode.parentNode.attributes[this.attr].value;
		
		switch (this.level) {
			case '2' :
					this.formId = this.elem.parentNode.parentNode.parentNode.parentNode.parentNode.attributes['id'].value;
					this.elem.className = this.helpClassRemoveLevelTwo;
				break;
			default :
					this.formId = this.elem.parentNode.parentNode.parentNode.parentNode.attributes['id'].value;
					this.elem.className = this.helpClassRemove;	
		}
	
		this.target = this.processId;
	
		this.fetch = this.processId.replace(/(_(\d+))/, '');	
		if (this.fetch) {
			this.processId = this.fetch;
		}
		this.url = this.parseHelpUrl + '?page=' + this.formId + '_' + this.processId;
			
		if (typeof this.req != 'undefined') {
		
			var _url		= this.url;
			var _target		= this.target;
		
			_req.open('GET', _url, true);
			_req.onreadystatechange = function () { Help.process(_url,_target);};
			_req.send('');
		}
		
		Help.setCorrespondingFocus(this.elem, this.attr);
		Element.update(this.elem, this.helpHtmlHide);
		Behaviour.apply();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help
 * @param {string} elem Actual element
 * @param {string} level Wich depth of implementation to apply css class; can be empty/non set (= level 1)
 * @throws applyError on exception
 */
function Help_hide (elem, level)
{
	try {
		// properties
		this.elem = elem;
		this.attr = 'for';
		this.level = level;
	
		this.processId = this.elem.parentNode.parentNode.attributes[this.attr].value;
		this.processIdAfter = $(this.processId).parentNode.nextSibling;
		
		switch (this.level) {
			case '2' :
					this.elem.className = this.helpClassLevelTwo;
				break;
			default :
					this.elem.className = this.helpClass;	
		}
		Effect.Fade(this.processIdAfter,{duration: 0.5});
		Element.update(this.elem, this.helpHtmlShow);
		Behaviour.apply();
		
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Help
 * @param {string} url path
 * @param {string} target Wich layer div should be used
 * @throws applyError on exception
 * @throws DevError on condition
 */
function Help_process (url, target)
{  
	try {
		if (_req.readyState == 4) {
			if (_req.status == 200) {
				new Insertion.After($(target).parentNode, _req.responseText);				
				var target_after = $(target).parentNode.nextSibling;
				Element.hide(target_after);
				Effect.Appear(target_after, {duration: 0.8});
			} else {
	  			throw new DevError(_req.statusText);
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
	this.inst = elem.parentNode.parentNode.attributes[attr].value;
	$(this.inst).focus();
}


/**
 * Building new instance for class Help
 */
Help = new Help();

