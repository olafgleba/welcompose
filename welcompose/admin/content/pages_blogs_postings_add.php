<?php

/**
 * Project: Welcompose
 * File: pages_blogs_postings_add.php
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
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// define area constant
define('WCOM_CURRENT_AREA', 'ADMIN');

// get loader
$path_parts = array(
	dirname(__FILE__),
	'..',
	'..',
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/../../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

// admin_navigation
$admin_navigation_path = dirname(__FILE__).'/../../core/includes/admin_navigation.inc.php';
require(Base_Compat::fixDirectorySeparator($admin_navigation_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_admin_conf = dirname(__FILE__).'/../../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// load user class
	/* @var $USER User_User */
	$USER = load('user:user');
	
	// load login class
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load page class
	/* @var $PAGE Content_Page */
	$PAGE = load('content:page');
	
	// load blogposting class
	/* @var $BLOGPOSTING Content_Blogposting */
	$BLOGPOSTING = load('content:blogposting');
	
	// load blogtag class
	/* @var $BLOGTAG Content_Blogtag */
	$BLOGTAG = load('content:blogtag');
	
	// load textconverter class
	/* @var $TEXTCONVERTER Application_Textconverter */
	$TEXTCONVERTER = load('application:textconverter');
	
	// load textmacro class
	/* @var $TEXTMACRO Application_Textmacro */
	$TEXTMACRO = load('application:textmacro');
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');
	
	// load Content_BlogPodcast class
	$BLOGPODCAST = load('Content:BlogPodcast');
	
	// load Content_BlogPodcastCategory class
	$BLOGPODCASTCATEGORY = load('Content:BlogPodcastCategory');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'BlogPosting', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);

	// get default text converter if set
	$default_text_converter = $TEXTCONVERTER->selectDefaultTextConverter();
		
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC));
	
	// prepare podcast category array
	$podcast_categories = array();
	$podcast_categories_with_empty = array("" => "");
	foreach ($BLOGPODCASTCATEGORY->selectBlogPodcastCategories() as  $_category) {
		$podcast_categories[(int)$_category['id']] = htmlspecialchars($_category['name']);
		$podcast_categories_with_empty[(int)$_category['id']] = htmlspecialchars($_category['name']);
	}
	
	// prepare summary/description/keyword source selects for podcasts
	$podcast_description_sources = array(
		'summary' => gettext('Use summary'),
		'content' => gettext('Use content'),
		'feed_summary' => gettext('Use feed summary'),
		'empty' => gettext('Leave it empty')
	);
	
	$podcast_summary_sources = array(
		'summary' => gettext('Use summary'),
		'content' => gettext('Use content'),
		'feed_summary' => gettext('Use feed summary'),
		'empty' => gettext('Leave it empty')
	);
	
	$podcast_keywords_sources = array(
		'tags' => gettext('Use tags'),
		'empty' => gettext('Leave it empty')
	);
	
	// prepare podcast explicit array
	$podcast_explicit = array(
		'yes' => gettext('yes'),
		'clean' => gettext('clean'),
		'no' => gettext('no')
	);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('blog_posting');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// hidden for page
	$page_id = $FORM->addElement('hidden', 'page', array('id' => 'page'));

	// textfield for title	
	$title = $FORM->addElement('text', 'title', 
		array('id' => 'blog_posting_title', 'maxlength' => 255, 'class' => 'w300 urlify'),
		array('label' => gettext('Title'))
		);
	$title->addRule('required', gettext('Please enter a title'));
	
	// textfield for URL title
	$title_url = $FORM->addElement('text', 'title_url', 
		array('id' => 'blog_posting_title_url', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('URL title'))
		);
	$title_url->addRule('required', gettext('Enter an URL title'));
	$title_url->addRule('regex', gettext('The URL title may only contain chars, numbers and hyphens'), WCOM_REGEX_URL_NAME);
	
	// textarea for summary	
	$summary = $FORM->addElement('textarea', 'summary', 
		array('id' => 'blog_posting_summary', 'cols' => 3, 'rows' => '2', 'class' => 'w540h150'),
		array('label' => gettext('Summary'))
		);

	// textarea for content	
	$content = $FORM->addElement('textarea', 'content', 
		array('id' => 'blog_posting_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h550'),
		array('label' => gettext('Content'))
		);		
	
	/*
	 * Podcast layer
	 */
	
	// hidden for podcast id	
	$podcast_id = $FORM->addElement('hidden', 'podcast_id', array('id' => 'podcast_id'));

	// hidden for mediafile id
	$podcast_media_object = $FORM->addElement('hidden', 'podcast_media_object', array('id' => 'podcast_media_object'));

	// hidden for display status
	$podcast_details_display = $FORM->addElement('hidden', 'podcast_details_display', array('id' => 'podcast_details_display'));	
	
	// textfield for title	
	$podcast_title = $FORM->addElement('text', 'podcast_title', 
		array('id' => 'blog_posting_podcast_title', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Title'))
		);
	if ($podcast_media_object->getValue() != "") {
		$podcast_title->addRule('required', gettext('Please enter a podcast title'));
	}
	
	// select for description
	$podcast_description = $FORM->addElement('select', 'podcast_description',
	 	array('id' => 'blog_posting_podcast_description'),
		array('label' => gettext('Description'), 'options' => $podcast_description_sources)
		);
	if ($podcast_media_object->getValue() != "") {
		$podcast_description->addRule('required', gettext('Please select a podcast description source'));
	}		

	// select for summary
	$podcast_summary = $FORM->addElement('select', 'podcast_summary',
	 	array('id' => 'blog_posting_podcast_summary'),
		array('label' => gettext('Summary'), 'options' => $podcast_summary_sources)
		);
	if ($podcast_media_object->getValue() != "") {
		$podcast_summary->addRule('required', gettext('Please select a podcast summary source'));
	}		

	// select for keywords
	$podcast_keywords = $FORM->addElement('select', 'podcast_keywords',
	 	array('id' => 'blog_posting_podcast_keywords'),
		array('label' => gettext('Keywords'), 'options' => $podcast_keywords_sources)
		);
	if ($podcast_media_object->getValue() != "") {
		$podcast_keywords->addRule('required', gettext('Please select a podcast keyword source'));
	}	

	// select for category_1	
	$podcast_category_1 = $FORM->addElement('select', 'podcast_category_1',
	 	array('id' => 'blog_posting_podcast_category_1'),
		array('label' => gettext('Category 1'), 'options' => $podcast_categories)
		);
	if ($podcast_media_object->getValue() != "") {
		$podcast_category_1->addRule('required', gettext('Please select a podcast category'));
	}
	
	// select for category_2
	$podcast_category_2 = $FORM->addElement('select', 'podcast_category_2',
	 	array('id' => 'blog_posting_podcast_category_2'),
		array('label' => gettext('Category 2'), 'options' => $podcast_categories_with_empty)
		);
	if ($podcast_media_object->getValue() != "") {
		$podcast_category_2->addRule('required', gettext('Please select a podcast category'));
	}

	// select for category_3
	$podcast_category_3 = $FORM->addElement('select', 'podcast_category_3',
	 	array('id' => 'blog_posting_podcast_category_3'),
		array('label' => gettext('Category 3'), 'options' => $podcast_categories_with_empty)
		);
	if ($podcast_media_object->getValue() != "") {
		$podcast_category_3->addRule('required', gettext('Please select a podcast category'));
	}
		
	// textfield for author		
	$podcast_author = $FORM->addElement('text', 'podcast_author', 
		array('id' => 'blog_posting_podcast_author', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Author'))
		);
	if ($podcast_media_object->getValue() != "") {
		$podcast_author->addRule('required', gettext('Please enter a podcast author'));
	}
	
	// select for explicit
	$podcast_explicit = $FORM->addElement('select', 'podcast_explicit',
	 	array('id' => 'blog_posting_podcast_explicit'),
		array('label' => gettext('Explicit'), 'options' => $podcast_explicit)
		);	
	
	// checkbox for explicit
	$podcast_block = $FORM->addElement('checkbox', 'podcast_block',
		array('id' => 'blog_posting_podcast_block', 'class' => 'chbx'),
		array('label' => gettext('Block'))
		);
	$podcast_block->addRule('regex', gettext('The field whether an episode should be blocked accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);

	// submit button
	$toggleExtendedView = $FORM->addElement('inputButton', 'toggleExtendedView', 
		array('id' => 'toggleExtendedView', 'class' => 'toggleExtendedView120', 'value' => gettext('Show details'))
		);
	$showIDThree = $FORM->addElement('inputButton', 'showIDThree', 
		array('id' => 'showIDThree', 'class' => 'showIDThree120', 'value' => gettext('Show ID3'))
		);
	$discardPodcast = $FORM->addElement('inputButton', 'discardPodcast', 
		array('id' => 'discardPodcast', 'class' => 'discardPodcast120', 'value' => gettext('Discard cast'))
		);
	
	/*
	 * End of podcast layer
	 */	
	
	// select for text_converter
	$text_converter = $FORM->addElement('select', 'text_converter',
	 	array('id' => 'blog_posting_text_converter'),
		array('label' => gettext('Text converter'), 'options' => $TEXTCONVERTER->getTextConverterListForForm())
		);
		
	// checkbox for apply_macros
	$apply_macros = $FORM->addElement('checkbox', 'apply_macros',
		array('id' => 'blog_posting_apply_macros', 'class' => 'chbx'),
		array('label' => gettext('Apply text macros'))
		);
	$apply_macros->addRule('regex', gettext('The field whether to apply text macros accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);

	// checkbox for meta_use		
	$meta_use = $FORM->addElement('checkbox', 'meta_use',
		array('id' => 'blog_posting_meta_use', 'class' => 'chbx'),
		array('label' => gettext('Custom meta tags'))
		);
	$meta_use->addRule('regex', gettext('The field whether to use customized meta tags accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// textfield for meta_title
	$meta_title = $FORM->addElement('text', 'meta_title', 
		array('id' => 'blog_posting_meta_title', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Title'))
		);
	
	// textarea for meta_keywords
	$meta_keywords = $FORM->addElement('textarea', 'meta_keywords', 
		array('id' => 'blog_posting_meta_keywords', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Keywords'))
		);

	// textarea for meta_description
	$meta_description = $FORM->addElement('textarea', 'meta_description', 
		array('id' => 'blog_posting_meta_description', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Description'))
		);
	
	// textarea for tags
	$tags = $FORM->addElement('textarea', 'tags', 
		array('id' => 'blog_posting_tags', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Tags'))
		);
	
	// textarea for feed_summary
	$feed_summary = $FORM->addElement('textarea', 'feed_summary', 
		array('id' => 'blog_posting_feed_summary', 'cols' => 3, 'rows' => '2', 'class' => 'w540h150'),
		array('label' => gettext('Feed Summary'))
		);
		
	// optional inputs
	
	// textarea for optional content 1
	$optional_content_1 = $FORM->addElement('textarea', 'optional_content_1', 
		array('id' => 'blog_posting_optional_content_1', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Optional Content 1'))
		);
		
	// textarea for optional content 2
	$optional_content_2 = $FORM->addElement('textarea', 'optional_content_2', 
		array('id' => 'blog_posting_optional_content_2', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Optional Content 2'))
		);
		
	// textarea for optional content 3
	$optional_content_3 = $FORM->addElement('textarea', 'optional_content_3', 
		array('id' => 'blog_posting_optional_content_3', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Optional Content 3'))
		);
	
	// checkbox for draft
	$draft = $FORM->addElement('checkbox', 'draft',
		array('id' => 'blog_posting_draft', 'class' => 'chbx'),
		array('label' => gettext('Draft'))
		);
	$draft->addRule('regex', gettext('The field whether the posting is a draft accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);

	// checkbox for ping
	$ping = $FORM->addElement('checkbox', 'ping',
		array('id' => 'blog_posting_ping', 'class' => 'chbx'),
		array('label' => gettext('Ping'))
		);
	$ping->addRule('regex', gettext('The field whether a ping should be issued accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);

	// checkbox for comments_enable
	$comments_enable = $FORM->addElement('checkbox', 'comments_enable',
		array('id' => 'blog_posting_comments_enable', 'class' => 'chbx'),
		array('label' => gettext('Comments'))
		);
	$comments_enable->addRule('regex', gettext('The field whether comments are enabled accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
		
	// checkbox for trackbacks_enable
	$trackbacks_enable = $FORM->addElement('checkbox', 'trackbacks_enable',
		array('id' => 'blog_posting_trackbacks_enable', 'class' => 'chbx'),
		array('label' => gettext('Enable trackbacks'))
		);
	$trackbacks_enable->addRule('regex', gettext('The field whether trackbacks are enabled accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// date element for date_added
	$date_added = $FORM->addElement('date', 'date_added', null,
		array('label' => gettext('Creation date'),'language' => 'de', 'format' => 'd.m.Y H:i','minYear' => date('Y')-5, 'maxYear' => date('Y')+5)
	);
		
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Save'))
		);
		
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'page' => Base_Cnc::ifsetor($page['id'], null),
		'text_converter' => ($default_text_converter > 0) ? $default_text_converter['id'] : null,
		'apply_macros' => 1,
		'ping' => 0,
		'comments_enable' => 0,
		'trackbacks_enable' => 0,
		'date_added' => date('Y-m-d H:i:s')
	)));
	
	
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		
		// fetch {function} template to set
		// required/error markup on each form fields
		$BASE->utility->smarty->fetch(dirname(__FILE__).'/../quickform.tpl');
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
		
		// build session
		$session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], WCOM_REGEX_NUMERIC)
		);
		
		// assign $_SESSION to smarty
		$BASE->utility->smarty->assign('session', $session);
		
		// empty $_SESSION
		if (!empty($_SESSION['response'])) {
			$_SESSION['response'] = '';
		}
		
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// assign page
		$BASE->utility->smarty->assign('page', $page);
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_blogs_postings_add.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		
		// print_r($FORM->getValue());
		// exit;
		
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// prepare sql data
		$sqlData = array();
		$sqlData['page'] = $page_id->getValue();
		$sqlData['user'] = WCOM_CURRENT_USER;
		$sqlData['title'] = $title->getValue();
		$sqlData['title_url'] = $title_url->getValue();
		$sqlData['summary_raw'] = $summary->getValue();
		$sqlData['summary'] = $summary->getValue();
		$sqlData['content_raw'] = $content->getValue();
		$sqlData['content'] = $content->getValue();
		$sqlData['feed_summary_raw'] = $feed_summary->getValue();
		$sqlData['feed_summary'] = $feed_summary->getValue();
		$sqlData['text_converter'] = ($text_converter->getValue() > 0) ? 
			$text_converter->getValue() : null;
		$sqlData['apply_macros'] = (string)intval($apply_macros->getValue());
		$sqlData['meta_use'] = $meta_use->getValue();
		$sqlData['meta_title_raw'] = null;
		$sqlData['meta_title'] = null;
		$sqlData['meta_keywords'] = null;
		$sqlData['meta_description'] = null;
		$sqlData['optional_content_1'] = $optional_content_1->getValue();
		$sqlData['optional_content_2'] = $optional_content_2->getValue();
		$sqlData['optional_content_3'] = $optional_content_3->getValue();
		$sqlData['draft'] = (string)intval($draft->getValue());
		$sqlData['ping'] = (string)intval($ping->getValue());
		$sqlData['comments_enable'] = (string)intval($comments_enable->getValue());
		$sqlData['trackbacks_enable'] = (string)intval($trackbacks_enable->getValue());
		$sqlData['date_added'] = $date_added = $HELPER->datetimeFromQuickFormDate($date_added->getValue());
		$sqlData['year_added'] = date('Y', strtotime($date_added));
		$sqlData['month_added'] = date('m', strtotime($date_added));
		$sqlData['day_added'] = date('d', strtotime($date_added));
		
		// apply text macros and text converter if required
		if ($text_converter->getValue() > 0 || $apply_macros->getValue() > 0) {
			// extract summary/content
			$summary = $summary->getValue();
			$content = $content->getValue();
			$feed_summary = $feed_summary->getValue();

			// apply startup and pre text converter text macros 
			if ($apply_macros->getValue() > 0) {
				$summary = $TEXTMACRO->applyTextMacros($summary, 'pre');
				$content = $TEXTMACRO->applyTextMacros($content, 'pre');
				$feed_summary = $TEXTMACRO->applyTextMacros($feed_summary, 'pre');
			}

			// apply text converter
			if ($text_converter->getValue() > 0) {
				$summary = $TEXTCONVERTER->applyTextConverter(
					$text_converter->getValue(),
					$summary
				);
				$content = $TEXTCONVERTER->applyTextConverter(
					$text_converter->getValue(),
					$content
				);
				$feed_summary = $TEXTCONVERTER->applyTextConverter(
					$text_converter->getValue(),
					$feed_summary
				);
			}

			// apply post text converter and shutdown text macros 
			if ($apply_macros->getValue() > 0) {
				$summary = $TEXTMACRO->applyTextMacros($summary, 'post');
				$content = $TEXTMACRO->applyTextMacros($content, 'post');
				$feed_summary = $TEXTMACRO->applyTextMacros($feed_summary, 'post');
			}

			// assign summary/content to sql data array
			$sqlData['summary'] = $summary;
			$sqlData['content'] = $content;
			$sqlData['feed_summary'] = $feed_summary;
		}
		
		// prepare custom meta tags
		if ($meta_use->getValue() == 1) { 
			$sqlData['meta_title_raw'] = $meta_title->getValue();
			$sqlData['meta_title'] = str_replace("%title", $title->getValue(), 
				$meta_title->getValue());
			$sqlData['meta_keywords'] = $meta_keywords->getValue();
			$sqlData['meta_description'] = $meta_description->getValue();
		}
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$posting_id = $BLOGPOSTING->addBlogPosting($sqlData);
			
			// add tags
			$BLOGTAG->addPostingTags($page_id->getValue(), $posting_id,
				$BLOGTAG->_tagStringToArray($tags->getValue()));
			
			// get tags
			$tags = $BLOGTAG->selectBlogTags(array('posting' => $posting_id));
			
			// update blog posting
			$sqlData = array(
				'tag_count' => count($tags),
				'tag_array' => $BLOGTAG->getSerializedTagArrayFromTagArray($tags)
			);
			
			$BLOGPOSTING->updateBlogPosting($posting_id, $sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
		
		/*
		 * Process podcast
		 */
		if ($podcast_media_object->getValue() != "") {
			// prepare sql data
			$sqlData = array();
			$sqlData['blog_posting'] = (int)$posting_id;
			$sqlData['media_object'] = (int)$podcast_media_object->getValue();
			$sqlData['title'] = $podcast_title->getValue();
			$sqlData['description_source'] = $podcast_description->getValue();
			$sqlData['summary_source'] = $podcast_summary->getValue();
			$sqlData['keywords_source'] = $podcast_keywords->getValue();
			$sqlData['category_1'] = (($podcast_category_1->getValue() == "") ? null :
				$podcast_category_1->getValue());
			$sqlData['category_2'] = (($podcast_category_2->getValue() == "") ? null :
				$podcast_category_2->getValue());
			$sqlData['category_3'] = (($podcast_category_3->getValue() == "") ? null :
				$podcast_category_3->getValue());
			$sqlData['author'] = $podcast_author->getValue();
			$sqlData['block'] = (string)intval($podcast_block->getValue());
			$sqlData['explicit'] = $podcast_explicit->getValue();
			
			// test sql data for pear errors
			$HELPER->testSqlDataForPearErrors($sqlData);
			
			// insert it
			try {
				// begin transaction
				$BASE->db->begin();
				
				$BLOGPODCAST->addBlogPodcast($sqlData);
				
				// commit
				$BASE->db->commit();
			} catch (Exception $e) {
				// do rollback
				$BASE->db->rollback();
			
				// re-throw exception
				throw $e;
			}
		}
		
		// issue pings if required
		if ($ping->getValue() == 1) {	
			
			// load ping service configuration class
			$PINGSERVICECONFIGURATION = load('application:pingserviceconfiguration');
			
			// load ping service class
			$PINGSERVICE = load('application:pingservice');
			
			// get configured ping service configurations
			$configurations = $PINGSERVICECONFIGURATION->selectPingServiceConfigurations(array('page' => $page_id->getValue()));
			
			// issue pings if configurations exits
			if (!empty($configurations)) {
				foreach ($configurations as $_configuration) {
					$PINGSERVICE->pingService($_configuration['id']);
				}
			}
		}
	
		// add response to session
		$_SESSION['response'] = 1;
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: pages_blogs_postings_add.php?page=".$page_id->getValue());
		exit;
	}
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty);
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}
?>