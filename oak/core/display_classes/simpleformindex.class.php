<?php

/**
 * Project: Oak
 * File: simpleformindex.class.php
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
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

// load the display interface
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'display.interface.php');

class Display_SimpleFormIndex implements Display {
	
	/**
	 * Singleton
	 *
	 * @var object
	 */
	private static $instance = null;
	
	/**
	 * Reference to base class
	 *
	 * @var object
	 */
	public $base = null;
	
	/**
	 * Container for project information
	 * 
	 * @var array
	 */
	protected $_project_info = array();
	
	/**
	 * Container for page information
	 * 
	 * @var array
	 */
	protected $_page_info = array();
	
	/**
	 * Container for simple form information
	 * 
	 * @var array
	 */
	protected $_simple_form = array();
	
/**
 * Creates new instance of display driver. Takes an array
 * with the project information as first argument, an array
 * with the information about the current page as second
 * argumenht.
 * 
 * @param array Project information
 * @param array Page information
 */
public function __construct($project_info, $page_info)
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
				
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
	
	// input check
	if (!is_array($project_info)) {
		throw new Display_SimpleFormIndex("Input for parameter project_info is expected to be an array");
	}
	if (!is_array($page_info)) {
		throw new Display_SimpleFormIndex("Input for parameter page_info is expected to be an array");
	}
	
	$this->_project_info = $project_info;
	$this->_page_info = $page_info;
	
	// get simple form
	$SIMPLEFORM = load('content:simpleform');
	$this->_simple_form = $SIMPLEFORM->selectSimpleForm(OAK_CURRENT_PAGE);
}

/**
 * Loads new instance of display driver. See the constructor
 * for an argument description.
 *
 * In comparison to the constructor, it can be called using
 * call_user_func_array(). Please note that's not a singleton.
 * 
 * @param array Project information
 * @param array Page information
 * @return object New display driver instance
 */
public static function instance($project_info, $page_info)
{
	return new Display_SimpleFormIndex($project_info, $page_info);
}

/**
 * Default method that will be called from the display script
 * and has to care about the page preparation. Returns boolean
 * true on success.
 * 
 * @return bool
 */ 
public function render ()
{
	if ($this->_simple_form['type'] == 'personal') {
		return $this->renderPersonalForm();
	} elseif ($this->_simple_form['type'] == 'business') {
		return $this->renderBusinessForm();
	}
}

/**
 * Renderer for the personal form.
 * 
 * @throws Display_SimpleFormIndexException
 * @return bool
 */ 
