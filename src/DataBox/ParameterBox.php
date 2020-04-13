<?php
namespace Mtchabok\Request\DataBox;

use ArrayIterator;

/**
 * Class ParameterBox
 * @package Mtchabok\Request
 *
 *
 */
class ParameterBox implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/** @var ParameterBox|array */
	protected $_parent;
	/** @var bool */
	protected $_localOnly = false;
	/** @var bool */
	protected $_parentOnly = false;
	/** @var array */
	protected $_data = [];


	/** @return bool */
	public function hasParent() :bool
	{ return isset($this->_parent); }



	/**
	 * @param string $index
	 * @param bool $localOnly [optional]
	 * @return bool
	 */
	public function exist(string $index, bool $localOnly = null) :bool
	{
		if(is_null($localOnly)) $localOnly = (bool) $this->_localOnly;
		if(!$this->_parentOnly && array_key_exists($index, $this->_data))
			return true;
		elseif(!$localOnly && $this->_parent){
			if(is_array($this->_parent))
				return array_key_exists($index, $this->_parent);
			elseif ($this->_parent instanceof ParameterBox)
				return $this->_parent->exist($index);
		}
		return false;
	}

	/**
	 * @param string $index
	 * @param bool $localOnly [optional]
	 * @return bool
	 */
	public function isEmpty(string $index, bool $localOnly = null) :bool
	{
		if(!$this->_parentOnly && array_key_exists($index, $this->_data))
			return empty($this->_data[$index]);
		elseif((false===$localOnly || (is_null($localOnly) && !$this->_localOnly)) && $this->_parent){
			if(is_array($this->_parent))
				return !array_key_exists($index, $this->_parent) || empty($this->_parent[$index]);
			elseif ($this->_parent instanceof ParameterBox)
				return $this->_parent->isEmpty($index);
		} return true;
	}

	/**
	 * @param string $index
	 * @param mixed $default [optional]
	 * @param bool $localOnly [optional]
	 * @return mixed
	 */
	public function get(string $index, $default = null, bool $localOnly = null)
	{
		$value = null;
		if(is_null($localOnly)) $localOnly = (bool) $this->_localOnly;
		if(!$this->_parentOnly && array_key_exists($index, $this->_data))
			$value = $this->_data[$index];
		elseif(!$localOnly && $this->_parent){
			if(is_array($this->_parent))
				$value = array_key_exists($index, $this->_parent) ?$this->_parent[$index] :null;
			elseif ($this->_parent instanceof ParameterBox)
				return $this->_parent->get($index, $default);
		}
		return is_null($value) ?$default :$value;
	}

	/**
	 * @param string $index
	 * @param string|array $default [optional]
	 * @param null|bool|string $trim [optional]
	 * @param bool $localOnly [optional]
	 * @return array|string
	 */
	public function getString(string $index, $default = null, $trim = null, bool $localOnly = null)
	{
		$values = $this->get($index, $default, $localOnly);
		$isArray = true;
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
	 * @param string $index
	 * @param int|float $default [optional]
	 * @param bool $localOnly [optional]
	 * @return int[]|float[]|int|float
	 */
	public function getNumber(string $index, $default = 0, bool $localOnly = null)
	{
		$values = $this->get($index, $default, $localOnly);
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
	 * @param callable $matchFunc
	 * 		function(string $index, $value){ return is match ?true :false; }
	 * @param bool $localOnly [optional]
	 * @return array
	 */
	public function find(callable $matchFunc, bool $localOnly = null) :array
	{
		$founds = [];
		if(!$this->_parentOnly) {
			foreach ($this->_data as $index=>$value){
				if(call_user_func_array($matchFunc, [$index, $value]))
					$founds[$index] = $value;
			}
		}
		if((false===$localOnly || (is_null($localOnly) && !$this->_localOnly)) && $this->_parent){
			if(is_array($this->_parent)) {
				foreach ($this->_parent as $index=>$value){
					if(!array_key_exists($index, $founds) && call_user_func_array($matchFunc, [$index, $value]))
						$founds[$index] = $value;
				}
			}elseif ($this->_parent instanceof ParameterBox){
				foreach ($this->_parent->find($matchFunc) as $index=>$value){
					if(!array_key_exists($index, $founds))
						$founds[$index] = $value;
				}
			}
		}
		return $founds;
	}

	/**
	 * @param string $index
	 * @param mixed $value
	 * @param bool $localOnly [optional]
	 * @return $this
	 */
	public function set(string $index, $value, bool $localOnly = null)
	{
		if(is_null($localOnly)) $localOnly = (bool) $this->_localOnly;
		if(!$this->_parentOnly)
			$this->_data[$index] = $value;
		elseif (!$localOnly && $this->_parent){
			if(is_array($this->_parent))
				$this->_parent[$index] = $value;
			elseif ($this->_parent instanceof ParameterBox)
				$this->_parent->set($index, $value);
		}
		return $this;
	}

	/**
	 * @param string $index
	 * @param bool $localOnly [optional]
	 * @return $this
	 */
	public function delete(string $index, bool $localOnly = null)
	{
		if(is_null($localOnly)) $localOnly = (bool) $this->_localOnly;
		if(!$this->_parentOnly)
			unset($this->_data[$index]);
		elseif (!$localOnly && $this->_parent){
			if(is_array($this->_parent))
				unset($this->_parent[$index]);
			elseif ($this->_parent instanceof ParameterBox)
				$this->_parent->delete($index);
		}
		return $this;
	}




	/**
	 * @param bool $localOnly [optional]
	 * @return array
	 */
	public function toArray(bool $localOnly = null) :array
	{
		$data = [];
		if(!$this->_parentOnly) $data = $this->_data;
		if((false===$localOnly || (is_null($localOnly) && !$this->_localOnly)) && $this->_parent){
			if(is_array($this->_parent)) {
				$data+= $this->_parent;
			}elseif ($this->_parent instanceof ParameterBox) {
				$data+= $this->_parent->toArray();
			}
		}
		return $data;
	}

	/**
	 * @param bool $localOnly [optional]
	 * @return ArrayIterator
	 */
	public function getIterator(bool $localOnly = null) : ArrayIterator
	{ return new ArrayIterator($this->toArray($localOnly)); }


	/**
	 * @param bool $localOnly [optional]
	 * @return int
	 */
	public function count(bool $localOnly = null) :int
	{
		$count = 0;
		if(is_null($localOnly)) $localOnly = (bool) $this->_localOnly;
		if(!$this->_parentOnly)
			$count+= count($this->_data);
		if(!$localOnly && $this->_parent){
			if(is_array($this->_parent)) {
				if($count){
					foreach ($this->_data as $n=>&$v){
						if(array_key_exists($n, $this->_parent))
							--$count;
					}
				}
				$count += count($this->_parent);
			}elseif ($this->_parent instanceof ParameterBox) {
				if($count){
					foreach ($this->_data as $n=>&$v){
						if($this->_parent->exist($n))
							--$count;
					}
				}
				$count += $this->_parent->count();
			}
		}
		return $count;
	}



	/**
	 * ParameterBox constructor.
	 * @param array $options [optional]
	 */
	public function __construct(array $options = null)
	{
		if(!is_array($options)) $options = [];

		if(array_key_exists('parent', $options)){
			if(is_array($options['parent']))
				$this->_parent = &$options['parent'];
			elseif ($options['parent'] instanceof ParameterBox)
				$this->_parent = $options['parent'];
		}

		if(array_key_exists('localOnly', $options))
			$this->_localOnly = (bool) $options['localOnly'];
		elseif (array_key_exists('parentOnly', $options))
			$this->_parentOnly = (bool) $options['parentOnly'];

		if(array_key_exists('data', $options) && is_array($options['data'])){
			foreach ($options['data'] as $index=>$value)
				$this->set($index, $value);
		}
	}


	public function offsetExists($offset)
	{ return $this->exist($offset); }

	public function offsetGet($offset)
	{ return $this->get($offset); }

	public function offsetSet($offset, $value)
	{ $this->set($offset, $value); }

	public function offsetUnset($offset)
	{ $this->delete($offset); }


	public function __isset($name)
	{ return $this->exist($name); }

	public function __get($name)
	{ return $this->get($name); }

	public function __set($name, $value)
	{ $this->set($name, $value); }

	public function __unset($name)
	{ $this->delete($name); }

}