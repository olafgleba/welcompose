<?php

/**
 * Project: Welcompose
 * File: table_aliases.inc.php
 * 
 * Copyright (c) 2008 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// application table aliases
define("WCOM_DB_APPLICATION_INFO", "application_info");
define("WCOM_DB_APPLICATION_PING_SERVICES", "application_ping_services");
define("WCOM_DB_APPLICATION_PING_SERVICE_CONFIGURATIONS", "application_ping_service_configurations");
define("WCOM_DB_APPLICATION_PROJECTS", "application_projects");
define("WCOM_DB_APPLICATION_TEXT_CONVERTERS", "application_text_converters");
define("WCOM_DB_APPLICATION_TEXT_MACROS", "application_text_macros");

// community table aliases
define("WCOM_DB_COMMUNITY_ANTI_SPAM_PLUGINS", "community_anti_spam_plugins");
define("WCOM_DB_COMMUNITY_BLOG_COMMENT_STATUSES", "community_blog_comment_statuses");
define("WCOM_DB_COMMUNITY_BLOG_COMMENTS", "community_blog_comments");
define("WCOM_DB_COMMUNITY_SETTINGS", "community_settings");

// content table aliases
define("WCOM_DB_CONTENT_ABBREVIATIONS", "content_abbreviations");
define("WCOM_DB_CONTENT_BLOG_PODCASTS", "content_blog_podcasts");
define("WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES", "content_blog_podcast_categories");
define("WCOM_DB_CONTENT_BLOG_POSTINGS", "content_blog_postings");
define("WCOM_DB_CONTENT_BLOG_TAGS", "content_blog_tags");
define("WCOM_DB_CONTENT_BLOG_TAGS2CONTENT_BLOG_POSTINGS", "content_blog_tags2content_blog_postings");
define("WCOM_DB_CONTENT_EVENT_POSTINGS", "content_event_postings");
define("WCOM_DB_CONTENT_EVENT_TAGS", "content_event_tags");
define("WCOM_DB_CONTENT_EVENT_TAGS2CONTENT_EVENT_POSTINGS", "content_event_tags2content_event_postings");
define("WCOM_DB_CONTENT_BOXES", "content_boxes");
define("WCOM_DB_CONTENT_GENERATOR_FORMS", "content_generator_forms");
define("WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS", "content_generator_form_fields");
define("WCOM_DB_CONTENT_GLOBAL_BOXES", "content_global_boxes");
define("WCOM_DB_CONTENT_NAVIGATIONS", "content_navigations");
define("WCOM_DB_CONTENT_NODES", "content_nodes");
define("WCOM_DB_CONTENT_PAGES", "content_pages");
define("WCOM_DB_CONTENT_PAGES2USER_GROUPS", "content_pages2user_groups");
define("WCOM_DB_CONTENT_PAGE_TYPES", "content_page_types");
define("WCOM_DB_CONTENT_SIMPLE_DATES", "content_simple_dates");
define("WCOM_DB_CONTENT_SIMPLE_FORMS", "content_simple_forms");
define("WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS", "content_simple_guestbooks");
define("WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES", "content_simple_guestbook_entries");
define("WCOM_DB_CONTENT_SIMPLE_PAGES", "content_simple_pages");
define("WCOM_DB_CONTENT_STRUCTURAL_TEMPLATES", "content_structural_templates");

// media table aliases
define("WCOM_DB_MEDIA_OBJECTS", "media_objects");
define("WCOM_DB_MEDIA_OBJECTS2MEDIA_TAGS", "media_objects2media_tags");
define("WCOM_DB_MEDIA_TAGS", "media_tags");

// templating table aliases
define("WCOM_DB_TEMPLATING_GLOBAL_FILES", "templating_global_files");
define("WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES", "templating_global_templates");
define("WCOM_DB_TEMPLATING_TEMPLATES", "templating_templates");
define("WCOM_DB_TEMPLATING_TEMPLATE_SETS", "templating_template_sets");
define("WCOM_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES", "templating_template_sets2templating_templates");
define("WCOM_DB_TEMPLATING_TEMPLATE_TYPES", "templating_template_types");

// user table aliases
define("WCOM_DB_USER_GROUPS", "user_groups");
define("WCOM_DB_USER_GROUPS2USER_RIGHTS", "user_groups2user_rights");
define("WCOM_DB_USER_RIGHTS", "user_rights");
define("WCOM_DB_USER_USERS", "user_users");
define("WCOM_DB_USER_USERS2APPLICATION_PROJECTS", "user_users2application_projects");
define("WCOM_DB_USER_USERS2USER_GROUPS", "user_users2user_groups");

?>