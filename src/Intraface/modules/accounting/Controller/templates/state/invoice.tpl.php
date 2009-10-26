<?php
$accounting_module = $context->getModule();
?>

<h1>Bogf�r faktura #<?php e($context->getDebtor()->get('number')); ?></h1>

<ul class="options">
    <li><a href="view.php?id=<?php e($context->getDebtor()->get("id")); ?>">Luk</a></li>
    <li><a href="list.php?type=invoice&amp;id=<?php e($context->getDebtor()->get("id")); ?>&amp;use_stored=true">Tilbage til fakturaoversigten</a></li>
</ul>

<?php if (!$context->getYear()->readyForState($context->getDebtor()->get('this_date'))): ?>
    <?php echo $context->getYear()->error->view(); ?>
    <p>G� til <a href="<?php e($this->url('../../../../../accounting/')); ?>">regnskabet</a></p>
<?php else: ?>

    <p class="message">N�r du bogf�rer fakturaerne vil det skyldige bel�b blive sat p� debitorkontoen. N�r kunden har betalt, skal betalingen bogf�res for at overf�re bel�bet fra debitorkontoen til din indkomst konto (fx Bankkonto).</p>

    <?php $context->getDebtor()->readyForState($context->getYear(), 'skip_check_products'); ?>
    <?php echo $context->getDebtor()->error->view(); ?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" value="<?php e($context->getDebtor()->get('id')); ?>" name="id" />
    <fieldset>
        <legend>Faktura</legend>
        <table>
            <tr>
                <th><?php e(__("invoice number")); ?></th>
                <td><?php e($context->getDebtor()->get("number")); ?></td>
            </tr>
            <tr>
                <th>Dato</th>
                <td><?php e($context->getDebtor()->get("dk_this_date")); ?></td>
            </tr>
        </table>
    </fieldset>

    <?php if ($context->getDebtor()->readyForState($context->getYear(), 'skip_check_products')): ?>
        <fieldset>
            <legend>Oplysninger der bogf�res</legend>
            <table>
                <tr>
                    <th>Bilagsnummer</th>
                    <td><input type="text" name="voucher_number" value="<?php e($voucher->getMaxNumber() + 1); ?>" /></td>
                </tr>
                <tr>
                    <th>Bogf�r p� dato</th>
                    <td><input type="text" name="date_state" value="<?php e($context->getDebtor()->get("dk_this_date")); ?>" /></td>
                </tr>
            </table>
        </fieldset>



        <table class="stripe">
            <thead>
                <tr>
                    <th>Varenr.</th>
                    <th>Beskrivelse</th>
                    <th>Bel�b</th>
                    <th>Bogf�res p�</th>
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
                    $product = new Product($kernel, $items[$i]['product_id']);
                    $account = Account::factory($context->getYear(), $product->get('state_account_id'));

                    $total += $items[$i]["quantity"] * $items[$i]["price"]->getAsIso(2);
                    $vat = $items[$i]["vat"];
                    ?>
                    <tr>
                        <td><?php e($items[$i]["number"]); ?></td>
                        <td><?php e($items[$i]["name"]); ?></td>
                        <td><?php e(amountToOutput($items[$i]["quantity"]*$items[$i]["price"]->getAsIso(2))); ?></td>
                        <td>
                            <?php if (!$context->getDebtor()->isStated()):
                                $year = new Year($kernel);
                                $context->getYear()->loadActiveYear();
                                $accounts =  $account->getList('sale');
                                ?>
                                <select id="state_account" name="state_account_id[<?php e($product->get('id')); ?>]">
                                    <option value="">V�lg...</option>
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
            <input type="submit" value="Bogf�r" /> eller
            <a href="view.php?id=<?php e($context->getDebtor()->get('id')); ?>">fortryd</a>
        </div>
   <?php endif;  ?>
    </form>
<?php endif; ?>
