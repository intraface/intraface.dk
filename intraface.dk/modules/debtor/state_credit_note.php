<?php
require '../../include_first.php';

$debtor_module = $kernel->module('debtor');
$accounting_module = $kernel->useModule('accounting');
$product_module = $kernel->useModule('product');
$translation = $kernel->getTranslation('debtor');

$year = new Year($kernel);
$voucher = new Voucher($year);

if (!empty($_POST)) {

    $debtor = Debtor::factory($kernel, intval($_POST["id"]));
    if ($debtor->get('type') != 'credit_note') {
        trigger_error('You can only state credit notes from this page', E_USER_ERROR);
        exit;
    }

    foreach ($_POST['state_account_id'] as $product_id => $state_account_id) {
        if (empty($state_account_id)) {
            $debtor->error->set('Mindst et produkt ved ikke hvor det skal bogføres.');
            continue;
        }

        $product = new Product($kernel, $product_id);
        $product->getDetails()->setStateAccountId($state_account_id);
    }

    if ($debtor->error->isError()) {
        $debtor->loadItem();
    } elseif (!$debtor->state($year, $_POST['voucher_number'], $_POST['date_state'], $translation)) {
        $debtor->error->set('Kunne ikke bogføre posten');
        $debtor->loadItem();
    } else {
        header('Location: view.php?id='.$debtor->get('id'));
        exit;
    }
} else {
    $debtor = Debtor::factory($kernel, intval($_GET["id"]));

    if ($debtor->get('type') != 'credit_note') {
        trigger_error('You can only state credit notes from this page', E_USER_ERROR);
        exit;
    }

    $debtor->loadItem();
}

$items = $debtor->item->getList();
$value = $debtor->get();


$page = new Intraface_Page($kernel);
$page->start($translation->get('State invoice'));

?>
<h1>Bogfør kreditnota #<?php e($debtor->get('number')); ?></h1>

<ul class="options">
    <li><a href="view.php?id=<?php e($debtor->get("id")); ?>">Luk</a></li>
    <li><a href="list.php?type=credit_note&amp;id=<?php e($debtor->get("id")); ?>&amp;use_stored=true">Tilbage til oversigten over kreditnotaer</a></li>
</ul>

<?php if (!$year->readyForState($debtor->get('this_date'))): ?>
    <?php echo $year->error->view(); ?>
    <p>Gå til <a href="<?php e($accounting_module->getPath().'years.php'); ?>">regnskabet</a></p>
<?php else: ?>

    <p class="message">Når du bogfører en kreditnota vil beløbet bliver trukket fra debitorkontoen.</p>

    <?php $debtor->readyForState($year, 'skip_check_products'); ?>
    <?php echo $debtor->error->view(); ?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" value="<?php e($value['id']); ?>" name="id" />
    <fieldset>
        <legend>Oplysninger der bogføres</legend>
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
            <tr>
                <th><?php e($translation->get("credit note number")); ?></th>
                <td><?php e($debtor->get("number")); ?></td>
            </tr>
            <tr>
                <th>Dato</th>
                <td><?php e($debtor->get("dk_this_date")); ?></td>
            </tr>
            <?php if ($debtor->isStated()): ?>
                <tr>
                    <th>Bogført</th>
                    <td>
                            <?php e($debtor->get("dk_date_stated")); ?>
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <th>Bogfør på dato</th>
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
                <th>Beløb</th>
                <th>Krediteres på</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            if (isset($items[0]["vat"])) {
                $vat = $items[0]["vat"]; // Er der moms på det første produkt
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
                            <select if="state_account" name="state_account_id[<?php e($product->get('id')); ?>]">
                                <option value="">Vælg...</option>
                                <?php
                                $x = 0;
                                $optgroup = 1;
                                foreach ($accounts AS $a):
                                    if (strtolower($a['type']) == 'sum') continue;
                                    if (strtolower($a['type']) == 'headline') continue;
                                ?>

                                    <option value="<?php e($a['number']); ?>"
                                    // er det korrekt at det er number? og måske skal et produkt i virkeligheden snarere
                                    // gemmes med nummeret en med id - for så er det noget lettere at opdatere fra år til år
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

    <?php  if ($debtor->readyForState($year)): ?>
        <div>
            <input type="submit" value="Bogfør" /> eller
            <a href="view.php?id=<?php e($value['id']); ?>">fortryd</a>
        </div>
    <?php  else: ?>
        <p><a href="<?php e($accounting_module->getPath()); ?>daybook.php">Gå til kassekladden</a></p>
    <?php endif;  ?>
    </form>
<?php endif; ?>
<?php
$page->end();
?>