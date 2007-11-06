<?php
require('../../include_first.php');

$shared_fileimport = $kernel->useShared('fileimport');
$shared_filehandler = $kernel->useShared('filehandler');
$translation = $kernel->getTranslation('fileimport');

$fileimport = new FileImport;

$redirect = Redirect::receive($kernel);

if($redirect->get('id') == 0) {
    trigger_error('we did not find a redirect, which is needed', E_USER_ERROR);
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if(isset($_POST['upload_file'])) {
        
        $filehandler = new Filehandler($kernel);
        $filehandler->createUpload();
        
        if($file_id = $filehandler->upload->upload('userfile', 'temporary')) {
            $filehandler = new FileHandler($kernel, $file_id);
            if($filehandler->get('id') == 0) {
                trigger_error('unable to load file after upload', E_USER_ERROR);
                exit;
            }
            $parser = $fileimport->createParser('CSV');
            if($values = $parser->parse($filehandler->get('file_path'), 0, 1)) {
                if(empty($values) || empty($values[0])) {
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
    elseif(isset($_POST['save'])) {
        $filehandler = new Filehandler($kernel, $_POST['file_id']);
        if($filehandler->get('id') == 0) {
            trigger_error('unable to load data file', E_USER_ERROR);
            exit;
        }
        elseif(empty($_POST['fields']) || !is_array($_POST['fields'])) {
            trigger_error('there was no fields!', E_USER_ERROR);
            exit;
        }
        else {
            $parser = $fileimport->createParser('CSV');
            $parser->assignFieldNames($_POST['fields']);
            if(!empty($_POST['header'])) {
                $offset = 1;
            }
            else {
                $offset = 0;
            }
            
            if($data = $parser->parse($filehandler->get('file_path'), $offset)) {
                
                // 
                $_SESSION['shared_fileimport_data'] = $data;
                
                $redirect->setParameter('session_variable_name', 'shared_fileimport_data');
                if($url = $redirect->getRedirect('')) {
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

$page = new Page($kernel);
$page->start($translation->get('import contacts'));
?>
<h1><?php echo safeToHtml($translation->get('import contacts')); ?></h1>

<?php echo $fileimport->error->view(); ?> 

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
  
<?php if(isset($mode) && $mode = 'select_fields'): ?>
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('select the fields for import')); ?></legend>
        <?php foreach($values[0] AS $key => $value): ?>
            <div class="formrow">
                <label for="fields_<?php echo safeToHtml($key); ?>"><?php echo safeToHtml($value); ?></label>
                <select name="fields[<?php echo safeToHtml($key); ?>]" id="fields_<?php echo safeToHtml($key); ?>">
                    <option value="">[<?php echo safeToHtml($translation->get('ignore')); ?>]</option>
                    <?php foreach($fields AS $field): ?>
                        <option value="<?php echo safeToHtml($field); ?>"><?php echo safeToHtml($translation->get($field), $translation_page_id); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </fieldset>
    
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('header')); ?></legend>
        <div class="formrow">
            <label for="header"><?php echo safeToHtml($translation->get('dataset has header')); ?></label>
            <input type="checkbox" name="header" id="header" value="1" />
        </div>
        <div style="clear:both;"><?php echo safeToHtml($translation->get('tip: if the fieldnames you see in the left column above is the first data record you want to import, your dataset does not have a header')); ?>.</div>  
    </fieldset>    
    
    <input type="hidden" name="file_id" value="<?php echo intval($filehandler->get('id')); ?>" />
    
    <input type="submit" class="save" name="save" value="<?php echo safeToHtml($translation->get('select').'...'); ?>" />
    <?php echo safeToHtml($translation->get('or', 'common')); ?> 
    <a href="<?php echo 'index.php'; ?>"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a>
    
<?php else: ?>
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('file')); ?></legend>
            
        <div><?php echo safeToHtml($translation->get('currently files in the CSV format are supported')); ?></div>
            
        <div class="formrow">
            <label for="userfile"><?php echo safeToHtml($translation->get('choose your file')); ?></label>
            <input name="userfile" type="file" id="userfile" />
        </div>       
    </fieldset>
    
    <input type="submit" class="save" name="upload_file" value="<?php echo safeToHtml($translation->get('analyze file').'...'); ?>" />
    <?php echo safeToHtml($translation->get('or', 'common')); ?> 
    <a href="<?php echo 'index.php'; ?>"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a>
<?php endif; ?>
</form>

<?php
$page->end();
?>