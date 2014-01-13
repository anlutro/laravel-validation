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
use Illuminate\Support\Facades\Validator as VFactory;

/**
 * Validator class that can be injected into a repository or controller or
 * whatever else for easy validation of Eloquent models.
 */
abstract class Validator
{
	/**
	 * The key to replace <key> with in rules.
	 *
	 * @var string
	 */
	protected $key = 'NULL';

	/**
	 * @var Illuminate\Validation\Validator;
	 */
	protected $validator;

	/**
	 * @var Illuminate\Database\Eloquent\Model;
	 */
	protected $model;

	/**
	 * @param \Illuminate\Database\Eloquent\Model $model
	 */
	public function __construct(Model $model)
	{
		$this->model = $model;
	}

	/**
	 * Set the key of the active model. Should be done before updating if there
	 * are any exists/unique rules.
	 *
	 * @param mixed $key
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * Get the key of the active model.
	 *
	 * @return mixed
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Prepare rules, make the validator and check if it passes.
	 *
	 * @param  array  $rules
	 * @param  array  $attributes
	 *
	 * @return boolean
	 */
	protected function valid(array $rules, array $attributes)
	{
		$rules = $this->parseRules($rules, $attributes);
		$this->validator = VFactory::make($attributes, $rules);
		$this->prepareValidator($this->validator);
		return $this->validator->passes();
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

		return $this->valid($rules, $attributes);
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
		$rules = array_merge_recursive($this->getCommonRules(), $rules);
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
			if (strpos($item, '<key>') !== false) {
				$item = str_replace('<key>', $this->key, $item);
			}

			if (strpos($item, '<table>') !== false) {
				$item = str_replace('<table>', $this->model->getTable(), $item);
			}

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
		} elseif ($this->validator !== null) {
			return call_user_func_array([$this->validator, $method], $args);
		} else {
			throw new \BadMethodCallException("$method does not exist on this class or its Validator");
		}
	}
}
