#
# Database upgrade script
# 
# This script should not be run directly, but rather be executed by using install.php.
# Otherwise, important data conversion steps may be missing.
#
# @package Setup
# @author  Andreas Goetz <cpuidle@gmx.de>
# @version $Id: upgrade.sql,v 1.21 2013/03/16 10:10:07 andig2 Exp $
#

# These were introduced by accident but are not needed
DROP TABLE IF EXISTS codec;
DROP TABLE IF EXISTS files;
DROP TABLE IF EXISTS imdb;
DROP TABLE IF EXISTS video;

# mediatypes
CREATE TABLE IF NOT EXISTS mediatypes(
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(15) NULL,
	PRIMARY KEY (id)
);

# add mediatypes
INSERT IGNORE INTO mediatypes (id, name) VALUES (1,'DVD');
INSERT IGNORE INTO mediatypes (id, name) VALUES (2,'SVCD');
INSERT IGNORE INTO mediatypes (id, name) VALUES (3,'VCD');
INSERT IGNORE INTO mediatypes (id, name) VALUES (4,'CD-R');
INSERT IGNORE INTO mediatypes (id, name) VALUES (5,'CD-RW');
INSERT IGNORE INTO mediatypes (id, name) VALUES (6,'VHS');
INSERT IGNORE INTO mediatypes (id, name) VALUES (7,'DVD-R');
INSERT IGNORE INTO mediatypes (id, name) VALUES (8,'DVD-RW');
INSERT IGNORE INTO mediatypes (id, name) VALUES (9,'DVD+R');
INSERT IGNORE INTO mediatypes (id, name) VALUES (10,'DVD+RW');
INSERT IGNORE INTO mediatypes (id, name) VALUES (11,'DVD-DL');
INSERT IGNORE INTO mediatypes (id, name) VALUES (12,'DVD+DL');
INSERT IGNORE INTO mediatypes (id, name) VALUES (13,'LaserDisc');
INSERT IGNORE INTO mediatypes (id, name) VALUES (50,'wanted');

# add genres
INSERT IGNORE INTO genres (id, name) VALUES (20,'Adult');

# modify videodata
ALTER TABLE videodata ADD mediatype INT(10) UNSIGNED NOT NULL;
ALTER TABLE videodata ADD custom1 VARCHAR(255) NULL;
ALTER TABLE videodata ADD custom2 VARCHAR(255) NULL;
ALTER TABLE videodata ADD custom3 VARCHAR(255) NULL;
ALTER TABLE videodata ADD custom4 VARCHAR(255) NULL;

# configuration
CREATE TABLE IF NOT EXISTS config (
  opt VARCHAR(50) NOT NULL,
  value VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (opt)
);

# some DEFAULTs
INSERT IGNORE INTO config (opt, value) VALUES ('language', 'en');
INSERT IGNORE INTO config (opt, value) VALUES ('mediaDEFAULT', '4');
INSERT IGNORE INTO config (opt, value) VALUES ('langDEFAULT', 'english');
INSERT IGNORE INTO config (opt, value) VALUES ('filterDEFAULT', 'unseen');
INSERT IGNORE INTO config (opt, value) VALUES ('IMDBage', '432000');
INSERT IGNORE INTO config (opt, value) VALUES ('thumbnail', '1');
INSERT IGNORE INTO config (opt, value) VALUES ('castcolumns', '1');
INSERT IGNORE INTO config (opt, value) VALUES ('template', 'modern::compact');
INSERT IGNORE INTO config (opt, value) VALUES ('languageflags', 'german::spanish::english::french');

# actor headshots
CREATE TABLE IF NOT EXISTS actors (
  name VARCHAR(255) NOT NULL,
  imgurl VARCHAR(255) NOT NULL DEFAULT '',
  checked TIMESTAMP NOT NULL,
  PRIMARY KEY  (name)
);

# some indexes
ALTER TABLE videodata ENGINE=MyISAM;
ALTER TABLE videodata ADD INDEX title_idx (title);
ALTER TABLE videodata ADD INDEX diskid_idx (diskid);
ALTER TABLE videodata ADD FULLTEXT actors_idx (actors);

# creation date
ALTER TABLE videodata ADD created DATETIME;
UPDATE videodata SET created = lastupdate WHERE created IS NULL;

# multiusersupport
CREATE TABLE IF NOT EXISTS users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL DEFAULT '',
  passwd VARCHAR(100) NOT NULL DEFAULT '',
  cookiecode VARCHAR(100) DEFAULT NULL,
  permissions INT(10) UNSIGNED DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  timestamp TIMESTAMP NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY `name` (`name`)
);
INSERT IGNORE INTO users (name, passwd, permissions) VALUES ('admin', '21232f297a57a5a743894a0e4a801fc3', 15);
ALTER TABLE videodata ADD owner VARCHAR(255) DEFAULT NULL;

