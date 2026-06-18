<?php
declare(strict_types=1);

// Headers
header('Content-Type: application/json; charset=utf-8');

function verify_bearer() {
	$headers = getallheaders();

	if (empty($headers['Authorization'])) {	
		response_error('Missing \'Authorization\' header.');
	}
	list($name, $token) = explode(' ', $headers['Authorization'], 2);
	if ($name !== 'Bearer') {
		response_error('Missing \'Bearer\' in Authorization.');
	}
	if (empty($token)) {
		response_error('Empty token.');
	}

	require_once __DIR__ . '/../../header.inc.php';
	authenticate('anonymous');
	global $mysqli;

	$sql = "SELECT `user_id`,`client_id` FROM `user_client` WHERE `bearer` COLLATE utf8mb4_bin = '$token'";
	$query = $mysqli->query($sql);
	if (!$query || $query->num_rows !== 1) {
		response_error('Wrong token.', 403);
	}

	$row = $query->fetch_assoc();
	return [
		'user_id'   => (int)$row['user_id'],
		'client_id' => (int)$row['client_id'],
	];
}

function response_error(string $message, int $httpCode = 401, $details = null): void {
	http_response_code($httpCode);

	$response = [
		'success' => false,
		'message' => $message,
	];

	if ($details !== null) {
		$response['details'] = $details;
	}

	echo json_encode($response);
	exit;
}
