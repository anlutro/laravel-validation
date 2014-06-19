<?php
/**
 * Laravel 4 Validator service
 *
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-validation
 */

namespace anlutro\LaravelValidation;

use Illuminate\Validation\Factory;

/**
 * Validation service class.
 */
abstract class Validator implements ValidatorInterface
{
	/**
	 * @var \Illuminate\Validation\Validator
	 */
	protected $validator;

	/**
	 * @var \Illuminate\Validation\Factory
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
	protected $throwExceptions = false;

	/**
	 * Default merge behaviour - whether specific rules are merged with
	 * getCommonRules or not.
	 *
	 * @var boolean
	 */
	protected $merge = true;

	/**
	 * @param \Illuminate\Validation\Factory
	 */
	public function __construct(Factory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toggleExceptions($toggle = true)
	{
		$this->throwExceptions = (bool) $toggle;
		return $this;
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function valid($action, array $attributes, $merge = null)
	{
		$rules = $this->prepareRules($this->getRules($action, $merge), $attributes);
		$rules = $this->replaceRuleVariables($rules, $attributes);

		$this->validator = $this->makeValidator($rules, $attributes);

		if ($this->throwExceptions) {
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
	 * Get the rules for an action.
	 *
	 * @param  string  $action
	 * @param  boolean $merge  Whether or not to merge with common rules. Leave
	 * the parameter out to default to $this->merge
	 *
	 * @return array
	 */
	protected function getRules($action, $merge = null)
	{
		$method = 'get' . ucfirst($action) . 'Rules';

		if (method_exists($this, $method)) {
			$rules = $this->$method();
		} else {
			$rules = [];
		}

		if ($merge === null) {
			$merge = $this->merge;
		}

		if ($merge) {
			$rules = array_merge_recursive($this->getCommonRules(), $rules);
		}

		return $rules;
	}

	/**
	 * Parse the rules of the validator.
	 *
	 * @param  array  $rules
	 * @param  array  $attributes
	 *
	 * @return array
	 */
	protected function parseRules(array $rules, array $attributes)
	{
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
	 * Make a new validator instance.
	 *
	 * @param  array  $rules
	 * @param  array  $attributes
	 *
	 * @return \Illuminate\Validation\Validator
	 */
	protected function makeValidator(array $rules, array $attributes)
	{
		$validator = $this->factory->make($attributes, $rules);
		$this->prepareValidator($this->validator);
		return $validator;
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
	 * {@inheritdoc}
	 */
	public function getErrors()
	{
		return $this->validator->errors();
	}

	/**
	 * Missing method calls to this class will be passed on to the underlying
	 * validator class for convenience.
	 */
	public function __call($method, $args)
	{
		if (substr($method, 0, 5) === 'valid') {
			if (!isset($args[0])) {
				throw new \InvalidArgumentException("Missing argument #1 for $method (array of attributes to validate)");
			}
			$action = substr($method, 5);
			$attributes = $args[0];
			return $this->valid($action, $attributes);
		} elseif ($this->validator !== null && is_callable([$this->validator, $method])) {
			return call_user_func_array([$this->validator, $method], $args);
		} else {
			throw new \BadMethodCallException("$method does not exist on this class or its Validator");
		}
	}
}
