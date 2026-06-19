<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/Post.php';

$auth = verify_bearer();

// Raw JSON body
$raw = file_get_contents('php://input');

// Check if body consists of JSON
$json = json_decode($raw, true);
if (is_array($json)) {
	$input = $json;
} else {
	response_error('No valid body found. Send data as JSON in body.', 400);
}

// Parameters
$params = [
	'plate'        => isset($input['plate']) ? mb_strtoupper($input['plate']) : '',
	'note'         => isset($input['note']) ? $input['note'] : '',
	'insurance_id' => isset($input['insurance_id']) ? $input['insurance_id'] : '',
	'user_id'      => (int)$auth['user_id'],
	'client_id'    => (int)$auth['client_id'],
	'status'       => 'new',
];

$details = [];

// Declare fields and input length limit
$fields = [
	'plate'        => 7,
	'insurance_id' => 2,
	'note'         => 65535,
];

// Validate fields
foreach ($fields as $f => $limit) {
	// Validate required fields - missing
	if (empty($params[$f]) && $f !== 'note') {
		$details[] = [
			'field' => $f, 
			'error' => "Missing.",
		];
		continue;
	} 

	// Validate fields - too many characters
	$length = mb_strlen($params[$f]);
	if ($f === 'insurance_id') {
		// Check 'insurance_id' field characters is exactly 2
		if ($length !== $limit) {
			$details[] = [
				'field' => $f,
				'error' => "Must be exactly {$limit} characters.",
			];
			continue;
		}
	} else {
		// Check 'plate'- or 'note' field characters is more than the limit
		if ($length > $limit) {
			$details[] = [
				'field' => $f,
				'error' => "Exceeded limit of {$limit} characters.",
			];
		}
	}

	// Validate insurance_id field - exists as a foreign key in database
	if ($f === 'plate' || $f === 'note') continue;

	$sql = "SELECT `id` FROM `insurance` WHERE `id` = '{$params['insurance_id']}'";
	$query = $mysqli->query($sql);

	$row = $query->fetch_assoc();
	$id = $row['id'] ?? null;

	if ((string)$params['insurance_id'] !== $id) {
		$details[] = [
			'field' => 'insurance_id', 
			'error' => "No insurance with id `{$params['insurance_id']}`.",
		];
	}
}

// Response message - validation client error
if (count($details) > 0) {
	response_error('Validation error.', 400, $details);
}

// SQL
$case_id = Post::create_case($params);

// Response message - success
http_response_code(201);
echo json_encode([
	'success'		=> true,
	'message'		=> 'Case created successfully.',
	'id'			=> $case_id,
	'plate'			=> $params['plate'],
	'note'			=> $params['note'],
	'insurance_id'	=> $params['insurance_id'],
	'user_id'		=> $params['user_id'],
	'client_id'		=> $params['client_id'],
]);
exit;
