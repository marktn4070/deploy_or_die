<?php
require_once __DIR__.'/../header.inc.php';
authenticate('basic');

$user_id = SimpleAuth::user_id();

$doc = new BootSome();

$java = "Ufo.post('dialog','".__ROOT__."/mo_case/new.script.php','dialogform');return false;";
$form = $doc->form()->at(['id'=>'dialogform','enctype'=>'multipart/form-data','onsubmit'=>$java]);

$modal = $form->modal();

$header = $modal->header();
$header->title('Ny arbejdsordre');
$header->close()->at(['onclick'=>"Ufo.abort('dialog')"]);

$mbody = $modal->body();

if(!SimpleAuth::access('employee')) {
	$clientjoin = "INNER JOIN `user_client` ON `user_client`.`user_id`='$user_id' AND `user_client`.`client_id` = `client`.`id`";
}
else {
	$clientjoin = '';
}

$sql = "SELECT `client`.`id`,CONCAT(`client`.`name`,' [',`client`.`workshop`,']') as name FROM `client`
	$clientjoin
	ORDER BY `client`.`name`";
$query = $mysqli->query($sql);

if($query->num_rows===1) {
	$client_id = $query->fetch_object()->id;
	$form->hidden('client_id',$client_id);
	Ufo::get('popup',__ROOT__.'/mo_case/new_userlist.script.php?client_id='.$client_id);
}
elseif($query->num_rows > 1) {
	$row = $mbody->row_gutter('g-2 pb-2');
	$datalist = $row->floating_datalist('Kunde','client_id')->required();
	$datalist->options($query);

	$java = "Ufo.get('popup','".__ROOT__."/mo_case/new_userlist.script.php?client_id='+document.getElementById('client_id').value);";
	$datalist->at(['onchange'=>$java],true);
}
else {
	Ufo::abort('dialog');
	Ufo::call('alert','Ingen kunder tilknyttet');
	exit;
}

$row = $mbody->row_gutter('g-2');

$js = "this.value = this.value.replace(/\s+/g,'').toUpperCase();";
$row->col('col-12','col-md-6')->floating_input('Nummerplade','plate')->at(['pattern'=>'[A-Z0-9]{1,7}','onchange'=>$js])->required();

$select = $row->col('col-12','col-md-6')->floating_select('Selskab','insurance_id')->required();

$sql = "SELECT `insurance`.`id`,`insurance`.`name`
	FROM `user_insurance`
	INNER JOIN `insurance` ON `user_insurance`.`insurance_id` = `insurance`.`id`
	WHERE `user_insurance`.`user_id` = $user_id
	ORDER BY `name`";
$query = $mysqli->query($sql);
if($query->num_rows!==1) {
	$select->option('');
}
if($query->num_rows) {
	$select->optgroup('Favoritter')->options($query,$query->num_rows==1 ? true : null);
	$select = $select->optgroup('Alle');
}

$sql = "SELECT id,name FROM insurance ORDER BY name";
$query = $mysqli->query($sql);
$select->options($query);

$select = $row->col('col-12','col-md-6')->floating_select('Bruger','user_id')->required();
$select->option('')->at(['disabled']);

$row->col('col-12')->floating_textarea('Sagsnote','note');

$template = $mbody->el('template',['data-tmpl-name'=>'add_damage']);
damage_template($template);

$mbody->el('hr');

$row = $mbody->row_gutter('g-2');
$onchange = 'WildFile.list("BootSome").add(this);';
$row->col('col-12')->floating_file('Vedhæft filer','file',true)->at(['accept'=>'image/jpeg,application/pdf','onchange'=>$onchange]);
$table = $mbody->table()->tbody()->at(['id'=>'chunked_upload_files']);

$footer = $modal->footer();
$footer->hidden('status','new');



$js = "document.getElementById('status').value='new';";
$footer->button('Opret','plus')->at(['onclick'=>$js,'type'=>'submit']);

Ufo::output('dialog',$doc);

function damage_template($html) {
	$html = $html->el('div');
	$html->el('hr');
	$row = $html->row();
		
	$group = $row->col('col-lg-6','pr-lg-1')->form_horizontal(6);
	$group->label('RP-nummer','new_damage_number[]');
	$group->input('new_damage_number[]')->at(['maxlength'=>18,'minlength'=>13,'required']);
	
	$group = $row->col('col-lg-6','pl-lg-1')->form_horizontal();
	$group->label('');
	$js = "this.parentElement.parentElement.parentElement.parentElement.parentElement.parentNode.removeChild(this.parentElement.parentElement.parentElement.parentElement.parentElement);";
	$group->button('Fjern','ban','warning')->at(['onclick'=>$js]);
}