protected function renderPersonalForm ()
{
	// start new HTML_QuickForm
	$FORM = $this->base->utility->loadQuickForm('simpleform', 'post');
	
	// textfield for name
	$FORM->addElement('text', 'name', gettext('Name'),
		array('id' => 'simple_form_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('name', 'trim');
	$FORM->applyFilter('name', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter a name'), 'required');
	
	// textfield for email
	$FORM->addElement('text', 'email', gettext('E-mail address'),
		array('id' => 'simple_form_email', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('email', 'trim');
	$FORM->applyFilter('email', 'strip_tags');
	$FORM->addRule('email', gettext('Please enter an e-mail address'), 'required');
	$FORM->addRule('email', gettext('Please enter a valid e-mail address'), 'email');
	
	// textfield for homepage
	$FORM->addElement('text', 'homepage', gettext('Homepage'),
		array('id' => 'simple_form_homepage', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('homepage', 'trim');
	$FORM->applyFilter('homepage', 'strip_tags');
	$FORM->addRule('homepage', gettext('Please enter a valid website URL'), 'regex',
		OAK_REGEX_URL);
	
	// terxtarea for message
	$FORM->addElement('textarea', 'message', gettext('Message'),
		array('id' => 'simple_form_message', 'cols' => 30, 'rows' => 6, 'class' => 'w300h200'));
	$FORM->applyFilter('message', 'trim');
	$FORM->applyFilter('message', 'strip_tags');
	$FORM->addRule('message', gettext('Please enter a message'), 'required');
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Send'),
		array('class' => 'submitbut100'));
	
	// test if the form validates. if it validates, process it and
	// skip the rest of the page
	if ($FORM->validate()) {
		// freeze the form
		$FORM->freeze();
		
		// prepare & assign form data
		$form_data = array(
			'name' => $FORM->exportValue('name'),
			'email' => $FORM->exportValue('email'),
			'homepage' => $FORM->exportValue('homepage'),
			'message' => $FORM->exportValue('message'),
			'now' => mktime()
		);
		$this->base->utility->smarty->assign('form_data', $form_data);
		
		// fetch mail body
		$body = $this->base->utility->smarty->fetch($this->getPersonalMailTemplateName(),
			md5($_SERVER['REQUEST_URI']));
		
		// load PEAR::Mail
		require_once('Mail.php');
		$MAIL = Mail::factory('mail');
		
		// prepare the rest of the email
		$recipients = $this->_simple_form['email_to'];
		
		// headers
		$headers = array();
		$headers['From'] = (($this->_simple_form['email_from'] == 'sender@simpleform.oak') ?
			$FORM->exportValue('email') : $this->_simple_form['email_from']);
		$headers['Subject'] = $this->_simple_form['email_subject'];
		
		// send mail
		if ($MAIL->send($recipients, $headers, $body)) {
			header($this->getRedirectLocationSelf());
			exit;
		} else {
			throw new Display_SimpleFormIndexException("E-mail couldn't be sent");
		}
	}
	
	// render form
	$renderer = $this->base->utility->loadQuickFormSmartyRenderer();
	$renderer->setRequiredTemplate($this->getRequiredTemplate());
	
	// remove attribute on form tag for XHTML compliance
	$FORM->removeAttribute('name');
	$FORM->removeAttribute('target');
	
	$FORM->accept($renderer);

	// assign the form to smarty
	$this->base->utility->smarty->assign('form', $renderer->toArray());
	
	return true;
}

/**
 * Renderer for the business form.
 * 
 * @throws Display_SimpleFormIndexException
 * @return bool
 */
protected function renderBusinessForm ()
{
	// prepare salutations
	$salutations = array(
		gettext('Mr.'),
		gettext('Mrs.')
	);
	
	// start new HTML_QuickForm
	$FORM = $this->base->utility->loadQuickForm('simpleform', 'post');
	
	// select for salutation
	$FORM->addElement('select', 'salutation', gettext('Salutation'), $salutations,
		array('id' => 'simple_form_salutation'));
	$FORM->applyFilter('salutation', 'trim');
	$FORM->applyFilter('salutation', 'strip_tags');
	$FORM->addRule('salutation', gettext('Please select a salutation'), 'required');
	$FORM->addRule('salutation', gettext('Salutation is out of range'), 'in_array_keys', $salutations);
	
	// textfield for first_name
	$FORM->addElement('text', 'first_name', gettext('First name'),
		array('id' => 'simple_form_first_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('first_name', 'trim');
	$FORM->applyFilter('first_name', 'strip_tags');
	$FORM->addRule('first_name', gettext('Please enter your first name'), 'required');
	
	// textfield for last_name
	$FORM->addElement('text', 'last_name', gettext('Last name'),
		array('id' => 'simple_form_last_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('last_name', 'trim');
	$FORM->applyFilter('last_name', 'strip_tags');
	$FORM->addRule('last_name', gettext('Please enter your last name'), 'required');
	
	// textfield for address
	$FORM->addElement('text', 'address', gettext('Address'),
		array('id' => 'simple_form_address', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('address', 'trim');
	$FORM->applyFilter('address', 'strip_tags');
	
	// textfield for location
	$FORM->addElement('text', 'location', gettext('ZIP/City'),
		array('id' => 'simple_form_location', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('location', 'trim');
	$FORM->applyFilter('location', 'strip_tags');
	
	// checkbox for call_back
	$FORM->addElement('checkbox', 'call_back', gettext('Please call me'), null,
		array('id' => 'simple_form_call_back', 'class' => 'checkb'));
	$FORM->applyFilter('call_back', 'trim');
	$FORM->applyFilter('call_back', 'strip_tags');
	$FORM->addRule('call_back', gettext('The field call_back accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// textfield for phone
	$FORM->addElement('text', 'phone', gettext('Phone'),
		array('id' => 'simple_form_phone', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('phone', 'trim');
	$FORM->applyFilter('phone', 'strip_tags');
	if ($FORM->exportValue('call_back') == 1) {
		$FORM->addRule('phone', gettext('Please enter your phone number'), 'required');
	}
	$FORM->addRule('phone', gettext('Please enter a valid phone number'), 'regex',
		OAK_REGEX_PHONE_NUMBER);
	
	// textfield for email
	$FORM->addElement('text', 'email', gettext('E-mail address'),
		array('id' => 'simple_form_email', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('email', 'trim');
	$FORM->applyFilter('email', 'strip_tags');
	$FORM->addRule('email', gettext('Please enter an e-mail address'), 'required');
	$FORM->addRule('email', gettext('Please enter a valid e-mail address'), 'email');
	
	// terxtarea for message
	$FORM->addElement('textarea', 'message', gettext('Message'),
		array('id' => 'simple_form_message', 'cols' => 30, 'rows' => 6, 'class' => 'w300h200'));
	$FORM->applyFilter('message', 'trim');
	$FORM->applyFilter('message', 'strip_tags');
	$FORM->addRule('message', gettext('Please enter a message'), 'required');
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Send'),
		array('class' => 'submitbut100'));
	
	// test if the form validates. if it validates, process it and
	// skip the rest of the page
	if ($FORM->validate()) {
		// freeze the form
		$FORM->freeze();
		
		// prepare & assign form data
		$form_data = array(
			'salutation' => $FORM->exportValue('salutation'),
			'first_name' => $FORM->exportValue('first_name'),
			'last_name' => $FORM->exportValue('last_name'),
			'email' => $FORM->exportValue('email'),
			'address' => $FORM->exportValue('address'),
			'location' => $FORM->exportValue('location'),
			'message' => $FORM->exportValue('message'),
			'call_back' => $FORM->exportValue('call_back'),
			'phone' => $FORM->exportValue('phone'),
			'now' => mktime()
		);
		$this->base->utility->smarty->assign('form_data', $form_data);
		
		// fetch mail body
		$body = $this->base->utility->smarty->fetch($this->getBusinessMailTemplateName(),
			md5($_SERVER['REQUEST_URI']));
		
		// load PEAR::Mail
		require_once('Mail.php');
		$MAIL = Mail::factory('mail');
		
		// prepare the rest of the email
		$recipients = $this->_simple_form['email_to'];
		
		// headers
		$headers = array();
		$headers['From'] = (($this->_simple_form['email_from'] == 'sender@simpleform.oak') ?
			$FORM->exportValue('email') : $this->_simple_form['email_from']);
		$headers['Subject'] = $this->_simple_form['email_subject'];
		
		// send mail
		if ($MAIL->send($recipients, $headers, $body)) {
			header($this->getRedirectLocationSelf());
			exit;
		} else {
			throw new Display_SimpleFormIndexException("E-mail couldn't be sent");
		}
	}
	
	// render form
	$renderer = $this->base->utility->loadQuickFormSmartyRenderer();
	$renderer->setRequiredTemplate($this->getRequiredTemplate());
	
	// remove attribute on form tag for XHTML compliance
	$FORM->removeAttribute('name');
	$FORM->removeAttribute('target');
	
	$FORM->accept($renderer);

	// assign the form to smarty
	$this->base->utility->smarty->assign('form', $renderer->toArray());
	
	return true;
}

/**
 * Returns the cache mode for the current template.
 * 
 * @return int
 */
public function getMainTemplateCacheMode ()
{
	return 0;
}

/**
 * Returns the cache lifetime of the current template.
 * 
 * @return int
 */
public function getMainTemplateCacheLifetime ()
{
	return 0;
}

/** 
 * Returns the name of the current template.
 * 
 * @return string
 */ 
public function getMainTemplateName ()
{
	return "oak:simple_form_index.".OAK_CURRENT_PAGE;
}

/**
 * Returns the name of the personal mail template.
 * 
 * @return string
 */
public function getPersonalMailTemplateName ()
{
	return "oak:simple_form_personal_form_mail.".OAK_CURRENT_PAGE;
}

/**
 * Returns the name of the business mail template.
 * 
 * @return string
 */
public function getBusinessMailTemplateName ()
{
	return "oak:simple_form_business_form_mail.".OAK_CURRENT_PAGE;
}

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems).
 * 
 * @return string
 */
public function getRedirectLocationSelf ()
{
	if ($this->_page['index_page']) {
		return sprintf("Location: index.php?page=%u&action=index", $this->_page['id']);
	} else {
		return 'index.php';
	}
}

/**
 * Returns QuickForm template to indicate required field.
 * 
 * @return string
 */
public function getRequiredTemplate ()
{
	$tpl = '
		{if $error}
			{$label}<span style="color:red;">*</span>
		{else}
			{if $required}
				{$label}*
			{else}
				{$label}
			{/if}      
		{/if}
	';
	
	return $tpl;
}

/**
 * Returns information whether to skip authentication
 * or not.
 * 
 * @return bool
 */
public function skipAuthentication ()
{
	return false;
}

// end of class
}

class Display_SimplePageIndexException extends Exception { }

?>