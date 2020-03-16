<?php
namespace Mtchabok\Request;

/**
 * Class RequestDataValue
 * @package Mtchabok\Request
 */
class RequestDataValue
{
	public $type = '';
	public $value;

	public function __construct($type, $value)
	{
		$this->type = strtoupper($type);
		$this->value = $value;
	}
}