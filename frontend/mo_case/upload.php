<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/wild-file/WildFileChunkedUpload.php';

$upload = WildFileChunkedUpload::from_input();

if($upload->complete()) {
	require_once __DIR__.'/../header.inc.php';
	authenticate('basic');
	
	if(!SimpleAuth::access('employee')) {
		$user_id = (int) SimpleAuth::user_id();
		$case_id = (int) $_GET['case_id'];
		
		$sql = "SELECT `case`.`id`
			FROM `case`
			INNER JOIN `user_client` ON `user_client`.`client_id` = `case`.`client_id`
			WHERE `case`.`id` = $case_id AND `user_client`.`user_id`='$user_id'";
		$query = $mysqli->query($sql);
		
		if($query->num_rows!==1) {
			exit;
		}
	}

	$wf = new WildFile($mysqli,FILE_STORAGE,'case_file');
	$wf->set_callback('store','filecheck');

	$fields = [];
	$fields['case_id'] = ['value'=>$_GET['case_id']];
	$fields['name'] = ['value'=>$upload->name];
	$fields['size'] = ['value'=>$upload->file_size];
	$fields['mime'] = ['value'=>$upload->mime];
	$fields['checksum'] = ['value'=>$upload->checksum];

	$file_id = $wf->store_file($upload->file_uri, $fields);
}
else {
	$file_id = null;
}

$result = $upload->to_output($file_id);
echo json_encode($result);
