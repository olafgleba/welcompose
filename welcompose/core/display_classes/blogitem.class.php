<?php

/**
 * Project: Welcompose
 * File: blogitem.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
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
function Display_BlogItem ($args)
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

	return new Display_BlogItem($args[0], $args[1]);
}

class Display_BlogItem implements Display {
	
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
	 * Container for posting
	 * 
	 * @var array
	 */
	protected $_posting = array();
	
	/** 
	 * Container for community settings
	 *
	 * @var array
	 */
	protected $_settings = array();
	
/**
 * Creates new instance of display driver. Takes an array
 * with the project information as first argument, an array
 * with the information about the current page as second
 * argument.
 * 
 * @throws Display_BlogItemException
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
		throw new Display_BlogItemException("Input for parameter project is expected to be an array");
	}
	if (!is_array($page)) {
		throw new Display_BlogItemException("Input for parameter page is expected to be an array");
	}
	
	// start session class
	$this->session = load('Base:Session');
	
	// assign project, page info to class properties
	$this->_project = $project;
	$this->_page = $page;
	
	// get posting -- if 
	$BLOGPOSTING = load('Content:BlogPosting');
	$posting_id = $BLOGPOSTING->resolveBlogPosting();
	$this->_posting = $BLOGPOSTING->selectBlogPosting($posting_id);
	
	// assign blog posting to smarty
	$this->base->utility->smarty->assign('blog_posting', $this->_posting);
	
	// get community settings
	$SETTINGS = load('Community:Settings');
	$this->_settings = $SETTINGS->getSettings();
	
	// load captcha class
	$this->captcha = load('Utility:Captcha');
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
	return new Display_BlogItem($project, $page);
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
	// only create form if commenting is allowed
	if ($this->_posting['comments_enable']) {
		// start new HTML_QuickForm
		$FORM = $this->base->utility->loadQuickForm('blog_comment', 'post',
			$this->getLocationSelf());
		
		// hidden for posting
		$FORM->addElement('hidden', 'posting');
		$FORM->applyFilter('posting', 'trim');
		$FORM->applyFilter('posting', 'strip_tags');
		$FORM->addRule('posting', gettext('Posting is not expected to be empty'), 'required');
		$FORM->addRule('posting', gettext('Posting is expected to be numeric'), 'numeric');
		
		// textfield for name
		$FORM->addElement('text', 'name', gettext('Name'),
			array('id' => 'comment_name', 'maxlength' => 255, 'class' => 'w300'));
		$FORM->applyFilter('name', 'trim');
		$FORM->applyFilter('name', 'strip_tags');
		$FORM->addRule('name', gettext('Please enter a name'), 'required');
		
		// textfield for email
		$FORM->addElement('text', 'email', gettext('E-mail'),
			array('id' => 'comment_email', 'maxlength' => 255, 'class' => 'w300'));
		$FORM->applyFilter('email', 'trim');
		$FORM->applyFilter('email', 'strip_tags');
		$FORM->addRule('email', gettext('Please enter a valid e-mail address'), 'email');
		
		// textfield for homepage
		$FORM->addElement('text', 'homepage', gettext('Homepage'),
			array('id' => 'comment_homepage', 'maxlength' => 255, 'class' => 'w300'));
		$FORM->applyFilter('homepage', 'trim');
		$FORM->applyFilter('homepage', 'strip_tags');
		$FORM->addRule('homepage', gettext('Please enter a valid website URL'), 'regex',
			WCOM_REGEX_URL);
		
		// terxtarea for message
		$FORM->addElement('textarea', 'comment', gettext('Comment'),
			array('id' => 'comment_comment', 'cols' => 30, 'rows' => 6, 'class' => 'w300h200'));
		$FORM->applyFilter('comment', 'trim');
		$FORM->applyFilter('comment', 'strip_tags');
		$FORM->addRule('comment', gettext('Please enter a comment'), 'required');
		
		// textfield for captcha if the captcha is enabled
		if ($this->_settings['blog_comment_use_captcha'] != 'no') {
			$FORM->addElement('text', '_qf_captcha', gettext('Captcha text'),
				array('id' => 'simple_form_captcha', 'maxlength' => 255, 'class' => 'w300'));
			$FORM->applyFilter('_qf_captcha', 'trim');
			$FORM->applyFilter('_qf_captcha', 'strip_tags');
			$FORM->addRule('_qf_captcha', gettext('Please enter the captcha text'), 'required');
			$FORM->addRule('_qf_captcha', gettext('Invalid captcha text entered'), 'is_equal',
				$this->captcha->captchaValue());
		}
		
		// submit button
		$FORM->addElement('submit', 'submit', gettext('Send'),
			array('class' => 'submitbutton'));
		
		// set defaults
		$FORM->setDefaults(array(
			'posting' => (int)$this->_posting['id']
		));
		
		// test if the form validates. if it validates, process it and
		// skip the rest of the page
		if ($FORM->validate()) {
			// freeze the form
			$FORM->freeze();
			
			// load Community_BlogComment class
			$BLOGCOMMENT = load('Community:BlogComment');
			
			// load Application_TextConverter class
			$TEXTCONVERTER = load('Application:TextConverter');
			
			// 
			// Well, once in the future we're going to execute the spam checks here.
			// Once. In the future.
			// 
			
			// prepare sql data
			$sqlData = array();
			$sqlData['posting'] = $this->_posting['id'];
			$sqlData['user'] = ((WCOM_CURRENT_USER_ANONYMOUS !== true) ? WCOM_CURRENT_USER : null); 
			$sqlData['status'] = $this->_settings['blog_comment_default_status'];
			$sqlData['name'] = $FORM->exportValue('name');
			$sqlData['email'] = $FORM->exportValue('email');
			$sqlData['homepage'] = $FORM->exportValue('homepage');
			$sqlData['content_raw'] = $FORM->exportValue('comment');
			$sqlData['content'] = $FORM->exportValue('comment');
			$sqlData['original_raw'] = $FORM->exportValue('comment');
			$sqlData['original'] = $FORM->exportValue('comment');
			$sqlData['text_converter'] = null;
			$sqlData['edited'] = "0";
			$sqlData['date_added'] = date('Y-m-d H:i:s');
			
			// apply text converter if required
			if (!empty($this->_settings['blog_comment_text_converter'])) {
				$sqlData['content'] = $TEXTCONVERTER->applyTextConverter($this->_settings['blog_comment_text_converter'],
					$FORM->exportValue('comment'));
				$sqlData['original'] = $TEXTCONVERTER->applyTextConverter($this->_settings['blog_comment_text_converter'],
					$FORM->exportValue('comment'));
				$sqlData['text_converter'] = $this->_settings['blog_comment_text_converter'];
			}
			
			// test sql data for pear errors
			$HELPER = load('Utility:Helper');
			$HELPER->testSqlDataForPearErrors($sqlData);
			
			// insert it
			try {
				// begin transaction
				$this->base->db->begin();
				
				// execute operation
				$BLOGCOMMENT->addBlogComment($sqlData);
				
				// commit
				$this->base->db->commit();
			} catch (Exception $e) {
				// do rollback
				$this->base->db->rollback();

				// re-throw exception
				throw $e;
			}
			
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
		
		// render form
		$renderer = $this->base->utility->loadQuickFormSmartyRenderer();
		$renderer->setRequiredTemplate($this->getRequiredTemplate());
	
		// remove attribute on form tag for XHTML compliance
		$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
	
		$FORM->accept($renderer);

		// assign the form to smarty
		$this->base->utility->smarty->assign('form', $renderer->toArray());
		
		// generate captcha if required
		if ($this->_settings['blog_comment_use_captcha'] != 'no') {
			// captcha generation
			$captcha = null;
			if ($this->_settings['blog_comment_use_captcha'] == 'image') {
				// generate image captcha
				$captcha = $this->captcha->createCaptcha('image');
				
				// let's tell the template that the captcha is an image
				$this->base->utility->smarty->assign('captcha_type', 'image');
			} elseif ($this->_settings['blog_comment_use_captcha'] == 'numeral') { 
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
	return "wcom:blog_item.".WCOM_CURRENT_PAGE;
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
	return "Location: ".$this->getLocationSelf();
}

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems).
 * 
 * @return string
 */
public function getLocationSelf ()
{
	// prepare params
	$params = array(
		'project' => $this->_project['name_url'],
		'page_id' => $this->_page['id'],
		'action' => 'Item',
		'posting_id' => $this->_posting['id']
	);
	
	// send params to url generator. we hope to get back something useful.
	$URLGENERATOR = load('Utility:UrlGenerator');
	$url = $URLGENERATOR->generateInternalLink($params);
	
	// return the url or a hash mark if the url is empty 
	if (empty($url)) {
		return '#';
	} else {
		return $url;
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

class Display_BlogItemException extends Exception { }

?>