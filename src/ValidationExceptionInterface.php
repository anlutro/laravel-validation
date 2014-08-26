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

// 4.3 compatibility
if (!class_exists('Illuminate\Support\Contracts\ArrayableInterface')
	&& class_exists('Illuminate\Contracts\Support\ArrayableInterface')) {
	class_alias('Illuminate\Contracts\Support\ArrayableInterface',
		'Illuminate\Support\Contracts\ArrayableInterface');
}
if (!class_exists('Illuminate\Support\Contracts\JsonableInterface')
	&& class_exists('Illuminate\Contracts\Support\JsonableInterface')) {
	class_alias('Illuminate\Contracts\Support\JsonableInterface',
		'Illuminate\Support\Contracts\JsonableInterface');
}
if (!class_exists('Illuminate\Support\Contracts\MessageProviderInterface')
	&& class_exists('Illuminate\Contracts\Support\MessageProviderInterface')) {
	class_alias('Illuminate\Contracts\Support\MessageProviderInterface',
		'Illuminate\Support\Contracts\MessageProviderInterface');
}

interface ValidationExceptionInterface extends
	ArrayableInterface,
	JsonableInterface,
	JsonSerializable,
	MessageProviderInterface
{
}
