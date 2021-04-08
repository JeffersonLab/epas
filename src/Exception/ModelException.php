<?php

namespace Jlab\Epas\Exception;

use Illuminate\Database\Eloquent\Model;

/**
 * Used when validation fails. Contains the invalid model for easy analysis.
 * @package Atlis
 */
class ModelException extends \RuntimeException {

	/**
	 * The invalid model.
	 * @var \Illuminate\Database\Eloquent\Model;
	 */
	protected $model;

	/**
	 * The message bag instance containing validation error messages
	 * @var \Illuminate\Support\MessageBag
	 */
	protected $errors;

	/**
	 * Receives the invalid model and sets the {@link model} and {@link errors} properties.
	 * @param Model $model The troublesome model.
	 */
	public function __construct($message, Model $model) {
		parent::__construct($message);
                $this->model  = $model;
		        $this->errors = $model->errors();
	}

	/**
	 * Returns the model with invalid attributes.
	 * @return Model
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * Returns directly the message bag instance with the model's errors.
	 * @return \Illuminate\Support\MessageBag
	 */
	public function getErrors() {
		return $this->errors;
	}
}
