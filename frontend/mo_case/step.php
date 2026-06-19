<?php
require_once __DIR__.'/../header.inc.php';
require_once __DIR__.'/function.php';
authenticate('employee');

$case_id = $mysqli->real_escape_string($_GET['case_id']);
$status = $mysqli->real_escape_string($_GET['status']);
$sql = "SELECT `case`.plate,`case`.note,user.phone,user.username as email,user.name as user,
		client.name as client,client.agreement,client.batch,client.id as client_id,
		insurance.name as insurance,client.disable,`case`.responsible_id,`client`.`workshop`,
		responsible.name as responsible,done.name as done,IF(client.refresh_token IS NOT NULL,1,0) as client_api,
		organization.name as organization,organization.id as organization_id,client.fixed
	FROM `case`
	INNER JOIN client ON client.id = `case`.client_id
	INNER JOIN user ON user.id = `case`.user_id
	LEFT JOIN user as responsible ON responsible.id = `case`.responsible_id
	LEFT JOIN user as done ON done.id = `case`.done_id
	INNER JOIN insurance ON insurance.id = `case`.insurance_id
	INNER JOIN organization ON organization.id =  `insurance`.organization_id
	WHERE `case`.`id`=$case_id AND `case`.status='$status'";
$query = $mysqli->query($sql);
if(!$rs = $query->fetch_object()) {
	Ufo::abort('dialog');
	exit;
}

$doc = new BootSome();

$exception = [];
foreach(case_status() as $key => $value) {
	if($value['exception']) $exception[] = $key;
}

$java = "Ufo.post('dialog','".__ROOT__."/mo_case/step.script.php','dialogform');return false;";
$form = $doc->form()->at(['id'=>'dialogform','enctype'=>'multipart/form-data','onsubmit'=>$java]);
$form->hidden('case_id',$case_id);
$form->hidden('status',$status);

$modal = $form->modal();

$header = $modal->header();
if($status=='new') {
	$header->title('Tildel');
	$buttontext = 'Tildel';
	$icon = 'user-check';
}
elseif($status=='ass') {
	$header->title('Påbegynd');
	$buttontext = 'Påbegynd';
	$icon = 'users-cog';
}
elseif($status=='ong') {
	$header->title('Afsend');
	$buttontext = 'Afsend';
	$icon = 'mail-bulk';
}
elseif(in_array($status,['snt','ctl'])) {
	$header->title('Registrer');
	$buttontext = 'Registrer';
	$icon = 'check';
}
elseif(in_array($status,$exception)) {
	$header->title('Genoptag');
	$buttontext = 'Genoptag';
	$icon = 'users-cog';
}
else {
	$header->title('Information');
}

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

if($rs->responsible) {
	$group = $col->form_horizontal();
	$group->label('Ansvarlig','responsible');
	$group->input('responsible',$rs->responsible)->at(['readonly','tabindex'=>'-1']);
}
if($rs->done) {
	$group = $col->form_horizontal();
	$group->label('Registrering','done');
	$group->input('done',$rs->done)->at(['readonly','tabindex'=>'-1']);
}

$col = $row->col('col-lg-6','pl-lg-1');

$group = $col->form_horizontal();
$group->label('Reparatørnummer','workshop');
$inputgroup = $group->inputgroup();
$inputgroup->input('workshop',$rs->workshop)->at(['readonly','tabindex'=>'-1']);
$java = "navigator.clipboard.writeText(document.getElementById('workshop').value);";
$inputgroup->button('Kopier','copy')->at(['onclick'=>$java]);

$group = $col->form_horizontal();
$group->label('Kunde','client');
$group->input('client',$rs->client)->at(['readonly','tabindex'=>'-1']);

$group = $col->form_horizontal();
$group->label('Kontakt','user');
$group->input('user',$rs->user)->at(['readonly','tabindex'=>'-1']);

$group = $col->form_horizontal();
$group->label('E-mail','email');
$ig = $group->inputgroup();
$ig->input('email',$rs->email)->at(['readonly','tabindex'=>'-1']);
$js = "window.location.href='mailto:".rawurlencode($rs->email)."?subject=".rawurlencode('Vedr. '.$rs->plate)."'";
$ig->button('E-mail','envelope')->at(['onclick'=>$js]);

$group = $col->form_horizontal();
$group->label('Telefon','phone');
$inputgroup = $group->inputgroup();
$inputgroup->input('phone',$rs->phone)->at(['readonly','tabindex'=>'-1']);
$java = "window.location.href='tel:'+document.getElementById('phone').value;";
$inputgroup->button('Opkald','phone')->at(['onclick'=>$java]);

