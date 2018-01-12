Logiks-Core :: Framework
------------------------

[![GitHub version](https://badge.fury.io/gh/Logiks%2FLogiks-Core.svg?style=for-the-badge)](https://badge.fury.io/gh/Logiks%2FLogiks-Core)
[![GitHub issues](https://img.shields.io/github/issues/Logiks/Logiks-Core.svg?style=for-the-badge)](https://github.com/Logiks/Logiks-Core/issues)
[![Build Status](https://travis-ci.org/Logiks/Logiks-Core.svg?style=for-the-badge)](https://travis-ci.org/Logiks/Logiks-Core)
[![codecov](https://codecov.io/gh/Logiks/Logiks-Core/branch/master/graph/badge.svg?style=for-the-badge)](https://codecov.io/gh/Logiks/Logiks-Core)

[![Software license](https://img.shields.io/github/license/Logiks/Logiks-Core.svg?style=for-the-badge)](https://github.com/Logiks/Logiks-Core/blob/master/license.txt)

Logiks Framework is an open source high-performance web application building platform. It applies RAD principles along with agile concepts for building and deploying web based PHP projects basically AppSites. Logiks is basically built around SRTP principle of project development with prime concern being Developer's ease of creating projects.

Added the continuous testing and integration framework from Travis. Find the state above.

### Server Requirments
+ PHP 5.6+
+ Supported Servers : Apache/Web Server with .htaccess capabilities
+ Supported OS : Windows 2000/XP/Vista/Server, CentOS, Any Linux 
+ Supported DBs: Mysql, SQLite
+ Memcached	For caching (Optional)

### PHP Extensions Required
+ Curl Libs

### PHP.ini Configurations
+ Make sure you turn off notices in your php.ini file: 
+ error_reporting = E_ALL & ~E_NOTICE
+ Insure that you have set session.save_path to a valid directory
+ Ensure that short_open_tag = On

### Apache Mods Required
+ mod_rewrite
+ mod_headers
+ mod_expires	(optional)
+ mod_deflate	(optional)

### Optional PHP Extensions
+ SQLite3 Libs
+ GD Libs
+ Memcached Libs

### More ...
+ Plugins Found At <https://github.com/LogiksPlugins>
+ Apps and AppImages Found At <https://github.com/LogiksApps>
+ Snippets At <https://github.com/Logiks/Logiks-Snippets>
+ Hooks At <https://github.com/Logiks/Logiks-Hooks>
+ Web Installer At <https://github.com/Logiks/Logiks-Installer-Web>
+ Bash Installer At <https://github.com/Logiks/Logiks-Installer-Bash>


### Installation
Complete installation instruction can be found at <https://github.com/Logiks/Logiks-Core/wiki/Installation>

### License
Open Source MIT. Please visit the License Agreement Page Of <http://openlogiks.org/license/logiks>.
This project uses some other open source projects, please find their descriptions at OpenLogiks License Page.

### Notes
These are plain source code of/part of the complete working project.
+ For more details visit <http://openlogiks.org/>
+ For a complete api documentation  visit <http://apidocs.openlogiks.org/>
+ For a complete working downloadable package please visit <http://openlogiks.org/downloads/>
+ Use Logiks Play to learn and explore Logiks, prototype parts of your app without installing anything <http://play.openlogiks.net>

