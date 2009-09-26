<?php
require '../../include_first.php';

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
$page->start(__('State invoice'));

?>
<h1><?php e(t('state reminder')) ?> #<?php e($reminder->get('number')); ?></h1>

<ul class="options">
    <li><a href="reminder.php?id=<?php e($reminder->get("id")); ?>"><?php e(t('close', 'common')) ?></a></li>
</ul>

<?php if (!$year->readyForState($reminder->get('this_date'))): ?>
    <?php echo $year->error->view(); ?>
    <p><?php e(t('go to the')); ?> <a href="<?php e($accounting_module->getPath().'years.php'); ?>"><?php e(t('accounts')); ?></a></p>
<?php else: ?>

    <p class="message"><?php e(t('this function will only state the reminder fee on this reminder. all invoices and earlier reminder fees on the reminder should be stated on the corresponding invoices and reminders.')); ?></p>

    <?php $reminder->readyForState($year); ?>
    <?php echo $reminder->error->view(); ?>

    <fieldset>
        <legend><?php e(t('reminder')); ?></legend>
        <table>
            <tr>
                <th><?php e(__("reminder number")); ?></th>
                <td><?php e($reminder->get("number")); ?></td>
            </tr>
            <tr>
                <th><?php e(t('reminder date', 'common')); ?></th>
                <td><?php e($reminder->get("dk_this_date")); ?></td>
            </tr>
        </table>
    </fieldset>

    <?php  if ($reminder->readyForState($year)): ?>
        <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
        <input type="hidden" value="<?php e($reminder->get('id')); ?>" name="id" />
        <fieldset>
            <legend><?php e(t('Information to state')); ?></legend>

            <div class="formrow">
                <label for="voucher_number"><?php e(t('Voucher number')); ?></label>
                <input type="text" name="voucher_number" id="voucher_number" value="<?php e($voucher->getMaxNumber() + 1); ?>" />
            </div>

            <div class="formrow">
                <label for="date_state"><?php e(t('State on date')); ?></label>
                <input type="text" name="date_state" id="date_state" value="<?php e($reminder->get("dk_this_date")); ?>" />
            </div>


            <p><?php e(t('The reminder fee will be taken from the account below and set on the the debitor account.')); ?></p>

            <p><?php e(t('There is no vat on reminder fee, so it should be stated on an account without vat.')); ?></p>


            <div class="formrow">
                <label for="state_account"><?php e(__("State on account")); ?></label>
                <?php
                $account = new Account($year); // $product->get('state_account_id')

                $year = new Year($kernel);
                $year->loadActiveYear();
                $accounts =  $account->getList('operating');
                ?>
                <select id="state_account" name="state_account_id">
                    <option value=""><?php e(t('Choose')); ?>...</option>
                    <?php
                    $x = 0;
                    $default_account_id = $kernel->setting->get('intranet', 'reminder.state.account');

                    foreach ($accounts AS $a):
                        if (strtolower($a['type']) == 'sum') continue;
                        if (strtolower($a['type']) == 'headline') continue;
                        ?>
                        <option value="<?php e($a['number']); ?>"
                        <?php if ($default_account_id == $a['number']) echo ' selected="selected"'; ?>
                        ><?php e($a['name']); ?></option>
                    <?php endforeach;
                    ?>
                </select>
            </div>
        </fieldset>
        <div>
            <input type="submit" value="<?php e(t('State')); ?>" /> <?php e(t('or')); ?>
            <a href="view.php?id=<?php e($value['id']); ?>"><?php e(t('cancel')); ?></a>
        </div>
    <?php endif;  ?>
    </form>
<?php endif; ?>
<?php
$page->end();
?>