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
	{ return static::__callStatic(__FUNCTION__, [is_string($options) ?['method'=>$options] :$options]); }

	/**
	 * @param string|array $options [optional]
	 * @return Request
	 */
	public static function newRequestGlobal($options = null)
	{ return static::__callStatic(__FUNCTION__, [is_string($options) ?['method'=>$options] :$options]); }

	/**
	 * @param array $options [optional]
	 * @return Request
	 */
	public static function newGet($options = null)
	{ return static::__callStatic(__FUNCTION__, [$options]); }

	/**
	 * @param array $options [optional]
	 * @return Request
	 */
	public static function newGetGlobal($options = null)
	{ return static::__callStatic(__FUNCTION__, [$options]); }

	/**
	 * @param array $options [optional]
	 * @return Request
	 */
	public static function newPost($options = null)
	{ return static::__callStatic(__FUNCTION__, [$options]); }

	/**
	 * @param array $options [optional]
	 * @return Request
	 */
	public static function newPostGlobal($options = null)
	{ return static::__callStatic(__FUNCTION__, [$options]); }

	/**
	 * @param array $options [optional]
	 * @return Request
	 */
	public static function newCli($options = null)
	{ return static::__callStatic(__FUNCTION__, [$options]); }

	/**
	 * @param array $options [optional]
	 * @return Request
	 */
	public static function newCliGlobal($options = null)
	{ return static::__callStatic(__FUNCTION__, [$options]); }








	/**
	 * @return ClassAlias
	 */
	public static function getClassAlias()
	{
		if(!$CA = ClassAlias::getClassAlias('MTCHABOK_REQUEST')){
			ClassAlias::getClassAlias()->addOnNotExist([
				['alias'=>'MtchabokRequest', 'className'=>static::class, 'method'=>null],
				['alias'=>'MtchabokRequestCookieBox', 'className'=>CookieBox::class],
				['alias'=>'MtchabokRequestFileBox', 'className'=>FileBox::class],
				['alias'=>'MtchabokRequestPostBox', 'className'=>PostBox::class],
				['alias'=>'MtchabokRequestQueryBox', 'className'=>QueryBox::class],
				['alias'=>'MtchabokRequestServerBox', 'className'=>ServerBox::class],
			]);

			$CA = ClassAlias::newClassAlias('MTCHABOK_REQUEST');
			$CA->add([
				['alias'=>'newRequest'	, 'className'=>static::class, 'link'=>['', 'MtchabokRequest'], 'method'=>null],
				['alias'=>'newGet'		, 'className'=>static::class, 'method'=>Request::METHOD_GET],
				['alias'=>'newPost'		, 'className'=>static::class, 'method'=>Request::METHOD_POST],
				['alias'=>'newDelete'	, 'className'=>static::class, 'method'=>Request::METHOD_DELETE],
				['alias'=>'newHead'		, 'className'=>static::class, 'method'=>Request::METHOD_HEAD],
				['alias'=>'newPut'		, 'className'=>static::class, 'method'=>Request::METHOD_PUT],
				['alias'=>'newPatch'	, 'className'=>static::class, 'method'=>Request::METHOD_PATCH],
				['alias'=>'newCli'		, 'className'=>static::class, 'method'=>Request::METHOD_CLI],
				['alias'=>'newPurge'	, 'className'=>static::class, 'method'=>Request::METHOD_PURGE],
				['alias'=>'newOptions'	, 'className'=>static::class, 'method'=>Request::METHOD_OPTIONS],
				['alias'=>'newTrace'	, 'className'=>static::class, 'method'=>Request::METHOD_TRACE],
				['alias'=>'newConnect'	, 'className'=>static::class, 'method'=>Request::METHOD_CONNECT],
			]);
			ClassAlias::addClassAlias($CA);
		} return $CA;
	}








	public static function __callStatic($name, $arguments)
	{
		if(substr($name, 0, 3)=='new'){
			if($isGlobal = substr($name, -6)=='Global')
				$name = substr($name,0,-6);
			$aliasDetail = static::getClassAlias()->get($name, true);
			$className = $aliasDetail
				?$aliasDetail->getClassName(Request::class)
				:static::getClassAlias()->getClassName('MtchabokRequest', Request::class);
			$options = isset($arguments[0]) ?(array)$arguments[0] :[];
			if($isGlobal) {
				foreach (['server', 'query', 'post', 'file', 'cookie'] as $n) {
					if (!isset($options[$n]['parent'])) $options[$n]['parentOnly'] = true;
				}
			}
			if($method = $aliasDetail ?$aliasDetail->get('method',null) :null)
				$options['method'] = $method;
			$request = new $className($options);
			assert($request instanceof Request);
			return $request;
		}
		return null;
	}





	final private function __construct(array $options = null)
	{
		foreach ([
			'server' => [ &$this->_server, 'MtchabokRequestServerBox', ServerBox::class ],
			'query' => [ &$this->_query, 'MtchabokRequestQueryBox', QueryBox::class ],
			'post' => [ &$this->_post, 'MtchabokRequestPostBox', PostBox::class ],
			'file' => [ &$this->_file, 'MtchabokRequestFileBox', FileBox::class ],
			'cookie' => [ &$this->_cookie, 'MtchabokRequestCookieBox', CookieBox::class ],
				 ] as $pn=>&$pv){
			$className = self::getClassAlias()->getClassName($pv[1], $pv[2]);
			if(!empty($options[$pn]))
				$pv[0] = $options[$pn] instanceof $pv[2] ?$options[$pn] :new $className($options[$pn]);
			else
				$pv[0] = new $className();
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
		if(preg_match('#^is(([A-Z]).*)$#', $name, $nameParsed) && !empty($nameParsed[1]))
			return $this->isMethod($nameParsed[1]);
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