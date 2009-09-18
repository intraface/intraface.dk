<?php
$value = $context->getValues();
$address_value = $context->getValues();
?>

<h1><?php e(__('Edit intranet')); ?></h1>

<?php echo $context->getIntranet()->error->view(); ?>

<form action="<?php e(url()); ?>" method="post">
<input name="_method" value="<?php e($context->method); ?>" type="hidden" />
<fieldset>
	<legend>Oplysninger om intranettet</legend>
	<div class="formrow">
		<label for="name"><?php e(__('name', 'address')); ?></label>
		<input type="text" name="name" id="name" value="<?php if (!empty($value['name'])) e($value["name"]); ?>" size="50" />
	</div>
	<div class="formrow">
		<label for="name"><?php e(__('identifier', 'common')); ?></label>
		<input type="text" name="identifier" id="identifier" value="<?php if (!empty($value['identifier'])) e($value["identifier"]); ?>" size="50" />
	</div>

	<div class="formrow">
		<label for="maintained_by_user_id"><?php e(__('maintained by')); ?></label>
		<select name="maintained_by_user_id">
			<?php
			$users = $context->getKernel()->user->getList();

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
		<?php e(__('private key')); ?>:
		<?php e($context->getIntranet()->get("private_key")); ?>
	</div>
	<div>
		<input type="checkbox" name="generate_private_key" id="generate_private_key" value="yes" />
		<label for="generate_private_key"><?php e(__('create new private key')); ?>  </label>
	</div>
	<div>
		<?php e(__('public key')); ?>:
		<?php e($context->getIntranet()->get("public_key")); ?>
	</div>
	<div>
		<input type="checkbox" name="generate_public_key" id="generate_public_key" value="yes" />
		<label for="generate_public_key"><?php e(__('create new public key')); ?></label>
	</div>

</fieldset>

<input type="submit" name="submit" value="Gem" id="submit-save-keys" />

<fieldset>
	<legend><?php e(__('address information', 'address')); ?></legend>

	<div class="formrow">
		<label for="address_name"><?php e(__('name', 'address')); ?></label>
		<input type="text" name="address_name" id="address_name" value="<?php if (!empty($address_value["name"])) e($address_value["name"]); ?>" />
	</div>
	<div class="formrow">
		<label for="address"><?php e(__('address', 'address')); ?></label>
		<textarea name="address" id="address" rows="2"><?php if (!empty($address_value["address"])) e($address_value["address"]); ?></textarea>
	</div>
	<div class="formrow">
		<label for="postcode"><?php e(__('postal code and city', 'address')); ?></label>
		<div>
			<input type="text" name="postcode" id="postcode" value="<?php if (!empty($address_value["postcode"])) e($address_value["postcode"]); ?>" size="4" />
			<input type="text" name="city" id="city" value="<?php if (!empty($address_value["city"])) e($address_value["city"]); ?>" />
		</div>
	</div>
	<div class="formrow">
		<label for="country"><?php e(__('country', 'address')); ?></label>
		<input type="text" name="country" id="country" value="<?php if (!empty($address_value["country"])) e($address_value["country"]); ?>" />
	</div>
	<div class="formrow">
		<label for="cvr"><acronym title="Centrale VirksomhedsRegister">CVR</acronym>-nummer</label>
		<input type="text" name="cvr" id="cvr" value="<?php if (!empty($address_value["cvr"])) e($address_value["cvr"]); ?>" />
	</div>
	<div class="formrow">
		<label for="email"><?php e(__('e-mail', 'address')); ?></label>
		<input type="text" name="email" id="email" value="<?php if (!empty($address_value["email"])) e($address_value["email"]); ?>" />
	</div>
	<div class="formrow">
		<label for="website"><?php e(__('website', 'address')); ?></label>
		<input type="text" name="website" id="website" value="<?php if (!empty($address_value["website"])) e($address_value["website"]); ?>" />
	</div>
	<div class="formrow">
		<label for="phone"><?php e(__('phone', 'address')); ?></label>
		<input type="text" name="phone" id="phone" value="<?php if (!empty($address_value["phone"])) e($address_value["phone"]); ?>" />
	</div>
</fieldset>
<input type="hidden" name="id" id="id" value="<?php e($context->getIntranet()->get("id")); ?>" />
<input type="submit" name="submit" value="Gem" id="submit-save-address" />

</form>
