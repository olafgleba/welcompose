/**
 * Project: Oak
 * File: oak.xhr.js
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
 * @copyright 2004-2005 sopic GmbH
 * @author Olaf Gleba
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */
 
/**
 * Connect by XHR to intergrate external navigation files
 *
 * @param {string} url path
 * @param {string} target div to fill
 */
function xhrNav(url, target)
{
	try {	
		//$(target).innerHTML = '';
		if (window.XMLHttpRequest) {
			req = new XMLHttpRequest();
	 	} else if (window.ActiveXObject) {
			req = new ActiveXObject("Microsoft.XMLHTTP");
	  	}
	 	if (req != undefined) {
			req.open('GET', url, true);
			req.onreadystatechange = function() {xhrNavDone(url, target);};
			req.send('');
	  	}
	} catch (e) {
		applyError(e);
	}
}  

/**
 * Process XHR to intergrate external navigation files
 * used: func xhr()
 *
 * @param {string} url path
 * @param {string} target src div
 */
function xhrNavDone(url, target)
{  
	try {
		if (req.readyState == 4) {
			if (req.status == 200) {
				Element.hide($('topsubnavconstatic'));
				Element.update(target, req.responseText);
				Behaviour.apply();
			} else {
	  			throw new devError(req.statusText);
			}
		}
	} catch (e) {
		applyError(e);
	}
}





