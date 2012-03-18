<h1><?php e(t('State procurement')); ?> #<?php e($procurement->get('number')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
    <li><a href="<?php e(url('../../', array('use_stored'=>'true'))); ?>"><?php e(t('To procurements')); ?></a></li>
</ul>

<div class="message">
    <p><?php e(t('Please verify manually whether the amounts has been stated correctly')); ?>.</p>
</div>

    <?php echo $procurement->error->view(); ?>

    <form action="<?php e(url()); ?>" method="post">

    <fieldset>
        <legend><?php e(t('procurement')); ?></legend>
        <table>
            <tr>
                <th><?php e(t("number")); ?></th>
                <td><?php e($procurement->get("number")); ?></td>
            </tr>
            <tr>
                <th><?php e(t('description')) ?></th>
                <td><?php autohtml($procurement->get("description")); ?></td>
            </tr>
            <tr>
                <th><?php e(t('date recieved')) ?></th>
                <td><?php e($procurement->get("dk_date_recieved")); ?></td>
            </tr>
            <?php
            /*
            <tr>
                 <th><?php e(t('price for items')) ?></th>
                <td><?php e($procurement->get("dk_price_items")); ?> kroner</td>
            </tr>
            <tr>
                <th><?php e(t('price for shipment etc')) ?></th>
                <td><?php e($procurement->get("dk_price_shipment_etc")); ?> kroner</td>
            </tr>

            <tr>
                <th><?php e(t('vat')) ?></th>
                <td><?php e($procurement->get("dk_vat")); ?> kroner</td>
            </tr>
            */
            ?>
            <tr>
                <th><?php e(t('total price')) ?></th>
                <td><?php e($procurement->get("dk_total_price")); ?> kroner</td>
            </tr>
            <tr>
                <td><?php e(t('buy from')) ?></td>
                <td><?php e(t($procurement->get('from_region'), 'procurement')); ?>
            </td>
        </tr>

        </table>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Information')); ?></legend>

        <div class="formrow">
            <label for="voucher_number"><?php e(t('voucher number')) ?></label>
            <input type="text" name="voucher_number" id="voucher_number" value="<?php e($voucher->getMaxNumber() + 1); ?>" />
        </div>

        <div class="formrow">
            <label for="voucher_date"><?php e(t('state on date')) ?></label>
            <input type="text" name="voucher_date" id="voucher_date" value="<?php e($procurement->get("dk_paid_date")); ?>" />
        </div>

        <div class="formrow">
            <label for="credit_account_id"><?php e(t('paid from account')) ?></label>
            <select name="credit_account_number">
                <option value=""><?php e(t('Choose')); ?></option>
                <?php
                foreach ($account->getList('finance') as $a):
                    if ($year->getSetting('debtor_account_id') == $a['id']) continue;
                    ?>
                    <option value="<?php e($a['number']); ?>"
                    <?php if (isset($value['credit_account_number']) && $a['number'] == $value['credit_account_number']) echo ' selected="selected"'; ?>
                    ><?php e($a['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </fieldset>

    <?php if (isset($value['debet_account']) && is_array($value['debet_account'])): ?>
    <table class="stripe">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th><?php e(t('Description')); ?></th>
                <th><?php e(t('Amount')); ?></th>
                <th><?php e(t('State on')); ?>...</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
               if (isset($value['debet_account']) && is_array($value['debet_account'])) {

                foreach ($value['debet_account'] AS $key => $line) {
                    ?>
                    <tr>
                        <td><?php e($key+1); ?></td>
                        <td><?php e($procurement->get('description')); ?> - <input type="text" name="debet_account[<?php e($key); ?>][text]" value="<?php e($line["text"]); ?>" /></td>
                        <td><input type="text" name="debet_account[<?php e($key); ?>][amount]" value="<?php e($line["amount"]); ?>" size="8" /> <?php e('('.t('excl. vat').')'); ?></td>
                        <td>
                            <select id="state_account" name="debet_account[<?php e($key); ?>][state_account_id]">
                                <option value=""><?php e(t('Choose')); ?></option>
                                <?php
                                foreach ($account->getList('expenses') AS $a):
                                    if (strtolower($a['type']) == 'sum') continue;
                                    if (strtolower($a['type']) == 'headline') continue;
                                    ?>
                                    <option value="<?php e($a['number']); ?>"
                                    <?php if (isset($line['state_account_id']) && $line['state_account_id'] == $a['number']) echo ' selected="selected"'; ?>
                                    ><?php e($a['name'].' ('.t('vat', 'accounting').': '.t($a['vat_shorthand'], 'accounting').')'); ?></option>

                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="submit" name="remove_line[<?php e($key); ?>]" value="<?php e(t('remove')); ?>" /></td>
                    </tr>
                    <?php
                }
            }
            ?>
            <?php if ($procurement->get('vat') > 0): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td><?php e($procurement->get('description'). ' - '.t('vat')); ?></td>
                    <td><?php e($procurement->get('dk_vat')); ?></td>
                    <td><?php e($vat_account->get('number') . ' ' . $vat_account->get('name')); ?></td>
                    <td>&nbsp;</td>
                 </tr>
             <?php endif; ?>

        </tbody>
    </table>
    <?php endif; ?>
    <div>
        <input type="submit" name="add_line" value="<?php e(t('add line')); ?>" />
    </div>
    <?php if (empty($value['debet_account'])): ?>
         <p class="warning"><?php e(t('Cannot state procurement with no items.')); ?> <a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></p>
    <?php else: ?>
    <div>
         <input type="submit" name="state" value="<?php e(t('State')); ?>" />
         <a href="<?php url('../'); ?>"><?php e(t('Cancel')); ?></a>
    </div>
    <?php endif; ?>

</form>
