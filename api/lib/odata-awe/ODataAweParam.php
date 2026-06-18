<?php
/*
ODataAwe is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/odata-awe/blob/main/LICENSE
*/
trait ODataAweParamTrait {
	private $param = [];

	private function Param($input) {
		$this->param['top'] = isset($input['$top']) ? (int) $input['$top'] : null;
		$this->param['skip'] = isset($input['$skip']) ? (int) $input['$skip'] : 0;
		$this->param['count'] = empty($input['$count']) ? false : true;
		$this->param['select'] = empty($input['$select']) ? null : array_fill_keys(explode(',',$input['$select']),null);
		$this->param['filter'] = [];
		if(!empty($input['$filter'])) {
			foreach(explode(' and ',$input['$filter']) as $value) {
				list($field,$ops,$value) = explode(' ',$value);
				if(in_array($ops,['gt','ge','lt','le','eq','ne'])) {
					if($value=="''") $value = '';
					elseif($value=="null") $value = null;
					$this->param['filter'][] = [$field,$ops,$value];
				}
			}
		}
		$this->param['orderby'] = [];
		if(!empty($input['$orderby'])) {
			foreach(explode(',',$input['$orderby']) as $value) {
				$value = explode(' ',$value);
				$direction = (isset($value[1]) && $value[1]=='desc') ? SORT_DESC : SORT_ASC;
				$this->param['orderby'][] = [$value[0],$direction];
			}
		}
	}

	private function Header($headers) {
		if(isset($headers['Prefer'])) {
			$prefer = explode(',',$headers['Prefer']);
			foreach($prefer as $item) {
				if(strpos($item,'odata.maxpagesize')===0) {
					$maxpagesize = (int) substr($item,strpos($item,'=')+1);
					if($maxpagesize<$this->options['maxpagesize']) {
						$this->options['maxpagesize'] = $maxpagesize;
					}
				}
			}
		}
	}

	public function getTop() {
		return $this->param['top'] ? $this->param['top'] : $this->options['maxpagesize']+1;
	}
	public function getSkip() {
		return $this->param['skip'];
	}
	public function getSelect() {
		return $this->param['select'];
	}
	public function getOrderby() {
		return $this->param['orderby'];
	}
	public function getFilter() {
		return $this->param['filter'];
	}
}
