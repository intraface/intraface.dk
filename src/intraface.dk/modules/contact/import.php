<?php
require '../../include_first.php';

$contact_module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = $_SESSION['shared_fileimport_data'];

    if (!is_array($data) || count($data) == 0) {
        trigger_error('This is not a valid dataset!', E_USER_ERROR);
        exit;
    }

    $errors = array();
    $e = 0;
    $success = 0;

    foreach ($data AS $line => $contact) {

        $contact_object = new Contact($kernel);

        if ($contact_object->save($contact)) {

            $appender = $contact_object->getKeywordAppender();
            $string_appender = new Intraface_Keyword_StringAppender($contact_object->getKeywords(), $appender);

            $string_appender->addKeywordsByString($_POST['keyword']);
            $success++;
        }
        else {
            $errors[$e]['line'] = $line+1; /* line starts at 0 */
            $errors[$e]['name'] = $contact['name'];
            $errors[$e]['email'] = $contact['email'];
            $errors[$e]['error'] = $contact_object->error;
            $e++;
        }
    }

    unset($_SESSION['shared_fileimport_data']);
}
else {

    $redirect = Intraface_Redirect::returns($kernel);
    if ($redirect->getId('id') == 0) {
        trigger_error('we did not recive a redirect', E_USER_ERROR);
        exit;
    }

    if ($redirect->getParameter('session_variable_name') != 'shared_fileimport_data') {
        trigger_error('the session variable name must have been changed as it is not the same anymore: "'.$redirect->getParameter('session_variable_name').'"', E_USER_ERROR);
        exit;
    }

    $data = $_SESSION['shared_fileimport_data'];
}

$page = new Intraface_Page($kernel);
$page->start(__('import contacts'));
?>
<h1><?php e(__('import contacts')); ?></h1>

<?php // echo $contact->error->view(); ?>

<?php if (isset($success) && isset($errors)): ?>

    <fieldset>
        <legend><?php e(__('imported contacts')); ?></legend>

        <div><?php e(sprintf(__('%d records was imported successfully'), $success)); ?></div>
    </fieldset>

    <h3><?php e(__('errors')); ?></h3>

    <?php if (count($errors) == 0): ?>
        <div><?php e(__('lucky you - no errors in import')); ?></div>
    <?php else: ?>

        <?php foreach ($errors AS $error): ?>
            <div><?php e(sprintf(__('error in line %d. unable to import %s <%s>'), $error['line'], $error['name'], $error['email'])); ?></div>
            <?php echo $error['error']->view($translation); ?>
        <?php endforeach; ?>
    <?php endif; ?>

<?php else: ?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">


    <fieldset>
        <legend><?php e(__('data')); ?></legend>

        <div><?php e(sprintf(__('there are %d records to import'), count($data))); ?></div>

    </fieldset>

    <fieldset>
        <legend><?php e(__('keywords')); ?></legend>

        <div class="formrow">
            <label for="keyword"><?php e(__('keywords')); ?></label>
            <input type="text" name="keyword" id="keyword" value="" /> <?php e(__('separated by comma')); ?>
        </div>
    </fieldset>

    <?php /* if ($kernel->user->hasModuleAccess('newsletter')): ?>
        <?php
        $module_newsletter = $kernel->useModule('newsletter');
        $newsletter_list = new NewsletterList($kernel);
        $list = $newsletter_list->getList();
        ?>
        <fieldset>
            <legend><?php e(__('newsletter')); ?></legend>

            <?php foreach ($list AS $newsletter): ?>
                <div id="formrow">
                    <label for="newsletter_<?php echo intval($newsletter['id']); ?>"><?php e($newsletter['title']); ?></label>
                    <input type="checkbox" name="newsletter[<?php echo intval($newsletter['id']); ?>]" id="newsletter_<?php echo intval($newsletter['id']); ?>" id="1" />
                </div>
            <?php endforeach; ?>
        </fieldset>
    <?php endif; */ ?>

    <input type="submit" class="save" name="submit" value="<?php e(__('import')); ?>" />
    <?php e(__('or', 'common')); ?>
    <a href="<?php echo 'index.php'; ?>"><?php e(__('Cancel', 'common')); ?></a>

    </form>
<?php endif; ?>
<?php
$page->end();
?>