<?php
/*
ODataAwe is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/odata-awe/blob/main/LICENSE
*/
require_once __DIR__.'/ODataAwe.php';

class ODataAweMySQL extends ODataAwe {
	private $dbconn = null;
	private $database = [];

	public function addMySQLQuery($entityset,$fields,$database,$sort,$where = [],$group = []) {
		$this->database[$entityset] = [
			'database' => $database,
			'fields' => $fields,
			'group' => $group,
			'sort' => $sort,
			'where' => $where,
		];
		parent::addFunction($entityset,$fields,[$this,'query']);
	}

	protected function query($odata) {
		$setup = $this->database[$odata->getEntitySet()];

		// Select
		$select = $odata->getSelect();
		$fields = [];
		foreach($setup['fields'] as $key => $value) {
			if(!$select || array_key_exists($key,$select)) {
				$fields[] = $value['dbfield'].' as `'.$key.'`';
			}
		}
		$fields = implode(',',$fields);

		// Filter
		$filter = $odata->getFilter();
		$where = $setup['where'];
		foreach($filter as $value) {
			switch($value[1]) {
				case 'gt': $where[] = $setup['fields'][$value[0]]['dbfield']." > '".$value[2]."'"; break;
				case 'ge': $where[] = $setup['fields'][$value[0]]['dbfield']." >= '".$value[2]."'"; break;
				case 'le': $where[] = $setup['fields'][$value[0]]['dbfield']." <= '".$value[2]."'"; break;
				case 'lt': $where[] = $setup['fields'][$value[0]]['dbfield']." < '".$value[2]."'"; break;
				case 'eq': $where[] = $setup['fields'][$value[0]]['dbfield']." = '".$value[2]."'"; break;
				case 'ne': $where[] = $setup['fields'][$value[0]]['dbfield']." != '".$value[2]."'"; break;
			}
		}
		if($where) {
			$where = implode(' AND ',$where);
		}
		else {
			$where = 'TRUE';
		}

		// Sorting
		$orderby = $odata->getOrderby();
		$sort = [];

		foreach($orderby as $value) {
			if($value[1]==SORT_ASC) {
				$sort[] = $setup['fields'][$value[0]]['dbfield'].' ASC';
			}
			elseif($value[1]==SORT_DESC) {
				$sort[] = $setup['fields'][$value[0]]['dbfield'].' DESC';
			}
		}
		$sort[] = $setup['sort'];
		$sort = implode(',',$sort);

		// Slice result
		$limit = 'LIMIT '.$odata->getTop();
		$offset = $odata->getSkip() ? 'OFFSET '.$odata->getSkip() : "";

		// Set Count
		$sql = "SELECT COUNT(*) as count ";
		$sql .= "FROM $setup[database] ";
		$sql .= "WHERE $where ";
		$query = $this->dbconn->query($sql);
		$odata->setCount($query->fetch_assoc()['count']);

		$sql = "SELECT $fields ";
		$sql .= "FROM $setup[database] ";
		$sql .= "WHERE $where ";
		if($setup['group']) $sql .= "GROUP BY ".implode(',',$setup['group']).' ';
		$sql .= "ORDER BY $sort $limit $offset";
		$query = $this->dbconn->query($sql);

		while($rs = $query->fetch_assoc()) {
			$result = $odata->addData($rs);
			if($result===false) {
				$odata->nextLink();
				return;
			}
		}
	}

	public function setDBConn($dbconn) {
		$this->dbconn = $dbconn;
	}
}