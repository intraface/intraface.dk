<?php
require('../../include_first.php');

$debtor_module = $kernel->module("debtor");
$product_module = $kernel->useModule('product');
$translation = $kernel->getTranslation('debtor');

settype($_GET['id'], "integer");

if (!empty($_POST)) {
	$debtor = Debtor::factory($kernel, intval($_POST["debtor_id"]));
	$debtor->loadItem(intval($_POST["id"]));

	if ($id = $debtor->item->save($_POST)) {
		header("Location: view.php?id=".$debtor->get("id")."&item_id=".$id);
        exit;
	} else {
		$values = $_POST;
	}
} elseif (isset($_GET['debtor_id']) && isset($_GET['id'])) {
	$debtor = Debtor::factory($kernel, intval($_GET["debtor_id"]));
	$debtor->loadItem(intval($_GET["id"]));
	$values = $debtor->item->get();
	$values["quantity"] = number_format($debtor->item->get('quantity'), 2, ",", ".");
    $values['description'] = $debtor->item->get('description');
} else {
	trigger_error("Der mangler debtor_id eller id", E_USER_ERROR);
}

if (isset($_GET['change_product'])) {
	$redirect = Intraface_Redirect::factory($kernel, 'go');
	$url = $redirect->setDestination($product_module->getPath().'select_product.php', $debtor_module->getPath().'item_edit.php?debtor_id='.$debtor->get('id').'&id='.$debtor->item->get('id'));
	$redirect->askParameter('product_id');
	header('location: '.$url);
	exit;
}

if (isset($_GET['return_redirect_id'])) {
	$redirect = Intraface_Redirect::factory($kernel, 'return');
    $returned_values = unserialize($redirect->getParameter('product_id'));
	$debtor->item->changeProduct($returned_values['product_id'], $returned_values['product_variation_id']);
    $debtor->loadItem(intval($_GET["id"]));
}

$page = new Intraface_Page($kernel);
$page->start(__($debtor->get('type').' content'));
?>

<h1><?php e(__($debtor->get('type').' content')); ?></h1>

<?php echo $debtor->item->error->view(); ?>

<form method="POST" action="item_edit.php" id="form_items">
<fieldset>
	<legend><?php e(__('Product')); ?></legend>
	<div class="formrow">
		<label for="number"><?php e(__('Number')); ?></label><span id="number"><?php e($debtor->item->getProductNumber()); ?></span>
	</div>

	<div class="formrow">
		<label for="name"><?php e(__('Name', 'common')); ?></label><span id="name"><?php e($debtor->item->getProductName()); ?> <a href="item_edit.php?debtor_id=<?php e($debtor->get('id')); ?>&amp;id=<?php e($debtor->item->get('id')); ?>&amp;change_product=1" class="edit">Skift</a></span>
	</div>

	<div class="formrow">
		<label for="price"><?php e(__('Price', 'common')); ?></label><span id="price"><?php echo $debtor->item->getProductPrice()->getAsLocal('da_dk', 2); ?></span>
	</div>

	<div class="formrow">
		<label for="vat"><?php e(__('VAT')); ?></label><span id="vat"><?php if ($debtor->item->getTaxPercent() > 0): e(__('Yes', 'common')); else: e(__('No', 'common')); endif; ?></span>
	</div>
</fieldset>

<fieldset>
	<legend><?php e(__('Quantity')); ?></legend>

	<div class="formrow">
		<label for="quantity"><?php e(__('Quantity')); ?></label>
    <input type="text" name="quantity" id="quantity" value="<?php e($values["quantity"]); ?>" />
	</div>
</fieldset>

<fieldset>
	<legend><?php e(__('Description')); ?></legend>
	<div class="formrow">
		<label for="description"><?php e(__('Description')); ?></label>
    <textarea name="description" id="description" style="width: 500px; height: 200px;"><?php if (isset($values["description"])) e($values["description"]); ?></textarea>
	</div>
</fieldset>
<div>
    <input type="hidden" name="id" value="<?php e($debtor->item->get("id")); ?>" />
    <input type="hidden" name="debtor_id" value="<?php e($debtor->get("id")); ?>" />
    <input type="hidden" name="product_id" value="<?php  e($debtor->item->get('product_id')); ?>" />
    <input type="hidden" name="product_detail_id" value="<?php  e($debtor->item->get('product_detail_id')); ?>" />
    <input type="hidden" name="product_variation_id" value="<?php  e($debtor->item->get('product_variation_id')); ?>" />
    <input type="hidden" name="product_variation_detail_id" value="<?php  e($debtor->item->get('product_variation_detail_id')); ?>" />

	<input type="submit" name="submit" value="<?php e(__('Save', 'common')); ?>" class="save" /> <?php e(__('or', 'common')); ?>
  <a href="view.php?id=<?php e($debtor->get("id"));  ?>"><?php e(__('Cancel', 'common')); ?></a>

</div>
</form>

<?php
$page->end();
?>