<?php

/* UNDER DEVELOPMENT */


/**
 * Settings for comments
 *
 * @author  Lars Olesen <lars@legestue.net>
 * @version 1.0
 * @todo    Mulighed for at vælge størrelser på gravatar og sætte sin egen standard
 *
 */

require('../../include_first.php');

$kernel->module('comment');
$translation = $kernel->getTranslation('comment');

if (!empty($_POST)) {

	// reminder
	$kernel->setting->set('intranet', 'reminder.first.text', $_POST['reminder_text']);

	// bank
	$kernel->setting->set('intranet', 'bank_name', $_POST['bank_name']);
	$kernel->setting->set('intranet', 'bank_reg_number', $_POST['bank_reg_number']);
	$kernel->setting->set('intranet', 'bank_account_number', $_POST['bank_account_number']);
	$kernel->setting->set('intranet', 'giro_account_number', $_POST['giro_account_number']);


	if (!empty($_POST['scan_in_contact']) AND Validate::email($_POST['scan_in_contact'])) {
		$kernel->useModule('contact');

		$contact = new Contact($kernel);
		if ($contact_id = $contact->save(array(
			'name' => 'Læs Ind Bureau (bør ikke ændres)',
			'email' => $_POST['scan_in_contact']
		))) {
			$kernel->setting->set('intranet', 'debtor.scan_in_contact', $contact_id);

		}
	}

	header('Location: setting.php');
 	exit;

}
else {
	// find settings frem
	$values['bank_name'] = $kernel->setting->get('intranet', 'bank_name');
	$values['bank_reg_number'] = $kernel->setting->get('intranet', 'bank_reg_number');
	$values['bank_account_number'] = $kernel->setting->get('intranet', 'bank_account_number');
	$values['giro_account_number'] = $kernel->setting->get('intranet', 'giro_account_number');
	$values['reminder_text'] = $kernel->setting->get('intranet', 'reminder.first.text');
	$values['scan_in_contact'] = $kernel->setting->get('intranet', 'debtor.scan_in_contact');
	if (!empty($values['scan_in_contact'])) {
		$contact = new Contact($kernel, $values['scan_in_contact']);
		$values['scan_in_email'] = $contact->address->get('email');
	}
	
}

$page = new Page($kernel);
$page->start('Indstillinger');
?>

<h1>Indstillinger</h1>

<?php // echo $oSetting->error->view(); ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<fieldset>
		<legend>Bankoplysninger</legend>
		<fieldset>
			<legend>Kontobetaling:</legend>
			<div class="formrow">
				<label for="bankname">Bank</label>
				<input type="text" name="bank_name" id="bankname" value="<?php print $values['bank_name']; ?>" />
			</div>
			<div class="formrow">
				<label for="regnumber">Registreringsnummer</label>
				<input type="text" name="bank_reg_number" id="regnumber" value="<?php echo $values['bank_reg_number']; ?>" />
			</div>
			<div class="formrow">
				<label for="accountnumber">Kontonummer</label>
				<input type="text" name="bank_account_number" id="accountnumber" value="<?php echo $values['bank_account_number']; ?>" />
			</div>
		</fieldset>
		<fieldset>
			<legend>Girobetaling:</legend>
			<div class="formrow">
				<label for="giroaccountnumber">Girokontonummer</label>
				<input type="text" name="giro_account_number" id="giroaccountnumber" value="<?php echo $values['giro_account_number']; ?>" />
			</div>
		</fieldset>
	</fieldset>

	<fieldset>
		<legend>E-mail til Læs-ind bureau</legend>
		<div class="formrow">
			<label for="scan-in">E-mail</label>
			<?php if (!empty($values['scan_in_email'])): ?>
				<?php echo $values['scan_in_email']; ?> <a href="<?php echo $_SERVER['PHP_SELF'] ?>?action=deletescanin">Slet Læs-ind e-mail</a>
			<?php else: ?>
			<input type="text" value="" name="scan_in_contact" />
			<?php endif; ?>
		</div>
	</fieldset>

	<fieldset>
		<legend>Tekst på rykker</legend>
		<textarea name="reminder_text" cols="80" rows="8"><?php echo $values['reminder_text']; ?></textarea>
	</fieldset>

	<div>
		<input type="submit" name="submit" value="Gem" /> eller <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">Fortryd</a>
	</div>
</form>

<?php
$page->end();
?>
