<?php
declare(strict_types=1);

class Post {
	/**
	 * Create a case
	 * @param array $params
	 */
	public static function create_case(array $params)
	{
		require_once __DIR__ . '/../../mo_case/function.php';
		global $mysqli;

		// Escape
		$plate_db      = $mysqli->real_escape_string($params['plate']);
		$note_db       = $mysqli->real_escape_string($params['note']);
		$insurance_db  = $mysqli->real_escape_string($params['insurance_id']);
		$status_db     = $mysqli->real_escape_string($params['status']);

		// Sql
		$sql = "INSERT INTO `case` (plate, note, insurance_id, user_id, client_id, status)
				VALUES ('{$plate_db}', '{$note_db}', '{$insurance_db}', {$params['user_id']}, {$params['client_id']}, '{$status_db}')";

		try {
			$result = $mysqli->query($sql);

			// Check result
			if ($result === false) {
				response_error('An error with sql query occurred.', $mysqli->errno, $mysqli->error);
			}

			$insert_id = (int)$mysqli->insert_id;

			set_deadline($insert_id);

			return $insert_id;
		} catch (\Throwable $e) {
			// Response message - server error
			response_error('An internal server error occurred.', 500, $e->getMessage());
		}
	}
}
