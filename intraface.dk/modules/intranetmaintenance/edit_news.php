<?php
require('../../include_first.php');

$modul = $kernel->module("intranetmaintenance");

$systemmessage = $kernel->useShared('systemmessage');

if(isset($_POST['submit'])) {

	$intranetnews = new IntranetNews($kernel, intval($_POST['id']));
	if($intranetnews->update($_POST) != 0) {
		header("location: messages.php");
		exit;
	}

	$values = $_POST;
}
elseif(isset($_GET['id'])) {
	$intranetnews = new IntranetNews($kernel, intval($_GET['id']));
    $values = $intranetnews->get();

}
else {
	$intranetnews = new IntranetNews($kernel);

}

$page = new Intraface_Page($kernel);
$page->start("Intranet nyhed");
?>

<h1><?php print("Intranet nyhed"); ?></h1>

<?php echo $intranetnews->error->view(); ?>


<form action="edit_news.php" method="post">

<fieldset>
	<legend>Opret nyhed</legend>

	<form action="messages.php" method="post">

	<div class="formrow">
		<label for="area">Område</label>
		<input type="text" name="area" id="area" value="<?php if(isset($values['area'])) print($values['area']); ?>" />
	</div>

	<div class="formrow">
		<label for="description">Nyhed</label>
		<textarea name="description" id="description" style="width: 400px; height: 70px;"><?php if(isset($values['description'])) print($values['description']); ?></textarea>
	</div>

</fieldset>

<input type="submit" name="submit" value="Gem" class="save" /> eller <a href="messages.php">Fortryd</a>

<input type="hidden" name="id" value="<?php print($intranetnews->get('id')); ?>" />

</form>

<?php

$page->end();

?>