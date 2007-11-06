<?php
require('../../include_first.php');

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

$redirect = Redirect::factory($kernel, 'receive');

if (!empty($_POST['addfile'])) {

    foreach($_POST['addfile'] AS $key=>$value) {
        $filemanager = new FileManager($kernel, $value);
        $appender = $filemanager->getKeywordAppender();
        $string_appender = new Intraface_Keyword_StringAppender(new Keyword($filemanager), $appender);
        $string_appender->addKeywordsByString($_POST['keywords']);

        $filemanager->update(array('accessibility' => $_POST['accessibility']));

        if ($filemanager->moveFromTemporary()) {

            // $msg = 'Filerne er uploadet. <a href="'.$redirect->getRedirect('/modules/filemanager/').'">Åbn filarkivet</a>.';
        }
    }
    header('location: '.$redirect->getRedirect(PATH_WWW . '/modules/filemanager/'));
    exit;

}

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('upload files')));
?>

<h1><?php echo safeToHtml($translation->get('upload files')); ?></h1>

<?php if (!empty($msg)): ?>
<p class="message"><?php echo $msg; ?></p>
<?php endif; ?>

<fieldset>
    <legend><?php echo safeToHtml($translation->get('select files to upload')); ?></legend>
    <div id="iframe">
        <iframe frameborder="0" src="upload_script.php"></iframe>
    </div>
</fieldset>

<form action="<?php echo safeToForm($_SERVER['PHP_SELF']); ?>" method="post">
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('uploaded files')); ?></legend>
        <div id="images"></div>
    </fieldset>

    <fieldset>
        <legend><?php echo safeToHtml($translation->get('keywords and permissions')); ?></legend>
        <div class="formrow">
            <label for=""><?php echo safeToHtml($translation->get('keywords', 'keyword')); ?></label>
            <input type="text" name="keywords" value="<?php if(isset($_POST['keywords'])) echo safeToForm($_POST['keywords']); ?>" />
        </div>

        <div class="formrow">
            <label for="accessibility"><?php echo safeToHtml($translation->get('accessibility')); ?></label>
            <select name="accessibility">
                <option value="public"><?php echo safeToHtml($translation->get('public')); ?></option>
                <option value="intranet"><?php echo safeToHtml($translation->get('intranet')); ?></option>
            </select>
        </div>
    </fieldset>

    <p class="alert"><?php echo safeToHtml($translation->get('only click save when all files are uploaded')); ?>.</p>


    <p>
        <input type="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" />
         <a href="<?php print($redirect->getRedirect('index.php')); ?>"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a>
    </p>

</form>

<?php
$page->end();
?>