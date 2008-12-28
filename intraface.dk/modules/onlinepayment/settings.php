<?php
require '../../include_first.php';

$onlinepayment_module = $kernel->module('onlinepayment');
$implemented_providers = $onlinepayment_module->getSetting('implemented_providers');

$onlinepayment = OnlinePayment::factory($kernel);
$language = new Intraface_modules_language_Languages;
$settings = Doctrine::getTable('Intraface_modules_onlinepayment_Language')->findByIntranetId($kernel->intranet->getId());
$settings = $settings[0];

if (!empty($_POST)) {

    $settings->Translation['da']->email = $_POST['email']['da'];
    $settings->Translation['da']->subject = $_POST['subject']['da'];
    foreach ($language->getChosenAsArray() as $lang) {
        $settings->Translation[$lang->getIsoCode()]->email = $_POST['email'][$lang->getIsoCode()];
        $settings->Translation[$lang->getIsoCode()]->subject = $_POST['subject'][$lang->getIsoCode()];
    }
    //$settings->save();

	if ($onlinepayment->setSettings($_POST)) {
		header('Location: index.php');
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

<h1>Sæt indstillinger</h1>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

	<fieldset>
		<legend>Udbyder</legend>
		<p>Du har valgt <strong><?php e($implemented_providers[$kernel->setting->get('intranet', 'onlinepayment.provider_key')]); ?></strong> som din udbyder. <a href="choose_provider.php">Vælg en anden udbyder</a>.</p>
	</fieldset>

	<?php
	switch($implemented_providers[$kernel->setting->get('intranet', 'onlinepayment.provider_key')]):
		case 'quickpay':
			?>
			<fieldset>
				<legend>Indstillinger</legend>
				<div class="formrow">
					<label for="merchant_id">Merchant id</label>
					<input type="text" name="merchant_id" id="merchant_id" value="<?php e($value['merchant_id']); ?>" />
				</div>

				<div class="formrow">
					<label for="md5_secret">MD5 Secret</label>
					<input type="text" name="md5_secret" id="md5_secret" value="<?php e($value['md5_secret']); ?>" />
				</div>
			</fieldset>
			<?php
			break;
		case 'dandomain':
			?>
			<fieldset>
				<legend>Indstillinger</legend>
				<div class="formrow">
					<label for="merchant_id">Merchant id</label>
					<input type="text" name="merchant_id" id="merchant_id" value="<?php e($value['merchant_id']); ?>" />
				</div>

				<div class="formrow">
					<label for="password">Password</label>
					<input type="text" name="password" id="password" value="<?php e($value['password']); ?>" />
				</div>
			</fieldset>
			<?php
			break;
		case 'default':
			echo '<p>Her behøver du ikke sætte nogen særlige indstillinger for denne provider.</p>';
			break;
		default:
			trigger_error('Ugyldig provider');
			break;
	endswitch;
	?>
    <fieldset>
        <legend>Tekst på e-mail på dansk</legend>
            <label for="language_da_subject">Subject</label><br />
            <input type="text" id="language_da_subject" name="subject[da]" value="<?php e($settings->Translation['da']->subject); ?>" />
            <br>

            <label for="language_da">Body</label><br />
            <textarea cols="80" id="language_da" name="email[da]"><?php e($settings->Translation['da']->email); ?></textarea>
            <br>
        </fieldset>
        <?php foreach ($language->getChosenAsArray() as $lang): ?>
            <fieldset>
        <legend>Tekst på e-mail på <?php e($lang->getDescription()); ?></legend>
            <label for="language_da_subject">Subject</label><br />
            <input type="text" id="language_<?php e($lang->getIsoCode()); ?>_subject" name="subject[<?php e($lang->getIsoCode()); ?>]" value="<?php e($settings->Translation[$lang->getIsoCode()]->subject); ?>" />
            <br>

            <label for="language_<?php e($lang->getIsoCode()); ?>">Subject</label><br />
            <textarea cols="80" id="language_<?php e($lang->getIsoCode()); ?>" name="email[<?php e($lang->getIsoCode()); ?>]"><?php e($settings->Translation[$lang->getIsoCode()]->email); ?></textarea>
            <br>
          </fieldset>
        <?php endforeach; ?>



	<div>
		<input type="submit" value="Gem" />
	</div>

<form>

<?php
$page->end();
?>