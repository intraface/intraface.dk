<h1>Ret vare</h1>

<?php echo $procurement->item->error->view(); ?>

<form method="POST" action="<?php e(url()); ?>" id="form_items">
<fieldset>
	<legend>Produkt</legend>

	<div class="formrow">
		<label for="number">Nummer</label><span id="number"><?php e($procurement->item->getProductNumber()); ?></span>
	</div>

	<div class="formrow">
		<label for="name">Navn</label><span id="name"><?php e($procurement->item->getProductName()); ?> <a href="<?php e(url(null, array('change_product'=>1))); ?>" class="edit">Skift</a></span>
	</div>

	<div class="formrow">
		<label for="price">Pris</label><span id="price"><?php echo $procurement->item->getProductPrice()->getAsLocal('da_dk', 2); ?></span>
	</div>

</fieldset>

<fieldset>
	<legend>Antal</legend>

	<div class="formrow">
		<label for="quantity">Antal</label>
    <input type="text" name="quantity" id="quantity" value="<?php if (!empty($values['quantity'])) e($values["quantity"]); ?>" />
	</div>

	<div class="formrow">
		<label for="dk_unit_purchase_price">Pris pr. stk</label>
    <input type="text" name="dk_unit_purchase_price" id="dk_unit_purchase_price" value="<?php if (!empty($values['dk_unit_purchase_price'])) e($values["dk_unit_purchase_price"]); ?>" />
	</div>
</fieldset>
<div>
	<input type="hidden" name="id" value="<?php e($procurement->item->get('id')); ?>" />
    <input type="hidden" name="procurement_id" value="<?php e($procurement->get("id")); ?>" />
    <input type="hidden" name="product_id" value="<?php e($procurement->item->get('product_id')); ?>" />
    <input type="hidden" name="product_detail_id" value="<?php e($procurement->item->get('product_detail_id')); ?>" />
    <input type="hidden" name="product_variation_id" value="<?php e($procurement->item->get('product_variation_id')); ?>" />
    <input type="hidden" name="product_variation_detail_id" value="<?php e($procurement->item->get('product_variation_detail_id')); ?>" />

    <input type="submit" name="submit" value="Gem" class="save" />
    <a href="<?php e(url('../'));  ?>">Fortryd</a>
</div>
</form>
