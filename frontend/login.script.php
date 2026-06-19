<?php
require_once __DIR__.'/header.inc.php';

try {
	SimpleAuth::login($_POST['email'],$_POST['password'],true);
	Ufo::call('reload',__ROOT__.'/');
}
catch(Exception $e) {
	Ufo::call('alert',SimpleAuth::error_string($e->getMessage()));
	Ufo::call('dialog_enable','<i class="fas fa-unlock"></i><span>Login</span>');
}
