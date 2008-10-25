<?php
require('../../include_first.php');

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

if (!empty($_POST)) {
    foreach ($_POST['description'] AS $key=>$value) {
        $filemanager = new FileManager($kernel, $key);
        if ($filemanager->update(array(
            'description' => $_POST['description'][$key],
            'accessibility' => $_POST['accessibility'][$key]
            ))) {

            $appender = $filemanager->getKeywordAppender();
            $string_appender = new Intraface_Keyword_StringAppender($filemanager->getKeywords(), $appender);
            $string_appender->addKeywordsByString($_POST['keywords'][$key]);
        }
        echo $filemanager->error->view();
    }

    header('Location: index.php?use_stored=true');
    exit;
}

if (empty($_GET['use_stored'])) {
    trigger_error($translation->get('you cannot batch edit files with no save results'), E_USER_ERROR);
}


$filemanager = new FileManager($kernel);
$filemanager->getDBQuery()->storeResult('use_stored', 'filemanager', 'toplevel');

$files = $filemanager->getList();

$page = new Intraface_Page($kernel);
$page->start($translation->get('files'));
?>

<h1><?php e($translation->get('files')); ?></h1>

<ul class="options">
    <li><a href="index.php?use_stored=true"><?php e($translation->get('Cancel', 'common')); ?></a></li>
</ul>



<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
<?php

for ($i = 0, $max = count($files); $i < $max; $i++) {
    $this_filemanager = new FileManager($kernel, $files[$i]['id']);
    if ($this_filemanager->get('is_picture')) {

    }
    $keyword_object = $this_filemanager->getKeywordAppender();
    $files[$i]['keywords'] = $keyword_object->getConnectedKeywordsAsString();
    ?>
    <table class="stripe">
    <caption>Fil</caption>
        <tbody>
            <tr>
                <td rowspan="5" style="width: 280px;">
                    <?php if ($this_filemanager->get('is_picture')): ?>
                        <?php $this_filemanager->createInstance('small');?>
                        <img src="<?php e($this_filemanager->instance->get('file_uri')); ?>" alt="" />
                    <?php else: ?>
                        <img src="<?php e($this_filemanager->get('icon_uri')); ?>" alt="" />
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php e($translation->get('file')); ?></th>
                <td><?php e($files[$i]['file_name']); ?></td>
            </tr>
            <tr>
                <th><?php e($translation->get('file description')); ?></th>
                <td><textarea style="width: 400px; height; 100px;" name="description[<?php e($files[$i]['id']); ?>]"><?php e($files[$i]['description']); ?></textarea></td>
            </tr>
            <tr>
                <th><?php e($translation->get('keywords', 'keyword')); ?></th>
                <td><input type="text" name="keywords[<?php e($files[$i]['id']); ?>]" value="<?php e($files[$i]['keywords']); ?>" /></td>
            </tr>
            <tr>
                <th><?php e($translation->get('file accessibility')); ?></th>
                <td><input type="radio" id="accessibility[<?php e($files[$i]['id']); ?>]_public" name="accessibility[<?php e($files[$i]['id']); ?>]" value="public" <?php if (isset($files[$i]['accessibility']) && $files[$i]['accessibility'] == 'public') echo 'checked="checked"'; ?> /><label for="accessibility[<?php e($files[$i]['id']); ?>]_public"><?php e($translation->get('public')); ?></label> &nbsp; &nbsp; <input type="radio" id="accessibility[<?php e($files[$i]['id']); ?>]_intranet" name="accessibility[<?php e($files[$i]['id']); ?>]" value="intranet" <?php if (isset($files[$i]['accessibility']) && $files[$i]['accessibility'] == 'intranet') echo 'checked="checked"'; ?> /><label for="accessibility[<?php e($files[$i]['id']); ?>]_intranet"><?php e($translation->get('intranet')); ?></label></td>
            </tr>
        </tbody>
    </table>
    <?php
}
?>
<p>
<input type="submit" value="<?php e($translation->get('save', 'common')); ?>" />
<a href="index.php?use_stored=true"><?php e($translation->get('Cancel', 'common')); ?></a>
</p>
</form>

<?php
$page->end();
?>
