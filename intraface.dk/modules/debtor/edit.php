<?php
require('../../include_first.php');

$module = $kernel->module('debtor');
$kernel->useModule('invoice');
$translation = $kernel->getTranslation('debtor');

if(!empty($_POST)) {
	$debtor = Debtor::factory($kernel, intval($_POST["id"]), $_POST["type"]);
	$contact = new Contact($kernel, $_POST["contact_id"]);

	if(isset($_POST["contact_person_id"]) && $_POST["contact_person_id"] == "-1") {
		$contact_person = new ContactPerson($contact);
		$person["name"] = $_POST['contact_person_name'];
		$person["email"] = $_POST['contact_person_email'];
		$contact_person->save($person);
		$contact_person->load();
		$_POST["contact_person_id"] = $contact_person->get("id");
	}

	if($debtor->update($_POST)) {
		header("Location: view.php?id=".$debtor->get("id"));
		exit;
	} else {
		$value = $_POST;

		$action = 'edit';
		$value["dk_due_date"] = $value["due_date"];
		$value["dk_this_date"] = $value["this_date"];
	}
} elseif(!empty($_GET["id"])) {
	$debtor = Debtor::factory($kernel, intval($_GET["id"]));

	$action = 'edit';
	$contact = new Contact($kernel, $debtor->get('contact_id'));
	$value = $debtor->get();

} else {
	$debtor = Debtor::factory($kernel, 0, $_GET["type"]);

	$action = 'create';
	if(isset($_GET['contact_id'])) {
		$contact = new Contact($kernel, intval($_GET['contact_id']));
	} elseif(isset($_GET['return_redirect_id'])) {
		$redirect = Redirect::factory($kernel, 'return');
		$contact_id = $redirect->getParameter('contact_id');
		$contact = new Contact($kernel, intval($contact_id));
	} else {
		trigger_error("A contact id i needed to create a new debtor", E_USER_ERROR);
	}

	/*
	LAVES
	if($contact->address->get("type") == ") {
		$value["attention_to"] = $contact->address->get("contactname");
	}
	*/

	$value["number"] = intval($debtor->getMaxNumber()) + 1;
	$value["dk_this_date"] = date("d-m-Y");
	if($debtor->get("type") == "invoice") {
		$value["dk_due_date"] = date("d-m-Y", time() + 24 * 60 * 60 * $contact->get("paymentcondition"));
	}
	else {
		$value["dk_due_date"] = "";
	}
	$value["payment_method_id"] = 1;

}

$page = new Page($kernel);
$page->includeJavascript('module', 'edit.js');
$page->start(safeToHtml($translation->get($action.' '.$debtor->get('type'))));

?>
<h1><?php echo safeToHtml($translation->get($action.' '.$debtor->get('type'))); ?></h1>

<?php if($kernel->intranet->address->get('id') == 0): ?>
	<p>Du mangler at udfylde adresse til dit intranet. Det skal du gøre, før du kan oprette en <?php print(safeToHtml(strtolower($translation->get($debtor->get('type'))))); ?>.
	<?php if($kernel->user->hasModuleAccess('administration')): ?>
		<?php
		$module_administration = $kernel->useModule('administration');
		?>
		<a href="<?php echo safeToHtml($module_administration->getPath().'intranet_edit.php'); ?>">Udfyld adresse</a>.</p>
	<?php else: ?>
		Du har ikke adgang til at rette adresseoplysningerne, det må du bede din administrator om at gøre.</p>
	<?php endif; ?>
<?php else: ?>


<?php echo $debtor->error->view($translation); ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<fieldset>
	<legend><?php echo safeToHtml($translation->get($debtor->get('type').' data')); ?></legend>

	<div class="formrow">
		<label for="number"><?php echo safeToHtml($translation->get($debtor->get('type').' number')); ?></label>
    <input type="text" name="number" id="number" value="<?php print(safeToForm($value["number"])); ?>" />
	</div>
	<div class="formrow">
		<label for="description">Beskrivelse</label>
		<input class="input" id="description" name="description" value="<?php if(isset($value["description"])) print(safeToForm($value["description"])); ?>" size="60" />
	</div>
	<div class="formrow">
		<label for="this_date">Dato</label>
		<input class="input" name="this_date" id="this_date" value="<?php if(isset($value["dk_this_date"])) print(safeToForm($value["dk_this_date"])); ?>" size="10" />
	</div>
	<div class="formrow">
		<label for="due_date"><?php echo safeToHtml($translation->get($debtor->get('type').' due date')); ?></label>
		<input class="input" name="due_date" id="due_date" value="<?php if(isset($value["dk_due_date"])) print(safeToForm($value["dk_due_date"])); ?>" size="10" />
	</div>
	<?php
	if($debtor->get("type") == "invoice") {
		?>
		<div class="formrow">
			<label for="round_off">Afrund (kontantsalg)</label>
			<input class="input" type="checkbox" name="round_off" id="round_off" value="1" size="10" <?php if(isset($value["round_off"]) && $value["round_off"] == 1) print('checked="checked"'); ?> />
		</div>
		<?php
	}
	?>
	
	<div class="formrow">
		<label for="message">Besked</label>
		<textarea id="message" type="text" name="message" cols="80" rows="5"><?php if(isset($value["message"])) print(safeToForm($value["message"])); ?></textarea>
	</div>

