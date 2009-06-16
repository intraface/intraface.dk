<?php
require('../../include_first.php');

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

if (isset($_POST['submit'])) {

    $filemanager = new FileManager($kernel, intval($_POST['id']));
    if ($filemanager->get('id') == 0) {
        trigger_error('Invalid id!', E_USER_ERROR);
        exit;
    }

    $filemanager->createUpload();
    $filemanager->upload->setSetting('max_file_size', '1000000');
    if ($filemanager->upload->isUploadFile('replace_file')) { //
        $upload_result = $filemanager->upload->upload('replace_file');
    }
    else {
        $upload_result = true;
    }

    if ($filemanager->update($_POST) && $upload_result) {
        header('Location: file.php?id='.$filemanager->get('id'));
    }
    else {
        $values = $_POST;
    }
}
elseif (isset($_GET['id'])) {

    $filemanager = new FileManager($kernel, intval($_GET["id"]));
    if ($filemanager->get('id') == 0) {
        trigger_error('Invalid id!', E_USER_ERROR);
        exit;
    }
    $values = $filemanager->get();
}
else {
    trigger_error($translation->get('you cannot edit a file without an id'), E_USER_ERROR);
}

$page = new Intraface_Page($kernel);
$page->start($translation->get('edit file'));
?>

<h1><?php e($translation->get('edit file')); ?></h1>

<?php echo $filemanager->error->view(); ?>

<form action="edit.php" method="POST" enctype="multipart/form-data">
<fieldset>
    <legend><?php e($translation->get('file information')); ?></legend>

    <div class="formrow">
        <label for="accessibility"><?php e($translation->get('file accessibility')); ?></label>
        <select name="accessibility">
            <option value="public" <?php if (!empty($values['accessibility']) AND $values['accessibility'] == 'public') print('selected="selected"'); ?> ><?php e($translation->get('public', 'filehandler')); ?></option>
            <option value="intranet" <?php if (!empty($values['accessibility']) AND $values['accessibility'] == 'intranet') print('selected="selected"'); ?> ><?php e($translation->get('intranet', 'filehandler')); ?></option>
        </select>
    </div>

    <div class="formrow">
        <label for="description"><?php e($translation->get('file description')); ?></label>
        <textarea name="description" id="description" style="width: 500px; height: 200px;"><?php if (!empty($values['description'])) e($values['description']); ?></textarea>
    </div>

</fieldset>

<fieldset>
    <legend><?php e($translation->get('Replace file')); ?></legend>

    <div class="formrow">
        <label for="replace_file"><?php e($translation->get('Choose file')); ?></label>
        <input name="replace_file" type="file" id="replace_file" />
    </div>

</fieldset>

<p>
<input type="submit" class="save" name="submit" value="<?php e($translation->get('save', 'common')); ?>" />
<a href="file.php?id=<?php e($filemanager->get('id')); ?>"><?php e($translation->get('Cancel', 'common')); ?></a>
</p>
<input type="hidden" name="id" value="<?php e($filemanager->get("id")); ?>" />

</form>

<?php
$page->end();
?>