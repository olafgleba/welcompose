/**
 * Project: Welcompose
 * File: wcom.update.validation.js
 *
 * Copyright (c) 2004-2005 sopic GmbH
 *
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id$
 *
 * @copyright 2004-2005 sopic GmbH
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

function validate(elem)
{	
	var url		= 'validate.js.php';
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