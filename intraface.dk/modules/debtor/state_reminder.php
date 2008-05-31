<?php
require('../../include_first.php');

$debtor_module = $kernel->module('debtor');
$accounting_module = $kernel->useModule('invoice');
$accounting_module = $kernel->useModule('accounting');
$product_module = $kernel->useModule('product');
$translation = $kernel->getTranslation('debtor');

$year = new Year($kernel);
$voucher = new Voucher($year);

if (!empty($_POST)) {
    
    $reminder = new Reminder($kernel, intval($_POST["id"]));
    
    if ($reminder->error->isError()) {
        $reminder->loadItem();
    } elseif (!$reminder->state($year, $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $translation)) {
        $reminder->error->set('unable to state the reminder');
        $reminder->loadItem();
    } else {
        header('Location: reminder.php?id='.$reminder->get('id'));
        exit;
    }
} else {
    $reminder = new Reminder($kernel, intval($_GET["id"]));
    $value = $reminder->get();
}

$page = new Intraface_Page($kernel);
$page->start($translation->get('State invoice'));

?>
<h1><?php e(t('state reminder')) ?> #<?php echo safeToHtml($reminder->get('number')); ?></h1>

<ul class="options">
    <li><a href="reminder.php?id=<?php print(intval($reminder->get("id"))); ?>"><?php e(t('close', 'common')) ?></a></li>
</ul>

<?php if (!$year->readyForState($reminder->get('this_date'))): ?>
    <?php echo $year->error->view(); ?>
    <p><?php e(t('go to the')); ?> <a href="<?php echo $accounting_module->getPath().'years.php'; ?>"><?php e(t('accounts')); ?></a></p>
<?php else: ?>

    <p class="message"><?php e(t('this function will only state the reminder fee on this reminder. all invoices and earlier reminder fees on the reminder should be stated on the corresponding invoices and reminders.')); ?></p>
    
    <?php $reminder->readyForState($year); ?>  
    <?php echo $reminder->error->view(); ?>
    
    <fieldset>
        <legend><?php e(t('reminder')); ?></legend>
        <table>
            <tr>
                <th><?php print(safeToHtml($translation->get("reminder number"))); ?></th>
                <td><?php print(safeToHtml($reminder->get("number"))); ?></td>
            </tr>
            <tr>
                <th><?php e(t('reminder date', 'common')); ?></th>
                <td><?php print(safeToHtml($reminder->get("dk_this_date"))); ?></td>
            </tr>
        </table>
    </fieldset>
    
    <?php  if ($reminder->readyForState($year)): ?>
        <form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
        <input type="hidden" value="<?php echo intval($reminder->get('id')); ?>" name="id" />
        <fieldset>
            <legend>Oplysninger der bogføres</legend>
            
            <div class="formrow">
                <label for="voucher_number"><?php e(t('voucher number')); ?></label>
                <input type="text" name="voucher_number" id="voucher_number" value="<?php echo safeToHtml($voucher->getMaxNumber() + 1); ?>" />
            </div>
                
            <div class="formrow">
                <label for="date_state"><?php e(t('state on date')); ?></label>
                <input type="text" name="date_state" id="date_state" value="<?php echo safeToHtml($reminder->get("dk_this_date")); ?>" />
            </div>
            
            
            <p><?php e(t('the reminder fee will be taken from the account below and set on the the debitor account.')); ?></p>
                
            <p><?php e(t('there is no vat on reminder fee, so it should be stated on an account without vat.')); ?></p>
        
            
            <div class="formrow">
                <label for="state_account"><?php print(safeToHtml($translation->get("state on account"))); ?></label>
                <?php 
                $account = new Account($year); // $product->get('state_account_id')
            
                $year = new Year($kernel);
                $year->loadActiveYear();
                $accounts =  $account->getList('operating');
                ?>
                <select id="state_account" name="state_account_id">
                    <option value=""><?php e(t('choose', 'common')); ?>...</option>
                    <?php
                    $x = 0;
                    $default_account_id = $kernel->setting->get('intranet', 'reminder.state.account');
    
                    foreach($accounts AS $a):
                        if (strtolower($a['type']) == 'sum') continue;
                        if (strtolower($a['type']) == 'headline') continue;
                        
                        echo '<option value="'. $a['number'].'"';
                        if ($default_account_id == $a['number']) echo ' selected="selected"';
                        echo '>'.safeToForm($a['name']).'</option>';
                    endforeach;
                    ?>
                </select>
            </div>
        </fieldset>
        <div>
            <input type="submit" value="<?php e(t('state')); ?>" /> <?php e(t('or', 'common')); ?>
            <a href="view.php?id=<?php echo intval($value['id']); ?>"><?php e(t('regret', 'common')); ?></a>
        </div>
    <?php endif;  ?>
    </form>
<?php endif; ?>
<?php
$page->end();
?>