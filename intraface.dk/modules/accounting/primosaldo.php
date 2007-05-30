<?php
require('../../include_first.php');

$kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

if (!empty($_POST)) {

    $year = new Year($kernel, $_POST['id']);

    if ($year->get('last_year_id') == 0) {
        trigger_error('Der findes ikke noget sidste år', E_USER_ERROR);
    }

    // oprette objekt til at holde sidste år
    $last_year = new Year($kernel, $year->get('last_year_id'));

    // hente konti hvor de nye har created_from_id
    $account = new Account($year);
    $accounts = $account->getList('balance');
    foreach ($accounts AS $a) {

        #
        # Måske bør der laves et tjek på om alle posterne i det gamle år er bogførte
        #

        $old_account = new Account($last_year, $a['created_from_id']);
        $saldo = $old_account->getSaldo('stated');

        if ($old_account->get('credit') == $old_account->get('debet')) {
            $saldo = array(
                'credit' => 0,
                'debet' => 0
            );
        }
        elseif ($old_account->get('credit') > $old_account->get('debet')) {
            $saldo = array(
                'credit' => $old_account->get('credit') - $old_account->get('debet'),
                'debet' => 0
            );
        }
        elseif ($old_account->get('credit') < $old_account->get('debet')) {
            $saldo = array(
                'credit' => 0,
                'debet' => $old_account->get('debet') - $old_account->get('credit')
            );
        }

        $account = new Account($year, $a['id']);
        $account->savePrimosaldo(number_format($saldo['debet'], 2, ',', ''), number_format($saldo['credit'], 2, ',', ''));

    }
}
elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
    $year = new Year($kernel, $_GET['id']);
}
else {
    $year = new Year($kernel);
}

$account = new Account($year);

$accounts = $account->getList('balance');


$total_debet = 0;
$total_credit = 0;

$page = new Page($kernel);
$page->start('Primosaldo');
?>

<h1>Primosaldo <?php echo $year->get('label'); ?></h1>

<ul class="options">
    <li><a href="year.php?id=<?php echo $year->get('id'); ?>">Gå tilbage til regnskabsåret</a></li>
    <li><a class="edit" href="primosaldo_edit.php?id=<?php echo $year->get('id'); ?>">Ret</a></li>
</ul>

<table>
<caption>Primosaldo for statuskonti</caption>
<thead>
    <tr>
        <th>Kontonummer</th>
        <th>Kontonavn</th>
        <th>Debet</th>
        <th>Credit</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($accounts AS $account): ?>
    <tr>
        <td><?php echo $account['number']; ?></td>
        <td><?php echo $account['name']; ?></td>
        <td><?php echo amountToOutput($account['primosaldo_debet']); ?></td>
        <td><?php echo amountToOutput($account['primosaldo_credit']); ?></td>
    </tr>
    <?php
        // udregninger
        $total_debet += $account['primosaldo_debet'];
        $total_credit += $account['primosaldo_credit'];
    ?>

    <?php endforeach; ?>
    <tr>
        <td></td>
        <td>
            <strong>Balance</strong>
            <?php
                if ($total_debet != $total_credit) {
                    echo '<strong class="warning">Balancen stemmer ikke</strong>';
                }
            ?>
        </td>
        <td><strong><?php echo amountToOutput($total_debet); ?></strong></td>
        <td><strong><?php echo amountToOutput($total_credit); ?></strong></td>
    </tr>
</tbody>
</table>

<?php if ($year->get('last_year_id') > 0): ?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="id" value="<?php echo $year->get('id'); ?>" />
    <fieldset>
        <legend>Oplysninger til primosaldo</legend>

        <p>Du kan hente primobalancen fra sidste års regnskab. Du skal bare være opmærksom på, at tallene i din nuværende primobalance overskrives - og at handlingen ikke kan fortrydes.</p>
        <div>
            <input type="submit" name="get_last_year" value="Hent saldoen fra sidste års regnskab" onclick="return confirm('Vær opmærksom på at denne funktion stadig er under udvikling, og sikkert ikke virker helt efter hensigten. \n\nEr du sikker på, at du vil opdatere din primobalance? Handlingen kan ikke fortrydes!')" />
        </div>

    </fieldset>
</form>

<?php endif; ?>

<?php
$page->end();
?>