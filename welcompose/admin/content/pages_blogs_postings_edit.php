<?php

/**
 * Project: Welcompose
 * File: pages_blogs_postings_edit.php
 *
 * Copyright (c) 2008 creatics media.systems
 *
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * $Id$
 *
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
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
	
	// load Content_BlogPodcast class
	$BLOGPODCAST = load('Content:BlogPodcast');
	
	// load Content_BlogPodcastCategory class
	$BLOGPODCASTCATEGORY = load('Content:BlogPodcastCategory');
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');
	
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
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC));
	
	// get blog posting
	$blog_posting = $BLOGPOSTING->selectBlogPosting(Base_Cnc::filterRequest($_REQUEST['id'],
		WCOM_REGEX_NUMERIC));
	
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
	$FORM = $BASE->utility->loadQuickForm('blog_posting', 'post');
	
	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// hidden for page
	$FORM->addElement('hidden', 'page');
	$FORM->applyFilter('page', 'trim');
	$FORM->applyFilter('page', 'strip_tags');
	$FORM->addRule('page', gettext('Page is not expected to be empty'), 'required');
	$FORM->addRule('page', gettext('Page is expected to be numeric'), 'numeric');
	
	// hidden for start
	$FORM->addElement('hidden', 'start');
	$FORM->applyFilter('start', 'trim');
	$FORM->applyFilter('start', 'strip_tags');
	$FORM->addRule('start', gettext('start is expected to be numeric'), 'numeric');
	
	// hidden for frontend view control
	$FORM->addElement('hidden', 'preview');
	$FORM->applyFilter('preview', 'trim');
	$FORM->applyFilter('preview', 'strip_tags');
	$FORM->addRule('preview', gettext('Id is expected to be numeric'), 'numeric');

	// textfield for title
	$FORM->addElement('text', 'title', gettext('Title'),
		array('id' => 'blog_posting_title', 'maxlength' => 255, 'class' => 'w300 urlify'));
	$FORM->applyFilter('title', 'trim');
	$FORM->applyFilter('title', 'strip_tags');
	$FORM->addRule('title', gettext('Please enter a title'), 'required');
	
	// textfield for URL title
	$FORM->addElement('text', 'title_url', gettext('URL title'),
		array('id' => 'blog_posting_title_url', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('title_url', 'trim');
	$FORM->applyFilter('title_url', 'strip_tags');
	$FORM->addRule('title_url', gettext('Enter an URL title'), 'required');
	$FORM->addRule('title_url', gettext('The URL title may only contain chars, numbers and hyphens'),
		WCOM_REGEX_URL_NAME);
	
	// textarea for summary
	$FORM->addElement('textarea', 'summary', gettext('Summary'),
		array('id' => 'blog_posting_summary', 'cols' => 3, 'rows' => '2', 'class' => 'w540h150'));
	$FORM->applyFilter('summary', 'trim');
	
	// textarea for content
	$FORM->addElement('textarea', 'content', gettext('Content'),
		array('id' => 'blog_posting_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h550'));
	$FORM->applyFilter('content', 'trim');
	
	/*
	 * Podcast layer
	 */
	
	// hidden for podcast id
	$FORM->addElement('hidden', 'podcast_id', '', array('id' => 'podcast_id'));
	$FORM->applyFilter('podcast_id', 'trim');
	$FORM->applyFilter('podcast_id', 'strip_tags');
	$FORM->addRule('podcast_id', gettext('The podcast id is expected to be numeric'), 'numeric');
	
	// hidden for mediafile id
	$FORM->addElement('hidden', 'podcast_media_object', '', array('id' => 'podcast_media_object'));
	$FORM->applyFilter('podcast_media_object', 'trim');
	$FORM->applyFilter('podcast_media_object', 'strip_tags');
	$FORM->addRule('podcast_media_object', gettext('The media file id is expected to be numeric'), 'numeric');
	
	// hidden for display status
	$FORM->addElement('hidden', 'podcast_details_display', '', array('id' => 'podcast_details_display'));
	$FORM->applyFilter('podcast_details_display', 'trim');
	$FORM->addRule('id', gettext('Podcast details display is expected to be numeric'), 'numeric');
	
	// textfield for title
	$FORM->addElement('text', 'podcast_title', gettext('Title'),
		array('id' => 'blog_posting_podcast_title', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('podcast_title', 'trim');
	$FORM->applyFilter('podcast_title', 'strip_tags');
	if ($FORM->exportValue('podcast_media_object') != "") {
		$FORM->addRule('podcast_title', gettext('Please enter a podcast title'), 'required');
	}
	
	// select for description
	$FORM->addElement('select', 'podcast_description', gettext('Description'), $podcast_description_sources,
		array('id' => 'blog_posting_podcast_description'));
	$FORM->applyFilter('podcast_description', 'trim');
	$FORM->applyFilter('podcast_description', 'strip_tags');
	if ($FORM->exportValue('podcast_media_object') != "") {
		$FORM->addRule('podcast_description', gettext('Please select a podcast description source'), 'required');
	}
	$FORM->addRule('podcast_description', gettext('Podcast description source is out of range'),
		'in_array_keys', $podcast_description_sources);
	
	// select for summary
	$FORM->addElement('select', 'podcast_summary', gettext('Summary'), $podcast_summary_sources,
		array('id' => 'blog_posting_podcast_summary'));
	$FORM->applyFilter('podcast_summary', 'trim');
	$FORM->applyFilter('podcast_summary', 'strip_tags');
	if ($FORM->exportValue('podcast_media_object') != "") {
		$FORM->addRule('podcast_summary', gettext('Please select a podcast summary source'), 'required');
	}
	$FORM->addRule('podcast_summary', gettext('Podcast summary source is out of range'),
		'in_array_keys', $podcast_summary_sources);
	
	// select for keywords
	$FORM->addElement('select', 'podcast_keywords', gettext('Keywords'), $podcast_keywords_sources,
		array('id' => 'blog_posting_podcast_keywords'));
	$FORM->applyFilter('podcast_keywords', 'trim');
	$FORM->applyFilter('podcast_keywords', 'strip_tags');
	if ($FORM->exportValue('podcast_media_object') != "") {
		$FORM->addRule('podcast_keywords', gettext('Please select a podcast keyword source'), 'required');
	}
	$FORM->addRule('podcast_keywords', gettext('Podcast keywords source is out of range'),
		'in_array_keys', $podcast_keywords_sources);
	
	// select for category_1
	$FORM->addElement('select', 'podcast_category_1', gettext('Category 1'), $podcast_categories,
		array('id' => 'blog_posting_podcast_category_1'));
	$FORM->applyFilter('podcast_category_1', 'trim');
	$FORM->applyFilter('podcast_category_1', 'strip_tags');
	$FORM->addRule('podcast_category_1', gettext('Podcast category 1 is out of range'),
		'in_array_keys', $podcast_categories);
	if ($FORM->exportValue('podcast_media_object') != "") {
		$FORM->addRule('podcast_category_1', gettext('Please select a podcast category'), 'required');
	}
	
	// select for category_2
	$FORM->addElement('select', 'podcast_category_2', gettext('Category 2'), $podcast_categories_with_empty,
		array('id' => 'blog_posting_podcast_category_2'));
	$FORM->applyFilter('podcast_category_2', 'trim');
	$FORM->applyFilter('podcast_category_2', 'strip_tags');	
	$FORM->addRule('podcast_category_2', gettext('Podcast category 2 is out of range'),
		'in_array_keys', $podcast_categories_with_empty);
	
	// select for category_3
	$FORM->addElement('select', 'podcast_category_3', gettext('Category 3'), $podcast_categories_with_empty,
		array('id' => 'blog_posting_podcast_category_3'));
	$FORM->applyFilter('podcast_category_3', 'trim');
	$FORM->applyFilter('podcast_category_3', 'strip_tags');	
	$FORM->addRule('podcast_category_3', gettext('Podcast category 3 is out of range'),
		'in_array_keys', $podcast_categories_with_empty);

	// textfield for author
	$FORM->addElement('text', 'podcast_author', gettext('Author'),
		array('id' => 'blog_posting_podcast_author', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('podcast_author', 'trim');
	$FORM->applyFilter('podcast_author', 'strip_tags');
	if ($FORM->exportValue('podcast_media_object') != "") {
		$FORM->addRule('podcast_author', gettext('Please enter a podcast author'), 'required');
	}
	
	// select for explicit
	$FORM->addElement('select', 'podcast_explicit', gettext('Explicit'), $podcast_explicit,
		array('id' => 'blog_posting_podcast_explicit'));
	$FORM->applyFilter('podcast_explicit', 'trim');
	$FORM->applyFilter('podcast_explicit', 'strip_tags');	
	$FORM->addRule('podcast_explicit', gettext('Podcast explicit is out of range'),
		'in_array_keys', $podcast_explicit);
	
	// checkbox for explicit
	$FORM->addElement('checkbox', 'podcast_block', gettext('Block'), null,
		array('id' => 'blog_posting_podcast_block', 'class' => 'chbx'));
	$FORM->applyFilter('podcast_block', 'trim');
	$FORM->applyFilter('podcast_block', 'strip_tags');
	$FORM->addRule('podcast_block', gettext('The field whether an episode should be blocked accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);	
	
	// submit button
	$FORM->addElement('button', 'toggleExtendedView', gettext('Show details'),
		array('class' => 'toggleExtendedView120'));
		
	$FORM->addElement('button', 'showIDThree', gettext('Show ID3'),
		array('class' => 'showIDThree120'));
	
	$FORM->addElement('button', 'discardPodcast', gettext('Discard cast'),
		array('class' => 'discardPodcast120'));
	
	/*
	 * End of podcast layer
	 */
	
	// select for text_converter
	$FORM->addElement('select', 'text_converter', gettext('Text converter'),
		$TEXTCONVERTER->getTextConverterListForForm(), array('id' => 'blog_posting_text_converter'));
	$FORM->applyFilter('text_converter', 'trim');
	$FORM->applyFilter('text_converter', 'strip_tags');
	$FORM->addRule('text_converter', gettext('Chosen text converter is out of range'),
		'in_array_keys', $TEXTCONVERTER->getTextConverterListForForm());
	
	// checkbox for apply_macros
	$FORM->addElement('checkbox', 'apply_macros', gettext('Apply text macros'), null,
		array('id' => 'blog_posting_apply_macros', 'class' => 'chbx'));
	$FORM->applyFilter('apply_macros', 'trim');
	$FORM->applyFilter('apply_macros', 'strip_tags');
	$FORM->addRule('apply_macros', gettext('The field whether to apply text macros accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
		
	// checkbox for meta_use
	$FORM->addElement('checkbox', 'meta_use', gettext('Custom meta tags'), null,
		array('id' => 'blog_posting_meta_use', 'class' => 'chbx'));
	$FORM->applyFilter('meta_use', 'trim');
	$FORM->applyFilter('meta_use', 'strip_tags');
	$FORM->addRule('meta_use', gettext('The field whether to use customized meta tags accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// textfield for meta_title
	$FORM->addElement('text', 'meta_title', gettext('Title'),
		array('id' => 'blog_posting_meta_title', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('meta_title', 'trim');
	$FORM->applyFilter('meta_title', 'strip_tags');
	
	// textarea for meta_keywords
	$FORM->addElement('textarea', 'meta_keywords', gettext('Keywords'),
		array('id' => 'blog_posting_meta_keywords', 'cols' => 3, 'rows' => 2, 'class' => 'w540h50'));
	$FORM->applyFilter('meta_keywords', 'trim');
	$FORM->applyFilter('meta_keywords', 'strip_tags');

	// textarea for meta_description
	$FORM->addElement('textarea', 'meta_description', gettext('Description'),
		array('id' => 'blog_posting_meta_description', 'cols' => 3, 'rows' => 2, 'class' => 'w540h50'));
	$FORM->applyFilter('meta_description', 'trim');
	$FORM->applyFilter('meta_description', 'strip_tags');
	
	// textarea for tags
	$FORM->addElement('textarea', 'tags', gettext('Tags'),
		array('id' => 'blog_posting_tags', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'));
	$FORM->applyFilter('tags', 'trim');
	$FORM->applyFilter('tags', 'strip_tags');
	
	// textarea for feed_summary
	$FORM->addElement('textarea', 'feed_summary', gettext('Feed Summary'),
		array('id' => 'blog_posting_feed_summary', 'cols' => 3, 'rows' => '2', 'class' => 'w540h150'));
	$FORM->applyFilter('feed_summary', 'trim');
	
	// checkbox for draft
	$FORM->addElement('checkbox', 'draft', gettext('Draft'), null,
		array('id' => 'blog_posting_draft', 'class' => 'chbx'));
	$FORM->applyFilter('draft', 'trim');
	$FORM->applyFilter('draft', 'strip_tags');
	$FORM->addRule('draft', gettext('The field whether the posting is a draft accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// checkbox for ping
	$FORM->addElement('checkbox', 'ping', gettext('Ping'), null,
		array('id' => 'blog_posting_ping', 'class' => 'chbx'));
	$FORM->applyFilter('ping', 'trim');
	$FORM->applyFilter('ping', 'strip_tags');
	$FORM->addRule('ping', gettext('The field whether a ping should be issued accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);

	// checkbox for comments_enable
	$FORM->addElement('checkbox', 'comments_enable', gettext('Enable comments'), null,
		array('id' => 'blog_posting_comments_enable', 'class' => 'chbx'));
	$FORM->applyFilter('comments_enable', 'trim');
	$FORM->applyFilter('comments_enable', 'strip_tags');
	$FORM->addRule('comments_enable', gettext('The field whether comments are enabled accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// checkbox for trackbacks_enable
	$FORM->addElement('checkbox', 'trackbacks_enable', gettext('Enable trackbacks'), null,
		array('id' => 'blog_posting_trackbacks_enable', 'class' => 'chbx'));
	$FORM->applyFilter('trackbacks_enable', 'trim');
	$FORM->applyFilter('trackbacks_enable', 'strip_tags');
	$FORM->addRule('trackbacks_enable', gettext('The field whether trackbacks are enabled accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// date element for date_added
	$FORM->addElement('date', 'date_added', gettext('Creation date'),
		array('language' => 'en', 'format' => 'd.m.Y \u\m H:i', 'addEmptyOption' => true),
		array('id' => 'blog_posting_date_added'));
	
	// submit button (save and stay)
	$FORM->addElement('submit', 'save', gettext('Save edit'),
		array('class' => 'submit200'));
		
	// submit button (save and go back)
	$FORM->addElement('submit', 'submit', gettext('Save edit and go back'),
		array('class' => 'submit200go'));
	
	// set defaults
	$FORM->setDefaults(array(
		'page' => Base_Cnc::ifsetor($page['id'], null),
		'id' => Base_Cnc::ifsetor($blog_posting['id'], null),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'title' => Base_Cnc::ifsetor($blog_posting['title'], null),
		'title_url' => Base_Cnc::ifsetor($blog_posting['title_url'], null),
		'summary' => Base_Cnc::ifsetor($blog_posting['summary_raw'], null),
		'content' => Base_Cnc::ifsetor($blog_posting['content_raw'], null),
		'feed_summary' => Base_Cnc::ifsetor($blog_posting['feed_summary_raw'], null),
		'text_converter' => Base_Cnc::ifsetor($blog_posting['text_converter'], null),
		'apply_macros' => Base_Cnc::ifsetor($blog_posting['apply_macros'], null),
		'meta_use' => Base_Cnc::ifsetor($blog_posting['meta_use'], null),
		'meta_title' => Base_Cnc::ifsetor($blog_posting['meta_title_raw'], null),
		'meta_keywords' => Base_Cnc::ifsetor($blog_posting['meta_keywords'], null),
		'meta_description' => Base_Cnc::ifsetor($blog_posting['meta_description'], null),
		'tags' => $BLOGTAG->getTagStringFromSerializedArray(Base_Cnc::ifsetor($blog_posting['tag_array'], null)),
		'draft' => Base_Cnc::ifsetor($blog_posting['draft'], null),
		'ping' => 0,
		'comments_enable' => Base_Cnc::ifsetor($blog_posting['comments_enable'], null),
		'trackbacks_enable' => Base_Cnc::ifsetor($blog_posting['trackbacks_enable'], null),
		'date_added' => Base_Cnc::ifsetor($blog_posting['date_added'], null),
		'podcast_media_object' => Base_Cnc::ifsetor($blog_posting['podcast_media_object'], null),
		'podcast_id' => Base_Cnc::ifsetor($blog_posting['podcast_id'], null),
		'podcast_title' => Base_Cnc::ifsetor($blog_posting['podcast_title'], null),
		'podcast_description' => Base_Cnc::ifsetor($blog_posting['podcast_description_source'], null),
		'podcast_summary' => Base_Cnc::ifsetor($blog_posting['podcast_summary_source'], null),
		'podcast_keywords' => Base_Cnc::ifsetor($blog_posting['podcast_keywords_source'], null),
		'podcast_category_1' => Base_Cnc::ifsetor($blog_posting['podcast_category_1'], null),
		'podcast_category_2' => Base_Cnc::ifsetor($blog_posting['podcast_category_2'], null),
		'podcast_category_3' => Base_Cnc::ifsetor($blog_posting['podcast_category_3'], null),
		'podcast_author' => Base_Cnc::ifsetor($blog_posting['podcast_author'], null),
		'podcast_explicit' => Base_Cnc::ifsetor($blog_posting['podcast_explicit'], null),
		'podcast_block' => Base_Cnc::ifsetor($blog_posting['podcast_block'], null),
		// ctrl var for frontend view
		'preview' => $_SESSION['preview_ctrl']
	));
	
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		$quickform_tpl_path = dirname(__FILE__).'/../quickform.tpl.php';
		include(Base_Compat::fixDirectorySeparator($quickform_tpl_path));
		
		// remove attribute on form tag for XHTML compliance
		$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
		
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $renderer->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);
		
		// build session
		$session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], WCOM_REGEX_NUMERIC),
			'preview_ctrl' => Base_Cnc::filterRequest($_SESSION['preview_ctrl'], WCOM_REGEX_NUMERIC)
		);
		
		// assign $_SESSION to smarty
		$BASE->utility->smarty->assign('session', $session);
		
		// empty $_SESSION
		if (!empty($_SESSION['response'])) {
			$_SESSION['response'] = '';
		}	
		if (!empty($_SESSION['preview_ctrl'])) {
		  	$_SESSION['preview_ctrl'] = '';
		}

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// assign page
		$BASE->utility->smarty->assign('page', $page);
		
		// assign posting id
		$BASE->utility->smarty->assign('blog_posting', $blog_posting);
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_blogs_postings_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// prepare sql data
		$sqlData = array();
		$sqlData['title'] = $FORM->exportValue('title');
		$sqlData['title_url'] = $FORM->exportValue('title_url');
		$sqlData['summary_raw'] = $FORM->exportValue('summary');
		$sqlData['summary'] = $FORM->exportValue('summary');
		$sqlData['content_raw'] = $FORM->exportValue('content');
		$sqlData['content'] = $FORM->exportValue('content');
		$sqlData['feed_summary_raw'] = $FORM->exportValue('feed_summary');
		$sqlData['feed_summary'] = $FORM->exportValue('feed_summary');
		$sqlData['text_converter'] = ($FORM->exportValue('text_converter') > 0) ? 
			$FORM->exportValue('text_converter') : null;
		$sqlData['apply_macros'] = (string)intval($FORM->exportValue('apply_macros'));
		$sqlData['meta_use'] = $FORM->exportValue('meta_use');
		$sqlData['meta_title_raw'] = null;
		$sqlData['meta_title'] = null;
		$sqlData['meta_keywords'] = null;
		$sqlData['meta_description'] = null;
		$sqlData['draft'] = (string)intval($FORM->exportValue('draft'));
		$sqlData['ping'] = (string)intval($FORM->exportValue('ping'));
		$sqlData['comments_enable'] = (string)intval($FORM->exportValue('comments_enable'));
		$sqlData['trackbacks_enable'] = (string)intval($FORM->exportValue('trackbacks_enable'));
		$sqlData['date_added'] = $date_added = $HELPER->datetimeFromQuickFormDate($FORM->exportValue('date_added'));
		$sqlData['year_added'] = date('Y', strtotime($date_added));
		$sqlData['month_added'] = date('m', strtotime($date_added));
		$sqlData['day_added'] = date('d', strtotime($date_added));
		
		// apply text macros and text converter if required
		if ($FORM->exportValue('text_converter') > 0 || $FORM->exportValue('apply_macros') > 0) {
			// extract summary/content
			$summary = $FORM->exportValue('summary');
			$content = $FORM->exportValue('content');
			$feed_summary = $FORM->exportValue('feed_summary');

			// apply startup and pre text converter text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
				$summary = $TEXTMACRO->applyTextMacros($summary, 'pre');
				$content = $TEXTMACRO->applyTextMacros($content, 'pre');
				$feed_summary = $TEXTMACRO->applyTextMacros($feed_summary, 'pre');
			}

			// apply text converter
			if ($FORM->exportValue('text_converter') > 0) {
				$summary = $TEXTCONVERTER->applyTextConverter(
					$FORM->exportValue('text_converter'),
					$summary
				);
				$content = $TEXTCONVERTER->applyTextConverter(
					$FORM->exportValue('text_converter'),
					$content
				);
				$feed_summary = $TEXTCONVERTER->applyTextConverter(
					$FORM->exportValue('text_converter'),
					$feed_summary
				);
			}

			// apply post text converter and shutdown text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
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
		if ($FORM->exportValue('meta_use') == 1) { 
			$sqlData['meta_title_raw'] = $FORM->exportValue('meta_title');
			$sqlData['meta_title'] = str_replace("%title", $FORM->exportValue('title'), 
				$FORM->exportValue('meta_title'));
			$sqlData['meta_keywords'] = $FORM->exportValue('meta_keywords');
			$sqlData['meta_description'] = $FORM->exportValue('meta_description');
		}

		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$BLOGPOSTING->updateBlogPosting($FORM->exportValue('id'), $sqlData);
			
			// update tags
			$BLOGTAG->updatePostingTags($FORM->exportValue('page'), $FORM->exportValue('id'),
				$BLOGTAG->_tagStringToArray($FORM->exportValue('tags')));
			
			// get tags
			$tags = $BLOGTAG->selectBlogTags(array('posting' => $FORM->exportValue('id')));
			
			// update blog posting
			$sqlData = array(
				'tag_count' => count($tags),
				'tag_array' => $BLOGTAG->getSerializedTagArrayFromTagArray($tags)
			);
			$BLOGPOSTING->updateBlogPosting($FORM->exportValue('id'), $sqlData);
			
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
		if ($FORM->exportValue('podcast_media_object') != "") {
			// prepare sql data
			$sqlData = array();
			$sqlData['blog_posting'] = $FORM->exportValue('id');
			$sqlData['title'] = $FORM->exportValue('podcast_title');
			$sqlData['media_object'] = $FORM->exportValue('podcast_media_object');
			$sqlData['description_source'] = $FORM->exportValue('podcast_description');
			$sqlData['summary_source'] = $FORM->exportValue('podcast_summary');
			$sqlData['keywords_source'] = $FORM->exportValue('podcast_keywords');
			$sqlData['category_1'] = (($FORM->exportValue('podcast_category_1') == "") ? null :
				$FORM->exportValue('podcast_category_1'));
			$sqlData['category_2'] = (($FORM->exportValue('podcast_category_2') == "") ? null :
				$FORM->exportValue('podcast_category_2'));
			$sqlData['category_3'] = (($FORM->exportValue('podcast_category_3') == "") ? null :
				$FORM->exportValue('podcast_category_3'));
			$sqlData['author'] = $FORM->exportValue('podcast_author');
			$sqlData['block'] = (string)intval($FORM->exportValue('podcast_block'));
			$sqlData['explicit'] = $FORM->exportValue('podcast_explicit');
		
			// test sql data for pear errors
			$HELPER->testSqlDataForPearErrors($sqlData);
			
			// insert it
			try {
				// begin transaction
				$BASE->db->begin();
				
				// insert or update podcast depending whether there's a podcast id or not
				if ($FORM->exportValue('podcast_id') == "") {
					$BLOGPODCAST->addBlogPodcast($sqlData);
				} else {
					$BLOGPODCAST->updateBlogPodcast($FORM->exportValue('podcast_id'), $sqlData);
				}
				
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
		if ($FORM->exportValue('ping') == 1) {	
			
			// load ping service configuration class
			$PINGSERVICECONFIGURATION = load('application:pingserviceconfiguration');
			
			// load ping service class
			$PINGSERVICE = load('application:pingservice');
			
			// get configured ping service configurations
			$configurations = $PINGSERVICECONFIGURATION->selectPingServiceConfigurations(array('page' => $page['id']));

			// issue pings if configurations exits
			if (!empty($configurations)) {
				foreach ($configurations as $_configuration) {
					$PINGSERVICE->pingService($_configuration['id']);
				}
			}
		}

		// controll value
		$saveAndRemainOnPage = $FORM->exportValue('save');
		
		// add response to session
		if (!empty($saveAndRemainOnPage)) {
			$_SESSION['response'] = 1;
		}
		
		// preview control value
		$activePreview = $FORM->exportValue('preview');
				
		// add preview_ctrl to session
		if (!empty($activePreview)) {
			$_SESSION['preview_ctrl'] = 1;
		}
				
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}

		// save request start range
		$start = $FORM->exportValue('start');
		$start = (!empty($start)) ? $start : 0;
		
		// redirect
		if (!empty($saveAndRemainOnPage)) {
			header("Location: pages_blogs_postings_edit.php?page=".
						$FORM->exportValue('page')."&id=".$FORM->exportValue('id')."&start=".$start);
		} else {
			header("Location: pages_blogs_postings_select.php?page=".
						$FORM->exportValue('page')."&start=".$start);
		}
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
