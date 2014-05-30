INSERT INTO `lgks_access` (`id`, `master`, `sites`, `blocked`, `doc`, `doe`) VALUES 
('1', 'All Sites', '*', 'false', CURDATE(), CURDATE());

INSERT INTO `lgks_admin_links` (`id`, `menuid`, `title`, `mode`, `category`, `menugroup`, `class`, `link`, `iconpath`, `tips`, `site`, `privilege`, `blocked`, `onmenu`, `target`, `device`, `to_check`, `weight`, `userid`, `doc`, `doe`) VALUES 
('1', '', 'LGKS Pages', '*', '', '', '', '%', '', 'General Pages', '*', '*', 'false', 'false', '', '*', '', '0', 'root', CURDATE(), CURDATE()),
('11112', '', 'Profile', '*', '', '', '', 'page=profile', '', 'Profile Page', '*', '*', 'false', 'false', '', '*', '', '0', 'root', CURDATE(), CURDATE()),
('11113', '', 'Settings', '*', '', '', '', 'page=settings', '', 'Settings Page', '*', '*', 'false', 'false', '', '*', '', '0', 'root', CURDATE(), CURDATE()),
('11114', '', 'Config Editor', '*', '', '', '', 'page=configeditor&cfg=apps', '', '', '*', 'root,', 'false', 'false', '', '*', '', '0', 'root', CURDATE(), CURDATE()),
('11115', '', 'Code Editor', '*', '', '', '', 'page=codeeditor', '', 'Code Editor', '*', '*', 'false', 'false', '', '*', '', '0', 'root', CURDATE(), CURDATE());

INSERT INTO `lgks_privileges` (`id`, `name`, `site`, `blocked`, `remarks`, `userid`, `doc`, `doe`) VALUES 
('1', 'root', '*', 'false', '', '', '', ''),
('2', 'devroot', '*', 'false', '', '', '', ''),
('3', 'admin', '*', 'false', '', '', '', ''),
('5', 'moderator', '*', 'false', 'General Moderator Level', 'installer', CURDATE(), CURDATE()),
('6', 'editor', '*', 'false', 'General Editor Level', 'installer', CURDATE(), CURDATE()),
('7', 'user', '*', 'false', 'General User Level', '', '', '');

INSERT INTO `lgks_users` (`id`, `userid`, `pwd`, `site`, `privilege`, `access`, `name`, `dob`, `email`, `address`, `region`, `country`, `zipcode`, `mobile`, `blocked`, `expires`, `remarks`, `vcode`, `privacy`, `avatar`, `avatar_type`, `doc`, `doe`) VALUES 
('1', 'root', '7815696ecbf1c96e6894b779456d330e', 'admincp', '1', '1', 'ROOT', '', '', '', '', 'India', '', '', 'false', '', '', '', 'protected', '', 'auto', CURDATE(), CURDATE());
