<?php
$values = $context->getValues();
?>
            <caption>Udgifter</caption>
            <thead>
                <tr>
                    <th><label for="date">Dato</label></th>
                    <th><label for="voucher_number">Bilag</label></th>
                    <th><label for="text">Bilagstekst</label></th>
                    <th><label for="buy_account_number">Købskonto</label></th>
                    <th><label for="buy_balance_account">Modpost</label></th>
                    <th><label for="amount">Beløb</label></th>
                    <th><label for="reference">Reference</label></th>
                    <?php if ($context->getYear()->get('vat') > 0) : ?>
                    <th><label for="vat_on">U. moms</label></th>
                    <?php endif; ?>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input tabindex="1" name="date" type="text" size="7" value="<?php e($values['date']);  ?>" />
                    </td>
                    <td>
                        <input tabindex="2" name="voucher_number" id="voucher_number" type="text" size="5" value="<?php e($values['voucher_number']); ?>" />
                    </td>
                    <td>
                        <input tabindex="3" type="text" name="text" id="text" value="<?php e($values['text']); ?>" />
                    </td>
                    <td>
                        <select name="debet_account_number" id="buy_account_number_select" tabindex="4">
                            <option value=""><?php e(t('Choose')); ?></option>
                            <?php foreach ($context->getAccount()->getList('expenses') as $a) : ?>
                                <option value="<?php e($a['number']); ?>"
                                    <?php if ($values['debet_account_number'] == $a['number']) {
                                        echo ' selected="selected"';
} ?>
                                    ><?php e($a['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="credit_account_number" id="balance_account_number_select" tabindex="4">
                            <option value=""><?php e(t('Choose')); ?></option>
                            <?php foreach ($context->getAccount()->getList('finance') as $a) : ?>
                             <option value="<?php e($a['number']); ?>"
                                    <?php if ($values['credit_account_number'] == $a['number']) {
                                        echo ' selected="selected"';
} ?>
                                 ><?php e($a['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input tabindex="6" name="amount" id="amount" type="text" size="8"  value="<?php e($values['amount']); ?>" />
                    </td>
                    <td>
                        <input tabindex="7" name="reference" id="reference" type="text" size="7"  value="<?php if (!empty($values['reference'])) {
                            e($values['reference']);
} ?>" />
                    </td>
                    <?php if ($context->getYear()->get('vat') > 0) : ?>
                    <td>
                        <input tabindex="8" name="vat_off" id="vat_off" type="checkbox" value="1" />
                    </td>
                    <?php endif; ?>
                    <td>
                        <input tabindex="9" type="submit" id="submit" value="Gem" />
                    </td>
                </tr>
            </tbody>
