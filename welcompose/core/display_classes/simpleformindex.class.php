<?php

/**
 * Project: Welcompose
 * File: simpleformindex.class.php
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

// load the display interface
if (!interface_exists('Display')) {
	$path_parts = array(
		dirname(__FILE__),
		'display.interface.php'
	);
	require(implode(DIRECTORY_SEPARATOR, $path_parts));
}

/**
 * Class loader compatible to loader.php. Wrapps around constructor.
 * 
 * @param array
 * @return object
 */
function Display_SimpleFormIndex ($args)
{
	// check input
	if (!is_array($args)) {
		trigger_error('Constructor args are not an array', E_USER_ERROR);
	}
	if (!array_key_exists(0, $args)) {
		trigger_error('Constructor arg project does not exist', E_USER_ERROR);
	}
	if (!array_key_exists(1, $args)) {
		trigger_error('Constructor arg page does not exist', E_USER_ERROR);
	}

	return new Display_SimpleFormIndex($args[0], $args[1]);
}

class Display_SimpleFormIndex implements Display {
	
	/**
	 * Reference to base class
	 *
	 * @var object
	 */
	public $base = null;
	
	/**
	 * Reference to session class
	 *
	 * @var object
	 */
	public $session = null;
	
	/**
	 * Reference to captcha class
	 * 
	 * @var object
	 */
	public $captcha = null;
	
	/**
	 * Container for project information
	 * 
	 * @var array
	 */
	protected $_project = array();
	
	/**
	 * Container for page information
	 * 
	 * @var array
	 */
	protected $_page = array();
	
	/**
	 * Container for simple form information
	 * 
	 * @var array
	 */
	protected $_simple_form = array();
	
	/**
	 * Set appropriate charset
	 * 
	 * @var string
	 */
	protected $_charset = 'utf-8';
	
	/**
	 * Set appropriate mime type for plain text mails
	 * 
	 * @var string
	 */
	protected $_mime_type = 'text/plain';
	
/**
 * Creates new instance of display driver. Takes an array
 * with the project information as first argument, an array
 * with the information about the current page as second
 * argument.
 * 
 * @param array Project information
 * @param array Page information
 */
public function __construct($project, $page)
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
	if (!is_array($project)) {
		throw new Display_SimpleFormIndexException("Input for parameter project is expected to be an array");
	}
	if (!is_array($page)) {
		throw new Display_SimpleFormIndexException("Input for parameter page is expected to be an array");
	}
	
	// load session class
	$this->session = load('Base:Session');
	
	// assign project, page info to class properties
	$this->_project = $project;
	$this->_page = $page;
	
	// get simple form
	$SIMPLEFORM = load('Content:SimpleForm');
	$this->_simple_form = $SIMPLEFORM->selectSimpleForm(WCOM_CURRENT_PAGE);
	
	// assign simple form to smarty
	$this->base->utility->smarty->assign('simple_form', $this->_simple_form);
	
	// load captcha class
	$this->captcha = load('Utility:Captcha');
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
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
	// make sure that configured form type is valid
	if (!preg_match(WCOM_REGEX_CUSTOM_FORM_TYPE, $this->_simple_form['type'])) {
		throw new Display_SimpleFormIndexException('Invalid form type configured');
	}
	
	// prepare name of the form render method
	$render_method = 'render'.$this->_simple_form['type'];
	
	// test if renderer method is callable
	if (!method_exists($this, $render_method)) {
		throw new Display_SimpleFormIndexException('Configured render method does not exist');
	}
	
	// call form render method
	return $this->$render_method();
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
	$FORM = $this->base->utility->loadQuickForm('simpleform', 'post', 
		array('accept-charset' => 'utf-8','action' => $this->getLocationSelf(true)));
		
	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	$FORM->addRecursiveFilter('strip_tags');
	
