<?php
require '../../include_first.php';

$onlinepayment_module = $kernel->module('onlinepayment');
$translation = $kernel->getTranslation('onlinepayment');
$implemented_providers = $onlinepayment_module->getSetting('implemented_providers');

$onlinepayment = OnlinePayment::factory($kernel);
$language = new Intraface_modules_language_Languages;
$settings = Doctrine::getTable('Intraface_modules_onlinepayment_Language')->findOneByIntranetId($kernel->intranet->getId());

if (!$settings) {
	$settings = new Intraface_modules_onlinepayment_Language;
    $settings->save();
}

if (!empty($_POST)) {

    $settings->Translation['da']->email = $_POST['email']['da'];
    $settings->Translation['da']->subject = $_POST['subject']['da'];

    foreach ($language->getChosenAsArray() as $lang) {
        $settings->Translation[$lang->getIsoCode()]->email = $_POST['email'][$lang->getIsoCode()];
        $settings->Translation[$lang->getIsoCode()]->subject = $_POST['subject'][$lang->getIsoCode()];
    }

    $settings->save();

	if ($onlinepayment->setSettings($_POST)) {
		header('Location: ' . $_SERVER['PHP_SELF']);
		exit;
	} else {
		$value = $_POST;
	}
} else {
	$value = $onlinepayment->getSettings();
}

$page = new Intraface_Page($kernel);
$page->start('Onlinebetalinger');
?>

<h1><?php e(t('Settings')); ?></h1>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

	<fieldset>
		<legend><?php e(t('Provider')); ?></legend>
		<p><?php e(t('You have chosen')); ?> <strong><?php e($implemented_providers[$kernel->setting->get('intranet', 'onlinepayment.provider_key')]); ?></strong>. <a href="choose_provider.php"><?php e(t('Select another provider')); ?></a>.</p>
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
        <legend>Tekst på e-mail på <?php e($lang->getDescription()); ?></legend>
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

<?php
$page->end();
?>