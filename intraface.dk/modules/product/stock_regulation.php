<?php
require('../../include_first.php');

$module = $kernel->module("product");
$translation = $kernel->getTranslation('product');

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
$page->start(t('regulate stock'));
?>

<h1><?php e(t('regulate stock product')); ?></h1>

<p>#<?php print($product_object->get('number').' '.$product_object->get('name')); ?></p>

<?php echo $product_object->error->view(); ?>

<form method="POST" action="stock_regulation.php">
<fieldset>
    <legend><?php e(t('regulate with')); ?></legend>

    <div class="formrow">
      <label for="quantity"><?php e(t('quantity')); ?></label>
        <input type="text" name="quantity" id="quantity" value="<?php if(isset($values['quantity'])) e($values['quantity']); ?>" size="3" />
    </div>

    <div class="formrow">
        <label for="description"><?php e(t('description')); ?></label>
        <input type="text" name="description" id="description" value="<?php if(isset($values['description'])) e($values['description']); ?>" />
    </div>

    <br />

    <p><?php e(t('positive quantity should be used when products are added to the stock, and negative when removing products from the stock.')); ?></p>


</fieldset>

<input type="hidden" name="product_id" value="<?php e($product_object->get('id')); ?>" />
<input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" />  <a href="product.php?id=<?php print($product_object->get('id')); ?>&from=stock#stock"><?php e(t('regret', 'common')); ?></a>
</form>

<?php
$page->end();
?>