	// textfield for name
	$name = $FORM->addElement('text', 'name', 
		array('id' => 'simple_form_name', 'maxlength' => 255, 'class' => 'ftextfield'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a name'));
	
	// textfield for email
	$email = $FORM->addElement('text', 'email', 
		array('id' => 'simple_form_email', 'maxlength' => 255, 'class' => 'ftextfield'),
		array('label' => gettext('E-mail'))
		);
	$email->addRule('required', gettext('Please enter an e-mail address'));
	$email->addRule('regex', gettext('Please enter a valid e-mail address'), WCOM_REGEX_EMAIL);		
	
	// textfield for homepage
	$homepage = $FORM->addElement('text', 'homepage', 
		array('id' => 'simple_form_homepage', 'maxlength' => 255, 'class' => 'ftextfield'),
		array('label' => gettext('Homepage'))
		);
	$homepage->addRule('regex', gettext('Please enter a valid website URL'), WCOM_REGEX_URL);
	
	// textarea for message
	$message = $FORM->addElement('textarea', 'message', 
		array('id' => 'simple_form_message', 'cols' => 30, 'rows' => 6, 'class' => 'ftextarea'),
		array('label' => gettext('Message'))
		);
	$message->addRule('required', gettext('Please enter a message'));
	
	// textfield for captcha if the captcha is enabled
	if ($this->_simple_form['use_captcha'] != 'no') {
		$_qf_captcha = $FORM->addElement('text', '_qf_captcha', 
			array('id' => 'simple_form_captcha', 'maxlength' => 255, 'class' => 'ftextfield'),
			array('label' => gettext('Captcha text'))
			);
		$_qf_captcha->addRule('required', gettext('Please enter the captcha text'));
		$_qf_captcha->addRule('eq', gettext('Invalid captcha text entered'), $this->captcha->captchaValue());	
	}	
	
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'fsubmit', 'value' => gettext('Send'))
		);
	
	// test if the form validates. if it validates, process it and
	// skip the rest of the page
	if ($FORM->validate()) {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// prepare & assign form data
		$form_data = array(
			'name' => $name->getValue(),
			'email' => $email->getValue(),
			'homepage' => $homepage->getValue(),
			'message' => $message->getValue(),
			'now' => mktime()
		);
		$this->base->utility->smarty->assign('form_data', $form_data);
		
		// fetch mail body
		$body = $this->base->utility->smarty->fetch($this->getPersonalMailTemplateName(),
			md5($_SERVER['REQUEST_URI']));
		
		// prepare sending information
		$recipients = $this->_simple_form['email_to'];
		
		// prepare From: address
		$from = (($this->_simple_form['email_from'] == 'sender@simpleform.wcom') ?
			$email->getValue() : $this->_simple_form['email_from']);
		$from = preg_replace('=((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*=i',
			null, $from);
		
		// headers
		$headers = array();
		$headers['From'] = $from;
		$headers['Subject'] = $this->_simple_form['email_subject'];
		$headers['Reply-To'] = $email->getValue();
		$headers['Content-Type'] = ''.$this->_mime_type.'; charset='.$this->_charset.'';
		
		// prepare params
		$params = array();
		$params = sprintf('-f %s', $from);
		
		// load PEAR::Mail
		require_once('Mail.php');
		$MAIL = Mail::factory('mail', $params);
		
		// send mail
		if ($MAIL->send($recipients, $headers, $body)) {
			// add response to session
			$_SESSION['form_submitted'] = 1;
			
			// save session
			$this->session->save();
			
			// redirect
			header($this->getRedirectLocationSelf());
			exit;
		} else {
			throw new Display_SimpleFormIndexException("E-mail couldn't be sent");
		}
	}
	
	// render form
	$renderer = $this->base->utility->loadQuickFormSmartyRenderer();
	
	// fetch {function} template to set
	// required/error markup on each form fields
	$this->base->utility->smarty->fetch(dirname(__FILE__).'/../../admin/quickform.tpl');

	// assign the form to smarty
	$this->base->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
	
	// generate captcha if required
	if ($this->_simple_form['use_captcha'] != 'no') {
		// captcha generation
		if ($this->_simple_form['use_captcha'] == 'image') {
			// generate image captcha
			$captcha = $this->captcha->createCaptcha('image');
			
			// let's tell the template that the captcha is an image
			$this->base->utility->smarty->assign('captcha_type', 'image');
		} elseif ($this->_simple_form['use_captcha'] == 'numeral') { 
			// generate numeral captcha
			$captcha = $this->captcha->createCaptcha('numeral');
			
			// let's tell the template that the captcha is an numeral captcha 
			$this->base->utility->smarty->assign('captcha_type', 'numeral');
		}
		$this->base->utility->smarty->assign('captcha', $captcha);
	}
	
