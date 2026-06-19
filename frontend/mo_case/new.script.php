<?php
require_once __DIR__.'/../header.inc.php';
require_once __DIR__.'/function.php';
authenticate('basic');

$client_id = (int) $_POST['client_id'];
$user_id = (int) $_POST['user_id'];

if(!SimpleAuth::access('employee')) {
	$authuser_id = (int) SimpleAuth::user_id();
	$sql = "SELECT `user_client`.`user_id` FROM `user_client`
		WHERE `user_client`.`user_id`='$authuser_id' AND `user_client`.`client_id` = '$client_id'";
	$query = $mysqli->query($sql);

	if($query->num_rows!==1) {
		Ufo::abort('dialog');
		exit;
	}
	
	if($user_id!==$authuser_id) {
		$sql = "SELECT `user_client`.`user_id` FROM `user_client`
			WHERE `user_client`.`user_id`='$user_id' AND `user_client`.`client_id` = '$client_id'";
		$query = $mysqli->query($sql);

		if($query->num_rows!==1) {
			Ufo::abort('dialog');
			exit;
		}
	}
}

if(!empty($_POST['plate']) && !empty($_POST['insurance_id']) && $user_id && $client_id) {
	$plate = preg_replace("/[^A-Z0-9]+/", "", mb_strtoupper($_POST['plate']));
	$insurance_id = $mysqli->real_escape_string($_POST['insurance_id']);
	$note = trim($mysqli->real_escape_string($_POST['note']));
	
	$sql = "INSERT INTO `case` (plate,note,insurance_id,user_id,client_id)
		VALUES ('$plate','$note','$insurance_id',$user_id,$client_id)";
	$mysqli->query($sql);
	$case_id = $mysqli->insert_id;
	
	set_deadline($case_id);

	if(isset($_POST['new_damage_number'])) foreach($_POST['new_damage_number'] as $number) {
		$number = $mysqli->real_escape_string($number);
		$number = clear_case($number);

		$sql = "INSERT INTO `damage` (number,case_id) VALUES ('$number',$case_id)";
		$mysqli->query($sql);
	}
	else {
		$sql = "INSERT INTO `damage` (number,case_id) VALUES ('',$case_id)";
		$mysqli->query($sql);
	}

	$url = __ROOT__.'/mo_case/upload.php?case_id='.$case_id;

	if($_POST['status']=='ass' && SimpleAuth::access('employee')) {
		$responsible_id = SimpleAuth::user_id();
		$sql = "UPDATE `case` SET
				responsible_id = '$responsible_id',
				status='ass'
			WHERE id=$case_id AND status='new'";
		$mysqli->query($sql);

		Ufo::call('upload',$url,__ROOT__.'/mo_case/step.php?case_id='.$case_id.'&status=ass');
	}
	else {
		Ufo::call('upload',$url);
	}
}
else {
	Ufo::update('main');
}
