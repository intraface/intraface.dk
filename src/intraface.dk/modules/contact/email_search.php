<?php
require('../../include_first.php');

$kernel->module('contact');
$kernel->useShared('email');
$translation = $kernel->getTranslation('contact');

$_GET['use_stored'] = true;

$contact = new Contact($kernel);
$keyword = $contact->getKeywords();
$keywords = $keyword->getAllKeywords();
$contact->getDBQuery()->defineCharacter('character', 'address.name');
$contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
$contacts = $contact->getList("use_address");

if (!empty($_POST)) {

	$validator = new Intraface_Validator($contact->error);
	$validator->isString($_POST['subject'], 'error in subject');
	$validator->isString($_POST['text'], 'error in text');

	if (!$contact->error->isError()) {
		// valideret subject og body
		$j = 0;

		for ($i = 0, $max = count($contacts); $i < $max; $i++) {
			if (!$validator->isEmail($contacts[$i]['email'], "")) {
				// Hvis de ikke har en mail, kører vi videre med næste.
				continue;
			}

			$contact = new Contact($kernel, $contacts[$i]['id']);

			$email = new Email($kernel);
			$input = array(
				'subject' => $_POST['subject'],
				'body' => $_POST['text'] . "\n\nLogin: " . $contact->get('login_url'),
				'from_email' => $kernel->user->get('email'),
				'from_name' => $kernel->user->get('name'),
				'contact_id' => $contact->get('id'),
				'type_id' => 11, // email til search
				'belong_to' => 0 // der er ikke nogen specifik id at sætte
			);

			$email->save($input);
			// E-mailen sættes i kø - hvis vi sender den med det samme tager det
			// alt for lang tid.
			$email->send(Intraface_Mail::factory(), 'queue');
			$j++;
		}
		$msg = 'Emailen blev i alt sendt til ' . $j . ' kontakter. <a href="">Tilbage til kontakter</a>.';
	} else {
		$value = $_POST;
	}
}

$page = new Intraface_Page($kernel);
$page->start('Rediger e-mail');

?>

<h1><?php e(t('Send email')); ?></h1>

<?php if (!empty($msg)): ?>

<p><?php echo $msg; ?></p>

<?php else: ?>

	<?php echo $contact->error->view(); ?>

<p class="message">Du er ved at sende en e-mail til <?php e(count($contacts)); ?> kontakter. Vi sender naturligvis kun til de kontakter, der har en e-mail-adresse.</p>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
	<fieldset>

	<div class="formrow">
		<label for="title"><?php e(t('Subject')); ?></label>
		<input type="text" name="subject" size="60" value="<?php if (!empty($value['subject'])) e($value['subject']); ?>" />
	</div>
	<div class="formrow">
		<label for=""><?php e(t('Body text')); ?></label>
		<textarea name="text" cols="90" rows="20"><?php if (!empty($value['subject'])) e($value['text']); ?></textarea>
	</div>
	<div>
		<input type="submit" name="submit" value="<?php e(t('Send')); ?>" class="save" /> <a href="index.php?use_stored=true"><?php e(t('Cancel')); ?></a>
	</div>
	</fieldset>
</form>
<?php endif; ?>

<?php
$page->end();
?>