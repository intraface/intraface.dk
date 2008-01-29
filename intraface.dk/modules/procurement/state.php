<?php
require('../../include_first.php');

$procurement_module = $kernel->module('procurement');
$accounting_module = $kernel->useModule('accounting');

$year = new Year($kernel);
$voucher = new Voucher($year);

if (!empty($_POST)) {
    $procurement = new Procurement($kernel, intval($_POST["id"]));
    $procurement->setStateAccountId((int)$_POST['state_account_id']);
    if (!$procurement->state($year, $_POST['voucher_number'], (int)$_POST['credit_account_id'])) {
        $procurement->error->set('Kunne ikke bogføre posten');
        $value = $_POST;
    }
} else {
    $procurement = new Procurement($kernel, intval($_GET["id"]));
}

$value = $procurement->get();


$page = new Page($kernel);
$page->start('Bogfør indkøb #' . $procurement->get('number'));

?>
<h1>Bogfør indkøb #<?php echo $procurement->get('number'); ?></h1>

<ul class="options">
    <li><a href="view.php?id=<?php print($procurement->get("id")); ?>">Luk</a></li>
    <li><a href="index.php?type=invoice&amp;id=<?php print($procurement->get("id")); ?>&amp;use_stored=true">Tilbage til indkøbslisten</a></li>
</ul>


<?php if(!$year->readyForState()): ?>
    <?php echo $year->error->view(); ?>
    <p>Gå til <a href="<?php echo $accounting_module->getPath().'years.php'; ?>">regnskabet</a></p>


<?php else: ?>

    <?php echo $procurement->error->view(); ?>

    <p class="warning">
        <strong>Betafuntion - under test</strong>: Du skal være opmærksom på at denne funktion altid sætter fakturaerne på kreditorkontoen, og at den bruger betalingsdatoen som bogføringsdato. Desuden sætter den automatisk beløbet for forsendelse mv. på den valgte bogføringskonto.
    </p>
    

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" value="<?php echo $value['id']; ?>" name="id" />

    <fieldset>
        <legend><?php e(t('procurement')); ?></legend>
        <table>
            <tr>
                <th><?php print(safeToHtml($translation->get("number"))); ?></th>
                <td><?php print(safeToHtml($procurement->get("number"))); ?></td>
            </tr>
            <tr>
                <th><?php e(t('description')) ?></th>
                <td><?php print(nl2br(safeToHtml($procurement->get("description")))); ?></td>
            </tr>
            <tr>
                <th><?php e(t('date recieved')) ?></th>
                <td><?php print(safeToHtml($procurement->get("dk_date_recieved"))); ?></td>
            </tr>
        </table>
    </fieldset>



<fieldset>
    <legend>Oplysninger der bogføres</legend>

        <table>
                    <tr>
                        <th>Bilagsnummer</th>
                        <td><input type="text" name="voucher_number" value="<?php echo $voucher->getMaxNumber() + 1; ?>" /></td>
                    </tr>
                    <tr>
                        <th>Dato</th>
                        <td><input type="text" name=""<?php print($procurement->get("dk_paid_date")); ?></td>
                    </tr>
                    <tr>
                        <th>Beløb</th>
                        <td><?php print($procurement->get("dk_total_price_items")); ?> kroner</td>
                    </tr>
                    <tr>
                        <th>Forsendelse mv.</th>
                        <td><?php print($procurement->get("dk_price_shipment_etc")); ?> kroner</td>
                    </tr>

                    <tr>
                        <th>Moms</th>
                        <td><?php print($procurement->get("dk_vat")); ?> kroner</td>
                    </tr>

                    <?php if ($procurement->isStated()): ?>
                    <tr>
                        <th>Bogført:</th>
                        <td><?php echo $procurement->get("date_stated_dk"); ?></td>
                    </tr>
                    <?php elseif ($kernel->user->hasModuleAccess('accounting')): ?>
                    <tr>
                        <th>Bogføres på konto</th>
                        <td>
                            <select name="state_account_id">
                                <option value="">Vælg</option>
                            <?php
                                $account = new Account($year);
                                $accounts = $account->getList('expenses');
                                foreach ($accounts AS $account):
                                    echo '<option value="'.$account['id'].'"';
                                    if ($account['id'] == $_POST['state_account_id']) echo ' selected="selected"';
                                    echo '>'.$account['name'].'</option>';
                                endforeach;
                            ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Pengene tages fra</th>
                        <td>
                            <select name="credit_account_id">
                                <option value="">Vælg</option>
                            <?php
                                $credit_account = new Account($year, $year->getSetting('credit_account_id'));
                                if ($credit_account->get('id') > 0) {
                                    echo '<option value="'.$credit_account->get('id').'"';
                                    if ($credit_account->get('id') == $_POST['credit_account_id']) echo ' selected="selected"';
                                    echo '>'.$credit_account->get('name').'</option>';
                                }
                                $account = new Account($year);
                                $accounts = $account->getList('finance');
                                foreach ($accounts AS $account):
                                    if ($year->getSetting('debtor_account_id') == $account['id']) continue;
                                    echo '<option value="'.$account['id'].'"';
                                    if ($account['id'] == $_POST['credit_account_id']) echo ' selected="selected"';
                                    echo '>'.$account['name'].'</option>';
                                endforeach;
                            ?>
                            </select>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>

</fieldset>


     <?php if (!$procurement->isStated()): ?>
     <div>
         <input type="submit" value="Bogfør" /> eller
        <a href="view.php?id=<?php echo $value['id']; ?>">fortryd</a>
    </div>
    <?php else: ?>
    <p><a href="/modules/accounting/daybook.php">Gå til kassekladden</a></p>
    <?php endif; ?>
</form>

<?php endif; ?>

<?php
$page->end();
?>
