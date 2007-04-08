<?php
require('../../include_first.php');

// $modul = $kernel->module('administration');
$translation = $kernel->getTranslation('controlpanel');

if(!empty($_POST)) {

	$user = new User($kernel->user->get('id'));

	if($user->updatePassword($_POST['old_password'], $_POST['new_password'], $_POST['repeat_password'])) {
		header("Location: user.php");
		exit;
	}

}
else {

		$user = new User($kernel->user->get('id'));
}


$page = new Page($kernel);
$page->start(safeToHtml($translation->get('change user password')));
?>

<h1><?php echo safeToHtml($translation->get('change user password')); ?></h1>

<ul class="options">
	<li><a href="index.php"><?php echo safeToHtml($translation->get('close')); ?></a></li>
</ul>

<?php  $user->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

<fieldset>
	<legend><?php echo safeToHtml($translation->get('change password')); ?></legend>
	<div class="formrow">
		<label for="old-password"><?php echo safeToHtml($translation->get('old password')); ?></label>
		<input type="password" name="old_password" id="old-password" value="" />
	</div>

	<div class="formrow">
		<label for="new-password"><?php echo safeToHtml($translation->get('new password')); ?></label>
		<input type="password" name="new_password" id="new-password" value="" />
	</div>
	<div class="formrow">
		<label for="repeat-password"><?php echo safeToHtml($translation->get('repeat new password')); ?></label>
		<input type="password" name="repeat_password" id="repeat-password" value="" />
	</div>
</fieldset>

<p><input type="submit" name="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" />
<a href="user.php"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a></p>

</form>

<?php
$page->end();
?>
