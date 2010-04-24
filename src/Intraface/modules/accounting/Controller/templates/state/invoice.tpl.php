<?php
$accounting_module = $context->getModule();
$items = $context->getItems();
?>

<h1>Bogfør faktura #<?php e($context->getModel()->get('number')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
    <li><a href="<?php e(url('../../', array('use_stored' => true))); ?>"><?php e(t('To invoices')); ?></a></li>
</ul>

<?php if (!$context->getYear()->readyForState($context->getModel()->get('this_date'))): ?>
    <?php echo $context->getYear()->error->view(); ?>
    <p>Gå til <a href="<?php e($this->url('../../../../../accounting/')); ?>">regnskabet</a></p>
<?php else: ?>

    <p class="message">Når du bogfører fakturaerne vil det skyldige beløb blive sat på debitorkontoen. Når kunden har betalt, skal betalingen bogføres for at overføre beløbet fra debitorkontoen til din indkomst konto (fx Bankkonto).</p>

    <?php $context->getModel()->readyForState($context->getYear(), 'skip_check_products'); ?>
    <?php echo $context->getModel()->error->view(); ?>

    <form action="<?php e(url()); ?>" method="post">
    <fieldset>
        <legend>Faktura</legend>
        <table>
            <tr>
                <th><?php e(t("invoice number")); ?></th>
                <td><?php e($context->getModel()->get("number")); ?></td>
            </tr>
            <tr>
                <th>Dato</th>
                <td><?php e($context->getModel()->get("dk_this_date")); ?></td>
            </tr>
        </table>
    </fieldset>

    <?php if ($context->getModel()->readyForState($context->getYear(), 'skip_check_products')): ?>
        <fieldset>
            <legend>Oplysninger der bogføres</legend>
            <table>
                <tr>
                    <th>Bilagsnummer</th>
                    <td><input type="text" name="voucher_number" value="<?php e($context->getVoucher()->getMaxNumber() + 1); ?>" /></td>
                </tr>
                <tr>
                    <th>Bogfør på dato</th>
                    <td><input type="text" name="date_state" value="<?php e($context->getModel()->get("dk_this_date")); ?>" /></td>
                </tr>
            </table>
        </fieldset>

        <table class="stripe">
            <thead>
                <tr>
                    <th>Varenr.</th>
                    <th>Beskrivelse</th>
                    <th>Beløb</th>
                    <th>Bogføres på</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                if (isset($items[0]["vat"])) {
                    $vat = $items[0]["vat"]; // Er der moms p� det f�rste produkt
                } else {
                    $vat = 0;
                }

                for ($i = 0, $max = count($items); $i<$max; $i++) {
                    $product = new Product($context->getKernel(), $items[$i]['product_id']);
                    $account = Account::factory($context->getYear(), $product->get('state_account_id'));

                    $total += $items[$i]["quantity"] * $items[$i]["price"]->getAsIso(2);
                    $vat = $items[$i]["vat"];
                    ?>
                    <tr>
                        <td><?php e($items[$i]["number"]); ?></td>
                        <td><?php e($items[$i]["name"]); ?></td>
                        <td><?php e(amountToOutput($items[$i]["quantity"]*$items[$i]["price"]->getAsIso(2))); ?></td>
                        <td>
                            <?php if (!$context->getModel()->isStated()):
                                $year = new Year($context->getKernel());
                                $context->getYear()->loadActiveYear();
                                $accounts =  $account->getList('sale');
                                ?>
                                <select id="state_account" name="state_account_id[<?php e($product->get('id')); ?>]">
                                    <option value=""><?php e(t('Choose')); ?></option>
                                    <?php
                                    $x = 0;
                                    $optgroup = 1;
                                    foreach ($accounts AS $a):
                                        if (strtolower($a['type']) == 'sum') continue;
                                        if (strtolower($a['type']) == 'headline') continue;
                                        ?>
                                        <option value="<?php e($a['number']); ?>"
                                        <?php
                                        // er det korrekt at det er number? og m�ske skal et produkt i virkeligheden snarere
                                        // gemmes med nummeret en med id - for s� er det noget lettere at opdatere fra �r til �r
                                        if ($product->get('state_account_id') == $a['number']) echo ' selected="selected"';
                                        ?>
                                        ><?php e($a['name']); ?></option>
                                        <?php $optgroup = 0;
                                    endforeach;
                                    ?>
                                </select>
                            <?php else: ?>
                                <?php e($account->get('number') . ' ' . $account->get('name')); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                    if ($vat == 1 && (!isset($items[$i+1]["vat"]) || $items[$i+1]["vat"] == 0)) {
                        ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td><b>25% moms af <?php e(amountToOutput($total)); ?></b></td>
                            <td><b><?php e(amountToOutput($total * 0.25, 2)); ?></b></td>
                            <td>
                                <?php
                                    $account = new Account($context->getYear(), $context->getYear()->getSetting('vat_out_account_id'));
                                    e($account->get('number') . ' ' . $account->get('name'));
                                ?>
                            </td>
                        </tr>
                        <?php
                        $total = $total * 1.25;
                    }
                }
                ?>
            </tbody>
        </table>

        <div>
            <input type="submit" value="<?php e(t('State')); ?>" />
            <a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a>
        </div>
   <?php endif;  ?>
    </form>
<?php endif; ?>
