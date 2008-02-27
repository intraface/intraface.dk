<?php
require('../../include_first.php');

$procurement_module = $kernel->module('procurement');
$accounting_module = $kernel->useModule('accounting');

$year = new Year($kernel);
$voucher = new Voucher($year);

if (!empty($_POST)) {
    
    $procurement = new Procurement($kernel, intval($_POST["id"]));
    
    if(isset($_POST['state'])) {
        
        if($procurement->checkStateDebetAccounts($year, $_POST['debet_account'])) {
            if ($procurement->state($year, $_POST['voucher_number'], $_POST['voucher_date'], $_POST['debet_account'], (int)$_POST['credit_account_number'], $translation)) {
                header('location: view.php?id='.$procurement->get('id'));
                exit;
            }
            $procurement->error->set('Kunne ikke bogføre posten');
        }
    }
    
    $value = $_POST;
    
    if(isset($_POST['add_line'])) {
        array_push($value['debet_account'], array('text' => '', 'amount' => '0,00'));
    }
    
    if(isset($_POST['remove_line'])) {
        foreach($_POST['remove_line'] AS $key => $void) {
            array_splice($value['debet_account'], $key, 1);
        }
    }
    
    
} else {
    $procurement = new Procurement($kernel, intval($_GET["id"]));
    $value = $procurement->get();
    $procurement->loadItem();
    $items = $procurement->item->getList();
    $i = 0;
    $items_amount = 0;
    
    if(count($items) > 0) {
        /**
         * implement to a line for each item
         */
    }
    
    if($procurement->get('price_items') - $items_amount > 0) {
        $value['debet_account'][$i++] = array('text' => '', 'amount' => $procurement->get('dk_price_items') - $items_amount);
    }
        
    if($procurement->get('price_shipment_etc') > 0) {
        $value['debet_account'][$i++] = array('text' => $translation->get('shipment etc'), 'amount' => $procurement->get('dk_price_shipment_etc'));
    }   
}


$page = new Page($kernel);
$page->start('Bogfør indkøb #' . $procurement->get('number'));

?>
<h1>Bogfør indkøb #<?php echo $procurement->get('number'); ?></h1>

<ul class="options">
    <li><a href="view.php?id=<?php print($procurement->get("id")); ?>">Luk</a></li>
    <li><a href="index.php?type=invoice&amp;id=<?php print($procurement->get("id")); ?>&amp;use_stored=true">Tilbage til indkøbslisten</a></li>
</ul>

<div class="message">
    <p>Du bedes manuelt kontrollere at indkøbet bliver bogført korrekt.</p>
</div>

<?php if(!$year->readyForState()): ?>
    <?php echo $year->error->view(); ?>
    <p>Gå til <a href="<?php echo $accounting_module->getPath().'years.php'; ?>">regnskabet</a></p>


<?php else: ?>

    <?php echo $procurement->error->view(); ?>

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
            <?php
            /*
            <tr>
                 <th><?php e(t('price for items')) ?></th>
                <td><?php print(safeToHtml($procurement->get("dk_price_items"))); ?> kroner</td>
            </tr>
            <tr>
                <th><?php e(t('price for shipment etc')) ?></th>
                <td><?php print(safeToHtml($procurement->get("dk_price_shipment_etc"))); ?> kroner</td>
            </tr>

            <tr>
                <th><?php e(t('vat')) ?></th>
                <td><?php print(safeToHtml($procurement->get("dk_vat"))); ?> kroner</td>
            </tr>
            */
            ?>
            <tr>
                <th><?php e(t('total price')) ?></th>
                <td><?php print(safeToHtml($procurement->get("dk_total_price"))); ?> kroner</td>
            </tr>
            <tr>
                <td><?php e(t('buy from')) ?></td>
                <td><?php e($translation->get($procurement->get('from_region'), 'procurement')); ?>
            </td>
        </tr>
            
        </table>
    </fieldset>



    <fieldset>
        <legend>Oplysninger der bogføres</legend>
    
        <div class="formrow">
            <label for="voucher_number"><?php e(t('voucher number')) ?></label>
            <input type="text" name="voucher_number" id="voucher_number" value="<?php echo $voucher->getMaxNumber() + 1; ?>" />
        </div>
        
        <div class="formrow">
            <label for="voucher_date"><?php e(t('state on date')) ?></label>
            <input type="text" name="voucher_date" id="voucher_date" value="<?php print($procurement->get("dk_paid_date")); ?>" />
        </div>
        
        <div class="formrow">
            <label for="credit_account_id"><?php e(t('paid from account')) ?></label>
            <select name="credit_account_number">
                <option value="">Vælg</option>
                <?php
                $account = new Account($year);
                $accounts = $account->getList('finance');
                foreach ($accounts AS $account) {
                    if ($year->getSetting('debtor_account_id') == $account['id']) continue;
                    echo '<option value="'.$account['number'].'"';
                    if (isset($value['credit_account_number']) && $account['number'] == $value['credit_account_number']) echo ' selected="selected"';
                    echo '>'.$account['name'].'</option>';
                }
                ?>
            </select>
        </div>
    
    </fieldset>

    <table class="stripe">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Beskrivelse</th>
                <th>Beløb</th>
                <th>Bogføres på</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if(isset($value['debet_account']) && is_array($value['debet_account'])) {   
                 
                $account = new Account($year);
                 
                foreach($value['debet_account'] AS $key => $line) {
                    ?>
                    <tr>
                        <td><?php e($key+1); ?></td>
                        <td><?php e($procurement->get('description')); ?> - <input type="text" name="debet_account[<?php echo intval($key); ?>][text]" value="<?php print(safeToHtml($line["text"])); ?>" /></td>
                        <td><input type="text" name="debet_account[<?php echo intval($key); ?>][amount]" value="<?php print(safeToHtml($line["amount"])); ?>" size="8" /> <?php e('('.t('excl. vat').')'); ?></td>
                        <td>
                            <?php 
                            $accounts =  $account->getList('expenses');
                            ?>
                            <select id="state_account" name="debet_account[<?php echo intval($key); ?>][state_account_id]">
                                <option value="">Vælg...</option>
                                <?php
                                foreach($accounts AS $a) {
                                    if (strtolower($a['type']) == 'sum') continue;
                                    if (strtolower($a['type']) == 'headline') continue;
                                    echo '<option value="'. $a['number'].'"';
                                    if (isset($line['state_account_id']) && $line['state_account_id'] == $a['number']) echo ' selected="selected"';
                                    echo '>'.safeToForm($a['name']).'</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <td><input type="submit" name="remove_line[<?php echo intval($key); ?>]" value="<?php e(t('remove')); ?>" /></td>
                    </tr>
                    <?php
                }
            }
            ?>
            <?php if($procurement->get('vat') > 0): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td><?php e($procurement->get('description'). ' - '.t('vat')); ?></td>
                    <td><?php e($procurement->get('dk_vat')); ?></td>                    
                    <td>
                        <?php
                        $account = new Account($year, $year->getSetting('vat_in_account_id'));
                        echo safeToHtml($account->get('number') . ' ' . $account->get('name'));
                        ?>
                    </td>
                    <td>&nbsp;</td>
                 </tr>
             <?php endif; ?>
                
        </tbody>
    </table>
    <div>
        <input type="submit" name="add_line" value="<?php e(t('add line')); ?>" />
    </div>
    
    <div>
         <input type="submit" name="state" value="<?php e(t('state')); ?>" /> eller
         <a href="view.php?id=<?php echo $value['id']; ?>">fortryd</a>
    </div>
    
</form>

<?php endif; ?>

<?php
$page->end();
?>