<?php
require_once __DIR__.'/../header.inc.php';
require_once __DIR__.'/function.php';
authenticate('basic');

$doc = new BootSome();
$form = $doc->form()->at(['id'=>'mainform']);

$filter = FancyFilter::get('main',['page'=>1]);
$page = (int) $filter->page;

$cs = case_status();
$searcharray = [];
$where = [];

if($filter->search) {
	$search = $mysqli->real_escape_string(trim($filter->search));
	$number = clear_case($search);
	$searcharray[] = "`damage`.number LIKE '%$number%'";
	$searcharray[] = "`case`.plate LIKE '%$search%'";
	$searcharray = ($searcharray) ? implode(' OR ',$searcharray) : $searcharray = 'true';
}
else {
	$searcharray = 'true';
	
	if(SimpleAuth::access('employee')) {
		if($filter->client_id) {
			$where[] = "`case`.client_id=".$mysqli->real_escape_string($filter->client_id);
		}
		if($filter->responsible_id) {
			$where[] = "`case`.responsible_id=".$mysqli->real_escape_string($filter->responsible_id);
		}
	}

	$status = [];
	if($filter->status=='active') {
		foreach($cs as $key => $val) {
			if($val['active'] && !$val['exception']) $status[] = $key;
		}
	}
	elseif($filter->status=='stop') {
		foreach($cs as $key => $val) {
			if($val['exception']) $status[] = $key;
		}
	}
	elseif($filter->status) {
		$status[] = $mysqli->real_escape_string($filter->status);
	}
	else {
		foreach($cs as $key => $val) {
			if($val['active']) $status[] = $key;
		}
	}
	$where[] = "status IN ('".implode("','",$status)."')";
}

if(!SimpleAuth::access('employee')) {
	$user_id = SimpleAuth::user_id();
	$clientjoin = "INNER JOIN `user_client` ON `user_client`.`user_id`='$user_id' AND `user_client`.`client_id` = `case`.`client_id`";
}
else {
	$clientjoin = "";
}

$where = $where ? implode(' AND ',$where) : 'TRUE';

$sql = "SELECT `case`.id,`case`.status,`case`.plate,`client`.name as client,`case`.attention,
		`insurance`.name as insurance,responsible.name as responsible,`case`.`create`,`case`.`deadline`,
		`client`.`disable`,`client`.`archive`,`client`.`batch`,COUNT(DISTINCT `case_comment`.`id`) as comment,
		GROUP_CONCAT(DISTINCT `damage`.`state`) as state,`case`.`note`
	FROM `case`
	$clientjoin
	INNER JOIN client ON client.id = `case`.client_id
	INNER JOIN insurance ON insurance.id = `case`.insurance_id
	LEFT JOIN damage ON damage.case_id = `case`.id
	LEFT JOIN `case_comment` ON `case_comment`.`case_id` = `case`.`id`
	LEFT JOIN user as responsible ON responsible.id = `case`.responsible_id
	WHERE $where AND ($searcharray)
	GROUP BY `case`.`id`
	ORDER BY `case`.`deadline` DESC,`case`.`create` DESC,`case`.id";
$total = (int) $mysqli->query($sql)->num_rows;

$limit = 20;
$sql .= ' LIMIT '.$limit.' OFFSET '.($page-1)*$limit;
$query = $mysqli->query($sql);

if($query->num_rows) {
	$table = $form->table();
	$thead = $table->thead()->tr();
	$thead->th()->at(['colspan'=>2]);
	$thead->th()->te('Kunde');
	$thead->th()->te('Nummerplade');
	$thead->th()->te('Selskab');
	$thead->th()->te('Ansvarlig');
	$thead->th()->te('Oprettet');
	if(SimpleAuth::access('employee')) $thead->th()->te('Deadline');
	
	$now = new DateTime('now');
	$ds = damage_state();
	
	$tbody = $table->tbody();
	while($rs = $query->fetch_object()) {
		$tr = $tbody->tr();
		
		$td = $tr->td();

		if($rs->attention) {
			$td->te(' ');
			$td->el('span',['class'=>'text-info'])->icon('calculator');
		}
		if($rs->disable && SimpleAuth::access('employee')) {
			$td->te(' ');
			$td->el('span',['class'=>'text-warning','title'=>'Deaktiveret kunde!'])->icon('exclamation-triangle');
		}
		if($rs->archive && SimpleAuth::access('employee')) {
			$td->te(' ');
			$td->el('span',['class'=>'text-danger','title'=>'Arkiveret kunde!'])->icon('exclamation-circle');
		}

		if(!empty($rs->state)) {
			foreach(explode(',',$rs->state) as $state) {
				$td->te(' ');
				$td->el('span',['class'=>'text-'.$ds[$state]['color']])->icon($ds[$state]['icon'])->at(['title'=>$ds[$state]['name']]);
			}
		}
		
		if($rs->comment && SimpleAuth::access('employee')) {
			$td->te(' ');
			$td->el('span',['class'=>'text-secondary','title'=>'Kommentar'])->icon('comment');
		}
		if($rs->batch && $rs->status==='snt' && SimpleAuth::access('employee')) {
			$tr->td()->at(['center','onclick'=>'event.stopPropagation();'])->checkbox('batch['.$rs->id.']',true);
		}
		else {
			$tr->td();
		}
		$tr->td()->te($rs->client);
		$tr->td()->te($rs->plate);
		$tr->td()->te($rs->insurance);
		$tr->td()->te($rs->responsible ?? '');
		$tr->td()->te((new Datetime($rs->create))->format('j/n H:i'));
		
		if(SimpleAuth::access('employee')) {
			if($rs->deadline) {
				$minutes = ((new Datetime($rs->deadline))->getTimestamp() - $now->getTimestamp()) / 60;
				$minutes = round($minutes).'M';
			}
			else {
				$minutes = '-';
			}
			$tr->td()->te($minutes);
		}
	}
}
else {
	$doc->te('Intet fundet!');
}

$url = function($i) {
	return ['onclick'=>"FancyFilter.set('main','page',".$i.");Ufo.update('main');"];
};

$doc->pagination($total,$limit,$page,$url);

Ufo::output('main',$doc);