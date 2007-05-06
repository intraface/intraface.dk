<?php
require('../../include_first.php');
$module = $kernel->module("debtor");

$translation = $kernel->getTranslation('debtor');

$mainInvoice = $kernel->useModule("invoice");
$mainInvoice->includeFile("Reminder.php");
$mainInvoice->includeFile("ReminderItem.php");

$reminder = new Reminder($kernel, intval($_GET["id"]));
$payment = new Payment($reminder);

if(isset($_GET["action"]) && $_GET["action"] == "sent") {
	$reminder->setStatus("sent");
}

if(isset($_POST["payment"])) {
	$payment->update($_POST);
}

if(isset($_GET['return_redirect_id'])) {
	$return_redirect = Redirect::factory($kernel, 'return');

	if($return_redirect->get('identifier') == 'send_email') {
		if($return_redirect->getParameter('send_email_status') == 'sent') {
			$reminder->setStatus('sent');
			$return_redirect->delete();
		}

	}
}

$page = new Page($kernel);
$page->start("Rykker");

?>

<div id="colOne"> <!-- style="float: left; width: 45%;" -->
<div class="box">
<h1>Rykker #<?php print(safeToHtml($reminder->get("number"))); ?></h1>

<ul class="options">
	<?php if($reminder->get("locked") == false) {
		?>
			<li><a href="reminder_edit.php?id=<?php print(intval($reminder->get("id"))); ?>">Ret</a></li>
		<?php
	}
	?>

		<li><a class="pdf" href="reminder_pdf.php?id=<?php print(intval($reminder->get("id"))); ?>" target="_blank">Udskriv PDF</a></li>
	<?php
	if($reminder->get("send_as") == "email" AND $reminder->get('status_key') < 1) {
		?>
		<li><a href="reminder_email.php?id=<?php print(intval($reminder->get("id"))); ?>">Send E-mail</a></li>
		<?php
	}

	if($reminder->get("status") == "created" AND $reminder->get("send_as") != "email") {
		?>
		<li><a href="reminder.php?id=<?php print(intval($reminder->get("id"))); ?>&amp;action=sent">Markér som sendt</a></li>
		<?php
	}
	?>
	<li><a href="list.php?type=invoice&amp;use_stored=true">Tilbage til fakturaer</a></li>
	<li><a href="reminders.php?id=<?php print(intval($reminder->get("id"))); ?>&amp;use_stored=true">Luk</a></li>
</ul>

<p><?php print(safeToHtml($reminder->get('description'))); ?></p>

</div>

<?php $reminder->error->view(); ?>


				<table>
					<caption>Rykkerinformationer</caption>
					<tr>
						<th>Dato:</th>
						<td><?php print(safeToHtml($reminder->get("dk_this_date"))); ?></td>
					</tr>
					<tr>
						<th>Forfaldsdato:</th>
						<td><?php print(safeToHtml($reminder->get("dk_due_date"))); ?></td>
					</tr>
					<tr>
						<th>Betalingmetode</th>
						<td><?php print(safeToHtml($reminder->get("payment_method"))); ?></td>
					</tr>
					<?php if($reminder->get("payment_method_key") == 3): ?>
						<tr>
							<th>Girolinje</th>
							<td>+71&lt;<?php echo str_repeat("0", 15 - strlen($reminder->get("girocode"))).safeToHtml($reminder->get("girocode")); ?> +<?php print(safeToHtml($kernel->setting->get("intranet", "giro_account_number"))); ?>&lt;</td>
						</tr>
					<?php endif; ?>
					<?php if($reminder->get("status") == "cancelled"): ?>
						<tr>
							<th>Afskrevet dato</th>
							<td><?php print(safeToHtml($reminder->get("dk_date_cancelled"))); ?></td>
						</tr>
					<?php endif; ?>
					<?php if($reminder->get("status") == "executed"): ?>
						<tr>
							<th>Færdigbehandlet dato:</th>
							<td><?php print(safeToHtml($reminder->get("dk_date_executed"))); ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<th>Send som:</th>
						<td><?php print(safeToHtml($reminder->get("send_as"))); ?></td>
					</tr>

					
				<?php if ($kernel->setting->get('intranet', 'debtor.sender') == 'user' || $kernel->setting->get('intranet', 'debtor.sender') == 'defined'): ?>
				<tr>
					<th>Vores ref.</th>
						<td>
							<?php
							switch($kernel->setting->get('intranet', 'debtor.sender')) {
								case 'user':
									echo $kernel->user->address->get('name'). ' &lt;'.$kernel->user->address->get('email').'&gt;';
									break;
								case 'defined':
									echo $kernel->setting->get('intranet', 'debtor.sender.name').' &lt;'.$kernel->setting->get('intranet', 'debtor.sender.email').'&gt;';
									break;
							}
							
							if($kernel->user->hasModuleAccess('administration')) {
								$debtor_module = $kernel->useModule('debtor');
								echo ' <a href="'.$debtor_module->getPath().'setting.php" class="edit">'.safeToHtml($translation->get('change')).'</a></p>';	
							} 
							?>
						</td>
				</tr>
			<?php endif; ?>
					
					
					
					<tr>
						<th>Status:</th>
						<td><?php print(safeToHtml($translation->get($reminder->get("status")))); ?></td>
					</tr>
				</table>

