<h1><?php e(t('Settings')); ?></h1>

<form action="<?php e(url()); ?>" method="post">

	<fieldset>
		<legend><?php e(t('Provider')); ?></legend>
		<p><?php e(t('You have chosen')); ?> <strong><?php e($implemented_providers[$kernel->setting->get('intranet', 'onlinepayment.provider_key')]); ?></strong>.
		<a href="<?php e(url('chooseprovider')); ?>"><?php e(t('Select another provider')); ?></a>.</p>
	</fieldset>

	<?php
	switch($implemented_providers[$kernel->setting->get('intranet', 'onlinepayment.provider_key')]):
		case 'quickpay':
			?>
			<fieldset>
				<legend><?php e(t('Settings')); ?></legend>
				<div class="formrow">
					<label for="merchant_id"><?php e(t('Merchant id')); ?></label>
					<input type="text" name="merchant_id" id="merchant_id" value="<?php e($value['merchant_id']); ?>" />
				</div>

				<div class="formrow">
					<label for="md5_secret"><?php e(t('MD5 secret')); ?></label>
					<input type="text" name="md5_secret" id="md5_secret" value="<?php e($value['md5_secret']); ?>" />
				</div>
			</fieldset>
			<?php
			break;
		case 'dandomain':
			?>
			<fieldset>
				<legend><?php e(t('Settings')); ?></legend>
				<div class="formrow">
					<label for="merchant_id"><?php e(t('Merchant id')); ?></label>
					<input type="text" name="merchant_id" id="merchant_id" value="<?php e($value['merchant_id']); ?>" />
				</div>

				<div class="formrow">
					<label for="password"><?php e(t('Password')); ?></label>
					<input type="text" name="password" id="password" value="<?php e($value['password']); ?>" />
				</div>
			</fieldset>
			<?php
			break;
		case 'default': ?>
			<p><?php e(t('No specific settings needed for this provider')); ?></p>
			<?php break;
		default:
			trigger_error('Ugyldig provider');
			break;
	endswitch;
	?>
    <fieldset>
        <legend><?php e(t('Text on danish email')); ?></legend>
            <label for="language_da_subject"><?php e(t('Subject')); ?></label><br />
            <input type="text" id="language_da_subject" name="subject[da]" value="<?php e($settings->Translation['da']->subject); ?>" />
            <br>

            <label for="language_da"><?php e(t('Body text')); ?></label><br />
            <textarea cols="80" id="language_da" name="email[da]"><?php e($settings->Translation['da']->email); ?></textarea>
            <br>
        </fieldset>
        <?php foreach ($language->getChosenAsArray() as $lang): ?>
            <fieldset>
        <legend>Tekst p� e-mail p� <?php e($lang->getDescription()); ?></legend>
            <label for="language_da_subject"><?php e(t('Subject')); ?></label><br />
            <input type="text" id="language_<?php e($lang->getIsoCode()); ?>_subject" name="subject[<?php e($lang->getIsoCode()); ?>]" value="<?php e($settings->Translation[$lang->getIsoCode()]->subject); ?>" />
            <br>

            <label for="language_<?php e($lang->getIsoCode()); ?>"><?php e(t('Body text')); ?></label><br />
            <textarea cols="80" id="language_<?php e($lang->getIsoCode()); ?>" name="email[<?php e($lang->getIsoCode()); ?>]"><?php e($settings->Translation[$lang->getIsoCode()]->email); ?></textarea>
            <br>
          </fieldset>
        <?php endforeach; ?>



	<div>
		<input type="submit" value="<?php e(t('Save')); ?>" />
	</div>

</form>
