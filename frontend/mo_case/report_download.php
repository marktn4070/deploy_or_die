<?php
require_once __DIR__.'/../header.inc.php';
authenticate('employee');

if(!empty($_GET['damage_id'])) {
	if(isset($_GET['report_init'])) {
		$type = 'damage_report_init';
		$name = "Før-";
	}
	elseif(isset($_GET['report_sent'])) {
		$type = 'damage_report_sent';
		$name = "Afsendt-";
	}
	else {
		exit;
	}
	
	$wf = new WildFile($mysqli,FILE_STORAGE,'damage',$type);
	$file = $wf->get($_GET['damage_id'],['number']);
	
	WildFileHeader::expires();
	WildFileHeader::type('application/pdf');
	WildFileHeader::filename($name.$file->number.'.pdf');
	$file->output();
}
