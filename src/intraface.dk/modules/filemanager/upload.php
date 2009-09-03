<?php
require('../../include_first.php');

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

$redirect = Intraface_Redirect::factory($kernel, 'receive');

if (isset($_POST["submit"])) {

	$filemanager = new FileManager($kernel);
	$filemanager->createUpload();

	$filemanager->upload->setSetting('file_accessibility', $_POST['accessibility']);
	$filemanager->upload->setSetting('max_file_size', '10000000');
	$filemanager->upload->setSetting('add_keyword', $_POST['keyword']);
	if ($id = $filemanager->upload->upload('userfile')) {
		header("location: ".$redirect->getRedirect('file.php?id='.$id));
		exit;
	}
}
else {
	$filemanager = new FileManager($kernel);
}


$page = new Intraface_Page($kernel);
$page->start($translation->get('upload file'));
?>

<h1><?php e($translation->get('upload file')); ?></h1>

<?php echo $filemanager->error->view(); ?>

<form action="upload.php" method="POST" enctype="multipart/form-data">
<fieldset>
	<legend><?php e($translation->get('file')); ?></legend>
	<!--
	Overvej at gøre det muligt at uploade flere filer på det samme filboks
	http://the-stickman.com/web-development/javascript/upload-multiple-files-with-a-single-file-element/
	-->
	<div class="formrow">
		<label for="userfile"><?php e($translation->get('file')); ?></label>
		<input name="userfile" type="file" id="userfile" />
	</div>
	<div class="formrow">
		<label for="accessibility"><?php e($translation->get('accessibility')); ?></label>
		<select name="accessibility">
			<option value="intranet"><?php e($translation->get('intranet')); ?></option>
			<option value="public"><?php e($translation->get('public')); ?></option>
		</select>
	</div>

	<div class="formrow">
		<label for="keyword"><?php e($translation->get('keyword', 'keyword')); ?>:</label>
		<input type="text" name="keyword" id="keyword" value="" />
	</div>
</fieldset>

<input type="submit" class="save" name="submit" value="<?php e($translation->get('upload')); ?>" />
<a href="<?php e($redirect->getRedirect('index.php')); ?>"><?php e($translation->get('Cancel', 'common')); ?></a>

</form>

<?php
$page->end();
?>