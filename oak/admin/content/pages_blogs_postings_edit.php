<?php

/**
 * Project: Oak
 * File: pages_blogs_postings_add.php
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

// define area constant
define('OAK_CURRENT_AREA', 'ADMIN');

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
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], OAK_REGEX_NUMERIC));
	
	// get blog posting
	$blog_posting = $BLOGPOSTING->selectBlogPosting(Base_Cnc::filterRequest($_REQUEST['id'],
		OAK_REGEX_NUMERIC));
	
	// prepare text converters array
	$text_converters = array(
		'' => gettext('None')
	);
	foreach ($TEXTCONVERTER->selectTextConverters() as $_converter) {
		$text_converters[(int)$_converter['id']] = htmlspecialchars($_converter['name']);
	}
	
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

	// textfield for title
	$FORM->addElement('text', 'title', gettext('Title'),
		array('id' => 'blog_posting_title', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('title', 'trim');
	$FORM->applyFilter('title', 'strip_tags');
	$FORM->addRule('title', gettext('Please enter a title'), 'required');

	// textarea for summary
	$FORM->addElement('textarea', 'summary', gettext('Summary'),
		array('id' => 'blog_posting_summary', 'cols' => 3, 'rows' => '2', 'class' => 'w540h150'));
	$FORM->applyFilter('summary', 'trim');
	
	// textarea for content
	$FORM->addElement('textarea', 'content', gettext('Content'),
		array('id' => 'blog_posting_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h550'));
	$FORM->applyFilter('content', 'trim');


	// podcast layer
		
	// hidden for id
	$FORM->addElement('hidden', 'mediafile_id', '', array('id' => 'mediafile_id'));
	$FORM->applyFilter('mediafile_id', 'trim');
	
	// textfield for title
	$FORM->addElement('text', 'podcast_title', gettext('Title'),
		array('id' => 'blog_posting_podcast_title', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('podcast_title', 'trim');
	$FORM->applyFilter('podcast_title', 'strip_tags');	
	
	// collect for description
	$casts_description = array(
		'1' => gettext('Use Feed Description'),
		'0' => gettext('No Description')
		);

	// select for description
	$FORM->addElement('select', 'podcast_description', gettext('Description'), $casts_description,
		array('id' => 'blog_posting_podcast_description'));
	$FORM->applyFilter('podcast_description', 'trim');
	$FORM->applyFilter('podcast_description', 'strip_tags');

	// collect for summary
	$casts_summary = array(
		'1' => gettext('Use Feed Summary'),
		'0' => gettext('No Summary')
		);
			
	// select for summary
	$FORM->addElement('select', 'podcast_summary', gettext('Summary'), $casts_summary,
		array('id' => 'blog_posting_podcast_summary'));
	$FORM->applyFilter('podcast_summary', 'trim');
	$FORM->applyFilter('podcast_summary', 'strip_tags');
	
	// collect for keywords
	$casts_keywords= array(
		'1' => gettext('Use Feed Keywords'),
		'0' => gettext('No Keywords')
		);
			
	// select for keywords
	$FORM->addElement('select', 'podcast_keywords', gettext('Keywords'), $casts_keywords,
		array('id' => 'blog_posting_podcast_keywords'));
	$FORM->applyFilter('podcast_keywords', 'trim');
	$FORM->applyFilter('podcast_keywords', 'strip_tags');	
	
	// textfield for author
	$FORM->addElement('text', 'podcast_author', gettext('Author'),
		array('id' => 'blog_posting_podcast_author', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('podcast_author', 'trim');
	$FORM->applyFilter('podcast_author', 'strip_tags');
	
	// checkbox for explicit
	$FORM->addElement('checkbox', 'podcast_explicit', gettext('Explicit'), null,
		array('id' => 'blog_posting_podcast_explicit', 'class' => 'chbx'));
	$FORM->applyFilter('podcast_explicit', 'trim');
	$FORM->applyFilter('podcast_explicit', 'strip_tags');
	$FORM->addRule('podcast_explicit', gettext('The field whether a ping should be issued accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);	
	
	// checkbox for explicit
	$FORM->addElement('checkbox', 'podcast_block', gettext('Blog Appearance'), null,
		array('id' => 'blog_posting_podcast_block', 'class' => 'chbx'));
	$FORM->applyFilter('podcast_block', 'trim');
	$FORM->applyFilter('podcast_block', 'strip_tags');
	$FORM->addRule('podcast_block', gettext('Prevent an episode or podcast from appearing'),
		'regex', OAK_REGEX_ZERO_OR_ONE);	
	
	// submit button
	$FORM->addElement('button', 'toggleExtendedView', gettext('Show details'),
		array('class' => 'toggleExtendedView120'));
		
	$FORM->addElement('button', 'showIDThree', gettext('Show ID3'),
		array('class' => 'showIDThree120'));

	$FORM->addElement('button', 'discardPodcast', gettext('Discard cast'),
		array('class' => 'discardPodcast120'));
			
	// podcast layer eof	
	
		
	// select for text_converter
	$FORM->addElement('select', 'text_converter', gettext('Text converter'), $text_converters,
		array('id' => 'blog_posting_text_converter'));
	$FORM->applyFilter('text_converter', 'trim');
	$FORM->applyFilter('text_converter', 'strip_tags');
	$FORM->addRule('text_converter', gettext('Chosen text converter is out of range'),
		'in_array_keys', $text_converters);
	
	// checkbox for apply_macros
	$FORM->addElement('checkbox', 'apply_macros', gettext('Apply text macros'), null,
		array('id' => 'blog_posting_apply_macros', 'class' => 'chbx'));
	$FORM->applyFilter('apply_macros', 'trim');
	$FORM->applyFilter('apply_macros', 'strip_tags');
	$FORM->addRule('apply_macros', gettext('The field whether to apply text macros accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// textarea for tags
	$FORM->addElement('textarea', 'tags', gettext('Tags'),
		array('id' => 'blog_posting_tags', 'cols' => 3, 'rows' => '2', 'class' => 'w298h50'));
	$FORM->applyFilter('tags', 'trim');
	$FORM->applyFilter('tags', 'strip_tags');
	
	// checkbox for draft
	$FORM->addElement('checkbox', 'draft', gettext('Draft'), null,
		array('id' => 'blog_posting_draft', 'class' => 'chbx'));
	$FORM->applyFilter('draft', 'trim');
	$FORM->applyFilter('draft', 'strip_tags');
	$FORM->addRule('draft', gettext('The field whether the posting is a draft accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// checkbox for ping
	$FORM->addElement('checkbox', 'ping', gettext('Ping'), null,
		array('id' => 'blog_posting_ping', 'class' => 'chbx'));
	$FORM->applyFilter('ping', 'trim');
	$FORM->applyFilter('ping', 'strip_tags');
	$FORM->addRule('ping', gettext('The field whether a ping should be issued accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);

	// checkbox for comments_enable
	$FORM->addElement('checkbox', 'comments_enable', gettext('Enable comments'), null,
		array('id' => 'blog_posting_comments_enable', 'class' => 'chbx'));
	$FORM->applyFilter('comments_enable', 'trim');
	$FORM->applyFilter('comments_enable', 'strip_tags');
	$FORM->addRule('comments_enable', gettext('The field whether comments are enabled accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// checkbox for trackbacks_enable
	$FORM->addElement('checkbox', 'trackbacks_enable', gettext('Enable trackbacks'), null,
		array('id' => 'blog_posting_trackbacks_enable', 'class' => 'chbx'));
	$FORM->applyFilter('trackbacks_enable', 'trim');
	$FORM->applyFilter('trackbacks_enable', 'strip_tags');
	$FORM->addRule('trackbacks_enable', gettext('The field whether trackbacks are enabled accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Update blog posting'),
		array('class' => 'submit200'));
	
	// set defaults
	$FORM->setDefaults(array(
		'page' => Base_Cnc::ifsetor($page['id'], null),
		'id' => Base_Cnc::ifsetor($blog_posting['id'], null),
		'title' => Base_Cnc::ifsetor($blog_posting['title'], null),
		'summary' => Base_Cnc::ifsetor($blog_posting['summary_raw'], null),
		'content' => Base_Cnc::ifsetor($blog_posting['content_raw'], null),
		'text_converter' => Base_Cnc::ifsetor($blog_posting['text_converter'], null),
		'apply_macros' => Base_Cnc::ifsetor($blog_posting['apply_macros'], null),
		'tags' => $BLOGTAG->_serializedTagArrayToString(Base_Cnc::ifsetor($blog_posting['tag_array'], null)),
		'draft' => Base_Cnc::ifsetor($blog_posting['draft'], null),
		'ping' => 0,
		'comments_enable' => Base_Cnc::ifsetor($blog_posting['comments_enable'], null),
		'trackbacks_enable' => Base_Cnc::ifsetor($blog_posting['trackbacks_enable'], null)
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
		$BASE->utility->smarty->assign('oak_admin_root_www',
			$BASE->_conf['path']['oak_admin_root_www']);
		
		// build $session
	    $session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], OAK_REGEX_NUMERIC)
	    );
	    
	    // assign $_SESSION to smarty
	    $BASE->utility->smarty->assign('session', $session);
	    
	    // empty $_SESSION
	    if (!empty($_SESSION['response'])) {
	        $_SESSION['response'] = '';
	    }
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('oak_current_user', OAK_CURRENT_USER);
		$BASE->utility->smarty->assign('oak_current_project', OAK_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => OAK_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// assign page
		$BASE->utility->smarty->assign('page', $page);
		
		// display the form
		define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_blogs_postings_edit.html', OAK_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// prepare sql data
		$sqlData = array();
		$sqlData['title'] = $FORM->exportValue('title');
		$sqlData['title_url'] = $HELPER->createMeaningfulString($FORM->exportValue('title'));
		$sqlData['summary_raw'] = $FORM->exportValue('summary');
		$sqlData['summary'] = $FORM->exportValue('summary');
		$sqlData['content_raw'] = $FORM->exportValue('content');
		$sqlData['content'] = $FORM->exportValue('content');
		$sqlData['text_converter'] = ($FORM->exportValue('text_converter') > 0) ? 
			$FORM->exportValue('text_converter') : null;
		$sqlData['apply_macros'] = (string)intval($FORM->exportValue('apply_macros'));
		$sqlData['draft'] = (string)intval($FORM->exportValue('draft'));
		$sqlData['ping'] = (string)intval($FORM->exportValue('ping'));
		$sqlData['comments_enable'] = (string)intval($FORM->exportValue('comments_enable'));
		$sqlData['trackbacks_enable'] = (string)intval($FORM->exportValue('trackbacks_enable'));
		
		// prepare tags
		$sqlData['tag_array'] = $BLOGTAG->_tagStringToSerializedArray($FORM->exportValue('tags'));
		
		// apply text macros and text converter if required
		if ($FORM->exportValue('text_converter') > 0 || $FORM->exportValue('apply_macros') > 0) {
			// extract summary/content
			$summary = $FORM->exportValue('summary');
			$content = $FORM->exportValue('content');

			// apply startup and pre text converter text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
				$summary = $TEXTMACRO->applyTextMacros($summary, 'pre');
				$content = $TEXTMACRO->applyTextMacros($content, 'pre');
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
			}

			// apply post text converter and shutdown text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
				$summary = $TEXTMACRO->applyTextMacros($summary, 'post');
				$content = $TEXTMACRO->applyTextMacros($content, 'post');
			}

			// assign summary/content to sql data array
			$sqlData['summary'] = $summary;
			$sqlData['content'] = $content;
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
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
		
		// issue pings if required
		if ($FORM->exportValue('ping') == 1) {	
			
			// load ping service configuration class
			$PINGSERVICECONFIGURATION = load('application:pingserviceconfiguration');
			
			// load ping service class
			$PINGSERVICE = load('application:pingservice');
			
			// get configured ping service configurations
			$configurations = $PINGSERVICECONFIGURATION->selectPingServiceConfigurations(array('page' => $page['id']));
			
			// issue pings
			foreach ($configurations as $_configuration) {
				$PINGSERVICE->pingService($_configuration['id']);
			}
		}
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: pages_blogs_postings_select.php?page=".$FORM->exportValue('page'));
		exit;
	}
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}
?>