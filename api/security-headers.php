<?php
/**
 * Security Headers Configuration
 * 
 * Sets HTTP security headers to protect against common web vulnerabilities.
 * Headers can be configured via config/security.cfg
 * 
 * @package    Logiks
 * @subpackage Security
 * @author     Logiks Security Team
 * @version    4.2
 * @since      PHP 7.4
 */

if(!defined('ROOT')) exit('No direct script access allowed');

/**
 * Apply security headers to HTTP response
 * 
 * This function sets various security-related HTTP headers to protect
 * against XSS, clickjacking, MIME sniffing, and other attacks.
 * 
 * @return void
 */
function applySecurityHeaders() {
	// Skip if headers already sent
	if (headers_sent()) {
		return;
	}
	
	// Get security configuration
	$enableCSP = getConfig('SECURITY_HEADER_CSP') !== 'false';
	$enableHSTS = getConfig('SECURITY_HEADER_HSTS') !== 'false' && isHTTPS();
	$cspPolicy = getConfig('SECURITY_CSP_POLICY');
	
	// Default CSP policy if not configured
	if (!$cspPolicy) {
		// Safe default: no inline scripts/styles, only same origin
		$cspPolicy = "default-src 'self'; " .
		             "script-src 'self'; " .
		             "style-src 'self'; " .
		             "img-src 'self' data:; " .
		             "font-src 'self'; " .
		             "connect-src 'self'; " .
		             "frame-ancestors 'none'; " .
		             "base-uri 'self'; " .
		             "form-action 'self'";
	}
	
	// Apply Content Security Policy
	if ($enableCSP) {
		header("Content-Security-Policy: " . $cspPolicy);
	}
	
	// Prevent clickjacking attacks
	header("X-Frame-Options: DENY");
	
	// Prevent MIME type sniffing
	header("X-Content-Type-Options: nosniff");
	
	// Enable XSS filter in browsers (legacy browsers)
	header("X-XSS-Protection: 1; mode=block");
	
	// Referrer Policy - don't leak URLs
	header("Referrer-Policy: strict-origin-when-cross-origin");
	
	// Permissions Policy - disable unnecessary browser features
	header("Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()");
	
	// HTTP Strict Transport Security (HSTS) - only on HTTPS
	if ($enableHSTS) {
		$hstsMaxAge = getConfig('SECURITY_HSTS_MAX_AGE') ?: '31536000'; // 1 year default
		header("Strict-Transport-Security: max-age={$hstsMaxAge}; includeSubDomains");
	}
	
	// Remove or mask server information
	header_remove('X-Powered-By');
	if (getConfig('SECURITY_MASK_SERVER') !== 'false') {
		header('Server: WebServer');
	}
}

// Apply headers immediately when file is included
applySecurityHeaders();