# 
# changes in DB version 4
# 

ALTER TABLE `actors` ADD `actorid` VARCHAR( 15 ) NOT NULL AFTER `name` ; 

# 
# changes in DB version 5
# 

ALTER TABLE `users` ADD `email` VARCHAR( 255 ) ;

# 
# changes in DB version 6
# 

CREATE TABLE IF NOT EXISTS userconfig (
  user VARCHAR(255) NOT NULL DEFAULT '',
  opt VARCHAR(50) NOT NULL DEFAULT '',
  value VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (user,opt)
);

# 
# changes in DB version 7
# 

INSERT IGNORE INTO genres (id, name) VALUES (21,'Music');

# 
# changes in DB version 8
# 

CREATE TABLE userseen (
  user VARCHAR(255) NOT NULL,
  id INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (user,id)
);

# 
# changes in DB version 9
# 

ALTER TABLE `videodata` CHANGE `imdbID` `imdbID` VARCHAR( 15 ) DEFAULT NULL;

# 
# changes in DB version 10
# 

ALTER TABLE `users` ADD `name` VARCHAR( 255 ) NOT NULL AFTER `user`;
UPDATE `users` SET `name` = `user`;
ALTER TABLE `users` DROP PRIMARY KEY;
ALTER TABLE `users` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `users` ADD UNIQUE (`name`);
ALTER TABLE `users` DROP `user`;

ALTER TABLE `userseen` CHANGE `id` `video_id` INT UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `userseen` ADD `user_id` INT NOT NULL ;
ALTER TABLE `userconfig` ADD `user_id` INT NOT NULL FIRST;
ALTER TABLE `videodata` ADD `owner_id` INT NOT NULL ;

# 
# changes in DB version 11
# 
# Note- before this step is applied, the user data conversion must be performed!
# 

ALTER TABLE `userseen` DROP PRIMARY KEY, ADD PRIMARY KEY ( `video_id`, `user_id` );
ALTER TABLE `userseen` DROP `user`;
ALTER TABLE `userconfig` DROP PRIMARY KEY, ADD PRIMARY KEY ( `user_id`, `opt` );
ALTER TABLE `userconfig` DROP `user`;
ALTER TABLE `videodata` DROP `owner`;

# 
# changes in DB version 12
# 

ALTER TABLE `videogenre` CHANGE `id` `video_id` INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL ;
ALTER TABLE `videogenre` CHANGE `gid` `genre_id` INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL ;

# 
# changes in DB version 13
# 

ALTER TABLE `videodata` MODIFY COLUMN `imgurl` VARCHAR(255) DEFAULT NULL;

# 
# changes in DB version 14
#

CREATE TABLE IF NOT EXISTS permissions (
  `from_uid` INT(11) NOT NULL,
  `to_uid` INT(11) NOT NULL,
  `permissions` INT(10) UNSIGNED DEFAULT NULL,
  `timestamp` TIMESTAMP NOT NULL,
  PRIMARY KEY  (`from_uid`, `to_uid`)
);

# convert old permissions -> new permissions
CREATE TABLE temp_perm (
  `from_uid` INT(11) NOT NULL,
  `to_uid` INT(11) NOT NULL,
  `permissions` INT(10) UNSIGNED DEFAULT NULL,
  `timestamp` TIMESTAMP NOT NULL,
  PRIMARY KEY  (`from_uid`, `to_uid`)
);

INSERT IGNORE INTO temp_perm (from_uid,to_uid,permissions) 
  SELECT a.id AS from_uid,
         b.id AS to_uid,
         CASE 
           WHEN a.permissions & 2 = 2 THEN 6 | COALESCE(c.permissions,0)
           WHEN a.permissions & 4 = 4 THEN 
             CASE 
               WHEN a.id = b.id THEN 6 | COALESCE(c.permissions,0)
               ELSE 2 | COALESCE(c.permissions,0)
             END
           ELSE 0 | COALESCE(c.permissions,0)
         END AS permissions
    FROM (users a, users b)
    LEFT OUTER JOIN permissions c
      ON  c.from_uid = a.id
      AND c.to_uid = b.id
    ORDER BY from_uid DESC;

DELETE FROM temp_perm WHERE permissions = 0;

UPDATE users  SET permissions = 
    CASE WHEN (permissions & 2) = 2 
       THEN (permissions & 11)  | 4
       ELSE (permissions & 9)   | 2
    END;

