<?php
$values = $context->getValues();
?>
            <caption>Standardvisning</caption>
            <thead>
                <tr>
                    <th><label for="date">Dato</label></th>
                    <th><label for="voucher_number">Bilag</label></th>
                    <th><label for="text">Bilagstekst</label></th>
                    <th><label for="debet_account_number">Debet</label></th>
                    <th><label for="credit_acount_number">Kredit</label></th>
                    <th><label for="amount">Beløb</label></th>
                    <th><label for="reference">Reference</label></th>
                    <th><label for="vat_on">U. moms</label></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input tabindex="1" accesskey="1" name="date" id="date" type="text" size="7" value="<?php e($values['date']);  ?>" />
                    </td>
                    <td>
                        <input tabindex="2" name="voucher_number" id="voucher_number" type="text" size="5" value = "<?php e($values['voucher_number']); ?>" />
                    </td>
                    <td>
                        <input tabindex="3" type="text" name="text" id="text" value="<?php e($values['text']); ?>" size="20" />
                    </td>
                    <td>
                        <input tabindex="4" type="text" name="debet_account_number" id="debet_account_number" value="<?php e($values['debet_account_number']);  ?>" size="8" />
                        <a href="<?php e($context->url('../account/popup')); ?>" id="debet_account_open">+</a>
                        <div id="debet_account_name">&nbsp;</div>
                    </td>
                    <td>
                        <input tabindex="5" type="text" name="credit_account_number" id="credit_account_number" value="<?php e($values['credit_account_number']); ?>" size="8" />
                        <a href="<?php e($context->url('../account/popup')); ?>" id="credit_account_open">+</a>
                        <div id="credit_account_name">&nbsp;</div>
                    </td>
                    <td>
                        <input tabindex="6" name="amount" id="amount" type="text" size="8" value="<?php e($values['amount']); ?>"  />
                    </td>
                    <td>
                        <input tabindex="7" name="reference" id="reference" type="text" size="7" value="<?php if (!empty($values['reference'])) e($values['reference']);  ?>"  />
                    </td>
                    <?php if ($context->getYear()->get('vat') > 0): ?>
                    <td>
                        <input tabindex="8" name="vat_off" id="vat_off" type="checkbox" value="1" />
                    </td>
                    <?php endif; ?>
                    <td>
                        <input tabindex="9" type="submit" id="submit" value="Gem" />
                    </td>
                </tr>
            </tbody>
