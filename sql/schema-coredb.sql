--
-- MySQL 5.5.29
-- Sun, 30 Mar 2014 16:02:14 +0000
--

CREATE TABLE `lgks_access` (
   `id` int(10) unsigned not null auto_increment,
   `master` varchar(255),
   `sites` varchar(500),
   `blocked` enum('true','false') default 'false',
   `doc` date,
   `doe` date,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_admin_links` (
   `id` int(10) unsigned not null auto_increment,
   `menuid` varchar(25),
   `title` varchar(150),
   `mode` varchar(150) default '*',
   `category` varchar(255),
   `menugroup` varchar(150),
   `class` varchar(150),
   `link` varchar(255) default '#',
   `iconpath` varchar(255),
   `tips` varchar(255),
   `site` varchar(150) default '*',
   `privilege` varchar(500) default '*',
   `blocked` enum('true','false') default 'false',
   `onmenu` enum('true','false') default 'true',
   `target` varchar(55),
   `device` varchar(20) default '*',
   `to_check` varchar(200),
   `weight` int(11) default '0',
   `userid` varchar(155) default 'root',
   `doc` date,
   `doe` date,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_config_sites` (
   `id` int(10) unsigned not null auto_increment,
   `site` varchar(150) default '*',
   `scope` varchar(50),
   `name` varchar(255) not null,
   `value` text,
   `type` varchar(50) default 'string',
   `class` varchar(55),
   `edit_params` varchar(255),
   `remarks` varchar(255),
   `doc` date,
   `doe` date,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_config_users` (
   `id` int(10) unsigned not null auto_increment,
   `site` varchar(150),
   `scope` varchar(50),
   `userid` varchar(150) default 'all',
   `name` varchar(255) not null,
   `value` text,
   `type` varchar(50) default 'string',
   `class` varchar(55),
   `edit_params` varchar(255),
   `remarks` varchar(255),
   `doc` date,
   `doe` date,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_cron_jobs` (
   `id` int(11) not null auto_increment,
   `title` varchar(100) not null,
   `scriptpath` varchar(255) not null,
   `description` varchar(255),
   `script_params` text not null,
   `method` enum('POST','GET','LOCAL') default 'POST',
   `schdulle` int(11) default '0',
   `last_completed` datetime,
   `run_only_once` enum('true','false') default 'false',
   `task_md5_hash` varchar(32) not null,
   `tsoc` datetime not null,
   `site` varchar(150) default '*',
   `createdBy` varchar(150),
   `retired` enum('true','false') default 'false',
   `blocked` enum('true','false') default 'false',
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_privileges` (
   `id` int(10) unsigned not null auto_increment,
   `name` varchar(35),
   `site` varchar(150),
   `blocked` enum('true','false') default 'false',
   `remarks` varchar(255),
   `userid` varchar(150),
   `doc` date,
   `doe` date,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_sys_redirects` (
   `id` int(11) unsigned not null auto_increment,
   `link_from` varchar(255) not null,
   `link_to` varchar(255) not null,
   `redirect_type` varchar(55) not null,
   `redirect_msg` varchar(355) not null,
   `blocked` enum('false','true') not null,
   `dtoc` datetime not null,
   `dtoe` datetime not null,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_rolemodel` (
   `id` int(10) unsigned not null auto_increment,
   `site` varchar(150),
   `category` varchar(100),
   `module` varchar(100),
   `activity` varchar(255) not null,
   `privilegeid` varchar(25) not null,
   `access` enum('true','false') default 'true',
   `role_type` varchar(255) not null default 'auto',
   `userid` varchar(150),
   `doc` date,
   `doe` date,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_server_msgs` (
   `id` int(10) unsigned not null auto_increment,
   `for_site` varchar(150),
   `for_user` varchar(150),
   `by_site` varchar(150),
   `by_user` varchar(150),
   `dated` date not null,
   `till_date` date,
   `msgtxt` varchar(255) not null,
   `viewable` enum('true','false') default 'true',
   `obsolate` enum('true','false') default 'false',
   `tsoc` datetime,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_sys_iplist` (
   `id` int(10) unsigned not null auto_increment,
   `ipaddress` varchar(30) not null,
   `allow_type` enum('blacklist','whitelist') default 'blacklist',
   `site` varchar(150),
   `active` enum('true','false') default 'true',
   `userid` varchar(150),
   `doc` date,
   `doe` date,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_users` (
   `id` int(11) not null auto_increment,
   `guid` varchar(150) not null default '3cbfc610b158e774809db3a5bdf4124c',
   `userid` varchar(150) not null,
   `pwd` varchar(100),
   `site` varchar(150) not null,
   `privilege` int(11) not null default '7',
   `access` int(11) not null default '1',
   `name` varchar(255) not null,
   `dob` date,
   `gender` enum('male','female') default 'male',
   `email` varchar(200),
   `mobile` varchar(20),
   `address` varchar(255),
   `region` varchar(255),
   `country` varchar(150),
   `zipcode` varchar(15),
   `geolocation` varchar(15),
   `geoip` varchar(15),
   `blocked` enum('true','false') default 'false',
   `expires` date,
   `remarks` varchar(250),
   `vcode` varchar(65),
   `mauth` varchar(65),
   `refid` varchar(30),
   `privacy` enum('private','public','protected') default 'protected',
   `avatar` varchar(200),
   `avatar_type` varchar(15) not null default 'photoid',
   `last_login` datetime,
   `doc` date,
   `doe` date,
   PRIMARY KEY (`id`),
   UNIQUE KEY (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;