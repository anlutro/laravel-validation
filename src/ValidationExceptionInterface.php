<?php
/**
 * Laravel 4 Validator service
 *
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  l4-validation
 */

namespace anlutro\LaravelValidation;

use JsonSerializable;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Support\Contracts\MessageProviderInterface;

// 5.x compatibility
if (!interface_exists('Illuminate\Support\Contracts\ArrayableInterface')) {
	class_alias('Illuminate\Contracts\Support\Arrayable',
		'Illuminate\Support\Contracts\ArrayableInterface');
}
if (!interface_exists('Illuminate\Support\Contracts\JsonableInterface')) {
	class_alias('Illuminate\Contracts\Support\Jsonable',
		'Illuminate\Support\Contracts\JsonableInterface');
}
if (!interface_exists('Illuminate\Support\Contracts\MessageProviderInterface')) {
	class_alias('Illuminate\Contracts\Support\MessageProvider',
		'Illuminate\Support\Contracts\MessageProviderInterface');
}

interface ValidationExceptionInterface extends
	ArrayableInterface,
	JsonableInterface,
	JsonSerializable,
	MessageProviderInterface
{
}
