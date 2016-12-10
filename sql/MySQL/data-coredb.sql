--
-- MySQL 5.6+
--

INSERT INTO `access` (`id`, `name`, `sites`, `blocked`, `creator`, `created_on`, `edited_on`) VALUES 
('1', 'All Sites', '*', 'false', 'auto', '2016-01-04 02:52:49', '2016-01-04 02:52:49');

INSERT INTO `privileges` (`id`, `guid`, `site`, `name`, `blocked`, `remarks`, `creator`, `created_on`, `edited_on`) VALUES 
('1', 'global', '*', 'root', 'false', 'Master Privilege Level', 'auto', '2016-01-04 02:53:13', '2016-01-04 02:53:13');

INSERT INTO `users` (`id`, `guid`, `userid`, `pwd`, `privilegeid`, `accessid`, `name`, `dob`, `gender`, `email`, `mobile`, `address`, `region`, `country`, `zipcode`, `geolocation`, `geoip`, `tags`, `avatar_type`, `avatar`, `privacy`, `blocked`, `expires`, `registerd_site`, `remarks`, `vcode`, `mauth`, `refid`, `security_policy`) VALUES 
('1', 'globals', 'root', '', '1', '1', 'Root User', '', 'male', 'admin@test.com', '', '', '', '', '', '', '', '', 'photoid', '', 'public', 'false', '', '', '', '', '', '', 'open');

INSERT INTO `users_group` (`id`, `guid`, `group_name`, `group_manager`, `group_descs`, `created_by`, `edited_by`) VALUES
(1, 'globals', 'Default', NULL, 'No description available', 'root' 'root');
