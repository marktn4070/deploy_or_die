#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__.'/lib/service-wraith/msg.php';
require_once __DIR__.'/lib/api-help/company.php';
require_once __DIR__.'/header.php';
syslog(LOG_INFO,'Started');

$mainloop = function($message,$received_message_type) {
	process_workorder($message['tenant'],$message['location'],$message['workorder_id']);
};

ServiceWraith::msg($mainloop,MSG_QUEUE);
ServiceWraith::run(__DIR__);

function process_workorder(string $tenant,int $location,int $workorder_id) : void {
	syslog(LOG_NOTICE,'Process: '.$tenant.'|'.$workorder_id);
	
	global $mysqli;
	$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_BASE);
	$mysqli->set_charset('utf8mb4');
	
	$sql = "SELECT `id`,`tenant`,`token`,`bearer`
		FROM `company`
		WHERE `tenant`='$tenant' AND (`location`=$location OR `location` IS NULL) AND `active`=1
		LIMIT 1";
	$query = $mysqli->query($sql);
	
	if(!$rs = $query->fetch_object()) {
		syslog(LOG_ERR,'Tenant not found: '.$tenant);
	}
	else {
		$company = new Company($rs,['id'=>'int','bearer'=>'string']);
		try {
			transfer($company,$workorder_id);
		}
		catch(\Exception $e) {
			syslog(LOG_ERR,'Error: '.$e->getMessage());
		}
	}
	
	$mysqli->close();
}

function transfer(object $company,int $workorder_id) : void {
	$param = ['workorder_id' => $workorder_id,'workorder'=>['files' => true]];
	$workorder = $company->soap('workorderGet',$param);
	
	syslog(LOG_NOTICE,'Create: '.$workorder->plate.' ['.$workorder->jobprovider_key.']');
	
	$request = [
		'plate' => $workorder->plate,
		'note' => 'ProcessManager: '.$workorder->brand,
		'insurance_id' => $workorder->jobprovider_key,
		'user_id' => 5, // REMOVE LATER
		'client_id' => 11 // REMOVE LATER
	];
	
	$return = restcall(API_URL.'/case/insert.php',$request,$company->bearer);
	$return = json_decode($return);
	
	$case_id = $return->id;
	if($case_id) {
		syslog(LOG_INFO,'Returned: case_id: '.$case_id);
		
		foreach($workorder->files as $file) {
			$response = $company->soap('workorderFileGet',['file_id' => $file->file_id]);
			if($response->mimetype!=='image/jpeg') continue;
			
			$url = API_URL.'/case/upload.php/'.$case_id.'?filename='.urlencode($response->name);
			restcall($url,$response->data,$company->bearer,'file');
			syslog(LOG_NOTICE,'File: '.$response->name);
		}
	}
	
	$company->soap('workorderAction',['workorder_id'=>$workorder_id,'action_key'=>'takseringshj-ok']);
}
