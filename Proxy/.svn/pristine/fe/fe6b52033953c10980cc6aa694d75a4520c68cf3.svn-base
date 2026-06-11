<?php
declare(strict_types=1);

require_once __DIR__.'/../../header.php';
require_once __DIR__.'/../../lib/api-help/webhook.php';

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_BASE);
$mysqli->set_charset('utf8mb4');
syslog(LOG_INFO,'Setting');

header('Content-Type: application/json');

verify_bearer(WEBHOOK_BEARER);
$json = get_json_contents();

if(empty($json->tenant ?? $json->customer) || empty($json->token)) {
	response_error('Missing tenant / token');
}

$tenant = $mysqli->real_escape_string($json->tenant ?? $json->customer);
$token = $mysqli->real_escape_string($json->token);

if($_SERVER['REQUEST_METHOD']==='POST') {
	if($json) {
		if(!empty($json->bearer)) {
			$bearer = $mysqli->real_escape_string($json->bearer);
			$sql = "SELECT `id` FROM `company` WHERE `tenant`='$tenant' AND `token`='$token'";
			$query = $mysqli->query($sql);

			if($query->num_rows===0) {
				$sql = "INSERT INTO `company` (`tenant`,`token`,`bearer`,`active`)
					VALUES ('$tenant','$token','$bearer',1)";
			}
			else {
				$sql = "UPDATE `company` SET `bearer`='$bearer'
					WHERE `tenant`='$tenant' AND `token`='$token'";
			}
			$mysqli->query($sql);
		}
		
		$location = empty($json->location_id) ? 'NULL' : (int) $json->location_id;
		$sql = "UPDATE `company` SET `location`=$location,`active`=1,lastuse=NULL
			WHERE `tenant`='$tenant' AND `token`='$token'";
		$query = $mysqli->query($sql);
		
		echo json_encode('success');
		exit;
	}
	else {
		response_error('JSON decode error');
	}
}
else {
	$sql = "SELECT `active`,`lastuse` FROM `company` WHERE `tenant`='$tenant' AND `token`='$token'";
	$query = $mysqli->query($sql);

	if(!$rs = $query->fetch_object()) {
		$rs = (object) ['bearer'=>null,'active'=>0,'lastuse'=>null];
		$status = 'Ny opsætning';
	}
	else {
		$status = $rs->active ? 'Aktiv' : 'Stoppet';
	}

	$fields = [];
	$fields[] = ['type'=>'header','name'=>SOAP_SOFTWARE];
	$fields[] = ['type'=>'readonly','name'=>'Status','value'=>$status];
	$date = ($rs->lastuse!==null) ? (new DateTime($rs->lastuse))->format('Y-m-d H:i') : 'Aldrig';
	$fields[] = ['type'=>'readonly','name'=>'Sidste kørsel','value'=>$date];
	$fields[] = ['type'=>'string','key'=>'bearer','name'=>'Indsæt AuthBearerToken'];
	
	if($rs->active) {
		$fields[] = ['type'=>'header','name'=>'Konfiguration'];
		$fields[] = ['type'=>'readonly','name'=>'API funktion','value'=>'takseringshj'];
		$fields[] = ['type'=>'readonly','name'=>'API retur handling','value'=>'takseringshj-ok'];
	}
	
	echo json_encode($fields);
}
