<?php

use Mockery as m;
use Illuminate\Support\Facades;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function testRulesArePassed()
	{
		$f = $this->makeFactory();
		$v = $this->makeValidator($f);
		$input = ['foo' => 'bar'];
		$rules = ['foo' => ['bar']];
		$f->shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$result = $v->validSomething($input);
		$this->assertTrue($result);
	}
	
	public function testRulesArePassed2()
	{
		$f = $this->makeFactory();
		$v = $this->makeValidator($f);
		$input = ['foo' => 'bar'];
		$rules = ['foo' => ['bar']];
		$f->shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => false]));
		$result = $v->validSomething($input);
		$this->assertFalse($result);
	}

	public function testRulesAreCombined()
	{
		$f = $this->makeFactory();
		$v = $this->makeValidator($f);
		$input = ['foo' => 'bar'];
		$rules = ['foo' => ['bar', 'foo'], 'bar' => ['baz']];
		$f->shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$result = $v->validCreate($input);
		$this->assertTrue($result);
	}

	public function testTableIsReplaced()
	{
		$f = $this->makeFactory();
		$v = $this->makeValidator($f);
		$v->setTable('table');
		$input = ['foo' => 'bar'];
		$rules = ['foo' => ['bar'], 'baz' => ['foo:table', 'bar:1']];
		$f->shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$v->setKey(1);
		$result = $v->validDynamic($input);
		$this->assertTrue($result);
	}

	public function testInputVarIsReplaced()
	{
		$f = $this->makeFactory();
		$v = $this->makeValidator($f);
		$input = ['foo' => 'bar', 'input' => 'baz'];
		$rules = ['foo' => ['bar', 'bar:baz']];
		$f->shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$result = $v->validInput($input);
		$this->assertTrue($result);
	}

	public function testRulesArePrepared()
	{
		$f = $this->makeFactory();
		$v = $this->makeValidator($f);
		$input = ['foo' => 'bar', 'prepareme' => 'prepareme'];
		$rules = ['foo' => ['bar'], 'prepared' => true];
		$f->shouldReceive('make')->once()->with($input, $rules)
			->andReturn(m::mock(['passes' => true]));
		$result = $v->validSomething($input);
		$this->assertTrue($result);
	}

	protected function makeFactory()
	{
		return m::mock('Illuminate\Validation\Factory');
	}

	protected function makeValidator($factory)
	{
		return new ValidatorStub($factory);
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
