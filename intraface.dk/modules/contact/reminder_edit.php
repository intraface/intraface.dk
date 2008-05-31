<?php
require('../../include_first.php');

$contact_module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');
$contact_module->includeFile('ContactReminder.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// for a new contact we want to check if similar contacts alreade exists
	if (empty($_POST['id'])) {
		$contact = new Contact($kernel, (int)$_POST['contact_id']);
		if ($contact->get('id') == 0) {
			trigger_error("Invalid contact id", E_USER_ERROR);
		}
		$reminder = new ContactReminder($contact);

	}
	else {
		$reminder = ContactReminder::factory($kernel, (int)$_POST['id']);
		$contact = $reminder->contact;
	}

	if ($id = $reminder->update($_POST)) {
		header('Location: contact.php?id='.$contact->get('id'));
		exit;
	}

	$value = $_POST;	
}
elseif (isset($_GET['id'])) {
	$reminder = ContactReminder::factory($kernel, (int)$_GET['id']);
	if($reminder->get('id') == 0) {
		trigger_error('Invalid reminder id', E_USER_ERROR);
	}
	$contact = $reminder->contact;
	$value = $reminder->get();
	$value['reminder_date'] = $reminder->get('dk_reminder_date');
}
elseif(isset($_GET['contact_id'])) {
	$contact = new Contact($kernel, (int)$_GET['contact_id']);
	if($contact->get('id') == 0) {
		trigger_error("Invalod contact_id", E_USER_ERROR);
	}
	$reminder = new ContactReminder($contact);
	$value['reminder_date'] = date('d-m-Y');
}
else {
	trigger_error("An id or contact id is needed", E_USER_ERROR);
}


$page = new Intraface_Page($kernel);
$page->start($translation->get('Edit reminder'));
?>


<h1><?php echo safeToHtml($translation->get('Edit reminder')); ?></h1>

<?php echo $reminder->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

<fieldset>
	<legend><?php echo safeToHtml($translation->get('Reminder date')); ?></legend>
	<div class="formrow">
		<label for="reminder_date"><?php echo safeToHtml($translation->get('Reminder date')); ?></label>
		<input type="text" name="reminder_date" id="reminder_date" value="<?php if (!empty($value['reminder_date'])) echo safeToForm($value['reminder_date']); ?>" />
	</div>
</fieldset>

<fieldset>
	<legend><?php echo safeToHtml($translation->get('Reminder information')); ?></legend>
	
	<div class="formrow">
		<label for="subject"><?php echo safeToHtml($translation->get('Subject')); ?></label>
		<input type="text" name="subject" id="subject" value="<?php if (!empty($value['subject'])) echo safeToForm($value['subject']); ?>" />
	</div>
	
	<div class="formrow">
		<label for="description"><?php echo safeToHtml($translation->get('Description')); ?></label>
		<textarea name="description" id="description" style="width: 400px; height: 100px;"><?php if (!empty($value['description'])) echo safeToForm($value['description']); ?></textarea>
	</div>
</fieldset>

<div>
	<input type="hidden" name="id" value="<?php if (!empty($value['id']))  echo intval($value['id']); ?>" />
	<input type="hidden" name="contact_id" value="<?php echo intval($contact->get('id')); ?>" />
	
	
	<input type="submit" name="submit" value="<?php echo safeToHtml($translation->get('Save', 'common')); ?>" id="save" class="save" />
		<?php echo safeToHtml($translation->get('or', 'common')); ?>
	<a href="contact.php?id=<?php echo intval($contact->get('id')); ?>" title="<?php echo safeToHtml($translation->get('Cancel', 'common')); ?>"><?php echo safeToHtml($translation->get('cancel', 'common')); ?></a>
	</div>
</form>

<?php
$page->end();
?>
