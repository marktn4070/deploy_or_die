<?php
require_once __DIR__.'/../header.inc.php';
require_once __DIR__.'/function.php';
authenticate('employee');

$user_id = SimpleAuth::user_id();
$case_id = $mysqli->real_escape_string($_POST['case_id']);
$status = $mysqli->real_escape_string($_POST['status']);
$sql = "SELECT id FROM `case` WHERE `case`.`id`=$case_id AND `case`.status='$status'";
$query = $mysqli->query($sql);
if(!$rs = $query->fetch_object()) {
	Ufo::abort('dialog');
	Ufo::update('main');
	exit;
}

$saving = (isset($_POST['saving']) && $_POST['saving']=='yes') ? true : false;

if(!empty($_POST['comment'])) {
	$comment = $mysqli->real_escape_string($_POST['comment']);
	$sql = "INSERT INTO `case_comment` (`case_id`,`create_by`,`content`)
		VALUES ('$case_id','$user_id','$comment');";
	$mysqli->query($sql);
}

if(!empty($_FILES['file'])) {
	$wf = new WildFile($mysqli,FILE_STORAGE,'case_file');
	$wf->set_callback('store','filecheck');

	$fields = [];
	$fields['case_id'] = ['value'=>$_POST['case_id']];
	$fields['name'] = ['auto'=>WildFile::NAME];
	$fields['size'] = ['auto'=>WildFile::SIZE];
	$fields['mime'] = ['auto'=>WildFile::MIME];
	$fields['checksum'] = ['auto'=>WildFile::CHECKSUM];
	$wf->store_post($_FILES['file'],$fields);
}

$exception = [];
foreach(case_status() as $key => $value) {
	if($value['exception']) $exception[] = $key;
}