	// empty $_SESSION
	if (!empty($_SESSION['form_submitted'])) {
		$_SESSION['form_submitted'] = '';
	}
	
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
		gettext('Mr.') => gettext('Mr.'),
		gettext('Mrs.') => gettext('Mrs.')
	);
	
	$FORM = $this->base->utility->loadQuickForm('simpleform', 'post', 
		array('accept-charset' => 'utf-8','action' => $this->getLocationSelf(true)));
		
	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	$FORM->addRecursiveFilter('strip_tags');
	
	// select for salutation
	$salutation = $FORM->addElement('select', 'salutation',
	 	array('id' => 'simple_form_salutation', 'class' => 'fselect'),
		array('label' => gettext('Salutation'), 'options' => $salutations)
		);
	$salutation->addRule('required', gettext('Please select a salutation'));
	
	// textfield for first_name
	$first_name = $FORM->addElement('text', 'first_name', 
		array('id' => 'simple_form_first_name', 'maxlength' => 255, 'class' => 'ftextfield'),
		array('label' => gettext('First name'))
		);
	$first_name->addRule('required', gettext('Please enter your first name'));
	
	// textfield for last_name
	$last_name = $FORM->addElement('text', 'last_name', 
		array('id' => 'simple_form_last_name', 'maxlength' => 255, 'class' => 'ftextfield'),
		array('label' => gettext('Last name'))
		);
	$last_name->addRule('required', gettext('Please enter your last name'));
	
	// textfield for address
	$address = $FORM->addElement('text', 'address', 
		array('id' => 'simple_form_address', 'maxlength' => 255, 'class' => 'ftextfield'),
		array('label' => gettext('Address'))
		);

	// textfield for location
	$location = $FORM->addElement('text', 'location', 
		array('id' => 'simple_form_location', 'maxlength' => 255, 'class' => 'ftextfield'),
		array('label' => gettext('ZIP/City'))
		);

	// checkbox for call_back
	$call_back = $FORM->addElement('checkbox', 'call_back',
		array('id' => 'simple_form_call_back', 'class' => 'fcheckbox'),
		array('label' => gettext('Please call me'))
		);
	$call_back->addRule('regex', gettext('The field call_back accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// textfield for phone
	$phone = $FORM->addElement('text', 'phone', 
		array('id' => 'simple_form_phone', 'maxlength' => 255, 'class' => 'ftextfield'),
		array('label' => gettext('Phone'))
		);
	$phone->addRule('regex', gettext('Please enter a valid phone number'), WCOM_REGEX_PHONE_NUMBER);	
	if ($call_back->getValue() == 1) {
		$phone->addRule('required', gettext('Please enter your phone number'));
	}
	
	// textfield for email
	$email = $FORM->addElement('text', 'email', 
		array('id' => 'simple_form_email', 'maxlength' => 255, 'class' => 'ftextfield'),
		array('label' => gettext('E-mail'))
		);
	$email->addRule('required', gettext('Please enter an e-mail address'));
	$email->addRule('regex', gettext('Please enter a valid e-mail address'), WCOM_REGEX_EMAIL);	

	// textarea for message
	$message = $FORM->addElement('textarea', 'message', 
		array('id' => 'simple_form_message', 'cols' => 30, 'rows' => 6, 'class' => 'ftextarea'),
		array('label' => gettext('Message'))
		);
	$message->addRule('required', gettext('Please enter a message'));
	
	// textfield for captcha if the captcha is enabled
	if ($this->_simple_form['use_captcha'] != 'no') {
		$_qf_captcha = $FORM->addElement('text', '_qf_captcha', 
			array('id' => 'simple_form_captcha', 'maxlength' => 255, 'class' => 'ftextfield'),
			array('label' => gettext('Captcha text'))
			);
		$_qf_captcha->addRule('required', gettext('Please enter the captcha text'));
		$_qf_captcha->addRule('eq', gettext('Invalid captcha text entered'), $this->captcha->captchaValue());	
	}
	
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'fsubmit', 'value' => gettext('Send'))
		);		
	
	// test if the form validates. if it validates, process it and
	// skip the rest of the page
	if ($FORM->validate()) {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// prepare & assign form data
		$form_data = array(
			'salutation' => $salutation->getValue(),
			'first_name' => $first_name->getValue(),
			'last_name' => $last_name->getValue(),
			'email' => $email->getValue(),
			'address' => $address->getValue(),
			'location' => $location->getValue(),
			'message' => $message->getValue(),
			'call_back' => $call_back->getValue(),
			'phone' => $phone->getValue(),
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
		$headers['From'] = (($this->_simple_form['email_from'] == 'sender@simpleform.wcom') ?
			$email->getValue() : $this->_simple_form['email_from']);
		$headers['Subject'] = $this->_simple_form['email_subject'];
		$headers['Reply-To'] = $email->getValue();
		$headers['Content-Type'] = ''.$this->_mime_type.'; charset='.$this->_charset.'';
		
		// send mail
		if ($MAIL->send($recipients, $headers, $body)) {
			// add response to session
			$_SESSION['form_submitted'] = 1;
			
			// save session
			$this->session->save();
			
			// redirect
			header($this->getRedirectLocationSelf());
			exit;
		} else {
			throw new Display_SimpleFormIndexException("E-mail couldn't be sent");
		}
	}
	
	// render form
	$renderer = $this->base->utility->loadQuickFormSmartyRenderer();
	
	// fetch {function} template to set
	// required/error markup on each form fields
	$this->base->utility->smarty->fetch(dirname(__FILE__).'/../../admin/quickform.tpl');

	// assign the form to smarty
	$this->base->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
	
	// generate captcha if required
	if ($this->_simple_form['use_captcha'] != 'no') {
		// captcha generation
		if ($this->_simple_form['use_captcha'] == 'image') {
			// generate image captcha
			$captcha = $this->captcha->createCaptcha('image');
			
			// let's tell the template that the captcha is an image
			$this->base->utility->smarty->assign('captcha_type', 'image');
		} elseif ($this->_simple_form['use_captcha'] == 'numeral') { 
			// generate numeral captcha
			$captcha = $this->captcha->createCaptcha('numeral');
			
			// let's tell the template that the captcha is an numeral captcha 
			$this->base->utility->smarty->assign('captcha_type', 'numeral');
		}
		$this->base->utility->smarty->assign('captcha', $captcha);
	}
	
	// empty $_SESSION
	if (!empty($_SESSION['form_submitted'])) {
		$_SESSION['form_submitted'] = '';
	}
	
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
	return "wcom:simple_form_index.".WCOM_CURRENT_PAGE;
}

