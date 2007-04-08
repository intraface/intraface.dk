<?php
require('../../include_first.php');

if (empty($_REQUEST['contact_id']) OR !is_numeric($_REQUEST['contact_id'])) {
	trigger_error('Der er ikke angivet nogen kontaktid', E_USER_ERROR);
}

$module = $kernel->module("contact");
$translation = $kernel->getTranslation('contact');

// prepare to save
if (count($_POST) > 0) {

	$contact = new Contact($kernel, $_POST['contact_id']);
  $contact->loadMessage(@(int)$_POST['id']);

	if ($id = $contact->message->update($_POST)) {
		header("Location: contact.php?id=".$contact->get("id")."&from_msg_id=".$id."#messages");
		exit;
	}
	else {
		// sikkert ikke den allersmarteste måde at gøre det på :)
		$values = $_POST;
		$contact = new Contact($kernel, $_POST['contact_id']);
    $contact->loadMessage();

	}
}
elseif (isset($_GET['id']) AND is_numeric($_GET['id'])) {
	$contact = new Contact($kernel, $_GET['contact_id']);
  $contact->loadMessage($_GET['id']);
  $values = $contact->message->get();
}
else {
	$contact = new Contact($kernel, $_GET['contact_id']);
  $contact->loadMessage();
}

$page = new Page($kernel);
$page->start("Rediger besked");
?>


<h1>Rediger besked</h1>

<?php // echo $contact->infoHeader(); ?>

<?php $contact->message->error->view(); ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<fieldset>
	<legend>Besked</legend>
	<div class="formrow">
		<label for="message">Besked</label>
    <textarea name="message" id="message" rows="5" cols="40"><?php echo $values['message']; ?></textarea>
	</div>
	<div style="clear:both;">
		<input type="checkbox" value="1" id="important" name="important" <?php if ($values['important'] == 1) echo ' checked="checked"'; ?>/>
		<label for="important">Marker som vigtig</label>
		<input type="hidden" value="<?php echo $contact->get('id'); ?>" name="contact_id" />
		<input type="hidden" value="<?php echo $values['id']; ?>" name="id" />
	</div>
</fieldset>
	<div>
		<input type="submit" name="submit" value="Gem" id="save" class="save" />
    <a href="contact.php?id=<?php echo $contact->get('id'); ?>">Fortryd</a>
	</div>
</form>

<?php $page->end(); ?>
