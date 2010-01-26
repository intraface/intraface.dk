<div id="colOne">

<div class="box">
    <h2>#<?php e($product->get('number'));  ?> <?php e($product->get('name')); ?></h2>
    <ul class="options">
        <?php if ($product->get('locked') != 1) { ?>
        <li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(__('Edit', 'common')); ?></a></li>

        <li><a class="confirm" href="<?php e(url(null, array('delete'))); ?>"><?php e(__('Delete', 'common')); ?></a></li>
        <?php } ?>
        <li><a href="<?php e(url(null, array('copy'))); ?>"><?php e(t('Copy', 'common')); ?></a></li>
        <li><a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(t('Close', 'common')); ?></a></li>
    </ul>
    <div><?php autohtml($product->get('description')); ?></div>
</div>

<table>
    <?php if (!$product->hasVariation()): ?>
        <tr>
            <td><?php e(t('Price')); ?></td>
            <td><?php e(number_format($product->get('price'), 2, ",", ".")); ?> <?php e(t('excl. vat')); ?></td>
        </tr>
        <?php if ($kernel->user->hasModuleAccess('webshop') || $kernel->user->hasModuleAccess('shop')): ?>
            <?php if ($product->get('before_price') != 0.00): ?>
                <tr>
                    <td><?php e(t('Before price')); ?></td>
                    <td><?php e(number_format($product->get('before_price'), 2, ",", ".")); ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td><?php e(t('Weight')); ?></td>
                <td><?php e($product->get('weight')); ?> gram</td>
            </tr>
        <?php endif; ?>
    <?php endif; ?>
    <tr>
        <td><?php e(t('Unit type')); ?></td>
        <td>
            <?php
                // getting settings
                $unit_choises  = Product::getUnits();
                e(t($unit_choises[$product->get('unit_id')]['combined']));
            ?>
        </td>
    </tr>

    <?php if ($kernel->user->hasModuleAccess("webshop") || $kernel->user->hasModuleAccess("shop")): ?>

    <tr>
        <td><?php e(t('Show in webshop')); ?></td>
        <td>
            <?php
                $show_choises = array(0=>"Nej", 1=>"Ja");
                e($show_choises[$product->get('do_show')]);
            ?>
        </td>
    </tr>

    <!-- her b�r v�re en tidsangivelse -->

    <?php endif; ?>

    <tr>
        <td><?php e(t('Vat')); ?></td>
        <td>
            <?php
                $vat_choises = array(0=>"Nej", 1=>"Ja");
                e($vat_choises[$product->get('vat')]);
            ?>
        </td>
    </tr>
    <?php if ($kernel->intranet->hasModuleAccess('stock')): ?>
    <tr>
        <td><?php e(t('Stock product')); ?></td><td>
            <?php
                $stock_choises = array(0=>"No", 1=>"Yes");
                e($stock_choises[$product->get('stock')], 'common');
            ?>
        </td>
    </tr>
    <?php endif; ?>
    <?php
    if ($kernel->user->hasModuleAccess('accounting')):
        $mainAccounting = $kernel->useModule("accounting");
        ?>
        <tr>
            <td><?php e(t('State on')); ?></td><td>
            <?php
                $year = new Year($kernel);
                if ($year->get('id') == 0) {
                    echo t('year is not set in accounting');
                } else {
                    $account = Account::factory($year, $product->get('state_account_id'));
                    if ($account->get('name')) {
                        e($account->get('number') . ' ' . $account->get('name'));
                    } else {
                        echo t('Not set');
                    }
                }
            ?>
            </td>
        </tr>
    <?php endif; ?>
</table>

<?php
if ($kernel->user->hasModuleAccess('invoice')) {
    $debtor_module = $kernel->useModule('debtor');
    $invoice = new Debtor($kernel, 'invoice');
    if ($invoice->any('product', $product->get('id'))) {
        ?>
        <ul class="options">
            <li><a href="<?php e(url('../../debtor/invoice/list', array('product_id' => $product->get('id'), 'status' => -1))); ?>"><?php e(t('Invoices with this product')); ?></a></li>
        </ul>
        <?php
    }
}
?>