if(!empty($rs->agreement)) {
	$group = $col->form_horizontal();
	$group->label('Aftale','agreement');
	$group->textarea('agreement',$rs->agreement)->at(['readonly','rows'=>6,'tabindex'=>'-1']);
}

$sqla = "SELECT 'Kunde' as source,`guide`,`priority`,`url` FROM `client_organization`
	WHERE `client_id` = $rs->client_id AND (`organization_id` = $rs->organization_id OR `organization_id` IS NULL)";

$sqlb = "SELECT 'Gruppe' as source,`guide`,`priority`,`url`
	FROM `client_group`
	INNER JOIN `group_organization` ON `group_organization`.`group_id` = `client_group`.`group_id`
	WHERE `client_id` = $rs->client_id AND (`organization_id` = $rs->organization_id OR `organization_id` IS NULL)";

$sql = "($sqla) UNION ALL ($sqlb) ORDER BY `priority`,`guide`";

$query_guide = $mysqli->query($sql);
while($rs_guide = $query_guide->fetch_object()) {
	$alert = $mbody->alert($rs_guide->priority);
	$alert->el('i')->te($rs_guide->source);
	$alert->te(': '.$rs_guide->guide,true);
	if($rs_guide->url) {
		$alert->te(' ');
		$alert->el('b')->a($rs_guide->url,'Link')->at(['target'=>'_blank']);
	}
}

$mbody->el('hr');
$mbody->floating_textarea('Kommentar','comment')->at(['rows'=>4]);

$sql = "SELECT `case_comment`.`content`,`case_comment`.`create_time`,`user`.`name` as user
	FROM `case_comment`
	LEFT JOIN `user` ON `user`.`id` = `case_comment`.`create_by`
	WHERE `case_id` = $case_id
	ORDER BY `case_comment`.`create_time` DESC,`case_comment`.`id`";
$query_comment = $mysqli->query($sql);

if($query_comment->num_rows) {
	$table = $mbody->table();
	$thead = $table->thead()->tr();
	$thead->th()->te('Ansvarlig');
	$thead->th()->te('Oprettet');
	$thead->th()->te('Kommentar');
	
	$tbody = $table->tbody();
	while($rs_comment = $query_comment->fetch_object()) {
		$tr = $tbody->tr();
		$tr->td()->te($rs_comment->user);
		$tr->td()->te((new Datetime($rs_comment->create_time))->format('j/n H:i'));
		$tr->td()->at(['wrap'])->te($rs_comment->content,true);
	}
}

$mbody->el('hr');
$group = $mbody->form_horizontal();
$group->label('Vedhæft filer','file');
$group->file('file',true)->at(['accept'=>'image/jpeg,application/pdf']);

$sql = "SELECT `id`,`name` FROM `case_file` WHERE `case_id`='$case_id' ORDER BY `id`";
$query_file = $mysqli->query($sql);
if($query_file->num_rows) {

	$row = $mbody->row();
	while($rs_file = $query_file->fetch_object()) {
		$col = $row->col('col-lg-3');
		$url = __ROOT__.'/mo_case/file_download.php?file_id='.$rs_file->id;
		$a = $col->a($url)->at(['target'=>'_blank','title'=>$rs_file->name]);
		$js = "event.stopPropagation();event.dataTransfer.setData('DownloadURL','image/jpeg:".$rs_file->name.":'+window.location.origin+'".$url."')";
		$a->img(__ROOT__.'/mo_case/file_download.php?file_id='.$rs_file->id.'&preview',$rs_file->name,true)->at(['ondragstart' => $js,'class'=>'rounded'],true);
	}
}

if($rs->client_api && $status=='ass') {
	$mbody->el('hr');
	
	$group = $mbody->form_horizontal(4);
	$js = "this.disabled=true;if(confirm('Opret RP i Forsi?')) {Ufo.get('dialog','".__ROOT__."/mo_case/forsi_draft.script.php?case_id=".$case_id."');}";
	$group->button('Opret RP i Forsi','file-export','info')->at(['onclick'=>$js]);
}

if(in_array($status,['ong','snt','ctl'])) {
	quicklist($mbody,$rs->client_id);
}

