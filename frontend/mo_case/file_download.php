<?php
require_once __DIR__.'/../header.inc.php';
authenticate('basic');

if(!empty($_GET['file_id'])) {
	$file_id =  (int) $_GET['file_id'];
	
	if(!SimpleAuth::access('employee')) {
		$user_id = SimpleAuth::user_id();
		$clientjoin = "INNER JOIN `user_client` ON `user_client`.`user_id`='$user_id' AND `user_client`.`client_id` = `case`.`client_id`";
	}
	else {
		$clientjoin = "";
	}
	
	$sql = "SELECT `case_file`.`id`
		FROM `case_file`
		INNER JOIN `case` ON `case_file`.`case_id` = `case`.`id`
		$clientjoin
		WHERE `case_file`.`id`='$file_id'";
	$query = $mysqli->query($sql);
	
	if($rs = $query->fetch_object()) {
		$wf = new WildFile($mysqli,FILE_STORAGE,'case_file');
		$file = $wf->get($_GET['file_id'],['name','size','mime']);
		
		if(!isset($_GET['preview'])) {
			WildFileHeader::type($file->mime);
			WildFileHeader::expires();
			WildFileHeader::size($file->size);
			WildFileHeader::filename($file->name);
			$file->output();
		}
		else {
			$image = new Imagick();
			$image->readImage($file->get_path());
			$image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
			$image->cropThumbnailImage(320,180);
			$image->setImageFormat('jpeg');
			$image->setImageCompressionQuality(80);
			
			WildFileHeader::type('image/jpeg');
			WildFileHeader::expires();
			WildFileHeader::size(strlen((string) $image));
			echo (string) $image;
		}
	}
}
