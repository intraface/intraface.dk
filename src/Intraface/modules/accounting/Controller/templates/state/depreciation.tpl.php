<h1><?php e(__('State depreciation for '.$context->getType())); ?> #<?php e($object->get('number')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php if (!$context->getYear()->readyForState($depreciation->get('payment_date'))): ?>
    <?php echo $context->getYear()->error->view(); ?>
    <p>G� til <a href="<?php e($accounting_module->getPath().'years.php'); ?>">regnskabet</a></p>
<?php elseif ($depreciation->isStated()): ?>
    <p><?php e(t('the depreciation is alredy stated')); ?>. <a href="<?php e($accounting_module->getPath()).'voucher.php?id='.$depreciation->get('voucher_id'); ?>"><?php e(t('see the voucher')); ?></a>.</p>
<?php else: ?>
    <?php
    // need to be executed to generate errors!
    $depreciation->readyForState();
    echo $depreciation->error->view();
    ?>

    <form action="<?php e(url()); ?>" method="post">
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
        <legend>Oplysninger der bogf�res</legend>

        <div class="formrow">
            <label for="voucher_number">Bilagsnummer</label>
            <input type="text" name="voucher_number" id="voucher_number" value="<?php e($context->getVoucher()->getMaxNumber() + 1); ?>" />
        </div>

        <div class="formrow">
            <label for="date_stated">Bogf�r p� dato</label>
            <input type="text" name="date_state" id="date_stated" value="<?php e($depreciation->get("dk_payment_date")); ?>" />
        </div>

        <p>Bel�bet vil blive trukket fra debitorkontoen og blive sat p� kontoen, du v�lger herunder:</p>

        <div class="formrow">
            <label for="state_account"><?php e(__("state on account")); ?></label>
            <?php
            $account = new Account($context->getYear()); // $product->get('state_account_id')

            $accounts =  $account->getList('operating');
            ?>
            <select id="state_account" name="state_account_id">
                <option value="">V�lg...</option>
                <?php
                $x = 0;
                $default_account_id = $context->getKernel()->setting->get('intranet', 'depreciation.state.account');

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
            <input type="submit" value="<?php e(t('State')); ?>" />
            <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
        </div>
    <?php endif;  ?>
    </form>
<?php endif; ?>
