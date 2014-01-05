#
# Database installation script
# 
# This script should not be run directly, but rather be executed by using install.php
#
# @package Setup
# @author  Andreas Goetz <cpuidle@gmx.de>
# @version $Id: install.sql,v 1.22 2013/03/13 15:26:50 andig2 Exp $
#

# genres
CREATE TABLE genres (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL,
	PRIMARY KEY (id)
) CHARACTER SET UTF8;

# lent
CREATE TABLE lent (
	diskid VARCHAR(15) NOT NULL,
	who VARCHAR(255) NOT NULL,
	dt TIMESTAMP NOT NULL,
	PRIMARY KEY (diskid)
) CHARACTER SET UTF8;

# mediatypes
CREATE TABLE mediatypes (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(15) NULL,
	PRIMARY KEY (id)
) CHARACTER SET UTF8;

# videodata
CREATE TABLE videodata (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  md5 VARCHAR(32) DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  subtitle VARCHAR(255) DEFAULT NULL,
  language VARCHAR(255) DEFAULT NULL,
  diskid VARCHAR(15) DEFAULT NULL,
  comment VARCHAR(255) DEFAULT NULL,
  disklabel VARCHAR(32) DEFAULT NULL,
  imdbID VARCHAR(30) DEFAULT NULL,
  year INT(4) UNSIGNED NOT NULL DEFAULT '0',
  imgurl VARCHAR(255) DEFAULT NULL,
  director VARCHAR(255) DEFAULT NULL,
  actors MEDIUMTEXT,
  runtime INT(10) UNSIGNED DEFAULT NULL,
  country VARCHAR(255) DEFAULT NULL,
  plot TEXT,
  rating VARCHAR(15) DEFAULT NULL,
  filename VARCHAR(255) DEFAULT NULL,
  filesize BIGINT UNSIGNED DEFAULT NULL,
  filedate DATETIME DEFAULT NULL,
  audio_codec VARCHAR(255) DEFAULT NULL,
  video_codec VARCHAR(255) DEFAULT NULL,
  video_width INT(10) UNSIGNED DEFAULT NULL,
  video_height INT(10) UNSIGNED DEFAULT NULL,
  istv tinyINT(1) UNSIGNED NOT NULL DEFAULT '0',
  has3d tinyINT(1) UNSIGNED NOT NULL DEFAULT '0',
  lastupdate TIMESTAMP NOT NULL,
  mediatype INT(10) UNSIGNED NOT NULL DEFAULT '0',
  custom1 VARCHAR(255) DEFAULT NULL,
  custom2 VARCHAR(255) DEFAULT NULL,
  custom3 VARCHAR(255) DEFAULT NULL,
  custom4 VARCHAR(255) DEFAULT NULL,
  created DATETIME DEFAULT NULL,
  `owner_id` INT(11) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`),
  KEY `title_idx` (`title`),
  KEY `diskid_idx` (`diskid`),
  KEY `mediatype` (`mediatype`,`istv`),
  FULLTEXT KEY `plot_idx` (`plot`),
  FULLTEXT KEY `actors_idx` (`actors`),
  FULLTEXT KEY `comment` (`comment`)
) CHARACTER SET UTF8, ENGINE=MyISAM;

# videogenre
CREATE TABLE videogenre (
	video_id INT(10) UNSIGNED NOT NULL,
	genre_id INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (video_id, genre_id)
) CHARACTER SET UTF8;

# add genres

INSERT INTO genres (id, name) VALUES (1,'Action');
INSERT INTO genres (id, name) VALUES (2,'Adventure');
INSERT INTO genres (id, name) VALUES (3,'Animation');
INSERT INTO genres (id, name) VALUES (4,'Comedy');
INSERT INTO genres (id, name) VALUES (5,'Crime');
INSERT INTO genres (id, name) VALUES (6,'Documentary');
INSERT INTO genres (id, name) VALUES (7,'Drama');
INSERT INTO genres (id, name) VALUES (8,'Family');
INSERT INTO genres (id, name) VALUES (9,'Fantasy');
INSERT INTO genres (id, name) VALUES (10,'Film-Noir');
INSERT INTO genres (id, name) VALUES (11,'Horror');
INSERT INTO genres (id, name) VALUES (12,'Musical');
INSERT INTO genres (id, name) VALUES (13,'Mystery');
INSERT INTO genres (id, name) VALUES (14,'Romance');
INSERT INTO genres (id, name) VALUES (15,'Sci-Fi');
INSERT INTO genres (id, name) VALUES (16,'Short');
INSERT INTO genres (id, name) VALUES (17,'Thriller');
INSERT INTO genres (id, name) VALUES (18,'War');
INSERT INTO genres (id, name) VALUES (19,'Western');
INSERT INTO genres (id, name) VALUES (20,'Adult');
INSERT INTO genres (id, name) VALUES (21,'Music');
INSERT INTO genres (id, name) VALUES (22,'Biography');
INSERT INTO genres (id, name) VALUES (23,'History');
INSERT INTO genres (id, name) VALUES (24,'Sport');

# add mediatypes

INSERT INTO mediatypes (id, name) VALUES (1,'DVD');
INSERT INTO mediatypes (id, name) VALUES (2,'SVCD');
INSERT INTO mediatypes (id, name) VALUES (3,'VCD');
INSERT INTO mediatypes (id, name) VALUES (4,'CD-R');
INSERT INTO mediatypes (id, name) VALUES (5,'CD-RW');
INSERT INTO mediatypes (id, name) VALUES (6,'VHS');
INSERT INTO mediatypes (id, name) VALUES (7,'DVD-R');
INSERT INTO mediatypes (id, name) VALUES (8,'DVD-RW');
INSERT INTO mediatypes (id, name) VALUES (9,'DVD+R');
INSERT INTO mediatypes (id, name) VALUES (10,'DVD+RW');
INSERT INTO mediatypes (id, name) VALUES (11,'DVD-DL');
INSERT INTO mediatypes (id, name) VALUES (12,'DVD+DL');
INSERT INTO mediatypes (id, name) VALUES (13,'LaserDisc');
INSERT INTO mediatypes (id, name) VALUES (14,'HDD');
INSERT INTO mediatypes (id, name) VALUES (15,'HD-DVD');
INSERT INTO mediatypes (id, name) VALUES (16,'Blu-ray');
INSERT INTO mediatypes (id, name) VALUES (17,'AVCHD');
INSERT INTO mediatypes (id, name) VALUES (18,'CD');
INSERT INTO mediatypes (id, name) VALUES (50,'wanted');

# configuration
CREATE TABLE config (
  opt VARCHAR(50) NOT NULL,
  value VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (opt)
) CHARACTER SET UTF8;

# actors
CREATE TABLE actors (
  name VARCHAR(255) NOT NULL,
  actorid VARCHAR(15) NOT NULL DEFAULT '',
  imgurl VARCHAR(255) NOT NULL DEFAULT '',
  checked TIMESTAMP NOT NULL,
  PRIMARY KEY (`name`),
  KEY `actorid` (`actorid`)
) CHARACTER SET UTF8;

# multiusersupport
CREATE TABLE users (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `passwd` VARCHAR(100) NOT NULL DEFAULT '',
  `cookiecode` VARCHAR(100) DEFAULT NULL,
  `permissions` INT(10) UNSIGNED DEFAULT NULL,
  `timestamp` TIMESTAMP NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) CHARACTER SET UTF8;
INSERT INTO users (id, name, passwd, permissions) VALUES (1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 7);

# user-specific configuration
CREATE TABLE userconfig (
  `user_id` INT(11) NOT NULL DEFAULT '0',
  `opt` VARCHAR(50) NOT NULL DEFAULT '',
  `value` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`user_id`,`opt`)
) CHARACTER SET UTF8;

# user seen table
CREATE TABLE userseen (
  `video_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`video_id`,`user_id`)
) CHARACTER SET UTF8;