</fieldset>

<fieldset>
	<legend>Kundeoplysninger</legend>
	<div class="formrow">
	  <label for="contact_id">Kunde</label>
    <span id="contact_id"><?php print(safeToHtml($contact->address->get("name"))); ?></span>
	</div>
	<?php
	if($contact->get("type") == "corporation") {
		?>
		<div class="formrow">
			<label for="contact_person_id">Att.</label>
			<select name="contact_person_id" id="contact_person_id">
				<option value="0"></option>
				<?php
				$persons = $contact->contactperson->getList();

				for($i = 0, $max = count($persons); $i < $max; $i++) {
					?>
					<option value="<?php print(intval($persons[$i]["id"])); ?>" <?php if(!empty($value["contact_person_id"]) AND $value["contact_person_id"] == $persons[$i]["id"]) print('selected="selected"'); ?> ><?php print(safeToForm($persons[$i]["name"])); ?></option>
					<?php
				}
				?>
				<option value="-1">Opret ny >></option>
			</select>
		</div>
		<fieldset id="contactperson">
			<legend>Ny kontaktperson</legend>
			<div class="formrow">
				<label for="contactperson-name">Navn</label>
				<input id="contactperson-name" type="text" name="contact_person_name" value="" />
			</div>
			<div class="formrow">
				<label for="contactperson-email">E-mail</label>
				<input id="contactperson-email" type="text" name="contact_person_email" value="" />
			</div>

		</fieldset>
		<?php
	}
	?>

</fieldset>

<?php

if($debtor->get("type") == "invoice" || $debtor->get("type") == "order") {
	?>
	<fieldset class="radiobuttons">
		<legend>Betalingsoplysninger</legend>
		<p><?php echo safeToHtml($translation->get('which payment method do you want to show on the '.$debtor->get("type"))); ?></p>
		<div>
			<label<?php if(isset($value['payment_method']) && $value['payment_method'] == 0) print(" class=\"selected\""); ?>><input class="input" id="none" type="radio" name="payment_method" value="0" <?php if(isset($value['payment_method']) && $value['payment_method'] == 0) print("checked=\"CHECKED\""); ?> />
			Ingen</label>
		</div>
    <?php if ($kernel->setting->get('intranet', 'bank_account_number')) { ?>
		<div>
			<label<?php if(isset($value['payment_method']) AND $value['payment_method'] == 1) print(' class="selected"'); ?>><input class="input" id="account" type="radio" name="payment_method" value="1" <?php if(isset($value['payment_method']) AND $value['payment_method'] == 1) print("checked=\"CHECKED\""); ?> />
			Kontooverførsel</label>
		</div>
    <?php } else { echo '<p>Hvis du ønsker at modtage penge via kontooverførsel, kan du indtaste dit kontonummer under <a href="setting.php">Indstillingerne</a>.</p>'; }?>
    <?php if ($kernel->setting->get('intranet', 'giro_account_number')) { ?>
		<div>
			<label for="giro01"<?php if(isset($value['payment_method']) AND $value['payment_method'] == 2) print ' class="selected"'; ?>><input class="input" type="radio" id="giro01" name="payment_method" value="2" <?php if(isset($value['payment_method']) AND $value['payment_method'] == 2) print("checked=\"CHECKED\""); ?> />
			Girokort +01</label>
		</div>
		<div class="specialcase<?php if(isset($value['payment_method']) AND $value['payment_method'] == 3) print(" selected"); ?>">
			<input class="input" id="giro71" type="radio" name="payment_method" value="3" <?php if(isset($value['payment_method']) AND $value['payment_method'] == 3) print("checked=\"CHECKED\""); ?> />
			<label for="giro71">Girokort +71</label> &lt;
			<label for="girocode" style="display: none;">Girokode</label> <input class="input" name="girocode" id="girocode" value="<?php if(isset($value['girocode'])) print safeToHtml($value['girocode']); ?>" size="16" onfocus="if(document.getElementById) document.getElementById('giro71').checked = true;" /> + <?php echo $kernel->setting->get("intranet", "giro_account_number"); ?>&lt;
		</div>
    <?php } ?>
	</fieldset>
	<?php
}
?>

<div>
<input type="submit" class="save" name="submit" value="Videre" />
eller
<?php if (!$debtor->get("id")) { ?>
<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">Fortryd</a>
<?php } else { ?>
<a href="view.php?id=<?php print(safeToHtml($debtor->get("id"))); ?>">Fortryd</a>
<?php } ?>
</div>

<input type="hidden" name="id" value="<?php print(intval($debtor->get("id"))); ?>" />
<input type="hidden" name="type" value="<?php print(safeToForm($debtor->get("type"))); ?>" />
<input type="hidden" name="contact_id" value="<?php print(intval($contact->get('id'))); ?>" />
</form>

<?php endif; ?>

<?php
$page->end();
?>