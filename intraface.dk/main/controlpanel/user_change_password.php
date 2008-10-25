<?php
require '../../include_first.php';

$translation = $kernel->getTranslation('controlpanel');

if (!empty($_POST)) {
	$user = new Intraface_User($kernel->user->get('id'));

	if ($user->updatePassword($_POST['old_password'], $_POST['new_password'], $_POST['repeat_password'])) {
		header("Location: user.php");
		exit;
	}

} else {
    $user = new Intraface_User($kernel->user->get('id'));
}

$page = new Intraface_Page($kernel);
$page->start(t('change user password'));
?>

<h1><?php e(t('change user password')); ?></h1>

<ul class="options">
	<li><a href="index.php"><?php e(t('close')); ?></a></li>
</ul>

<?php echo $user->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

<fieldset>
	<legend><?php e(t('change password')); ?></legend>
	<div class="formrow">
		<label for="old-password"><?php e(t('old password')); ?></label>
		<input type="password" name="old_password" id="old-password" value="" />
	</div>

	<div class="formrow">
		<label for="new-password"><?php e(t('new password')); ?></label>
		<input type="password" name="new_password" id="new-password" value="" />
	</div>
	<div class="formrow">
		<label for="repeat-password"><?php e(t('repeat new password')); ?></label>
		<input type="password" name="repeat_password" id="repeat-password" value="" />
	</div>
</fieldset>

<p><input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" />
<a href="user.php"><?php e(t('Cancel', 'common')); ?></a></p>

</form>

<?php
$page->end();
?>
