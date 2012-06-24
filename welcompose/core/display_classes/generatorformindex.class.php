<?php

/**
 * Project: Welcompose
 * File: generatorformindex.class.php
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
 * @author Andreas Ahlenstorf, Olaf Gleba
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
function Display_GeneratorFormIndex ($args)
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

	return new Display_GeneratorFormIndex($args[0], $args[1]);
}

class Display_GeneratorFormIndex implements Display {
	
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
	 * Container for generator form information
	 * 
	 * @var array
	 */
	protected $_generator_form = array();
	
	/**
	 * Container for generator form fields
	 *
	 * @var array
	 */
	protected $_generator_form_fields = array();

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
	
	// get generator form
	$GENERATORFORM = load('Content:GeneratorForm');
	$this->_generator_form = $GENERATORFORM->selectGeneratorForm(WCOM_CURRENT_PAGE);
	
	// assign simple form to smarty
	$this->base->utility->smarty->assign('generator_form', $this->_generator_form);
	
	// get generator form fields
	$GENERATORFORMFIELDS = load('Content:GeneratorFormField');
	$select_params = array(
		'form' => WCOM_CURRENT_PAGE
	);
	$this->_generator_form_fields = $GENERATORFORMFIELDS->selectGeneratorFormFields($select_params);
	
	// load captcha class
	$this->captcha = load('Utility:Captcha');
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($this->base->_conf['locales']['all']);
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
	// start new HTML_QuickForm
	$FORM = $this->base->utility->loadQuickForm('generator_form', 'post', 
		array('accept-charset' => 'utf-8','action' => $this->getLocationSelf(true)));
		
	// load generator form field class	
	$GENERATORFORMFIELDS = load('Content:GeneratorFormField');
	
	foreach ($this->_generator_form_fields as $_field) {
		// prepare id
		$field_id = sprintf('generator_form_%s', strtolower($_field['name']));
		
		// prepare regex
		$field_regex = sprintf('=%s=', $_field['validator_regex']);
		
		// create element depending on the field type
		switch ((string)$_field['type']) {
			case 'checkbox':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => (!empty($_field['class'])) ? 'fcheckbox '. $_field['class'] : 'fcheckbox'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'required_attr', 'autofocus'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);					
					// prepare data
					$data = array(
						'label' => $_field['label']
					);					
					$element = $FORM->addElement('checkbox', $_field['name'], $attributes, $data);
				break;
			case 'email':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'maxlength' => 255,
						'value' => $_field['value'],
						'class' => (!empty($_field['class'])) ? 'femail '. $_field['class'] : 'femail'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'placeholder', 'pattern', 'maxlength', 'required_attr', 'autofocus', 'readonly'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);					
					// prepare data
					$data = array(
						'label' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('email', $_field['name'], $attributes, $data);
				break;
			case 'file':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => (!empty($_field['class'])) ? 'ffile '. $_field['class'] : 'ffile'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'required_attr', 'autofocus'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);
					// prepare data
					$data = array(
						'label' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('file', $_field['name'], $attributes, $data);
				break;
			case 'hidden':
					// prepare attributes
					$attributes = array(
						'id' => $field_id
					);
					// create element
					$element = $FORM->addElement('hidden', $_field['name'], $attributes);
				break;
			case 'number':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'value' => $_field['value'],
						'class' => (!empty($_field['class'])) ? 'fnumber '. $_field['class'] : 'fnumber'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'min', 'max', 'step', 'required_attr', 'autofocus', 'readonly'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);					
					// prepare data
					$data = array(
						'label' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('number', $_field['name'], $attributes, $data);
				break;
			case 'radio':					
					// add Group
					$element = $FORM->addGroup($_field['name']);				
				
					$i = 1;
					foreach (explode(';', str_replace(' ','',$_field['value'])) as $_value) {
						if (empty($_value)) {
							continue;
						}
						// prepare attributes
						$attributes = array(
							'value' => $_value,
							'id' => $field_id . '_' . $i,
							'class' => (!empty($_field['class'])) ? 'fradio '. $_field['class'] : 'fradio'
						);
						// prepare optional attributes and extend attributes array
						$declare_attributes = array(
							'required_attr', 'autofocus'
						);
						$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);						
						// prepare data
						$data = array(
							'label' => $_field['label'],
							'content' => $_value
						);					
						// create (grouped) element
						$element->addElement('radio', $_field['name'], $attributes, $data);												
						$i++;
					}
				break;
			case 'range':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'value' => $_field['value'],
						'class' => (!empty($_field['class'])) ? 'frange '. $_field['class'] : 'frange'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'min', 'max', 'step', 'autofocus'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);					
					// prepare data
					$data = array(
						'label' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('range', $_field['name'], $attributes, $data);
				break;
			case 'reset':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => (!empty($_field['class'])) ? 'freset '. $_field['class'] : 'freset',
						'value' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('reset', $_field['name'], $attributes);
				break;
			case 'search':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'maxlength' => 255,
						'value' => $_field['value'],
						'class' => (!empty($_field['class'])) ? 'fsearch '. $_field['class'] : 'fsearch'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'placeholder', 'pattern', 'maxlength', 'required_attr', 'autofocus', 'readonly'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);					
					// prepare data
					$data = array(
						'label' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('search', $_field['name'], $attributes, $data);
				break;
			case 'select':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => (!empty($_field['class'])) ? 'fselect '. $_field['class'] : 'fselect'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'required_attr','autofocus'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);
					// prepare values
					$values = array();
					foreach (explode(',', str_replace(' ','',$_field['value'])) as $_value) {
						$values[$_value] = $_value;
					}
					// prepare data					
					$data = array(
						'label' => $_field['label'],
						'options' => $values
					);					
					// create element
					$element = $FORM->addElement('select', $_field['name'], $attributes, $data);
				break;
			case 'submit':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => (!empty($_field['class'])) ? 'fsubmit '. $_field['class'] : 'fsubmit'
					);					
					// create element
					$element = $FORM->addElement('submit', $_field['name'], $attributes);
				break;
			case 'tel':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'maxlength' => 255,
						'value' => $_field['value'],
						'class' => (!empty($_field['class'])) ? 'ftel '. $_field['class'] : 'ftel'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'placeholder', 'pattern', 'maxlength', 'required_attr', 'autofocus', 'readonly'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);					
					// prepare data
					$data = array(
						'label' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('tel', $_field['name'], $attributes, $data);
				break;
			case 'text':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'maxlength' => 255,
						'value' => $_field['value'],
						'class' => (!empty($_field['class'])) ? 'ftextfield '. $_field['class'] : 'ftextfield'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'placeholder', 'pattern', 'maxlength', 'required_attr', 'autofocus', 'readonly'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);					
					// prepare data
					$data = array(
						'label' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('text', $_field['name'], $attributes, $data);
				break;
			case 'textarea':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'value' => $_field['value'],
						'class' => (!empty($_field['class'])) ? 'ftextarea '. $_field['class'] : 'ftextarea',
						'cols' => 30,
						'rows' => 6
					);
				// prepare optional attributes and extend attributes array
				$declare_attributes = array(
					'placeholder', 'pattern', 'maxlength', 'required_attr', 'autofocus', 'readonly'
				);
				$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);	
					// prepare label
					$data = array(
						'label' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('textarea', $_field['name'], $attributes, $data);
				break;
			case 'url':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'maxlength' => 255,
						'value' => $_field['value'],
						'class' => (!empty($_field['class'])) ? 'furl '. $_field['class'] : 'furl'
					);
					// prepare optional attributes and extend attributes array
					$declare_attributes = array(
						'placeholder', 'pattern', 'maxlength', 'required_attr', 'autofocus', 'readonly'
					);
					$GENERATORFORMFIELDS->prepareOptionalAttributes($declare_attributes, &$_field, &$attributes);					
					// prepare data
					$data = array(
						'label' => $_field['label']
					);					
					// create element
					$element = $FORM->addElement('url', $_field['name'], $attributes, $data);
				break;
		} // switch
		
		// apply filters to all fields
		$FORM->addRecursiveFilter('trim');
		$FORM->addRecursiveFilter('strip_tags');
		
		// add required rule?
		if ((int)$_field['required']) {
			$element->addRule('required', $_field['required_message']);
		}		
		
		// add regex rule?
		if (!empty($_field['validator_regex'])) {
			$element->addRule('regex', $_field['validator_message'], $field_regex);
		}
		
		// collect values
		if ($FORM->isSubmitted()) {
			foreach ($FORM->getElementsByName($_field['name']) as $element_field_name) {
				// grouped elements
				if ($element_field_name->getType() == 'group') {
						foreach ($element_field_name as $_element_field_name) {						
							$elements[$_field['name']] .= $_element_field_name->getValue();
						}
				 } else {					
					$elements[$_field['name']] .= $element_field_name->getValue();
				}		
			}
		}				
	} // foreach
	
	
	// textfield for captcha if the captcha is enabled
	if ($this->_generator_form['use_captcha'] != 'no') {			
		$_qf_captcha = $FORM->addElement('text', '_qf_captcha', 
			array('id' => 'generator_form_captcha', 'maxlength' => 255, 'class' => 'ftextfield'),
			array('label' => gettext('Captcha text'))
			);
		$_qf_captcha->addRule('required', gettext('Please enter the captcha text'));
		$_qf_captcha->addRule('eq', gettext('Invalid captcha text entered'), $this->captcha->captchaValue());		
	}
	
	// test if the form validates. If it validates, 
	// process it and skip the rest of the page
	if ($FORM->validate()) {
		// freeze the form
		$FORM->toggleFrozen(true);
				
		// prepare & assign form data
		$form_data = array(
			'now' => mktime()
		);
		foreach ($this->_generator_form_fields as $_field) {
			$form_data[$_field['name']] = $elements[$_field['name']];
		}		
		$this->base->utility->smarty->assign('form_data', $form_data);
		 
		// fetch mail body
		$body = $this->base->utility->smarty->fetch($this->getMailTemplateName(),
			md5($_SERVER['REQUEST_URI']));
		
		// prepare sending information
		$recipients = $this->_generator_form['email_to'];
		
		// prepare From: address
		$from = (($this->_generator_form['email_from'] == 'sender@generatorform.wcom') ?
			$_element['email'] : $this->_generator_form['email_from']);
		$from = preg_replace('=((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*=i',
			null, $from);
		
		// headers
		$headers = array();
		$headers['From'] = $from;
		$headers['Subject'] = $this->_generator_form['email_subject'];
		$headers['Reply-To'] = $_element['email'];
		
		// prepare params
		$params = array();
		$params = sprintf('-f %s', $from);
		
		// load PEAR::Mail and PEAR::Mail_mime
		require_once('Mail.php');
		require_once('Mail/mime.php');
		
		// build PEAR::Mail_Mime Instance 
		$mime = new Mail_mime();
		$mime->setTXTBody($body);
		
		// Attachments
		if (isset($_FILES)) {

			// prepare upload path
			$uploadpath = dirname(__FILE__).'/../../tmp/mail_attachments/';
				
			foreach ($_FILES as $_files) {
				if ($_files['size'] > 0) {
				
					// attached file
					$uploadfile = $_files['name'];
				
					// prepare target file path
					$file = $uploadpath.DIRECTORY_SEPARATOR.$uploadfile;
					
					// var to unlink after sending the mail
					$_file[] = $uploadpath.DIRECTORY_SEPARATOR.$uploadfile;
				
					// move file
					move_uploaded_file($_files['tmp_name'], $file);
				
					// attach file
					$mime->addAttachment($file);
				}
			}
		}

		$mime_body = $mime->get(array('text_charset' => 'utf-8'));
		$mime_headers = $mime->headers($headers);	
		
		$MAIL = Mail::factory('mail', $params);
		
		// send mail
		if ($MAIL->send($recipients, $mime_headers, $mime_body)) {
			// delete attachment file(s) if exists
			if(!empty($uploadfile)) {
				foreach ($_file as $f) {
					if(file_exists($f)) {
						unlink($f);
					}
				}
			}
			
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
	if ($this->_generator_form['use_captcha'] != 'no') {
		// captcha generation
		$captcha = null;
		if ($this->_generator_form['use_captcha'] == 'image') {
			// generate image captcha
			$captcha = $this->captcha->createCaptcha('image');
			
			// let's tell the template that the captcha is an image
			$this->base->utility->smarty->assign('captcha_type', 'image');
		} elseif ($this->_generator_form['use_captcha'] == 'numeral') { 
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
	return "wcom:generator_form_index.".WCOM_CURRENT_PAGE;
}

/**
 * Returns the name of the mail template.
 * 
 * @return string
 */
public function getMailTemplateName ()
{
	return "wcom:generator_form_mail.".WCOM_CURRENT_PAGE;
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