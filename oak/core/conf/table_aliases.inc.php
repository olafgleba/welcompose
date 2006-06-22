<?php

/**
 * Project: Oak
 * File: table_aliases.inc.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

// application table aliases
define("OAK_DB_APPLICATION_PING_SERVICES", "application_ping_services");
define("OAK_DB_APPLICATION_PING_SERVICE_CONFIGURATIONS", "application_ping_service_configurations");
define("OAK_DB_APPLICATION_PROJECTS", "application_projects");
define("OAK_DB_APPLICATION_TEXT_CONVERTER", "application_text_converter");

// community table aliases
define("OAK_DB_COMMUNITY_BLOG_COMMENTS", "community_blog_comments");

// content table aliases
define("OAK_DB_CONTENT_BLOG_POSTINGS", "content_blog_postings");
define("OAK_DB_CONTENT_BLOG_TAGS", "content_blog_tags");
define("OAK_DB_CONTENT_BLOG_TAGS2CONTENT_BLOG_POSTINGS", "content_blog_tags2content_blog_postings");
define("OAK_DB_CONTENT_BOXES", "content_boxes");
define("OAK_DB_CONTENT_NAVIGATIONS", "content_navigations");
define("OAK_DB_CONTENT_NODES", "content_nodes");
define("OAK_DB_CONTENT_PAGES", "content_pages");
define("OAK_DB_CONTENT_PAGES2USER_GROUPS", "content_pages2application_groups");
define("OAK_DB_CONTENT_PAGE_TYPES", "content_page_types");
define("OAK_DB_CONTENT_SIMPLE_PAGE", "content_simple_page");

// media table aliases
define("OAK_DB_MEDIA_DOCUMENTS", "media_documents");
define("OAK_DB_MEDIA_IMAGES", "media_images");
define("OAK_DB_MEDIA_IMAGE_THUMBNAILS", "media_image_thumbnails");

// templating table aliases
define("OAK_DB_TEMPLATING_TEMPLATES", "templating_templates");
define("OAK_DB_TEMPLATING_TEMPLATE_SETS", "templating_template_sets");
define("OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES", "templating_template_sets2templating_templates");
define("OAK_DB_TEMPLATING_TEMPLATE_TYPES", "templating_template_types");

// user table aliases
define("OAK_DB_USER_GROUPS", "user_groups");
define("OAK_DB_USER_USERS", "user_users");
define("OAK_DB_USER_USERS2APPLICATION_PROJECTS", "user_users2application_projects");
define("OAK_DB_USER_USERS2USER_GROUPS", "user_users2user_groups");

?>