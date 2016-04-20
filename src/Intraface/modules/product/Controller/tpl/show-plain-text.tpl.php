<table class="stripe">
    <caption><?php e(t('Product information')); ?></caption>
    <tr>
        <td><?php e(t('Id')); ?></td>
        <td><?php e($product->getId()); ?></td>
    </tr>
    <tr>
        <td><?php e(t('Number')); ?></td>
        <td><?php e($product->get('number'));  ?></td>
    </tr>
    <tr>
        <td><?php e(t('Name')); ?></td>
        <td><?php e($product->get('name')); ?></td>
    </tr>
    <tr>
        <td><?php e(t('Price DKK')); ?></td>
        <td><?php e(number_format($product->get('price_incl_vat'), 2)); ?> (<?php e(number_format($product->get('price'), 2)); ?> <?php e(t('ex vat')); ?>)</td>
    </tr>
    <tr>
        <td><?php e(t('Before price DKK')); ?></td>
        <td><?php e(number_format($product->getDetails()->getBeforePriceIncludingVat()->getAsISo(), 2)); ?> (<?php e(number_format($product->get('before_price'), 2)); ?> <?php e(t('ex vat')); ?>)</td>
    </tr>
    <tr>
        <td><?php e(t('Vat')); ?></td>
        <td>
            <?php
                $vat_choises = array(0=>"No", 1=>"Yes");
                e(t($vat_choises[$product->get('vat')]));
            ?>
        </td>
    </tr>
    <tr>
        <td><?php e(t('Weight')); ?></td>
        <td><?php e($product->get('weight')); ?> gram</td>
    </tr>
    <tr>
        <td><?php e(t('Show in webshop')); ?></td>
        <td>
            <?php
                $show_choises = array(0=>"No", 1=>"Yes");
                e(t($show_choises[$product->get('do_show')]));
            ?>
        </td>
    </tr>
    <?php if ($kernel->intranet->hasModuleAccess('stock')) : ?>
    <tr>
        <td><?php e(t('Stock product')); ?></td><td>
            <?php
                $stock_choises = array(0=>"No", 1=>"Yes");
                e(t($stock_choises[$product->get('stock')]));
            ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
       <td><?php e(t('Description')); ?></td>
       <td><?php autohtml($product->get('description')); ?></td>
    </tr>
</table>

<?php if ($product->hasVariation()) : ?>
    <?php
    try {
        $variations = $product->getVariations();
        $variation_is_present = true;
    } catch (Intraface_Gateway_Exception $e) {
        $variation_is_present = false;
    }
    ?>
    <?php if ($variation_is_present) : ?>
        <table summary="<?php e(t('Variations')); ?>" id="variations_table" class="stripe">
            <caption><?php e(t('Variations')); ?></caption>
            <thead>
                <tr>
                    <th>#</th>
                    <th><?php e(t('Variation')); ?></th>
                    <th><?php e(t('Price')); ?><br /><?php e(t('excl. vat')); ?></th>
                    <th><?php e(t('Weight')); ?><br />Gram</th>
                    <?php if ($kernel->user->hasModuleAccess("stock") and $product->get('stock')) : ?>
                        <th><?php e(t('In stock')); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($variations as $variation) : ?>
                <tr>
                    <td><?php e($variation->getNumber()); ?></td>
                    <td><?php e($variation->getName()); ?></td>
                    <td><?php e($variation->getDetail()->getPrice($product)->getAsLocal('da_dk', 2)); ?> </td>
                    <td><?php e($product->get('weight')+$variation->getDetail()->getWeightDifference()); ?></td>
                    <?php if ($kernel->user->hasModuleAccess("stock") and $product->get('stock')) : ?>
                        <td><?php e($variation->getStock($product)->get('actual_stock')); ?></td>
                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php endif; ?>

<table class="stripe">
    <caption><?php e(t('Related products')); ?></caption>
    <thead>
        <tr>
            <th><?php e(t('ID')); ?></th>
            <th><?php e(t('Number')); ?></th>
            <th><?php e(t('Name')); ?></th>
        </tr>
    </thead>
    <?php
    $related = $product->getRelatedProducts();
    ?>
    <?php if (!empty($related) and count($related) > 0) : ?>
        <?php foreach ($related as $p) : ?>
            <tr>
                <td><?php e($p['id']); ?></td>
                <td><?php e($p['number']); ?></td>
                <td><?php e($p['name']); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<table class="stripe">
    <caption><?php e(t('Images')); ?></caption>
    <thead>
        <tr>
            <th><?php e(t('Name')); ?></th>
            <th><?php e(t('Download')); ?></th>
        </tr>
    </thead>
    <?php
    $pictures = $product->getNewPictures();
    ?>
    <?php if (count($pictures) > 0) : ?>
        <?php foreach ($pictures as $appendix) : ?>
            <tr>
                <td><?php e($appendix['original']['name']); ?></td>
                <td><a href="<?php e($appendix['original']['file_uri']); ?>"><img src="<?php e($appendix['original']['file_uri']); ?>" height="100" /></a></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
    

<?php if ($kernel->user->hasModuleAccess('shop')) : ?>
    <?php $module_shop = $kernel->useModule('shop'); ?>
    <table class="stripe">
        <caption><?php e(t('Categories')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('Shop')); ?></th>
                <th><?php e(t('Category')); ?></th>
            </tr>
        </thead>
        <?php
        $gateway = new Intraface_modules_shop_Shop_Gateway();
        $shops = $gateway->findAll();
        ?>
        <?php foreach ($shops as $shop) : ?>
            <tr>
                <td><strong><?php e($shop->getName()); ?></strong></td>
                <td>
                    <?php
                    $category_type = new Intraface_Category_Type('shop', $shop->getId());
                    $category = new Intraface_Category($kernel, $db, $category_type);
                    $appender = $category->getAppender($product->getId());
                    ?>
                    <ul>
                        <?php foreach ($appender->getCategories() as $category) : ?>
                            <li><?php e($category['name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
        <?php endforeach; ?>
     </table>
<?php endif; ?>

<table class="stripe">
    <caption><?php e(t('Keywords')); ?></caption>
    <thead>
        <tr>
            <th><?php e(t('Keyword')); ?></th>
        </tr>
    </thead>
    <?php
    $keyword = $product->getKeywordAppender();
    $keywords = $keyword->getConnectedKeywords();
    ?>
    <?php if (is_array($keywords) and count($keywords) > 0) : ?>
        <?php foreach ($keywords as $k) : ?>
            <tr>
                <td><?php e($k['keyword']); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php if ($kernel->user->hasModuleAccess("stock") and $product->get('stock') and !$product->get('has_variation')) : ?>
    <table>
        <caption><?php e(t('Stock')); ?></caption>
        <tr>
            <td><?php e(t('stock status')); ?></td>
            <td><?php e($product->getStock()->get("actual_stock")); ?></td>
        </tr>
        <tr>
            <td><?php e(t('ordered')); ?></td>
            <td><?php e($product->getStock()->get("on_order")); ?></td>
        </tr>
        <tr>
            <td><?php e(t('reserved')); ?></td>
            <td><?php e($product->getStock()->get("reserved")); ?> (<?php e($product->getStock()->get("on_quotation")); ?>)</td>
        </tr>
    </table>
<?php endif; ?>
