<?php
namespace Mtchabok\Request\DataBox;

/**
 * Class FileBox
 * @package Mtchabok\Request\DataBox
 */
class FileBox extends ParameterBox
{
	public function __construct(array $options = null)
	{
		if(!is_array($options)) $options = [];
		if(empty($options['localOnly']) && !array_key_exists('parent', $options) && isset($_FILES))
			$options['parent'] = &$_FILES;
		parent::__construct($options);
	}
}