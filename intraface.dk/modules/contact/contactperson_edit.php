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

$page = new Page($kernel);
$page->start('Rediger kontaktperson');
?>


<h1>Rediger kontaktperson</h1>

<?php if (is_object($contact->contactperson->error)) echo $contact->contactperson->error->view(); ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<fieldset>
	<legend>Kontaktoplysninger</legend>
  <input type="hidden" name="id" value="<?php if(isset($value['id'])) echo $value['id']; ?>" />
  <input type="hidden" name="contact_id" value="<?php if(isset($value['contact_id'])) echo $value['contact_id']; ?>" />

	<div class="formrow">
	  <label for="company">Navn</label>
    <input type="text" name="name" id="name" value="<?php if(isset($value['name'])) echo $value['name']; ?>" />
	</div>

	<div class="formrow">
    <label for="email">E-mail</label>
    <input type="text" name="email" id="email" value="<?php if(isset($value['email'])) echo $value['email']; ?>" />
	</div>
	<div class="formrow">
    <label for="phone">Telefon</label>
    <input type="text" name="phone" id="phone" value="<?php if(isset($value['phone'])) echo $value['phone']; ?>" />
	</div>
	<div class="formrow">
    <label for="mobile">Mobil</label>
    <input type="text" name="mobile" id="mobile" value="<?php if(isset($value['mobile'])) echo $value['mobile']; ?>" />
	</div>

</fieldset>

	<div>
		<input type="submit" name="submit" value="Gem" id="save" class="save" />
		<a href="contact.php?id=<?php if(isset($value['contact_id'])) echo $value['contact_id'] ?>&from_person_id=<?php if(isset($value['id'])) echo $value['id']; ?>#contactpersons">Fortryd</a>
	</div>
</form>

<?php
$page->end();
?>
