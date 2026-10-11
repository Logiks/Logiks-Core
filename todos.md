# Logiks-PHP Core - Implementation TODO List

## Executive Summary

**Overall Assessment: 6.5/10**

This document tracks the recommended improvements for the Logiks-PHP framework based on comprehensive code analysis. Items are prioritized by severity and impact.

---

## 🔴 Priority 1: Security (CRITICAL)

### 1.1 Replace Manual SQL Escaping with Prepared Statements
- [ ] Audit all database queries in the codebase
- [ ] Replace `cleanForDB()` function usage with prepared statements
- [ ] Update `Database` class to enforce prepared statements
- [ ] Create migration guide for existing code
- [ ] Add deprecation warnings to `cleanForDB()` function
- [ ] Remove manual escaping functions after migration

**Files to Update:**
- `api/commons.php` - Remove `cleanForDB()`, `clean()` functions
- `api/libs/logiksDB/*.php` - Enforce prepared statements
- All files using direct SQL queries

**Example Fix:**
```php
// REMOVE
$query = "SELECT * FROM users WHERE id = " . cleanForDB($userId);

// REPLACE WITH
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
```

**Estimated Effort:** 3-4 weeks  
**Severity:** CRITICAL 🔴

---

### 1.2 Fix Session Cookie Security Bug
- [ ] Fix HttpOnly flag logic in `api/initialize.php`
- [ ] Add SameSite attribute to session cookies
- [ ] Ensure Secure flag is always set on HTTPS
- [ ] Test session security in development and production
- [ ] Document session configuration best practices

**File:** `api/initialize.php` (around line 40-50)

**Current Code:**
```php
setcookie(
    'PHPSESSID',
    $sidvalue,
    0,
    $currentCookieParams['path'],
    $currentCookieParams['domain'],
    isHTTPS(), // secure
    isHTTPS()?false:true // httponly - WRONG!
);
```

**Fixed Code:**
```php
setcookie('PHPSESSID', $sidvalue, [
    'expires' => 0,
    'path' => $currentCookieParams['path'],
    'domain' => $currentCookieParams['domain'],
    'secure' => true,      // Always use HTTPS in production
    'httponly' => true,    // Prevent XSS attacks
    'samesite' => 'Strict' // CSRF protection
]);
```

**Estimated Effort:** 2 hours  
**Severity:** CRITICAL 🔴

---

### 1.3 Implement Input Validation Layer
- [ ] Create `LogiksValidator` class with comprehensive validation rules
- [ ] Add whitelist-based validation (not blacklist)
- [ ] Validate all `$_GET`, `$_POST`, `$_REQUEST` inputs at entry points
- [ ] Implement CSRF token validation
- [ ] Add rate limiting for API endpoints
- [ ] Document validation patterns for developers

**New Files to Create:**
- `api/libs/logiksvalidation/InputValidator.php`
- `api/libs/logiksvalidation/ValidationRules.php`
- `api/libs/logiksvalidation/CSRFProtection.php`

**Estimated Effort:** 2 weeks  
**Severity:** HIGH 🔴

---

### 1.4 Add Security Headers
- [ ] Implement security headers middleware
- [ ] Add Content-Security-Policy
- [ ] Add X-Frame-Options: DENY
- [ ] Add X-Content-Type-Options: nosniff
- [ ] Add Strict-Transport-Security (HSTS)
- [ ] Add Referrer-Policy
- [ ] Test headers in all environments

**File:** `api/initialize.php` or new `api/security-headers.php`

