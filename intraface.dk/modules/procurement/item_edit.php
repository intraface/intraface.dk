<?php
require('../../include_first.php');

$procurement_module = $kernel->module("procurement");
$product_module = $kernel->useModule('product');
$translation = $kernel->getTranslation('procurement');

settype($_GET['id'], "integer");

if(!empty($_POST)) {
	$procurement = new Procurement($kernel, intval($_POST["procurement_id"]));
	$procurement->loadItem(intval($_POST["id"]));

	if($id = $procurement->item->save($_POST)) {
		header("Location: view.php?id=".$procurement->get("id")."&item_id=".$id);
    exit;
	}
	else {
		$values = $_POST;
	}
}
elseif(isset($_GET['procurement_id']) && isset($_GET['id'])) {
	$procurement = new Procurement($kernel, intval($_GET["procurement_id"]));
	$procurement->loadItem(intval($_GET["id"]));
    $values['quantity'] = $procurement->item->get('quantity');
    $values['dk_unit_purchase_price'] = $procurement->item->get('dk_unit_purchase_price');
}
else {
	trigger_error("Der mangler procurement_id eller id", E_USER_ERROR);
}

if(isset($_GET['change_product'])) {
	$redirect = Intraface_Redirect::factory($kernel, 'go');
	$url = $redirect->setDestination($product_module->getPath().'select_product.php', $procurement_module->getPath().'item_edit.php?procurement_id='.$procurement->get('id').'&id='.$procurement->item->get('id'));
	$redirect->askParameter('product_id');
	header('location: '.$url);
	exit;
}

if(isset($_GET['return_redirect_id'])) {
	$redirect = Intraface_Redirect::factory($kernel, 'return');
	$returned_values = unserialize($redirect->getParameter('product_id'));
    $procurement->item->changeProduct($returned_values['product_id'], $returned_values['product_variation_id']);
    $procurement->loadItem(intval($_GET["id"]));
    
}

$page = new Intraface_Page($kernel);
$page->start("Ret vare");
?>

<h1>Ret vare</h1>

<?php echo $procurement->item->error->view(); ?>



<form method="POST" action="item_edit.php" id="form_items">
<fieldset>
	<legend>Produkt</legend>
	
	<div class="formrow">
		<label for="number">Nummer</label><span id="number"><?php e($procurement->item->getProductNumber()); ?></span>
	</div>

	<div class="formrow">
		<label for="name">Navn</label><span id="name"><?php e($procurement->item->getProductName()); ?> <a href="item_edit.php?procurement_id=<?php echo intval($procurement->get('id')); ?>&amp;id=<?php echo intval($procurement->item->get('id')); ?>&amp;change_product=1" class="edit">Skift</a></span>
	</div>

	<div class="formrow">
		<label for="price">Pris</label><span id="price"><?php echo $procurement->item->getProductPrice()->getAsLocal('da_dk', 2); ?></span>
	</div>

</fieldset>

<fieldset>
	<legend>Antal</legend>

	<div class="formrow">
		<label for="quantity">Antal</label>
    <input type="text" name="quantity" id="quantity" value="<?php if (!empty($values['quantity'])) echo safeToForm($values["quantity"]); ?>" />
	</div>

	<div class="formrow">
		<label for="dk_unit_purchase_price">Pris pr. stk</label>
    <input type="text" name="dk_unit_purchase_price" id="dk_unit_purchase_price" value="<?php if (!empty($values['dk_unit_purchase_price'])) echo safeToForm($values["dk_unit_purchase_price"]); ?>" />
	</div>
</fieldset>
<div>
	<input type="hidden" name="id" value="<?php print(intval($procurement->item->get('id'))); ?>" />
    <input type="hidden" name="procurement_id" value="<?php print(intval($procurement->get("id"))); ?>" />
    <input type="hidden" name="product_id" value="<?php echo intval($procurement->item->get('product_id')); ?>" />
    <input type="hidden" name="product_detail_id" value="<?php echo intval($procurement->item->get('product_detail_id')); ?>" />
    <input type="hidden" name="product_variation_id" value="<?php echo intval($procurement->item->get('product_variation_id')); ?>" />
    <input type="hidden" name="product_variation_detail_id" value="<?php echo intval($procurement->item->get('product_variation_detail_id')); ?>" />
    
    <input type="submit" name="submit" value="Gem" class="save" /> eller
    <a href="view.php?id=<?php echo intval($procurement->get("id"));  ?>">Fortryd</a>
</div>
</form>

<?php
$page->end();
?>