<?php

namespace AvelPress\Support;

use AvelPress\Utils\Str;

defined( 'ABSPATH' ) || exit;

class Validator {

	/**
	 * Data
	 * 
	 * @var array
	 */
	protected $data;

	protected $errors = [];

	protected $validatedData = [];

	public function __construct( $data, $rules ) {
		$this->data = $data;
		$this->rules = $rules;
	}

	public static function make( $data, $rules ) {
		return new static( $data, $rules );
	}


	/**
	 * Validate the request with the given rules.
	 * 
	 * @return void
	 */
	public function validate() {
		$this->prepareForValidation();

		$rules = $this->rules();
		$this->validatedData = [];

		foreach ( $rules as $field => $rule ) {
			$fieldRules = explode( '|', $rule );
			$fieldValid = true;

			$isNullable = in_array( 'nullable', $fieldRules );
			$fieldValue = Str::getNestedValue( $this->data, $field );

			if ( $isNullable && ( $fieldValue === null || $fieldValue === '' ) ) {
				continue;
			}

			foreach ( $fieldRules as $singleRule ) {
				$ruleParts = explode( ':', $singleRule );
				$method = $ruleParts[0];
				$parameters = isset( $ruleParts[1] ) ? explode( ',', $ruleParts[1] ) : [];

				if ( $method === 'nullable' ) {
					continue;
				}

				if ( ! method_exists( $this, $method ) ) {
					continue;
				}

				$errorCountBefore = count( $this->errors );
				call_user_func_array( [ $this, $method ], [ $field, ...$parameters ] );
				$errorCountAfter = count( $this->errors );

				if ( $errorCountAfter > $errorCountBefore ) {
					$fieldValid = false;
				}
			}

			if ( $fieldValid && $this->hasNestedKey( $this->data, $field ) ) {
				Str::setNestedValue( $this->validatedData, $field, $fieldValue );
			}
		}
	}


	protected function prepareForValidation() {
		// Prepare the data for validation
	}


	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, array<mixed>|string>
	 */
	public function rules() {
		return [];
	}

	public function all() {
		return $this->data;
	}

	public function errors() {
		return $this->errors;
	}

	public function hasErrors() {
		return ! empty( $this->errors );
	}

	public function fails() {
		return $this->hasErrors();
	}

	/**
	 * Get the validated data that passed all validation rules.
	 * 
	 * @param string|null $key Optional specific field to get (supports dot notation)
	 * @return mixed Returns all validated data if $key is null, specific field value if $key provided
	 */
	public function validated( $key = null ) {
		if ( $key === null ) {
			return $this->validatedData;
		}

		return Str::getNestedValue( $this->validatedData, $key );
	}

	protected function required( $field ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value === null || $value === '' ) {
			$this->errors[ $field ] = "The field {$field} is required.";
		}
	}

	protected function email( $field ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value !== null && ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
			$this->errors[ $field ] = "The field {$field} must be a valid email address.";
		}
	}

	protected function array( $field ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( ! is_array( $value ) ) {
			$this->errors[ $field ] = "The field {$field} must be an array.";
		}
	}

	protected function min( $field, $length ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value !== null && strlen( (string) $value ) < $length ) {
			$this->errors[ $field ] = "The field {$field} must be at least {$length} characters.";
		}
	}

	protected function max( $field, $length ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value !== null && strlen( (string) $value ) > $length ) {
			$this->errors[ $field ] = "The field {$field} may not be greater than {$length} characters.";
		}
	}

	protected function size( $field, $size ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value !== null && strlen( (string) $value ) !== (int) $size ) {
			$this->errors[ $field ] = "The field must be {$size} characters.";
		}
	}

	protected function integer( $field ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value !== null && ! filter_var( $value, FILTER_VALIDATE_INT ) ) {
			$this->errors[ $field ] = "The field {$field} must be an integer.";
		}
	}

	protected function nullable( $field ) {
		// This method exists just to support the nullable rule
		// The actual logic is handled in the validate() method
	}

	protected function in( $field, ...$values ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value !== null && ! in_array( $value, $values, true ) ) {
			$validValues = implode( ', ', $values );
			$this->errors[ $field ] = "The field {$field} must be one of: {$validValues}.";
		}
	}

	protected function numeric( $field ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value !== null && ! is_numeric( $value ) ) {
			$this->errors[ $field ] = "The field {$field} must be numeric.";
		}
	}

	protected function string( $field ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value !== null && ! is_string( $value ) ) {
			$this->errors[ $field ] = "The field {$field} must be a string.";
		}
	}

	protected function date( $field ) {
		$value = Str::getNestedValue( $this->data, $field );
		if ( $value !== null && ! strtotime( $value ) ) {
			$this->errors[ $field ] = "The field {$field} must be a valid date.";
		}
	}


	protected function merge( $fields ) {
		foreach ( $fields as $key => $value ) {
			Str::setNestedValue( $this->data, $key, $value );
		}
	}

	public function get( $field, $default = null ) {
		return Str::getNestedValue( $this->data, $field, $default );
	}

	public function __get( $name ) {
		return Str::getNestedValue( $this->data, $name, null );
	}

	/**
	 * Set a value using dot notation
	 * 
	 * @param string $field Field name (supports dot notation)
	 * @param mixed $value Value to set
	 * @return void
	 */
	public function set( $field, $value ) {
		Str::setNestedValue( $this->data, $field, $value );
	}

	/**
	 * Magic method to set values using dot notation
	 * 
	 * @param string $name Field name (supports dot notation)
	 * @param mixed $value Value to set
	 * @return void
	 */
	public function __set( $name, $value ) {
		Str::setNestedValue( $this->data, $name, $value );
	}

	/**
	 * Check if a field exists using dot notation
	 * 
	 * @param string $field Field name (supports dot notation)
	 * @return bool
	 */
	public function has( $field ) {
		return $this->hasNestedKey( $this->data, $field );
	}

	/**
	 * Magic method to check if field exists using dot notation
	 * 
	 * @param string $name Field name (supports dot notation)
	 * @return bool
	 */
	public function __isset( $name ) {
		return $this->hasNestedKey( $this->data, $name );
	}


	/**
	 * Check if nested key exists using dot notation
	 * 
	 * @param array $data The data array
	 * @param string $key The key (supports dot notation)
	 * @return bool
	 */
	protected function hasNestedKey( array $data, string $key ): bool {
		if ( strpos( $key, '.' ) === false ) {
			return array_key_exists( $key, $data );
		}

		$keys = explode( '.', $key );
		$current = $data;

		foreach ( $keys as $segment ) {
			if ( ! is_array( $current ) || ! array_key_exists( $segment, $current ) ) {
				return false;
			}
			$current = $current[ $segment ];
		}

		return true;
	}


	/**
	 * Get the data
	 * 
	 * @return array
	 */
	protected function getData() {
		return $this->data;
	}

	/**
	 * Get the value of a given field
	 * 
	 * @param string $field Field name (supports dot notation)
	 * @return mixed
	 */
	protected function getFieldValue( $field ) {
		return Str::getNestedValue( $this->data, $field );
	}
}