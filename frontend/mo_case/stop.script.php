<?php
require_once __DIR__.'/../header.inc.php';
authenticate('employee');

if(!empty($_POST['case_id']) && !empty($_POST['status'])) {
	$case_id = (int) $_POST['case_id'];
	$status = $mysqli->real_escape_string($_POST['status']);
	$sql = "UPDATE `case` SET status='$status' WHERE `id`='$case_id'";
	$mysqli->query($sql);
}

Ufo::abort('dialog');
Ufo::update('main');
