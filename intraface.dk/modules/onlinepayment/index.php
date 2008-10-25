<?php
require '../../include_first.php';

$module = $kernel->module('onlinepayment');
$translation = $kernel->getTranslation('onlinepayment');

$onlinepayment = OnlinePayment::factory($kernel);

if (isset($_GET['status'])) {
	$onlinepayment->getDBQuery()->setFilter('status', $_GET['status']);
} else {
    $onlinepayment->getDBQuery()->setFilter('status', 2);
}
if (isset($_GET['text'])) {
	$onlinepayment->getDBQuery()->setFilter('text', $_GET['text']);
}
if (isset($_GET["from_date"]) && $_GET["from_date"] != "") {
    $onlinepayment->getDBQuery()->setFilter("from_date", $_GET["from_date"]);
}
if (isset($_GET["to_date"]) && $_GET["to_date"] != "") {
    $onlinepayment->getDBQuery()->setFilter("to_date", $_GET["to_date"]);
}

$payments = $onlinepayment->getList();

$page = new Intraface_Page($kernel);
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
			<input type="text" name="text" value="<?php e($onlinepayment->getDBQuery()->getFilter("text")); ?>" />
		</label>
		<label>Status
		<select name="status">
			<option value="-1">Alle</option>
			<?php
			$status_types = OnlinePayment::getStatusTypes();
			for ($i = 1, $max = count($status_types); $i < $max; $i++) {
				?>
				<option value="<?php e($i); ?>" <?php if ($onlinepayment->getDBQuery()->getFilter("status") == $i) echo ' selected="selected"';?>><?php e($translation->get($status_types[$i])); ?></option>
				<?php
			}
			?>
			</select>
		</label>
        <label>Fra dato
            <input type="text" name="from_date" id="date-from" value="<?php e($onlinepayment->getDBQuery()->getFilter("from_date")); ?>" /> <span id="calender"></span>
        </label>
        <label>Til dato
            <input type="text" name="to_date" value="<?php e($onlinepayment->getDBQuery()->getFilter("to_date")); ?>" />
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
		for ($i = 0, $max = count($payments); $i < $max; $i++) {
			?>
			<tr id="p<?php e($payments[$i]["id"]); ?>" <?php if (!empty($_GET['from_id']) AND $_GET['from_id'] == $payments[$i]["id"]) echo ' class="fade"'; ?>>
				<td><?php e($payments[$i]["dk_date_created"]); ?></td>
				<td><a href="payment.php?id=<?php e($payments[$i]['id']); ?>">
					<?php
					if ($payments[$i]["transaction_number"] == "") {
						e("Ej angivet");
					} else {
						e($payments[$i]["transaction_number"]);
					}
					?></a>
				</td>
				<td>
					<?php
					switch($payments[$i]['belong_to']) {
						case "invoice":
							if ($kernel->user->hasModuleAccess('invoice')) {
								$debtor_module = $kernel->useModule('debtor');
								print("<a href=\"".$debtor_module->getPath()."view.php?id=".$payments[$i]['belong_to_id']."\">Faktura</a>");
							}
							else {
								e("Faktura");
							}
						break;
						case "order":
							if ($kernel->user->hasModuleAccess('order')) {
								$debtor_module = $kernel->useModule('debtor');
								print("<a href=\"".$debtor_module->getPath()."view.php?id=".$payments[$i]['belong_to_id']."\">Ordre</a>");
							}
							else {
								e("Ordre");
							}
						break;
						default:
							e("Ingen");
					}
					?>
				</td>
				<td><?php e($payments[$i]["dk_amount"]); ?></td>
				<td>
					<?php
					if ($payments[$i]["status"] == 'captured') {
						$saldo += $payments[$i]["amount"];
					}


					e($translation->get($payments[$i]["status"]));

					if ($payments[$i]['user_transaction_status_translated'] != "") {
						e(" (".$payments[$i]['user_transaction_status_translated']);
                        if ($payments[$i]['pbs_status'] != '' && $payments[$i]['pbs_status'] != '000') {
                            e(": ".$payments[$i]['pbs_status']);
                        }
                        e(")");
					}
					elseif ($payments[$i]['status'] == 'authorized') {
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

<p><strong>Hævet i alt på søgningen: </strong> <?php e($saldo); ?> kroner.</p>
<?php endif; ?>
<?php

$page->end();

?>
