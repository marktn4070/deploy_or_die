<?php
require_once __DIR__.'/../header.inc.php';
require_once __DIR__.'/../forsi.inc.php';
authenticate('employee');

$case_id = $mysqli->real_escape_string($_GET['case_id']);
$sql = "SELECT `case`.plate,`case`.note,`case`.insurance_id,`case`.`create`,
		client.id as client_id,client.refresh_token
	FROM `case`
	INNER JOIN client ON client.id = `case`.client_id
	WHERE `case`.`id`=$case_id AND `case`.status='ass' AND client.refresh_token!=''";
$query = $mysqli->query($sql);
if(!$rs = $query->fetch_object()) {
	Ufo::abort('dialog');
	exit;
}

$company = new Company($rs->client_id,$rs->refresh_token);

try {
	$incidentDate = (new DateTime($rs->create))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
	
	$data = [];
	$data['vehicle'] = [
		'licensePlate' => $rs->plate,
	];
	$data['damages'] = [
		'companyCode' => $rs->insurance_id,
		'incidentDate' => $incidentDate,
		'createdByName' => 'Deploy or die',
	];
	$data['type'] = 'AUTO';
	$result = forsicall($company,'/workshop/draft',$data);
	$result = json_decode($result);
	
	if(empty($result->id)) {
		Ufo::call('alert',$result);
		exit;
	}
	$draft_id = $result->id;
	$rp = $result->draftKey;
	
	if(!empty($rp)) {
		$number = clear_case($rp);
		$sql = "INSERT INTO `damage` (number,case_id) VALUES ('$number',$case_id)";
		$mysqli->query($sql);
	}
	
	$wf = new WildFile($mysqli,FILE_STORAGE,'case_file');
	
	$sql = "SELECT `id`,`name` FROM `case_file` WHERE `case_id`='$case_id' ORDER BY `id`";
	$query_file = $mysqli->query($sql);
	while($rs_file = $query_file->fetch_object()) {
		$file = new \CURLFile($wf->get($rs_file->id)->get_path(),'image/jpeg',$rs_file->name);

		$result = forsicall($company,'/workshop/draft/'.$draft_id.'/attachments',['file' => $file],'multipart');
		$result = json_decode($result);
		
		if(empty($result->id)) {
			Ufo::call('alert',$result);
			exit;
		}
		unlink($tmpname);
	}
	
	Ufo::call('alert',$rp.': Oprettet'.PHP_EOL.'Husk at slette skadedato!');
	Ufo::get('dialog',__ROOT__.'/mo_case/step.php?case_id='.$case_id.'&status=ass');
}
catch(Exception $e) {
	Ufo::call('alert',$e->getMessage());
}