**Code to Add:**
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline';");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
```

**Estimated Effort:** 1 week  
**Severity:** HIGH 🔴

---

### 1.5 Security Audit and Penetration Testing
- [ ] Conduct internal security audit
- [ ] Perform SQL injection testing
- [ ] Test for XSS vulnerabilities
- [ ] Test for CSRF vulnerabilities
- [ ] Test authentication and authorization
- [ ] Test file upload security
- [ ] Create vulnerability disclosure policy
- [ ] Document security testing results

**Estimated Effort:** 2 weeks  
**Severity:** CRITICAL 🔴

---

## 🔴 Priority 2: Code Modernization (HIGH)

### 2.1 Implement PSR-4 Autoloading
- [ ] Update `composer.json` with PSR-4 autoload configuration
- [ ] Organize classes into proper namespaces
- [ ] Remove manual `include_once` statements
- [ ] Update `api/libs/logiksclassloader.inc` to use Composer autoloader
- [ ] Test autoloading in all environments
- [ ] Update documentation for namespace usage

**Update `composer.json`:**
```json
{
    "autoload": {
        "psr-4": {
            "Logiks\\": "api/libs/",
            "Logiks\\Helpers\\": "api/helpers/",
            "Logiks\\DB\\": "api/libs/logiksDB/"
        },
        "files": [
            "api/commons.php",
            "api/helpers/shortfuncs.php"
        ]
    }
}
```

**Estimated Effort:** 4 weeks  
**Severity:** HIGH 🔴

---

### 2.2 Add Type Declarations Throughout
- [ ] Add scalar type declarations to all functions
- [ ] Add return type declarations
- [ ] Add property type declarations (PHP 7.4+)
- [ ] Enable strict_types in all files
- [ ] Run static analysis (PHPStan) to verify types
- [ ] Document type system usage

**Example Updates:**
```php
// Before
function getUserById($id) {
    // ...
}

// After
declare(strict_types=1);

function getUserById(int $id): ?array {
    // ...
}
```

**Files:** All PHP files (~382 files)

**Estimated Effort:** 6 weeks  
**Severity:** HIGH 🔴

---

### 2.3 Migrate Configuration to .env
- [ ] Install `vlucas/phpdotenv` package
- [ ] Create `.env.example` file
- [ ] Migrate all `.cfg` files to `.env` format
- [ ] Update `api/configurator.php` to use .env
- [ ] Add `.env` to `.gitignore`
- [ ] Update documentation for configuration
- [ ] Create migration script for existing deployments

**New Files:**
- `.env.example`
- `api/libs/logiksconfig/EnvLoader.php`

**Estimated Effort:** 2 weeks  
**Severity:** MEDIUM 🟡

---

### 2.4 Remove Error Suppression (@)
- [ ] Find all instances of `@` operator
- [ ] Replace with proper try-catch blocks
- [ ] Implement proper error handling for file operations
- [ ] Implement proper error handling for network operations
- [ ] Test error scenarios thoroughly
- [ ] Document error handling patterns

**Files with @ operator:**
- `api/initialize.php` - `@session_start()`
- `api/commons.php` - `@trim($str)`
- Multiple helper files

**Estimated Effort:** 2 weeks  
**Severity:** MEDIUM 🟡

---

### 2.5 Implement Modern PHP 8.x Features
- [ ] Use null coalescing operator (`??`) instead of `isset()` checks
- [ ] Use nullsafe operator (`?->`) for optional chaining
- [ ] Replace switch statements with match expressions
- [ ] Use constructor property promotion
- [ ] Use named arguments where appropriate
- [ ] Consider using attributes for routing/validation
- [ ] Document PHP 8.x feature usage

**Estimated Effort:** 3 weeks  
**Severity:** MEDIUM 🟡

---

## 🔴 Priority 3: Testing (HIGH)

### 3.1 Set Up PHPUnit Properly
- [ ] Update `.phpunit.xml` configuration
- [ ] Create test bootstrap file
- [ ] Configure code coverage reporting
- [ ] Set up test database
- [ ] Configure test environment variables
- [ ] Document testing setup and procedures

**Update `.phpunit.xml`:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/integration</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">api</directory>
        </include>
        <exclude>
            <directory>api/vendors</directory>
            <directory>api/compat</directory>
        </exclude>
    </coverage>
</phpunit>
```

**Estimated Effort:** 1 week  
**Severity:** HIGH 🔴

---

