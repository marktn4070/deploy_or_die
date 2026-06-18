<?php
require_once __DIR__.'/config.inc.php';

define('TITLE','Taks::Force');

setlocale(LC_TIME, 'da_DK.utf-8');
date_default_timezone_set('Europe/Copenhagen');

require_once __DIR__.'/lib/heal-document/HealDocument.php';
require_once __DIR__.'/lib/boot-some/BootSome.php';
require_once __DIR__.'/lib/simple-auth/SimpleAuth.php';

function authenticate($permission) {
	if($permission==='anonymous') {
		global $mysqli;
		$mysqli = new mysqli(DBHOST,DBUSER,DBPASS,DBBASE);
		$mysqli->set_charset('utf8mb4');
		return;
	}
	if(SimpleAuth::user_id()) {
		if(SimpleAuth::access($permission)) {
			global $mysqli;
			$mysqli = new mysqli(DBHOST,DBUSER,DBPASS,DBBASE);
			$mysqli->set_charset('utf8mb4');
		}
		else {
			if(empty($_GET['ufo'])) {
				echo "Adgang forbudt";
			}
			else {
				Ufo::call('alert',"Adgang forbudt");
			}
			exit;
		}
	}
	else {
		if(empty($_GET['ufo'])) {
			header('location: '.__ROOT__.'/login/');
		}
		else {
			Ufo::call('reload',__ROOT__.'/login/');
		}
	}
}
	