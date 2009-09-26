<?php
require '../../include_first.php';

$debtor_module = $kernel->module('debtor');
$accounting_module = $kernel->useModule('accounting');
$kernel->useModule('invoice');
$translation = $kernel->getTranslation('debtor');

$year = new Year($kernel);
$voucher = new Voucher($year);

if (!empty($_POST)) {

    if (empty($_POST['for'])) {
        trigger_error('you need to provide what the depreciation is for', E_USER_ERROR);
        exit;
    }

    switch($_POST['for']) {
        case 'invoice':
            $object = new Invoice($kernel, intval($_POST["id"]));
            $for = 'invoice';
        break;
        case 'reminder':
            $object = new Reminder($kernel, intval($_POST['id']));
            $for = 'reminder';
        break;
        default:
            trigger_error('Invalid for', E_USER_ERROR);
            exit;
    }

    if ($object->get('id') == 0) {
        trigger_error('Invalid '.$for.' #'. $_POST["id"], E_USER_ERROR);
        exit;
    }
    $depreciation = new Depreciation($object, intval($_POST['depreciation_id']));
    if ($depreciation->get('id') == 0) {
        trigger_error('Invalid depreciation #'. $_POST["depreciation_id"], E_USER_ERROR);
        exit;
    }

    $kernel->setting->set('intranet', 'depreciation.state.account', intval($_POST['state_account_id']));

    if ($depreciation->error->isError()) {
        // nothing, we continue
    } elseif (!$depreciation->state($year, $_POST['voucher_number'], $_POST['date_state'], $_POST['state_account_id'], $translation)) {
        $depreciation->error->set('Kunne ikke bogføre posten');
    } else {

        if ($for == 'invoice') {
            header('Location: view.php?id='.$object->get('id'));
            exit;
        } elseif ($for == 'reminder') {
            header('Location: reminder.php?id='.$object->get('id'));
            exit;
        }
    }
} else {
    if (empty($_GET['for'])) {
        trigger_error('you need to provide what the depreciation is for', E_USER_ERROR);
        exit;
    }

    switch($_GET['for']) {
        case 'invoice':
            $object = new Invoice($kernel, intval($_GET["id"]));
            $for = 'invoice';
        break;
        case 'reminder':
            $object = new Reminder($kernel, intval($_GET['id']));
            $for = 'reminder';
        break;
        default:
            trigger_error('Invalid for', E_USER_ERROR);
            exit;
    }

    $depreciation = new Depreciation($object, $_GET['depreciation_id']);

}

$page = new Intraface_Page($kernel);
$page->start(__('state depreciation for '.$for));

?>
<h1><?php e(__('state depreciation for '.$for)); ?> #<?php e($object->get('number')); ?></h1>

<ul class="options">
    <?php if ($for == 'invoice'): ?>
        <li><a href="view.php?id=<?php e($object->get("id")); ?>">Luk</a></li>
    <?php elseif ($for == 'reminder'): ?>
        <li><a href="reminder.php?id=<?php e($object->get("id")); ?>">Luk</a></li>
    <?php endif; ?>
</ul>


<?php if (!$year->readyForState($depreciation->get('payment_date'))): ?>
    <?php echo $year->error->view(); ?>
    <p>Gå til <a href="<?php e($accounting_module->getPath().'years.php'); ?>">regnskabet</a></p>
<?php elseif ($depreciation->isStated()): ?>
    <p><?php e(t('the depreciation is alredy stated')); ?>. <a href="<?php e($accounting_module->getPath()).'voucher.php?id='.$depreciation->get('voucher_id'); ?>"><?php e(t('see the voucher')); ?></a>.</p>
<?php else: ?>
    <?php
    // need to be executed to generate errors!
    $depreciation->readyForState();
    echo $depreciation->error->view();
    ?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" value="<?php e($object->get('id')); ?>" name="id" />
    <input type="hidden" value="<?php e($for); ?>" name="for" />
    <input type="hidden" value="<?php e($depreciation->get('id')); ?>" name="depreciation_id" />
    <fieldset>
        <legend><?php e('depreciation'); ?></legend>
        <table>
            <tr>
                <th><?php e(__("date")); ?></th>
                <td><?php e($depreciation->get("dk_payment_date")); ?></td>
            </tr>
            <tr>
                <th><?php e(__("amount")); ?></th>
                <td><?php e(number_format($depreciation->get("amount"), 2, ',', '.')); ?></td>
            </tr>
        </table>
    </fieldset>

    <fieldset>
        <legend>Oplysninger der bogføres</legend>

        <div class="formrow">
            <label for="voucher_number">Bilagsnummer</label>
            <input type="text" name="voucher_number" id="voucher_number" value="<?php e($voucher->getMaxNumber() + 1); ?>" />
        </div>

        <div class="formrow">
            <label for="date_stated">Bogfør på dato</label>
            <input type="text" name="date_state" id="date_stated" value="<?php e($depreciation->get("dk_payment_date")); ?>" />
        </div>

        <p>Beløbet vil blive trukket fra debitorkontoen og blive sat på kontoen, du vælger herunder:</p>

        <div class="formrow">
            <label for="state_account"><?php e(__("state on account")); ?></label>
            <?php
            $account = new Account($year); // $product->get('state_account_id')

            $year = new Year($kernel);
            $year->loadActiveYear();
            $accounts =  $account->getList('operating');
            ?>
            <select id="state_account" name="state_account_id">
                <option value="">Vælg...</option>
                <?php
                $x = 0;
                $default_account_id = $kernel->setting->get('intranet', 'depreciation.state.account');

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

    <?php  if ($depreciation->readyForState()): ?>
        <div>
            <input type="submit" value="Bogfør" /> eller
            <a href="view.php?id=<?php e($object->get('id')); ?>">fortryd</a>
        </div>
    <?php endif;  ?>
    </form>
<?php endif; ?>
<?php
$page->end();
?>