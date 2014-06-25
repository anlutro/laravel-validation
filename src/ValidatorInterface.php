<?php
/**
 * Laravel 4 Validator service
 *
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-validation
 */

namespace anlutro\LaravelValidation;

/**
 * Interface the validator implements in order to make it swappable.
 */
interface ValidatorInterface
{
	/**
	 * Toggle whether to throw exceptions on validation errors.
	 *
	 * @param  boolean $toggle
	 *
	 * @return static
	 */
	public function toggleExceptions($toggle = true);

	/**
	 * Tell the validator to replace a certain value in the rules.
	 *
	 * @param  string $key
	 * @param  string $value
	 *
	 * @return void
	 */
	public function replace($key, $value);

	/**
	 * Validate a set of attributes against an action.
	 *
	 * @param  string  $action
	 * @param  array   $attributes
	 * @param  boolean $merge      Whether or not to merge with common rules.
	 * Leave the parameter out to use whatever is the default.
	 *
	 * @return boolean
	 *
	 * @throws \anlutro\Validation\ValidationException if exceptions are toggled
	 * on and validation fails
	 */
	public function valid($action, array $attributes, $merge = null);

	/**
	 * Get the validation errors. Alternative to ->errors()
	 *
	 * @return \Illuminate\Support\MessageBag
	 */
	public function getErrors();
}
