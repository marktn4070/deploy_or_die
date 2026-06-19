<?php
/*
FancyFilter is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/fancy-filter/blob/master/LICENSE
*/
declare(strict_types=1);

class FancyFilter {
	private static $filters = [], $escape_function, $cookie_prefix = 'ffilter_', $store_options = [];

	public static function get($name, $defaults = []){
		if(!isset(self::$filters[$name])){
			self::$filters[$name] = new self($name);
		}
		self::$filters[$name]->defaults = $defaults;
		return self::$filters[$name];
	}

	public static function set_escape_function($func){
		if(is_callable($func)){
			self::$escape_function = $func;
		}
	}

	public static function set_cookie_prefix($prefix){
		if(is_string($prefix)){
			self::$cookie_prefix = $prefix;
		}
	}

	public static function set_option($option, $value){
		self::$store_options[$option] = $value;
	}

	public static function set_option_array($options){
		self::$store_options = $options;
	}

	private $name, $defaults = [], $values = null;

	private function __construct($name){
		$this->name = self::$cookie_prefix.$name;
	}

	public function set($key, $value){
		$this->load();
		if(is_null($value)){
			unset($this->values[$key]);
		} else {
			$this->values[$key] = $value;
		}
		$this->store();
	}

	public function set_values($values){
		$this->load();
		foreach($values as $key => $value){
			if(is_null($value)){
				unset($this->values[$key]);
			} else {
				$this->values[$key] = $value;
			}
		}
		$this->store();
	}

	private function load(){
		if(!isset($this->values)){
			if(!isset($_COOKIE[$this->name])){
				$this->values = [];
			} else {
				$this->values = json_decode($_COOKIE[$this->name],true);
				if(!is_array($this->values)) $this->values = [];
			}
		}
	}

	private function store(){
		if(!isset($this->values)) return;
		$json = json_encode($this->values, JSON_FORCE_OBJECT);
		if(!empty(self::$store_options)){
			if(PHP_VERSION_ID >= 70300){
				setcookie($this->name, $json, self::$store_options);
			} else {
				setcookie(
					$this->name,
					$json,
					isset(self::$store_options['expires']) ? self::$store_options['expires'] : 0,
					isset(self::$store_options['path']) ? self::$store_options['path'] : "",
					isset(self::$store_options['domain']) ? self::$store_options['domain'] : "",
					isset(self::$store_options['secure']) ? self::$store_options['secure'] : false,
					isset(self::$store_options['httponly']) ? self::$store_options['httponly'] : false
				);
			}
		} else {
			setcookie($this->name, $json);
		}	
	}

	public function __get($key){
		$this->load();
		if(isset($this->values[$key])){
			$value = $this->values[$key];
		} elseif(isset($this->defaults[$key])){
			$value = $this->defaults[$key];
		} else {
			$value = null;
		}
		if(isset($value) && isset(self::$escape_function)){
			$escape_func = self::$escape_function;
			$value = $escape_func($value);
		}
		return $value;
	}

	public function __isset($key){
		$this->load();
		return isset($this->values[$key]) || isset($this->defaults[$key]);
	}
}
