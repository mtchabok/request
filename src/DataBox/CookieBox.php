<?php
namespace Mtchabok\Request\DataBox;

/**
 * Class CookieBox
 * @package Mtchabok\Request
 */
class CookieBox extends ParameterBox
{
	public function __construct(array $options = null)
	{
		if(!is_array($options)) $options = [];
		if(empty($options['localOnly']) && !array_key_exists('parent', $options) && isset($_COOKIE))
			$options['parent'] = &$_COOKIE;
		parent::__construct($options);
	}
}