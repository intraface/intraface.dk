<?php
require('../../include_first.php');

$module = $kernel->module("procurement");
$translation = $kernel->getTranslation('procurement');

if(isset($_POST["submit"])) {

	$procurement = new Procurement($kernel, intval($_POST["id"]));

	if($procurement->update($_POST)) {

		if(isset($_POST["recieved"]) && $_POST["recieved"] == "1") {
			$procurement->setStatus("recieved");
		}

		header("location: view.php?id=".$procurement->get("id"));
        exit;
	}
	else {
		$values = $_POST;
		$title = "Ret indkøb";
	}
}
elseif(isset($_GET["id"])) {

	$procurement = new Procurement($kernel, intval($_GET["id"]));
	$values = $procurement->get();
	$title = "Ret indkøb";
}
else {
	$procurement = new Procurement($kernel);
	$values["number"] = $procurement->getMaxNumber() + 1;
	$title = "Opret indløb";
}

$page = new Page($kernel);
$page->includeJavascript('module', 'edit.js');
$page->start();
?>
<h1><?php print($title); ?></h1>

<?php echo $procurement->error->view(); ?>

<form action="edit.php" method="POST">
<fieldset>
	<legend>Oplysninger</legend>

	<div class="formrow">
		<label for="number">Indkøbsnummer</label>
		<input type="text" name="number" id="number" value="<?php if (!empty($values['number'])) echo safeToForm($values["number"]); ?>" />
	</div>

	<div class="formrow">
		<label for="description">Beskrivelse</label>
		<input type="text" name="description" id="description" value="<?php  if (!empty($values['description'])) echo safeToForm($values["description"]); ?>" size="30" />
	</div>

	<div class="formrow">
		<label for="dk_invoice_date">Fakturadato</label>
		<input type="text" name="dk_invoice_date" id="dk_invoice_date" value="<?php  if (!empty($values['dk_invoice_date'])) echo safeToForm($values["dk_invoice_date"]); ?>" size="10" onBlur="fillDateFields();" />
	</div>

	<div class="formrow">
		<label for="dk_delivery_date">Leveringsdato</label>
		<input type="text" name="dk_delivery_date" id="dk_delivery_date" value="<?php  if (!empty($values['dk_delivery_date'])) echo safeToForm($values["dk_delivery_date"]); ?>" size="10" />
	</div>

	<div class="formrow">
		<label for="dk_payment_date">Betalingsdato</label>
		<input type="text" name="dk_payment_date" id="dk_payment_date" value="<?php  if (!empty($values['dk_payment_date'])) echo safeToForm($values["dk_payment_date"]); ?>" size="10" />
	</div>

	<div class="formrow">
		<label for="from_region">Køb fra</label>
		<select name="from_region" id="from_region">
			<?php
			$from_region = $module->getSetting("from_region");

			foreach($from_region AS $key => $region) {
				?>
				<option value="<?php print($key); ?>" <?php if(!empty($values["from_region"]) AND $values["from_region"] == $key) print("selected='selected'"); ?> ><?php echo safeToHtml($translation->get($region)); ?></option>
				<?php
			}
			?>
		</select>
	</div>

	<div class="formrow">
		<label for="vendor">Leverandør</label>
		<input type="text" name="vendor" id="vendor" value="<?php  if (!empty($values['vendor'])) echo safeToForm($values["vendor"]); ?>" size="30" />
	</div>
</fieldset>

<fieldset>
	<legend>Pris</legend>

	<div class="formrow">
		<label for="dk_price_items">Pris for varer (eks. forsendelse, gebyr osv.)</label>
		<input type="text" name="dk_price_items" id="dk_price_items" value="<?php if (!empty($values['dk_price_items'])) print($values["dk_price_items"]); ?>" size="10" /> Kr. (eks. moms)
	</div>
    
    <div class="formrow">
        <label for="dk_price_shipment_etc">Pris for forsendelse, gebyr osv.</label>
        <input type="text" name="dk_price_shipment_etc" id="dk_price_shipment_etc" value="<?php  if (!empty($values['dk_price_shipment_etc'])) echo safeToForm($values["dk_price_shipment_etc"]); ?>" size="10" /> Kr. (eks. evt. moms)
    </div>

	<div class="formrow">
		<label for="dk_vat">Moms</label>
		<input type="text" name="dk_vat" id="vat" value="<?php  if (!empty($values['dk_vat'])) echo safeToHtml($values["dk_vat"]); ?>" size="10" /> Kr.
	</div>

</fieldset>

<input type="submit" class="save" name="submit" value="Gem" />
<a href="index.php">Fortryd</a>

<input type="hidden" name="id" value="<?php print($procurement->get("id")); ?>" />

</form>

<?php
$page->end();
?>