<fieldset>
	<legend>Tekst</legend>
	<p><?php print(nl2br(safeToHtml($reminder->get("text")))); ?></p>
</fieldset>

</div>

<div id="colTwo">

<div class="box">
	<table>
		<caption>Kontaktoplysninger</caption>

		<tr>
			<th>Nummer</th>
			<?php
			$contact_module = $kernel->getModule('contact');
			?>
			<td><?php print(safeToHtml($reminder->contact->get("number"))); ?> <a href="<?php print($contact_module->getPath()); ?>contact_edit.php?id=<?php echo intval($reminder->contact->get('id')); ?>" class="edit">Ret</a></td>
		</tr>
		<tr>
			<th>Kontakt</th>
			<td><a href="<?php echo $contact_module->getPath().'contact.php?id='.$reminder->contact->get('id'); ?>"><?php print(safeToHtml($reminder->contact->address->get("name"))); ?></a></td>
		</tr>
		<tr>
			<th>Adresse</th>
			<td>
				<?php
				if(isset($reminder->contact_person) && is_object($reminder->contact_person) && strtolower(get_class($reminder->contact_person)) == "contactperson") {
					print("Att: ".safeToHtml($reminder->contact_person->get("name"))) . '<br />';
				}
				?>
				<?php print(nl2br(safeToHtml($reminder->contact->address->get("address")))); ?><br />
				<?php print(safeToHtml($reminder->contact->address->get("postcode")." ".$reminder->contact->address->get("city"))); ?>
			</td>
		</tr>
		<?php if(isset($reminder->contact_person) && strtolower(get_class($reminder->contact_person)) == "contactperson"): ?>
			<tr>
				<th>Att.</th>
				<td><?php echo safeToHtml($reminder->contact_person->get("name")); ?></td>
			</tr>
		<?php endif; ?>
	</table>

</div>