# cache table
CREATE TABLE cache (
  `tag` VARCHAR(45) NOT NULL,
  `value` VARCHAR(32) DEFAULT NULL,
  PRIMARY KEY (`tag`)
) CHARACTER SET UTF8;

# set DEFAULT options

# some defaults
REPLACE INTO `config` (opt, value) VALUES ('language', 'en');
REPLACE INTO `config` (opt, value) VALUES ('mediadefault', '4');
REPLACE INTO `config` (opt, value) VALUES ('langdefault', 'english');
REPLACE INTO `config` (opt, value) VALUES ('filterdefault', 'unseen');
REPLACE INTO `config` (opt, value) VALUES ('IMDBage', '432000');
REPLACE INTO `config` (opt, value) VALUES ('thumbnail', '1');
REPLACE INTO `config` (opt, value) VALUES ('template', 'nexgen::nexgen');
REPLACE INTO `config` (opt, value) VALUES ('languageflags', 'german::spanish::english::french');
REPLACE INTO `config` (opt, value) VALUES ('adultgenres', '20');

REPLACE INTO `config` (opt, value) VALUES ('imdbBrowser', 1);
REPLACE INTO `config` (opt, value) VALUES ('actorpics', 1);
REPLACE INTO `config` (opt, value) VALUES ('listcolumns', 6);
REPLACE INTO `config` (opt, value) VALUES ('castcolumns', 4);

REPLACE INTO `config` (opt, value) VALUES ('enginedefault', 'imdb');
REPLACE INTO `config` (opt, value) VALUES ('engineimdb', 1);
REPLACE INTO `config` (opt, value) VALUES ('engineamazon', 1);
REPLACE INTO `config` (opt, value) VALUES ('engineamazonaws', 1);
REPLACE INTO `config` (opt, value) VALUES ('enginegoogle', 1);
REPLACE INTO `config` (opt, value) VALUES ('engineyoutube', 1);

# user permissions
CREATE TABLE permissions (
  `from_uid` INT(11) NOT NULL,
  `to_uid` INT(11) NOT NULL,
  `permissions` INT(10) UNSIGNED DEFAULT NULL,
  `timestamp` TIMESTAMP NOT NULL,
  PRIMARY KEY (`from_uid`,`to_uid`)
) CHARACTER SET UTF8;

# create guest user
INSERT IGNORE INTO config (opt, value) VALUES ('guestid', '10000');
INSERT IGNORE INTO config (opt, value) VALUES ('adminid', '1');
INSERT IGNORE INTO config (opt, value) VALUES ('denyguest', '1');
INSERT IGNORE INTO users (id, name, passwd, permissions) VALUES (10000 ,'Guest', '---', 2);

#
# IMPORTANT
#
# Always increase this number in install/install.sql, install/upgrade.sql and
# core/constants.php when changing the database structure!
#

REPLACE INTO config (opt,value) VALUES ('dbversion', 42);
