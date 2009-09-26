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

<h1><?php e(t('Settings')); ?></h1>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

<?php echo $error->view(); ?>

<!--
<fieldset>
	<legend>Tillad at kontakter kan logge ind</legend>

	<p>Du kan tillade kontakterne at logge ind i et system, hvor de kan rette deres egne oplysninger og se oplysninger om dem selv.</p>

	HVAD NU HVIS MAN IKKE TILLADER DET - DE SKAL HAVE LEJLIGHED FOR AT GODKENDE NYHEDSBREVE OG MEDDELELSER.

</fieldset>
-->

<fieldset class="radiobuttons">


	<legend><?php e(t('Contact login')); ?></legend>

	<?php foreach ($url_options AS $key=>$value): ?>
		<?php
		/*
		<label>
			<input type="radio" value="<?php e($value); ?>" name="contact_login_url" <?php if ($values['contact_login_url'] == $value) echo 'checked="checked"'; ?>/> <?php e($value); ?>
		</label>
		*/
		?>
	<?php endforeach; ?>

	<p><?php e(t('You can choose between the following standard links:')); ?> <?php echo implode($url_options, ', '); ?> <?php e(t('(recommended) or you can write your own link.')); ?></p>

	<label>
		<?php e(t('Link')); ?> <input type="text" name="contact_login_url" value="<?php e($values['contact_login_url']); ?>" />
	</label>

</fieldset>


<fieldset>
	<legend><?php e(t('Text on email to login')); ?></legend>
	<div class="formrow">
		<label><?php e(t('Body text')); ?></label>
		<textarea name="text" cols="80" rows="10"><?php e($values['text']); ?></textarea>
	</div>



</fieldset>

	<div>
		<input type="submit" name="submit" value="<?php e(t('Save')); ?>" /> <a href="/modules/contact/"><?php e(t('cancel')); ?></a>
	</div>
</form>

<?php
$page->end();
?>
