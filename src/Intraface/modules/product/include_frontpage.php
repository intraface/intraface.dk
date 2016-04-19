<?php
/**
 * @package Intraface_Product
 */

$product_module = $kernel->useModule('product');

$product = new Product($kernel);

if (!$product->isFilledIn()) :
    $_advice[] = array(
        'msg' => 'you can create new products',
        'link' => $product_module->getPath(),
        'module' => $product_module->getName()
    );
endif;
