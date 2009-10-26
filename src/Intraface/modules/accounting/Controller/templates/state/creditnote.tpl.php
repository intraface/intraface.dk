<h1>Bogf�r kreditnota #<?php e($debtor->get('number')); ?></h1>

<ul class="options">
    <li><a href="view.php?id=<?php e($debtor->get("id")); ?>">Luk</a></li>
    <li><a href="list.php?type=credit_note&amp;id=<?php e($debtor->get("id")); ?>&amp;use_stored=true">Tilbage til oversigten over kreditnotaer</a></li>
</ul>

<?php if (!$year->readyForState($debtor->get('this_date'))): ?>
    <?php echo $year->error->view(); ?>
    <p>G� til <a href="<?php e($accounting_module->getPath().'years.php'); ?>">regnskabet</a></p>
<?php else: ?>

    <p class="message">N�r du bogf�rer en kreditnota vil bel�bet bliver trukket fra debitorkontoen.</p>

    <?php $debtor->readyForState($year, 'skip_check_products'); ?>
    <?php echo $debtor->error->view(); ?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" value="<?php e($value['id']); ?>" name="id" />

    <fieldset>
        <legend><?php e(__('Credit note')); ?></legend>
        <table>
            <tr>
                <th><?php e(__("credit note number")); ?></th>
                <td><?php e($debtor->get("number")); ?></td>
            </tr>
            <tr>
                <th>Dato</th>
                <td><?php e($debtor->get("dk_this_date")); ?></td>
            </tr>
        </table>
    </fieldset>

    <?php if ($debtor->readyForState($year, 'skip_check_products')): ?>
        <fieldset>
            <legend>Oplysninger der bogf�res</legend>
            <table>
                <tr>
                    <th>Bilagsnummer</th>
                    <td>
                        <?php if (!$debtor->isStated()): ?>
                        <input type="text" name="voucher_number" value="<?php e($voucher->getMaxNumber() + 1); ?>" />
                        <?php else: ?>
                        <a href="<?php e($accounting_module->getPath()); ?>voucher.php?id=<?php e($debtor->get("voucher_id")); ?>">Se bilag</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($debtor->isStated()): ?>
                    <tr>
                        <th>Bogf�rt</th>
                        <td>
                                <?php e($debtor->get("dk_date_stated")); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th>Bogf�r p� dato</th>
                        <td>
                            <input type="text" name="date_state" value="<?php e($debtor->get("dk_this_date")); ?>" />
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </fieldset>



        <table class="stripe">
            <thead>
                <tr>
                    <th>Varenr.</th>
                    <th>Beskrivelse</th>
                    <th>Bel�b</th>
                    <th>Krediteres p�</th>
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
                    $account = Account::factory($year, $product->get('state_account_id'));

                    $total += $items[$i]["quantity"] * $items[$i]["price"]->getAsIso(2);
                    $vat = $items[$i]["vat"];
                    ?>
                    <tr>
                        <td><?php e($items[$i]["number"]); ?></td>
                        <td><?php e($items[$i]["name"]); ?></td>
                        <td><?php e(amountToOutput($items[$i]["quantity"]*$items[$i]["price"]->getAsIso(2))); ?></td>
                        <td>
                            <?php if (!$debtor->isStated()):
                                $year = new Year($kernel);
                                $year->loadActiveYear();
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
                                        ?>
                                        <?php if ($product->get('state_account_id') == $a['number']) echo ' selected="selected"'; ?>
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
                                    $account = new Account($year, $year->getSetting('vat_out_account_id'));
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
            <a href="view.php?id=<?php e($value['id']); ?>">fortryd</a>
        </div>
    <?php endif;  ?>
    </form>
<?php endif; ?>