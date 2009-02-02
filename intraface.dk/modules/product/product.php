<?php
require '../../include_first.php';

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

$shared_filehandler = $kernel->useShared('filehandler');
$shared_filehandler->includeFile('AppendFile.php');

$filehandler = new FileHandler($kernel);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product = new Product($kernel, $_POST['id']);

    if (isset($_POST['append_file_submit'])) {

        $append_file = new AppendFile($kernel, 'product', $product->get('id'));

        if (isset($_FILES['new_append_file'])) {
            $filehandler = new FileHandler($kernel);

            $filehandler->createUpload();
            $filehandler->upload->setSetting('max_file_size', 5000000);
            
            /*
             * @todo: It is not enough validation if we have shop to make it public. Should probably be possible to set on the image if it should be public. 
             */
            if ($kernel->user->hasModuleAccess('shop')) { // if shown i webshop $product->get('do_show') == 1
                $filehandler->upload->setSetting('file_accessibility', 'public');
            }
            if ($id = $filehandler->upload->upload('new_append_file')) {
                $append_file->addFile(new FileHandler($kernel, $id));
            }
        }
        if (!$filehandler->error->isError()) {
            header('Location: product.php?id='.$product->get('id'));
            exit;
        }

    }

    if (!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
        $redirect = Intraface_Redirect::factory($kernel, 'go');
        $module_filemanager = $kernel->useModule('filemanager');
        $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php?images=1', $module->getPath().'product.php?id='.$product->get('id'));
        $redirect->setIdentifier('product');
        $redirect->askParameter('file_handler_id', 'multiple');

        header('Location: '.$url);
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // delete
    if (!empty($_GET['delete'])) {
        $product = new Product($kernel, $_GET['delete']);
        if ($id = $product->delete()) {
            header('Location: index.php?use_stored=true');
            exit;
        }
    } elseif (!empty($_GET['copy']) AND is_numeric($_GET['copy'])) {
        $product = new Product($kernel, $_GET['copy']);
        if ($id = $product->copy()) {
            header('Location: product.php?id='.$id);
            exit;
        }
    } elseif (isset($_GET['delete_appended_file_id'])) {
        $product = new Product($kernel, $_GET['id']);
        $append_file = new AppendFile($kernel, 'product', $product->get('id'));
        $append_file->delete((int)$_GET['delete_appended_file_id']);
        header('Location: product.php?id='.$product->get('id'));
        exit;

    } elseif (isset($_GET['moveup'])) {
        $product = new Product($kernel, $_GET['id']);
        $append_file = new AppendFile($kernel, 'product', $product->get('id'));
        $file = $append_file->findById(intval($_GET['moveup']));
        try {
            $file->moveUp();
        } catch (Exception $e) {
        }

        header('Location: product.php?id='.$product->get('id'));
        exit;
    } elseif (isset($_GET['movedown'])) {
        $product = new Product($kernel, $_GET['id']);
        $append_file = new AppendFile($kernel, 'product', $product->get('id'));
        $file = $append_file->findById(intval($_GET['movedown']));
        try {
            $file->moveDown();
        } catch (Exception $e) {
        }
        header('Location: product.php?id='.$product->get('id'));
        exit;
    } elseif (!empty($_GET['del_related']) AND is_numeric($_GET['del_related'])) {
        $product = new Product($kernel, $_GET['id']);
        $product->deleteRelatedProduct($_GET['del_related']);
        header('Location: product.php?id='.$product->get('id'));
        exit;
    } elseif (isset($_GET['append_category']) && $kernel->user->hasModuleAccess('shop')) {
        $product = new Product($kernel, $_GET['id']);
        $module_shop = $kernel->useModule('shop');
        $redirect = Intraface_Redirect::factory($kernel, 'go');
        $url = $redirect->setDestination($module_shop->getPath().'shop/'.$_GET['shop_id'].'/categories?product_id='.$product->getId(), $module->getPath().'product.php?id='.$product->getId());
        header('location: '.$url);
        exit;
    } elseif (isset($_GET['remove_appended_category']) && $kernel->user->hasModuleAccess('shop')) {
        $product = new Product($kernel, $_GET['id']);
        $category = new Intraface_Category($kernel, MDB2::factory(DB_DSN), new Intraface_Category_Type('shop', $_GET['shop_id']), $_GET['remove_appended_category']);
        $appender = $category->getAppender($product->getId());
        $appender->delete($category);
    } elseif (!empty($_GET['id'])) {
        $product = new Product($kernel, $_GET['id']);
        $filehandler = new FileHandler($kernel);

        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($kernel, 'return');
            if ($redirect->get('identifier') == 'product') {
                $append_file = new AppendFile($kernel, 'product', $product->get('id'));
                $array_files = $redirect->getParameter('file_handler_id');
                if (is_array($array_files)) {
                    foreach ($array_files AS $file_id) {
                        $append_file->addFile(new FileHandler($kernel, $file_id));
                    }
                }

            }
        }

    } else {
        trigger_error('Ulovligt', E_USER_ERROR);
    }
}

