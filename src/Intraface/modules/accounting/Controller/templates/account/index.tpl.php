<h1>Konti <?php e($context->getYear()->get('label')); ?></h1>

<div class="message">
	<p><strong>Kontoplan</strong>. Dette er en oversigt over alle dine konti, hvor du kan se saldoen, rette de enkelte konti og slette dem. Hvis du vil se bevægelserne på den enkelte konto, kan du klikke på kontonavnet.</p>
</div>

<ul class="options">
	<li><a href="<?php e(url('create')); ?>">Opret konto</a></li>
	<li><a class="excel" href="<?php e(url(null, array('format' => 'excel'))); ?>">Excel</a></li>
</ul>
<?php
/*
<form action="<?php e(url(null)); ?>" method="get">
	<fieldset>
		<legend>Avanceret visning</legend>
		<label id="date-from">Fra
			<input type="text" name="from_date" id="date-from" value="<?php e($values['from_date']); ?>" />
		</label>
		<label id="date-to">Til
			<input type="text" name="to_date" id="date-to" value="<?php e($values['to_date']); ?>" />
		</label>
		<input type="submit" value="Vis" />
	</fieldset>
</form>
*/
?>
<?php echo $context->getAccount()->error->view(); ?>

<?php if (count($context->getAccounts()) == 0): ?>
	<div class="message-dependent">
		<p>Der er endnu ikke oprettet nogen konti.</p>
		<p>Du kan oprette en standardkontoplan under <a href="year.php?id=<?php e($year->get('id')); ?>">regnskabsåret</a>, eller du kan taste dem manuelt ind ved at klikke på opret konto ovenfor.</p>
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
	<?php foreach ($context->getAccounts() as $account): ?>

		<tr<?php if ($account['type'] == 'headline') { echo ' class="headline"'; } elseif ($account['type'] == 'sum') { echo ' class="sum"';} ?><?php if (!empty($_GET['from_account_id']) AND $_GET['from_account_id'] == $account['id']) { echo ' id="'.$account['id'].'" class="fade"'; } ?>>
	  		<?php if ($account['type'] != 'headline' AND $account['type'] != 'sum'): ?>
				<td><a href="account.php?id=<?php e($account['id']); ?>"><?php e($account['number']); ?></a></td>
			<?php else: ?>
				<td><?php e($account['number']); ?></td>
			<?php endif; ?>
	  		<?php if ($account['type'] == 'headline'): ?>
				<td colspan="4" class="headline"><?php e($account['name']); ?></td>
			<?php elseif ($account['type'] == 'sum'): ?>
				<td><?php e($account['name']); ?><?php if ($account['type'] == 'sum') { e(' ('. $account["sum_from"] . ' til ' . $account["sum_to"] . ')'); } ?></td>
			<?php else:?>
				<td><a href="account.php?id=<?php e($account['id']); ?>"><?php e($account['name']); ?></a></td>
			<?php endif; ?>

			<?php if ($account['type'] != 'headline') { ?>
			<td><?php e($translation->get($account['type'])); ?></td>
			<td><?php if ($account['type'] == 'balance, asset' OR $account['type'] == 'balance, liability' OR $account['type'] == 'operating') e($translation->get($account['vat_shorthand'])); ?></td>
			<td class="amount"><?php e(amountToOutput($account['saldo'], 2, ",", ".")); ?></td>
			<?php } ?>
			<td class="options">
				<a class="edit" href="account_edit.php?id=<?php e($account['id']); ?>">Ret</a>
				<a class="delete" href="accounts.php?id=<?php e($account['id']); ?>&amp;action=delete">Slet</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>