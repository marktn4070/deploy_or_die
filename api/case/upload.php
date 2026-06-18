<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../lib/wild-file/WildFileChunkedUpload.php';

$auth = verify_bearer();

// Path info: e.g. /uploadFile.php/123
$case_id = null;
if (!empty($_SERVER['PATH_INFO'])) {
	$parts = explode('/', trim($_SERVER['PATH_INFO'], '/'));
	if (isset($parts[0]) && is_numeric($parts[0])) {
		$case_id = (int)$parts[0];
	}
}

// Validate case_id field - exists as a primary key in database
$sql = "SELECT `id` FROM `case` WHERE `id` = '{$case_id}'";
$query = $mysqli->query($sql);

$row = $query->fetch_assoc();
$id = $row['id'] ?? null;

if ((string)$case_id !== $id) {
	$details[] = [
		'field' => 'case_id', 
		'error' => "No case with id `{$case_id}`.",
	];
	response_error('Validation error.', 400, $details);
}

// Case access control
$sql = "SELECT `case`.`id`
		FROM `case`
		INNER JOIN `user_client` ON `user_client`.`client_id` = `case`.`client_id`
		WHERE `case`.`id` = $case_id AND `user_client`.`user_id`='{$auth['user_id']}'";
$query = $mysqli->query($sql);
if ($query->num_rows !== 1) {
	$details[] = [
		'field' => 'case_id', 
		'error' => "No access to this case with id `{$case_id}`.",
	];
	response_error('Validation error.', 403, $details);
}

try {
	$wf = new WildFile($mysqli, FILE_STORAGE, 'case_file');
	$wf->set_callback('store', 'filecheck');

	// Raw JSON body
	$raw = file_get_contents('php://input');

	// Filename
	$name = $_GET['filename'];

	// Filetype
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_buffer($finfo, $raw);
	finfo_close($finfo);

	// Fields
	$fields = [
		'case_id'  => ['value' => $case_id],
		'name'     => ['value' => $name],
		'mime'     => ['value' => $mime],
		'size'     => ['auto'  => WildFile::SIZE],
		'checksum' => ['auto'  => WildFile::CHECKSUM],
	];

	$wf->store_string($raw, $fields);
	$file_id = (int)$mysqli->insert_id;

	// Response message - success
	http_response_code(201);
	echo json_encode([
		'success'	=> true,
		'message'	=> 'File uploaded successfully.',
		'id'		=> $file_id,
		'case_id'	=> $case_id,
		'name'		=> $name,
		'mime'		=> $mime,
	]);
	exit;

} catch (\Throwable $e) {
	// Response message - server error
	response_error('An internal server error occurred.', 500, $e->getMessage());
}

// No file received
response_error('No file uploaded.', 400);
