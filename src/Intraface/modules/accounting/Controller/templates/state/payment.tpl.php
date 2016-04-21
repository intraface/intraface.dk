<?php // @todo should have state payment for xxxxxx $context->getType() ?>
<h1><?php e(t('State payment for ')); ?> #<?php e($payment->get('id')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../../../')); ?>">Luk</a></li>
</ul>

<?php if (!$year->readyForState($payment->get('payment_date'))) : ?>
    <?php echo $year->error->view(); ?>
    <p>Gå til <a href="<?php e($accounting_module->getPath().'years'); ?>">regnskabet</a></p>
<?php elseif ($payment->isStated()) : ?>
    <p><?php e(t('the payment is alredy stated')); ?>. <a href="<?php e($context->getKernel()->useModule('accounting')->getPath()).'voucher/'.$payment->get('voucher_id'); ?>"><?php e(t('see the voucher')); ?></a>.</p>
<?php else : ?>
    <?php
    // need to be executed to generate errors!
    $payment->readyForState();
    echo $payment->error->view();
    ?>

    <form action="<?php e(url()); ?>" method="post">
    <fieldset>
        <legend><?php e('payment'); ?></legend>
        <table>
            <tr>
                <th><?php e(t("payment type")); ?></th>
                <td><?php e(t($payment->get("type"))); ?></td>
            </tr>
            <tr>
                <th><?php e(t("date")); ?></th>
                <td><?php e($payment->get("dk_payment_date")); ?></td>
            </tr>
            <tr>
                <th><?php e(t("amount")); ?></th>
                <td><?php e(number_format($payment->get("amount"), 2, ',', '.')); ?></td>
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
            <input type="text" name="date_state" id="date_stated" value="<?php e($payment->get("dk_payment_date")); ?>" />
        </div>

        <p>Beløbet vil blive trukket fra debitorkontoen og blive sat på kontoen, du vælger herunder:</p>

        <div class="formrow">
            <label for="state_account"><?php e(t("state on account")); ?></label>
            <?php
            $account = new Account($year); // $product->get('state_account_id')

            $year = new Year($kernel);
            $year->loadActiveYear();
            $accounts =  $account->getList('finance');
            ?>
            <select id="state_account" name="state_account_id">
                <option value=""><?php e(t('Choose')); ?></option>
                <?php
                $x = 0;
                $default_account_id = $kernel->setting->get('intranet', 'payment.state.'.$payment->get('type').'.account');

                foreach ($accounts as $a) :
                    if (strtolower($a['type']) == 'sum') {
                        continue;
                    }
                    if (strtolower($a['type']) == 'headline') {
                        continue;
                    }
                    ?>
                    <option value="<?php e($a['number']); ?>"
                    <?php if ($default_account_id == $a['number']) {
                        echo ' selected="selected"';
} ?>
                    ><?php e($a['name']); ?></option>
                <?php                                                                                                                                                                                                                                                                                                                                                                                                                                                 endforeach;
                ?>
            </select>
        </div>
    </fieldset>

    <?php  if ($payment->readyForState()) : ?>
        <div>
            <input type="submit" value="<?php e(t('State')); ?>" />
            <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
        </div>
    <?php endif;  ?>
    </form>
<?php endif; ?>
