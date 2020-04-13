<?php
namespace Mtchabok\Request\DataBox;

/**
 * Class QueryBox
 * @package Mtchabok\Request\DataBox
 */
class QueryBox extends ParameterBox
{
	public function __construct(array $options = null)
	{
		if(!is_array($options)) $options = [];
		if(empty($options['localOnly']) && !array_key_exists('parent', $options))
			$options['parent'] = &$_GET;
		parent::__construct($options);
	}
}