<?php
function case_status() {
	return [
		'new' => ['active'=>true,'internal'=>true,'exception'=>false,'name' => 'Ny','color' => 'warning','icon' => 'folder-plus'],
		'ass' => ['active'=>true,'internal'=>true,'exception'=>false,'name' => 'Tildelt','color' => 'info','icon' => 'user-check'],
		'ong' => ['active'=>true,'internal'=>true,'exception'=>false,'name' => 'Påbegyndt','color' => 'primary','icon' => 'users-cog'],
		'pri' => ['active'=>true,'internal'=>true,'exception'=>true,'name' => 'Afventer priser','color' => 'danger','icon' => 'money-bill-wave'],
		'pho' => ['active'=>true,'internal'=>true,'exception'=>true,'name' => 'Mangler yderligere billeder','color' => 'danger','icon' => 'camera-retro'],
		'rev' => ['active'=>true,'internal'=>true,'exception'=>true,'name' => 'Mangler gennemgang med kunde','color' => 'danger','icon' => 'person-military-pointing'],
		'mis' => ['active'=>true,'internal'=>true,'exception'=>true,'name' => 'Mangelfuld information','color' => 'danger','icon' => 'hand-paper'],
		'apr' => ['active'=>true,'internal'=>true,'exception'=>true,'name' => 'Afventer godkendelse','color' => 'danger','icon' => 'list-check'],
		'pst' => ['active'=>true,'internal'=>true,'exception'=>true,'name' => 'Udsættelse','color' => 'danger','icon' => 'hourglass-half'],
		'snt' => ['active'=>false,'internal'=>false,'exception'=>false,'name' => 'Afsendt','color' => 'success','icon' => 'mail-bulk'],
		'ctl' => ['active'=>false,'internal'=>false,'exception'=>false,'name' => 'Kontrol','color' => 'secondary','icon' => 'magnifying-glass'],
		'dne' => ['active'=>false,'internal'=>false,'exception'=>false,'name' => 'Registreret','color' => 'secondary','icon' => 'check'],
	];
}

function damage_state() {
	return [
		'unknown' => ['name' => 'unknown','color' => 'danger','icon' => 'question'],
		'advise' => ['name' => 'Advisering','color' => 'dark','icon' => 'triangle-exclamation'],
		'taken_over' => ['name' => 'Overtaget','color' => 'dark','icon' => 'right-left'],
		'rejected' => ['name' => 'Afvist','color' => 'danger','icon' => 'thumbs-down'],
		'approved' => ['name' => 'Godkendt','color' => 'success','icon' => 'thumbs-up'],
		'approved_with_changes' => ['name' => 'Godkendt med ændringer','color' => 'success','icon' => 'pen'],
		'approved_with_changes_accept_requested' => ['name' => 'Godkendt med ændringer','color' => 'success','icon' => 'pen'],
		'approved_with_changes_and_accepted' => ['name' => 'Godkendt med ændringer','color' => 'success','icon' => 'pen'],
		'approved_with_changes_and_rejected' => ['name' => 'Godkendt med ændringer','color' => 'success','icon' => 'pen'],
		'approved_with_reservation' => ['name' => 'Godkendt med forbehold','color' => 'success','icon' => 'triangle-exclamation'],
		'approved_with_reservation_accept_requested' => ['name' => 'Godkendt med forbehold','color' => 'success','icon' => 'triangle-exclamation'],
		'approved_with_reservation_and_accepted' => ['name' => 'Godkendt med forbehold','success' => 'warning','icon' => 'triangle-exclamation'],
		'approved_with_reservation_and_rejected' => ['name' => 'Godkendt med forbehold','success' => 'warning','icon' => 'triangle-exclamation'],
		'preliminary' => ['name' => 'preliminary','color' => 'danger','icon' => 'question'],
		'backup' => ['name' => 'preliminary','color' => 'danger','icon' => 'question'],
		'total_loss' => ['name' => 'Totalskade','color' => 'warning','icon' => 'triangle-exclamation'],
		'canceled' => ['name' => 'Annulleret','color' => 'danger','icon' => 'triangle-exclamation'],
		'sold_for_rebuild' => ['name' => 'Solgt til genopbygning','color' => 'warning','icon' => 'gavel'],
		'not_found' => ['name' => 'Ikke fundet','color' => 'danger','icon' => 'plug-circle-exclamation'],
	];
}

function set_deadline($case_id) {
	global $mysqli;
	
	$case_id = (int) $case_id;
	$sql = "SELECT `client`.`processing` FROM `case`
		INNER JOIN `client` ON `case`.`client_id` = `client`.`id`
		WHERE `case`.`id`= $case_id";
	$processing = (int) $mysqli->query($sql)->fetch_object()->processing;
	
	$now = new DateTime();
	$interval = new DateInterval('PT'.$processing.'M');
	$deadline = add_rollover($now, $interval,'8:00','16:00');
	$deadline = $deadline->format('Y-m-d H:i:s');
	$sql = "UPDATE `case` SET `deadline` = '$deadline' WHERE `case`.`id`= $case_id";
	$mysqli->query($sql);
}

function add_rollover($given, $interval, $time_start, $time_end, $exclude = [0,6]) {
	$limit = clone $given;
	$limit->setTime(...explode(':', $time_start));
	if($limit->getTimestamp() > $given->getTimestamp()) {
		$given->setTime(...explode(':', $time_start));
	}
	$limit->setTime(...explode(':', $time_end));
	if($limit->getTimestamp() < $given->getTimestamp()) {
		$given->setTime(...explode(':', $time_end));
	}
	$end = clone $given;
	$end->setTime(...explode(':', $time_end));
	$given->add($interval);
	if($given > $end) {
		$seconds = $given->getTimestamp() - $end->getTimestamp();
		while(true) {
			$end->add(new DateInterval('PT24H'));
			$next = $end->setTime(...explode(':', $time_start));
			if(in_array($next->format('w'), $exclude)) {
				continue;
			}
			else {
				$tmp = clone $next;
				$tmp->setTime(...explode(':', $time_end));
				$next->add(new DateInterval('PT'.$seconds.'S'));
				if($next > $tmp) {
					$seconds = $next->getTimestamp()-$tmp->getTimestamp();
					$end = clone $tmp;
					$end->setTime(...explode(':', $time_start));
				}
				else {
					return $end;
				}
			}
		}
	}
	return $given;
}

function filecheck(string $tmp_name) : void {
	if(!in_array(mime_content_type($tmp_name),['image/jpeg','application/pdf'],true)) {
		throw new \Exception('Wrong format! - Must be image/jpeg,application/pdf');
	}
}