$page = new Intraface_Page($kernel);
$page->start(t('product') . ': ' . $product->get('name'));
?>

<div id="colOne">

<div class="box">
    <h2>#<?php e($product->get('number'));  ?> <?php e($product->get('name')); ?></h2>
    <ul class="options">
        <?php if ($product->get('locked') != 1) { ?>
        <li><a href="product_edit.php?id=<?php e($product->get('id')); ?>"><?php e($translation->get('edit', 'common')); ?></a></li>

        <li><a class="confirm" href="<?php e($_SERVER['PHP_SELF']); ?>?delete=<?php e($product->get('id')); ?>"><?php e($translation->get('delete', 'common')); ?></a></li>
        <?php } ?>
        <li><a href="product.php?copy=<?php e($product->get('id')); ?>"><?php e(t('copy', 'common')); ?></a></li>
        <li><a href="index.php?from_product_id=<?php e($product->get('id')); ?>&amp;use_stored=true"><?php e(t('close', 'common')); ?></a></li>
    </ul>
    <div><?php autohtml($product->get('description')); ?></div>
</div>

<table>
    <?php if (!$product->hasVariation()): ?>
        <tr>
            <td><?php e(t('price')); ?></td>
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
                <td><?php e(t('weight')); ?></td>
                <td><?php e($product->get('weight')); ?> gram</td>
            </tr>
        <?php endif; ?>
    <?php endif; ?>
    <tr>
        <td><?php e(t('unit type')); ?></td>
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
        <td><?php e(t('show in webshop')); ?></td>
        <td>
            <?php
                $show_choises = array(0=>"Nej", 1=>"Ja");
                e($show_choises[$product->get('do_show')]);
            ?>
        </td>
    </tr>

    <!-- her bør være en tidsangivelse -->

    <?php endif; ?>

    <tr>
        <td><?php e(t('vat')); ?></td>
        <td>
            <?php
                $vat_choises = array(0=>"Nej", 1=>"Ja");
                e($vat_choises[$product->get('vat')]);
            ?>
        </td>
    </tr>
    <?php if ($kernel->intranet->hasModuleAccess('stock')): ?>
    <tr>
        <td><?php e(t('stock product')); ?></td><td>
            <?php
                $stock_choises = array(0=>"Nej", 1=>"Ja");
                e($stock_choises[$product->get('stock')]);
            ?>
        </td>
    </tr>
    <?php endif; ?>
    <?php
    if ($kernel->user->hasModuleAccess('accounting')):
        $mainAccounting = $kernel->useModule("accounting");
        ?>
        <tr>
            <td><?php e(t('state on')); ?></td><td>
            <?php
                $year = new Year($kernel);
                if ($year->get('id') == 0) {
                    echo t('year is not set in accounting');
                } else {
                    $account = Account::factory($year, $product->get('state_account_id'));
                    if ($account->get('name')) {
                        e($account->get('number') . ' ' . $account->get('name'));
                    } else {
                        echo t('not set');
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
            <li><a href="<?php e($debtor_module->getPath().'list.php?type=invoice&amp;status=-1&amp;product_id='.$product->get('id')); ?>"><?php e(t('invoices with this product')); ?></a></li>
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
            <li><a href="product_select_attribute_groups.php?id=<?php e($product->get('id')); ?>"><?php e(t('Select attributes for product')); ?></a></li>
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
                <li><a href="product_variations_edit.php?id=<?php e($product->get('id')); ?>"><?php e(t('Create variations for the product')); ?></a></li>
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
                            <td><a href="product_variation.php?id=<?php e($variation->getId()); ?>&amp;product_id=<?php e($product->getId()); ?>"><?php e(t('Details', 'common')); ?></a></td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <?php endif; ?>
            <ul class="options">
                <li><a href="product_variations_edit.php?id=<?php e($product->get('id')); ?>"><?php e(t('Edit variations for the product')); ?></a></li>
            </ul>

    <?php endif; ?>
<?php endif; ?>

<div id="related_products" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'related') echo ' fade'; ?>">
    <h2><?php e(t('related products')); ?></h2>
    <?php if ($product->get('locked') == 0) { ?>
        <ul class="button">
            <li><a href="related_product.php?id=<?php e($product->get('id')); ?>"><?php e(t('add products')); ?></a></li>
        </ul>
    <?php } ?>
    <?php
        $related = $product->getRelatedProducts();
        if (!empty($related) AND count($related) > 0) {
            foreach ($related AS $p) {
                echo '<li>'. $p['name'];
                if ($p['locked'] == 0) {
                    echo ' <a class="delete" href="product.php?id='.$product->get('id').'&amp;del_related='.$p['related_id'].'&amp;from=related#related">'.t('remove').'</a>';
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
        $product->getPictures();
        if (count($product->get('pictures')) > 0) {
            foreach ($product->get('pictures') AS $appendix) {
                echo '<div class="appendix">
                        <img src="'.$appendix['system-square']['file_uri'].'" />'.$appendix['original']['name'].'
                                <a class="delete" href="product.php?id='.$product->get('id').'&amp;delete_appended_file_id='.$appendix['appended_file_id'].'">Fjern</a>
                                <a class="moveup" href="product.php?id='.$product->get('id').'&amp;moveup='.$appendix['appended_file_id'].'">Moveup</a>
                                <a class="movedown" href="product.php?id='.$product->get('id').'&amp;movedown='.$appendix['appended_file_id'].'">Movedown</a>
                                </div>';
            }
        }
        ?>


        <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="POST"  enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php e($product->get('id')); ?>" />
        <input type="hidden" name="detail_id" value="<?php e($product->get('detail_id')); ?>" />

        <?php
        $filehandler_html = new FileHandlerHTML($filehandler);
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
            $db =  MDB2::factory(DB_DSN);

            ?>
            <?php foreach ($shops AS $shop): ?>
                <?php $category_type = new Intraface_Category_Type('shop', $shop->getId()); ?>
                <h3><?php e($shop->getName()); ?></h3>
                <ul class="options">
                    <li><a href="product.php?id=<?php e($product->getId()); ?>&amp;shop_id=<?php e($shop->getId()); ?>&amp;append_category=1"><?php e(t('Add product to categories')); ?></a></li>
                </ul>
                <?php
                $category = new Intraface_Category($kernel, $db, $category_type);
                $appender = $category->getAppender($product->getId());
                ?>
                <ul>
                    <?php foreach ($appender->getCategories() AS $category): ?>
                        <li><?php e($category['name']); ?> <a href="product.php?id=<?php e($product->getId()); ?>&amp;shop_id=<?php e($shop->getId()); ?>&amp;remove_appended_category=<?php e($category['id']); ?>" class="delete"><?php e(t('Remove', 'common')); ?></a></li>
                    <?php endforeach; ?>
                </ul>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <div id="keywords" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'keywords') echo ' fade'; ?>">
      <h2><?php e(t('keywords')); ?></h2>
    <?php if ($product->get('locked') == 0) { $shared_keyword = $kernel->useShared('keyword'); ?>
    <ul class="button"><li><a href="<?php e($shared_keyword->getPath()); ?>connect.php?product_id=<?php e($product->get('id')); ?>">Tilknyt nøgleord</a></li></ul>
    <?php } ?>
    <?php
        $keyword = $product->getKeywordAppender();
        $keywords = $keyword->getConnectedKeywords();
        if (is_array($keywords) AND count($keywords) > 0) { ?>
            <ul>
            <?php foreach ($keywords AS $k) { ?>
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
                <li><a href="stock_regulation.php?product_id=<?php e($product->get('id')); ?>">Regulering</a></li>
                <li><a href="product.php?id=<?php e($product->get('id')); ?>&amp;adaptation=true" class="confirm">Afstem</a></li>
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
<?php
$page->end();
?>