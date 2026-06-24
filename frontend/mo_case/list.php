<?php
require_once __DIR__.'/../header.inc.php';
require_once __DIR__.'/function.php';
authenticate('basic');
$container = pagestart('Arbejdsordrer','case');

BootSome::$body->at(['onload'=>"Ufo.interval('main',20);Ufo.get('main','".__ROOT__."/mo_case/list_ajax.php');"],true);

$form = $container->form()->form_inline();
$form->button('Opret','plus')->at(['onclick'=>"Ufo.get('dialog','".__ROOT__."/mo_case/new.php');"]);

$main = $container->el('div',['id'=>'main']);
$main->spinner();
