--
-- PEAR::LiveUser Database Schema
--
-- $Id$
--

CREATE TABLE `liveuser_applications` (
  `application_id` int(11) default '0',
  `application_define_name` char(32) default NULL,
  UNIQUE KEY `application_id_idx` (`application_id`),
  UNIQUE KEY `define_name_i_idx` (`application_define_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_applications_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_area_admin_areas` (
  `area_id` int(11) default '0',
  `perm_user_id` int(11) default '0',
  UNIQUE KEY `id_i_idx` (`area_id`,`perm_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_areas` (
  `area_id` int(11) default '0',
  `application_id` int(11) default '0',
  `area_define_name` char(32) default NULL,
  UNIQUE KEY `area_id_idx` (`area_id`),
  UNIQUE KEY `define_name_i_idx` (`application_id`,`area_define_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_areas_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_group_subgroups` (
  `group_id` int(11) default '0',
  `subgroup_id` int(11) default '0',
  UNIQUE KEY `id_i_idx` (`group_id`,`subgroup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_grouprights` (
  `group_id` int(11) default '0',
  `right_id` int(11) default '0',
  `right_level` int(11) default '0',
  UNIQUE KEY `id_i_idx` (`group_id`,`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_groups` (
  `group_id` int(11) default '0',
  `group_type` int(11) default '0',
  `group_define_name` char(32) default NULL,
  `is_active` tinyint(1) default '1',
  `owner_user_id` int(11) default '0',
  `owner_group_id` int(11) default '0',
  UNIQUE KEY `group_id_idx` (`group_id`),
  UNIQUE KEY `define_name_i_idx` (`group_define_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_groups_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_groupusers` (
  `perm_user_id` int(11) default '0',
  `group_id` int(11) default '0',
  UNIQUE KEY `id_i_idx` (`perm_user_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_perm_users` (
  `perm_user_id` int(11) default '0',
  `auth_user_id` char(32) default NULL,
  `auth_container_name` char(32) default NULL,
  `perm_type` int(11) default '0',
  UNIQUE KEY `perm_user_id_idx` (`perm_user_id`),
  UNIQUE KEY `auth_id_i_idx` (`auth_user_id`,`auth_container_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_perm_users_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_right_implied` (
  `right_id` int(11) default '0',
  `implied_right_id` int(11) default '0',
  UNIQUE KEY `id_i_idx` (`right_id`,`implied_right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_rights` (
  `right_id` int(11) default '0',
  `area_id` int(11) default '0',
  `right_define_name` char(32) default NULL,
  `has_implied` tinyint(1) default '1',
  UNIQUE KEY `right_id_idx` (`right_id`),
  UNIQUE KEY `define_name_i_idx` (`area_id`,`right_define_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_rights_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_translations` (
  `translation_id` int(11) default '0',
  `section_id` int(11) default '0',
  `section_type` int(11) default '0',
  `language_id` char(32) default NULL,
  `name` char(32) default NULL,
  `description` char(32) default NULL,
  UNIQUE KEY `translation_id_idx` (`translation_id`),
  UNIQUE KEY `translation_i_idx` (`section_id`,`section_type`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_translations_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_userrights` (
  `perm_user_id` int(11) default '0',
  `right_id` int(11) default '0',
  `right_level` int(11) default '0',
  UNIQUE KEY `id_i_idx` (`perm_user_id`,`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_users` (
  `auth_user_id` char(32) default NULL,
  `handle` char(32) default NULL,
  `passwd` char(32) default NULL,
  `owner_user_id` int(11) default NULL,
  `owner_group_id` int(11) default NULL,
  `lastlogin` datetime default NULL,
  `is_active` tinyint(1) default '1',
  UNIQUE KEY `auth_user_id_idx` (`auth_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `liveuser_users_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
