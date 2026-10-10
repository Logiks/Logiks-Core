<?php
/**
 * Input Validation Framework
 * 
 * Provides comprehensive input validation with chainable rules.
 * 
 * @package    Logiks
 * @subpackage Validation
 * @author     Logiks Security Team
 * @version    4.2
 * @since      PHP 7.4
 */

if(!defined('ROOT')) exit('No direct script access allowed');

/**
 * Input Validator Class
 * 
 * Validates user input against a set of rules with support for
 * custom error messages and multiple validation patterns.
 * 
 * Example usage:
 * ```php
 * $validator = new InputValidator($_POST);
 * $validator->validate([
 *     'email' => 'required|email',
 *     'age' => 'required|integer|min:18',
 *     'username' => 'required|alphanumeric|min:3|max:20'
 * ]);
 * 
 * if ($validator->fails()) {
 *     $errors = $validator->errors();
 * }
 * ```
 */
class InputValidator {
	
	/**
	 * @var array Input data to validate
	 */
	protected $data = [];
	
	/**
	 * @var array Validation errors
	 */
	protected $errors = [];
	
	/**
	 * @var array Custom error messages
	 */
	protected $messages = [];
	
	/**
	 * Constructor
	 * 
	 * @param array $data Input data to validate
	 */
	public function __construct(array $data = []) {
		$this->data = $data;
	}
	
	/**
	 * Validate data against rules
	 * 
	 * @param array $rules Validation rules
	 * @param array $messages Custom error messages (optional)
	 * @return bool True if validation passes
	 */
	public function validate(array $rules, array $messages = []): bool {
		$this->messages = $messages;
		$this->errors = [];
		
		foreach ($rules as $field => $fieldRules) {
			$this->validateField($field, $fieldRules);
		}
		
		return empty($this->errors);
	}
	
	/**
	 * Validate a single field
	 * 
	 * @param string $field Field name
	 * @param string|array $rules Validation rules
	 * @return void
	 */
	protected function validateField(string $field, $rules): void {
		// Convert pipe-separated string to array
		if (is_string($rules)) {
			$rules = explode('|', $rules);
		}
		
		$value = $this->data[$field] ?? null;
		
		foreach ($rules as $rule) {
			// Handle rules with parameters like 'min:18'
			if (is_string($rule) && strpos($rule, ':') !== false) {
				list($ruleName, $parameters) = explode(':', $rule, 2);
				$parameters = explode(',', $parameters);
			} elseif (is_array($rule)) {
				// Handle array format like ['regex' => '/pattern/']
				$ruleName = key($rule);
				$parameters = is_array($rule[$ruleName]) ? $rule[$ruleName] : [$rule[$ruleName]];
			} else {
				$ruleName = $rule;
				$parameters = [];
			}
			
			// Skip remaining rules if field is optional and empty
			if ($ruleName !== 'required' && ($value === null || $value === '')) {
				continue;
			}
			
			// Validate using appropriate method
			$method = 'validate' . ucfirst($ruleName);
			if (method_exists($this, $method)) {
				if (!$this->$method($field, $value, $parameters)) {
					break; // Stop validating this field after first failure
				}
			}
		}
	}
	
	/**
	 * Check if validation failed
	 * 
	 * @return bool True if there are errors
	 */
	public function fails(): bool {
		return !empty($this->errors);
	}
	
	/**
	 * Check if validation passed
	 * 
	 * @return bool True if no errors
	 */
	public function passes(): bool {
		return empty($this->errors);
	}
	
	/**
	 * Get all validation errors
	 * 
	 * @return array Errors array
	 */
	public function errors(): array {
		return $this->errors;
	}
	
	/**
	 * Add an error for a field
	 * 
	 * @param string $field Field name
	 * @param string $message Error message
	 * @return void
	 */
	protected function addError(string $field, string $message): void {
		if (!isset($this->errors[$field])) {
			$this->errors[$field] = [];
		}
		$this->errors[$field][] = $message;
	}
	
	/**
	 * Get custom or default error message
	 * 
	 * @param string $field Field name
	 * @param string $rule Rule name
	 * @param string $default Default message
	 * @return string Error message
	 */
	protected function getMessage(string $field, string $rule, string $default): string {
		$key = "{$field}.{$rule}";
		return $this->messages[$key] ?? $this->messages[$rule] ?? $default;
	}
	
