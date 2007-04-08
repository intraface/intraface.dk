<?php
require('../../include_first.php');

$module = $kernel->module("product");

if(!$kernel->user->hasModuleAccess('stock')) {
	trigger_error("Du har ikke adgang til disse sider", ERROR);
}


if(isset($_POST['submit'])) {
	$product_object = new Product($kernel, $_POST['product_id']);

	if($product_object->get('id') == 0) {
		trigger_error("Ugyldigt product_id", ERROR);
	}

	if($product_object->stock->regulate($_POST)) {
		header("Location: product.php?id=".$product_object->get('id')."&from=stock#stock");
		exit;
	}

	$values = $_POST;
}
else {
	// set up product
	$product_object = new Product($kernel, $_GET['product_id']);
	if($product_object->get('id') == 0) {
		trigger_error("Ugyldigt product_id", ERROR);
	}
}

$page = new Page($kernel);
$page->start("Lager regulering");
?>

<h1>Regulering af lagervare</h1>

<p>#<?php print($product_object->get('number').' '.$product_object->get('name')); ?></p>

<?php $product_object->error->view(); ?>

<form method="POST" action="stock_regulation.php">
<fieldset>
	<legend>Regulere med</legend>

	<div class="formrow">
	  <label for="quantity">Antal:</label>
		<input type="text" name="quantity" id="quantity" value="<?php if(isset($values['quantity'])) print($values['quantity']); ?>" size="3" />
	</div>

	<div class="formrow">
		<label for="description">Beskrivelse:</label>
		<input type="text" name="description" id="description" value="<?php if(isset($values['description'])) print($values['description']); ?>" />
	</div>

	<br />

	<p>Positivt antal benyttes ved tilgang af varen til lageret, mens negativt antal benyttes ved nedættelse af antallet på lageret.</p>


</fieldset>

<input type="hidden" name="product_id" value="<?php print($product_object->get('id')); ?>" />
<input type="submit" name="submit" value="Gem" />  <a href="product.php?id=<?php print($product_object->get('id')); ?>&from=stock#stock">Fortryd</a>
</form>

<?php
$page->end();
?>
