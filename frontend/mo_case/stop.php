<?php
require_once __DIR__.'/../header.inc.php';
require_once __DIR__.'/function.php';
authenticate('employee');

$case_id = $mysqli->real_escape_string($_GET['case_id']);
$sql = "SELECT `id`
	FROM `case` WHERE `id`=$case_id";
$query = $mysqli->query($sql);
if(!$rs = $query->fetch_object()) {
	Ufo::abort('dialog');
	exit;
}

$doc = new BootSome();

$java = "Ufo.post('dialog','".__ROOT__."/mo_case/stop.script.php','dialogform');return false;";
$form = $doc->form()->at(['id'=>'dialogform','onsubmit'=>$java]);
$form->hidden('case_id',$case_id);

$modal = $form->modal();

$header = $modal->header();
$header->title('Stop');
$header->close()->at(['onclick'=>"Ufo.abort('dialog')"]);

$mbody = $modal->body();

$group = $mbody->form_horizontal();
$group->label('Status','status');
$select = $group->select('status')->at(['required']);
$select->option('');
foreach(case_status() as $key => $status) {
	if(!$status['exception']) continue;
	$select->option($status['name'],$key);
}

$footer = $modal->footer();
$footer->button('Gem','pen','warning')->at(['type'=>'submit']);

Ufo::output('dialog',$doc);
