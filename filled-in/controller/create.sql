# Modification 1.7.6 - CHARSET = latin1 added to each SQL statement

# Dump of table filled_in_data
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `filled_in_data` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `form_id` int(11) unsigned NOT NULL default '0',
  `user_agent` int(11) unsigned NOT NULL default '0',
  `ip` int(11) unsigned NOT NULL default '0',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `data` text,
  `cookie` text,
  `upload` text,
  `time_to_complete` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) CHARSET = latin1;



# Dump of table filled_in_errors
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `filled_in_errors` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `form_id` int(11) unsigned NOT NULL default '0',
  `data_id` int(11) unsigned NOT NULL default '0',
  `type` enum('pre','post','filter','result') NOT NULL default 'pre',
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) CHARSET = latin1;



# Dump of table filled_in_extensions
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `filled_in_extensions` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `form_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(50) default NULL,
  `base` enum('pre','filter','post','result') NOT NULL default 'pre',
  `type` varchar(50) NOT NULL,
  `config` text,
  `position` int(10) unsigned NOT NULL default '0',
  `status` enum('on','off') NOT NULL default 'on',
  PRIMARY KEY  (`id`)
) CHARSET = latin1;



# Dump of table filled_in_forms
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `filled_in_forms` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `quickview` varchar(40) NOT NULL default '',
  `options` mediumtext,
  `type` enum('form','report') default 'form',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) CHARSET = latin1;



# Dump of table filled_in_useragents
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `filled_in_useragents` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `agent` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `agent` (`agent`)
) CHARSET = latin1;



# Indexation of filled_in_errors for fast joining with filled_in_data
# --------------------------------------------------------------

CREATE INDEX filled_errors_data ON `filled_in_errors` (`data_id`);
CREATE INDEX filled_form_data ON `filled_in_data` (`form_id`);