if($status=='new') {
	$mbody->el('hr');
	
	$group = $mbody->form_horizontal();
	$group->label('Ansvarlig','responsible_id');
	$select = $group->select('responsible_id')->at(['required']);
	$sql = "SELECT id,name
		FROM user
		INNER JOIN access ON user.id = access.user_id AND `access`.`permission`='employee'
		ORDER BY name";
	$query = $mysqli->query($sql);
	$select->option('');
	$selected = $rs->responsible_id ?? SimpleAuth::user_id();
	$select->options($query,$selected);
}
elseif($status=='ass') {
	$sql = "SELECT `damage`.`id`,`damage`.`number`,IF(`report_init_checksum` IS NULL,0,1) as `report_init`
		FROM `damage`
		WHERE `damage`.`case_id` = $case_id
		ORDER BY `damage`.`id`";
	$query_dam = $mysqli->query($sql);
	while($rs_dam = $query_dam->fetch_object()) {
		damage_template($mbody,(bool) $rs->batch,$rs_dam->id,$rs_dam->number,$rs_dam->report_init ? true : null);
	}
	
	$template = $mbody->el('template',['data-tmpl-name'=>'add_damage']);
	damage_template($template,(bool) $rs->batch);
}
elseif($status=='ong') {
	$sql = "SELECT damage.id,damage.number,damage.notice,
			IF(`report_init_checksum` IS NULL,0,1) as report_init
		FROM damage
		WHERE damage.case_id = $case_id
		ORDER BY damage.id";
	$query_dam = $mysqli->query($sql);
	while($rs_dam = $query_dam->fetch_object()) {
		$mbody->el('hr');
		$row = $mbody->row();
		
		$group = $row->col('col-lg-6','pr-lg-1')->form_horizontal(6);
		$group->label('SkadeID','damage_id['.$rs_dam->id.']');
		$group->input('damage_id['.$rs_dam->id.']',$rs_dam->id)->at(['readonly','tabindex'=>'-1']);
		
		$group = $row->col('col-lg-6','pl-lg-1')->form_horizontal();
		$group->label('RP-nummer','damage_number['.$rs_dam->id.']');
		$group->input('damage_number['.$rs_dam->id.']',format_case($rs_dam->number))->at(['readonly']);

		$row = $mbody->row();
		$group = $row->col('col-lg-6','pr-lg-1')->form_horizontal();
		
		damage_file($group,$rs_dam);
		
		$group = $row->col('col-lg-6','pl-lg-1')->form_horizontal();
		$group->label('Efter-rapport','damage_report_sent['.$rs_dam->id.']');
		$file = $group->file('damage_report_sent['.$rs_dam->id.']')->at(['accept'=>'application/pdf']);
		if(!$rs->batch) $file->at(['required']);
		
		$row = $mbody->row_gutter('g-2');
		$row->col('col-12','col-md-8')->floating_textarea('Tilrettelser','damage_notice['.$rs_dam->id.']',trim($rs_dam->notice ?? '')."\n")->at(['required','style'=>'height:8em','id'=>'damage_notice_'.$rs_dam->id]);
		$input = $row->col('col-12','col-md-4')->floating_input('Quick indsæt','quickinsert');
		quicklistjs($input,'damage_notice_'.$rs_dam->id);
	}
}
elseif($status=='mis') {
	
}
elseif(in_array($status,['snt','ctl'])) {
	$sql = "SELECT damage.id,damage.notice,damage.number,
			damage.body,damage.paint,damage.sparepart,damage.void,damage.fixed,
			IF(`report_init_checksum` IS NULL,0,1) as report_init,IF(`report_sent_checksum` IS NULL,0,1) as report_sent
		FROM damage
		WHERE damage.case_id = $case_id
		ORDER BY damage.id";
	$query_dam = $mysqli->query($sql);
	while($rs_dam = $query_dam->fetch_object()) {
		$mbody->el('hr');
		$row = $mbody->row();

		$group = $row->col('col-lg-6','pr-lg-1')->form_horizontal(6);
		$group->label('SkadeID','damage_id['.$rs_dam->id.']');
		$group->input('damage_id['.$rs_dam->id.']',$rs_dam->id)->at(['readonly']);
		
		$group = $row->col('col-lg-6','pl-lg-1')->form_horizontal();
		$group->label('RP-nummer','damage_number['.$rs_dam->id.']');
		$group->input('damage_number['.$rs_dam->id.']',format_case($rs_dam->number))->at(['readonly']);
		
		$row = $mbody->row_gutter('g-2');
		$row->col('col-12','col-md-8')->floating_textarea('Tilrettelser','damage_notice['.$rs_dam->id.']',trim($rs_dam->notice)."\n")->at(['required','style'=>'height:8em','id'=>'damage_notice_'.$rs_dam->id]);
		$input = $row->col('col-12','col-md-4')->floating_input('Quick indsæt','quickinsert');
		quicklistjs($input,'damage_notice_'.$rs_dam->id);

		$row = $mbody->form_row()->at(['class'=>'pt-2'],true);
		$group = $row->form_group(3);
		damage_file($group,$rs_dam);
		
		$group = $row->form_group(4);
		$inputgroup = $group->inputgroup();
		$input = $inputgroup->input('body['.$rs_dam->id.']',$rs_dam->body ? number_format($rs_dam->body,2,',','') : '');
		$input->at(['placeholder'=>'Arbejdsløn','autocomplete'=>'off','class'=>'text-right'],true);
		$input = $inputgroup->input('sparepart['.$rs_dam->id.']',$rs_dam->sparepart ? number_format($rs_dam->sparepart,2,',','') : '');
		$input->at(['placeholder'=>'Reservedele','autocomplete'=>'off','class'=>'text-right'],true);
		$input = $inputgroup->input('paint['.$rs_dam->id.']',$rs_dam->paint ? number_format($rs_dam->paint,2,',','') : '');
		$input->at(['placeholder'=>'Lakering','autocomplete'=>'off','class'=>'text-right'],true);
		
		$group = $row->form_group(3);
		$inputgroup = $group->inputgroup();
		$inputgroup->text('Fastpris');
		$input = $inputgroup->input('fixed['.$rs_dam->id.']',$rs->batch ? number_format($rs->fixed,2,',','') : '');
		$input->at(['autocomplete'=>'off','class'=>'text-right'],true);
		
		$group = $row->form_group(2);
		$group->checkbox('void['.$rs_dam->id.']',$rs_dam->void,1,'Totalskade');
	}
}
elseif($status=='dne') {
	$sql = "SELECT damage.id,damage.notice,damage.number,
			damage.body,damage.paint,damage.sparepart,damage.void,damage.fixed,
			IF(`report_init_checksum` IS NULL,0,1) as report_init,IF(`report_sent_checksum` IS NULL,0,1) as report_sent
		FROM damage
		WHERE damage.case_id = $case_id
		ORDER BY damage.id";
	$query_dam = $mysqli->query($sql);
	while($rs_dam = $query_dam->fetch_object()) {
		$mbody->el('hr');
		$row = $mbody->row();

		$group = $row->col('col-lg-6','pr-lg-1')->form_horizontal(6);
		$group->label('SkadeID','damage_id['.$rs_dam->id.']');
		$group->input('damage_id['.$rs_dam->id.']',$rs_dam->id)->at(['readonly']);
		
		$group = $row->col('col-lg-6','pl-lg-1')->form_horizontal();
		$group->label('RP-nummer','damage_number['.$rs_dam->id.']');
		$group->input('damage_number['.$rs_dam->id.']',format_case($rs_dam->number))->at(['readonly']);
		
		$row = $mbody->row_gutter('g-2');
		$row->col('col-12')->floating_textarea('Tilrettelser','damage_notice['.$rs_dam->id.']',trim($rs_dam->notice)."\n")->at(['readonly','style'=>'height:8em']);

		$row = $mbody->form_row()->at(['class'=>'pt-2'],true);
		$group = $row->form_group(3);
		damage_file($group,$rs_dam);
		
		$group = $row->form_group(4);
		$inputgroup = $group->inputgroup();
		$input = $inputgroup->input('body['.$rs_dam->id.']',$rs_dam->body ? number_format($rs_dam->body,2,',','') : '');
		$input->at(['readonly','placeholder'=>'Arbejdsløn','class'=>'text-right'],true);
		$input = $inputgroup->input('sparepart['.$rs_dam->id.']',$rs_dam->sparepart ? number_format($rs_dam->sparepart,2,',','') : '');
		$input->at(['readonly','placeholder'=>'Reservedele','class'=>'text-right'],true);
		$input = $inputgroup->input('paint['.$rs_dam->id.']',$rs_dam->paint ? number_format($rs_dam->paint,2,',','') : '');
		$input->at(['readonly','placeholder'=>'Lakering','class'=>'text-right'],true);
		
		$group = $row->form_group(3);
		$inputgroup = $group->inputgroup();
		$inputgroup->text('Fastpris');
		$input = $inputgroup->input('fixed['.$rs_dam->id.']',$rs_dam->fixed ? number_format($rs_dam->fixed,2,',','') : '-');
		$input->at(['readonly','class'=>'text-right'],true);
		
		$group = $row->form_group(2);
		$group->checkbox('void['.$rs_dam->id.']',$rs_dam->void,1,'Totalskade')->at(['disabled']);
	}
}

