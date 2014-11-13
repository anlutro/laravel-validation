<?php

class ValidationExceptionTest extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function inheritsCorrectInterfaces()
	{
		$e = new \anlutro\LaravelValidation\ValidationException([]);
		$this->assertInstanceOf('JsonSerializable', $e);
		$this->assertInstanceOf('Illuminate\Contracts\Support\Arrayable', $e);
		$this->assertInstanceOf('Illuminate\Contracts\Support\Jsonable', $e);
		$this->assertInstanceOf('Illuminate\Contracts\Support\MessageProvider', $e);
	}
}
