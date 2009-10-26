<?php
require('../../include_first.php');

$product_module = $kernel->module("product");
$translation = $kernel->getTranslation('product');

$redirect = Intraface_Redirect::factory($kernel, 'receive');

if ($redirect->get('id') != 0) {
    $multiple = $redirect->isMultipleParameter('product_variation_id');
} else {
    trigger_error("Der mangler en gyldig redirect", E_USER_ERROR);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['set_quantity']) && (int)$_POST['set_quantity'] == 1) {
        $quantity = 1;
    } else {
        $quantity = 0;
    }
    
    if (empty($_POST['product_id'])) {
        throw new Exception('You need to provide a product_id');
    }
    
    $product = new Product($kernel, intval($_POST['product_id']));
    
    if (isset($_POST['submit']) || isset($_POST['submit_close'])) {
        if ($multiple && is_array($_POST['selected'])) {
            foreach ($_POST['selected'] AS $selected_id => $selected_value) {
                if ((int)$selected_value > 0) {
                    $selected = serialize(array('product_id' => $product->getId(), 'product_variation_id' => $selected_id));
                    // Hvis der allerede er gemt en værdi, så starter vi med at fjerne den, så der ikke kommer flere på.
                    $redirect->removeParameter('product_variation_id', $selected);
                    if ($quantity) {
                        $redirect->setParameter('product_variation_id', $selected, $selected_value);
                    } else {
                        $redirect->setParameter('product_variation_id', $selected);
                    }
                }
            }
        } elseif (!$multiple && !empty($_POST['selected'])) {
            $selected = serialize(array('product_id' => $product->getId(), 'product_variation_id' => (int)$_POST['selected']));
            if ($quantity) {
                $redirect->setParameter('product_variation_id', $selected, (int)$_POST['quantity']);
            } else {
                $redirect->setParameter('product_variation_id', $selected);
            }
        }
    
        if (isset($_POST['submit_close'])) {
            header('location: '.$redirect->getRedirect('index.php'));
            exit;
        }
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    if (isset($_GET['set_quantity']) && (int)$_GET['set_quantity'] == 1) {
        $quantity = 1;
    } else {
        $quantity = 0;
    }
    
    if (empty($_GET['product_id'])) {
        throw new Exception('You need to provide a product_id');
    }
    
    $product = new Product($kernel, intval($_GET['product_id']));
    
    if (isset($_GET['edit_product_variation'])) {
        $add_redirect = Intraface_Redirect::factory($kernel, 'go');
        $add_redirect->setIdentifier('add_new');
        $url = $add_redirect->setDestination($product_module->getPath().'product_edit.php', $product_module->getPath().'select_product.php?'.$redirect->get('redirect_query_string').'&set_quantity='.$quantity);
        header('location: '.$url);
        exit;
    }   
}


if (!$product->get('has_variation')) {
    throw new Exception('The product is not with variations');
}

try {
    $variations = $product->getVariations();
}
catch(Exception $e) {
    if ($e->getMessage() == 'No groups is added to the product') {
        $variations = array();
    } else {
        throw $e;
    }
}

$page = new Intraface_Page($kernel);
$page->start(t('Select product variation'));
?>
<h1><?php e(t('Select product variation')); ?></h1>

<?php if (count($variations) == 0): ?>
    <p><?php e(t('No variations created for the product')); ?></p>
<?php else: ?>
    <form action="<?php e($_SERVER['PHP_SELF']); ?>?set_quantity=<?php e($quantity); ?>" method="post">
        <input type="hidden" name="product_id" value="<?php e($product->getId()); ?>" />
        <input type="hidden" name="set_quantity" value="<?php e($quantity); ?>" />
        <table summary="<?php e(t('Variations')); ?>" id="variations_table" class="stripe">
            <caption><?php e(t('Variations')); ?></caption>
            <thead>
                <tr>
                    <th><?php if ($multiple && $quantity): e(t('Quantity')); else: echo e(t('Choose')); endif; ?></th>
                    <th>#</th>
                    <th><?php e(t('Variation')); ?></th>
                    <th><?php e(t('Price')); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($variations AS $variation): ?>
                <tr>
                    <td>
                        <?php if ($multiple && $quantity): ?>
                            <input id="<?php e($variation->getId()); ?>" type="text" name="selected[<?php e($variation->getId()); ?>]" value="<?php echo '0' ?>" size="2" />
                        <?php elseif ($multiple && !$quantity): ?>
                            <input id="<?php e($variation->getId()); ?>" type="checkbox" name="selected[<?php e($variation->getId()); ?>]" value="1" />
                        <?php elseif (!$multiple): ?>
                            <input id="<?php e($variation->getId()); ?>" type="radio" name="selected" value="<?php e($variation->getId()); ?>" />
                        <?php endif; ?>
                    </td>
                    <td><?php e($variation->getNumber()); ?></td>
                    <td><?php e($variation->getName()); ?></td>
                    <td><?php e($variation->getDetail()->getPrice($product)->getAsLocal('da_dk', 2)); ?> <?php e(t('excl. vat')); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p>
        <?php if (!$multiple && $quantity): ?>
            <?php e(t('Quantity')); ?>: <input type="text" name="quantity" value="1" />
        <?php endif; ?>
        <?php if ($multiple): ?>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" />
        <?php endif; ?>
        <input type="submit" name="submit_close" value="<?php e(t('save and close', 'common')); ?>" /></p>
    </form>
<?php endif; ?>
<?php
$page->end();
?>