	// ==================== Validation Rules ====================
	
	/**
	 * Validate required field
	 */
	protected function validateRequired(string $field, $value, array $params): bool {
		if ($value === null || $value === '' || (is_array($value) && empty($value))) {
			$this->addError($field, $this->getMessage($field, 'required', 
				ucfirst(str_replace('_', ' ', $field)) . ' is required'));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate email address
	 */
	protected function validateEmail(string $field, $value, array $params): bool {
		if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$this->addError($field, $this->getMessage($field, 'email',
				ucfirst(str_replace('_', ' ', $field)) . ' must be a valid email address'));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate integer
	 */
	protected function validateInteger(string $field, $value, array $params): bool {
		if (!filter_var($value, FILTER_VALIDATE_INT) && $value !== '0' && $value !== 0) {
			$this->addError($field, $this->getMessage($field, 'integer',
				ucfirst(str_replace('_', ' ', $field)) . ' must be an integer'));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate numeric value
	 */
	protected function validateNumeric(string $field, $value, array $params): bool {
		if (!is_numeric($value)) {
			$this->addError($field, $this->getMessage($field, 'numeric',
				ucfirst(str_replace('_', ' ', $field)) . ' must be numeric'));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate minimum value/length
	 */
	protected function validateMin(string $field, $value, array $params): bool {
		$min = $params[0] ?? 0;
		
		if (is_numeric($value)) {
			$valid = $value >= $min;
			$message = ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $min;
		} else {
			$length = is_array($value) ? count($value) : strlen($value);
			$valid = $length >= $min;
			$message = ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $min . ' characters';
		}
		
		if (!$valid) {
			$this->addError($field, $this->getMessage($field, 'min', $message));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate maximum value/length
	 */
	protected function validateMax(string $field, $value, array $params): bool {
		$max = $params[0] ?? 0;
		
		if (is_numeric($value)) {
			$valid = $value <= $max;
			$message = ucfirst(str_replace('_', ' ', $field)) . ' must be at most ' . $max;
		} else {
			$length = is_array($value) ? count($value) : strlen($value);
			$valid = $length <= $max;
			$message = ucfirst(str_replace('_', ' ', $field)) . ' must be at most ' . $max . ' characters';
		}
		
		if (!$valid) {
			$this->addError($field, $this->getMessage($field, 'max', $message));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate alphanumeric
	 */
	protected function validateAlphanumeric(string $field, $value, array $params): bool {
		if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
			$this->addError($field, $this->getMessage($field, 'alphanumeric',
				ucfirst(str_replace('_', ' ', $field)) . ' must contain only letters and numbers'));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate alpha (letters only)
	 */
	protected function validateAlpha(string $field, $value, array $params): bool {
		if (!preg_match('/^[a-zA-Z]+$/', $value)) {
			$this->addError($field, $this->getMessage($field, 'alpha',
				ucfirst(str_replace('_', ' ', $field)) . ' must contain only letters'));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate URL
	 */
	protected function validateUrl(string $field, $value, array $params): bool {
		if (!filter_var($value, FILTER_VALIDATE_URL)) {
			$this->addError($field, $this->getMessage($field, 'url',
				ucfirst(str_replace('_', ' ', $field)) . ' must be a valid URL'));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate regex pattern
	 */
	protected function validateRegex(string $field, $value, array $params): bool {
		$pattern = $params[0] ?? '';
		if (!preg_match($pattern, $value)) {
			$this->addError($field, $this->getMessage($field, 'regex',
				ucfirst(str_replace('_', ' ', $field)) . ' format is invalid'));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate value is in list
	 */
	protected function validateIn(string $field, $value, array $params): bool {
		if (!in_array($value, $params)) {
			$this->addError($field, $this->getMessage($field, 'in',
				ucfirst(str_replace('_', ' ', $field)) . ' must be one of: ' . implode(', ', $params)));
			return false;
		}
		return true;
	}
	
	/**
	 * Validate boolean
	 */
	protected function validateBoolean(string $field, $value, array $params): bool {
		$valid = in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false', 'on', 'off', 'yes', 'no'], true);
		if (!$valid) {
			$this->addError($field, $this->getMessage($field, 'boolean',
				ucfirst(str_replace('_', ' ', $field)) . ' must be true or false'));
			return false;
		}
		return true;
	}
}
