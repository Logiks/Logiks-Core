--
-- MySQL 8.0+
--

CREATE TABLE `lgks_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `name` varchar(255) DEFAULT NULL,
  `sites` varchar(500) DEFAULT NULL,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_cache_sessions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
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
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
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
  `weight` int DEFAULT '10',
  `onmenu` enum('true','false') DEFAULT 'true',
  `blocked` enum('true','false') DEFAULT 'false',
  `rules` text,
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_privileges` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `parent` int NOT NULL DEFAULT '0',
  `site` varchar(150) DEFAULT NULL,
  `name` varchar(35) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_rolemodel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `site` varchar(150) DEFAULT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'SYSTEM',
  `module` varchar(100) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `action` varchar(100) DEFAULT NULL,
  `privilegehash` varchar(80) NOT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `allow` enum('true','false') NOT NULL DEFAULT 'false',
  `role_type` varchar(25) NOT NULL DEFAULT 'auto',
  `policystr` varchar(90) NOT NULL,
  `rolehash` varchar(80) NOT NULL,
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rolehash` (`rolehash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `site` varchar(150) DEFAULT '*',
  `name` varchar(35) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_rolescope` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(64) NOT NULL DEFAULT 'global',
  `privilegeid` varchar(80) NOT NULL,
  `scope_title` varchar(90) NOT NULL,
  `scope_id` varchar(90) NOT NULL,
  `scope_type` varchar(20) NOT NULL DEFAULT 'generic',
  `scope_params` text,
  `remarks` varchar(200) DEFAULT NULL,
  `blocked` enum('false','true') NOT NULL DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scope_id` (`scope_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_security_apikeys` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `api_title` varchar(155) NOT NULL,
  `api_keys` varchar(150) NOT NULL,
  `api_secret` varchar(250) NOT NULL,
  `api_roles` text,
  `api_userid` varchar(155) NOT NULL,
  `api_whitelist` text NOT NULL,
  `blocked` enum('false','true') NOT NULL DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_security_iplist` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `ipaddress` varchar(30) NOT NULL,
  `allow_type` enum('blacklist','whitelist') DEFAULT 'blacklist',
  `site` varchar(150) NOT NULL DEFAULT '*',
  `active` enum('true','false') DEFAULT 'true',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `site` varchar(150) DEFAULT '*',
  `userid` varchar(155) NOT NULL,
  `name` varchar(155) NOT NULL,
  `settings` longblob,
  `scope` varchar(15) NOT NULL DEFAULT 'general',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_system_cronjobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `site` varchar(150) NOT NULL,
  `title` varchar(100) NOT NULL,
  `scriptpath` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `script_params` text NOT NULL,
  `method` enum('POST','GET','LOCAL') DEFAULT 'POST',
  `schedule` int DEFAULT '0',
  `start_after` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
  `last_completed` datetime DEFAULT NULL,
  `run_only_once` enum('true','false') DEFAULT 'false',
  `task_md5_hash` varchar(32) NOT NULL,
  `retired` enum('true','false') DEFAULT 'false',
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `userid` varchar(150) NOT NULL,
  `pwd` varchar(128) NOT NULL,
  `pwd_salt` varchar(128) DEFAULT NULL,
  `privilegeid` int NOT NULL DEFAULT '7',
  `accessid` int NOT NULL DEFAULT '1',
  `groupid` int NOT NULL DEFAULT '0',
  `roles` varchar(155) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
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
  `registered_site` varchar(150) DEFAULT NULL,
  `remarks` varchar(250) DEFAULT NULL,
  `vcode` varchar(65) DEFAULT NULL,
  `mauth` varchar(65) DEFAULT NULL,
  `refid` varchar(30) DEFAULT NULL,
  `security_policy` varchar(25) NOT NULL DEFAULT 'open',
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_users_group` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `guid` varchar(100) NOT NULL DEFAULT 'global',
  `group_parent` int NOT NULL DEFAULT '0',
  `group_name` varchar(150) NOT NULL,
  `group_manager` varchar(155) DEFAULT NULL,
  `group_descs` varchar(255) DEFAULT NULL,
  `blocked` enum('false','true') NOT NULL DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


CREATE TABLE `lgks_users_guid` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guid` (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
