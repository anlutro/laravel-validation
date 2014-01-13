# Laravel 4 Validation service
Installation: `composer require anlutro/l4-validation`

Pick the latest stable version from packagist or the GitHub tag list.

WARNING: Backwards compatibility is not guaranteed during version 0.x.

### Validation service
Location: src/Validator.php

This validation class is a layer on top of Laravel's own Validation class (the one you create by calling Validator::make), meant to be injected into a repository or controller.

Create one Validator for each model or purpose. Overwrite the constructor to inject the correct model type and call `parent::__construct($model)`.

Your class must implement the abstract method `getCommonRules` - this should return an array of rules that are used on every validation call. In addition you can implement as many extra rule getters as you like - `getCreateRules`, `getUpdateRules`, `getFooRules` and so on.

Rules are dynamically merged depending on what action you're trying to validate. For example, `$validator->validCreate($attributes)` will merge `getCommonRules` and `getCreateRules`. If `getCreateRules` doesn't exist, it'll just use `getCommonRules`.

The class will automatically replace `<table>` with the model's table, and if you've set a key using `$validator->setKey(123)`, `<key>` will be replaced with what you provided (if you don't, it'll replace it with `null`). Very useful for exists and unique rules.

The class will also replace `[foo]` with whatever the value of 'foo' from the provided input (attributes) are. This way, you can add the value of another input field to a rule (for example, `'end_date' => ['date', 'after:[start_date]']`.

There are a couple of hooks you can use to add custom behaviour. `prepareRules($rules, $attributes)` will be called after rules are merged and allows you to change validation rules based on input if necessary. This method *needs* to return the modified array of rules!

`prepareValidator($validator)` is called before checking if validation passes or not, and can be used to add sometimes() rules or any other custom behaviour.

## Contact
Open an issue on GitHub if you have any problems or suggestions.

## License
The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).