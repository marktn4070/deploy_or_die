<?php
require_once __DIR__.'/header.inc.php';
if(SimpleAuth::user_id()) {
	header('location:'.__ROOT__.'/');
	exit;
}

BootSome::document(TITLE,'da');

head();
$container = BootSome::$body->container(false);
$col = $container->row()->col('col-sm-10','offset-sm-1','col-md-8','offset-md-2');

$col->el('h1',['class'=>'text-center'])->te(TITLE.' - Login');

$java = "Ufo.post('dialog','".__ROOT__."/login.script.php','loginform');return false;";
$form = $col->form()->at(['id'=>'loginform','onsubmit'=>$java]);

$group = $form->form_horizontal();
$group->label('E-mail','email');
$group->input('email',empty($_GET['email']) ? '' : $_GET['email'])->at(['required','type'=>'email','autocapitalize'=>'none']);

$group = $form->form_horizontal();
$group->label('Adgangskode','password');
$group->password('password')->at(['required']);

if(!empty($_GET['message'])) {
	$form->alert()->te($_GET['message']);
}

$group = $form->form_group(null,true);

$java = "Ufo.get('main','".__ROOT__."/mo_user/reset_password.script.php?email='+document.getElementById('email').value);";

$group->button('Login','unlock')->at(['type'=>'submit']);
