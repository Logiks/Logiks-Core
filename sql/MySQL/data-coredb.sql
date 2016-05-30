--
-- MySQL 5.6+
--

INSERT INTO `access` (`id`, `name`, `sites`, `blocked`, `creator`, `dtoc`, `dtoe`) VALUES 
('1', 'All Sites', '*', 'false', 'auto', '2016-01-04 02:52:49', '2016-01-04 02:52:49');

INSERT INTO `privileges` (`id`, `site`, `name`, `blocked`, `remarks`, `creator`, `dtoc`, `dtoe`) VALUES 
('1', '*', 'root', 'false', 'Master Privilege Level', 'auto', '2016-01-04 02:53:13', '2016-01-04 02:53:13');

-- [Table `lgks_rolemodel` is empty]

-- [Table `lgks_rules` is empty]

-- [Table `lgks_security_apikeys` is empty]

-- [Table `lgks_security_bots` is empty]

-- [Table `lgks_security_geoip` is empty]

-- [Table `lgks_security_iplist` is empty]

-- [Table `lgks_security_userkeys` is empty]

-- [Table `lgks_settings` is empty]

-- [Table `lgks_system_bucket` is empty]

-- [Table `lgks_system_cronjobs` is empty]

-- [Table `lgks_system_queue` is empty]

INSERT INTO `users` (`id`, `guid`, `userid`, `pwd`, `privilegeid`, `accessid`, `name`, `dob`, `gender`, `email`, `mobile`, `address`, `region`, `country`, `zipcode`, `geolocation`, `geoip`, `tags`, `avatar_type`, `avatar`, `privacy`, `blocked`, `expires`, `registerd_site`, `remarks`, `vcode`, `mauth`, `refid`, `security_policy`) VALUES 
('1', '3cbfc610b158e774809db3a5bdf4124c', 'root', '', '1', '1', 'Root User', '', 'male', 'admin@test.com', '', '', '', '', '', '', '', '', 'photoid', '', 'public', 'false', '', '', '', '', '', '', 'open');
