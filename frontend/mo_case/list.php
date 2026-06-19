<?php
require_once __DIR__.'/../header.inc.php';
require_once __DIR__.'/function.php';
authenticate('basic');
$container = pagestart('Arbejdsordrer','case');

BootSome::$body->at(['onload'=>"Ufo.interval('main',20);Ufo.get('main','".__ROOT__."/mo_case/list_ajax.php');"],true);

$form = $container->form()->form_inline();
$form->button('Opret','plus')->at(['onclick'=>"Ufo.get('dialog','".__ROOT__."/mo_case/new.php');"]);

$filter = FancyFilter::get('main');
$filterupdate = "FancyFilter.set('main','page');Ufo.update('main');";

$onchange = "FancyFilter.set('main','status',this.value);".$filterupdate;
$select = $form->select('client')->at(['onchange'=>$onchange,'tabindex'=>'-1']);
$select->option('Alle','');
$select->option('Aktive','active',$filter->status==='active');
$select->option('Stoppet','stop',$filter->status==='stop');
$select->option('---','')->at(['disabled']);
foreach(case_status() as $key => $status) {
	if($status['internal'] && !SimpleAuth::access('employee')) continue;
	$select->option($status['name'],$key,$filter->status===$key);
}

if(SimpleAuth::access('employee')) {
	$sql = "SELECT `id`,`name` FROM `client` WHERE `archive`=0 ORDER BY `client`.`name`";
	$query = $mysqli->query($sql);
	$onchange = "FancyFilter.set('main','client_id',this.value);".$filterupdate;
	$select = $form->select('client')->at(['onchange'=>$onchange,'tabindex'=>'-1']);
	$select->option('Alle','');
	$select->options($query,$filter->client_id);

	$sql = "SELECT id,name
		FROM user
		INNER JOIN access ON user.id = access.user_id AND `access`.`permission`='employee'
		ORDER BY name";
	$query = $mysqli->query($sql);
	$onchange = "FancyFilter.set('main','responsible_id',this.value);".$filterupdate;
	$select = $form->select('responsible')->at(['onchange'=>$onchange,'tabindex'=>'-1']);
	$select->option('Alle','');
	$select->options($query,$filter->responsible_id);
}

$onchange = "FancyFilter.set('main','search',this.value);".$filterupdate;
$form->input('search',$filter->search)->at(['onchange'=>$onchange,'placeholder'=>'Søg']);


$main = $container->el('div',['id'=>'main']);
$main->spinner();
