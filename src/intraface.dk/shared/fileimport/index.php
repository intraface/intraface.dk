<?php
require('../../include_first.php');

$shared_fileimport = $kernel->useShared('fileimport');
$shared_filehandler = $kernel->useShared('filehandler');
$translation = $kernel->getTranslation('fileimport');

$fileimport = new FileImport;

$redirect = Intraface_Redirect::receive($kernel);

if ($redirect->get('id') == 0) {
    trigger_error('we did not find a redirect, which is needed', E_USER_ERROR);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['upload_file'])) {

        $filehandler = new Filehandler($kernel);
        $filehandler->createUpload();

        if ($file_id = $filehandler->upload->upload('userfile', 'temporary')) {
            $filehandler = new FileHandler($kernel, $file_id);
            if ($filehandler->get('id') == 0) {
                trigger_error('unable to load file after upload', E_USER_ERROR);
                exit;
            }
            $parser = $fileimport->createParser('CSV');
            if ($values = $parser->parse($filehandler->get('file_path'), 0, 1)) {
                if (empty($values) || empty($values[0])) {
                    $fileimport->error->set('there was found no data in the file');
                }
                else {
                    // This is now only for contact!
                    $fields = array('number', 'name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean', 'type', 'paymentcondition', 'preferred_invoice', 'openid_url');
                    $translation_page_id = 'contact';
                    $mode = 'select_fields';
                }

            }
            $fileimport->error->merge($parser->error->getMessage());
        }
        $fileimport->error->merge($filehandler->error->getMessage());
    }
    elseif (isset($_POST['save'])) {
        $filehandler = new Filehandler($kernel, $_POST['file_id']);
        if ($filehandler->get('id') == 0) {
            trigger_error('unable to load data file', E_USER_ERROR);
            exit;
        }
        elseif (empty($_POST['fields']) || !is_array($_POST['fields'])) {
            trigger_error('there was no fields!', E_USER_ERROR);
            exit;
        }
        else {
            $parser = $fileimport->createParser('CSV');
            $parser->assignFieldNames($_POST['fields']);
            if (!empty($_POST['header'])) {
                $offset = 1;
            }
            else {
                $offset = 0;
            }

            if ($data = $parser->parse($filehandler->get('file_path'), $offset)) {

                //
                $_SESSION['shared_fileimport_data'] = $data;

                $redirect->setParameter('session_variable_name', 'shared_fileimport_data');
                if ($url = $redirect->getRedirect('')) {
                    header('location: '.$url);
                    exit;
                }
                else {
                    trigger_error('No redirect url was found.');
                    exit;
                }
            }

        }
    }
}

$page = new Intraface_Page($kernel);
$page->start($translation->get('import contacts'));
?>
<h1><?php e($translation->get('import contacts')); ?></h1>

<?php echo $fileimport->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">

<?php if (isset($mode) && $mode = 'select_fields'): ?>
    <fieldset>
        <legend><?php e($translation->get('select the fields for import')); ?></legend>
        <?php foreach ($values[0] AS $key => $value): ?>
            <div class="formrow">
                <label for="fields_<?php e($key); ?>"><?php e($value); ?></label>
                <select name="fields[<?php e($key); ?>]" id="fields_<?php e($key); ?>">
                    <option value="">[<?php e(__('ignore', 'common')); ?>]</option>
                    <?php foreach ($fields AS $field): ?>
                        <option value="<?php e($field); ?>"><?php e(__($field, $translation_page_id)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <fieldset>
        <legend><?php e(__('column header')); ?></legend>
        <div class="formrow">
            <label for="header"><?php e(__('dataset has column header')); ?></label>
            <input type="checkbox" name="header" id="header" value="1" />
        </div>
        <div style="clear:both;"><?php e(__('tip: if the fieldnames you see in the left column above is the first data record you want to import, your dataset does not have a header')); ?>.</div>
    </fieldset>

    <input type="hidden" name="file_id" value="<?php e($filehandler->get('id')); ?>" />

    <input type="submit" class="save" name="save" value="<?php e(__('select', 'common').'...'); ?>" />
    <?php e(__('or', 'common')); ?>
    <a href="<?php echo 'index.php'; ?>"><?php e(__('Cancel', 'common')); ?></a>

<?php else: ?>
    <fieldset>
        <legend><?php e(__('file')); ?></legend>

        <div><?php e(__('currently files in the CSV format are supported')); ?></div>

        <div class="formrow">
            <label for="userfile"><?php e(__('choose your file')); ?></label>
            <input name="userfile" type="file" id="userfile" />
        </div>
    </fieldset>

    <input type="submit" class="save" name="upload_file" value="<?php e(__('analyze file').'...'); ?>" />
    <?php e(__('or', 'common')); ?>
    <a href="<?php echo 'index.php'; ?>"><?php e(__('Cancel', 'common')); ?></a>
<?php endif; ?>
</form>

<?php
$page->end();
?>