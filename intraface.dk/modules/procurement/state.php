<?php
require('../../include_first.php');

$mDebtor = $kernel->module('procurement');
$kernel->useModule('accounting');

$year = new Year($kernel);
$voucher = new Voucher($year);

if (!empty($_POST)) {
	#
	# Vi skal have lavet noget bogføring af payment
	# og credit_notes
	# og reminders
	#

	$procurement = new Procurement($kernel, intval($_POST["id"]));
	$procurement->setStateAccountId((int)$_POST['state_account_id']);
	if (!$procurement->state($year, $_POST['voucher_number'])) {
		$procurement->error->set('Kunne ikke bogføre posten');
		$value = $_POST;
	}
}
else {
	$procurement = new Procurement($kernel, intval($_GET["id"]));
	$procurement->readyForState();
}

$value = $procurement->get();


$page = new Page($kernel);
$page->start('Bogfør indkøb #' . $procurement->get('number'));

?>
<h1>Bogfør indkøb #<?php echo $procurement->get('number'); ?></h1>

<p class="warning">
	<strong>Betafuntion - under test</strong>: Du skal være opmærksom på at denne funktion altid sætter fakturaerne på kreditorkontoen, og at den bruger betalingsdatoen som bogføringsdato. Desuden sætter den automatisk beløbet for forsendelse mv. på den valgte bogføringskonto.
</p>

<ul class="options">
	<li><a href="view.php?id=<?php print($procurement->get("id")); ?>">Luk</a></li>
	<li><a href="index.php?type=invoice&amp;id=<?php print($procurement->get("id")); ?>&amp;use_stored=true">Tilbage til indkøbslisten</a></li>
</ul>

<?php echo $procurement->error->view(); ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" value="<?php echo $value['id']; ?>" name="id" />
<fieldset>
	<legend>Oplysninger der bogføres</legend>

		<table>
					<tr>
						<th>Bilagsnummer</th>
						<td>
							<?php if (!$procurement->isStated()): ?>
							<input type="text" name="voucher_number" value="<?php echo $voucher->getMaxNumber() + 1; ?>" />
							<?php else: ?>
							<?php echo $procurement->get("voucher_number"); ?>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th>Dato:</th>
						<td><?php print($procurement->get("dk_paid_date")); ?></td>
					</tr>
					<tr>
						<th>Beløb:</th>
						<td><?php print($procurement->get("dk_total_price_items")); ?> kroner</td>
					</tr>
					<tr>
						<th>Forsendelse mv:</th>
						<td><?php print($procurement->get("dk_price_shipment_etc")); ?> kroner</td>
					</tr>

					<tr>
						<th>Moms:</th>
						<td><?php print($procurement->get("dk_vat")); ?> kroner</td>
					</tr>

					<?php if ($procurement->isStated()): ?>
					<tr>
						<th>Bogført:</th>
						<td><?php echo $procurement->get("date_stated_dk"); ?></td>
					</tr>
					<?php elseif ($kernel->user->hasModuleAccess('accounting')): ?>
					<tr>
						<th>Bogføres på konto:</th>
						<td>
							<select name="state_account_id">
								<option value="">Vælg</option>
							<?php
								$account = new Account($year);
								$accounts = $account->getList('expenses');
								foreach ($accounts AS $account):
									echo '<option value="'.$account['id'].'">'.$account['name'].'</option>';
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
<?php
$page->end();
?>
