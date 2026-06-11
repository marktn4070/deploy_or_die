<?php
declare(strict_types=1);
require_once __DIR__.'/config.php';

date_default_timezone_set('Europe/Copenhagen');
// openlog('trp_takseringshjaelp',LOG_NDELAY|LOG_PID,LOG_LOCAL7);
if (defined('LOG_LOCAL7')) {
    openlog('trp_takseringshjaelp', LOG_NDELAY | LOG_PID, LOG_LOCAL7);
}

define('SOAP_SOFTWARE','Taks::Force');

function restcall($url,$data,$apitoken = null,$type = 'json') {
	syslog(LOG_DEBUG,$url);
	$ch = curl_init($url);
	$header = [];
	if($type=='json') {
		$header[] = 'Content-Type:application/json';
	}
	if($apitoken) {
		$header[] = 'Authorization: Bearer '.$apitoken;
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	if($data) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $type=='json' ? json_encode($data) : $data);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	$return = curl_exec($ch);
	
	if(curl_errno($ch)) {
		throw new \Exception(curl_error($ch),500);
	}
	
	$info = curl_getinfo($ch);
	if(!in_array($info['http_code'],[200,201],true)) {
		syslog(LOG_INFO,'curl http_code: '.$info['http_code']);
		if(!empty($return)) {
			syslog(LOG_INFO,'curl content: '.$return);
		}
		throw new \Exception('curl http_code: '.$info['http_code'],$info['http_code']);
	}
	
	curl_close($ch);
	return $return;
}
