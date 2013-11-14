<?php

// the purpose of this file is to ensure that the database tables for the blog are set up properly

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

$blog_entries        = DB_PREFIX . 'pico_blog_entries';
$blog_categories     = DB_PREFIX . 'pico_blog_categories';
$blog_comments       = DB_PREFIX . 'pico_blog_comments';
$blog_category_links = DB_PREFIX . 'pico_blog_category_links';

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$blog_entries` (
	`post_id` BIGINT(11) auto_increment,
	`component_id` BIGINT(11) NOT NULL,
	`date` BIGINT(11) NOT NULL,
	`saved_date` BIGINT(11) NOT NULL,
	`title` VARCHAR(255) NOT NULL,
	`tags` blob,
	`post` longtext,
	`alias` VARCHAR(255) NOT NULL,
	`published` TINYINT(1) NOT NULL DEFAULT 1,
	`allow_comments` TINYINT(1) NOT NULL DEFAULT 1,
	`auto_saved_date` BIGINT(11),
	`auto_saved_post` longtext,
	`last_saved_date` BIGINT(11),
	`last_saved_post` longtext,
	`related1` BIGINT(11) NULL,
	`related2` BIGINT(11) NULL,
	`related3` BIGINT(11) NULL,
	`scheduled_date` BIGINT(11) NOT NULL DEFAULT 0,
	`story_image` VARCHAR(255),
	`by_line` TEXT,
	PRIMARY KEY(`post_id`));
SQL
);

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$blog_categories` (
	`category_id` BIGINT(11) auto_increment,
	`title` VARCHAR(255) NOT NULL,
	`component_id` VARCHAR(11) NOT NULL,
	`alias` VARCHAR(255) NOT NULL,
	PRIMARY KEY(`category_id`));
SQL
);

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$blog_comments` (
	`comment_id` BIGINT(11) auto_increment,
	`post_id` BIGINT(11) NOT NULL,
	`name` VARCHAR(32) NOT NULL,
	`active` TINYINT(1) NOT NULL DEFAULT 0,
	`date` BIGINT(11) NOT NULL,
	`ip_address` VARCHAR(50) NOT NULL,
	`message` text,
	`url` VARCHAR(255),
	`email` VARCHAR(255),
	`parent` BIGINT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY(`comment_id`));
SQL
);

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$blog_category_links` (
	`post_id` BIGINT(11),
	`category_id` BIGINT(11)
);
SQL
);

// there have been updates to the blog from when those initial tables were created.
// below are a bunch of checks to make sure the blog is to its latest database version

// check blog comments table

$fields = $db->assoc('SHOW COLUMNS FROM `'.$blog_comments.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (!in_array('parent', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_comments.'` ADD COLUMN `parent` BIGINT(11) NOT NULL DEFAULT 0');
}

// check blog entries table

$fields = $db->assoc('SHOW COLUMNS FROM `'.$blog_entries.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (!in_array('allow_comments', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `allow_comments` TINYINT(1) NOT NULL DEFAULT 1');
}

if (!in_array('auto_saved_date', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `auto_saved_date` BIGINT(11)');
}

if (!in_array('auto_saved_post', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `auto_saved_post` longtext');
}

if (!in_array('last_saved_date', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `last_saved_date` BIGINT(11)');
}

if (!in_array('last_saved_post', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `last_saved_post` longtext');
}

if (!in_array('saved_date', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `saved_date` BIGINT(11) NOT NULL DEFAULT 0');
}

if (!in_array('story_image', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `story_image` VARCHAR(255)');
}

if (!in_array('published', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `published` TINYINT(1) NOT NULL DEFAULT 1');
}

if (!in_array('by_line', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `by_line` TEXT');
}

if (!in_array('author', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `author` VARCHAR(255)');
}

if (!in_array('image_caption', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `image_caption` TEXT');
}

if (!in_array('related1', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `related1` BIGINT(11) NULL');
}

if (!in_array('related2', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `related2` BIGINT(11) NULL');
}

if (!in_array('related3', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `related3` BIGINT(11) NULL');
}

if (!in_array('scheduled_date', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `scheduled_date` BIGINT(11) NOT NULL DEFAULT 0');
}

if (in_array('category', $all_fields))
{
	// go thru entries, put in new category table
	$entries = $db->force_multi_assoc('SELECT * FROM `'.$blog_entries.'`');
	if (is_array($entries)) 
	{
		foreach ($entries as $entry)
		{
			$category_id = $entry['category'];
			$post_id     = $entry['post_id'];

			$db->run('INSERT INTO `'.$blog_category_links.'` (`post_id`, `category_id`) VALUES (?,?)',
				$post_id, $category_id
			);
		}
	}
	$db->run('ALTER TABLE `'.$blog_entries.'` DROP COLUMN `category`');
}
// check blog categories table

$fields = $db->assoc('SHOW COLUMNS FROM `'.$blog_categories.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (in_array('position', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_categories.'` DROP `position`');
}


?>