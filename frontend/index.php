<?php
require_once __DIR__.'/header.inc.php';
authenticate('basic');

header('location:'.__ROOT__.'/case/');
exit;

$container = pagestart('Startside');
