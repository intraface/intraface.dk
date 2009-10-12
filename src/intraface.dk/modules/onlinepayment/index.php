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
$page->start('Online payments');
?>

<h1><?php e(t('Online payments')); ?></h1>

<?php if (!$onlinepayment->isProviderSet()): ?>

	<p>For at bruge onlinebetalinger, skal du have valgt en udbyder. <a href="choose_provider.php">Vælg udbyder</a>.</p>

<?php elseif (!$onlinepayment->isSettingsSet()): ?>

	<p>For at bruge onlinebetalinger, skal du have sat nogle indstillinger. <a href="settings.php">Sæt indstillinger</a>.</p>


<?php elseif (!$onlinepayment->isFilledIn()): ?>
	<p>Der er ikke oprettet nogen betalinger.</p>
<?php else: ?>


<fieldset class="hide_on_print">
	<legend><?php e(t('Search', 'common')); ?></legend>
	<form method="get" action="index.php">
		<label><?php e(t('Text', 'common')); ?>
			<input type="text" name="text" value="<?php e($onlinepayment->getDBQuery()->getFilter("text")); ?>" />
		</label>
		<label><?php e(t('Status', 'common')); ?>
		<select name="status">
			<option value="-1"><?php e(t('All', 'common')); ?></option>
			<?php
			$status_types = OnlinePayment::getStatusTypes();
			for ($i = 1, $max = count($status_types); $i < $max; $i++) {
				?>
				<option value="<?php e($i); ?>" <?php if ($onlinepayment->getDBQuery()->getFilter("status") == $i) echo ' selected="selected"';?>><?php e(__($status_types[$i])); ?></option>
				<?php
			}
			?>
			</select>
		</label>
        <label><?php e(t('From date', 'common')); ?>
            <input type="text" name="from_date" id="date-from" value="<?php e($onlinepayment->getDBQuery()->getFilter("from_date")); ?>" /> <span id="calender"></span>
        </label>
        <label><?php e(t('To date', 'common')); ?>
            <input type="text" name="to_date" value="<?php e($onlinepayment->getDBQuery()->getFilter("to_date")); ?>" />
        </label>
		<span>
		<input type="submit" value="<?php e(t('Search', 'common')); ?>" />
		</span>
	</form>
</fieldset>

<table class="stripe">
	<caption><?php e(t('Online payments')); ?></caption>
	<thead>
		<tr>
			<th><?php e(t('Date', 'common')); ?></th>
			<th><?php e(t('Transaction number')); ?></th>
			<th><?php e(t('Related to')); ?></th>
			<th><?php e(t('Amount', 'common')); ?></th>
			<th><?php e(t('Status', 'common')); ?></th>
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
				<td class="amount">
                    <?php
                    if($payments[$i]['currency'] && is_object($payments[$i]['currency'])) {
                        e($payments[$i]['currency']->getType()->getIsoCode().' ');
                    } elseif($kernel->intranet->hasModuleAccess('currency')) {
                        e('DKK ');
                    }

                    e($payments[$i]["dk_amount"]); ?></td>
				<td>
					<?php
					if ($payments[$i]["status"] == 'captured') {
						$saldo += $payments[$i]["amount"];
					}

					e(__($payments[$i]["status"]));

					if ($payments[$i]['user_transaction_status_translated'] != "") {
						e(" (".$payments[$i]['user_transaction_status_translated']);
                        if ($payments[$i]['pbs_status'] != '' && $payments[$i]['pbs_status'] != '000') {
                            e(": ".$payments[$i]['pbs_status']);
                        }
                        e(")");
					} elseif ($payments[$i]['status'] == 'authorized') {
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

<p><strong><?php e(t('Total captured on the search')); ?>: </strong> <?php e($saldo); ?></p>
<?php endif; ?>
<?php
$page->end();
?>