<?php
require('../../include_first.php');

// $modul = $kernel->module('administration');
$translation = $kernel->getTranslation('controlpanel');

if(!empty($_POST)) {

	$user = new Intraface_User($kernel->user->get('id'));

	$value = $_POST;
	$address_value = $_POST;
	$address_value['name'] = $_POST['address_name'];
    $address_value['email'] = $_POST['address_email'];

	// hvis man ændrer e-mail skal man have en e-mail som en sikkerhedsforanstaltning
	// på den gamle e-mail

	if($user->update($_POST)) {
		if($user->getAddress()->validate($address_value) && $user->getAddress()->save($address_value)) {
			header("Location: user.php");
			exit;
		}
	}

} else {
		$user = new Intraface_User($kernel->user->get('id'));
		$value = $user->get();
		$address_value = $user->getAddress()->get();
}


$page = new Page($kernel);
$page->start(safeToHtml($translation->get('edit user')));
?>

<h1><?php echo safeToHtml($translation->get('edit user')); ?></h1>

<ul class="options">
	<li><a href="index.php"><?php echo safeToHtml($translation->get('close')); ?></a></li>
</ul>

<?php echo $user->error->view(); ?>
<?php echo $user->getAddress()->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

<fieldset>
	<legend><?php echo safeToHtml($translation->get('information about user')); ?></legend>
	<div class="formrow">
		<label for="name"><?php echo safeToHtml($translation->get('e-mail', 'address')); ?></label>
		<input type="text" name="email" id="email" value="<?php echo safeToHtml($value["email"]); ?>" />
		<p style="clear:both;"><?php echo safeToHtml($translation->get('your e-mail is also your username','controlpanel')); ?></p>
	</div>
</fieldset>


<fieldset>
	<legend><?php echo safeToHtml($translation->get('address information')); ?></legend>
	<div class="formrow">
		<label for="address_name"><?php echo safeToHtml($translation->get('name', 'address')); ?></label>
		<input type="text" name="address_name" id="address_name" value="<?php if (!empty($address_value["name"])) echo safeToHtml($address_value["name"]); ?>" />
	</div>
	<div class="formrow">
		<label for="address"><?php echo safeToHtml($translation->get('address', 'address')); ?></label>
		<textarea name="address" id="address" rows="2"><?php if (!empty($address_value["address"])) echo safeToHtml($address_value["address"]); ?></textarea>
	</div>
	<div class="formrow">
		<label for="postcode"><?php echo safeToHtml($translation->get('postal code and city', 'address')); ?></label>
		<input type="text" name="postcode" id="postcode" value="<?php if (!empty($address_value["postcode"])) echo safeToHtml($address_value["postcode"]); ?>" size="4" />
		<input type="text" name="city" id="city" value="<?php if (!empty($address_value["city"])) echo safeToHtml($address_value["city"]); ?>" />
	</div>
	<div class="formrow">
		<label for="country"><?php echo safeToHtml($translation->get('country', 'address')); ?></label>
		<input type="text" name="country" id="country" value="<?php if (!empty($address_value["country"])) echo safeToHtml($address_value["country"]); ?>" />
	</div>
	<div class="formrow">
		<label for="address_email"><?php echo safeToHtml($translation->get('e-mail', 'address')); ?></label>
		<input type="text" name="address_email" id="address_email" value="<?php if (!empty($address_value["email"])) echo safeToHtml($address_value["email"]); ?>" />
	</div>
	<div class="formrow">
		<label for="website"><?php echo safeToHtml($translation->get('website', 'address')); ?></label>
		<input type="text" name="website" id="website" value="<?php if (!empty($address_value["website"])) echo safeToHtml($address_value["website"]); ?>" />
	</div>
	<div class="formrow">
		<label for="phone"><?php echo safeToHtml($translation->get('phone', 'address')); ?></label>
		<input type="text" name="phone" id="phone" value="<?php if (!empty($address_value["phone"])) echo safeToHtml($address_value["phone"]); ?>" />
	</div>
</fieldset>

<p><input type="submit" name="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" />
<a href="user.php"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a></p>

</form>

<?php
$page->end();
?>
