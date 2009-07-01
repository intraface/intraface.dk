<?php
require '../../include_first.php';

$modul = $kernel->module("intranetmaintenance");
$translation = $kernel->getTranslation('intranetmaintenance');

if (isset($_POST["submit"])) {

	$intranet = new IntranetMaintenance(intval($_POST["id"]));

	$value = $_POST;
	$address_value = $_POST;
	$address_value["name"] = $_POST["address_name"];

	if ($intranet->save($_POST) && $intranet->setMaintainedByUser($_POST['maintained_by_user_id'], $kernel->intranet->get('id'))) {
		if ($intranet->address->save($address_value)) {
			header("Location: intranet.php?id=".$intranet->get('id'));
			exit;
		}
	}

} else {
	if (isset($_GET["id"])) {
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

<h1><?php e($translation->get('edit intranet')); ?></h1>

<?php echo $intranet->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
<fieldset>
	<legend>Oplysninger om intranettet</legend>
	<div class="formrow">
		<label for="name"><?php e($translation->get('name', 'address')); ?></label>
		<input type="text" name="name" id="name" value="<?php if (!empty($value['name'])) e($value["name"]); ?>" size="50" />
	</div>
	<div class="formrow">
		<label for="name"><?php e($translation->get('identifier', 'common')); ?></label>
		<input type="text" name="identifier" id="identifier" value="<?php if (!empty($value['identifier'])) e($value["identifier"]); ?>" size="50" />
	</div>

	<div class="formrow">
		<label for="maintained_by_user_id"><?php e($translation->get('maintained by')); ?></label>
		<select name="maintained_by_user_id">
			<?php
			$users = $kernel->user->getList();

			for ($i = 0; $i < count($users); $i++) {
				?>
				<option value="<?php e($users[$i]["id"]); ?>" <?php if (!empty($value["maintained_by_user_id"]) AND $value["maintained_by_user_id"] == $users[$i]["id"]) print("selected=\"selected\""); ?> ><?php if (isset($users[$i]['name'])) e($users[$i]["name"]); ?> (<?php if (isset($users[$i]['email'])) e($users[$i]["email"]); ?>)</option>
				<?php
			}
			?>
		</select>
	</div>
</fieldset>

<fieldset>
	<legend>Intranetnøgle</legend>
	<div>
		<?php e($translation->get('private key')); ?>:
		<?php e($intranet->get("private_key")); ?>
	</div>
	<div>
		<input type="checkbox" name="generate_private_key" id="generate_private_key" value="yes" />
		<label for="generate_private_key"><?php e($translation->get('create new private key')); ?>  </label>
	</div>
	<div>
		<?php e($translation->get('public key')); ?>:
		<?php e($intranet->get("public_key")); ?>
	</div>
	<div>
		<input type="checkbox" name="generate_public_key" id="generate_public_key" value="yes" />
		<label for="generate_public_key"><?php e($translation->get('create new public key')); ?></label>
	</div>

</fieldset>

<input type="submit" name="submit" value="Gem" id="submit-save-keys" />

<fieldset>
	<legend><?php e($translation->get('address information', 'address')); ?></legend>

	<div class="formrow">
		<label for="address_name"><?php e($translation->get('name', 'address')); ?></label>
		<input type="text" name="address_name" id="address_name" value="<?php if (!empty($address_value["name"])) e($address_value["name"]); ?>" />
	</div>
	<div class="formrow">
		<label for="address"><?php e($translation->get('address', 'address')); ?></label>
		<textarea name="address" id="address" rows="2"><?php if (!empty($address_value["address"])) e($address_value["address"]); ?></textarea>
	</div>
	<div class="formrow">
		<label for="postcode"><?php e($translation->get('postal code and city', 'address')); ?></label>
		<div>
			<input type="text" name="postcode" id="postcode" value="<?php if (!empty($address_value["postcode"])) e($address_value["postcode"]); ?>" size="4" />
			<input type="text" name="city" id="city" value="<?php if (!empty($address_value["city"])) e($address_value["city"]); ?>" />
		</div>
	</div>
	<div class="formrow">
		<label for="country"><?php e($translation->get('country', 'address')); ?></label>
		<input type="text" name="country" id="country" value="<?php if (!empty($address_value["country"])) e($address_value["country"]); ?>" />
	</div>
	<div class="formrow">
		<label for="cvr"><acronym title="Centrale VirksomhedsRegister">CVR</acronym>-nummer</label>
		<input type="text" name="cvr" id="cvr" value="<?php if (!empty($address_value["cvr"])) e($address_value["cvr"]); ?>" />
	</div>
	<div class="formrow">
		<label for="email"><?php e($translation->get('e-mail', 'address')); ?></label>
		<input type="text" name="email" id="email" value="<?php if (!empty($address_value["email"])) e($address_value["email"]); ?>" />
	</div>
	<div class="formrow">
		<label for="website"><?php e($translation->get('website', 'address')); ?></label>
		<input type="text" name="website" id="website" value="<?php if (!empty($address_value["website"])) e($address_value["website"]); ?>" />
	</div>
	<div class="formrow">
		<label for="phone"><?php e($translation->get('phone', 'address')); ?></label>
		<input type="text" name="phone" id="phone" value="<?php if (!empty($address_value["phone"])) e($address_value["phone"]); ?>" />
	</div>
</fieldset>
<input type="hidden" name="id" id="id" value="<?php e($intranet->get("id")); ?>" />
<input type="submit" name="submit" value="Gem" id="submit-save-address" />

</form>



<?php
$page->end();
?>
