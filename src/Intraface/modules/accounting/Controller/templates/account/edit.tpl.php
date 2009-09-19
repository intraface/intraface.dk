<h1>Rediger konto</h1>

<form id="account" action="<?php e(url()); ?>" method="post">
	<input type="hidden" name="id" value="<?php if (!empty($values['id'])) e($values['id']); ?>" />
	<?php echo $context->getAccount($context->name())->error->view(); ?>

	<fieldset>
		<legend>Kontoplysninger</legend>
		<div class="formrow">
			<label for="account_number">Kontonummer</label>
			<input type="text" name="number" id="account_number" value="<?php if (!empty($values['number'])) e($values['number']); ?>" />
		</div>
		<div class="formrow">
			<label for="account_name">Kontonavn</label>
			<input type="text" name="name" id="account_name" value="<?php if (!empty($values['name'])) e($values['name']); ?>" />
		</div>
		<div class="formrow">
			<label for="account_type">Type</label>
			<select name="type_key" id="account_type">
				<option value="">Vælg</option>
				<?php foreach ($account->types as $type_key=>$type) { ?>
					<option value="<?php e($type_key); ?>"<?php if (!empty($values['type_key']) AND $type_key == $values['type_key']) { echo ' selected="selected"'; } ?>><?php e($translation->get($type)); ?></option>
				<?php } ?>
			</select>
		</div>
	</fieldset>
	<fieldset id="use_fieldset">
		<legend>Kontoen er beregnet til</legend>
		<p>Denne konto bruges i forbindelse med indtastningen i kassekladden til at vise de relevante konti. Den har ikke nogen direkte indvirkning på selve regnskabet.</p>
		<div class="formrow">
			<label for="account_usage">Brug</label>
			<select name="use_key" id="account_usage">
				<?php foreach ($account->use as $use_key=>$use) { ?>
					<option value="<?php e($use_key); ?>"<?php if (!empty($values['use_key']) AND $use_key == $values['use_key']) { echo ' selected="selected"'; } ?>><?php e($translation->get($use)); ?></option>
				<?php } ?>

			</select>
		</div>
	</fieldset>
	<?php if ($year->get('vat') > 0): ?>
	<fieldset id="vat_fieldset">
	<legend>Momsindstilling</legend>
		<div class="formrow">
			<label for="vat_id">Moms</label>
			<select name="vat_key" id="vat_id">
				<?php foreach ($account->vat as $vat_key=>$vat) { ?>
					<option value="<?php e($vat_key); ?>"<?php if (!empty($values['vat_key']) AND $vat_key == $values['vat_key']) { echo ' selected="selected"'; } ?>><?php e($translation->get($vat)); ?></option>
				<?php } ?>
			</select>
		</div>
	</fieldset>
	<?php endif; ?>

	<fieldset id="sum_fieldset">
	<legend>Summen på kontoen udregnes på følgende konti</legend>
		<div>
			<label for="sum_from">Fra kontonummer</label>
			<input type="text" name="sum_from" id="sum_from" value="<?php if (!empty($values['sum_from'])) e($values['sum_from']); ?>" />
			<label for="sum_to">Til kontonummer</label>
			<input type="text" name="sum_to" id="sum_to" value="<?php if (!empty($values['sum_to'])) e($values['sum_to']); ?>" />
		</div>
	</fieldset>
	<div>
		<input type="submit" value="Gem" />
		<?php if (!empty($values['id'])): ?>
		<a href="account.php?id=<?php e($values['id']); ?>">Fortryd</a>
		<?php else: ?>
		<a href="accounts.php">Fortryd</a>
		<?php endif; ?>
	</div>
</form>