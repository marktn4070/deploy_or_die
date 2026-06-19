<?php
require_once __DIR__.'/../header.inc.php';
authenticate('employee');

if(!empty($_POST['batch'])) {
	foreach(array_keys($_POST['batch']) as $key) {
		$case_id = (int) $key;
		$sql = "SELECT `client`.`batch`,`client`.`fixed`
			FROM `case`
			INNER JOIN `client` ON `client`.`id` = `case`.`client_id`
			WHERE `case`.`id`='$case_id' AND `case`.`status`='snt' LIMIT 1";
		$query = $mysqli->query($sql);
		if($rs = $query->fetch_object()) {
			if($rs->batch) {
				$sql = "UPDATE `damage` SET
						body='0',
						paint='0',
						sparepart='0',
						void='0',
						fixed='$rs->fixed'
					WHERE case_id='$case_id'";
				$mysqli->query($sql);
				
				$done_id = SimpleAuth::user_id();
				$sql = "UPDATE `case` SET
						`status`='dne',
						`done_id` = '$done_id',
						`invoice` = NOW()
					WHERE id='$case_id' AND status='snt'";
				$mysqli->query($sql);
			}
		}
	}
}

Ufo::abort('dailog');
Ufo::update('main');
