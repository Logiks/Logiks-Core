# Security Policy

## Supported Versions

The following versions of Logiks-PHP Framework are currently supported with security updates:

| Version | Supported          | PHP Requirements |
| ------- | ------------------ | ---------------- |
| 4.2.x   | :white_check_mark: | PHP 8.0 - 8.4    |
| 4.1.x   | :white_check_mark: | PHP 7.0 - 8.4    |
| < 4.0   | :x:                | Not supported    |

## Reporting a Vulnerability

We take security seriously. If you discover a security vulnerability in Logiks-PHP Framework, please report it responsibly.

### How to Report

**DO NOT** create a public GitHub issue for security vulnerabilities.

Instead, please send an email to: **security@openlogiks.org**

Include in your report:
- Description of the vulnerability
- Steps to reproduce the issue
- Potential impact
- Suggested fix (if any)
- Your name/handle for credit (optional)

### What to Expect

- **Acknowledgment**: We'll acknowledge receipt of your report within 48 hours
- **Assessment**: We'll assess the vulnerability and provide an initial response within 5 business days
- **Fix Timeline**: Critical vulnerabilities will be patched within 7-14 days
- **Disclosure**: We'll coordinate responsible disclosure with you before making the fix public
- **Credit**: We'll credit you in the security advisory (unless you prefer to remain anonymous)

## Security Features

### Built-in Protections

Logiks-PHP Framework 4.2+ includes the following security features:

#### 1. Session Security
- **Secure cookies**: HttpOnly and Secure flags enabled
- **SameSite protection**: CSRF protection via SameSite cookies
- **Session regeneration**: Automatic session ID regeneration

#### 2. CSRF Protection
- Token-based CSRF protection
- Automatic token rotation
- Grace period for concurrent requests
- Easy integration with forms

```php
// Generate CSRF token in form
echo CSRFProtection::generateTokenField();

// Validate on submission
if (!CSRFProtection::validateRequest()) {
    die('CSRF validation failed');
}
```

#### 3. Input Validation
- Comprehensive validation rules
- Type-safe validation
- Custom error messages
- Protection against injection attacks

```php
$validator = new InputValidator($_POST);
$validator->validate([
    'email' => 'required|email',
    'age' => 'required|integer|min:18'
]);
```

#### 4. Security Headers
- Content-Security-Policy (CSP)
- X-Frame-Options
- X-Content-Type-Options
- Strict-Transport-Security (HSTS)
- X-XSS-Protection
- Referrer-Policy
- Permissions-Policy

#### 5. SQL Injection Prevention
- Use prepared statements for all database queries
- Never use string concatenation for SQL queries
- Framework provides PreparedStatementBuilder for safe queries

### Configuration

Configure security features in `config/security.cfg`:

```ini
[CONFIG]
# Security Headers
SECURITY_HEADER_CSP=true
SECURITY_HEADER_HSTS=true
SECURITY_HSTS_MAX_AGE=31536000

# Content Security Policy
SECURITY_CSP_POLICY=default-src 'self'; script-src 'self';

# Server Information
SECURITY_MASK_SERVER=true

# CSRF Protection
CSRF_TOKEN_LIFETIME=3600
```

## Security Best Practices

### For Developers Using Logiks-PHP

#### 1. Always Use Prepared Statements

**NEVER DO THIS:**
```php
// ❌ DANGEROUS - SQL Injection vulnerability
$id = $_GET['id'];
$query = "SELECT * FROM users WHERE id = $id";
$result = $db->executeQuery($query);
```

**DO THIS INSTEAD:**
```php
// ✅ SAFE - Using prepared statements
$id = $_GET['id'];
$stmt = new PreparedStatementBuilder($db);
$result = $stmt->select('users')
    ->where('id = ?', [$id])
    ->execute();
```

#### 2. Validate All User Input

```php
$validator = new InputValidator($_POST);
$valid = $validator->validate([
    'username' => 'required|alphanumeric|min:3|max:20',
    'email' => 'required|email',
    'age' => 'required|integer|min:18|max:120'
]);

if (!$valid) {
    $errors = $validator->errors();
    // Handle validation errors
}
```

#### 3. Protect Forms with CSRF Tokens

```php
// In your form
<form method="POST">
    <?php echo CSRFProtection::generateTokenField(); ?>
    <!-- form fields -->
</form>

// In form handler
if (!CSRFProtection::validateRequest()) {
    die('CSRF token validation failed');
}
```

