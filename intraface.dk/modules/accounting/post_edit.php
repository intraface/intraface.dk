<?php
require('../../include_first.php');

$kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);
$year->checkYear();

// saving
if (!empty($_POST)) {
    // tjek om debet og credit account findes
    $post = new Post(new Voucher($year, $_POST['voucher_id']), $_POST['id']);
    $account = Account::factory($year, $_POST['account']);

    $date = new Intraface_Date($_POST['date']);
    $date->convert2db();


    $debet = new Amount($_POST['debet']);
    if (!$debet->convert2db()) {
        $this->error->set('Beløbet kunne ikke konverteres');
    }
    $debet = $debet->get();

    $credit = new Amount($_POST['credit']);
    if (!$credit->convert2db()) {
        $this->error->set('Beløbet kunne ikke konverteres');
    }
    $credit = $credit->get();

    if(empty($_POST['invoice_number'])) $_POST['invoice_number'] = '';
    
    if ($id = $post->save($date->get(), $account->get('id'), $_POST['text'], $debet, $credit, $_POST['invoice_number'])) {
        header('Location: voucher.php?id='.$post->voucher->get('id').'&from_post_id='.$id);
        exit;
    }
    else {
        $values = $_POST;
    }
}

elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
    $post = Post::factory($year, (int)$_GET['id']);
    $values = $post->get();
    $values['date'] = $post->get('date_dk');
    $values['debet'] = $post->get('debet');
    $values['credit'] = $post->get('credit');
}
elseif (!empty($_GET['voucher_id']) AND is_numeric($_GET['voucher_id'])) {
    $post = new Post(new Voucher($year, $_GET['voucher_id']));
    $values['date'] = $post->voucher->get('date_dk');
}
else {
    // setting variables
    $post = Post::factory($year);
    $values['date'] = date('d-m-Y');
    $values['debet_account_number'] = '';
    $values['credit_account_number'] = '';
    $values['amount'] = '';
    $values['text'] = '';
    $values['invoice_number'] = '';
    $values['id'] = '';
}

$account = new Account($year);


$page = new Page($kernel);
$page->start('Rediger post på bilag #' . $post->voucher->get('number'));
?>

<h1>Post på bilag #<?php echo $post->voucher->get('number'); ?></h1>

<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
    <input type="hidden" name="id" value="<?php echo $post->get('id'); ?>" />
    <input type="hidden" name="voucher_id" value="<?php echo $post->voucher->get('id'); ?>" />
    <input type="hidden" name="invoice_number" value="<?php e($values['invoice_number']); ?>" />

    <?php echo $post->error->view(); ?>

    <fieldset>
        <legend>Indtast</legend>
        <table>
            <thead>
                <tr>
                    <th><label for="date">Dato</label></th>
                    <th><label for="text">Tekst</label></th>
                    <th><label for="account">Konto</label></th>
                    <th><label for="debet">Debet</label></th>
                    <th><label for="credit">Kredit</label></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input tabindex="1" name="date" type="text" size="7" value="<?php if (!empty($values['date'])) echo safeToHtml($values['date']);  ?>" />
                    </td>
                    <td>
                        <input tabindex="2" type="text" name="text" id="text" value="<?php if (!empty($values['text'])) echo safeToHtml($values['text']); ?>" />
                    </td>
                    <td>
                        <select name="account" tabindex="3">
                            <option value="">Vælg</option>
                            <?php
                                foreach($account->getList() AS $a):
                                    echo '<option value="'.$a['number'].'"';
                                    if (!empty($values['account_number']) AND $values['account_number'] == $a['number']) echo ' selected="selected"';
                                    echo '>'.$a['name'].'</option>';
                                endforeach;
                            ?>
                        </select>
                    </td>
                    <td>
                        <input tabindex="4" name="debet" id="amount" type="text" size="8"  value="<?php if(!empty($values['debet'])) echo amountToForm($values['debet']); ?>" />
                    </td>

                    <td>
                        <input tabindex="5" name="credit" id="amount" type="text" size="8"  value="<?php if(!empty($values['credit'])) echo amountToForm($values['credit']); ?>" />
                    </td>
                    <td>
                        <input tabindex="6" type="submit" id="submit" value="Gem" />
                    </td>
                </tr>
            </tbody>
    </table>
</fieldset>
</form>

<p><a href="voucher.php?id=<?php echo $post->voucher->get('id'); ?>">Tilbage</a></p>


<?php
$page->end();
?>