$footer = $modal->footer();

if(SimpleAuth::access('admin')) {
	$footer->button('Rediger','pen','warning')->at(['onclick'=>"Ufo.get('dialog','".__ROOT__."/mo_case/edit.php?case_id=".$case_id."')"]);
}

if(in_array($status,['ass'])) {
	$footer->button('Tilføj skade','plus','info')->at(['onclick'=>"TinyTemplate.activate('add_damage');"]);
}
if(in_array($status,['ong'])) {
	$footer->hidden('exception','');
	$js = "document.getElementById('exception').value='stop';form.dispatchEvent(new Event('submit'))";
	$footer->button('Stop','ban','danger')->at(['onclick'=>$js]);
}
if(in_array($status,array_merge(['new','ass','ong','snt','ctl'],$exception))) {
	$footer->hidden('saving','');
	$js = "document.getElementById('saving').value='yes';form.dispatchEvent(new Event('submit'))";
	$footer->button('Gem','save','info')->at(['onclick'=>$js]);
}
if(in_array($status,['snt'])) {
	$footer->hidden('exception','');
	$js = "document.getElementById('exception').value='control';form.dispatchEvent(new Event('submit'))";
	$footer->button('Kontrol','magnifying-glass','secondary')->at(['onclick'=>$js]);
}
if(in_array($status,array_merge(['new','ass','ong','snt','ctl'],$exception))) {
	$footer->button($buttontext,$icon)->at(['type'=>'submit']);
}

