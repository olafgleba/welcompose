<?php

/**
 * Project: Welcompose
 * File: simpleguestbookindex.class.php
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
function Display_SimpleGuestbookIndex ($args)
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

	return new Display_SimpleGuestbookIndex($args[0], $args[1]);
}

class Display_SimpleGuestbookIndex implements Display {
	
	/**
	 * Singleton
	 *
	 * @var object
	 */
	public static $instance = null;
	
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
	 * Container for simple guestbook
	 * 
	 * @var array
	 */
	protected $_simple_guestbook = array();
	
	/**
	 * Set appropriate charset
	 * 
	 * @var string
	 */
	protected $_charset = 'utf-8';
	
	/**
	 * Set appropriate mime type
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
 * @throws Display_SimpleGuestbookException
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
		throw new Display_SimpleGuestbookException("Input for parameter project is expected to be an array");
	}
	if (!is_array($page)) {
		throw new Display_SimpleGuestbookException("Input for parameter page is expected to be an array");
	}
	
	// start session class
	$this->session = load('Base:Session');
	
	// assign project, page info to class properties
	$this->_project = $project;
	$this->_page = $page;
	
	// load simple book class
	$SIMPLEGUESTBOOK = load('Content:SimpleGuestbook');
	
	// get simple guestbook
	$this->_simple_guestbook = $SIMPLEGUESTBOOK->selectSimpleGuestbook(WCOM_CURRENT_PAGE);
	
	// assign simple page to smarty
	$this->base->utility->smarty->assign('simple_guestbook', $this->_simple_guestbook);

	
	// load captcha class
	$this->captcha = load('Utility:Captcha');
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
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
public static function instance($project, $page)
{
	return new Display_SimpleGuestbookIndex($project, $page);
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
	// Define and render form only if option allow_entry is not null
	if (!empty($this->_simple_guestbook['allow_entry'])) {
			
		// start new HTML_QuickForm
		$FORM = $this->base->utility->loadQuickForm('simpleguestbookentry', 'post', 
			array('accept-charset' => 'utf-8','action' => $this->getLocationSelf(true)));
			
		// apply filters to all fields
		$FORM->addRecursiveFilter('trim');
		$FORM->addRecursiveFilter('strip_tags');
	
		// hidden for book
		$book = $FORM->addElement('hidden', 'book', array('id' => 'book'));
		$book->addRule('required', gettext('book is not expected to be empty'));
		$book->addRule('regex', gettext('book is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
		// textfield for name
		$name = $FORM->addElement('text', 'name', 
			array('id' => 'guestbook_entry_name', 'maxlength' => 255, 'class' => 'ftextfield'),
			array('label' => gettext('Name'))
			);
		$name->addRule('required', gettext('Please enter a name'));
	
		// textfield for email
		$email = $FORM->addElement('text', 'email', 
			array('id' => 'guestbook_entry_email', 'maxlength' => 255, 'class' => 'ftextfield'),
			array('label' => gettext('E-mail'))
			);
		$email->addRule('regex', gettext('Please enter a valid e-mail address'), WCOM_REGEX_EMAIL);
	
		// textfield for subject
		$subject = $FORM->addElement('text', 'subject', 
			array('id' => 'guestbook_entry_subject', 'maxlength' => 255, 'class' => 'ftextfield'),
			array('label' => gettext('Subject'))
			);
	
		// textarea for message content
		$content = $FORM->addElement('textarea', 'content', 
			array('id' => 'guestbook_entry_content', 'cols' => 30, 'rows' => 6, 'class' => 'ftextarea'),
			array('label' => gettext('Message'))
			);
		$content->addRule('required', gettext('Please enter a message'));
	
		// textfield for captcha if the captcha is enabled
		if ($this->_simple_guestbook['use_captcha'] != 'no') {
			$_qf_captcha = $FORM->addElement('text', '_qf_captcha', 
				array('id' => 'simple_guestbook_captcha', 'maxlength' => 255, 'class' => 'ftextfield'),
				array('label' => gettext('Captcha text'))
				);
			$_qf_captcha->addRule('required', gettext('Please enter the captcha text'));
			$_qf_captcha->addRule('eq', gettext('Invalid captcha text entered'), $this->captcha->captchaValue());				
		}
	
		// submit button
		$submit = $FORM->addElement('submit', 'submit', 
			array('class' => 'fsubmit', 'value' => gettext('Send'))
			);
	
		// set defaults
		$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
			'book' => (int)$this->_simple_guestbook['id']
		)));	
	
		// test if the form validates. if it validates, process it and
		// skip the rest of the page
		if ($FORM->validate()) {
			// freeze the form
			$FORM->toggleFrozen(true);
	 	
			// prepare sql data
			$sqlData = array();
			$sqlData['book'] = $this->_simple_guestbook['id'];
			$sqlData['user'] = ((WCOM_CURRENT_USER_ANONYMOUS !== true) ? WCOM_CURRENT_USER : null);
			$sqlData['name'] = $name->getValue();
			$sqlData['email'] = $email->getValue();
			$sqlData['subject'] = $subject->getValue();
			$sqlData['content'] = $content->getValue();
			$sqlData['content_raw'] = $content->getValue();
			$sqlData['text_converter'] = null;
			$sqlData['date_added'] = date('Y-m-d H:i:s');
		
			// load Application_TextConverter class
			$TEXTCONVERTER = load('Application:TextConverter');
		
			// apply text converter if required
			if (!empty($this->_simple_guestbook['text_converter'])) {
				$sqlData['content'] = $TEXTCONVERTER->applyTextConverter($this->_simple_guestbook['text_converter'],
					$content->getValue());				
				$sqlData['text_converter'] = $this->_simple_guestbook['text_converter'];
			}
		
			// test sql data for pear errors
			$HELPER = load('Utility:Helper');
			$HELPER->testSqlDataForPearErrors($sqlData);
		
			// insert it
			try {
				// begin transaction
				$this->base->db->begin();
			
				// get simple guestbook entries class
				$SIMPLEGUESTBOOKENTRIES = load('Content:SimpleGuestbookEntry');
			
				// execute operation
				$SIMPLEGUESTBOOKENTRIES->addSimpleGuestbookEntry($sqlData);
			
				// commit
				$this->base->db->commit();
			} catch (Exception $e) {
				// do rollback
				$this->base->db->rollback();
	
				// re-throw exception
				throw $e;
			}
		
			// send e-mail notification		
			if (!empty($this->_simple_guestbook['send_notification'])) {
					
				// prepare & assign form data
				$form_data = array(
					'book' => $this->_simple_guestbook['title'],
					'name' => $name->getValue(),
					'email' => $email->getValue(),
					'subject' => $subject->getValue(),
					'content' => $content->getValue(),
					'now' => mktime()
				);
				$this->base->utility->smarty->assign('form_data', $form_data);
		
				// fetch mail body
				$body = $this->base->utility->smarty->fetch($this->getEntryMailTemplateName(),
					md5($_SERVER['REQUEST_URI']));
		
				// prepare sending information
				$recipients = $this->_simple_guestbook['notification_email_to'];
		
				// prepare From: address
				$from = (($this->_simple_guestbook['notification_email_from'] == 'sender@simpleguestbook.wcom') ?
					$email->getValue() : $this->_simple_guestbook['notification_email_from']);
				$from = preg_replace('=((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*=i',
					null, $from);
		
				// headers
				$headers = array();
				$headers['From'] = $from;
				$headers['Subject'] = $this->_simple_guestbook['notification_email_subject'];
				$headers['Reply-To'] = $from;
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
				
					// clean the buffer
					if (!$this->base->debug_enabled()) {
						@ob_end_clean();
					}
			
					// redirect
					header($this->getRedirectLocationSelf());
					exit;
				} else {
					throw new Display_SimpleGuestbookException("Notification E-mail couldn't be sent");
				}
			} else {				
				// add response to session
				$_SESSION['form_submitted'] = 1;
		
				// redirect
				$this->session->save();

				// clean the buffer
				if (!$this->base->debug_enabled()) {
					@ob_end_clean();
				}
		
				// redirect to itself
				header($this->getRedirectLocationSelf());
				exit;
			}
		}
	
		// render form
		$renderer = $this->base->utility->loadQuickFormSmartyRenderer();
		//$renderer->setRequiredTemplate($this->getRequiredTemplate());

		// assign the form to smarty
		$this->base->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
	
		// generate captcha if required
		if ($this->_simple_guestbook['use_captcha'] != 'no') {
			// captcha generation
			$captcha = null;
			if ($this->_simple_guestbook['use_captcha'] == 'image') {
				// generate image captcha
				$captcha = $this->captcha->createCaptcha('image');
			
				// let's tell the template that the captcha is an image
				$this->base->utility->smarty->assign('captcha_type', 'image');
			} elseif ($this->_simple_guestbook['use_captcha'] == 'numeral') { 
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
	return "wcom:simple_guestbook_index.".WCOM_CURRENT_PAGE;
}

/**
 * Returns the name of the entry mail template.
 * 
 * @return string
 */
public function getEntryMailTemplateName ()
{
	return "wcom:simple_guestbook_form_mail.".WCOM_CURRENT_PAGE;
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

class Display_SimpleGuestbookException extends Exception { }

?>