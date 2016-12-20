--
-- MySQL 5.6+
--

INSERT INTO `access` (`id`, `guid`, `name`, `sites`, `blocked`, `created_by`, `edited_by`) VALUES
(1, 'global', 'All Sites', '*', 'false', 'auto', 'auto');

INSERT INTO `privileges` (`id`, `guid`, `site`, `name`, `remarks`, `blocked`, `created_by`, `edited_by`) VALUES
(1, 'global', '*', 'root', 'Root User', 'false', 'auto', 'auto'),
(2, 'global', '*', 'suadmin', 'Super Admin', 'false', 'auto', 'auto'),
(5, 'global', '*', 'admin', 'Admin', 'false', 'auto', 'auto'),
(6, 'global', '*', 'manager', 'Manager', 'false', 'auto', 'auto'),
(7, 'global', '*', 'moderator', 'moderator', 'false', 'auto', 'auto'),
(8, 'global', '*', 'user', 'user', 'false', 'auto', 'auto');

INSERT INTO `users` (`id`, `guid`, `userid`, `pwd`, `pwd_salt`, `privilegeid`, `accessid`, `groupid`, `name`, `dob`, `gender`, `organization_name`, `organization_position`, `organization_email`, `email`, `mobile`, `address`, `region`, `country`, `zipcode`, `geolocation`, `geoip`, `tags`, `avatar_type`, `avatar`, `privacy`, `blocked`, `expires`, `registerd_site`, `remarks`, `vcode`, `mauth`, `refid`, `security_policy`, `last_login`, `created_by`, `edited_by`) VALUES
(1, 'global', 'root', '', '', 1, 1, 0, 'Root User', '0000-00-00', 'male', NULL, NULL, NULL, 'admin@demo.com', '', '', '', '', '', '', '', '', 'photoid', '', 'public', 'false', '0000-00-00', '', '', '', '', '', 'open', NULL, 'auto', 'auto');

INSERT INTO `users_group` (`id`, `guid`, `group_name`, `group_manager`, `group_descs`, `created_by`, `edited_by`) VALUES
(1, 'globals', 'Default', NULL, 'No description available', 'auto', 'auto');