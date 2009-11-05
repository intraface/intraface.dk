<?php
$values = $context->getValues();
?>

<h1><?php e(t('regulate stock product')); ?></h1>

<p>#<?php e($context->getProduct()->get('number')); if ($context->getVariation()) e('.'.$context->getVariation()->getNumber()); e(' '.$context->getProduct()->get('name')); if ($context->getVariation()) e(' - '.$context->getVariation()->getName()); ?></p>

<?php echo $context->getProduct()->error->view(); ?>

<form method="POST" action="<?php e(url()); ?>">
<fieldset>
    <legend><?php e(t('regulate with')); ?></legend>

    <div class="formrow">
      <label for="quantity"><?php e(t('quantity')); ?></label>
        <input type="text" name="quantity" id="quantity" value="<?php if (isset($values['quantity'])) e($values['quantity']); ?>" size="3" />
    </div>

    <div class="formrow">
        <label for="description"><?php e(t('description')); ?></label>
        <input type="text" name="description" id="description" value="<?php if (isset($values['description'])) e($values['description']); ?>" />
    </div>

    <br />

    <p><?php e(t('positive quantity should be used when products are added to the stock, and negative when removing products from the stock.')); ?></p>


</fieldset>

<input type="hidden" name="product_id" value="<?php e($context->getProduct()->get('id')); ?>" />
<input type="hidden" name="product_variation_id" value="<?php if ($context->getVariation()) e($context->getVariation()->getId()); ?>" />
<input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" />
<a href="<?php e(url('../')); ?>"><?php e(t('Cancel', 'common')); ?></a>
</form>
