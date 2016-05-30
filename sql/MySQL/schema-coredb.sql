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
   `site` varchar(150),
   `category` varchar(100) not null default 'SYSTEM',
   `module` varchar(100) not null,
   `activity` varchar(255) not null,
   `privilegehash` varchar(80) not null,
   `remarks` varchar(200),
   `allow` enum('true','false') default 'true',
   `role_type` varchar(55) not null default 'auto',
   `creator` varchar(155) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `rules` (
   `id` int(10) unsigned not null auto_increment,
   `rule_key` varchar(255) not null,
   `rule_type` varchar(55) not null,
   `rule_schema` longblob,
   `rule_precaller` varchar(255) not null,
   `rule_postcaller` varchar(255) not null,
   `site` varchar(150) not null,
   `blocked` enum('true','false') default 'false',
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


CREATE TABLE `security_bots` (
   `id` int(10) unsigned not null auto_increment,
   `botkey` varchar(30) not null,
   `allow_type` enum('blacklist','whitelist') default 'blacklist',
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


CREATE TABLE `security_userkeys` (
   `id` int(10) unsigned not null auto_increment,
   `site` varchar(150) not null,
   `userid` varchar(155) not null,
   `devicetype` varchar(100) not null,
   `userkey` varchar(100) not null,
   `devicekey` varchar(100) not null,
   `session` longblob,
   `creator` varchar(150) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `security_geoip` (
   `id` int(10) unsigned not null auto_increment,
   `ip_address` varchar(155),
   `country_code` varchar(25),
   `country_name` varchar(500),
   `geolocation` varchar(25),
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `settings` (
   `id` int(10) unsigned not null auto_increment,
   `site` varchar(150) default '*',
   `userid` varchar(155) not null,
   `name` varchar(155) not null,
   `settings` longblob,
   `scope` varchar(15) not null default 'general',
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `system_bucket` (
   `id` int(10) unsigned not null auto_increment,
   `site` varchar(150) not null,
   `bucket_category` varchar(255) not null,
   `bucket_key` varchar(255) not null,
   `bucket_data` longblob,
   `share` text,
   `published` enum('true','false') default 'true',
   `creator` varchar(150) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `system_queue` (
   `id` int(10) unsigned not null auto_increment,
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
   `id` int(11) not null auto_increment,
   `guid` varchar(64) not null default '3cbfc610b158e774809db3a5bdf4124c',
   `userid` varchar(150) not null,
   `pwd` varchar(128) not null,
   `pwd_salt` varchar(128),
   `privilegeid` int(11) not null default '7',
   `accessid` int(11) not null default '1',
   `name` varchar(255) not null,
   `dob` date,
   `gender` enum('male','female','other') default 'male',
   `email` varchar(200),
   `mobile` varchar(20),
   `address` varchar(255),
   `region` varchar(255),
   `country` varchar(150),
   `zipcode` varchar(15),
   `geolocation` varchar(15),
   `geoip` varchar(15),
   `tags` varchar(255),
   `avatar_type` varchar(15) not null default 'photoid',
   `avatar` varchar(250),
   `privacy` enum('private','public','protected') default 'protected',
   `blocked` enum('true','false') default 'false',
   `expires` date,
   `registerd_site` varchar(150),
   `remarks` varchar(250),
   `vcode` varchar(65),
   `mauth` varchar(65),
   `refid` varchar(30),
   `security_policy` varchar(25) not null default 'open',
   `last_login` datetime,
   `creator` varchar(155) not null,
   `dtoc` timestamp not null default CURRENT_TIMESTAMP,
   `dtoe` timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   UNIQUE KEY (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

