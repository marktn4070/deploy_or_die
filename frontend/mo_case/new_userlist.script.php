<?php
require_once __DIR__.'/../header.inc.php';
authenticate('basic');

$client_id = $mysqli->real_escape_string($_GET['client_id']);
$user_id = !empty($_GET['user_id']) ? $mysqli->real_escape_string($_GET['user_id']) : SimpleAuth::user_id();

$doc = new TRP\HealDocument\HealDocument();
$sql = "SELECT `user`.`id`,`user`.`name` FROM `user_client`
	INNER JOIN `user` ON `user`.`id` = `user_client`.`user_id`
	WHERE `user_client`.`client_id`='$client_id' ORDER BY `user`.`name`";
$query = $mysqli->query($sql);
if($query->num_rows) {
	Ufo::attribute('user_id','class','form-select');
	if($query->num_rows>1) {
		$doc->el('option',['value'=>''])->te('');
	}
	while($rs = $query->fetch_object()) {
		$option = $doc->el('option',['value'=>$rs->id])->te($rs->name);
		if($user_id==$rs->id) $option->at(['selected']);
	}
}
else {
	Ufo::attribute('user_id','class','form-select bg-warning');
	$doc->el('option',['value'=>''])->te('Ingen brugere fundet!');
}

Ufo::output('user_id',$doc);