<?php if ($product->hasVariation()): ?>
    <?php /* <h2><?php e(t('Variations')); ?></h2> */ ?>
    <?php
    $groups = $product->getAttributeGroups();
    ?>
    <?php if (count($groups) == 0): ?>
        <ul class="options">
            <li><a href="<?php e(url('variations/select_attribute_groups')); ?>"><?php e(t('Select attributes for product')); ?></a></li>
        </ul>
    <?php else: ?>
        <?php
        try {
            $variations = $product->getVariations();
            $variation_is_present = true;
        } catch (Intraface_Gateway_Exception $e) {
            $variation_is_present = false;
        }
        ?>
        <?php if ($variation_is_present): ?>
        <?php if ($variations->count() == 0): ?>
            <ul class="options">
                <li><a href="<?php e(url('variations', array('edit'))); ?>"><?php e(t('Create variations for the product')); ?></a></li>
            </ul>
        <?php else: ?>

            <table summary="<?php e(t('Variations')); ?>" id="variations_table" class="stripe">
                <caption><?php e(t('Variations')); ?></caption>
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php e(t('Variation')); ?></th>
                        <th><?php e(t('Price')); ?><br /><?php e(t('excl. vat')); ?></th>
                        <th><?php e(t('Weight')); ?><br />Gram</th>
                        <?php if ($kernel->user->hasModuleAccess("stock") AND $product->get('stock')): ?>
                            <th><?php e(t('In stock')); ?></th>
                            <?php /* At this moment there is only a reason for more details when there is stock */ ?>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($variations AS $variation): ?>
                    <tr>
                        <td><?php e($variation->getNumber()); ?></td>
                        <td><?php e($variation->getName()); ?></td>
                        <td><?php e($variation->getDetail()->getPrice($product)->getAsLocal('da_dk', 2)); ?> </td>
                        <td><?php e($product->get('weight')+$variation->getDetail()->getWeightDifference()); ?></td>
                        <?php if ($kernel->user->hasModuleAccess("stock") AND $product->get('stock')): ?>
                            <td><?php e($variation->getStock($product)->get('actual_stock')); ?></td>
                            <td><a href="<?php e(url('variation/' . $variation->getId())); ?>"><?php e(t('Details', 'common')); ?></a></td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <?php endif; ?>
            <ul class="options">
                <li><a href="<?php e(url('variations', array('edit'))); ?>"><?php e(t('Edit variations for the product')); ?></a></li>
				<?php if($context->getKernel()->user->hasModuleAccess('stock')): ?>
                <li><a href="<?php e(url('variations/stock')); ?>"><?php e(t('Edit stock for variations')); ?></a></li>
				<?php endif; ?>
            </ul>

    <?php endif; ?>
<?php endif; ?>

<div id="related_products" class="box">
    <h2><?php e(t('related products')); ?></h2>
    <?php if ($product->get('locked') == 0) { ?>
        <ul class="button">
            <li><a href="<?php e(url('related', array('add'))); ?>"><?php e(t('Add products')); ?></a></li>
        </ul>
    <?php } ?>
    <?php
        $related = $product->getRelatedProducts();
        if (!empty($related) AND count($related) > 0) {
            foreach ($related AS $p) {
                echo '<li>'. $p['name'];
                if ($p['locked'] == 0) {
                    echo ' <a class="delete" href="'.url('related', array('delete', 'del_related' => $p['related_id'])) .'">'.t('remove').'</a>';
                }
                echo '</li>';
            }
            echo '</ul>';
        }
    ?>
</div>

</div>