if($status=='new') {
	if(!$saving && !empty($_POST['responsible_id'])) {
		$responsible_id = $mysqli->real_escape_string($_POST['responsible_id']);
		$sql = "UPDATE `case` SET
				responsible_id = '$responsible_id',
				status='ass'
			WHERE id=$rs->id AND status='new'";
		$mysqli->query($sql);
		Ufo::get('dialog',__ROOT__.'/mo_case/step.php?case_id='.$case_id.'&status=ass');
	}
	else {
		Ufo::abort('dialog');
	}
	
	Ufo::update('main');
	exit;
}
elseif($status=='ass') {
	$ids = [];
	$wf = new WildFile($mysqli,FILE_STORAGE,'damage','damage_report_init');
	$wf_fields['report_init_checksum'] = ['auto'=>WildFile::CHECKSUM];
	
	if(isset($_POST['damage_id'])) foreach($_POST['damage_id'] as $damage_id) {
		$number = $mysqli->real_escape_string($_POST['damage_number'][$damage_id]);
		$number = clear_case($number);
		
		$damage_id = (int) $damage_id;
		
		$sql = "UPDATE `damage` SET number='$number'
			WHERE id=$damage_id AND case_id=$case_id";
		$mysqli->query($sql);
		$ids[] = $damage_id;
		
		if(!empty($_FILES['damage_report_init']['tmp_name'][$damage_id])) {
			$report_init = file_get_contents($_FILES['damage_report_init']['tmp_name'][$damage_id]);
			$wf->replace_string($damage_id,$report_init,$wf_fields);
		}
	}
	
	if(isset($_POST['new_damage_number'])) foreach($_POST['new_damage_number'] as $key => $number) {
		$number = $mysqli->real_escape_string($number);
		$number = clear_case($number);
		
		$sql = "INSERT INTO `damage` (number,case_id) VALUES ('$number',$case_id)";
		$mysqli->query($sql);
		
		$damage_id = $mysqli->insert_id;
		$ids[] = $mysqli->insert_id;
		
		if(!empty($_FILES['damage_report_init_new']['tmp_name'][$key])) {
			$report_init = file_get_contents($_FILES['damage_report_init_new']['tmp_name'][$key]);
			$wf->replace_string($damage_id,$report_init,$wf_fields);
		}
	}
	
	if($ids) {
		$ids = $ids ? 'id NOT IN ('.implode(',',$ids).')' : 'TRUE';
		$sql = "DELETE FROM `damage` WHERE case_id=$case_id AND $ids AND `report_init_checksum` IS NULL AND `report_sent_checksum` IS NULL";
		$mysqli->query($sql);
		
		if(!$saving) {
			$sql = "UPDATE `case` SET
					status='ong'
				WHERE id=$rs->id AND status='ass'";
			$mysqli->query($sql);
			Ufo::get('dialog',__ROOT__.'/mo_case/step.php?case_id='.$case_id.'&status=ong');
		}
		else {
			Ufo::abort('dialog');
		}
	}
	else {
		$sql = "DELETE FROM `damage` WHERE case_id=$case_id AND `report_init_checksum` IS NULL AND `report_sent_checksum` IS NULL";
		$mysqli->query($sql);
		
		if(!$saving) {
			Ufo::call('alert','Ingen skader tilføjet!');
			Ufo::get('dialog',__ROOT__.'/mo_case/step.php?case_id='.$case_id.'&status=ass');
		}
		else {
			Ufo::abort('dialog');
		}
	}
	
	Ufo::update('main');
	exit;
}
elseif($status=='ong') {
	if(!$saving) {
		if($_POST['exception']!=='stop') {
			$sql = "UPDATE `case` SET status='snt' WHERE id=$rs->id AND status='ong'";
			$mysqli->query($sql);
		}
	}
	
	$wf = new WildFile($mysqli,FILE_STORAGE,'damage','damage_report_sent');
	$wf_fields['report_sent_checksum'] = ['auto'=>WildFile::CHECKSUM];
	
	foreach($_POST['damage_id'] as $damage_id) {
		$damage_id = (int) $damage_id;
		$notice = $mysqli->real_escape_string(trim($_POST['damage_notice'][$damage_id]));
		
		$sql = "UPDATE `damage` SET notice='$notice'
			WHERE id=$damage_id AND case_id=$case_id";
		$mysqli->query($sql);
		
		if(!empty($_FILES['damage_report_sent']['tmp_name'][$damage_id])) {
			$report_sent = file_get_contents($_FILES['damage_report_sent']['tmp_name'][$damage_id]);
			$wf->replace_string($damage_id,$report_sent,$wf_fields);
		}
	}
}
elseif(in_array($status,['snt','ctl'])) {
	if(!$saving) {
		$new_status = empty($_POST['exception']) ? 'dne' : 'ctl';
		$sql = "UPDATE `case` SET
				`status`='$new_status',
				`done_id` = '$user_id',
				`invoice` = NOW()
			WHERE id=$rs->id AND status IN ('snt','ctl')";
		$mysqli->query($sql);
	}
	
	if(isset($_POST['damage_id'])) foreach($_POST['damage_id'] as $damage_id) {
		$damage_id = $mysqli->real_escape_string($damage_id);
		$notice = $mysqli->real_escape_string(trim($_POST['damage_notice'][$damage_id]));
		
		$void = !empty($_POST['void'][$damage_id]) ? 1 : 0;
		if(!$void) {
			$fixed = round(convertdouble($_POST['fixed'][$damage_id]),2);
			$body = round(convertdouble($_POST['body'][$damage_id]),2);
			$paint = round(convertdouble($_POST['paint'][$damage_id]),2);
			$sparepart = round(convertdouble($_POST['sparepart'][$damage_id]),2);
		}
		else {
			$fixed = 0;
			$body = 0;
			$paint = 0;
			$sparepart = 0;
		}
		
		$sql = "UPDATE `damage` SET
				notice='$notice',
				body='$body',
				paint='$paint',
				sparepart='$sparepart',
				void='$void',
				fixed='$fixed'
			WHERE id=$damage_id AND case_id=$case_id";
		$mysqli->query($sql);
	}
}
elseif(in_array($status,$exception)) {
	if(!$saving) {
		$sql = "UPDATE `case` SET status='ong' WHERE id=$rs->id";
		$mysqli->query($sql);
	
		set_deadline($case_id);
		Ufo::get('dialog',__ROOT__.'/mo_case/step.php?case_id='.$case_id.'&status=ong');
	}
	else {
		Ufo::abort('dialog');
	}
	Ufo::update('main');
	exit;
}

if($status=='ong' && $_POST['exception']==='stop') {
	Ufo::get('dialog',__ROOT__.'/mo_case/stop.php?case_id='.$case_id);
}
else {
	Ufo::abort('dialog');
}
Ufo::update('main');
