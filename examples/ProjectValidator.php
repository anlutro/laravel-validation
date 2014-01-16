<?php
/**
 * An example validator class for projects.
 */
class ProjectValidator extends \c\Validator
{
	/**
	 * Get the common rules ran on every validation call.
	 */
	protected function getCommonRules()
	{
		return [
			'title' => ['required', 'max:200'],
			'start' => ['date_format:Y-m-d H:i:s'],
			'deadline' => ['date_format:Y-m-d H:i:s'],
		];
	}

	/**
	 * These rules are only ran when creating a new project.
	 */
	protected function getCreateRules()
	{
		return [
			'owner' => ['required', 'exists:users,id'],
		];
	}

	/**
	 * Prepare the rules before passing them to the validator.
	 */
	protected function prepareRules(array $rules, array $attributes)
	{
		// start and deadline are both optional, but if they're both set, we
		// want to validate that the deadline is after the start.
		if (!empty($attributes['start']) && !empty($attributes['deadline'])) {
			// because we're validating that start and deadline are valid date/
			// time strings, we can use the "after" validation rule.
			$rules['deadline'][] = 'after:' . $attributes['start'];
		}

		// always return the modified rules!
		return $rules;
	}
}
