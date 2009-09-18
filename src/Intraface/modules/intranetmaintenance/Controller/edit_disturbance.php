<?php
require('../../include_first.php');

$modul = $kernel->module("intranetmaintenance");


if (isset($_POST['submit'])) {

	$systemdisturbance = new SystemDisturbance($kernel, intval($_POST['id']));
	if ($systemdisturbance->update($_POST) != 0) {
		header("location: messages.php");
		exit;
	}

	$values = $_POST;
}
elseif (isset($_GET['id'])) {
	$systemdisturbance = new SystemDisturbance($kernel, intval($_GET['id']));

	$values = $systemdisturbance->get();
	$values['from_date_time'] = $values['dk_from_date_time'];
	$values['to_date_time'] = $values['dk_to_date_time'];

}
else {
	$systemdisturbance = new SystemDisturbance($kernel);

	$values['from_date_time'] = date('d-m-Y H:i');
	$values['to_date_time'] = date('d-m-Y H:i', time() + 2 * 60 * 60);
}

$page = new Intraface_Page($kernel);
$page->start("Driftforstyrrelse");
?>

<h1><?php e("Driftforstyrrelse"); ?></h1>

<?php echo $systemdisturbance->error->view(); ?>

<form action="edit_disturbance.php" method="post">

<fieldset>
	<legend>Opret forstyrrelse</legend>



	<div class="formrow">
		<label for="from_date_time">Fra tidspunkt</label>
		<input type="text" name="from_date_time" id="from_date_time" value="<?php e($values['from_date_time']); ?>" />
	</div>

	<div class="formrow">
		<label for="to_date_time">Til tidspunkt</label>
		<input type="text" name="to_date_time" id="to_date_time" value="<?php e($values['to_date_time']); ?>" />
	</div>

	<div class="formrow">
		<label for="description">Beskrivelse</label>
		<textarea name="description" id="description" style="width: 400px; height: 70px;"><?php if (isset($values['description'])) e($values['description']); ?></textarea>
	</div>

	<div class="formrow">
		<label for="important">Slem forstyrrelse!</label>
		<input type="checkbox" name="important" id="important" value="1" <?php if (isset($values['important']) && intval($values['important']) == 1) print("checked=\"checked\""); ?> />
	</div>




</fieldset>

<input type="submit" name="submit" value="Gem" class="save" /> eller <a href="messages.php">Fortryd</a>

<input type="hidden" name="id" value="<?php e($systemdisturbance->get('id')); ?>" />

</form>

<?php

$page->end();

?>