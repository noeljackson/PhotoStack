# CocoaMySQL dump
# Version 0.7b4
# http://cocoamysql.sourceforge.net
#
# Host: localhost (MySQL 5.0.24-standard)
# Database: pstack
# Generation Time: 2007-03-27 20:04:32 -0400
# ************************************************************

# Dump of table ps_galleries
# ------------------------------------------------------------

CREATE TABLE `ps_galleries` (
  `galleryid` char(255) NOT NULL default '',
  `filename` char(255) default NULL,
  `name` char(255) default NULL,
  `desc` char(255) default NULL,
  `long_desc` char(255) default NULL,
  `date` char(255) default NULL,
  UNIQUE KEY `UNIQUE` (`galleryid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table ps_images
# ------------------------------------------------------------

CREATE TABLE `ps_images` (
  `galleryid` char(255) NOT NULL default '',
  `filename` char(255) default NULL,
  `name` char(255) default NULL,
  `desc` longtext,
  `long_desc` longtext,
  `date` char(255) default NULL,
  `sort` int(255) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table ps_users
# ------------------------------------------------------------

CREATE TABLE `ps_users` (
  `username` char(255) NOT NULL default '',
  `userpass` char(255) NOT NULL default '',
  `permissions` int(255) default NULL,
  `email` text,
  `name` text,
  `description` text,
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `ps_users` (`username`,`userpass`,`permissions`,`email`,`name`,`description`) VALUES ('admin','21232f297a57a5a743894a0e4a801fc3','1024',NULL,NULL,NULL);


