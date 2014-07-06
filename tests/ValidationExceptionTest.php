<?php
namespace anlutro\LaravelValidation\Tests;

use PHPUnit_Framework_TestCase;

class Test extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function inheritsCorrectInterfaces()
	{
		$e = new \anlutro\LaravelValidation\ValidationException([]);
		$this->assertInstanceOf('JsonSerializable', $e);
		$this->assertInstanceOf('Illuminate\Support\Contracts\ArrayableInterface', $e);
		$this->assertInstanceOf('Illuminate\Support\Contracts\JsonableInterface', $e);
		$this->assertInstanceOf('Illuminate\Support\Contracts\MessageProviderInterface', $e);
	}
}
