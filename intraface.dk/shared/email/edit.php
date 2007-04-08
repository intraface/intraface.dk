<?php
require('../../include_first.php');

$kernel->useShared('email');

$redirect = Redirect::factory($kernel, 'receive');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$email = new Email($kernel, $_POST['id']);

	if ($id = $email->save($_POST)) {
		header('Location: '.$redirect->getRedirect('email.php?id='.$id));
		exit;
	}
	else {
		$value = $_POST;
	}
}
else {
	$email = new Email($kernel, $_GET['id']);
	$value = $email->get();
}

$page = new Page($kernel);
$page->start('Email');
?>

<h1>Rediger e-mail</h1>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

	<input type="hidden" name="id" value="<?php echo intval($value['id']); ?>" />
	<input type="hidden" name="contact_id" value="<?php echo intval($value['contact_id']); ?>" />
	<input type="hidden" name="type_id" value="<?php echo intval($value['type_id']); ?>" />

	<fieldset>
		<legend>Overskrift</legend>
		<input size="80" type="text" name="subject" value="<?php echo htmlentities($value['subject']); ?>" />
	</fieldset>
	<fieldset>
		<legend>Tekst</legend>
		<textarea cols="80" rows="9" class="resizable" name="body"><?php echo wordwrap(htmlentities($value['body']), 75); ?></textarea>
	</fieldset>

	<p>
		<input type="submit" class="save" name="submit" value="Gem" />
		eller <a href="<?php echo $redirect->getRedirect('email.php?id='.intval($value['id'])); ?>">Fortryd</a>
	</p>
</form>

<?php
$page->end();
?>
