<?php
namespace Mtchabok\Request\DataBox;

/**
 * Class PostBox
 * @package Mtchabok\Request
 */
class PostBox extends ParameterBox
{
	public function __construct(array $options = null)
	{
		if(!is_array($options)) $options = [];
		if(empty($options['localOnly']) && !array_key_exists('parent', $options) && isset($_POST))
			$options['parent'] = &$_POST;
		parent::__construct($options);
	}
}