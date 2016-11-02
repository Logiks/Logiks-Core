--
-- MySQL 5.6+
--

CREATE TABLE `access` (
   `id` int(10) unsigned not null auto_increment,
   `name` varchar(255),
   `sites` varchar(500),
   `blocked` enum('true','false') default 'false',
   `creator` varchar(155) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `privileges` (
   `id` int(10) unsigned not null auto_increment,
   `guid` varchar(64) not null default 'globals',
   `site` varchar(150),
   `name` varchar(35),
   `remarks` varchar(255),
   `blocked` enum('true','false') default 'false',
   `creator` varchar(155) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `rolemodel` (
   `id` int(10) unsigned not null auto_increment,
   `guid` varchar(64) not null default 'globals',
   `site` varchar(150),
   `category` varchar(100) not null default 'SYSTEM',
   `module` varchar(100) not null,
   `activity` varchar(255) not null,
   `privilegehash` varchar(80) not null,
   `remarks` varchar(200),
   `allow` enum('true','false') not null default 'false',
   `role_type` varchar(55) not null default 'auto',
   `creator` varchar(155) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `cache_sessions` (
   `id` int(10) unsigned not null auto_increment,
   `guid` varchar(150) not null,
   `userid` varchar(155) not null,
   `site` varchar(150) not null,
   `device` varchar(100) not null,
   `client_ip` varchar(25) not null,
   `session_key` varchar(100) not null,
   `session_data` longblob,
   `global_data` longblob,
   `creator` varchar(150) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `security_apikeys` (
   `id` int(10) unsigned not null auto_increment,
   `apikey` varchar(100) not null,
   `site` varchar(150) not null,
   `userid` varchar(155) not null,
   `guid` varchar(155) not null,
   `privilegeid` int(11) not null default '7',
   `accessid` int(11) not null default '1',
   `expires` datetime,
   `remarks` varchar(200),
   `active` enum('true','false') default 'true',
   `creator` varchar(155) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `security_iplist` (
   `id` int(10) unsigned not null auto_increment,
   `ipaddress` varchar(30) not null,
   `allow_type` enum('blacklist','whitelist') default 'blacklist',
   `site` varchar(150) not null default '*',
   `active` enum('true','false') default 'true',
   `creator` varchar(155) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `settings` (
   `id` int(10) unsigned not null auto_increment,
   `guid` varchar(64) not null default 'globals',
   `site` varchar(150) default '*',
   `userid` varchar(155) not null,
   `name` varchar(155) not null,
   `settings` longblob,
   `scope` varchar(15) not null default 'general',
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `system_queue` (
   `id` int(10) unsigned not null auto_increment,
   `guid` varchar(64) not null default 'globals',
   `site` varchar(150) not null,
   `queue_key` varchar(255) not null,
   `queue_data` longblob,
   `expires` timestamp,
   `creator` varchar(150) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `system_cronjobs` (
   `id` int(11) not null auto_increment,
   `site` varchar(150) not null,
   `title` varchar(100) not null,
   `scriptpath` varchar(255) not null,
   `description` varchar(255),
   `script_params` text not null,
   `method` enum('POST','GET','LOCAL') default 'POST',
   `schedule` int(11) default '0',
   `last_completed` datetime,
   `run_only_once` enum('true','false') default 'false',
   `task_md5_hash` varchar(32) not null,
   `retired` enum('true','false') default 'false',
   `blocked` enum('true','false') default 'false',
   `creator` varchar(155) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `users` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(64) NOT NULL DEFAULT 'globals',
  `userid` varchar(150) NOT NULL,
  `pwd` varchar(128) NOT NULL,
  `pwd_salt` varchar(128) DEFAULT NULL,
  `privilegeid` int(11) NOT NULL DEFAULT '7',
  `accessid` int(11) NOT NULL DEFAULT '1',
  `groupid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT 'male',
  `organization_name` varchar(255) DEFAULT NULL,
  `organization_position` varchar(200) DEFAULT NULL,
  `organization_email` varchar(255) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `country` varchar(150) DEFAULT NULL,
  `zipcode` varchar(15) DEFAULT NULL,
  `geolocation` varchar(15) DEFAULT NULL,
  `geoip` varchar(15) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `avatar_type` varchar(15) NOT NULL DEFAULT 'photoid',
  `avatar` varchar(250) DEFAULT NULL,
  `privacy` enum('private','public','protected') DEFAULT 'protected',
  `blocked` enum('true','false') DEFAULT 'false',
  `expires` date DEFAULT NULL,
  `registerd_site` varchar(150) DEFAULT NULL,
  `remarks` varchar(250) DEFAULT NULL,
  `vcode` varchar(65) DEFAULT NULL,
  `mauth` varchar(65) DEFAULT NULL,
  `refid` varchar(30) DEFAULT NULL,
  `security_policy` varchar(25) NOT NULL DEFAULT 'open',
  `last_login` datetime DEFAULT NULL,
  `creator` varchar(155) NOT NULL,
  `dtoc` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dtoe` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `users_group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `group_manager` varchar(155) DEFAULT NULL,
  `group_descs` varchar(255) DEFAULT NULL,
  `created_by` varchar(155) NOT NULL,
  `dtoc` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `dtoe` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `users_guid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(64) NOT NULL DEFAULT 'global',
  `org_name` varchar(255) DEFAULT NULL,
  `org_email` varchar(255) DEFAULT NULL,
  `org_mobile` varchar(20) DEFAULT NULL,
  `org_address` varchar(255) DEFAULT NULL,
  `org_region` varchar(255) DEFAULT NULL,
  `org_country` varchar(150) DEFAULT NULL,
  `org_zipcode` varchar(15) DEFAULT NULL,
  `org_logo` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `blocked` enum('true','false') DEFAULT 'false',
  `account_expires` date DEFAULT NULL,
  `account_planid` varchar(155) DEFAULT NULL,
  `remarks` varchar(250) DEFAULT NULL,
  `creator` varchar(155) NOT NULL,
  `dtoc` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dtoe` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guid` (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
