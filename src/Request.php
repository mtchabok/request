<?php
namespace Mtchabok\Request;

use Exception;
use Mtchabok\ClassAlias\ClassAlias;
use Mtchabok\Request\DataBox\CookieBox;
use Mtchabok\Request\DataBox\FileBox;
use Mtchabok\Request\DataBox\PostBox;
use Mtchabok\Request\DataBox\QueryBox;
use Mtchabok\Request\DataBox\ServerBox;
use function json_decode;
use function simplexml_load_string;

/**
 * Class Request
 * @package Mtchabok\Request
 *
 * @property-read 	string 		method
 * @property 		ServerBox 	server
 * @property 		QueryBox 	query
 * @property 		PostBox		post
 * @property 		FileBox		file
 * @property 		CookieBox	cookie
 *
 * @method 			bool		isGet()
 * @method 			bool		isHead()
 * @method 			bool		isDelete()
 * @method 			bool		isPost()
 * @method 			bool		isPut()
 * @method 			bool		isPatch()
 * @method 			bool		isCli()
 * @method 			bool		isPurge()
 * @method 			bool		isOptions()
 * @method 			bool		isTrace()
 * @method 			bool		isConnect()
 *
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
	const METHOD_PURGE		= 'PURGE';
	const METHOD_OPTIONS	= 'OPTIONS';
	const METHOD_TRACE		= 'TRACE';
	const METHOD_CONNECT	= 'CONNECT';
	const METHODS_CLASSES   = [
		'GET'       => Request::class,
		'HEAD'      => Request::class,
		'DELETE'    => Request::class,
		'POST'      => Request::class,
		'PUT'       => Request::class,
		'PATCH'     => Request::class,
		'CLI'       => Request::class,
		'PURGE'     => Request::class,
		'OPTIONS'   => Request::class,
		'TRACE'     => Request::class,
		'CONNECT'   => Request::class,
	];



	/** @var string */
	protected $_method      = '';
	/** @var array */
	protected $_requestData	= [];

	/** @var ServerBox */
	protected $_server;
	/** @var QueryBox */
	protected $_query;
	/** @var PostBox */
	protected $_post;
	/** @var FileBox */
	protected $_file;
	/** @var CookieBox */
	protected $_cookie;











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
	 * @param string $index
	 * @return bool
	 */
	public function exist(string $index) :bool
	{
		if(array_key_exists($index, $this->_requestData))
			return true;
		if(static::METHOD_CLI==$this->_method)
			return false;
		if($this->_query->exist($index) || $this->_post->exist($index) || $this->_cookie->exist($index))
			return true;
		return false;
	}

	/**
	 * @param string $index
	 * @param mixed $default [optional]
	 * @return mixed
	 */
	public function get(string $index, $default = null)
	{
		if(array_key_exists($index, $this->_requestData))
			return $this->_requestData[$index];
		if(static::METHOD_CLI!=$this->_method){
			if($this->_query->exist($index))
				return $this->_query->get($index);
			elseif($this->_post->exist($index))
				return $this->_post->get($index);
			elseif($this->_cookie->exist($index))
				return $this->_cookie->get($index);
		}
		return $default;
	}

	/**
	 * @param string $index
	 * @param mixed $value
	 * @return $this
	 */
	public function set(string $index, $value)
	{
		$this->_requestData[(string) $index] = $value;
		return $this;
	}

	/**
	 * @param string $index
	 * @return $this
	 */
	public function delete(string $index)
	{
		unset($this->_requestData[(string) $index]);
		return $this;
	}











	/**
	 * @param string|array $options [optional]
	 * @return Request
	 */
	public static function newRequest($options = null)
	{
		$options = is_string($options) ?['method'=>strtoupper($options)] :[];
		$CN = static::class;
		if(array_key_exists('method', $options) && is_string($options['method'])
			&& array_key_exists($options['method'], static::METHODS_CLASSES))
			$CN = static::METHODS_CLASSES[$options['method']];
		return new $CN($options);
	}

	/**
	 * @param string|array $options [optional]
	 * @return Request
	 */
	public static function newRequestGlobal($options = null)
	{
		$options = is_string($options) ?['method'=>strtoupper($options)] :[];
		$CN = static::class;
		if(array_key_exists('method', $options) && is_string($options['method'])
			&& array_key_exists($options['method'], static::METHODS_CLASSES))
			$CN = static::METHODS_CLASSES[$options['method']];
		foreach (['server', 'query', 'post', 'file', 'cookie'] as $n)
			if (!isset($options[$n]['parent'])) $options[$n]['parentOnly'] = true;
		return new $CN($options);
	}





	final private function __construct(array $options = null)
	{
		foreach ([
				'server' => [ &$this->_server, ServerBox::class ],
				'query' => [ &$this->_query, QueryBox::class ],
				'post' => [ &$this->_post, PostBox::class ],
				'file' => [ &$this->_file, FileBox::class ],
				'cookie' => [ &$this->_cookie, CookieBox::class ],
	         ] as $pn=>&$pv){
			$CN = $pv[1];
			if(!empty($options[$pn]))
				$pv[0] = $options[$pn] instanceof $pv[1] ?$options[$pn] :new $CN($options[$pn]);
			else
				$pv[0] = new $CN();
		}

		// found method
		if(empty($options['method']) || !is_string($options['method'])){
			if(defined('STDIN') || php_sapi_name() === 'cli' || array_key_exists('SHELL', isset($_ENV) ?$_ENV :[]))
				$this->_method = static::METHOD_CLI;
			elseif (!empty($this->_server['REQUEST_METHOD']))
				$this->_method = strtoupper((string) $this->_server->REQUEST_METHOD);
		}else $this->_method = strtoupper((string) $options['method']);

		// parse raw input for put and patch methods
		if(in_array($this->_method, [static::METHOD_PUT, static::METHOD_PATCH])){
			$this->_requestData['input_raw'] = isset($options['input_raw']) ?$options['input_raw'] :file_get_contents('php://input');
			if($this->_server['CONTENT_TYPE']=='application/json'){
				$input = json_decode($this->_requestData['input_raw']);
				$this->_requestData['input'] = $input;
			}elseif ($this->_server['CONTENT_TYPE']=='application/xml'){
				try{
					$input = @simplexml_load_string($this->_requestData['input_raw']);
					$this->_requestData['input'] = $input;
				}catch (Exception $e){
					unset($this->_requestData['input']);
				}
			}
		}
	}

	public function __call($name, $arguments)
	{
		if(preg_match('#^is([A-Z].+)$#', $name, $nameParsed) && !empty($nameParsed[1]))
			return $this->isMethod(strtoupper($nameParsed[1]));
		return null;
	}


	public function __get($name)
	{
		switch ($name){
			case 'method': return $this->_method;
			case 'server': return $this->_server;
			case 'query': return $this->_query;
			case 'post': return $this->_post;
			case 'file': return $this->_file;
			case 'cookie': return $this->_cookie;
			default:
				return null;
		}
	}

	public function __set($name, $value)
	{}

	public function __isset($name)
	{ return in_array($name, ['method', 'server', 'query', 'post', 'file', 'cookie']); }

	public function __unset($name)
	{}

}