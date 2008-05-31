<?php
require('../../include_first.php');

$accounting_module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);
$year->checkYear();

if (!empty($_GET['action']) AND $_GET['action'] == 'delete' AND is_numeric($_GET['id'])) {
	$account = new Account($year, $_GET['id']);
	$account->delete();
}
else {
	$account = new Account($year);
	/*
	$values['from_date'] = $year->get('from_date_dk');
	$values['to_date'] = $year->get('to_date_dk');
	*/
	$values['from_date'] = '01-01-2005';
	$values['to_date'] = '01-02-2005';

}

//$accounts = $account->getSaldoList($values['from_date'], $values['to_date']);
$accounts = $account->getList('stated', true);
$page = new Intraface_Page($kernel);
$page->start('Kontooversigt');

?>
<h1>Konti <?php echo $year->get('label'); ?></h1>

<div class="message">
	<p><strong>Kontoplan</strong>. Dette er en oversigt over alle dine konti, hvor du kan se saldoen, rette de enkelte konti og slette dem. Hvis du vil se bevægelserne på den enkelte konto, kan du klikke på kontonavnet.</p>
</div>

<ul class="options">
	<li><a href="account_edit.php">Opret konto</a></li>
	<li><a class="excel" href="accounts_excel.php">Excel</a></li>
</ul>
<!--
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
	<fieldset>
		<legend>Avanceret visning</legend>
		<label id="date-from">Fra
			<input type="text" name="from_date" id="date-from" value="<?php echo safeToHtml($values['from_date']); ?>" />
		</label>
		<label id="date-to">Til
			<input type="text" name="to_date" id="date-to" value="<?php echo safeToHtml($values['to_date']); ?>" />
		</label>
		<input type="submit" value="Vis" />
	</fieldset>
</form>
-->
<?php echo $account->error->view(); ?>

<?php if (count($accounts) == 0): ?>
	<div class="message-dependent">
		<p>Der er endnu ikke oprettet nogen konti.</p>
		<p>Du kan oprette en standardkontoplan under <a href="year.php?id=<?php echo intval($year->get('id')); ?>">regnskabsåret</a>, eller du kan taste dem manuelt ind ved at klikke på opret konto ovenfor.</p>
	</div>
<?php else: ?>
<table>
	<caption>Kontoplan</caption>
	<thead>
		<tr>
			<th>Kontonummer</th>
			<th>Kontonavn</th>
			<th>Type</th>
			<th>Moms</th>
			<th>Saldo</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($accounts AS $account) { ?>

		<tr<?php if ($account['type'] == 'headline') { echo ' class="headline"'; } elseif ($account['type'] == 'sum') { echo ' class="sum"';} ?><?php if (!empty($_GET['from_account_id']) AND $_GET['from_account_id'] == $account['id']) { echo ' id="'.$account['id'].'" class="fade"'; } ?>>
	  		<?php if ($account['type'] != 'headline' AND $account['type'] != 'sum'): ?>
				<td><a href="account.php?id=<?php echo $account['id']; ?>"><?php echo safeToHtml($account['number']); ?></a></td>
			<?php else: ?>
				<td><?php echo $account['number']; ?></td>
			<?php endif; ?>
	  		<?php if ($account['type'] == 'headline'): ?>
				<td colspan="4" class="headline"><?php echo safeToHtml($account['name']); ?></td>
			<?php elseif ($account['type'] == 'sum'): ?>
				<td><?php echo $account['name']; ?><?php if ($account['type'] == 'sum') { echo ' ('. $account["sum_from"] . ' til ' . $account["sum_to"] . ')'; } ?></td>
			<?php else:?>
				<td><a href="account.php?id=<?php echo $account['id']; ?>"><?php echo safeToHtml($account['name']); ?></a></td>
			<?php endif; ?>

			<?php if ($account['type'] != 'headline') { ?>
			<td><?php echo safeToHtml($translation->get($account['type'])); ?></td>
			<td><?php if ($account['type'] == 'balance, asset' OR $account['type'] == 'balance, liability' OR $account['type'] == 'operating') echo safeToHtml($translation->get($account['vat_shorthand'])); ?></td>
			<td class="amount"><?php echo amountToOutput($account['saldo'], 2, ",", "."); ?></td>
			<?php } ?>
			<td class="options">
				<a class="edit" href="account_edit.php?id=<?php echo intval($account['id']); ?>">Ret</a>
				<a class="delete" href="accounts.php?id=<?php echo intval($account['id']); ?>&amp;action=delete">Slet</a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php endif; ?>

<?php
$page->end();
?>