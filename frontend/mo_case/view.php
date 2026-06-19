<?php
require_once __DIR__.'/../header.inc.php';
require_once __DIR__.'/function.php';
authenticate('basic');

$user_id = SimpleAuth::user_id();
$case_id = $mysqli->real_escape_string($_GET['case_id']);
$sql = "SELECT `case`.plate,`case`.note,user.phone,user.username as email,user.name as user,
		client.name as client,insurance.name as insurance,organization.name as organization
	FROM `case`
	INNER JOIN `user_client` ON `user_client`.`user_id`='$user_id' AND `user_client`.`client_id` = `case`.`client_id`
	INNER JOIN client ON client.id = `case`.client_id
	INNER JOIN user ON user.id = `case`.user_id
	INNER JOIN insurance ON insurance.id = `case`.insurance_id
	INNER JOIN organization ON organization.id =  `insurance`.organization_id
	WHERE `case`.`id`=$case_id";
$query = $mysqli->query($sql);
if(!$rs = $query->fetch_object()) {
	Ufo::abort('dialog');
	exit;
}

$doc = new BootSome();
$modal = $doc->modal();

$header = $modal->header();
$header->title('Information');
$header->close()->at(['onclick'=>"Ufo.abort('dialog')"]);

$mbody = $modal->body();

$row = $mbody->row();
$col = $row->col('col-lg-6','pr-lg-1');

$group = $col->form_horizontal();
$group->label('Nummerplade','plate');
$group->input('plate',$rs->plate)->at(['readonly']);

$group = $col->form_horizontal();
$group->label('Sagsnote','note');
$group->textarea('note',$rs->note)->at(['readonly','rows'=>4,'tabindex'=>'-1']);

$group = $col->form_horizontal();
$group->label('Selskab','insurance');
$group->input('insurance',$rs->insurance.' ('.$rs->organization.')')->at(['readonly','tabindex'=>'-1']);

$col = $row->col('col-lg-6','pl-lg-1');

$group = $col->form_horizontal();
$group->label('Kunde','client');
$group->input('client',$rs->client)->at(['readonly','tabindex'=>'-1']);

$group = $col->form_horizontal();
$group->label('Kontakt','user');
$group->input('user',$rs->user)->at(['readonly','tabindex'=>'-1']);

$sql = "SELECT `id`,`name` FROM `case_file` WHERE `case_id`='$case_id' ORDER BY `id`";
$query_file = $mysqli->query($sql);
if($query_file->num_rows) {
	$mbody->el('hr');
	$row = $mbody->row();
	while($rs_file = $query_file->fetch_object()) {
		$col = $row->col('col-lg-3');
		$a = $col->a(__ROOT__.'/mo_case/file_download.php?download&file_id='.$rs_file->id);
		$a->img(__ROOT__.'/mo_case/file_download.php?file_id='.$rs_file->id,$rs_file->name)->at(['class'=>'img-fluid']);
	}
}

Ufo::output('dialog',$doc);
