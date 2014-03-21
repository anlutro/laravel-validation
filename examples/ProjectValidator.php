<?php
/**
 * An example validator class for projects.
 */
class ProjectValidator extends \anlutro\LaravelValidation\Validator
{
	/**
	 * Get the common rules ran on every validation call.
	 *
	 * In this example we use variables wrapped in [brackets] to tell the
	 * validator to replace these with values from the input. In this example,
	 * goal must be a number between min and max.
	 */
	protected function getCommonRules()
	{
		return [
			'title'    => ['required', 'max:200'],
			'start'    => ['date_format:Y-m-d H:i:s'],
			'deadline' => ['date_format:Y-m-d H:i:s'],
			'min'      => ['required', 'numeric'],
			'max'      => ['required', 'numeric'],
			'goal'     => ['between:[min],[max]']
		];
	}

	/**
	 * These rules are only ran when creating a new project.
	 *
	 * In this example we provide the <usertable> and <userkey> variables -
	 * these are to be replaced by the table of the user and the name of the
	 * primary key column of the user table. We tell the validator to replace
	 * these variables by calling the following:
	 *
	 * $validator->replace('usertable', $usermodel->getTable());
	 * $validator->replace('userkey', $usermodel->getKeyName());
	 */
	protected function getCreateRules()
	{
		return [
			'owner' => ['required', 'exists:<usertable>,<userkey>'],
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
