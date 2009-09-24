<?php
$value = $context->getValues();
$address_value = $context->getValues();
?>

<h1><?php e(__('Keys')); ?></h1>

<?php echo $context->getIntranet()->error->view(); ?>

<form action="<?php e(url()); ?>" method="post">

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

</form>
