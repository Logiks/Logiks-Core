Logiks-Core :: Framework
------------------------

[![GitHub version](https://badge.fury.io/gh/Logiks%2FLogiks-Core.svg)](https://badge.fury.io/gh/Logiks%2FLogiks-Core)
[![Tests](https://github.com/Logiks/Logiks-Core/actions/workflows/tests.yml/badge.svg)](https://github.com/Logiks/Logiks-Core/actions/workflows/tests.yml)
[![CodeQL](https://github.com/Logiks/Logiks-Core/actions/workflows/codeql.yml/badge.svg)](https://github.com/Logiks/Logiks-Core/actions/workflows/codeql.yml)
[![GitHub issues](https://img.shields.io/github/issues/Logiks/Logiks-Core.svg)](https://github.com/Logiks/Logiks-Core/issues)
[![Software license](https://img.shields.io/github/license/Logiks/Logiks-Core.svg?style=for-the-badge)](https://github.com/Logiks/Logiks-Core/blob/master/license.txt)

## Logiks 6.0

Logiks Framework is an open source, high-performance web application platform. It applies RAD principles along with agile concepts for building and deploying web based PHP projects, basically AppSites. Logiks is built around the SRTP principle of project development, with the prime concern being the developer's ease of creating projects.

Logiks has an active developer team and community, bringing great value to the project. In addition to keeping you from reinventing the wheel, using Logiks means your application's core is well tested and constantly improved.

Instead of starting from scratch every time you begin a new project, check out apps from <https://github.com/LogiksApps> and get started with the logic of your application.

> **Security notice**
> - To report a vulnerability, **do not** open a public issue. Email **security@openlogiks.org**. See `SECURITY.md` for the disclosure policy, supported versions and hardening guidance. Version 6.0.x receives security fixes until July 2029.
> - Before going to production: serve over HTTPS (`FORCE_HTTPS=true`), generate your own unique `HASH_SALT` and `JWT_SECRET` (or supply them as the `LOGIKS_HASH_SALT` / `LOGIKS_JWT_SECRET` environment variables, see `.env.example`) and never commit them, and review the production checklist in `SECURITY.md`.

### What's new in 6.0

+ **Modern PHP**: runs on current PHP releases, verified on PHP 8.5 (tests) and 8.4 (database checks), CI on 8.1 to 8.4, minimum 7.4
+ **Current databases**: MySQL 8.4 LTS, MySQL 9 and MariaDB 11 through `mysqli` (verified, see [Requirements](#requirements)), plus SQLite 3
+ **Composer-managed dependencies**: Smarty 4, Monolog 2 and PHPMailer 6 install into `plugins/vendors/`; `composer audit` runs in CI
+ **Secure by default**: strict security headers, CSRF checks, login and API rate limiting, a parameterized query builder, upload validation, hardened session cookies
+ **Tested and scanned**: PHPUnit security suite and GitHub CodeQL on every push
+ **Secrets outside the repo**: environment-variable overrides for `HASH_SALT` and `JWT_SECRET`

### Quick list of features

+ Flexible MIT licensing
+ **API first** approach: every capability is a service command, so you can build and integrate great APIs first and add UIs later
+ **Token based auth**: sessions plus JWT support for APIs and mobile or single-page clients
+ **Polyglot services**: service engines for PHP, Python, Perl, Ruby and JavaScript (Node.js), plus a Node.js bridge agent run under PM2
+ Integrated database interaction with a driver model (MySQL/MariaDB, SQLite) and a parameterized query builder
+ Application scaffolding
+ Code Hooks, get under the skin fast
+ Page-Module-Service architecture
+ Request dispatcher with clean, custom URLs and routes
+ Built-in validation (rules, CSRF, file uploads with real content-type checking)
+ Fast and flexible templating (Smarty 4 and PHP syntax, with helpers)
+ View helpers for AJAX, JavaScript, HTML forms and more
+ Email (SMTP via PHPMailer 6), cookie, security, session, and request handling components
+ Structured logging through Monolog, with channels and rotating files
+ Flexible ACL with a default-deny role model
+ Data sanitization
+ Flexible caching: file, Memcached or Redis
+ File storage drivers: local disk or Amazon S3
+ Localization
+ Great 3rd party integration
+ Works from any web site directory, with little web server configuration involved

### Requirements

| Component | Supported | Notes |
| --------- | --------- | ----- |
| PHP | 7.4 minimum, **8.1 to 8.4 recommended**; 8.5 tested | PHP versions that are end-of-life upstream receive no security fixes, so production should run a supported release. The CI matrix covers 8.1 to 8.4. |
| MySQL | **8.4 LTS, 9.x** (8.0 uses the same driver but was not part of the check) | Via the `mysqli` driver, including the default `caching_sha2_password` authentication and `utf8mb4` |
| MariaDB | **11.x** | Via `mysqli` |
| SQLite | 3 | `sqlite3` extension |
| Web server | Apache with `mod_rewrite` | Ships with `.htaccess`. Nginx or Caddy work with equivalent rewrite rules (no config is shipped) |
| OS | Linux, macOS, Windows Server, containers | |
| Cache (optional) | Memcached, Redis, file system | |
| Storage (optional) | Local disk, Amazon S3 | |

How the database claims were checked: connections and parameterized queries (insert, select, update, delete, `IN` lists, `NULL`, floats, emoji and multi-byte text, injection payloads) were run through the `mysqli` driver path on PHP 8.4 against MySQL 8.4.11, MySQL 9.7.4 and MariaDB 11.8.9. The framework's own legacy query builder is covered by its existing tests only.

The older `mysql` (ext/mysql) driver folder is kept only for historical reasons; that extension no longer exists in PHP 7+, so use `mysqli`.

### PHP Extensions Required
+ `mbstring`, `openssl`, `json`
+ `curl`
+ `fileinfo` (content-type checks for uploads)
+ `mysqli` (for MySQL or MariaDB)

### Optional PHP Extensions
+ `sqlite3`
+ `gd`
+ `memcached`
+ `redis`

### PHP.ini Configurations
+ For production set `display_errors = Off` and `log_errors = On`, and log to a file outside the web root
+ `error_reporting = E_ALL & ~E_DEPRECATED` is a good starting point; the framework hides notices from users by default
+ Ensure `session.save_path` is set to a valid, writable directory
+ Keep `expose_php = Off`
+ `short_open_tag` is not needed by the core; enable it only if your own app templates use `<? ?>` tags

### Apache Mods Required
+ mod_rewrite
+ mod_headers
+ mod_expires (optional)
+ mod_deflate (optional)

### Getting started

Install the PHP dependencies with Composer. They go into `plugins/vendors/`, which the framework loads automatically:

```bash
composer install --no-dev --optimize-autoloader
```

Then configure the application in `config/` (database, `config/security.cfg`) and point your web server's document root at the project folder. Complete installation instructions can be found at <https://github.com/Logiks/Logiks-Core/wiki/Installation>.

Copies of older libraries bundled in `api/vendors/` are only a fallback for installs where Composer has not been run. They contain known vulnerabilities (Smarty 3.1.27, PHPMailer 5.2.10) and should not be used in production. Run `composer audit` regularly.

### Security

Logiks 6.0 ships with these protections, all configurable in `config/security.cfg`:

+ Content-Security-Policy, HSTS, `X-Frame-Options`, `Referrer-Policy` and related headers by default
+ Hardened session cookies (HttpOnly, Secure on HTTPS, SameSite)
+ CSRF protection for service calls (`CSRF_MODE`: `off`, `log` or `enforce`)
+ Rate limiting for login (per user and per IP), services and APIs
+ `PreparedStatementBuilder` for parameterized queries on mysqli and SQLite
+ `InputValidator` with file-upload rules that check the real content type, not just the file name
+ Default-deny role model, bad-bot and IP blacklist filtering

The full list, configuration reference, known limitations and the production checklist are in `SECURITY.md`.

### Testing and quality

```bash
composer install            # includes the dev dependencies
composer test               # runs PHPUnit with .phpunit.xml
composer audit              # checks dependencies for known vulnerabilities
```

GitHub Actions runs the security test suite on PHP 8.1 to 8.4, `composer audit`, and CodeQL on every push and pull request.

### Upgrading from earlier versions

+ Run `composer install`; Smarty, Monolog and PHPMailer now come from `plugins/vendors/`
+ **Smarty 4 does not execute PHP inside templates** (no `{php}` tags); `TEMPLATE_ALLOW_PHP` has no effect with it
+ PHPMailer 6 is namespaced; the old global `PHPMailer` class name still works through a compatibility alias
+ The removed `mcrypt` extension is no longer used for the encrypted-login path (OpenSSL replaces it)
+ Replace the example `HASH_SALT` and `JWT_SECRET` that earlier versions shipped; rotating `JWT_SECRET` signs everyone out
+ `FORCE_HTTPS` and a 12 character minimum password length are now the defaults; set `FORCE_HTTPS=false` for plain-HTTP local development
+ `X-Forwarded-For` is only trusted from proxies listed in `RATELIMIT_TRUSTED_PROXIES`; set it if you run behind a load balancer

### More ...
+ Plugins Found At <https://github.com/LogiksPlugins>
+ Apps and AppImages Found At <https://github.com/LogiksApps>
+ Snippets At <https://github.com/Logiks/Logiks-Snippets>
+ Hooks At <https://github.com/Logiks/Logiks-Hooks>
+ Web Installer At <https://github.com/Logiks/Logiks-Installer-Web>
+ Bash Installer At <https://github.com/Logiks/Logiks-Installer-Bash>
+ Docker based installation At <https://github.com/Logiks/Logiks-Docker>
+ CLI based installation At <https://github.com/Logiks/Logiks-Installer-Cli>

### Installation
Complete installation instruction can be found at <https://github.com/Logiks/Logiks-Core/wiki/Installation>

### License
Open Source MIT. Please visit the License Agreement Page Of <http://openlogiks.org/license/logiks>.
This project uses some other open source projects, please find their descriptions at OpenLogiks License Page.

### Notes
These are plain source code of/part of the complete working project. (We are currently restructuring our websites, will be back soon)
+ For more details visit <https://openlogiks.org/>
+ For a complete api documentation, please visit <https://kms.openlogiks.org/>
+ Use Logiks Play to learn and explore Logiks, prototype parts of your app without installing anything <https://play.openlogiks.org>