<div id="colTwo">

    <div class="box">
        <?php
        //$appendix_list = $append_file->getList();
        $pictures = $product->getNewPictures();
        if (count($pictures) > 0) {
            foreach ($pictures as $appendix) {
                echo '<div class="appendix">
                        <img src="'.$appendix['system-square']['file_uri'].'" />'.$appendix['original']['name'].'
                                <a class="delete" href="'.url('filehandler/selectfile', array('delete' => $appendix['appended_file_id'])).'">Fjern</a>
                                <a class="moveup" href="'.url('filehandler/selectfile', array('moveup' => $appendix['appended_file_id'])).'">Moveup</a>
                                <a class="movedown" href="'.url('filehandler/selectfile', array('movedown' => $appendix['appended_file_id'])).'">Movedown</a>
                                </div>';
            }
        }
        ?>

        <form action="<?php e(url(null)); ?>" method="POST"  enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php e($product->get('id')); ?>" />
        <input type="hidden" name="detail_id" value="<?php e($product->get('detail_id')); ?>" />

        <?php
        $filehandler_html = new Ilib_Filehandler_HTML($filehandler);
        $filehandler_html->printFormUploadTag('pic_id', 'new_append_file', 'choose_file', array('include_submit_button_name' => 'append_file_submit', 'filemanager' => true));
        //$filehandler_html->printFormUploadTag('pic_id', 'new_pic', 'choose_file', array('image_size' => 'small'));'
        ?>
        </form>
    </div>

    <?php if ($kernel->user->hasModuleAccess('shop')): ?>
        <?php $module_shop = $kernel->useModule('shop'); ?>
        <div id="categories" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'categories') echo ' fade'; ?>">
            <h2><?php e(t('Categories')); ?></h2>
            <?php
            $gateway = new Intraface_modules_shop_Shop_Gateway();
            $shops = $gateway->findAll();
            $db = MDB2::factory(DB_DSN);

            ?>
            <?php foreach ($shops as $shop): ?>
                <?php $category_type = new Intraface_Category_Type('shop', $shop->getId()); ?>
                <h3><?php e($shop->getName()); ?></h3>
                <ul class="options">
                    <li><a href="<?php e(url('shop/'. $shop->getId() . '/categories/', array('product_id' => $product->getId()))); ?>"><?php e(t('Add product to categories')); ?></a></li>
                </ul>
                <?php
                $category = new Intraface_Category($kernel, $db, $category_type);
                $appender = $category->getAppender($product->getId());
                ?>
                <ul>
                    <?php foreach ($appender->getCategories() AS $category): ?>
                        <li><?php e($category['name']); ?> <a href="<?php e(url(null, array('shop_id' => $shop->getId(), 'remove_appended_category' => $category['id']))); ?>" class="delete" id="remove_category_<?php e($category['id']); ?>"><?php e(t('Remove', 'common')); ?></a></li>
                    <?php endforeach; ?>
                </ul>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <div id="keywords" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'keywords') echo ' fade'; ?>">
      <h2><?php e(t('Keywords')); ?></h2>
    <?php if ($product->get('locked') == 0) { $shared_keyword = $kernel->useShared('keyword'); ?>
    <ul class="button"><li><a href="<?php e(url('keyword/connect')); ?>"><?php e(__('Add keywords')); ?></a></li></ul>
    <?php } ?>
    <?php
        $keyword = $product->getKeywordAppender();
        $keywords = $keyword->getConnectedKeywords();
        if (is_array($keywords) AND count($keywords) > 0) { ?>
            <ul>
            <?php foreach ($keywords as $k) { ?>
                <li><?php e($k['keyword']); ?></li>
            <?php } ?>
            </ul>
        <?php }
    ?>
  </div>




    <?php
    if ($kernel->user->hasModuleAccess("stock") AND $product->get('stock') AND !$product->get('has_variation')) {

        if (isset($_GET['adaptation']) && $_GET['adaptation'] == 'true') {
            $product->getStock()->adaptation();
        }
        ?>
        <div id="stock" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'stock') echo ' fade'; ?>">
            <h2><?php e(t('stock')); ?></h2>

            <table>
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

            <ul class="options">
                <li><a href="<?php e(url('stock')); ?>">Regulering</a></li>
                <li><a href="<?php e(url(null, array('adaptation' => true))); ?>" class="confirm">Afstem</a></li>
            </ul>

            <p>Sidst afstemt: <?php e($product->getStock()->get('dk_adaptation_date_time')); ?></p>

            <?php
            if ($kernel->user->hasModuleAccess('procurement')) {
                $kernel->useModule('procurement');

                $procurement = new Procurement($kernel);
                $latest = $procurement->getLatest($product->get('id'), $product->getStock()->get("actual_stock"));

                if (count($latest) > 0) {
                    ?>
                    <h3><?php e(t('latest purchases')); ?></h3>

                    <table>
                        <thead>
                            <tr>
                                <th><?php e(t('date')); ?></th>
                                <th class="amount"><?php e(t('price')); ?></th>
                                <th class="amount"><?php e(t('quantity')); ?></th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $is_under_actual = true;
                        for ($i = 0, $max = count($latest); $i < $max; $i++) {
                            ?>
                            <tr>
                                <td><?php e($latest[$i]['dk_invoice_date']); ?></td>
                                <td class="amount"><?php e(number_format($latest[$i]['calculated_unit_price'], 2, ",", ".")); ?></td>
                                <td class="amount"><?php e($latest[$i]['quantity']); ?></td>
                                <td>
                                    <?php
                                    if (isset($latest[$i]['sum_quantity']) && $latest[$i]['sum_quantity'] >= $product->getStock()->get("actual_stock") && $is_under_actual) {
                                        print("<");
                                        $is_under_actual = false;
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                    <?php
                }
            }
            ?>

        </div>
        <?php
    }
    ?>
</div>
