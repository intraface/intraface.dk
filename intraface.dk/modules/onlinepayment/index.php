<?php
require('../../include_first.php');

$module = $kernel->module('onlinepayment');
$translation = $kernel->getTranslation('onlinepayment');

$onlinepayment = OnlinePayment::factory($kernel);

if(isset($_GET['status'])) {
	$onlinepayment->dbquery->setFilter('status', $_GET['status']);
}

if(isset($_GET['text'])) {
	$onlinepayment->dbquery->setFilter('text', $_GET['text']);
}

$payments = $onlinepayment->getList();

$page = new Page($kernel);
$page->start('Onlinebetalinger');
?>

<h1>Onlinebetalinger</h1>

<?php if (!$onlinepayment->isProviderSet()): ?>

	<p>For at bruge onlinebetalinger, skal du have valgt en udbyder. <a href="choose_provider.php">Vælg udbyder</a>.</p>

<?php elseif (!$onlinepayment->isSettingsSet()): ?>

	<p>For at bruge onlinebetalinger, skal du have sat nogle indstillinger. <a href="settings.php">Sæt indstillinger</a>.</p>


<?php elseif (!$onlinepayment->isFilledIn()): ?>
	<p>Der er ikke oprettet nogen betalinger.</p>
<?php else: ?>


<fieldset class="hide_on_print">
	<legend>Søgning</legend>
	<form method="get" action="index.php">
		<label>Tekst
			<input type="text" name="text" value="<?php echo $onlinepayment->dbquery->getFilter("text"); ?>" />
		</label>
		<label>Status
		<select name="status">
			<option value="-1">Alle</option>
			<?php
			$status_types = $module->getSetting('status');
			for($i = 1, $max = count($status_types); $i < $max; $i++) {
				?>
				<option value="<?php print($i); ?>" <?php if ($onlinepayment->dbquery->getFilter("status") == $i) echo ' selected="selected"';?>><?php print($translation->get($status_types[$i])); ?></option>
				<?php
			}
			?>
			</select>
		</label>
		<span>
		<input type="submit" value="Find" />
		</span>
	</form>
</fieldset>



<table class="stripe">
	<caption>Onlinebetalinger</caption>
	<thead>
		<tr>
			<th>Dato</th>
			<th>Transaktionsnummer</th>
			<th>Tilknyttet</th>
			<th>Beløb</th>
			<th>Status</th>
		</tr>
	</thead>

	<tbody>
		<?php

		$saldo = 0;
		for($i = 0, $max = count($payments); $i < $max; $i++) {
			?>
			<tr id="p<?php print($payments[$i]["id"]); ?>" <?php if (!empty($_GET['from_id']) AND $_GET['from_id'] == $payments[$i]["id"]) echo ' class="fade"'; ?>>
				<td><?php print($payments[$i]["dk_date_created"]); ?></td>
				<td><a href="payment.php?id=<?php print($payments[$i]['id']); ?>">
					<?php
					if($payments[$i]["transaction_number"] == "") {
						print("Ej angivet");
					}
					else {
						print($payments[$i]["transaction_number"]);
					}
					?></a>
				</td>
				<td>
					<?php
					switch($payments[$i]['belong_to']) {
						case "invoice":
							if($kernel->user->hasModuleAccess('invoice')) {
								$debtor_module = $kernel->useModule('debtor');
								print("<a href=\"".$debtor_module->getPath()."view.php?id=".$payments[$i]['belong_to_id']."\">Faktura</a>");
							}
							else {
								print("Faktura");
							}
						break;
						case "order":
							if($kernel->user->hasModuleAccess('order')) {
								$debtor_module = $kernel->useModule('debtor');
								print("<a href=\"".$debtor_module->getPath()."view.php?id=".$payments[$i]['belong_to_id']."\">Ordre</a>");
							}
							else {
								print("Ordre");
							}
						break;
						default:
							print("Ingen");
					}
					?>
				</td>
				<td><?php print($payments[$i]["dk_amount"]); ?></td>
				<td>
					<?php
					if ($payments[$i]["status"] == 'captured') {
						$saldo += $payments[$i]["amount"];
					}


					print($translation->get($payments[$i]["status"]));

					if($payments[$i]['user_transaction_status_translated'] != "") {
						print(" (".$p['user_transaction_status_translated'].")");
					}
					elseif($payments[$i]['status'] == 'authorized') {
						print(" (Ikke <acronym title=\"Betaling kan først hæves når faktura er sendt\">hævet</acronym>)");
					}
					?>
				</td>

			</tr>
			<?php
		}
		?>
	</tbody>
</table>

<p><strong>Hævet i alt på søgningen: </strong> <?php echo $saldo; ?> kroner.</p>
<?php endif; ?>
<?php

$page->end();

?>
