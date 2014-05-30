--
-- MySQL 5.5.29
-- Sun, 30 Mar 2014 16:00:37 +0000
--

CREATE TABLE `lgks_log_activity` (
   `id` int(10) unsigned not null auto_increment,
   `date` date not null,
   `time` time,
   `site` varchar(255) default 'default',
   `user` varchar(255),
   `priority` int(10) unsigned default '4',
   `category` varchar(155),
   `module` varchar(255),
   `source` varchar(255),
   `log_data` text,
   `client` varchar(255),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_log_error` (
   `id` int(10) unsigned not null auto_increment,
   `date` date,
   `time` time,
   `site` varchar(255),
   `user` varchar(255),
   `error_code` varchar(15),
   `error_msg` varchar(255),
   `error_log` text,
   `backtrace` text,
   `source` varchar(255),
   `client` varchar(255),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_log_login` (
   `id` int(10) unsigned not null auto_increment,
   `date` date,
   `user` varchar(255),
   `site` varchar(255),
   `login_time` varchar(200),
   `logout_time` varchar(200),
   `sys_spec` varchar(255),
   `token` varchar(255),
   `mauth_key` varchar(155),
   `status` varchar(255),
   `msg` varchar(255),
   `persistant` varchar(50),
   `client` varchar(50),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_log_pcron` (
   `id` int(10) unsigned not null auto_increment,
   `cronid` int(10) unsigned not null,
   `ran_at` datetime not null,
   `scriptpath` varchar(255) not null,
   `script_params` text,
   `method` varchar(255) not null,
   `task_md5_hash` varchar(40) not null,
   `run_only_once` varchar(10) not null,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_log_requests` (
   `id` int(10) unsigned not null auto_increment,
   `date` date,
   `time` time,
   `script` varchar(255),
   `source` varchar(255),
   `page` varchar(150),
   `referer` varchar(250),
   `requestURI` varchar(500),
   `actualURI` varchar(500),
   `user` varchar(155),
   `site` varchar(155),
   `client` varchar(50),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `lgks_log_services` (
   `id` int(10) unsigned not null auto_increment,
   `date` date,
   `time` time,
   `apikey` varchar(255),
   `token` varchar(255),
   `source` varchar(255),
   `get` varchar(255),
   `post` varchar(255),
   `heads` varchar(255),
   `requestURI` varchar(500),
   `actualURI` varchar(500),
   `referer` varchar(255),
   `site` varchar(155),
   `user` varchar(155),
   `client` varchar(50),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `lgks_log_search` (
   `id` int(10) unsigned not null auto_increment,
   `date` date,
   `time` time,
   `site` varchar(255) default 'default',
   `user` varchar(155),
   `script` varchar(255),
   `source` varchar(255),
   `page` varchar(255),
   `searchtxt` varchar(255) not null,
   `client` varchar(50),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_log_sessions` (
   `id` int(10) unsigned not null auto_increment,
   `sessionid` varchar(100) not null,
   `timestamp` datetime not null,
   `last_updated` datetime not null,
   `user` varchar(155) not null,
   `site` varchar(255) default 'default',
   `session_data` text,
   `global_data` text,
   `client` varchar(255),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`),
   UNIQUE KEY (`sessionid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_log_sql` (
   `id` int(10) unsigned not null auto_increment,
   `sql_type` varchar(55),
   `sql_query` text,
   `tbl` varchar(255),
   `db` varchar(255),
   `msg` varchar(255),
   `date` date,
   `time` time,
   `userid` varchar(255),
   `page` varchar(255),
   `site` varchar(255),
   `client` varchar(55),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_log_system` (
   `id` int(10) unsigned not null auto_increment,
   `date` date not null,
   `time` time,
   `site` varchar(255) default 'default',
   `user` varchar(255),
   `priority` int(10) unsigned default '2',
   `category` varchar(155),
   `module` varchar(255),
   `source` varchar(255),
   `log_data` text,
   `client` varchar(255),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `lgks_log_visitor` (
   `id` int(11) not null auto_increment,
   `date` date,
   `time` time,
   `site` varchar(255),
   `script` varchar(255),
   `source` varchar(255),
   `page` varchar(255),
   `server_info` text,
   `client` varchar(255),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;