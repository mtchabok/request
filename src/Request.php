<?php
namespace Mtchabok\Request;

/**
 * Class Request
 * @package Mtchabok\Request
 *
 * @property-read string method
 */
class Request
{
	const METHOD_GET        = 'GET';
	const METHOD_HEAD       = 'HEAD';
	const METHOD_DELETE     = 'DELETE';
	const METHOD_POST       = 'POST';
	const METHOD_PUT        = 'PUT';
	const METHOD_PATCH      = 'PATCH';
	const METHOD_CLI        = 'CLI';

	const VARS_GET          = 'GET';
	const VARS_POST         = 'POST';
	const VARS_COOKIE       = 'COOKIE';
	const VARS_FILES        = 'FILES';
	const VARS_RAW_DATA     = 'RAW_DATA';
	const VARS_CLI_ARGS     = 'CLI_ARGS';

	private $_method        = '';
	private $_useGlobalData = true;

	private $_datas         = [];








	/**
	 * @return Request
	 * @param array $options
	 * @throws \Exception
	 */
	public static function newAuto($options = [])
	{
		$request = new self(null, $options);
		return $request;
	}

	/**
	 * @return Request
	 * @param array $options
	 * @throws \Exception
	 */
	public static function newGet($options = [])
	{
		$request = new self(self::METHOD_GET, $options);
		return $request;
	}

	/**
	 * @return Request
	 * @param array $options
	 * @throws \Exception
	 */
	public static function newHead($options = [])
	{
		$request = new self(self::METHOD_HEAD, $options);
		return $request;
	}

	/**
	 * @return Request
	 * @param array $options
	 * @throws \Exception
	 */
	public static function newDelete($options = [])
	{
		$request = new self(self::METHOD_DELETE, $options);
		return $request;
	}

	/**
	 * @return Request
	 * @param array $options
	 * @throws \Exception
	 */
	public static function newPost($options = [])
	{
		$request = new self(self::METHOD_POST, $options);
		return $request;
	}

	/**
	 * @return Request
	 * @param array $options
	 * @throws \Exception
	 */
	public static function newPut($options = [])
	{
		$request = new self(self::METHOD_PUT, $options);
		return $request;
	}

	/**
	 * @return Request
	 * @param array $options
	 * @throws \Exception
	 */
	public static function newPatch($options = [])
	{
		$request = new self(self::METHOD_PATCH, $options);
		return $request;
	}

	/**
	 * @return Request
	 * @param array $options
	 * @throws \Exception
	 */
	public static function newCli($options = [])
	{
		$request = new self(self::METHOD_CLI, $options);
		return $request;
	}









	/**
	 * @param string|array $methods
	 * @return bool
	 */
	public function isMethod($methods)
	{
		$methods = array_reduce(
			is_array($methods) ?$methods :func_get_args()
			, function ($all, $v){
			if(is_string($v)) $all[] = strtoupper($v);
			return $all;
		}
		);
		return in_array($this->_method, $methods);
	}




	/**
	 * @param array $options
	 * @return RequestData
	 */
	public function data($options = [])
	{ return new RequestData($this, array_merge([], $options)); }


	/**
	 * @param string $name
	 * @param string $varType=null
	 * @return bool
	 */
	public function exist($name, $varType = null)
	{
		if(null!==$varType) $varType = strtoupper($varType);
		if(array_key_exists($name, $this->_datas) AND (null===$varType OR $varType==$this->_datas[$name]->type))
			return true;
		if($this->_useGlobalData && in_array($this->method, [self::METHOD_GET, self::METHOD_DELETE, self::METHOD_HEAD, self::METHOD_PUT, self::METHOD_PATCH])){
			return ( (null===$varType OR self::VARS_GET==$varType) AND array_key_exists($name, $_GET) )
				OR ( (null===$varType OR self::VARS_COOKIE==$varType) AND array_key_exists($name, $_COOKIE) )
				OR false;
		}elseif ($this->_useGlobalData && in_array($this->method, [self::METHOD_POST])){
			return ( (null===$varType OR self::VARS_GET==$varType) AND array_key_exists($name, $_GET) )
				OR ( (null===$varType OR self::VARS_POST==$varType) AND array_key_exists($name, $_POST) )
				OR ( (null===$varType OR self::VARS_COOKIE==$varType) AND array_key_exists($name, $_COOKIE) )
				OR ( (null===$varType OR self::VARS_FILES==$varType) AND array_key_exists($name, $_FILES) )
				OR false;
		}
		return false;
	}


