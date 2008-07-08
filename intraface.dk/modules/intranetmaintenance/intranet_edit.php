<?php
require '../../include_first.php';

$modul = $kernel->module("intranetmaintenance");
$translation = $kernel->getTranslation('intranetmaintenance');

if (isset($_POST["submit"])) {

	$intranet = new IntranetMaintenance(intval($_POST["id"]));

	$value = $_POST;
	$address_value = $_POST;
	$address_value["name"] = $_POST["address_name"];

	if($intranet->save($_POST) && $intranet->setMaintainedByUser($_POST['maintained_by_user_id'], $kernel->intranet->get('id'))) {
		if($intranet->address->save($address_value)) {
			header("Location: intranet.php?id=".$intranet->get('id'));
			exit;
		}
	}

} else {
	if(isset($_GET["id"])) {
		$intranet = new IntranetMaintenance((int)$_GET["id"]);
		$value = $intranet->get();
		$address_value = $intranet->address->get();
	} else {
		$intranet = new IntranetMaintenance();
		$value = array();
		$address_value = array();
	}


}
$page = new Intraface_Page($kernel);
$page->start($translation->get('edit intranet'));
?>

<h1><?php print $translation->get('edit intranet'); ?></h1>

<?php echo $intranet->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
<fieldset>
	<legend>Oplysninger om intranettet</legend>
	<div class="formrow">
		<label for="name"><?php echo $translation->get('name', 'address'); ?></label>
		<input type="text" name="name" id="name" value="<?php if (!empty($value['name'])) echo safeToForm($value["name"]); ?>" size="50" />
	</div>
	<div class="formrow">
		<label for="name"><?php echo $translation->get('identifier', 'common'); ?></label>
		<input type="text" name="identifier" id="identifier" value="<?php if (!empty($value['identifier'])) echo safeToForm($value["identifier"]); ?>" size="50" />
	</div>

	<div class="formrow">
		<label for="maintained_by_user_id"><?php echo $translation->get('maintained by'); ?></label>
		<select name="maintained_by_user_id">
			<?php
			$users = $kernel->user->getList();

			for($i = 0; $i < count($users); $i++) {
				?>
				<option value="<?php print safeToHtml($users[$i]["id"]); ?>" <?php if(!empty($value["maintained_by_user_id"]) AND $value["maintained_by_user_id"] == $users[$i]["id"]) print("selected=\"selected\""); ?> ><?php if(isset($users[$i]['name'])) print safeToHtml($users[$i]["name"]); ?> (<?php if(isset($users[$i]['email'])) print safeToHtml($users[$i]["email"]); ?>)</option>
				<?php
			}
			?>
		</select>
	</div>
</fieldset>

<fieldset>
	<legend>Intranetnøgle</legend>
	<div>
		<?php echo $translation->get('private key'); ?>:
		<?php print safeToForm($intranet->get("private_key")); ?>
	</div>
	<div>
		<input type="checkbox" name="generate_private_key" id="generate_private_key" value="yes" />
		<label for="generate_private_key"><?php echo $translation->get('create new private key'); ?>  </label>
	</div>
	<div>
		<?php echo $translation->get('public key'); ?>:
		<?php print safeToForm($intranet->get("public_key")); ?>
	</div>
	<div>
		<input type="checkbox" name="generate_public_key" id="generate_public_key" value="yes" />
		<label for="generate_public_key"><?php echo $translation->get('create new public key'); ?>  </label>
	</div>

</fieldset>

<input type="submit" name="submit" value="Gem" id="submit-save-keys" />

<fieldset>
	<legend><?php echo $translation->get('address information', 'address'); ?></legend>

	<div class="formrow">
		<label for="address_name"><?php echo $translation->get('name', 'address'); ?></label>
		<input type="text" name="address_name" id="address_name" value="<?php if (!empty($address_value["name"])) print safeToHtml($address_value["name"]); ?>" />
	</div>
	<div class="formrow">
		<label for="address"><?php echo $translation->get('address', 'address'); ?></label>
		<textarea name="address" id="address" rows="2"><?php if (!empty($address_value["address"])) print htmlentities($address_value["address"]); ?></textarea>
	</div>
	<div class="formrow">
		<label for="postcode"><?php echo $translation->get('postal code and city', 'address'); ?></label>
		<div>
			<input type="text" name="postcode" id="postcode" value="<?php if (!empty($address_value["postcode"])) print htmlentities($address_value["postcode"]); ?>" size="4" />
			<input type="text" name="city" id="city" value="<?php if(!empty($address_value["city"])) print htmlentities($address_value["city"]); ?>" />
		</div>
	</div>
	<div class="formrow">
		<label for="country"><?php echo $translation->get('country', 'address'); ?></label>
		<input type="text" name="country" id="country" value="<?php if (!empty($address_value["country"])) print htmlentities($address_value["country"]); ?>" />
	</div>
	<div class="formrow">
		<label for="cvr"><acronym title="Centrale VirksomhedsRegister">CVR</acronym>-nummer</label>
		<input type="text" name="cvr" id="cvr" value="<?php if (!empty($address_value["cvr"])) print htmlentities($address_value["cvr"]); ?>" />
	</div>
	<div class="formrow">
		<label for="email"><?php echo $translation->get('e-mail', 'address'); ?></label>
		<input type="text" name="email" id="email" value="<?php if (!empty($address_value["email"])) print htmlentities($address_value["email"]); ?>" />
	</div>
	<div class="formrow">
		<label for="website"><?php echo $translation->get('website', 'address'); ?></label>
		<input type="text" name="website" id="website" value="<?php if(!empty($address_value["website"])) print htmlentities($address_value["website"]); ?>" />
	</div>
	<div class="formrow">
		<label for="phone"><?php echo $translation->get('phone', 'address'); ?></label>
		<input type="text" name="phone" id="phone" value="<?php if (!empty($address_value["phone"])) print htmlentities($address_value["phone"]); ?>" />
	</div>
</fieldset>
<input type="hidden" name="id" id="id" value="<?php print($intranet->get("id")); ?>" />
<input type="submit" name="submit" value="Gem" id="submit-save-address" />

</form>



<?php
$page->end();
?>
