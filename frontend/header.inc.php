<?php
require_once __DIR__.'/config.inc.php';

define('TITLE','Deploy or die');

setlocale(LC_TIME, 'da_DK.utf-8');
date_default_timezone_set('Europe/Copenhagen');

require_once __DIR__.'/lib/heal-document/HealDocument.php';
require_once __DIR__.'/lib/boot-some/BootSome.php';
require_once __DIR__.'/lib/simple-auth/SimpleAuth.php';
require_once __DIR__.'/lib/ufo-ajax/ufo.php';
require_once __DIR__.'/lib/fancy-filter/FancyFilter.php';
require_once __DIR__.'/lib/wild-file/WildFile.php';

$onlogin = function() {	
	SimpleAuth::add_access('basic');
	authenticate('basic');
	
	global $mysqli;
	$user_id = SimpleAuth::user_id();
	$sql = "SELECT `darktheme` FROM `user` WHERE `id`='$user_id' LIMIT 1";
	$rs = $mysqli->query($sql)->fetch_object();
	$_SESSION['darktheme'] = $rs->darktheme;
};

SimpleAuth::configure([
	'db_host' => DBHOST,
	'db_user' => DBUSER,
	'db_pass' => DBPASS,
	'db_base' => DBBASE,
	'db_pfix' => '',
	'cookie_path' => __ROOT__.'/',
	'onlogin' => $onlogin,
]);

FancyFilter::set_option('path',__ROOT__.'/');

BootSomeFormsFloating::$required_label = '*';

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
		exit;
	}
}

function mb_ucwords($str) {
	return mb_convert_case(mb_strtolower($str,'UTF-8'),MB_CASE_TITLE,'UTF-8');
}

function convertdouble($input) {
	$input = str_replace(" ","",$input);
	$input = str_replace(chr(194),"",$input);
	$input = str_replace(chr(160),"",$input);
		
	if(strpos($input,",")!==false and strpos($input,".")!==false) {
		$input = str_replace(".","",$input);
	}
	
	$input = str_replace(",",".",$input);
	return str_replace(",",".",floatval($input));
}

function money($amount) {
	return number_format($amount ?? 0,2,',','.').' kr.';
}

function format_case($str) {
	if (!function_exists('string_insert')){
		function string_insert($str,$insertstr,$pos) {
			$str = substr($str,0,$pos).$insertstr.substr($str,$pos);
			return $str;
		}
	}
	
	if($str) {
		$str = string_insert($str,' ',9);
		$str = string_insert($str,' ',7);
		$str = string_insert($str,' ',2);
	}
	
	return $str;
}

function clear_case($number) {
	$number = str_replace(' ','',$number);
	return substr($number,0,13);
}

require __DIR__.'/lib/phpmailer/Exception.php';
require __DIR__.'/lib/phpmailer/PHPMailer.php';
require __DIR__.'/lib/phpmailer/SMTP.php';

class CustomMail extends \PHPMailer\PHPMailer\PHPMailer	{
	function __construct(){
		parent::__construct();

		config_email($this);
		$this->CharSet = 'utf-8';

		$this->isHTML(true);
		$this->Body = new TRP\HealDocument\HealDocument();
	}
	
	public function template() {
		$main = $this->Body->el('div');
		$main->at(['style'=>'font-family:"Helvetica Neue", Arial, sans-serif;']);

		$header = $main->el('div');
		$header->at(['style'=>'padding:0px 0px 10px 0px;border-bottom:3px solid #30529C;']);
		$svg = file_get_contents(__DIR__.'/logo.svg');
		$svg = substr($svg,strpos($svg,"\n")+1);
		$header->el('div',['style'=>'height:80px'])->fr($svg);
	
		$content = $main->el('div');
		$content->at(['style'=>'padding:10px 0px 10px 0px;']);
		$content->el('h1',['style'=>'margin:0px 0px 10px 0px;font-size:1.5em'])->te(TITLE.' - '.$this->Subject);
	
		return $content;
	}
	
	function send(){
		if(is_object($this->Body) && get_class($this->Body)==='HealDocument') {
			$this->Body = (string) $this->Body;
		}

		if(parent::send()===false) {
			if(isset($_GET['ufo'])) {
				Ufo::log($this->ErrorInfo);
			}
			else {
				echo $this->ErrorInfo."<br>";
			}
		}
	}
}

require_once __DIR__.'/design.inc.php';
