<?php
/**
 * Laravel 4 Validator service
 *
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-validation
 */

namespace c;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Factory;

/**
 * Validator class that can be injected into a repository or controller or
 * whatever else for easy validation of Eloquent models.
 */
abstract class Validator
{
	/**
	 * @var Illuminate\Validation\Validator
	 */
	protected $validator;

	/**
	 * @var Illuminate\Validation\Factory
	 */
	protected $factory;

	/**
	 * Variables to replace in the validation rules.
	 *
	 * @var array
	 */
	protected $replace = array(
		'key' => 'NULL',
	);

	/**
	 * Whether the validator should throw an exception or just return a
	 * boolean on validation errors.
	 *
	 * @var boolean
	 */
	protected $throwException = false;

	/**
	 * @param Illuminate\Validation\Factory
	 */
	public function __construct(Factory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * Toggle whether to throw exceptions on validation errors.
	 *
	 * @param  boolean $toggle
	 *
	 * @return static
	 */
	public function toggleExceptions($toggle = true)
	{
		$this->throwException = (bool) $toggle;
		return $this;
	}

	/**
	 * Tell the validator to replace a certain value in the rules.
	 *
	 * @param  string $key
	 * @param  string $value
	 *
	 * @return void
	 */
	public function replace($key, $value)
	{
		$key = (string) $key;
		$value = (string) $value;

		if ($value === null) {
			unset($this->replace[$key]);
		} else {
			$this->replace[$key] = $value;
		}
	}

	/**
	 * @deprecated  Use replace()
	 */
	public function setKey($key)
	{
		$this->replace('key', $key);
	}

	/**
	 * @deprecated  Use replace()
	 */
	public function setTable($table)
	{
		$this->replace('table', $table);
	}

	/**
	 * Handle a dynamic 'valid' method call.
	 *
	 * @param  string $method
	 * @param  array  $args
	 *
	 * @return boolean
	 */
	protected function dynamicValidCall($method, array $args)
	{
		$action = substr($method, 5);
		$method = 'get' . ucfirst($action) . 'Rules';

		if (method_exists($this, $method)) {
			$rules = $this->$method();
		} else {
			$rules = [];
		}

		$attributes = $args[0];

		return $this->valid($rules, $attributes, true, $action);
	}

	/**
	 * Prepare rules, make the validator and check if it passes.
	 *
	 * @param  array  $rules
	 * @param  array  $attributes
	 * @param  bool   $merge      Whether or not to merge with common rules
	 * @param  string $action
	 *
	 * @return boolean
	 */
	protected function valid(array $rules, array $attributes, $merge = true, $action = null)
	{
		$rules = $this->parseRules($rules, $attributes, $merge);
		$this->validator = $this->factory->make($attributes, $rules);
		$this->prepareValidator($this->validator);

		if ($this->throwException) {
			if ($this->validator->passes()) {
				return true;
			} else {
				throw new ValidationException($this->validator->getMessageBag(), $rules, $attributes, $action);
			}
		} else {
			return $this->validator->passes();
		}
	}

	/**
	 * Parse the rules of the validator.
	 *
	 * @param  array  $rules
	 * @param  array  $attributes
	 * @param  bool   $merge      Whether or not to merge with common rules
	 *
	 * @return array
	 */
	protected function parseRules(array $rules, array $attributes, $merge = true)
	{
		if ($merge) $rules = array_merge_recursive($this->getCommonRules(), $rules);
		$rules = $this->prepareRules($rules, $attributes);
		$rules = $this->replaceRuleVariables($rules, $attributes);
		return $rules;
	}

	/**
	 * Dynamically replace variables in the rules.
	 *
	 * @param  array  $rules
	 * @param  array  $attributes
	 *
	 * @return array
	 */
	protected function replaceRuleVariables(array $rules, array $attributes)
	{
		array_walk_recursive($rules, function(&$item, $key) use($attributes) {
			// don't mess with regex rules
			if (substr($item, 0, 6) === 'regex:') return;

			// replace explicit variables
			foreach ($this->replace as $key => $value) {
				if (strpos($item, "<$key>") !== false) {
					$item = str_replace("<$key>", $value, $item);
				}
			}

			// replace input variables
			foreach ($attributes as $key => $value) {
				if (strpos($item, "[$key]") !== false) {
					$item = str_replace("[$key]", $value, $item);
				}
			}
		});

		return $rules;
	}

	/**
	 * Prepare the rules before replacing variables and passing it to the
	 * validator factory.
	 *
	 * @param  array  $rules
	 * @param  array  $attributes
	 *
	 * @return array
	 */
	protected function prepareRules(array $rules, array $attributes)
	{
		return $rules;
	}

	/**
	 * Prepare the validator class before checking if it passes or not. Useful
	 * for adding sometimes() calls or similar.
	 *
	 * @param  \Illuminate\Validation\Validator $validator
	 *
	 * @return void
	 */
	protected function prepareValidator($validator) {}

	/**
	 * Get the common rules to be used in every validation call.
	 *
	 * @return array
	 */
	protected abstract function getCommonRules();

	/**
	 * Missing method calls to this class will be passed on to the underlying
	 * validator class for convenience.
	 */
	public function __call($method, $args)
	{
		if (substr($method, 0, 5) === 'valid') {
			return $this->dynamicValidCall($method, $args);
		} elseif ($this->validator !== null && method_exists($this->validator, $method)) {
			return call_user_func_array([$this->validator, $method], $args);
		} else {
			throw new \BadMethodCallException("$method does not exist on this class or its Validator");
		}
	}
}
