<?php
declare(strict_types=1);

function response_error(string $string,int $code = 400) : void {
	syslog(LOG_ERR,'WebhookError: '.$string);
	http_response_code($code);
	$error = [
		'code' => $code,
		'message' => $string,
	];
	echo json_encode($error);
	exit;
}

function verify_bearer(string $input) : void {
	$headers = getallheaders();
	if(empty($headers['Authorization'])) {
		response_error('Missing \'Authorization\' header');
	}
	list($name,$token) = explode(' ',$headers['Authorization'],2);
	if($name!=='Bearer') {
		response_error('Missing \'Bearer\' in Authorization');
	}
	if(empty($token)) {
		response_error('Empty token');
	}
	if(strcmp($token, $input)!==0) {
		response_error('Wrong token',403);
	}
}

function get_json_contents() : object {
	$input = file_get_contents('php://input');
	if(empty($input)) {
		response_error('No POST input');
	}
	$json = json_decode($input);
	if(!$json) {
		response_error('JSON decode error');
	}
	return $json;
}
