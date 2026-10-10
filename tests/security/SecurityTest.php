<?php
/**
 * Security Features Test
 * 
 * Tests security-critical functions and features.
 * 
 * @package    Logiks
 * @subpackage Tests
 * @author     Logiks Security Team
 * @version    4.2
 */

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase {
	
	protected function setUp(): void {
		parent::setUp();
		
		// Ensure session is available for tests
		if (session_status() === PHP_SESSION_NONE) {
			@session_start();
		}
	}
	
	/**
	 * Test session cookie configuration
	 */
	public function testSessionCookieParameters() {
		if (session_status() !== PHP_SESSION_ACTIVE) {
			$this->markTestSkipped('Session not started');
		}
		
		$params = session_get_cookie_params();
		
		// HttpOnly should be set
		$this->assertTrue($params['httponly'], 'Session cookie should have httponly flag set');
		
		// If HTTPS, secure flag should be set
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
			$this->assertTrue($params['secure'], 'Session cookie should have secure flag on HTTPS');
		}
		
		// SameSite should be configured (PHP 7.3+)
		if (PHP_VERSION_ID >= 70300) {
			$this->assertNotEmpty($params['samesite'] ?? '', 'Session cookie should have SameSite attribute');
		}
	}
	
	/**
	 * Test security headers function exists
	 */
	public function testSecurityHeadersFunctionExists() {
		$this->assertTrue(function_exists('applySecurityHeaders'), 
			'applySecurityHeaders function should exist');
	}
	
	/**
	 * Test CSRFProtection class exists and works
	 */
	public function testCSRFProtectionExists() {
		require_once ROOT . 'api/libs/logiksValidation/CSRFProtection.php';
		
		$this->assertTrue(class_exists('CSRFProtection'), 'CSRFProtection class should exist');
	}
	
	/**
	 * Test CSRF token generation
	 */
	public function testCSRFTokenGeneration() {
		require_once ROOT . 'api/libs/logiksValidation/CSRFProtection.php';
		
		$token1 = CSRFProtection::generateToken();
		$this->assertNotEmpty($token1, 'CSRF token should not be empty');
		$this->assertIsString($token1, 'CSRF token should be a string');
		$this->assertEquals(64, strlen($token1), 'CSRF token should be 64 characters (32 bytes hex)');
		
		// Second call should generate different token
		$token2 = CSRFProtection::generateToken();
		$this->assertNotEquals($token1, $token2, 'Tokens should rotate');
	}
	
	/**
	 * Test CSRF token validation
	 */
	public function testCSRFTokenValidation() {
		require_once ROOT . 'api/libs/logiksValidation/CSRFProtection.php';
		
		// Generate token
		$token = CSRFProtection::generateToken();
		
		// Valid token should validate
		$this->assertTrue(CSRFProtection::validateToken($token, false), 
			'Valid CSRF token should pass validation');
		
		// Invalid token should fail
		$this->assertFalse(CSRFProtection::validateToken('invalid_token'), 
			'Invalid CSRF token should fail validation');
		
		// Empty token should fail
		$this->assertFalse(CSRFProtection::validateToken(''), 
			'Empty CSRF token should fail validation');
	}
	
	/**
	 * Test CSRF token field generation
	 */
	public function testCSRFTokenFieldGeneration() {
		require_once ROOT . 'api/libs/logiksValidation/CSRFProtection.php';
		
		$field = CSRFProtection::generateTokenField();
		
		$this->assertStringContainsString('<input', $field, 'Should generate input field');
		$this->assertStringContainsString('type="hidden"', $field, 'Should be hidden field');
		$this->assertStringContainsString('name="csrf_token"', $field, 'Should have csrf_token name');
		$this->assertStringContainsString('value="', $field, 'Should have value attribute');
	}
	
	/**
	 * Test InputValidator class exists
	 */
	public function testInputValidatorExists() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		$this->assertTrue(class_exists('InputValidator'), 'InputValidator class should exist');
	}
	
	/**
	 * Test input validation - required field
	 */
	public function testInputValidationRequired() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		$validator = new InputValidator(['name' => 'John']);
		$result = $validator->validate(['name' => 'required']);
		
		$this->assertTrue($result, 'Validation should pass for provided required field');
		$this->assertTrue($validator->passes(), 'Validator passes() should return true');
		$this->assertFalse($validator->fails(), 'Validator fails() should return false');
		
		// Test missing required field
		$validator2 = new InputValidator([]);
		$result2 = $validator2->validate(['name' => 'required']);
		
		$this->assertFalse($result2, 'Validation should fail for missing required field');
		$this->assertTrue($validator2->fails(), 'Validator fails() should return true');
		$this->assertNotEmpty($validator2->errors(), 'Should have errors');
	}
	
	/**
	 * Test input validation - email
	 */
	public function testInputValidationEmail() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		// Valid email
		$validator = new InputValidator(['email' => 'test@example.com']);
		$this->assertTrue($validator->validate(['email' => 'required|email']), 
			'Valid email should pass validation');
		
		// Invalid email
		$validator2 = new InputValidator(['email' => 'not-an-email']);
		$this->assertFalse($validator2->validate(['email' => 'required|email']), 
			'Invalid email should fail validation');
	}
	
	/**
	 * Test input validation - numeric and min/max
	 */
	public function testInputValidationNumericMinMax() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		// Valid age
		$validator = new InputValidator(['age' => '25']);
		$this->assertTrue($validator->validate(['age' => 'required|integer|min:18|max:100']), 
			'Valid age should pass validation');
		
		// Too young
		$validator2 = new InputValidator(['age' => '15']);
		$this->assertFalse($validator2->validate(['age' => 'required|integer|min:18']), 
			'Age below minimum should fail');
		
		// Too old
		$validator3 = new InputValidator(['age' => '150']);
		$this->assertFalse($validator3->validate(['age' => 'required|integer|max:100']), 
			'Age above maximum should fail');
	}
	
	/**
	 * Test input validation - string length
	 */
	public function testInputValidationStringLength() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		// Valid username
		$validator = new InputValidator(['username' => 'john_doe']);
		$this->assertTrue($validator->validate(['username' => 'required|min:3|max:20']), 
			'Valid username length should pass');
		
		// Too short
		$validator2 = new InputValidator(['username' => 'ab']);
		$this->assertFalse($validator2->validate(['username' => 'required|min:3']), 
			'Too short username should fail');
		
		// Too long
		$validator3 = new InputValidator(['username' => str_repeat('a', 25)]);
		$this->assertFalse($validator3->validate(['username' => 'required|max:20']), 
			'Too long username should fail');
	}
	
	/**
	 * Test input validation - alphanumeric
	 */
	public function testInputValidationAlphanumeric() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		// Valid alphanumeric
		$validator = new InputValidator(['code' => 'ABC123']);
		$this->assertTrue($validator->validate(['code' => 'alphanumeric']), 
			'Valid alphanumeric should pass');
		
		// Invalid with special chars
		$validator2 = new InputValidator(['code' => 'ABC-123']);
		$this->assertFalse($validator2->validate(['code' => 'alphanumeric']), 
			'Alphanumeric with special chars should fail');
	}
	
	/**
	 * Test input validation - URL
	 */
	public function testInputValidationURL() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		// Valid URL
		$validator = new InputValidator(['website' => 'https://example.com']);
		$this->assertTrue($validator->validate(['website' => 'url']), 
			'Valid URL should pass');
		
		// Invalid URL
		$validator2 = new InputValidator(['website' => 'not a url']);
		$this->assertFalse($validator2->validate(['website' => 'url']), 
			'Invalid URL should fail');
	}
	
	/**
	 * Test input validation - in list
	 */
	public function testInputValidationInList() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		// Valid value in list
		$validator = new InputValidator(['status' => 'active']);
		$this->assertTrue($validator->validate(['status' => 'in:active,inactive,pending']), 
			'Value in list should pass');
		
		// Invalid value not in list
		$validator2 = new InputValidator(['status' => 'deleted']);
		$this->assertFalse($validator2->validate(['status' => 'in:active,inactive,pending']), 
			'Value not in list should fail');
	}
	
	/**
	 * Test input validation - regex
	 */
	public function testInputValidationRegex() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		// Valid regex match
		$validator = new InputValidator(['zip' => '12345']);
		$result = $validator->validate(['zip' => [['regex' => '/^\d{5}$/']]]);
		$this->assertTrue($result, 'Value matching regex should pass');
		
		// Invalid regex match
		$validator2 = new InputValidator(['zip' => '1234']);
		$result2 = $validator2->validate(['zip' => [['regex' => '/^\d{5}$/']]]);
		$this->assertFalse($result2, 'Value not matching regex should fail');
	}
	
	/**
	 * Test error message retrieval
	 */
	public function testValidationErrorMessages() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		$validator = new InputValidator(['email' => 'invalid']);
		$validator->validate(['email' => 'required|email']);
		
		$errors = $validator->errors();
		$this->assertIsArray($errors, 'Errors should be an array');
		$this->assertArrayHasKey('email', $errors, 'Should have email field errors');
		$this->assertIsArray($errors['email'], 'Field errors should be an array');
		$this->assertNotEmpty($errors['email'], 'Should have error messages');
	}
	
	/**
	 * Test custom error messages
	 */
	public function testValidationCustomErrorMessages() {
		require_once ROOT . 'api/libs/logiksValidation/InputValidator.php';
		
		$customMessage = 'Custom email error message';
		$validator = new InputValidator(['email' => 'invalid']);
		$validator->validate(
			['email' => 'required|email'],
			['email.email' => $customMessage]
		);
		
		$errors = $validator->errors();
		$this->assertStringContainsString($customMessage, $errors['email'][0] ?? '', 
			'Should use custom error message');
	}
}
