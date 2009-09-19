<?php
require('../../include_first.php');

$kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);

if (!empty($_POST)) {
	$voucher = new Voucher($year, (int)$_POST['id']);
	if ($voucher->save($_POST)) {
		header('Location: voucher.php?id=' .$voucher->get('id'));
		exit;
	}
	else {
		$value = $_POST;
	}
}
elseif (!empty($_GET['id'])) {
	$voucher = new Voucher($year, $_GET['id']);
	$value = $voucher->get();
	$value['date'] = $voucher->get('date_dk');
}
else {
	trigger_error('needs an id', E_USER_ERROR);
	$voucher = new Voucher($year);
	$value['date'] = date('d-m-Y');
	$value['number'] = $voucher->getMaxNumber() + 1;
}

$page = new Intraface_Page($kernel);
$page->start('Regnskab');
?>

<h1>Rediger bilag #<?php e($voucher->get('number')); ?> på <?php e($year->get('label')); ?></h1>

<?php echo $voucher->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

	<fieldset>
		<input type="hidden" value="<?php e($value['id']); ?>" name="id" />
		<div class="formrow">
			<label for="date">Dato</label>
			<input type="text" value="<?php if (!empty($value['date'])) e($value['date']); ?>" name="date" />
		</div>
		<div class="formrow">
			<label for="number">Nummer</label>
			<input type="text" value="<?php if (!empty($value['number'])) e($value['number']); ?>" name="voucher_number" />
		</div>
		<div class="formrow">
			<label for="text">Tekst</label>
			<input type="text" value="<?php if (!empty($value['text'])) e($value['text']); ?>" name="text" />
		</div>
		<div class="formrow">
			<label for="reference">Reference</label>
			<input type="text" value="<?php if (!empty($value['reference'])) e($value['reference']); ?>" name="reference" />
		</div>
	</fieldset>

	<div>
		<input type="submit" value="Gem" /> eller
		<?php if ($voucher->get('id') > 0): ?>
		<a href="voucher.php?id=<?php e($voucher->get('id')); ?>">fortryd</a>
		<?php else: ?>
		<a href="vouchers.php">fortryd</a>
		<?php endif; ?>
	</div>

</form>

<?php
$page->end();
?>
