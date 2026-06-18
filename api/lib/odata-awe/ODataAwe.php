<?php
/*
ODataAwe is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/odata-awe/blob/main/LICENSE
*/
require_once __DIR__.'/ODataAweMeta.php';
require_once __DIR__.'/ODataAweParam.php';

class ODataAwe {
	use ODataAweMetaTrait;
	use ODataAweParamTrait;

	private $options = null;
	private $entityset = null;
	private $functions = [];
	private $data = [];
	private $count = null;
	private $pagesize = 0;
	private $nextlink = null;

	public function __construct($options = []) {
		$this->options = $options;
		if(!isset($this->options['rewritebase'])) {
			$this->options['rewritebase'] = '';
		}
		if(!isset($this->options['namespace'])) {
			$this->options['namespace'] = 'ODataAwe';
		}
		if(!isset($this->options['maxpagesize'])) {
			$this->options['maxpagesize'] = 20000;
		}
		else {
			$this->options['maxpagesize'] = (int) $this->options['maxpagesize'];
		}
	}

	public function handle() {
		$uri = $_SERVER['REQUEST_URI'];

		if($this->options['rewritebase']) {
			if(strpos($uri,$this->options['rewritebase'])===0) {
				$uri = substr($uri,strlen($this->options['rewritebase']));
			}
		}
		if(strpos($uri,'/')===0) {
			$uri = substr($uri,1);
		}
		if(strpos($uri,'?')!==false) {
			$uri = substr($uri,0,strpos($uri,'?'));
		}
		$path = explode('/',$uri);

		foreach($path as $part) {
			if($part==='$metadata') {
				$this->Metadata();
				return;
			}
		}

		$this->Header(getallheaders());
		$this->Param($_GET);
		header('Content-type: application/json; odata.metadata=minimal');
		header('OData-Version: 4.0');

		$this->entityset = !empty($path[0]) ? $path[0] : null;

		if($this->entityset===null) {
			foreach($this->functions as $key => $function) {
				$this->AddData([
					'name' => $key,
					'kind' => 'EntitySet',
					'url' => $key,
				]);
			}
		}
		else {
			if(isset($this->functions[$this->entityset])) {
				if(is_callable($this->functions[$this->entityset]['callback'])) {
					call_user_func($this->functions[$this->entityset]['callback'],$this,...$this->functions[$this->entityset]['param']);
				}
				else {
					throw new Exception('Function: '.$this->functions[$this->entityset]['callback'].' is not callable');
				}
			}
			else {
				echo 'EntitySet: '.$this->entityset.' is not found';
				http_response_code(404);
				exit;
			}
		}

		$context = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'].$this->options['rewritebase'];
		if($this->entityset) $context .= '/'.$this->entityset;

		$json = [];
		$json['@odata.context'] = $context.($this->entityset ? '/$metadata#'.$this->entityset : '/$metadata');
		if($this->param['count'] && $this->count!==null) $json['@odata.count'] = $this->count;
		if($this->nextlink) {
			$json['@odata.nextLink'] = $context.'?'.$this->nextlink;
		}
		$json['value'] = $this->data;
		echo json_encode($json,JSON_UNESCAPED_SLASHES);
	}

	public function addFunction($name,$fields,$callback = null,...$param) {
		if($callback===null) $callback = $name;
		$this->functions[$name] = [
			'callback' => $callback,
			'field' => $fields,
			'param' => $param,
		];
	}

	public function addData($data) {
		if($this->pagesize<$this->options['maxpagesize']) {
			if($this->entityset) {
				$this->pagesize++;
				foreach($data as $key => $value) {
					if($value===null && isset($this->functions[$this->entityset]['field'][$key]['null'])) {
						$data[$key] = null; continue 1;
					}
					switch($this->functions[$this->entityset]['field'][$key]['type']) {
						case 'bool': $data[$key] = (bool) $value; continue 2;
						case 'int': $data[$key] = (int) $value; continue 2;
						case 'float': $data[$key] = (float) $value; continue 2;
						case 'string': $data[$key] = (string) $value; continue 2;
						case 'datetime': $data[$key] = (new Datetime($value))->format('c'); continue 2;
					}
				}
			}
			$this->data[] = $data;
		}
		else {
			return false;
		}
	}

	public function nextLink() {
		$array = $_GET;
		if($this->param['top']) $array['$top'] = $this->param['top']-$this->pagesize;
		$array['$skip'] = ((int) $this->param['skip']+$this->pagesize);
		$variables = [];
		foreach($array as $key => $value) {
			$variables[] = $key.'='.urlencode($value);
		}
		$this->nextlink = implode('&',$variables);
	}

	public function setCount($count) {
		$this->count = (int) $count;
	}

	public function getEntitySet() {
		return $this->entityset;
	}
}