Ufo::output('dialog',$doc);

function damage_template($html,$batch = false,$id = null,$number = '',$report_init = null) {
	$html = $html->el('div');
	$html->el('hr');
	$row = $html->row();
	
	$group = $row->col('col-lg-6','pr-lg-1')->form_horizontal(6);
	$group->label('SkadeID',$id ? 'damage_id['.$id.']' : '');
	$group->input($id ? 'damage_id['.$id.']' : '',$id)->at(['readonly','tabindex'=>'-1']);
	
	$group = $row->col('col-lg-6','pl-lg-1')->form_horizontal();
	$group->label('RP-nummer',$id ? 'damage_number['.$id.']' : 'new_damage_number[]');
	$group->input($id ? 'damage_number['.$id.']' : 'new_damage_number[]',format_case($number))->at(['maxlength'=>18,'minlength'=>13,'required']);
	
	$row = $html->row();
	$group = $row->col('col-lg-6','pr-lg-1')->form_horizontal(6);
	$group->label('Før-rapport');
	$file = $group->file($id ? 'damage_report_init['.$id.']' : 'damage_report_init_new[]')->at(['accept'=>'application/pdf']);
	if($report_init) $group->text('Rapport findes allerede!','success');
	if($batch===false && $report_init===null) $file->at(['required']);
	
	if(!$report_init) {
		$group = $row->col('col-lg-6','pl-lg-1')->form_horizontal();
		$group->label('');
		$js = "this.parentElement.parentElement.parentElement.parentElement.parentElement.parentNode.removeChild(this.parentElement.parentElement.parentElement.parentElement.parentElement);";
		if($id) $js = "if(confirm('Fjern skade?'))".$js;
		$group->button('Fjern','ban','warning')->at(['onclick'=>$js]);
	}
}
function damage_file($group,$rs_dam) {
	if($rs_dam->report_init) {
		$group->button('Før','file-pdf','primary',__ROOT__.'/mo_case/report_download.php?report_init&damage_id='.$rs_dam->id)->at(['target'=>'_blank']);
	}
	if(!empty($rs_dam->report_sent)) {
		$group->button('Afsendt','file-pdf','primary',__ROOT__.'/mo_case/report_download.php?report_sent&damage_id='.$rs_dam->id)->at(['target'=>'_blank']);
	}
}
function quicklist($html,$client_id) {
	global $mysqli;
	$sql = "SELECT `group_quicklist`.`line`
		FROM `client_group`
		INNER JOIN `group_quicklist` ON `group_quicklist`.`group_id` = `client_group`.`group_id`
		WHERE `client_id` = $client_id";

	$query = $mysqli->query($sql);
	if($query->num_rows) {
		$datalist = $html->el('datalist',['id'=>'quickline']);
		while($rs = $query->fetch_object()) {
			$datalist->el('option',['value'=>$rs->line]);
		}
	}
}
function quicklistjs($input,$target) {
	$js = "document.getElementById('$target').value += this.value += '\\n';this.value='';";
	$js = 'if(this.value) {'.$js.'}';
	$input->at(['list'=>'quickline','onblur'=>$js,'onkeydown'=>'if(event.keyCode==9) {'.$js.'return false;}']);
}
