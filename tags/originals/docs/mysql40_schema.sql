-- phpMyAdmin SQL Dump
-- version 2.6.1-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jun 21, 2005 at 10:01 PM
-- Server version: 4.1.10
-- PHP Version: 4.3.10
-- 
-- Database: `impleri_plus`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `plus_auths`
-- 

CREATE TABLE `plus_auths` (
  `ua_group` int(8) NOT NULL default '0',
  `ua_object` int(8) NOT NULL default '0',
  `ua_perm` char(1) NOT NULL default '',
  KEY `auth_group_object` (`ua_group`,`ua_object`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_auths`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_auths_def`
-- 

CREATE TABLE `plus_auths_def` (
  `ud_object` int(8) NOT NULL default '0',
  `ud_name` varchar(50) NOT NULL default '',
  `ud_desc` text NOT NULL,
  `ud_perms` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`ud_object`),
  UNIQUE KEY `ud_name` (`ud_name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_auths_def`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_blog_cats`
-- 

CREATE TABLE `plus_blog_cats` (
  `bc_id` int(8) NOT NULL auto_increment,
  `bc_blog` int(8) NOT NULL default '0',
  `bc_name` varchar(50) NOT NULL default '',
  `bc_desc` text NOT NULL,
  PRIMARY KEY  (`bc_id`),
  KEY `blog_id` (`bc_blog`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_blog_cats`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_blog_posts`
-- 

CREATE TABLE `plus_blog_posts` (
  `bp_id` int(8) NOT NULL auto_increment,
  `bp_title` text NOT NULL,
  `bp_blog` int(8) NOT NULL default '0',
  `bp_cat` int(8) default NULL,
  `bp_short` varchar(50) NOT NULL default '',
  `bp_time` int(11) NOT NULL default '0',
  `bp_status` tinyint(1) NOT NULL default '1',
  `bp_text` text NOT NULL,
  `bp_extd` text NOT NULL,
  `bp_poll` int(8) default NULL,
  `bp_author` int(8) NOT NULL default '0',
  `bp_ipaddr` varchar(20) default NULL,
  PRIMARY KEY  (`bp_id`),
  UNIQUE KEY `permalink` (`bp_short`),
  KEY `blog_id` (`bp_blog`),
  KEY `blog_category` (`bp_cat`)
) TYPE=MyISAM PACK_KEYS=0;

-- 
-- Dumping data for table `plus_blog_posts`
-- 

INSERT INTO `plus_blog_posts` VALUES (1, 'First Post', 2, 1, 'first_post', 1118365296, 1, 'Yes, there''s even extended info.', 'See baby!', 0, 2, '7f0001');
INSERT INTO `plus_blog_posts` VALUES (2, 'Second Test', 2, 0, 'second_test', 1118361296, 1, 'Hopefully, it''ll work!', '', 0, 2, '7f0001');

-- --------------------------------------------------------

-- 
-- Table structure for table `plus_blogs`
-- 

CREATE TABLE `plus_blogs` (
  `b_id` int(8) NOT NULL auto_increment,
  `b_authors` varchar(255) NOT NULL default '',
  `b_title` text NOT NULL,
  `b_tag` text NOT NULL,
  `b_subtitle` text NOT NULL,
  `b_lastpost` int(8) default NULL,
  `b_parent` int(8) default NULL,
  `b_tpl` varchar(50) NOT NULL default '',
  `b_style` varchar(50) NOT NULL default '',
  `b_imgset` varchar(50) NOT NULL default '',
  `b_box1` text NOT NULL,
  `b_box2` text NOT NULL,
  `b_order` int(8) default NULL,
  PRIMARY KEY  (`b_id`),
  KEY `blog_authors` (`b_authors`),
  KEY `blog_order` (`b_order`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_blogs`
-- 

INSERT INTO `plus_blogs` VALUES (2, '2,3,', 'Testing Blog', 'testblog', 'Just a test blog to see if it works.', 0, 0, 'streaker', 'baby', 'streaker', '', '', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `plus_bots`
-- 

CREATE TABLE `plus_bots` (
  `sb_id` int(8) NOT NULL auto_increment,
  `sb_name` varchar(50) NOT NULL default '',
  `sb_desc` text NOT NULL,
  `sb_status` tinyint(1) NOT NULL default '0',
  `sb_author` varchar(50) NOT NULL default '',
  `sb_filename` varchar(20) NOT NULL default '',
  `sb_iscore` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`sb_id`),
  UNIQUE KEY `bot_filename` (`sb_filename`),
  KEY `bot_status` (`sb_status`),
  KEY `bot_name` (`sb_name`),
  KEY `bot_iscore` (`sb_iscore`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_bots`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_comments`
-- 

CREATE TABLE `plus_comments` (
  `z_id` int(8) NOT NULL auto_increment,
  `z_userid` int(8) NOT NULL default '0',
  `z_type` char(1) NOT NULL default '',
  `z_item` int(8) NOT NULL default '0',
  `z_username` varchar(25) NOT NULL default '',
  `z_time` int(11) NOT NULL default '0',
  `z_text` text NOT NULL,
  `z_link` text NOT NULL,
  `z_ipaddr` varchar(8) NOT NULL default '',
  PRIMARY KEY  (`z_id`),
  KEY `z_type` (`z_type`,`z_item`),
  KEY `z_userid` (`z_userid`),
  KEY `z_ipaddr` (`z_ipaddr`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_comments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_config`
-- 

CREATE TABLE `plus_config` (
  `c_name` varchar(255) NOT NULL default '',
  `c_value` varchar(255) NOT NULL default '',
  `c_static` tinyint(1) NOT NULL default '0',
  `c_override` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`c_name`),
  KEY `config_static` (`c_static`),
  KEY `config_override` (`c_override`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_config`
-- 

INSERT INTO `plus_config` VALUES ('disabled', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('sitename', 'yourdomain.com', 1, 0);
INSERT INTO `plus_config` VALUES ('site_desc', 'A _little_ text to describe your forum', 1, 0);
INSERT INTO `plus_config` VALUES ('cookie', 'plus', 1, 0);
INSERT INTO `plus_config` VALUES ('lifetime', '3600', 1, 0);
INSERT INTO `plus_config` VALUES ('allow_html', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('allow_html_tags', 'b,i,u,pre', 1, 0);
INSERT INTO `plus_config` VALUES ('allow_bbcode', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('allow_smilies', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('allow_sig', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('allow_namechange', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('allow_avatar_local', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('allow_avatar_remote', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('allow_avatar_upload', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('enable_confirm', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('posts_per_page', '15', 1, 0);
INSERT INTO `plus_config` VALUES ('topics_per_page', '50', 1, 0);
INSERT INTO `plus_config` VALUES ('hot_threshold', '25', 1, 0);
INSERT INTO `plus_config` VALUES ('max_poll_options', '10', 1, 0);
INSERT INTO `plus_config` VALUES ('max_sig_chars', '255', 1, 0);
INSERT INTO `plus_config` VALUES ('max_inbox_privmsgs', '50', 1, 0);
INSERT INTO `plus_config` VALUES ('max_sentbox_privmsgs', '25', 1, 0);
INSERT INTO `plus_config` VALUES ('max_savebox_privmsgs', '50', 1, 0);
INSERT INTO `plus_config` VALUES ('board_email_sig', 'Thanks, The Management', 1, 0);
INSERT INTO `plus_config` VALUES ('board_email', 'admin@impleri.net', 1, 0);
INSERT INTO `plus_config` VALUES ('smtp_delivery', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('smtp_host', '', 1, 0);
INSERT INTO `plus_config` VALUES ('smtp_username', '', 1, 0);
INSERT INTO `plus_config` VALUES ('smtp_password', '', 1, 0);
INSERT INTO `plus_config` VALUES ('sendmail_fix', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('require_activation', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('flood_interval', '15', 1, 0);
INSERT INTO `plus_config` VALUES ('board_email_form', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('avatar_filesize', '6144', 1, 0);
INSERT INTO `plus_config` VALUES ('avatar_max_width', '80', 1, 0);
INSERT INTO `plus_config` VALUES ('avatar_max_height', '80', 1, 0);
INSERT INTO `plus_config` VALUES ('default_style', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('default_dateformat', 'D M d, Y g:i a', 1, 0);
INSERT INTO `plus_config` VALUES ('board_timezone', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('prune_enable', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('privmsg_disable', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('gzip_compress', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('recache_tpl', '86400', 1, 0);
INSERT INTO `plus_config` VALUES ('record_online_users', '1', 0, 0);
INSERT INTO `plus_config` VALUES ('record_online_date', '1115432277', 0, 0);
INSERT INTO `plus_config` VALUES ('server_name', 'localhost/pluscms', 1, 0);
INSERT INTO `plus_config` VALUES ('server_port', '80', 1, 0);
INSERT INTO `plus_config` VALUES ('version', '.0.14', 1, 0);
INSERT INTO `plus_config` VALUES ('board_startdate', '1114645784', 1, 0);
INSERT INTO `plus_config` VALUES ('default_lang', 'en_US', 1, 0);
INSERT INTO `plus_config` VALUES ('keep_unreads', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('smart_date', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('topics_split_global', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('topics_split_announces', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('topics_split_stickies', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('default_duration', '7', 1, 0);
INSERT INTO `plus_config` VALUES ('pagination_min', '5', 1, 0);
INSERT INTO `plus_config` VALUES ('pagination_max', '11', 1, 0);
INSERT INTO `plus_config` VALUES ('pagination_percent', '10', 1, 0);
INSERT INTO `plus_config` VALUES ('topic_title_length', '60', 1, 0);
INSERT INTO `plus_config` VALUES ('sub_title_length', '100', 1, 0);
INSERT INTO `plus_config` VALUES ('last_topic_title_length', '25', 1, 0);
INSERT INTO `plus_config` VALUES ('index_pack', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('index_split', '0', 1, 0);
INSERT INTO `plus_config` VALUES ('board_box', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('cache_key', '34bccfab1cb5671e00b5907d0c6445b1', 0, 0);
INSERT INTO `plus_config` VALUES ('enable_cache', '1', 0, 0);
INSERT INTO `plus_config` VALUES ('cache_cfg', '1', 0, 0);
INSERT INTO `plus_config` VALUES ('cache_tpl', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('cache_time_f', '1115434245', 1, 0);
INSERT INTO `plus_config` VALUES ('cache_time_m', '1115532901', 1, 0);
INSERT INTO `plus_config` VALUES ('cache_time_g', '1115532901', 1, 0);
INSERT INTO `plus_config` VALUES ('cache_time_fjbox', '1115434249', 1, 0);
INSERT INTO `plus_config` VALUES ('stats_display_past', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('version_check_delay', '1116033444', 1, 0);
INSERT INTO `plus_config` VALUES ('enable_seo', '1', 1, 0);
INSERT INTO `plus_config` VALUES ('log_user_ip_action_url', 'http://network-tools.com/default.asp?host=', 1, 0);
INSERT INTO `plus_config` VALUES ('announcement_date_display', '1', 0, 0);
INSERT INTO `plus_config` VALUES ('announcement_display', '1', 0, 0);
INSERT INTO `plus_config` VALUES ('announcement_display_forum', '1', 0, 0);
INSERT INTO `plus_config` VALUES ('announcement_split', '1', 0, 0);
INSERT INTO `plus_config` VALUES ('announcement_forum', '1', 0, 0);
INSERT INTO `plus_config` VALUES ('announcement_duration', '7', 0, 0);
INSERT INTO `plus_config` VALUES ('announcement_prune_strategy', '0', 0, 0);
INSERT INTO `plus_config` VALUES ('announcement_last_prune', '1116219599', 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `plus_extensions`
-- 

CREATE TABLE `plus_extensions` (
  `se_id` int(8) NOT NULL auto_increment,
  `se_name` varchar(50) NOT NULL default '',
  `se_desc` text NOT NULL,
  `se_status` tinyint(1) NOT NULL default '0',
  `se_author` varchar(50) NOT NULL default '',
  `se_filename` varchar(20) NOT NULL default '',
  `se_iscore` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`se_id`),
  UNIQUE KEY `extension_filename` (`se_filename`),
  KEY `extension_status` (`se_status`),
  KEY `extension_name` (`se_name`),
  KEY `extension_iscore` (`se_iscore`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_extensions`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_forums`
-- 

CREATE TABLE `plus_forums` (
  `forum_id` smallint(5) unsigned NOT NULL default '0',
  `forum_parent` mediumint(8) unsigned NOT NULL default '0',
  `forum_name` varchar(150) default NULL,
  `forum_desc` text,
  `forum_status` tinyint(4) NOT NULL default '0',
  `forum_type` char(1) NOT NULL default 'f',
  `forum_order` mediumint(8) unsigned NOT NULL default '1',
  `forum_posts` mediumint(8) unsigned NOT NULL default '0',
  `forum_topics` mediumint(8) unsigned NOT NULL default '0',
  `forum_nav_icon` varchar(255) default NULL,
  `forum_icon` varchar(255) default NULL,
  `forum_style` tinyint(4) default '1',
  `forum_tpage` tinyint(2) default '20',
  `forum_sort` varchar(25) default NULL,
  `forum_sort_type` char(1) default 'd',
  `forum_disp` char(1) NOT NULL default 's',
  `forum_box` tinyint(1) NOT NULL default '0',
  `forum_censor` tinyint(1) NOT NULL default '1',
  `forum_count` tinyint(1) NOT NULL default '1',
  `forum_last_post` mediumint(8) unsigned NOT NULL default '0',
  `forum_last_title` varchar(255) default NULL,
  `forum_last_poster` mediumint(8) NOT NULL default '0',
  `forum_last_username` varchar(25) default NULL,
  `forum_last_time` int(11) NOT NULL default '0',
  `auth_view` tinyint(2) NOT NULL default '0',
  `auth_read` tinyint(2) NOT NULL default '0',
  `auth_post` tinyint(2) NOT NULL default '0',
  `auth_reply` tinyint(2) NOT NULL default '0',
  `auth_edit` tinyint(2) NOT NULL default '0',
  `auth_delete` tinyint(2) NOT NULL default '0',
  `auth_sticky` tinyint(2) NOT NULL default '0',
  `auth_announce` tinyint(2) NOT NULL default '0',
  `auth_global` tinyint(2) NOT NULL default '0',
  `auth_vote` tinyint(2) NOT NULL default '0',
  `auth_pollcreate` tinyint(2) NOT NULL default '0',
  `auth_attachments` tinyint(2) NOT NULL default '0',
  `prune_next` int(11) default NULL,
  `prune_enable` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `forum_order` (`forum_order`),
  KEY `forum_parent` (`forum_parent`),
  KEY `forum_last_post` (`forum_last_post`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_forums`
-- 

INSERT INTO `plus_forums` VALUES (1, 0, 'Test Forum', 'This is just a test', 1, 'f', 1, 0, 0, NULL, NULL, 1, 20, 'post', 'd', 's', 0, 1, 1, 0, NULL, 0, NULL, 0, 0, 0, 0, 0, 1, 1, 2, 2, 2, 1, 1, 1, NULL, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `plus_groups`
-- 

CREATE TABLE `plus_groups` (
  `g_id` int(8) NOT NULL auto_increment,
  `g_name` varchar(50) NOT NULL default '',
  `g_type` char(1) NOT NULL default '',
  `g_desc` text NOT NULL,
  `g_status` tinyint(1) NOT NULL default '0',
  `g_admins` text NOT NULL,
  `g_users` text NOT NULL,
  `g_parent` int(8) NOT NULL default '0',
  PRIMARY KEY  (`g_id`),
  UNIQUE KEY `group_name` (`g_name`),
  KEY `group_type` (`g_type`),
  KEY `group_status` (`g_status`),
  KEY `group_parent` (`g_parent`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_groups`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_languages`
-- 

CREATE TABLE `plus_languages` (
  `l_name` varchar(5) NOT NULL default '',
  `l_desc` text NOT NULL,
  `l_status` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `l_name` (`l_name`),
  KEY `l_status` (`l_status`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_languages`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_modules`
-- 

CREATE TABLE `plus_modules` (
  `sm_id` int(8) NOT NULL auto_increment,
  `sm_name` varchar(50) NOT NULL default '',
  `sm_desc` text NOT NULL,
  `sm_status` tinyint(1) NOT NULL default '0',
  `sm_author` varchar(50) NOT NULL default '',
  `sm_filename` varchar(20) NOT NULL default '',
  `sm_position` varchar(20) NOT NULL default '',
  `sm_order` int(8) NOT NULL default '0',
  `sm_iscore` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`sm_id`),
  KEY `module_status` (`sm_status`),
  KEY `module_name` (`sm_name`),
  KEY `module_iscore` (`sm_iscore`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_modules`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_polls`
-- 

CREATE TABLE `plus_polls` (
  `p_id` int(8) NOT NULL auto_increment,
  `p_text` text NOT NULL,
  `p_start` int(11) NOT NULL default '0',
  `p_length` int(11) NOT NULL default '0',
  `p_status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`p_id`),
  KEY `poll_status` (`p_status`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_polls`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_polls_options`
-- 

CREATE TABLE `plus_polls_options` (
  `po_id` int(8) NOT NULL auto_increment,
  `po_poll` int(8) NOT NULL default '0',
  `po_text` text NOT NULL,
  `po_count` int(8) NOT NULL default '0',
  `po_status` tinyint(1) NOT NULL default '0',
  `po_order` int(8) NOT NULL default '0',
  PRIMARY KEY  (`po_id`),
  KEY `poll_id` (`po_poll`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_polls_options`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_polls_voters`
-- 

CREATE TABLE `plus_polls_voters` (
  `pv_poll` int(8) NOT NULL default '0',
  `pv_userid` int(8) NOT NULL default '0',
  `pv_ipaddr` varchar(8) NOT NULL default '',
  `pv_time` int(11) NOT NULL default '0',
  `pv_option` int(8) NOT NULL default '0',
  UNIQUE KEY `user_poll_idx` (`pv_poll`,`pv_userid`),
  KEY `pv_poll` (`pv_poll`),
  KEY `pv_userid` (`pv_userid`),
  KEY `pv_option` (`pv_option`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_polls_voters`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_positions`
-- 

CREATE TABLE `plus_positions` (
  `tp_position` varchar(20) NOT NULL default '',
  `tp_desc` text NOT NULL,
  UNIQUE KEY `tp_position` (`tp_position`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_positions`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_posts`
-- 

CREATE TABLE `plus_posts` (
  `post_id` mediumint(8) unsigned NOT NULL auto_increment,
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `forum_id` mediumint(8) unsigned NOT NULL default '0',
  `poster_id` mediumint(8) NOT NULL default '0',
  `poster_name` varchar(25) NOT NULL default '',
  `poster_ip` varchar(8) NOT NULL default '',
  `post_time` int(11) NOT NULL default '0',
  `post_sig` text NOT NULL,
  `post_avatar` varchar(11) NOT NULL default '',
  `enable_html` tinyint(1) NOT NULL default '0',
  `enable_bbcode` tinyint(1) NOT NULL default '0',
  `enable_sig` tinyint(1) NOT NULL default '0',
  `enable_smiles` tinyint(1) NOT NULL default '0',
  `post_bbcode` varchar(10) NOT NULL default '',
  `post_title` varchar(60) NOT NULL default '',
  `post_subtitle` varchar(60) NOT NULL default '',
  `post_text` text NOT NULL,
  `post_edit_time` int(11) NOT NULL default '0',
  `post_edit` smallint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `poster_id` (`poster_id`),
  KEY `post_time` (`post_time`),
  KEY `forum_id` (`forum_id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_posts`
-- 

INSERT INTO `plus_posts` VALUES (1, 1, 1, 2, 'admin', '', 0, 'Checking Sigs', '', 1, 1, 1, 1, '', 'Welcome', 'to the next level', 'If you can read this, everything works!', 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `plus_ratings`
-- 

CREATE TABLE `plus_ratings` (
  `k_type` char(1) NOT NULL default '',
  `k_item` int(8) NOT NULL default '0',
  `k_user` varchar(8) NOT NULL default '',
  `k_value` tinyint(2) NOT NULL default '0',
  UNIQUE KEY `user_item` (`k_type`,`k_item`,`k_user`),
  KEY `item` (`k_type`,`k_item`),
  KEY `user` (`k_user`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_ratings`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_sessions`
-- 

CREATE TABLE `plus_sessions` (
  `s_id` varchar(32) NOT NULL default '',
  `s_userid` int(8) NOT NULL default '0',
  `s_start` int(11) NOT NULL default '0',
  `s_time` int(11) NOT NULL default '0',
  `s_ipaddr` varchar(8) NOT NULL default '0',
  `s_page` int(11) NOT NULL default '0',
  `s_logged_in` tinyint(1) NOT NULL default '0',
  `s_admin` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`s_id`),
  KEY `user_id_ip_session` (`s_id`,`s_ipaddr`,`s_userid`),
  KEY `userid` (`s_userid`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_sessions`
-- 

INSERT INTO `plus_sessions` VALUES ('4b2f5d695edac2eb63695155d9699f4e', -1, 1119407357, 1119409165, '7f000001', -31, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `plus_styles`
-- 

CREATE TABLE `plus_styles` (
  `t_type` char(1) NOT NULL default '',
  `t_name` varchar(50) NOT NULL default '',
  `t_desc` text NOT NULL,
  `t_status` tinyint(1) NOT NULL default '0',
  `t_author` varchar(50) NOT NULL default '',
  UNIQUE KEY `t_type` (`t_type`,`t_name`),
  KEY `t_status` (`t_status`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_styles`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_topics`
-- 

CREATE TABLE `plus_topics` (
  `topic_id` mediumint(8) unsigned NOT NULL auto_increment,
  `forum_id` smallint(8) unsigned NOT NULL default '0',
  `poll_id` mediumint(8) NOT NULL default '0',
  `topic_type` tinyint(3) NOT NULL default '0',
  `topic_expires` int(11) NOT NULL default '0',
  `topic_status` tinyint(3) NOT NULL default '0',
  `topic_icon` varchar(8) NOT NULL default '',
  `topic_title` varchar(60) NOT NULL default '',
  `topic_subtitle` varchar(60) NOT NULL default '',
  `topic_time` int(11) NOT NULL default '0',
  `topic_views` mediumint(8) unsigned NOT NULL default '0',
  `topic_replies` mediumint(8) unsigned NOT NULL default '0',
  `topic_moved_id` mediumint(8) unsigned NOT NULL default '0',
  `topic_first_post` mediumint(8) unsigned NOT NULL default '0',
  `topic_last_post` mediumint(8) unsigned NOT NULL default '0',
  `topic_first_poster` mediumint(8) NOT NULL default '0',
  `topic_last_poster` mediumint(8) NOT NULL default '0',
  `topic_last_time` int(11) NOT NULL default '0',
  `topic_attach` varchar(11) NOT NULL default '',
  PRIMARY KEY  (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_moved_id` (`topic_moved_id`),
  KEY `topic_status` (`topic_status`),
  KEY `topic_type` (`topic_type`),
  KEY `topic_time` (`topic_time`),
  KEY `topic_last_time` (`topic_last_time`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_topics`
-- 

INSERT INTO `plus_topics` VALUES (1, 1, 0, 1, 0, 1, '', 'Test post', 'First Check', 0, 0, 0, 0, 1, 1, 2, 2, 0, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `plus_user_favs`
-- 

CREATE TABLE `plus_user_favs` (
  `uf_userid` int(8) NOT NULL default '0',
  `uf_type` char(1) NOT NULL default '0',
  `uf_item` int(8) NOT NULL default '0',
  `uf_notify` tinyint(1) NOT NULL default '0',
  KEY `watch_item` (`uf_type`,`uf_item`),
  KEY `watch_user` (`uf_userid`),
  KEY `watch_status` (`uf_notify`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_user_favs`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_user_groups`
-- 

CREATE TABLE `plus_user_groups` (
  `ug_userid` int(8) NOT NULL default '0',
  `ug_groups` text NOT NULL,
  UNIQUE KEY `user_id` (`ug_userid`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_user_groups`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `plus_users`
-- 

CREATE TABLE `plus_users` (
  `u_id` mediumint(8) NOT NULL default '0',
  `u_active` tinyint(1) default '0',
  `username` varchar(25) NOT NULL default '',
  `u_name` varchar(50) NOT NULL default '',
  `u_password` varchar(32) NOT NULL default '',
  `u_session_time` int(11) NOT NULL default '0',
  `u_session_page` smallint(5) NOT NULL default '0',
  `u_lastvisit` int(11) NOT NULL default '0',
  `u_regdate` int(11) NOT NULL default '0',
  `u_posts` mediumint(8) unsigned NOT NULL default '0',
  `u_timezone` decimal(5,2) NOT NULL default '0.00',
  `u_style` varchar(50) NOT NULL default '',
  `u_tpl` varchar(50) NOT NULL default '',
  `u_imgset` varchar(50) NOT NULL default '',
  `u_lang` varchar(255) default NULL,
  `u_dateformat` varchar(14) NOT NULL default 'd M Y H:i',
  `u_unread_privmsg` smallint(5) unsigned NOT NULL default '0',
  `u_last_privmsg` int(11) NOT NULL default '0',
  `u_viewemail` tinyint(1) default NULL,
  `u_attachsig` tinyint(1) default NULL,
  `u_allowhtml` tinyint(1) default '1',
  `u_allowbbcode` tinyint(1) default '1',
  `u_allowsmile` tinyint(1) default '1',
  `u_allowavatar` tinyint(1) NOT NULL default '1',
  `u_allow_pm` tinyint(1) NOT NULL default '1',
  `u_allow_viewonline` tinyint(1) NOT NULL default '1',
  `u_notify` tinyint(1) NOT NULL default '1',
  `u_notify_pm` tinyint(1) NOT NULL default '0',
  `u_popup_pm` tinyint(1) NOT NULL default '0',
  `u_rank` int(11) default '0',
  `u_avatar` varchar(100) default NULL,
  `u_avatar_type` tinyint(4) NOT NULL default '0',
  `u_email` varchar(255) default NULL,
  `u_icq` varchar(15) default NULL,
  `u_website` varchar(100) default NULL,
  `u_from` varchar(100) default NULL,
  `u_sig` text,
  `u_sig_bbcode_uid` varchar(10) default NULL,
  `u_aim` varchar(255) default NULL,
  `u_yim` varchar(255) default NULL,
  `u_msnm` varchar(255) default NULL,
  `u_occ` varchar(100) default NULL,
  `u_interests` varchar(255) default NULL,
  `u_actkey` varchar(32) default NULL,
  PRIMARY KEY  (`u_id`),
  KEY `u_session_time` (`u_session_time`),
  KEY `u_name` (`u_name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_users`
-- 

INSERT INTO `plus_users` VALUES (-1, 0, 'guest', 'Anonymous', '', 0, 0, 0, 0, 0, 0.00, '', '0', '0', NULL, 'd M Y H:i', 0, 0, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `plus_users` VALUES (2, 1, 'admin', 'Administrator', '3e47b75000b0924b6c9ba5759a7cf15d', 0, 0, 1119396191, 0, 0, 0.00, 'streaker', 'streaker', 'streaker', 'en_UK', 'd M Y H:i', 0, 0, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, NULL, 0, 'admin@impleri.net', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `plus_users` VALUES (3, 1, 'joemama', 'Joe Mama', 'niosgheriohas', 0, 0, 0, 0, 0, 0.00, '', '', '', NULL, 'd M Y H:i', 0, 0, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `plus_watch`
-- 

CREATE TABLE `plus_watch` (
  `q_userid` int(8) NOT NULL default '0',
  `q_type` char(1) NOT NULL default '0',
  `q_item` int(8) NOT NULL default '0',
  `q_notify` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `user_item` (`q_userid`,`q_type`,`q_item`),
  KEY `watch_item` (`q_type`,`q_item`),
  KEY `watch_user` (`q_userid`),
  KEY `watch_status` (`q_notify`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `plus_watch`
-- 

INSERT INTO `plus_watch` VALUES (1, 'f', 2, 1);
