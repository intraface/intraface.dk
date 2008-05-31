<?php
require('../../include_first.php');

$contact_module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');


$url_options = $contact_module->getSetting('contact_login_url');

$error = new Intraface_Error();

if (!empty($_POST)) {

	// validering
	$validator = new Intraface_Validator($error);
	$validator->isUrl($_POST['contact_login_url'], 'Ikke gyldigt url');
	$validator->isString($_POST['text'], 'Ikke gyldig tekst', '');

	if (!$error->isError()) {
		$kernel->setting->set('intranet', 'contact.login_url', $_POST['contact_login_url']);
		$kernel->setting->set('intranet', 'contact.login_email_text', $_POST['text']);
		header('Location: setting.php');
		exit;
	}
	else {
		$values = $_POST;
	}

}
else {
	// find settings frem
	$values['contact_login_url'] = $kernel->setting->get('intranet', 'contact.login_url');
	$values['text'] = $kernel->setting->get('intranet', 'contact.login_email_text');
}

$page = new Intraface_Page($kernel);
$page->start('Indstillinger');
?>

<h1>Indstillinger</h1>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

<?php echo $error->view(); ?>

<!--
<fieldset>
	<legend>Tillad at kontakter kan logge ind</legend>

	<p>Du kan tillade kontakterne at logge ind i et system, hvor de kan rette deres egne oplysninger og se oplysninger om dem selv.</p>

	HVAD NU HVIS MAN IKKE TILLADER DET - DE SKAL HAVE LEJLIGHED FOR AT GODKENDE NYHEDSBREVE OG MEDDELELSER.

</fieldset>
-->

<fieldset class="radiobuttons">


	<legend>Kontaktlogin</legend>

	<?php foreach ($url_options AS $key=>$value): ?>
		<?php
		/*
		<label>
			<input type="radio" value="<?php echo $value; ?>" name="contact_login_url" <?php if ($values['contact_login_url'] == $value) echo 'checked="checked"'; ?>/> <?php echo $value; ?>
		</label>
		*/
		?>
	<?php endforeach; ?>

	<p>Du kan vælge mellem følgende standardlinks <?php echo implode($url_options, ', '); ?> (anbefales), eller du kan skrive dit eget link.</p>

	<label>
		Link <input type="text" name="contact_login_url" value="<?php echo safeToForm($values['contact_login_url']); ?>" />
	</label>

</fieldset>


<fieldset>
	<legend>Tekst på e-mail til login</legend>
	<div class="formrow">
		<label>Tekst</label>
		<textarea name="text" cols="80" rows="10"><?php echo safeToForm($values['text']); ?></textarea>
	</div>



</fieldset>

	<div>
		<input type="submit" name="submit" value="Gem" /> eller <a href="/modules/contact/">Fortryd</a>
	</div>
</form>

<?php
$page->end();
?>
