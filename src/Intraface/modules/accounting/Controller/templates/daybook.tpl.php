<?php
$values = $context->getValues();
$posts = $context->getPostsInDraft();
?>
<h1><?php e(t('Daybook for')); ?> <a href="<?php e(url('../year/' . $context->getYear()->getId())); ?>"><?php e($context->getYear()->get('label')); ?></a></h1>

<?php if (!$context->getAccount()->anyAccounts()): ?>
    <p class="message-dependent">Du skal f�rst oprette nogle konti, inden du kan taste poster ind i regnskabet. Du kan oprette en standardkontoplan under <a href="<?php e($context->url('../year/' . $context->getYear()->get('id'))); ?>">regnskabs�ret</a>.</p>
<?php elseif ($context->getYear()->get('vat') == 1 AND !$context->getYear()->vatAccountIsSet()): ?>
    <p class="message-dependent">Du har ikke sat momskonti.
    <a href="<?php e(url('../settings')); ?>">Gå til indstillingerne</a>.
    </p>
<?php else: ?>
    <?php if ($context->getKernel()->getSetting()->get('user', 'accounting.daybook.message') == 'view'): ?>
    <div class="message">
    <p>
        <strong>Kassekladde</strong>. Her opretter du poster til dit regnskab. I f�rste omgang figurerer bel�bene kun i kassekladden og under <a href="<?php e($context->url('state')); ?>">afstemningen</a>. Indtil du bogf�rer posterne, kan du stadig n� at redigere dem.
    </p>
    <p><strong>Hjælp</strong>. Du kan bogføre ved at indtaste kontonumrene i standardvisningen, men du kan ogs� bruge vores hj�lpefunktioner ved at klikke p� vores links nedenunder.</p>
    <p><a href="<?php e($context->url(null, array('message' => 'hide'))); ?>">Skjul</a></p>
    </div>
    <?php endif; ?>
    <form method="post" action="<?php e(url(null, array('view' => $context->query('view')))); ?>" id="accounting-form-state">
        <input type="hidden" name="id" value="<?php e($values['id']); ?>" />

    <?php echo $context->getVoucher()->error->view(); ?>

    <ul class="options">
        <li><a href="<?php e(url(null, array('view' => 'classic'))); ?>">Standard</a></li>
        <li><a href="<?php e(url(null, array('view' => 'income'))); ?>">Indt�gter</a></li>
        <li><a href="<?php e(url(null, array('view' => 'expenses'))); ?>">Udgifter</a></li>
        <li><a href="<?php e(url(null, array('view' => 'debtor'))); ?>">Betalende debitor</a></li>
    </ul>

    <fieldset>
        <legend>Indtast</legend>
        <table>
        <?php //if ($context->getKernel()->setting->get('user', 'accounting.daybook_view') == 'expenses'): ?>
        <?php if ($context->query('view') == 'expenses'): ?>
            <caption>Udgifter</caption>
            <thead>
                <tr>
                    <th><label for="date">Dato</label></th>
                    <th><label for="voucher_number">Bilag</label></th>
                    <th><label for="text">Bilagstekst</label></th>
                    <th><label for="buy_account_number">K�bskonto</label></th>
                    <th><label for="buy_balance_account">Modpost</label></th>
                    <th><label for="amount">Bel�b</label></th>
                    <th><label for="reference">Reference</label></th>
                    <?php if ($context->getYear()->get('vat') > 0): ?>
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
                            <option value="">V�lg</option>
                            <?php foreach ($context->getAccount()->getList('expenses') AS $a): ?>
                                <option value="<?php e($a['number']); ?>"
                                    <?php if ($values['debet_account_number'] == $a['number']) echo ' selected="selected"'; ?>
                                    ><?php e($a['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="credit_account_number" id="balance_account_number_select" tabindex="4">
                            <option value="">V�lg</option>
                            <?php foreach ($context->getAccount()->getList('finance') AS $a): ?>
                             <option value="<?php e($a['number']); ?>"
                                 <?php if ($values['credit_account_number'] == $a['number']) echo ' selected="selected"'; ?>
                                 ><?php e($a['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input tabindex="6" name="amount" id="amount" type="text" size="8"  value="<?php e($values['amount']); ?>" />
                    </td>
                    <td>
                        <input tabindex="7" name="reference" id="reference" type="text" size="7"  value="<?php if (!empty($values['reference'])) e($values['reference']); ?>" />
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
        <?php //elseif ($context->getKernel()->setting->get('user', 'accounting.daybook_view') == 'income'): ?>
        <?php elseif ($context->query('view') == 'income'): ?>
            <caption>Indt�gter</caption>
            <thead>
                <tr>
                    <th><label for="date">Dato</label></th>
                    <th><label for="voucher_number">Bilag</label></th>
                    <th><label for="text">Bilagstekst</label></th>
                    <th><label for="sales_balance_account">Modkonto</label></th>
                    <th><label for="sales_account_number">Salgskonto</label></th>
                    <th><label for="amount">Bel�b</label></th>
                    <th><label for="reference">Reference</label></th>
                    <?php if ($context->getYear()->get('vat') > 0): ?>
                    <th><label for="vat_on">U. moms</label></th>
                    <?php endif; ?>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input tabindex="1" accesskey="1" name="date" type="text" size="7" value="<?php e($values['date']);  ?>" />
                    </td>
                    <td>
                        <input tabindex="2" name="voucher_number" id="voucher_number" type="text" size="5" value = "<?php e($values['voucher_number']); ?>" />
                    </td>
                    <td>
                        <input tabindex="3" type="text" name="text" id="text" value="<?php e($values['text']); ?>" />
                    </td>
                    <td>
                        <select name="debet_account_number" id="balance_account_number_select" tabindex="4">
                            <option value="">V�lg</option>
                            <?php foreach ($context->getAccount()->getList('finance') AS $a): ?>
                                <option value="<?php e($a['number']); ?>"
                                    <?php if ($values['debet_account_number'] == $a['number']) echo ' selected="selected"'; ?>
                                    ><?php e($a['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="credit_account_number" id="sales_account_number_select" tabindex="5">
                            <option value="">V�lg</option>
                            <?php foreach ($context->getAccount()->getList('income') AS $a): ?>
                                <option value="<?php e($a['number']); ?>"
                                    <?php if ($values['credit_account_number'] == $a['number']) echo ' selected="selected"'; ?>
                                    ><?php e($a['name']); ?></option>
                                <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input tabindex="6" name="amount" id="amount" type="text" size="8"  value="<?php e($values['amount']); ?>"/>
                    </td>
                    <td>
                        <input tabindex="7" name="reference" id="reference" type="text" size="7" value="<?php if (!empty($values['reference'])) e($values['reference']); ?>" />
                    </td>
                    <?php if ($context->getYear()->get('vat') > 0): ?>
                    <td>
                        <input tabindex="8" name="vat_off" id="vat_off" type="checkbox" value="1" />
                    </td>
                    <?php endif; ?>
                    <td>
                        <input tabindex="9" type="submit" value="Gem" id="submit" />
                    </td>
                </tr>
            </tbody>

        <?php // elseif ($context->getKernel()->setting->get('user', 'accounting.daybook_view') == 'debtor'): ?>
        <?php elseif ($context->query('view') == 'debtor'): ?>
            <caption>Debitorbetaling</caption>
            <thead>
                <tr>
                    <th><label for="date">Dato</label></th>
                    <th><label for="voucher_number">Bilag</label></th>
                    <th><label for="text">Bilagstekst</label></th>
                    <th><label for="debitor_balance_account">Finanskonto</label></th>
                    <th><label for="debitor_account_number">Debitorkonto</label></th>
                    <th><label for="amount">Bel�b</label></th>
                    <th><label for="reference">Reference</label></th>
                    <!--
                    <?php if ($context->getYear()->get('vat') > 0): ?>
                    <th><label for="vat_on">U. moms</label></th>
                    <?php endif; ?>
                    -->
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input tabindex="1" accesskey="1" name="date" type="text" size="7" value="<?php e($values['date']);  ?>" />
                    </td>
                    <td>
                        <input tabindex="2" name="voucher_number" id="voucher_number" type="text" size="5" value="<?php e($values['voucher_number']); ?>" />
                    </td>
                    <td>
                        <input tabindex="3" type="text" name="text" id="text" value="<?php e($values['text']); ?>" />
                    </td>
                    <td>
                        <select name="debet_account_number" id="debitor_account_number_select" tabindex="4">
                            <option value="">V�lg</option>
                            <?php foreach ($context->getAccount()->getList('finance') AS $a): ?>
                                    <?php if ($context->getYear()->getSetting('debtor_account_id') == $a['id']) continue; ?>
                                    <option value="<?php e($a['number']); ?>"
                                    <?php if ($values['debet_account_number'] == $a['number']) echo ' selected="selected"'; ?>
                                    ><?php e($a['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input tabindex="5" type="text" name="credit_account_number" id="credit_account_number" value="<?php if (empty($values['credit_account_number'])) { $account = new Account($context->getYear(), $context->getYear()->getSetting('debtor_account_id')); e($context->getAccount()->get('number')); } else { e($values['credit_account_number']); }?>" size="8" />
                        <a href="daybook_list_accounts.php" id="credit_account_open">+</a>
                        <div id="credit_account_name">&nbsp;</div>
                    </td>
                    <td>
                        <input tabindex="6" name="amount" id="amount" type="text" value="<?php e($values['amount']); ?>" size="8" />
                    </td>
                    <td>
                        <input tabindex="7" name="reference" id="reference" type="text" size="7"  value="<?php if (!empty($values['reference'])) e($values['reference']); ?>" />
                    </td>
                    <!--
                    <?php if ($context->getYear()->get('vat') > 0): ?>
                    <td>
                        <input tabindex="8" name="vat_off" id="vat_off" type="checkbox" value="1" />
                    </td>
                    <?php endif; ?>
                    -->
                    <td>
                        <input tabindex="9" type="submit" value="Gem" id="submit" />
                    </td>
                </tr>
            </tbody>

        <?php else: ?>
            <caption>Standardvisning</caption>
            <thead>
                <tr>
                    <th><label for="date">Dato</label></th>
                    <th><label for="voucher_number">Bilag</label></th>
                    <th><label for="text">Bilagstekst</label></th>
                    <th><label for="debet_account_number">Debet</label></th>
                    <th><label for="credit_acount_number">Kredit</label></th>
                    <th><label for="amount">Bel�b</label></th>
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
        <?php endif;?>
    </table>
</fieldset>
</form>

<?php if (count($context->getPostsInDraft()) > 0): // tabellen skal kun vises hvis der er poster ?>

<table class="stripe">
<caption>Poster i kassekladden</caption>
<thead>
    <tr>
        <th>Dato</th>
        <th>Bilag</th>
        <th>Tekst</th>
        <th>Konto</th>
        <th>Debet</th>
        <th>Kredit</th>
        <th>Reference</th>
        <th>&nbsp;</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($context->getPostsInDraft() as $p): ?>
    <tr>
        <td><?php e($p['date_dk']); ?></td>
        <td><a href="<?php e(url('../voucher/' . $p['voucher_id'])); ?>"><?php e($p['voucher_number']); ?></a></td>
        <td><?php e($p['text']); ?></td>
        <td><a href="<?php e(url('../account/' . $p['account_id'])); ?>"><?php e($p['account_name']); ?></a></td>
        <td class="amount"><?php e(amountToOutput($p['debet'])); ?></td>
        <td class="amount"><?php e(amountToOutput($p['credit'])); ?></td>
        <td><?php if (!empty($p['reference'])) e($p['reference']); ?></td>
        <td><a href="<?php e(url('../voucher/' . $p['voucher_id'])); ?>">Se bilag</a></td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>

<?php if (round($context->getPost()->get('list_saldo'), 2) == 0.00): // this is a hack - can be removed when the database uses mindste enhed ?>
    <p class="advice"><a href="<?php e(url('state')); ?>">Bogfør posterne</a></p>
<?php else: ?>
    <p class="error">Kassekladden stemmer ikke. Der er en difference p� <?php e(amountToOutput($post->get('list_saldo'))); ?>.</p>
<?php endif; ?>

<?php else: ?>
    <p>Der er ikke nogen poster i kassekladden.</p>
<?php endif; ?>


<?php if ($context->getKernel()->setting->get('user', 'accounting.daybook_cheatsheet')== 'true'): ?>

<table summary="" id="accounting-cheatsheet">
    <caption>Hj�lp - hvad er nu debet og kredit? <a href="<?php e($context->url(null, array('quickhelp' => 'false'))); ?>" id="accounting-cheatsheet-link">(Skjul)</a></caption>
    <tr>
        <th></th>
        <th>Debet</th>
        <th>Kredit</th>
    </tr>
    <tr>
        <th>Indt�gter</th>
        <td>Debitor, kasse, bank</td>
        <td>Varekonto</td>
    </tr>
    <tr>
        <th>Udgifter</th>
        <td>Varekonto</td>
        <td>Kasse, bank</td>
    </tr>
    <tr>
        <th>Debitorbetaling</th>
        <td>Kasse, bank</td>
        <td>Debitor</td>
    </tr>
</table>
<?php else: ?>
    <ul class="options">
    	<li><a href="<?php e(url(null, array('quickhelp' => 'true'))); ?>">Sl� hurtighj�lp til</a></li>
    </ul>


<?php endif; ?>

<?php endif; ?>
