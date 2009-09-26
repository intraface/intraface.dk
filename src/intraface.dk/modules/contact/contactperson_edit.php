<?php
require('../../include_first.php');

if (empty($_REQUEST['contact_id']) OR !is_numeric($_REQUEST['contact_id'])) {
	header('Location: index.php');
	exit;
}

$kernel->module('contact');
$translation = $kernel->getTranslation('contact');

// prepare to save
if (!empty($_POST)) {
	$contact = new Contact($kernel, $_POST['contact_id']);
	$person = $contact->loadContactPerson($_POST['id']);
	if ($id = $person->save($_POST)) {
		header('Location: contact.php?id='.$contact->get('id').'&from_person_id='. $id . '#contactpersons');
		exit;
	}
	else {
		$value = $_POST;
	}
}

elseif (isset($_GET['id'])) {
	$contact = new Contact($kernel, (int)$_GET['contact_id']);
	$person = $contact->loadContactPerson($_GET['id']);
	$value = $person->get();
	$value['contact_id'] = (int)$_GET['contact_id'];
}

else {
	$contact = new Contact($kernel, (int)$_GET['contact_id']);
	$person = $contact->loadContactPerson();
	$value['contact_id'] = (int)$_GET['contact_id'];
}

$page = new Intraface_Page($kernel);
$page->start('Rediger kontaktperson');
?>


<h1><?php e(t('Edit contact')); ?></h1>

<?php if (is_object($contact->contactperson->error)) echo $contact->contactperson->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

<fieldset>
	<legend><?php e(t('Edit contact information')); ?></legend>
  <input type="hidden" name="id" value="<?php if (isset($value['id'])) e($value['id']); ?>" />
  <input type="hidden" name="contact_id" value="<?php if (isset($value['contact_id'])) e($value['contact_id']); ?>" />

	<div class="formrow">
	  <label for="company"><?php e(t('Name')); ?></label>
    <input type="text" name="name" id="name" value="<?php if (isset($value['name'])) e($value['name']); ?>" />
	</div>

	<div class="formrow">
    <label for="email"><?php e(t('Email')); ?></label>
    <input type="text" name="email" id="email" value="<?php if (isset($value['email'])) e($value['email']); ?>" />
	</div>
	<div class="formrow">
    <label for="phone"><?php e(t('Phone')); ?></label>
    <input type="text" name="phone" id="phone" value="<?php if (isset($value['phone'])) e($value['phone']); ?>" />
	</div>
	<div class="formrow">
    <label for="mobile"><?php e(t('Mobile phone')); ?></label>
    <input type="text" name="mobile" id="mobile" value="<?php if (isset($value['mobile'])) e($value['mobile']); ?>" />
	</div>

</fieldset>

	<div>
		<input type="submit" name="submit" value="<?php e(t('Save')); ?>" id="save" class="save" />
		<a href="contact.php?id=<?php if (isset($value['contact_id'])) e($value['contact_id']); ?>&from_person_id=<?php if (isset($value['id'])) e($value['id']); ?>#contactpersons"><?php e(t('Cancel')); ?></a>
	</div>
</form>

<?php
$page->end();
?>