#### 4. Use Security Headers

Security headers are automatically applied. To customize:

```php
// In config/security.cfg
SECURITY_CSP_POLICY=default-src 'self'; script-src 'self' 'unsafe-inline';
```

#### 5. Never Trust User Data

```php
// Always escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// Use validation before processing
$validator = new InputValidator($data);
$validator->validate($rules);
```

#### 6. Keep Dependencies Updated

```bash
# Update Composer dependencies regularly
composer update

# Check for security advisories
composer audit
```

#### 7. Use HTTPS in Production

```php
// Force HTTPS
define("FORCE_HTTPS", true);
```

#### 8. Secure File Uploads

```php
// Validate file uploads
$validator = new InputValidator($_FILES);
$validator->validate([
    'avatar' => 'required|file|mimes:jpg,png|max:2048'
]);

// Store outside web root
move_uploaded_file($tmp, ROOT . '../uploads/' . $safeFilename);
```

#### 9. Implement Rate Limiting

```php
// For APIs and login forms
// Limit requests per IP
$rateLimit = new RateLimit();
if (!$rateLimit->check($_SERVER['REMOTE_ADDR'], 5, 60)) {
    http_response_code(429);
    die('Too many requests');
}
```

#### 10. Log Security Events

```php
// Log failed login attempts
LogiksLogger::getInstance()->log('security', 'Failed login attempt', [
    'username' => $username,
    'ip' => $_SERVER['REMOTE_ADDR']
]);
```

## Common Vulnerabilities and Protections

### SQL Injection
**Protection**: Use prepared statements exclusively. Never concatenate user input into SQL queries.

### Cross-Site Scripting (XSS)
**Protection**: 
- Security headers with CSP
- HTML escape all output: `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`
- Template engine with auto-escaping

### Cross-Site Request Forgery (CSRF)
**Protection**: 
- CSRFProtection class with token generation and validation
- SameSite cookie attribute
- Validate tokens on all state-changing operations

### Session Hijacking
**Protection**:
- Secure and HttpOnly cookie flags
- Session regeneration on privilege changes
- HTTPS only in production

### Authentication Bypass
**Protection**:
- Strong password hashing (bcrypt/Argon2)
- Multi-factor authentication support
- Account lockout after failed attempts

### File Upload Vulnerabilities
**Protection**:
- Validate file types (MIME and extension)
- Limit file sizes
- Store uploads outside web root
- Generate random filenames

### Directory Traversal
**Protection**:
- Validate and sanitize file paths
- Never trust user input in file operations
- Use whitelist of allowed files

### Command Injection
**Protection**:
- Never use user input in system commands
- Use escapeshellarg() if shell commands are necessary
- Prefer native PHP functions over shell commands

## Security Checklist for Production

- [ ] HTTPS enabled with valid SSL certificate
- [ ] FORCE_HTTPS=true in configuration
- [ ] Security headers configured and tested
- [ ] CSP policy configured (no unsafe-inline/unsafe-eval)
- [ ] All database queries use prepared statements
- [ ] CSRF protection on all forms
- [ ] Input validation on all user inputs
- [ ] Output escaping in all templates
- [ ] Error messages don't leak sensitive information
- [ ] Debug mode disabled (display_errors=Off)
- [ ] File permissions properly set (755 for directories, 644 for files)
- [ ] Database credentials secured
- [ ] Regular backups configured
- [ ] Logging enabled for security events
- [ ] Rate limiting on sensitive endpoints
- [ ] Dependencies up to date
- [ ] Security audit completed

## Security Audit Log

### Version 4.2.0 (2024-10-09)
- ✅ Fixed session cookie HttpOnly flag bug
- ✅ Added comprehensive security headers
- ✅ Implemented CSRF protection framework
- ✅ Created input validation framework
- ✅ Added prepared statement builder
- ✅ Removed error suppression operators
- ✅ Added type declarations to core files
- ✅ Comprehensive security test suite

### Previous Versions
- Version 4.1.0: PHP 8.x compatibility layer
- Version 4.0.0: Core framework release

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [Logiks Framework Documentation](https://kms.openlogiks.org/)

## Contact

- **Security Issues**: security@openlogiks.org
- **General Support**: support@openlogiks.org
- **Website**: https://openlogiks.org/
- **GitHub**: https://github.com/Logiks/Logiks-Core

---

**Last Updated**: 2024-10-09  
**Version**: 4.2.0
