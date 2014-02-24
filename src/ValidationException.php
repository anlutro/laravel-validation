<?php
/**
 * Laravel 4 Validator service
 *
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-validation
 */

namespace c;

use Exception;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Contracts\MessageProviderInterface;

/**
 * Exception thrown on validation errors.
 */
class ValidationException extends Exception implements MessageProviderInterface
{
	protected $errors;
	protected $rules;
	protected $attributes;
	protected $action;

	/**
	 * @param mixed  $errors
	 * @param array  $rules
	 * @param array  $attributes
	 * @param string $action     The action attempted when validation failed.
	 */
	public function __construct($errors, array $rules = array(), array $attributes = array(), $action = null)
	{
		if (is_array($errors)) {
			$errors = new MessageBag($errors);
		} elseif ($errors instanceof MessageProviderInterface) {
			$errors = $errors->getMessageBag();
		}

		if (!$errors instanceof MessageBag) {
			$type = is_object($errors) ? get_class($errors) : gettype($errors);
			throw new Exception("Parameter #1 of \c\ValidationException::__construct must be an array, instance of Illuminate\Support\Contracts\MessageProviderInterface or Illuminate\Support\MessageBag - $type given", 0, $this);
		}

		$this->errors = $errors;
		$this->rules = $rules;
		$this->attributes = $attributes;
		$this->action = (string) $action;

		if ($this->action) {
			$this->message = "Validation errors on action [$this->action]";
		} else {
			$this->message = "Validation errors";
		}
	}

	/**
	 * Get the message bag instance.
	 *
	 * @return Illuminate\Support\MessageBag
	 */
	public function getMessageBag()
	{
		return $this->errors;
	}

	/**
	 * Get an array of all error messages.
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors->all();
	}

	/**
	 * Get the array of rules that the validation was done with.
	 *
	 * @return array
	 */
	public function getRules()
	{
		return $this->rules;
	}

	/**
	 * Get the array of attributes the validation was done with.
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Get the action that was executed when validation failed.
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Render the exception as a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$errors = implode(PHP_EOL, $this->getErrors());
		return $this->getMessage() . PHP_EOL . $errors;
	}
}