DELETE FROM permissions;

INSERT INTO permissions SELECT * FROM temp_perm;

DROP TABLE temp_perm;

INSERT IGNORE INTO config (opt, value) VALUES ('guestid', '10000');
INSERT IGNORE INTO users (id, name, passwd, permissions) VALUES (10000 ,'Guest', '---', 2);

# 
# changes in DB version 15
#

INSERT IGNORE INTO genres (id, name) VALUES (22,'Biography');
INSERT IGNORE INTO genres (id, name) VALUES (23,'History');
INSERT IGNORE INTO genres (id, name) VALUES (24,'Sport');
INSERT IGNORE INTO mediatypes (id, name) VALUES (11,'DVD-DL');
INSERT IGNORE INTO mediatypes (id, name) VALUES (12,'DVD+DL');

# 
# changes in DB version 16
#

ALTER TABLE `videodata` MODIFY COLUMN `imdbID` VARCHAR(30);

# 
# changes in DB version 17
#

INSERT IGNORE INTO mediatypes (id, name) VALUES (13,'LaserDisc');

# 
# changes in DB version 18
#

ALTER TABLE `actors` ADD KEY `actorid` (`actorid`);

# 
# changes in DB version 19
#

INSERT IGNORE INTO config (opt, value) VALUES ('adminid', '1');
UPDATE videodata SET owner_id = 1 WHERE owner_id = 0;

# 
# changes in DB version 20
#

SELECT 1;

# 
# changes in DB version 21
#

ALTER TABLE `videodata` DROP COLUMN `seen`;

# 
# changes in DB version 22
#

ALTER TABLE `videodata` MODIFY COLUMN `owner_id` INT NOT NULL DEFAULT '1';

#
# changes in DB version 23
#

ALTER TABLE `videodata` ADD COLUMN `rating` VARCHAR(15) DEFAULT NULL AFTER `plot`;

UPDATE lent SET dt=NOW() WHERE dt IS NULL;
ALTER TABLE `lent` CHANGE dt dt TIMESTAMP NOT NULL;

#
# changes in DB version 24
#

ALTER TABLE `videodata` ADD FULLTEXT INDEX `plot_idx`(`plot`);

#
# changes in DB version 25
#

INSERT IGNORE INTO mediatypes (id, name) VALUES (14,'HDD');

#
# changes in DB version 26
#

UPDATE config SET value='elegant::modern' WHERE opt='template' AND value LIKE 'modern%';

#
# changes in DB version 27
#

INSERT IGNORE INTO mediatypes (id, name) VALUES (15,'HD DVD');
INSERT IGNORE INTO mediatypes (id, name) VALUES (16,'Blu-ray');

#
# changes in DB version 28
#

ALTER TABLE `videodata` MODIFY COLUMN `year` INT(4) UNSIGNED NOT NULL DEFAULT '0';

#
# changes in DB version 29
#

CREATE TABLE cache (
  `tag` VARCHAR(32) NOT NULL,
  `value` VARCHAR(32) DEFAULT NULL,
  PRIMARY KEY (`tag`)
);

#
# changes in DB version 30
#

SELECT 1;

#
# changes in DB version 31
#

ALTER TABLE `cache` MODIFY COLUMN `tag` VARCHAR(45) NOT NULL;

#
# changes in DB version 32
#

UPDATE mediatypes SET name='HD-DVD' WHERE name='HD DVD';

#
# changes in DB version 33
#

DELETE FROM permissions WHERE NOT EXISTS (SELECT 1 FROM users WHERE id=from_uid);

#
# changes in DB version 34
#

ALTER TABLE `videodata` MODIFY COLUMN `filesize` BIGINT UNSIGNED DEFAULT NULL;

#
# changes in DB version 35
#

INSERT INTO mediatypes (id, name) VALUES (17,'CD');

#
# changes in DB version 36
#

UPDATE mediatypes SET id=18 WHERE id=17 AND name='CD';
INSERT IGNORE INTO mediatypes (id, name) VALUES (17,'AVCHD');

#
# changes in DB version 40
#

REPLACE INTO config (opt,value) VALUES ('template', 'nexgen::nexgen');
REPLACE INTO config (opt,value) VALUES ('actorpics', '1');
REPLACE INTO config (opt,value) VALUES ('imdbBrowser', '1');

*
* changes in DB 41
*
ALTER TABLE videodata MODIFY actors MEDIUMTEXT;

# 
# IMPORTANT
# 
# Always increase this number in install/install.sql, install/upgrade.sql and
# core/constants.php when changing the database structure!
# 

REPLACE INTO config (opt,value) VALUES ('dbversion', 41);