### 3.2 Write Comprehensive Tests
- [ ] **Unit Tests** (Target: 80% coverage)
  - [ ] Test all helper functions (api/helpers/*)
  - [ ] Test database abstraction layer
  - [ ] Test validation classes
  - [ ] Test security functions
  - [ ] Test configuration loading
  - [ ] Test caching mechanisms
  - [ ] Test template rendering
  - [ ] Test user authentication

- [ ] **Integration Tests**
  - [ ] Test database connections
  - [ ] Test API endpoints
  - [ ] Test page rendering
  - [ ] Test service layer
  - [ ] Test plugin system
  - [ ] Test multi-site functionality

- [ ] **Security Tests**
  - [ ] Test SQL injection prevention
  - [ ] Test XSS prevention
  - [ ] Test CSRF protection
  - [ ] Test authentication flows
  - [ ] Test authorization checks
  - [ ] Test file upload security

- [ ] **Performance Tests**
  - [ ] Test database query performance
  - [ ] Test caching effectiveness
  - [ ] Test page load times
  - [ ] Test API response times

**Current Coverage:** ~4% (16 test files / 382 PHP files)  
**Target Coverage:** 80%

**Estimated Effort:** 8 weeks  
**Severity:** HIGH 🔴

---

### 3.3 Add CI/CD Pipeline
- [ ] Set up GitHub Actions workflow
- [ ] Configure automated testing on push/PR
- [ ] Add code quality checks (PHPStan, Psalm)
- [ ] Add security scanning (Snyk, SonarQube)
- [ ] Configure automated deployment
- [ ] Add status badges to README
- [ ] Document CI/CD pipeline

**Create `.github/workflows/tests.yml`:**
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: [8.0, 8.1, 8.2, 8.3, 8.4]
    steps:
      - uses: actions/checkout@v3
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
      - run: composer install
      - run: vendor/bin/phpunit
      - run: vendor/bin/phpstan analyse
```

**Estimated Effort:** 1 week  
**Severity:** HIGH 🔴

---

## 🟡 Priority 4: Dependency Management (MEDIUM)

### 4.1 Properly Configure Composer
- [ ] Add all dependencies to `composer.json`
- [ ] Specify minimum PHP version
- [ ] Add development dependencies
- [ ] Configure vendor directory correctly
- [ ] Create composer scripts for common tasks
- [ ] Test fresh composer install

**Update `composer.json`:**
```json
{
    "name": "logiks/logiks-core",
    "type": "project",
    "description": "Logiks Framework Core",
    "license": "MIT",
    "require": {
        "php": "^8.0",
        "ext-mysqli": "*",
        "ext-json": "*",
        "ext-mbstring": "*",
        "ext-curl": "*",
        "smarty/smarty": "^4.3",
        "monolog/monolog": "^3.5",
        "vlucas/phpdotenv": "^5.6"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.5",
        "phpstan/phpstan": "^1.10",
        "squizlabs/php_codesniffer": "^3.8"
    },
    "autoload": {
        "psr-4": {
            "Logiks\\": "api/libs/"
        }
    },
    "config": {
        "vendor-dir": "plugins/vendors/"
    },
    "scripts": {
        "test": "phpunit",
        "analyse": "phpstan analyse",
        "cs-check": "phpcs"
    }
}
```

**Estimated Effort:** 1 week  
**Severity:** MEDIUM 🟡

---

### 4.2 Remove Vendor Code from Repository
- [ ] Add vendor directories to `.gitignore`
- [ ] Remove committed vendor code
- [ ] Document dependency installation
- [ ] Update deployment documentation
- [ ] Test clean installation process

**Update `.gitignore`:**
```
/plugins/vendors/
/api/vendors/
/node_modules/
.env
```

**Estimated Effort:** 1 week  
**Severity:** MEDIUM 🟡

---

### 4.3 Update Dependencies to Latest Versions
- [ ] Update Smarty to version 4.x
- [ ] Update Monolog to version 3.x
- [ ] Remove deprecated PSR implementations
- [ ] Test compatibility with updated dependencies
- [ ] Update documentation for breaking changes
- [ ] Create migration guide for dependency updates

**Estimated Effort:** 2 weeks  
**Severity:** MEDIUM 🟡

---

## 🟡 Priority 5: Documentation (MEDIUM)

### 5.1 API Documentation
- [ ] Install phpDocumentor
- [ ] Add PHPDoc blocks to all classes and methods
- [ ] Generate API documentation
- [ ] Publish documentation to GitHub Pages
- [ ] Add usage examples for all public APIs
- [ ] Document design patterns used

**Estimated Effort:** 3 weeks  
**Severity:** MEDIUM 🟡

---

### 5.2 Architecture Documentation
- [ ] Create Architecture Decision Records (ADRs)
- [ ] Document system architecture with diagrams
- [ ] Create UML diagrams for core flows
- [ ] Document database schema
- [ ] Document API endpoints
- [ ] Create developer onboarding guide

**Estimated Effort:** 2 weeks  
**Severity:** MEDIUM 🟡

---

### 5.3 Security Documentation
- [ ] Create SECURITY.md file
- [ ] Document secure coding standards
- [ ] Create security checklist for contributors
- [ ] Add vulnerability reporting process
- [ ] Document security features
- [ ] Create security audit log

**Estimated Effort:** 1 week  
**Severity:** MEDIUM 🟡

---

## ✅ Quick Wins (Can Do Immediately)

### Immediate Actions (< 1 day each)

- [ ] **Fix Session Cookie Security** (2 hours)
  - File: `api/initialize.php`
  - Change: Fix HttpOnly flag logic

- [ ] **Remove get_magic_quotes_gpc() Checks** (1 hour)
  - File: `api/commons.php`, `api/compat/php8-compat.php`
  - Change: Remove or simplify checks

- [ ] **Add .editorconfig Compliance** (1 hour)
  - Ensure `.editorconfig` is properly configured
  - Run formatting across codebase

- [ ] **Update README with Security Warnings** (1 hour)
  - Add security notice to README.md
  - Link to security documentation

- [ ] **Create SECURITY.md File** (2 hours)
  - Add vulnerability disclosure policy
  - List security contacts

- [ ] **Create .env.example** (2 hours)
  - Document all configuration options
  - Provide sensible defaults

- [ ] **Add Composer Dependencies** (3 hours)
  - List all actual dependencies in composer.json
  - Test fresh install

- [ ] **Setup GitHub Actions for Basic Tests** (4 hours)
  - Create workflow file
  - Configure test runner

**Total Estimated Time:** ~16 hours (2 days)

---

## 📊 Progress Tracking

### Security Progress
- [ ] 1.1 SQL Injection Prevention (0%)
- [ ] 1.2 Session Security Fix (0%)
- [ ] 1.3 Input Validation (0%)
- [ ] 1.4 Security Headers (0%)
- [ ] 1.5 Security Audit (0%)

### Modernization Progress
- [ ] 2.1 PSR-4 Autoloading (0%)
- [ ] 2.2 Type Declarations (0%)
- [ ] 2.3 .env Configuration (0%)
- [ ] 2.4 Remove @ Operator (0%)
- [ ] 2.5 PHP 8.x Features (0%)

### Testing Progress
- [ ] 3.1 PHPUnit Setup (0%)
- [ ] 3.2 Test Coverage (4% → Target 80%)
- [ ] 3.3 CI/CD Pipeline (0%)

### Dependencies Progress
- [ ] 4.1 Composer Configuration (0%)
- [ ] 4.2 Remove Vendor Code (0%)
- [ ] 4.3 Update Dependencies (0%)

### Documentation Progress
- [ ] 5.1 API Documentation (0%)
- [ ] 5.2 Architecture Documentation (0%)
- [ ] 5.3 Security Documentation (0%)

---

## 🎯 Milestones

### Milestone 1: Security Hardening (Month 1-2)
**Goal:** Address all critical security vulnerabilities

- Complete Priority 1 items (1.1-1.5)
- Achieve basic test coverage for security features
- Pass initial security audit

**Success Criteria:**
- [ ] Zero known SQL injection vulnerabilities
- [ ] Session security properly configured
- [ ] Input validation in place
- [ ] Security headers implemented
- [ ] Security audit passed

---

### Milestone 2: Code Modernization (Month 2-3)
**Goal:** Bring codebase up to modern PHP standards

- Complete Priority 2 items (2.1-2.5)
- Achieve 50% test coverage
- Pass static analysis with no errors

**Success Criteria:**
- [ ] PSR-4 autoloading implemented
- [ ] Type declarations on all functions
- [ ] .env configuration active
- [ ] No @ operators in code
- [ ] PHPStan level 5+ passing

---

### Milestone 3: Testing & Quality (Month 4-5)
**Goal:** Achieve comprehensive test coverage and quality standards

- Complete Priority 3 items (3.1-3.3)
- Achieve 80% test coverage
- CI/CD pipeline operational

**Success Criteria:**
- [ ] 80%+ code coverage
- [ ] All critical paths tested
- [ ] CI/CD running on all PRs
- [ ] Code quality metrics green

---

### Milestone 4: Production Ready (Month 6)
**Goal:** Final polish and production deployment preparation

- Complete Priority 4-5 items
- Comprehensive documentation
- Migration guides complete

**Success Criteria:**
- [ ] All dependencies properly managed
- [ ] API documentation published
- [ ] Security documentation complete
- [ ] Migration guide for existing apps
- [ ] Production deployment guide

---

## 📈 Estimated Total Timeline

| Phase | Duration | Effort |
|-------|----------|--------|
| Quick Wins | 2 days | 16 hours |
| Security Fixes | 6 weeks | 240 hours |
| Code Modernization | 8 weeks | 320 hours |
| Testing & QA | 10 weeks | 400 hours |
| Documentation | 6 weeks | 240 hours |
| **Total** | **~6 months** | **~1,216 hours** |

*Note: Timeline assumes 1-2 dedicated developers working full-time*

---

## 🔄 Maintenance Tasks (Ongoing)

- [ ] Weekly security updates review
- [ ] Monthly dependency updates
- [ ] Quarterly security audits
- [ ] Continuous test coverage improvement
- [ ] Regular performance profiling
- [ ] Community feedback incorporation
- [ ] Documentation updates with each release

---

## 📝 Notes

### Code Quality Metrics (Current)
- **Security Rating:** 3/10 ⚠️
- **Test Coverage:** 4% ⚠️
- **Modern Standards:** 3/10 ⚠️
- **Code Quality:** 5/10
- **Architecture:** 8/10 ✅
- **Documentation:** 7/10 ✅
- **Developer Experience:** 9/10 ✅
- **Overall:** 6.5/10

### Code Quality Metrics (Target)
- **Security Rating:** 9/10 ✅
- **Test Coverage:** 80%+ ✅
- **Modern Standards:** 9/10 ✅
- **Code Quality:** 8/10 ✅
- **Architecture:** 8/10 ✅
- **Documentation:** 9/10 ✅
- **Developer Experience:** 9/10 ✅
- **Overall:** 8.5/10 ✅

---

## 🤝 Contributing

When working on these TODO items:

1. Create a branch for each major item
2. Add tests before implementation (TDD)
3. Update documentation with changes
4. Get code review before merging
5. Update this TODO list with progress
6. Mark items complete with date

---

## 📚 Resources

### Learning Resources
- PHP 8.x Features: https://www.php.net/manual/en/migration80.php
- PSR Standards: https://www.php-fig.org/psr/
- OWASP Security: https://owasp.org/www-project-top-ten/
- PHPUnit Documentation: https://phpunit.de/documentation.html
- PHPStan Documentation: https://phpstan.org/user-guide/getting-started

### Tools
- PHPStan: Static analysis tool
- Psalm: Static analysis tool
- PHP CS Fixer: Code style fixer
- phpDocumentor: API documentation generator
- Snyk: Security vulnerability scanner

---

**Last Updated:** 2026-10-09  
**Status:** Planning Phase  
**Next Review:** Weekly during implementation

-----

Dependency management  -  there is a path issue, ie the dependency can be installed in given path with composer, so we are commiting the vendors. Let me know a better mothod exists.