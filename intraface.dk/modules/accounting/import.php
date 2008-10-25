<?php
require('../../include_first.php');

$kernel->module('accounting');
$kernel->useModule('procurement');
$kernel->useModule('debtor');
$kernel->useModule('invoice');
$kernel->useModule('contact');


$year = new Year($kernel);
$year->checkYear();

/*
$procurement = new Procurement($kernel);
$procurement->dbquery->setCondition("voucher_id = 0");
$procurement->dbquery->setFilter('from_date', $year->get('from_date_dk'));
$procurement->dbquery->setFilter('to_date', $year->get('to_date_dk'));
$procurements = $procurement->getList();
*/

$debtor = new Debtor($kernel, 'invoice', 0);
$debtor->dbquery->setCondition("voucher_id = 0");

$debtor->dbquery->setFilter('from_date', $year->get('from_date_dk'));
$debtor->dbquery->setFilter('to_date', $year->get('to_date_dk'));

$invoices = $debtor->getList();

$page = new Intraface_Page($kernel);
$page->start('Regnskab');
?>

<h1>Importer</h1>

<h2>Indkøb</h2>
<?php if (is_array($procurements) AND count($procurements) > 0): ?>
<ul>
<?php foreach ($procurements AS $procurement): ?>

<li><label for=""><input type="checkbox" name="procurement[]" value="<?php e($procurement['id']); ?>" /> <?php e($procurement['dk_invoice_date']); ?> <?php e($procurement['description']); ?></label></li>

<?php endforeach; ?>
</ul>
<?php endif; ?>


<h2>Fakturaer</h2>

<?php if (is_array($invoices) AND count($invoices) > 0): ?>
<ul>
<?php foreach ($invoices AS $debtor): ?>

<li><label for=""><input type="checkbox" name="debtor[]" value="<?php e($debtor['id']); ?>" /> <?php e($debtor['number']); ?> <?php e($debtor['this_date']); ?> <?php e($debtor['description']); ?></label></li>

<?php endforeach; ?>
</ul>
<?php endif; ?>

<h2>Kreditnotaer</h2>

<p>Kunne være smart at bogføre på samme bilag som fakturaen</p>

<h2>Reminders</h2>

<h2>Bankudtog</h2>

<p>Skal vi også kunne importere dem og bogføre dem noget nær automatisk?</p>
<p>Kunne være en ide at den importerer alle poster, og så stiller den det automatisk op som debet og kredit - og så skal man vælge modposter til hver enkelt post.</p>

<?php
$page->end();
?>
