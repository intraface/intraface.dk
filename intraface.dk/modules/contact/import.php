<?php
require('../../include_first.php');
require_once 'Services/Eniro.php';

$contact_module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');



if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $data = $_SESSION['shared_fileimport_data'];
    
    if(!is_array($data) || count($data) == 0) {
        trigger_error('This is not a valid dataset!', E_USER_ERROR);
        exit;
    }
    
    $errors = array();
    $e = 0;
    $success = 0;
    
    foreach($data AS $line => $contact) {
        
        $contact_object = new Contact($kernel);
        
        if($contact_object->save($contact)) {
            
            $contact_object->getKeywords();
            $contact_object->keywords->addKeywordsByString($_POST['keyword']);
            $success++;
        }
        else {
            $errors[$e]['line'] = $line;
            $errors[$e]['name'] = $contact['name'];
            $errors[$e]['email'] = $contact['email'];
            $errors[$e]['error'] = $contact_object->error;
        }        
    }
    
    unset($_SESSION['shared_fileimport_data']);    
}
else {
    
    $redirect = Redirect::returns($kernel);
    if($redirect->getId('id') == 0) {
        trigger_error('we did not recive a redirect', E_USER_ERROR);
        exit;
    }
    
    if($redirect->getParameter('session_variable_name') != 'shared_fileimport_data') {
        trigger_error('the session variable name must have been changed as it is not the same anymore: "'.$redirect->getParameter('session_variable_name').'"', E_USER_ERROR);
        exit;
    }
    
    $data = $_SESSION['shared_fileimport_data'];
}

$page = new Page($kernel);
$page->start($translation->get('import contacts'));
?>
<h1><?php echo safeToHtml($translation->get('import contacts')); ?></h1>

<?php /* echo $contact->error->view(); */ ?> 

<?php if(isset($success) && isset($errors)): ?>

    <fieldset>
        <legend><?php echo safeToHtml($translation->get('imported contacts')); ?></legend>
            
        <div><?php echo safeToHtml(sprintf($translation->get('%d records was imported successfully'), $success)); ?></div>          
    </fieldset>
    
    <h3><?php echo safeToHtml($translation->get('errors')); ?></h3>
    
    <?php if(count($errors) == 0): ?>
        <div><?php echo safeToHtml($translation->get('lucky you - no errors in import')); ?></div>          
    <?php else: ?>
        
        <?php foreach($errors AS $error): ?>            
            <div><?php echo safeToHtml(sprintf($translation->get('error in line %d. unable to import %s <%s>'), $error['line'], $error['name'], $error['email'])); ?></div>
            <?php echo $error['error']->view($translation); ?>       
        <?php endforeach; ?>
    <?php endif; ?>    

<?php else: ?>

    <form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
      
    
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('data')); ?></legend>
            
        <div><?php echo safeToHtml(sprintf($translation->get('there are %d records to import'), count($data))); ?></div>
               
    </fieldset>
    
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('keywords')); ?></legend>
        
        <div class="formrow">
            <label for="keyword"><?php echo safeToHtml($translation->get('keywords')); ?></label>
            <input type="text" name="keyword" id="keyword" value="" /> <?php echo safeToHtml($translation->get('separated by comma')); ?>
        </div>
    </fieldset>
    
    <?php /* if($kernel->user->hasModuleAccess('newsletter')): ?>
        <?php
        $module_newsletter = $kernel->useModule('newsletter');
        $newsletter_list = new NewsletterList($kernel);
        $list = $newsletter_list->getList();
        ?>
        <fieldset>
            <legend><?php echo safeToHtml($translation->get('newsletter')); ?></legend>
            
            <?php foreach($list AS $newsletter): ?>
                <div id="formrow">
                    <label for="newsletter_<?php echo intval($newsletter['id']); ?>"><?php echo safeToHtml($newsletter['title']); ?></label>
                    <input type="checkbox" name="newsletter[<?php echo intval($newsletter['id']); ?>]" id="newsletter_<?php echo intval($newsletter['id']); ?>" id="1" />
                </div>
            <?php endforeach; ?>
        </fieldset>
    <?php endif; */ ?>
    
    <input type="submit" class="save" name="submit" value="<?php echo safeToHtml($translation->get('import')); ?>" />
    <?php echo safeToHtml($translation->get('or', 'common')); ?> 
    <a href="<?php echo 'index.php'; ?>"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a>
    
    </form>
<?php endif; ?>
<?php
$page->end();
?>