<?php
namespace anlutro\LaravelValidation\Tests;

use anlutro\LaravelTesting\EloquentTestCase;
use Mockery as m;

class ModelValidatorTest extends EloquentTestCase
{
	protected $enableEvents = true;

	public function getMigrations()
	{
		return [__NAMESPACE__.'\\ValidatorModelMigration'];
	}

	public function tearDown()
	{
		m::close();
	}

	/** @test **/
	public function validatorIsCalledOnCreateAndFails()
	{
		$this->setExpectedException('anlutro\LaravelValidation\ValidationException',
			'Validation errors on action [Create]');
		$input = ['name' => '...'];
		$this->makeValidatorThatFails($input);
		ValidatorModelStub::create($input);
	}

	/** @test **/
	public function validatorIsCalledOnCreateAndSucceeds()
	{
		$input = ['name' => '...'];
		$this->makeValidatorThatPasses($input);
		$result = ValidatorModelStub::create($input);
		$this->assertInstanceOf(__NAMESPACE__.'\\ValidatorModelStub', $result);
	}

	/** @test **/
	public function validatorIsCalledOnDeleteAndFails()
	{
		$this->setExpectedException('anlutro\LaravelValidation\ValidationException',
			'Validation errors on action [Delete]');
		$input = ['name' => '...'];
		$model = ValidatorModelStub::create($input);
		$this->makeValidatorThatFails($model->getAttributes());
		$model->delete();
	}

	/** @test **/
	public function validatorIsCalledOnDeleteAndSucceeds()
	{
		$input = ['name' => '...'];
		$model = ValidatorModelStub::create($input);
		$this->makeValidatorThatPasses($model->getAttributes());
		$model->delete();
	}

	protected function makeValidatorThatPasses(array $input, $class = null, $model = null)
	{
		return $this->makeValidator($input, true, $class, $model);
	}

	protected function makeValidatorThatFails(array $input, $class = null, $model = null)
	{
		return $this->makeValidator($input, false, $class, $model);
	}

	protected function makeValidator(array $input, $passes, $class = null, $model = null)
	{
		$class = $class ?: __NAMESPACE__.'\\ValidatorStub';
		$model = $model ?: __NAMESPACE__.'\\ValidatorModelStub';
		$factory = m::mock('Illuminate\Validation\Factory');
		$validator = new $class($factory);
		$mockValidator = m::mock('Illuminate\Validation\Validator');
		$this->eventDispatcher->subscribe(new \anlutro\LaravelValidation\ModelValidator($validator, $model));
		$factory->shouldReceive('make')->once()->with($input, $validator->getCommonRules())->andReturn($mockValidator);
		$mockValidator->shouldReceive('passes')->andReturn((bool) $passes);
		if (!$passes) {
			$mockValidator->shouldReceive('getMessageBag')->andReturn(new \Illuminate\Support\MessageBag);
		}
	}
}

class ValidatorStub extends \anlutro\LaravelValidation\Validator
{
	public function getCommonRules()
	{
		return ['name' => ['required', 'min:10']];
	}
}
class ValidatorModelStub extends \Illuminate\Database\Eloquent\Model
{
	public $timestamps = false;
	protected $fillable = ['name'];
}
class ValidatorModelMigration extends \Illuminate\Database\Migrations\Migration
{
	public function up()
	{
		\Schema::create('validator_model_stubs', function($t) {
			$t->increments('id');
			$t->string('name', 50);
		});
	}

	public function down()
	{
		\Schema::dropIfExists('validator_model_stubs');
	}
}
