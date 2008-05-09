<?php
/**
 *
 * @author Sune Jensen <sj@sunet.dk>
 */
require('../../include_first.php');
$debtor_module = $kernel->module('debtor');
$invoice_module = $kernel->useModule('invoice');
$translation = $kernel->getTranslation('debtor');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if($_POST['for'] == 'invoice') {
        $object = new Invoice($kernel, intval($_POST["id"]));
        if($object->get('id') == 0) {
            trigger_error('Invalid debtor', E_USER_ERROR);
            exit;
        }
        if($object->get('type') != 'invoice') {
            trigger_error('Invalid debtor type given. Only invoice works: '.$object->get('type'), E_USER_ERROR);
            exit;
        }
        $for = 'invoice';
    }
    elseif($_POST['for'] == 'reminder') {
        $object = new Reminder($kernel, intval($_POST["id"]));
        if($object->get('id') == 0) {
            trigger_error('Invalid Reminder', E_USER_ERROR);
            exit;
        }
        $for = 'reminder';
    }
    else {
        trigger_error('Invalid for!', E_USER_ERROR);
        exit;
    }
    $depreciation = new Depreciation($object);
    if($depreciation->update($_POST)) {
        if($for == 'invoice') {
            if ($kernel->user->hasModuleAccess('accounting')) {
                header('location: state_depreciation.php?for=invoice&id=' . intval($object->get("id")).'&depreciation_id='.$depreciation->get('id'));
                exit;
            }
            else {
                header('location: view.php?id='.$object->get('id'));
                exit;
            }
        }
        elseif($for == 'reminder') {
            if ($kernel->user->hasModuleAccess('accounting')) {
                header('location: state_depreciation.php?for=reminder&id=' . intval($object->get("id")).'&depreciation_id='.$depreciation->get('id'));
                exit;
            }
            else {
                header('location: reminder.php?id='.$object->get('id'));
                exit;
            }
        }
    }
    
}
else {
    
    if($_GET['for'] == 'invoice') {
        $object = new Invoice($kernel, intval($_GET["id"]));
        if($object->get('id') == 0) {
            trigger_error('Invalid debtor', E_USER_ERROR);
            exit;
        }
        if($object->get('type') != 'invoice') {
            trigger_error('Invalid debtor type given. Only invoice works: '.$object->get('type'), E_USER_ERROR);
            exit;
        }
        $for = 'invoice';
    }
    elseif($_GET['for'] == 'reminder') {
        $object = new Reminder($kernel, intval($_GET["id"]));
        if($object->get('id') == 0) {
            trigger_error('Invalid Reminder', E_USER_ERROR);
            exit;
        }
        $for = 'reminder';
    }
    else {
        trigger_error('Invalid for!', E_USER_ERROR);
        exit;
    }
    $depreciation = new Depreciation($object);

    
}

$page = new Page($kernel);
$page->start(t('register depreciation for').' '.t($for));

?>

<h1><?php e(t('register depreciation for').' '.t($for).' #'.$object->get('number')); ?></h1>

<div class="message-dependent">
    <?php e(t('if the reason why you have not recieved the full amount of money is because the customer has returned parts of the sold goods, or you have agreed to reduce the price, you should rather send a credit note, to avoid paying the vat. depreciation should be used when you are not able to collect the money that you expected to get.')); ?>
</div>

<?php echo $depreciation->error->view(); ?>

<form method="post" action="register_depreciation.php">
<fieldset>
    <legend><?php e(t('depreciation')); ?></legend>
    
    <input type="hidden" name="id" value="<?php echo $object->get('id'); ?>" />
    <input type="hidden" name="for" value="<?php echo $for; ?>" />
    <div class="formrow">
        <label for="payment_date">Dato</label>
        <input type="text" name="payment_date" id="payment_date" value="<?php print(safeToHtml(date("d-m-Y"))); ?>" />
    </div>

    <div class="formrow">
        <label for="amount">Beløb</label>
        <input type="text" name="amount" id="amount" value="<?php print(number_format($object->get("arrears"), 2, ",", ".")); ?>" />
    </div>
</fieldset>
<input type="submit" name="depreciation" value="Registrér" />
<?php e(t('or', 'common')); ?>
<?php if($for == 'invoice'): ?>
    <a href="view.php?id=<?php e($object->get('id')); ?>"><?php e(t('regret', 'common')); ?></a>
<?php elseif($for == 'reminder'): ?>
    <a href="reminder.php?id=<?php e($object->get('id')); ?>"><?php e(t('regret', 'common')); ?></a>
<?php endif; ?>   
</form>
<?php
$page->end();
?>
    