	/**
	 * @param string $name
	 * @param mixed|null $default=null
	 * @param string $varType=null Request::VARS_...
	 * @return mixed
	 */
	public function get($name, $default = null, $varType = null)
	{
		if(null!==$varType) $varType = strtoupper($varType);
		if(array_key_exists($name, $this->_datas) AND ( $varType === null OR $varType==$this->_datas[$name]->type ) ){
			return $this->_datas[$name]->value;
		}
		if($this->_useGlobalData && in_array($this->method, [self::METHOD_GET, self::METHOD_DELETE, self::METHOD_HEAD, self::METHOD_PUT, self::METHOD_PATCH])){
			if((null===$varType OR self::VARS_GET==$varType) AND array_key_exists($name, $_GET))
				return $_GET[$name];
			elseif ((null===$varType OR self::VARS_COOKIE==$varType) AND array_key_exists($name, $_COOKIE))
				return $_COOKIE[$name];
		}elseif ($this->_useGlobalData && in_array($this->method, [self::METHOD_POST])){
			if((null===$varType OR self::VARS_GET==$varType) AND array_key_exists($name, $_GET))
				return $_GET[$name];
			elseif ((null===$varType OR self::VARS_POST==$varType) AND array_key_exists($name, $_POST))
				return $_POST[$name];
			elseif ((null===$varType OR self::VARS_COOKIE==$varType) AND array_key_exists($name, $_COOKIE))
				return $_COOKIE[$name];
			elseif ((null===$varType OR self::VARS_FILES==$varType) AND array_key_exists($name, $_FILES))
				return $_FILES[$name];
		}
		return $default;
	}









	/**
	 * Request constructor.
	 * @param string $method=null
	 * @param array $options=[]
	 * @throws \Exception
	 */
	public function __construct($method = null, $options = [])
	{
		if(null===$method){

			// cli mode
			if(defined('STDIN') || php_sapi_name() === 'cli' || array_key_exists('SHELL', $_ENV)) {
				$this->_method = self::METHOD_CLI;
				$this->_datas['args'] = new RequestDataValue(self::VARS_CLI_ARGS, $_SERVER['argv']);
				$this->_datas['filename'] = new RequestDataValue(self::VARS_CLI_ARGS, array_shift($this->_datas['args']->value));
				foreach ($this->_datas['args']->value as $arg){
					if(false!==strpos($arg, '=')){
						$arg = explode('=', $arg,2);
						//$this->_datas[trim($arg[0])] = trim($arg[1]);
						$this->_datas[trim($arg[0])] = new RequestDataValue(self::VARS_CLI_ARGS, trim($arg[1]));
					}
				}
				// get mode
			}elseif (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']==self::METHOD_GET){
				$this->_method = self::METHOD_GET;
				// post mode
			}elseif (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']==self::METHOD_POST){
				$this->_method = self::METHOD_POST;
				// head & delete mode
			}elseif (!empty($_SERVER['REQUEST_METHOD']) && in_array(strtoupper($_SERVER['REQUEST_METHOD']), [self::METHOD_HEAD, self::METHOD_DELETE])){
				$this->_method = strtoupper($_SERVER['REQUEST_METHOD']);
				// put & patch mode
			}elseif (!empty($_SERVER['REQUEST_METHOD']) && in_array(strtoupper($_SERVER['REQUEST_METHOD']), [self::METHOD_PUT, self::METHOD_PATCH])){
				$this->_method = strtoupper($_SERVER['REQUEST_METHOD']);
				$input = file_get_contents('php://input');
				if($_SERVER['CONTENT_TYPE']=='application/json'){
					$input = json_decode($input);
					//$this->_datas['input'] = $input;
					$this->_datas['input'] = new RequestDataValue(self::VARS_RAW_DATA, $input);
				}elseif ($_SERVER['CONTENT_TYPE']=='application/xml'){
					try{
						$input = @simplexml_load_string($input);
						$this->_datas['input'] = new RequestDataValue(self::VARS_RAW_DATA, $input);
					}catch (\Exception $e){
						unset($this->_datas['input']);
					}

				}
			}else throw new \Exception("current http request not supported: {$_SERVER['REQUEST_METHOD']}");

		}elseif(in_array($method, [self::METHOD_GET, self::METHOD_POST, self::METHOD_DELETE, self::METHOD_HEAD, self::METHOD_PUT, self::METHOD_PATCH, self::METHOD_CLI])) {
			$this->_method = $method;
			$this->_useGlobalData = false;
		}else throw new \Exception("this http method not supported: {$method}");
	}


	public function __get($name)
	{
		switch ($name){
			case 'method':
				return $this->_method;
			default:
				return null;
		}
	}

	public function __set($name, $value)
	{}

	public function __isset($name)
	{}

	public function __unset($name)
	{}

}