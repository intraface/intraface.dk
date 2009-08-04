<?php
/**
 * Import skal ikke bruges fra egen server før vi har et uploadtool.
 *
 * Måske kan vi importere fra en anden ftp-server, for vi kan med NET/Ftp.php
 * vist ret let flytte dem til vores server.
 *
 */

require('../../include_first.php');

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

$file_dir = $kernel->intranet->get('id').'/import/';

if (isset($_POST["submit"])) {

	$filemanager = new FileManager($kernel);
	$filemanager->loadUpload();

	$filemanager->upload->setSetting('file_accessibility', $_POST['accessibility']);
	$filemanager->upload->setSetting('max_file_size', 800000);
	$filemanager->upload->setSetting('add_keyword', $_POST['keyword']);

	if ($filemanager->upload->import(UPLOAD_PATH.$file_dir)) {
		// header("location: file.php?id=".$id);
		// her burde den gå til en batchedit af de uploadede filer!
		die("FÆRDIG");
		exit;
	}
}
else {
	$filemanager = new FileManager($kernel);
}


$page = new Intraface_Page($kernel);
$page->start($translation->get('import files'));
?>

<h1><?php e($translation->get('import files')); ?></h1>

<?php echo $filemanager->error->view(); ?>

<p><?php e($translation->get('import files from directory')); ?> <?php e($file_dir); ?></p>

<form action="import.php" method="POST">
<fieldset>
	<legend><?php e($translation->get('file information')); ?></legend>

	<div class="formrow">
		<label for="accessibility"><?php e($translation->get('accessibility')); ?></label>
		<select name="accessibility">
			<option value="public"><?php e($translation->get('public')); ?></option>
			<option value="intranet"><?php e($translation->get('intranet')); ?></option>
		</select>
	</div>

	<div class="formrow">
		<label for="keyword"><?php e($translation->get('keywords', 'keyword')); ?></label>
		<input type="text" name="keyword" id="keyword" value="" />
	</div>

</fieldset>

<input type="submit" class="save" name="submit" value="<?php e($translation->get('import files')); ?>" />

<a href="index.php"><?php e($translation->get('Cancel', 'common')); ?></a>

</form>

<?php
$page->end();
?>