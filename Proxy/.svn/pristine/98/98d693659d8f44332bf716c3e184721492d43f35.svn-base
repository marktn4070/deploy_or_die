<?php
declare(strict_types=1);

require_once __DIR__.'/../../header.php';
require_once __DIR__.'/../../lib/api-help/webhook.php';

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_BASE);
$mysqli->set_charset('utf8mb4');
syslog(LOG_INFO,'Queue');

header('Content-Type: application/json');

verify_bearer(WEBHOOK_BEARER);
$json = get_json_contents();

if(empty($json->tenant ?? $json->customer)) {
	response_error('Missing tenant');
}

$tenant = $mysqli->real_escape_string($json->tenant ?? $json->customer);
$location = intval($json->location_id);
$workorder_id = intval($json->workorder_id);

$sql = "SELECT `company`.`id`,`company`.`tenant`
	FROM `company`
	WHERE `company`.`tenant`='$tenant' AND `company`.`active`=1 LIMIT 1";
$query = $mysqli->query($sql);

if(!$rs = $query->fetch_object()) {
	response_error('Tenant not found: '.$tenant);
}

$data = [
	'tenant' => $tenant,
	'location' => $location,
	'workorder_id' => $workorder_id,
];

$message_queue = msg_get_queue(MSG_QUEUE);
$set = msg_send($message_queue, 10, $data);

$message = $set ? 'Sat i kø' : 'Fejl';
echo json_encode(['message'=> $message]);
