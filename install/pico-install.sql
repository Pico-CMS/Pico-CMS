DROP TABLE IF EXISTS `PREFIX_pico_components`;
>
CREATE TABLE IF NOT EXISTS `PREFIX_pico_components` (
  `component_id` bigint(11) NOT NULL auto_increment,
  `description` varchar(255) default NULL,
  `access` tinyint(1) NOT NULL default '0',
  `css` longtext,
  `javascript` longtext,
  `view_setting` tinyint(1) NOT NULL default '0',
  `folder` varchar(100) NOT NULL,
  `additional_info` longblob,
  `edit_lock` tinyint(1) NOT NULL default '0',
  `delete_lock` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`component_id`)
);
>
DROP TABLE IF EXISTS `PREFIX_pico_contact_form`;
>
CREATE TABLE IF NOT EXISTS `PREFIX_pico_contact_form` (
  `component_id` bigint(11) NOT NULL,
  `recipient_address` varchar(255) NOT NULL,
  `from_subject` varchar(255) NOT NULL,
  `complete_message` text,
  `fields` blob,
  PRIMARY KEY  (`component_id`)
);
>
DROP TABLE IF EXISTS `PREFIX_pico_content`;
>
CREATE TABLE IF NOT EXISTS `PREFIX_pico_content` (
  `instance_id` varchar(32) NOT NULL,
  `component_id` bigint(11) NOT NULL,
  `content` longtext,
  `additional_info` longblob,
  PRIMARY KEY  (`instance_id`)
);
>
DROP TABLE IF EXISTS `PREFIX_pico_content_links`;
>
CREATE TABLE IF NOT EXISTS `PREFIX_pico_content_links` (
  `page_id` bigint(11) NOT NULL,
  `component_id` bigint(11) NOT NULL,
  `position` bigint(11) NOT NULL default '0',
  `location` varchar(100) NOT NULL
);
>
DROP TABLE IF EXISTS `PREFIX_pico_dropdown`;
>
CREATE TABLE IF NOT EXISTS `PREFIX_pico_dropdown` (
  `drop_id` bigint(11) NOT NULL auto_increment,
  `instance_id` varchar(32) NOT NULL,
  `parent_id` bigint(11) NOT NULL default '0',
  `link_id` varchar(30) default NULL,
  `menu_text` varchar(255) default NULL,
  `position` bigint(11) NOT NULL,
  PRIMARY KEY  (`drop_id`)
);
>
DROP TABLE IF EXISTS `PREFIX_pico_links`;
>
CREATE TABLE IF NOT EXISTS `PREFIX_pico_links` (
  `link_id` bigint(11) NOT NULL auto_increment,
  `url` varchar(255) NOT NULL,
  `target` varchar(30) default NULL,
  `caption` varchar(255) default NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`link_id`)
);
>
DROP TABLE IF EXISTS `PREFIX_pico_pages`;
>
CREATE TABLE IF NOT EXISTS `PREFIX_pico_pages` (
  `page_id` bigint(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `theme` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL default '0',
  `www_title` varchar(255) default NULL,
  `keywords` text NOT NULL,
  `description` text NOT NULL,
  `user_access` tinyint(1) NOT NULL default '0',
  `groups` blob,
  PRIMARY KEY  (`page_id`),
  UNIQUE KEY `name` (`name`,`alias`)
);
>
DROP TABLE IF EXISTS `PREFIX_pico_users`;
>
CREATE TABLE IF NOT EXISTS `PREFIX_pico_users` (
  `id` bigint(11) NOT NULL auto_increment,
  `username` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `access` tinyint(1) NOT NULL default '0',
  `last_login` bigint(11) default NULL,
  `last_ip` varchar(39) default NULL,
  `email_address` varchar(255) NOT NULL,
  `first_name` varchar(100) default NULL,
  `last_name` varchar(100) default NULL,
  `user_active` tinyint(1) default '1',
  `registration_active` bigint(11) default NULL,
  `session_id` varchar(50) NOT NULL,
  `additional_data` LONGBLOB NULL,
  PRIMARY KEY  (`id`)
);
>
DROP TABLE IF EXISTS `PREFIX_pico_groups`;
>
CREATE TABLE `PREFIX_pico_groups` (
  `group_id` bigint(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `users` blob,
  PRIMARY KEY  (`group_id`)
);
>
DROP TABLE IF EXISTS `PREFIX_pico_settings`;
>
CREATE TABLE `PREFIX_pico_settings` (
	`keyfield` varchar(32) NOT NULL,
	`keyvalue` varchar(255)
);
>
INSERT INTO `PREFIX_pico_settings` (`keyfield`, `keyvalue`) VALUES ("pico_build_version", "1037");