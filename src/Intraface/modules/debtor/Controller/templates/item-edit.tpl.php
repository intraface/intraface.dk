<h1><?php e(__($context->getDebtor()->get('type').' content')); ?></h1>

<?php echo $context->getDebtor()->item->error->view(); ?>

<form method="POST" action="item_edit.php" id="form_items">
<fieldset>
	<legend><?php e(__('Product')); ?></legend>
	<div class="formrow">
		<label for="number"><?php e(__('Number')); ?></label><span id="number"><?php e($context->getDebtor()->item->getProductNumber()); ?></span>
	</div>

	<div class="formrow">
		<label for="name"><?php e(__('Name', 'common')); ?></label><span id="name"><?php e($context->getDebtor()->item->getProductName()); ?> <a href="item_edit.php?debtor_id=<?php e($context->getDebtor()->get('id')); ?>&amp;id=<?php e($context->getDebtor()->item->get('id')); ?>&amp;change_product=1" class="edit">Skift</a></span>
	</div>

	<div class="formrow">
		<label for="price"><?php e(__('Price', 'common')); ?></label><span id="price"><?php echo $context->getDebtor()->item->getProductPrice()->getAsLocal('da_dk', 2); ?></span>
	</div>

	<div class="formrow">
		<label for="vat"><?php e(__('VAT')); ?></label><span id="vat"><?php if ($context->getDebtor()->item->getTaxPercent() > 0): e(__('Yes', 'common')); else: e(__('No', 'common')); endif; ?></span>
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
    <input type="hidden" name="id" value="<?php e($context->getDebtor()->item->get("id")); ?>" />
    <input type="hidden" name="debtor_id" value="<?php e($context->getDebtor()->get("id")); ?>" />
    <input type="hidden" name="product_id" value="<?php  e($context->getDebtor()->item->get('product_id')); ?>" />
    <input type="hidden" name="product_detail_id" value="<?php  e($context->getDebtor()->item->get('product_detail_id')); ?>" />
    <input type="hidden" name="product_variation_id" value="<?php  e($context->getDebtor()->item->get('product_variation_id')); ?>" />
    <input type="hidden" name="product_variation_detail_id" value="<?php  e($context->getDebtor()->item->get('product_variation_detail_id')); ?>" />

	<input type="submit" name="submit" value="<?php e(__('Save', 'common')); ?>" class="save" /> <?php e(__('or', 'common')); ?>
  <a href="view.php?id=<?php e($context->getDebtor()->get("id"));  ?>"><?php e(__('Cancel', 'common')); ?></a>

</div>
</form>