<?php if($reminder->get("status") == "sent"): ?>
	<div class="box">

		<div style="border: 2px solid red; padding: 5px; margin: 10px;">
			<strong>Vigtigt</strong>: Registering af betaling her vedrører indtil videre KUN rukkergebyret på DENNE rykker. Dvs. du skal registere betalingen for fakturaer og tidligere rykkere på de respektive fakturaer og rykkere!
		</div>
		<form method="post" action="reminder.php?id=<?php print(intval($reminder->get("id"))); ?>">
		<div class="formrow">
			<label for="payment_date">Dato</label>
			<input type="input" name="payment_date" id="payment_date" value="<?php print(safeToHtml(date("d-m-Y"))); ?>" />
		</div>

		<div class="formrow">
			<label for="amount">Beløb</label>
			<input type="input" name="amount" id="amount" value="<?php print(number_format($reminder->get("arrears"), 2, ",", ".")); ?>" />
		</div>

		<div class="formrow">
			<label for="type">Type</label>
			<select name="type" id="type">
				<?php
				$invoice_module = $kernel->getModule("invoice");
				$types = $invoice_module->getSetting("payment_type");
				foreach($types AS $key => $value) {
					?>
					<option value="<?php print(safeToHtml($key)); ?>" <?php if($key == 0) print("selected='selected'"); ?> ><?php print(safeToHtml($translation->get($value))); ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<input class="confirm" type="submit" name="payment" value="Betalt" title="Dette vil registrere betalingen" />
		</form>
	</div>
<?php endif; ?>

</div> <!-- colTwo -->

<div style="clear:both;"></div>

<?php if($reminder->get('status') == 'sent' || $reminder->get('status') == 'executed'): ?>
	<?php
	$payments = $payment->getList();
	$payment_total = 0;
	if(count($payments) > 0) {
		?>
		<table style="clear:both;">
			<caption>Betaling (rykkergebyr)</caption>
			<thead>
				<tr>
					<th>Dato</th>
					<th>Type</th>
					<th>Beskrivelse</th>
					<th>Beløb</th>
				</tr>
			</thead>
  		<tbody>
			<?php
			for($i = 0, $max = count($payments); $i < $max; $i++) {
				$payment_total += $payments[$i]["amount"];
				?>
				<tr>
					<td><?php print(safeToHtml($payments[$i]["dk_date"])); ?></td>
					<td><?php print(safeToHtml($payments[$i]["type"])); ?></td>
					<td>
						<?php
						if($payments[$i]["type"] == "credit_note") {
							?>
							<a href="view.php?id=<?php print(intval($payments[$i]["id"])); ?>"><?php print(safeToHtml($payments[$i]["description"])); ?></a>
							<?php
						}
						else {
							print(safeToHtml($payments[$i]["description"]));
						}
						?>
					</td>
					<td><?php print(number_format($payments[$i]["amount"], 2, ",", ".")); ?></td>
				</tr>
				<?php
			}

			?>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>Betalt</td>
				<td><?php print(number_format($payment_total, 2, ",", ".")); ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>Manglende betaling</td>
				<td><?php print(number_format($reminder->get("total") - $payment_total, 2, ",", ".")); ?></td>
			</tr>
			</tbody>
		</table>
		<?php
	}
	?>
<?php endif; ?>



	<table class="stribe">
		<caption>Indhold</caption>
		<thead>
			<tr>
				<th>Nr.</th>
				<th>Beskrivelse</th>
				<th>Forfaldsdato</th>
				<th>Beløb</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$reminder->loadItem();
			$items = $reminder->item->getList("invoice");
			$total = 0;

			if(count($items) > 0) {
				?>
				<tr>
					<td colspan="4"><b>Fakturaer med restance:</b></td>
				</tr>
				<?php
			}

			for($i = 0, $max = 0; $i < count($items); $i++) {
				$total += $items[$i]["arrears"];
				?>
				<tr>
					<td class="number"><?php print(safeToHtml($items[$i]["number"])); ?></td>
					<td><a href="<?php echo $module->getPath().'view.php?id='.intval($items[$i]['invoice_id']); ?>"><?php print(safeToHtml($items[$i]["description"])); ?></a></td>
					<td class="date"><?php print(safeToHtml($items[$i]["dk_due_date"])); ?></td>
					<td class="amount"><?php print(number_format($items[$i]["arrears"], 2, ",",".")); ?></td>
				</tr>
				<?php
			}

			$items = $reminder->item->getList("reminder");
			if(count($items) > 0) {
				?>
				<tr>
  				<td colspan="4"><b>Tidligere rykkere:</b></td>
				</tr>
				<?php
			}



			for($i = 0, $max = 0; $i < count($items); $i++) {
				$total += $items[$i]["reminder_fee"];
				?>
				<tr>
					<td class="number"><?php print(safeToHtml($items[$i]["number"])); ?></td>
					<td><a href="reminder.php?id=<?php print(intval($items[$i]["reminder_id"])); ?>"><?php print(safeToHtml($items[$i]["description"])); ?></a></td>
					<td class="date"><?php print(safeToHtml($items[$i]["dk_due_date"])); ?></td>
					<td class="amount"><?php print(number_format($items[$i]["reminder_fee"], 2, ",",".")); ?></td>
				</tr>
				<?php
			}

			if($reminder->get("reminder_fee") != 0) {
				$total += $reminder->get("reminder_fee");
				?>
				<tr>
					<td colspan="2"><b>Rykkergebyr</b></td>
					<td class="date">&nbsp;</td>
					<td class="amount"><?php print(number_format($reminder->get("reminder_fee"), 2, ",",".")); ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><strong>Total:</strong></td>
				<td class="amount"><strong><?php print(number_format($total, 2, ",",".")); ?></strong></td>
			</tr>
		</tfoot>
	</table>



<?php
$page->end();
?>