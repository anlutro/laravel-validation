# Laravel 4 Validation service
Installation: `composer require anlutro/l4-validation`

Pick the latest stable version from packagist or the GitHub tag list.

WARNING: Backwards compatibility is not guaranteed during version 0.x.

### Validation service
This validation class is a layer on top of Laravel's own Validation class (the one you create by calling Validator::make), meant to be injected into a repository or controller. It allows for more advanced rulesets and more dynamic rules, and is being utilized in my [repository class](https://github.com/anlutro/laravel-repository).

Your class must implement one abstract method: `getCommonRules`. This should return an array of rules that are used on every validation call and should be the bare minimum of rules. In addition you can implement as many extra rule getters as you like - `getCreateRules`, `getUpdateRules`, `getUpdateAsAdminRules`, `getFooRules` and so on.

Rules are dynamically merged depending on what action you're trying to validate. For example, `$validator->validCreate($attributes)` will merge `getCommonRules` and `getCreateRules`. If `getCreateRules` doesn't exist, it'll just use `getCommonRules`. `$validator->validUpdateAsAdmin($attributes)` will merge `getCommonRules` and `getUpdateAsAdminRules`. Rules are merged recursively.

You can tell the validator to replace variables in rules with the `replace($key, $value)` method. For example, if you have a unique rule and want to dynamically replace the table with a model's table, you can do the following:

	public function getCommonRules() {
		return ['email' => 'unique:<table>'];
	}

	$validator->replace('table', $model->getTable());

The class will also replace variables in square brackets with the matching key from input. For example, `[foo]` will be replaced with whatever the value of 'foo' from the provided input (attributes) are. This way, you can add the value of another input field to a rule (for example, `'end_date' => ['date', 'after:[start_date]']`.

Replacing variables will not work in regex rules, as that would potentially break regex operators.

There are a couple of hooks you can use to add custom behaviour. `prepareRules($rules, $attributes)` will be called after rules are merged and allows you to change validation rules based on input if necessary. This method *needs* to return the modified array of rules!

`prepareValidator($validator)` is called before checking if validation passes or not, and can be used to add sometimes() rules or any other custom behaviour onto the Illuminate\Validation\Validator object itself.

#### Exceptions
You can call `$validatorService->toggleExceptions();` to make the validator throw exceptions instead of just returning false. The exception thrown will be of the type `anlutro\LaravelValidation\ValidationException`, which has some useful methods.

- `getErrors()` gets a flat array of the validation errors.
- `getRules()` gets the array of rules that were used when validating.
- `getAttributes()` gets the array of input that was validated.

It can also be cast to a string via `(string) $exception` which will render each validation error on one line.

#### Model Validation
Use model events to validate your models automatically. Assuming 'MyValidator' is an instance of anlutro\LaravelValidation\Validator and 'MyModel' is a valid Eloquent model, you can do the following for as many models as you like:

    use anlutro\LaravelValidation\ModelValidator;
    ModelValidator::register('MyValidator', 'MyModel');

What this does behind the scenes:

    public static function register($validator, $model)
	{
		$validator = \Illuminate\Support\Facades\App::make($validator);
		\Illuminate\Support\Facades\Event::subscribe(new ModelValidator($validator, $model));
	}

This will validate creates and updates separately, so you can use `getCreateRules` and `getUpdateRules` as you normally would.

Validation of deletes may be coming later.

## Contact
Open an issue on GitHub if you have any problems or suggestions.

## License
The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).