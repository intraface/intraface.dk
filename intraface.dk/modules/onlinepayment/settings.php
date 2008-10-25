<?php
/**
 * QuickPay:
 * md5_secret
 * merchant_id
 */
require('../../include_first.php');

$onlinepayment_module = $kernel->module('onlinepayment');
$implemented_providers = $onlinepayment_module->getSetting('implemented_providers');

$onlinepayment = OnlinePayment::factory($kernel);


if (!empty($_POST)) {

	if ($onlinepayment->setSettings($_POST)) {
		header('Location: index.php');
		exit;
	}
	else {
		$value = $_POST;
	}

}
else {

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
			echo '<p>Her behøver du ikke sætte nogen indstillinger.</p>';
			break;
		default:
			trigger_error('Ugyldig provider');
			break;
	endswitch;
	?>

	<div>
		<input type="submit" value="Gem" />
	</div>

<form>

<?php

$page->end();

?>