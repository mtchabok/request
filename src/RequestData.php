<?php
namespace Mtchabok\Request;

/**
 * Class RequestData
 * @package Mtchabok\Request
 */
class RequestData
{
	/** @var Request */
	private $_request;
	/** @var string */
	private $_prefixName = '';


	/** @return bool */
	public function isPost()
	{ return $this->_request->isMethod(Request::METHOD_POST); }

	/** @return bool */
	public function isGet()
	{ return $this->_request->isMethod(Request::METHOD_GET); }

	/** @return bool */
	public function isHead()
	{ return $this->_request->isMethod(Request::METHOD_HEAD); }

	/** @return bool */
	public function isDelete()
	{ return $this->_request->isMethod(Request::METHOD_DELETE); }

	/** @return bool */
	public function isPut()
	{ return $this->_request->isMethod(Request::METHOD_PUT); }

	/** @return bool */
	public function isPatch()
	{ return $this->_request->isMethod(Request::METHOD_PATCH); }

	/** @return bool */
	public function isCli()
	{ return $this->_request->isMethod(Request::METHOD_CLI); }


	/**
	 * @param string $name
	 * @param string $varType=null
	 * @return bool
	 */
	public function exist($name, $varType = null)
	{ return $this->_request->exist($this->_name($name), $varType); }

	/**
	 * @param string $name
	 * @param mixed|null $default=null
	 * @param string $varType=null
	 * @return mixed
	 */
	public function get($name, $default = null, $varType = null)
	{ return $this->_request->get($this->_name($name), $default, $varType); }

	/**
	 * @param string $name
	 * @param string|array $default
	 * @param null|bool|string|array $trim
	 * @param string $varType=null
	 * @return array|string
	 */
	public function getString($name, $default = '', $trim = null, $varType = null)
	{
		$values = $this->get($name, $default, $varType);
		$isArray = true;
		if(is_array($trim)) $trim = implode('', $trim);
		if(!is_array($values)){
			$isArray = false;
			$values = (array) $values;
		}
		foreach ($values as &$value){
			if(true===$trim) $value = trim($value);
			elseif (is_string($trim) && $trim) $value = trim($value, $trim);
			else $value = (string) $value;
		}
		return $isArray ?$values :array_pop($values);
	}

	/**
	 * @param string $name
	 * @param int|float $default=0
	 * @param string $varType=null
	 * @return array|int|float
	 */
	public function getNumber($name, $default = 0, $varType = null)
	{
		$values = $this->get($name, $default, $varType);
		$isArray = true;
		if(!is_array($values)){
			$isArray = false;
			$values = (array) $values;
		}
		foreach ($values as &$value){
			$valueDouble = floatval($value);
			$valueInteger = intval($value);
			$value = ($valueInteger==$valueDouble) ?$valueInteger :$valueDouble;
		}
		return $isArray ?$values :array_pop($values);
	}

	/**
	 * @param string $name
	 * @return mixed|null
	 */
	public function getFile($name)
	{ return $this->_request->get($this->_name($name), null, Request::VARS_FILES); }



	public function __construct(Request $request, array $options)
	{
		$this->_request = $request;
		if(!empty($options['prefixName'])) $this->_prefixName = $options['prefixName'];

	}



	private function _name($name)
	{
		return $this->_prefixName.$name;
	}
}