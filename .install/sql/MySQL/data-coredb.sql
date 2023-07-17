--
-- MySQL 8.0+
--

INSERT INTO `lgks_access` (`id`, `guid`, `name`, `sites`, `blocked`, `created_by`, `edited_by`) VALUES
(1, 'global', 'All Sites', '*', 'false', 'auto', 'auto');

INSERT INTO `lgks_privileges` (`id`, `guid`, `site`, `name`, `remarks`, `blocked`, `created_by`, `edited_by`) VALUES
(1, 'global', '*', 'root', 'Root User', 'false', 'auto', 'auto'),
(2, 'global', '*', 'suadmin', 'Super Admin', 'false', 'auto', 'auto'),
(5, 'global', '*', 'admin', 'Admin', 'false', 'auto', 'auto'),
(6, 'global', '*', 'manager', 'Manager', 'false', 'auto', 'auto'),
(7, 'global', '*', 'moderator', 'moderator', 'false', 'auto', 'auto'),
(8, 'global', '*', 'user', 'user', 'false', 'auto', 'auto');

INSERT INTO `lgks_roles` (`id`, `guid`, `site`, `name`, `remarks`, `blocked`, `created_by`, `edited_by`) VALUES
(1,	'global',	'*',	'Default',	NULL,	'false',	'auto',	'auto');

INSERT INTO `lgks_users` (`id`, `guid`, `userid`, `pwd`, `pwd_salt`, `privilegeid`, `accessid`, `groupid`, `roles`, `name`, `dob`, `gender`, `organization_name`, `organization_position`, `organization_email`, `email`, `mobile`, `address`, `region`, `country`, `zipcode`, `geolocation`, `geoip`, `tags`, `avatar_type`, `avatar`, `privacy`, `blocked`, `expires`, `registered_site`, `remarks`, `vcode`, `mauth`, `refid`, `security_policy`, `last_login`, `last_login_ip`, `created_by`, `edited_by`) VALUES
(1,	'global',	'root',	'',	'',	1,	1,	0,	'',	'Root User',	'1985-01-01',	'male',	NULL,	NULL,	NULL,	'admin@demo.com',	'',	'',	'',	'',	'',	'',	'',	'',	'photoid',	'',	'public',	'false',	'2200-01-01',	'',	'',	'',	'',	'',	'open',	NULL,	NULL,	'auto',	'auto');

INSERT INTO `lgks_users_group` (`id`, `guid`, `group_name`, `group_manager`, `group_descs`, `created_by`, `edited_by`) VALUES
(1, 'globals', 'Default', NULL, 'No description available', 'auto', 'auto');

INSERT INTO `lgks_users_guid` (`id`, `guid`, `org_name`, `org_email`, `org_mobile`, `org_address`, `org_region`, `org_country`, `org_zipcode`, `org_logo`, `tags`, `blocked`, `account_expires`, `account_planid`, `remarks`, `created_by`, `edited_by`) VALUES
(1,	'global',	'OpenLogiks',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'false',	NULL,	NULL,	NULL,	'auto',	'auto');