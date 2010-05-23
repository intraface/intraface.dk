<?php
$value = $context->getValues();
?>
<h1><?php e(t($context->getAction().' '.$context->getType())); ?></h1>

<?php if ($context->getKernel()->intranet->address->get('id') == 0): ?>
	<p><?php e(t('You have not filled in an address for your intranet. Please do that in order to create a debtor.')); ?>.
	<?php if ($context->getKernel()->user->hasModuleAccess('administration')): ?>
		<?php
		$module_administration = $context->getKernel()->useModule('administration');
		?>
		<a href="<?php e($module_administration->getPath().'intranet_edit.php'); ?>"><?php e(t('Fill out address')); ?></a>.
	<?php else: ?>
		<?php e(t('You do not have access to fill out an adress. Please ask an administrator to do that.')); ?>
	<?php endif; ?>
	</p>
<?php else: ?>


<?php echo $context->getDebtor()->error->view(); ?>

<form action="<?php e(url(null, array($context->subview(), 'contact_id' => $context->getContact()->get('id')))); ?>" method="post">

<fieldset>
	<legend><?php e(t($context->getDebtor()->get('type').' data')); ?></legend>

	<div class="formrow">
		<label for="number"><?php e(t($context->getDebtor()->get('type').' number')); ?></label>
    <input type="text" name="number" id="number" value="<?php e($value["number"]); ?>" />
	</div>
	<div class="formrow">
		<label for="description"><?php e(t('Description', 'debtor')); ?></label>
		<input class="input" id="description" name="description" value="<?php if (isset($value["description"])) e($value["description"]); ?>" size="60" />
	</div>
	<div class="formrow">
		<label for="this_date"><?php e(t('Date', 'debtor')); ?></label>
		<input class="input" name="this_date" id="this_date" value="<?php if (isset($value["dk_this_date"])) e($value["dk_this_date"]); ?>" size="10" />
	</div>
	<div class="formrow">
		<label for="due_date"><?php e(t($context->getDebtor()->get('type').' due date')); ?></label>
		<input class="input" name="due_date" id="due_date" value="<?php if (isset($value["dk_due_date"])) e($value["dk_due_date"]); ?>" size="10" />
	</div>
	<?php if ($context->getDebtor()->get("type") == "invoice") { ?>
		<div class="formrow">
			<label for="round_off"><?php e(t('Round off', 'debtor')); ?></label>
			<input class="input" type="checkbox" name="round_off" id="round_off" value="1" size="10" <?php if (isset($value["round_off"]) && $value["round_off"] == 1) print('checked="checked"'); ?> />
		</div>
	<?php } ?>

    <?php if ($context->getKernel()->intranet->hasModuleAccess('currency')): ?>
        <?php $context->getKernel()->useModule('currency', true); /* true: ignore user access */ ?>
        <div class="formrow">
            <label for="currency_id"><?php e(t('Currency')); ?></label>
            <select name="currency_id" id="currency_id">
                <option value="0">DKK (Standard)</option>
                <?php
                $gateway = new Intraface_modules_currency_Currency_Gateway(Doctrine_Manager::connection(DB_DSN));
                foreach ($gateway->findAllWithExchangeRate() AS $currency) {
                    ?>
                    <option value="<?php e($currency->getId()); ?>" <?php if(false !== ($debtor_currency = $context->getDebtor()->getCurrency()) && $debtor_currency->getId() == $currency->getId()) echo 'selected="selected"'; ?> ><?php e($currency->getType()->getIsoCode().' '.$currency->getType()->getDescription()); ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
    <?php endif; ?>

	<div class="formrow">
		<label for="message"><?php e(t('Message', 'debtor')); ?></label>
		<textarea id="message" name="message" cols="80" rows="5"><?php if (isset($value["message"])) e($value["message"]); ?></textarea>
	</div>

</fieldset>

<fieldset>
	<legend><?php e(t('Customer information', 'debtor')); ?></legend>
	<div class="formrow">
	  <label for="contact_id"><?php e(t('Customer', 'debtor')); ?></label>
    <span id="contact_id"><?php e($context->getContact()->address->get("name")); ?></span>
	</div>
	<?php
	if ($context->getContact()->get("type") == "corporation") {
		?>
		<div class="formrow">
			<label for="contact_person_id"><?php e(t('Attention', 'debtor')); ?></label>
			<select name="contact_person_id" id="contact_person_id">
				<option value="0"></option>
				<?php
				$persons = $context->getContact()->contactperson->getList();

				for ($i = 0, $max = count($persons); $i < $max; $i++) {
					?>
					<option value="<?php e($persons[$i]["id"]); ?>" <?php if (!empty($value["contact_person_id"]) AND $value["contact_person_id"] == $persons[$i]["id"]) print('selected="selected"'); ?> ><?php e($persons[$i]["name"]); ?></option>
					<?php
				}
				?>
				<option value="-1"><?php e(t('Create new', 'debtor') . ' >>'); ?></option>
			</select>
		</div>
		<fieldset id="contactperson">
			<legend><?php e(t('New contact person', 'debtor')); ?></legend>
			<div class="formrow">
				<label for="contactperson-name"><?php e(t('Name', 'debtor')); ?></label>
				<input id="contactperson-name" type="text" name="contact_person_name" value="" />
			</div>
			<div class="formrow">
				<label for="contactperson-email"><?php e(t('Email', 'debtor')); ?></label>
				<input id="contactperson-email" type="text" name="contact_person_email" value="" />
			</div>

		</fieldset>
		<?php
	}
	?>

</fieldset>

<?php if ($context->getDebtor()->get("type") == "invoice" || $context->getDebtor()->get("type") == "order") { ?>
	<fieldset class="radiobuttons">
		<legend><?php e(t('Payment information')); ?></legend>
		<p><?php e(t('Which payment method do you want to show on the '.$context->getDebtor()->get("type"))); ?></p>
		<div>
			<label<?php if (isset($value['payment_method']) && $value['payment_method'] == 0) print(" class=\"selected\""); ?>><input class="input" id="none" type="radio" name="payment_method" value="0" <?php if (isset($value['payment_method']) && $value['payment_method'] == 0) print("checked=\"CHECKED\""); ?> />
			<?php e(t('None')); ?></label>
		</div>
    <?php if ($context->getKernel()->setting->get('intranet', 'bank_account_number')) { ?>
		<div>
			<label<?php if (isset($value['payment_method']) AND $value['payment_method'] == 1) print(' class="selected"'); ?>><input class="input" id="account" type="radio" name="payment_method" value="1" <?php if (isset($value['payment_method']) AND $value['payment_method'] == 1) print("checked=\"CHECKED\""); ?> />
			<?php e(t('Wire transfer')); ?></label>
		</div>
    <?php } else { ?>
        <p><?php e(t('In order to receive money through wire transfer, you have to put in your account number')); ?>. <a href="<?php e(url('../../../settings')); ?>"><?php e(t('Put in bank account number')); ?></a>.</p>
    <?php } ?>
    <?php if ($context->getKernel()->setting->get('intranet', 'giro_account_number')) { ?>
		<div>
			<label for="giro01"<?php if (isset($value['payment_method']) AND $value['payment_method'] == 2) print ' class="selected"'; ?>><input class="input" type="radio" id="giro01" name="payment_method" value="2" <?php if (isset($value['payment_method']) AND $value['payment_method'] == 2) print("checked=\"CHECKED\""); ?> />
			Girokort +01</label>
		</div>
		<div class="specialcase<?php if (isset($value['payment_method']) AND $value['payment_method'] == 3) print(" selected"); ?>">
			<input class="input" id="giro71" type="radio" name="payment_method" value="3" <?php if (isset($value['payment_method']) AND $value['payment_method'] == 3) print("checked=\"CHECKED\""); ?> />
			<label for="giro71">Girokort +71</label> &lt;
			<label for="girocode" style="display: none;">Girokode</label> <input class="input" name="girocode" id="girocode" value="<?php if (isset($value['girocode'])) e($value['girocode']); ?>" size="16" onfocus="if (document.getElementById) document.getElementById('giro71').checked = true;" /> + <?php e($context->getKernel()->setting->get("intranet", "giro_account_number")); ?>&lt;
		</div>
    <?php } ?>
    <?php if ($context->getKernel()->intranet->hasModuleAccess('shop')): ?>
        <div>
            <label<?php if (isset($value['payment_method']) AND $value['payment_method'] == 4) print(' class="selected"'); ?>><input class="input" id="account" type="radio" name="payment_method" value="4" <?php if (isset($value['payment_method']) AND $value['payment_method'] == 4) print("checked=\"CHECKED\""); ?> />
            Per efterkrav</label>
        </div>
    <?php endif; ?>
    <?php if ($context->getKernel()->intranet->hasModuleAccess('shop')): ?>
        <div>
            <label<?php if (isset($value['payment_method']) AND $value['payment_method'] == 5) print(' class="selected"'); ?>><input class="input" id="account" type="radio" name="payment_method" value="5" <?php if (isset($value['payment_method']) AND $value['payment_method'] == 5) print("checked=\"CHECKED\""); ?> />
            <?php e(t('Online payment')); ?></label>
        </div>
    <?php endif; ?>

    </fieldset>
	<?php } ?>

<div>
<input type="submit" class="save" name="submit" value="<?php e(t('Continue')); ?>" />
<?php if (!$context->getDebtor()->get("id")) { ?>
<a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
<?php } else { ?>
<a href="<?php e(url()); ?>"><?php e(t('Cancel')); ?></a>
<?php } ?>
</div>

<input type="hidden" name="type" value="<?php e($context->getDebtor()->get("type")); ?>" />
<input type="hidden" name="contact_id" value="<?php e($context->getContact()->get('id')); ?>" />
</form>

<?php endif; ?>
