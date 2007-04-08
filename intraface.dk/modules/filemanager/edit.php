<?php
require('../../include_first.php');

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

if(isset($_POST['submit'])) {

	$filemanager = new FileManager($kernel, intval($_POST['id']));

	if($filemanager->update($_POST)) {
		header('Location: file.php?id='.$filemanager->get('id'));
	}
	else {
		$values = $_POST;
	}
}
elseif(isset($_GET['id'])) {

	$filemanager = new FileManager($kernel, intval($_GET["id"]));
	$values = $filemanager->get();
}
else {
	trigger_error($translation->get('you cannot edit a file without an id'), E_USER_ERROR);
}

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('edit file')));
?>

<h1><?php echo safeToHtml($translation->get('edit file')); ?></h1>

<?php $filemanager->error->view(); ?>

<form action="edit.php" method="POST">
<fieldset>
	<legend><?php echo safeToHtml($translation->get('file information')); ?></legend>

	<div class="formrow">
		<label for="accessibility"><?php echo safeToHtml($translation->get('file accessibility')); ?></label>
		<select name="accessibility">
			<option value="public" <?php if($values['accessibility'] == 'public') print('selected="selected"'); ?> ><?php echo safeToHtml($translation->get('public', 'filehandler')); ?></option>
			<option value="intranet" <?php if($values['accessibility'] == 'intranet') print('selected="selected"'); ?> ><?php echo safeToHtml($translation->get('intranet', 'filehandler')); ?></option>
		</select>
	</div>

	<div class="formrow">
		<label for="description"><?php echo safeToHtml($translation->get('file description')); ?></label>
		<textarea name="description" id="description" style="width: 500px; height: 200px;"><?php echo safeToForm($values['description']); ?></textarea>
	</div>

</fieldset>

<p></p><input type="submit" class="save" name="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" />
<a href="file.php?id=<?php print($filemanager->get('id')); ?>"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a>
</p>
</p><input type="hidden" name="id" value="<?php print($filemanager->get("id")); ?>" />

</form>

<?php
$page->end();
?>