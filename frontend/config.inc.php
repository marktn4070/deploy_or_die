<?php
//error_reporting(E_ALL | E_STRICT);

define('__ROOT__','https://mk.trp.solutions/-_Mercantec/deploy_or_die/frontend');

define('DBHOST','127.0.0.1');
define('DBUSER','pmadmin');
define('DBPASS','1234');
define('DBBASE','takseringshjaelp');

define('FILE_STORAGE',__DIR__.'/storage');
define('CACHE','1');

define('API_KEY','hello');

function config_email($mail) {
	$mail->isSMTP();
	$mail->Host = 'smtp.mailserver.dk';
	//$mail->SMTPAuth = true;
	//$mail->Username = 'user';
	//$mail->Password = 'pass';
	$mail->Port = 25;
	$mail->setFrom('mail@takseringshjaelp.dk', TITLE);
}
