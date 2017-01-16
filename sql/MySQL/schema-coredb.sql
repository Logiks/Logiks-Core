--
-- MySQL 5.6+
--

CREATE TABLE `access` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `name` varchar(255) DEFAULT NULL,
  `sites` varchar(500) DEFAULT NULL,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `privileges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `site` varchar(150) DEFAULT NULL,
  `name` varchar(35) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `rolemodel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `site` varchar(150) DEFAULT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'SYSTEM',
  `module` varchar(100) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `privilegehash` varchar(80) NOT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `allow` enum('true','false') NOT NULL DEFAULT 'false',
  `role_type` varchar(55) NOT NULL DEFAULT 'auto',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `cache_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `userid` varchar(155) NOT NULL,
  `site` varchar(150) NOT NULL,
  `device` varchar(100) NOT NULL,
  `client_ip` varchar(25) NOT NULL,
  `session_key` varchar(100) NOT NULL,
  `auth_key` varchar(100) NOT NULL,
  `session_data` longblob,
  `global_data` longblob,
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `security_apikeys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `api_title` varchar(155) NOT NULL,
  `api_keys` varchar(150) NOT NULL,
  `api_secret` varchar(250) NOT NULL,
  `api_roles` text,
  `api_userid` varchar(155) NOT NULL,
  `api_whitelist` text NOT NULL,
  `blocked` enum('false','true') NOT NULL DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `security_iplist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `ipaddress` varchar(30) NOT NULL,
  `allow_type` enum('blacklist','whitelist') DEFAULT 'blacklist',
  `site` varchar(150) NOT NULL DEFAULT '*',
  `active` enum('true','false') DEFAULT 'true',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `site` varchar(150) DEFAULT '*',
  `userid` varchar(155) NOT NULL,
  `name` varchar(155) NOT NULL,
  `settings` longblob,
  `scope` varchar(15) NOT NULL DEFAULT 'general',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



CREATE TABLE `system_cronjobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `site` varchar(150) NOT NULL,
  `title` varchar(100) NOT NULL,
  `scriptpath` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `script_params` text NOT NULL,
  `method` enum('POST','GET','LOCAL') DEFAULT 'POST',
  `schedule` int(11) DEFAULT '0',
  `last_completed` datetime DEFAULT NULL,
  `run_only_once` enum('true','false') DEFAULT 'false',
  `task_md5_hash` varchar(32) NOT NULL,
  `retired` enum('true','false') DEFAULT 'false',
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
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
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `users_group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `group_name` varchar(150) NOT NULL,
  `group_manager` varchar(155) DEFAULT NULL,
  `group_descs` varchar(255) DEFAULT NULL,
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `users_guid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
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
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guid` (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `menuid` varchar(25) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `mode` varchar(150) DEFAULT '*',
  `category` varchar(255) DEFAULT NULL,
  `menugroup` varchar(150) DEFAULT NULL,
  `class` varchar(150) DEFAULT NULL,
  `target` varchar(55) DEFAULT NULL,
  `link` varchar(255) DEFAULT '#',
  `iconpath` varchar(255) DEFAULT NULL,
  `tips` varchar(255) DEFAULT NULL,
  `site` varchar(150) DEFAULT '*',
  `device` varchar(20) DEFAULT '*',
  `privilege` varchar(1000) DEFAULT '*',
  `weight` int(11) DEFAULT '10',
  `onmenu` enum('true','false') DEFAULT 'true',
  `blocked` enum('true','false') DEFAULT 'false',
  `rules` text,
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