/**
 * Returns the name of the personal mail template.
 * 
 * @return string
 */
public function getPersonalMailTemplateName ()
{
	return "wcom:simple_form_personal_form_mail.".WCOM_CURRENT_PAGE;
}

/**
 * Returns the name of the business mail template.
 * 
 * @return string
 */
public function getBusinessMailTemplateName ()
{
	return "wcom:simple_form_business_form_mail.".WCOM_CURRENT_PAGE;
}

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems) with the
 * Location: header prepended.
 * 
 * @return string
 */
public function getRedirectLocationSelf ()
{
	return "Location: ".$this->getLocationSelf(true);
}

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems). Already
 * encoded ampersands will be removed if the optional
 * parameter remove_amps is set to true.
 * 
 * @param bool Remove encoded ampersands
 * @return string
 */
public function getLocationSelf ($remove_amps = false)
{
	// prepare params
	$params = array(
		'project' => $this->_project['name_url'],
		'page_id' => $this->_page['id'],
		'action' => 'Index'
	);
	
	// send params to url generator. we hope to get back something useful.
	$URLGENERATOR = load('Utility:UrlGenerator');
	$url = $URLGENERATOR->generateInternalLink($params, $remove_amps);
	
	// return the url or a hash mark if the url is empty 
	if (empty($url)) {
		return '#';
	} else {
		return $url;
	}
}

/**
 * Returns appropriate header
 * For example this should be used to asure valid feed output
 * 
 * @return string
 */
public function setTemplateHeader ()
{
	return false;
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

class Display_SimpleFormIndexException extends Exception { }

?>