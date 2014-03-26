<?php
namespace anlutro\LaravelValidation;

use Illuminate\Database\Eloquent\Model;

class ModelValidator
{
	public static function register($validator, $model)
	{
		$validator = \Illuminate\Support\Facades\App::make($validator);
		\Illuminate\Support\Facades\Event::subscribe(new static($validator, $model));
	}

	public function __construct(Validator $validator, $model)
	{
		$this->validator = $validator;
		$this->validator->toggleExceptions(true);
		$this->model = ($model instanceof Model) ? get_class($model) : $model;
	}

	public function subscribe($dispatcher)
	{
		$this->addEvent($dispatcher, 'creating', 'validateCreate');
		$this->addEvent($dispatcher, 'updating', 'validateUpdate');
		$this->addEvent($dispatcher, 'deleting', 'validateDelete');
	}

	protected function addEvent($dispatcher, $event, $method)
	{
		static $class; if ($class === null) $class = get_class($this);
		$dispatcher->listen("eloquent.{$event}: {$this->model}", [$this, $method]);
	}

	public function validateCreate(Model $model)
	{
		$this->validate('create', $model);
	}

	public function validateUpdate(Model $model)
	{
		$this->validator->replace('key', $model->getKey());
		$this->validate('update', $model);
	}

	public function validateDelete(Model $model)
	{
		$this->validator->replace('key', $model->getKey());
		$this->validate('delete', $model);
	}

	protected function validate($action, Model $model)
	{
		$this->validator->replace('table', $model->getTable());
		$method = 'valid' . ucfirst($action);
		$this->validator->$method($model->getAttributes());
	}
}
