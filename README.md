Logiks-Core :: Framework
------------------------

[![GitHub version](https://badge.fury.io/gh/Logiks%2FLogiks-Core.svg)](https://badge.fury.io/gh/Logiks%2FLogiks-Core)
[![GitHub issues](https://img.shields.io/github/issues/Logiks/Logiks-Core.svg)](https://github.com/Logiks/Logiks-Core/issues)
[![Software license](https://img.shields.io/github/license/Logiks/Logiks-Core.svg?style=for-the-badge)](https://github.com/Logiks/Logiks-Core/blob/master/license.txt)

## Logiks 6.0

> **Security notice**
> - To report a vulnerability, **do not** open a public issue. Email **security@openlogiks.org**. See `SECURITY.md` for the disclosure policy, supported versions and hardening guidance.
> - Before going to production: serve over HTTPS (`FORCE_HTTPS=true`), generate your own unique `HASH_SALT` and `JWT_SECRET` in `config/security.cfg` (e.g. `openssl rand -hex 32`) and never commit them, and review the production checklist in `SECURITY.md`.
> - The login endpoint, services and APIs are rate limited and CSRF-checked by default; see `config/security.cfg` (`RATELIMIT_*`, `LOGIN_RATELIMIT_*`, `CSRF_MODE`).


Logiks Framework is an open source high-performance web application building platform. It applies RAD principles along with agile concepts for building and deploying web based PHP projects basically AppSites. Logiks is basically built around SRTP principle of project development with prime concern being Developer's ease of creating projects.

Logiks has an active developer team and community, bringing great value to the project. In addition to keeping you from wheel-reinventing, using Logiks means your application’s core is well tested and is being constantly improved. 

Instead of reinventing the wheel every time you begin a new project, check out a apps from <https://github.com/LogiksApps> and get started with the logic of your application.

### Quick list of features:

+ Flexible MIT licensing
+ Compatible with PHP 7.4 and greater (PHP 8.1+ recommended; PHP versions that are end-of-life upstream receive no security fixes)
+ API first approach, start by building and integrating great API
+ Integrated database interaction
+ Application scaffolding
+ Code Hooks, get under the skin fast
+ Page-Module-Service architecture
+ Request dispatcher with clean, custom URLs and routes
+ Built-in validation
+ Fast and flexible templating (PHP syntax, with helpers)
+ View helpers for AJAX, JavaScript, HTML forms and more
+ Email, cookie, security, session, and request handling Components
+ Flexible ACL
+ Data sanitization
+ Flexible caching
+ Localization
+ Great 3rd Party Integration
+ Multi Development Language Supported : NodeJS, Python for extendibility
+ Works from any web site directory, with little Apache configuration involved

### Server Requirments
+ PHP 7.4+ (8.1+ recommended)
+ Composer, to install the PHP dependencies (see below)
+ Supported Servers : Apache/Web Server with .htaccess capabilities
+ Supported OS : Windows 2000/XP/Vista/Server, CentOS, Any Linux 
+ Supported DBs: Mysql, MariaDB, SQLite, APIDB (MongoDB, ElasticSearch, many more)
+ Memcached/Redis/File System	For caching (Optional)

### PHP Extensions Required
+ Curl Libs
+ mbstring, openssl, json

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
+ Redis Libs

### More ...
+ Plugins Found At <https://github.com/LogiksPlugins>
+ Apps and AppImages Found At <https://github.com/LogiksApps>
+ Snippets At <https://github.com/Logiks/Logiks-Snippets>
+ Hooks At <https://github.com/Logiks/Logiks-Hooks>
+ Web Installer At <https://github.com/Logiks/Logiks-Installer-Web>
+ Bash Installer At <https://github.com/Logiks/Logiks-Installer-Bash>


### Installing Dependencies
PHP dependencies (Smarty, Monolog, PHPMailer) are managed by Composer and installed into `plugins/vendors/`:

```bash
composer install --no-dev --optimize-autoloader
```

The framework loads `plugins/vendors/autoload.php` automatically. Copies of older versions bundled in `api/vendors/` are only a fallback for installs where Composer has not been run, and are deprecated: they contain known vulnerabilities (Smarty 3.1.27, PHPMailer 5.2.10) and should not be used in production. Run `composer audit` regularly.

### Installation
Complete installation instruction can be found at <https://github.com/Logiks/Logiks-Core/wiki/Installation>

### License
Open Source MIT. Please visit the License Agreement Page Of <http://openlogiks.org/license/logiks>.
This project uses some other open source projects, please find their descriptions at OpenLogiks License Page.

### Notes 
These are plain source code of/part of the complete working project. (We are currently restructuring our websites, will be back soon)
+ For more details visit <https://openlogiks.org/>
+ For a complete api documentation, please visit <https://kms.openlogiks.org/>
+ For a Docker based installation, please visit <https://github.com/Logiks/Logiks-Docker>
+ For CLI based installation, please visit <https://github.com/Logiks/Logiks-Installer-Cli>
+ Use Logiks Play to learn and explore Logiks, prototype parts of your app without installing anything <https://play.openlogiks.org>

