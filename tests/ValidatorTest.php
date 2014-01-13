<?php

use Mockery as m;
use Illuminate\Support\Facades;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		Facades\Facade::setFacadeApplication(null);
		Facades\Facade::clearResolvedInstances();
		m::close();
	}

	public function testRulesArePassed()
	{
		$v = $this->makeValidator();
		$input = ['foo' => 'bar'];
		$rules = ['foo' => ['bar']];
		Facades\Validator::shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$result = $v->validSomething($input);
		$this->assertTrue($result);
	}
	
	public function testRulesArePassed2()
	{
		$v = $this->makeValidator();
		$input = ['foo' => 'bar'];
		$rules = ['foo' => ['bar']];
		Facades\Validator::shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => false]));
		$result = $v->validSomething($input);
		$this->assertFalse($result);
	}

	public function testRulesAreCombined()
	{
		$v = $this->makeValidator();
		$input = ['foo' => 'bar'];
		$rules = ['foo' => ['bar', 'foo'], 'bar' => ['baz']];
		Facades\Validator::shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$result = $v->validCreate($input);
		$this->assertTrue($result);
	}

	public function testTableIsReplaced()
	{
		$v = $this->makeValidator();
		$input = ['foo' => 'bar'];
		$rules = ['foo' => ['bar'], 'baz' => ['foo:table', 'bar:1']];
		Facades\Validator::shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$v->setKey(1);
		$result = $v->validDynamic($input);
		$this->assertTrue($result);
	}

	public function testInputVarIsReplaced()
	{
		$v = $this->makeValidator();
		$input = ['foo' => 'bar', 'input' => 'baz'];
		$rules = ['foo' => ['bar', 'bar:baz']];
		Facades\Validator::shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$result = $v->validInput($input);
		$this->assertTrue($result);
	}

	public function testRulesArePrepared()
	{
		$v = $this->makeValidator();
		$input = ['foo' => 'bar', 'prepareme' => 'prepareme'];
		$rules = ['foo' => ['bar'], 'prepared' => true];
		Facades\Validator::shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$result = $v->validSomething($input);
		$this->assertTrue($result);
	}

	protected function makeValidator($class = 'ValidatorStub', $model = null)
	{
		$model = $model ?: $this->makeMockModel();
		return new $class($model);
	}

	protected function makeMockModel($class = 'Illuminate\Database\Eloquent\Model')
	{
		$mock = m::mock($class);
		$mock->shouldReceive('getTable')->andReturn('table');
		return $mock;
	}
}

class ValidatorStub extends \c\Validator
{
	protected function getCommonRules()
	{
		return ['foo' => ['bar']];
	}

	protected function getCreateRules()
	{
		return ['foo' => ['foo'], 'bar' => ['baz']];
	}

	public function getDynamicRules()
	{
		return ['baz' => ['foo:<table>', 'bar:<key>']];
	}

	public function getInputRules()
	{
		return ['foo' => ['bar:[input]']];
	}

	protected function prepareRules(array $rules, array $attributes)
	{
		if (isset($attributes['prepareme'])) {
			$rules['prepared'] = true;
		}

		return $rules;
	}
}
