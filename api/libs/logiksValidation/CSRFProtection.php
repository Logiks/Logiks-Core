<?php
/**
 * CSRF Protection
 * 
 * Provides Cross-Site Request Forgery protection through token generation
 * and validation.
 * 
 * @package    Logiks
 * @subpackage Security
 * @author     Logiks Security Team
 * @version    4.2
 * @since      PHP 7.4
 */

if(!defined('ROOT')) exit('No direct script access allowed');

/**
 * CSRF Protection Class
 * 
 * Generates and validates CSRF tokens to prevent cross-site request forgery attacks.
 * 
 * Example usage:
 * ```php
 * // In form:
 * echo CSRFProtection::generateTokenField();
 * 
 * // On form submission:
 * if (!CSRFProtection::validateToken($_POST['csrf_token'])) {
 *     die('CSRF token validation failed');
 * }
 * ```
 */
class CSRFProtection {
	
	/**
	 * Token lifetime in seconds (default: 1 hour)
	 */
	const TOKEN_LIFETIME = 3600;
	
	/**
	 * Grace period for old tokens (60 seconds for concurrent requests)
	 */
	const GRACE_PERIOD = 60;
	
	/**
	 * Generate a CSRF token
	 * 
	 * Generates a cryptographically secure token and stores it in the session.
	 * Supports token rotation with grace period for concurrent requests.
	 * 
	 * @return string The generated token
	 */
	public static function generateToken(): string {
		// Ensure session is started
		if (session_status() === PHP_SESSION_NONE) {
			return '';  // Cannot generate token without session
		}
		
		// Get site name for token namespace
		$siteName = defined('SITENAME') ? SITENAME : 'logiks';
		$tokenName = 'csrf_token_' . $siteName;
		$oldTokenName = 'csrf_token_old_' . $siteName;
		$timestampName = 'csrf_timestamp_' . $siteName;
		
		// Move current token to old (for grace period)
		if (isset($_SESSION[$tokenName])) {
			$_SESSION[$oldTokenName] = $_SESSION[$tokenName];
			$_SESSION['csrf_timestamp_old_' . $siteName] = $_SESSION[$timestampName] ?? time();
		}
		
		// Generate new token
		$token = bin2hex(random_bytes(32));
		
		// Store in session
		$_SESSION[$tokenName] = $token;
		$_SESSION[$timestampName] = time();
		
		return $token;
	}
	
	/**
	 * Get current CSRF token
	 * 
	 * Returns existing token or generates a new one if none exists.
	 * 
	 * @return string The CSRF token
	 */
	public static function getToken(): string {
		if (session_status() === PHP_SESSION_NONE) {
			return '';
		}
		
		$siteName = defined('SITENAME') ? SITENAME : 'logiks';
		$tokenName = 'csrf_token_' . $siteName;
		
		// Return existing token or generate new one
		if (isset($_SESSION[$tokenName])) {
			return $_SESSION[$tokenName];
		}
		
		return self::generateToken();
	}
	
	/**
	 * Validate CSRF token
	 * 
	 * Validates the provided token against stored session token.
	 * Supports grace period for old tokens to handle concurrent requests.
	 * 
	 * @param string $token Token to validate
	 * @param bool $autoRotate Auto-rotate token on successful validation
	 * @return bool True if token is valid
	 */
	public static function validateToken(string $token, bool $autoRotate = true): bool {
		if (session_status() === PHP_SESSION_NONE) {
			return false;
		}
		
		if (empty($token)) {
			return false;
		}
		
		$siteName = defined('SITENAME') ? SITENAME : 'logiks';
		$tokenName = 'csrf_token_' . $siteName;
		$oldTokenName = 'csrf_token_old_' . $siteName;
		$timestampName = 'csrf_timestamp_' . $siteName;
		$oldTimestampName = 'csrf_timestamp_old_' . $siteName;
		
		// Check current token
		if (isset($_SESSION[$tokenName]) && hash_equals($_SESSION[$tokenName], $token)) {
			// Check expiration
			$timestamp = $_SESSION[$timestampName] ?? 0;
			if (time() - $timestamp > self::TOKEN_LIFETIME) {
				return false;  // Token expired
			}
			
			// Valid token - rotate if requested
			if ($autoRotate) {
				self::generateToken();
			}
			
			return true;
		}
		
		// Check old token (grace period for concurrent requests)
		if (isset($_SESSION[$oldTokenName]) && hash_equals($_SESSION[$oldTokenName], $token)) {
			$oldTimestamp = $_SESSION[$oldTimestampName] ?? 0;
			
			// Old token is valid for grace period only
			if (time() - $oldTimestamp <= self::GRACE_PERIOD) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Generate hidden form field with CSRF token
	 * 
	 * Convenience method to generate HTML for CSRF token field.
	 * 
	 * @param string $fieldName Field name (default: 'csrf_token')
	 * @return string HTML for hidden input field
	 */
	public static function generateTokenField(string $fieldName = 'csrf_token'): string {
		$token = self::getToken();
		return '<input type="hidden" name="' . htmlspecialchars($fieldName) . '" value="' . htmlspecialchars($token) . '">';
	}
	
	/**
	 * Validate token from request
	 * 
	 * Convenience method to validate token from $_POST or $_GET
	 * 
	 * @param string $fieldName Field name (default: 'csrf_token')
	 * @param bool $autoRotate Auto-rotate token on successful validation
	 * @return bool True if token is valid
	 */
	public static function validateRequest(string $fieldName = 'csrf_token', bool $autoRotate = true): bool {
		$token = $_POST[$fieldName] ?? $_GET[$fieldName] ?? '';
		return self::validateToken($token, $autoRotate);
	}
	
	/**
	 * Clear CSRF tokens from session
	 * 
	 * Useful for logout or session cleanup
	 * 
	 * @return void
	 */
	public static function clearTokens(): void {
		if (session_status() === PHP_SESSION_NONE) {
			return;
		}
		
		$siteName = defined('SITENAME') ? SITENAME : 'logiks';
		
		unset($_SESSION['csrf_token_' . $siteName]);
		unset($_SESSION['csrf_token_old_' . $siteName]);
		unset($_SESSION['csrf_timestamp_' . $siteName]);
		unset($_SESSION['csrf_timestamp_old_' . $siteName]);
	}
}
