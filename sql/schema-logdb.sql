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
);


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
);


CREATE TABLE `lgks_log_login` (
   `id` int(10) unsigned not null auto_increment,
   `date` date,
   `user` varchar(255),
   `site` varchar(255),
   `login_time` varchar(200),
   `logout_time` varchar(200),
   `sys_spec` varchar(255),
   `user_agent` varchar(255),
   `token` varchar(255),
   `status` varchar(255),
   `msg` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
);


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
);


CREATE TABLE `lgks_log_requests` (
   `id` int(10) unsigned not null auto_increment,
   `date` date,
   `time` time,
   `script` varchar(255),
   `source` varchar(255),
   `page` varchar(150),
   `site` varchar(155),
   `user` varchar(155),
   `client` varchar(50),
   `user_agent` varchar(255),
   `device` varchar(50),
   PRIMARY KEY (`id`)
);


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
);


CREATE TABLE `lgks_log_sessions` (
   `sessionid` varchar(50) not null,
   `date` date,
   `userid` varchar(255) not null,
   `ip_address` varchar(25),
   `user_agent` varchar(100),
   `last_activity` datetime,
   `login_time` datetime,
   `session_data` text,
   `perpectual` varchar(5) default 'true',
   `device` varchar(50),
   PRIMARY KEY (`sessionid`)
);


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
   PRIMARY KEY (`id`)
);


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
);


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
);
