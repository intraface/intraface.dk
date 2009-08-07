<?php
require('../../include_first.php');

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

require_once('Ilib/Redirect.php');
$options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
$redirect = Ilib_Redirect::factory($kernel->getSessionId(), MDB2::factory(DB_DSN), 'receive', $options);

if (!empty($_POST['addfile'])) {

    foreach ($_POST['addfile'] AS $key=>$value) {
        $filemanager = new FileManager($kernel, $value);
        $appender = $filemanager->getKeywordAppender();
        $string_appender = new Intraface_Keyword_StringAppender(new Keyword($filemanager), $appender);
        $string_appender->addKeywordsByString($_POST['keywords']);

        $filemanager->update(array('accessibility' => $_POST['accessibility']));

        if ($filemanager->moveFromTemporary()) {

            // $msg = 'Filerne er uploadet. <a href="'.$redirect->getRedirect('/modules/filemanager/').'">Åbn filarkivet</a>.';
        }
    }
    header('location: '.$redirect->getRedirect(url('/modules/filemanager/')));
    exit;

}

$page = new Intraface_Page($kernel);
$page->start($translation->get('upload files'));
?>

<h1><?php e($translation->get('upload files')); ?></h1>

<?php if (!empty($msg)): ?>
<p class="message"><?php echo $msg; ?></p>
<?php endif; ?>

<fieldset>
    <legend><?php e($translation->get('select files to upload')); ?></legend>
    <div id="iframe">
        <iframe frameborder="0" src="upload_script.php"></iframe>
    </div>
</fieldset>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <fieldset>
        <legend><?php e($translation->get('uploaded files')); ?></legend>
        <div id="images"></div>
    </fieldset>

    <fieldset>
        <legend><?php e($translation->get('keywords and permissions')); ?></legend>
        <div class="formrow">
            <label for=""><?php e($translation->get('keywords', 'keyword')); ?></label>
            <input type="text" name="keywords" value="<?php if (isset($_POST['keywords'])) e($_POST['keywords']); ?>" />
        </div>

        <div class="formrow">
            <label for="accessibility"><?php e($translation->get('accessibility')); ?></label>
            <select name="accessibility">
                <option value="public"><?php e($translation->get('public')); ?></option>
                <option value="intranet"><?php e($translation->get('intranet')); ?></option>
            </select>
        </div>
    </fieldset>

    <p class="alert"><?php e($translation->get('only click save when all files are uploaded')); ?>.</p>


    <p>
        <input type="submit" value="<?php e($translation->get('save', 'common')); ?>" />
         <a href="<?php e($redirect->getRedirect('index.php')); ?>"><?php e($translation->get('Cancel', 'common')); ?></a>
    </p>

</form>

<?php
$page->end();
?>