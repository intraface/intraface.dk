<?php
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

$shared_filehandler = $kernel->useShared('filehandler');
$shared_filehandler->includeFile('AppendFile.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product = new Product($kernel, $_POST['id']);
    
    if(isset($_POST['append_file_submit'])) {

        $filehandler = new FileHandler($kernel);
        $append_file = new AppendFile($kernel, 'product', $product->get('id'));

        if(isset($_FILES['new_append_file'])) {
            $filehandler = new FileHandler($kernel);

            $filehandler->createUpload();
            if ($product->get('do_show') == 1) { // if shown i webshop
                $filehandler->upload->setSetting('file_accessibility', 'public');
            }
            if($id = $filehandler->upload->upload('new_append_file')) {
                $append_file->addFile(new FileHandler($kernel, $id));
            }
        }


    }

    if(!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
        $redirect = Redirect::factory($kernel, 'go');
        $module_filemanager = $kernel->useModule('filemanager');
        $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php?images=1', $module->getPath().'product.php?id='.$product->get('id'));
        $redirect->setIdentifier('product');
        $redirect->askParameter('file_handler_id', 'multiple');

        header('Location: '.$url);
        exit;
    }
    
    header('Location: product.php?id='.$product->get('id'));
    exit;
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // delete
    if (!empty($_GET['delete'])) {
        $product = new Product($kernel, $_GET['delete']);
        if ($id = $product->delete()) {
            header('Location: index.php?use_stored=true');
            exit;
        }
    }

    // copy product
    elseif (!empty($_GET['copy']) AND is_numeric($_GET['copy'])) {
        $product = new Product($kernel, $_GET['copy']);
        if ($id = $product->copy()) {
            header('Location: product.php?id='.$id);
            exit;
        }
    }

    // this has to be moved to post
    elseif(isset($_GET['delete_appended_file_id'])) {
        $product = new Product($kernel, $_GET['id']);
        $append_file = new AppendFile($kernel, 'product', $product->get('id'));
        $append_file->delete((int)$_GET['delete_appended_file_id']);
        header('Location: product.php?id='.$product->get('id'));
        exit;

    }

    // Delete related product
    // has to be moved to post
    elseif (!empty($_GET['del_related']) AND is_numeric($_GET['del_related'])) {
        $product = new Product($kernel, $_GET['id']);
        $product->deleteRelatedProduct($_GET['del_related']);
        header('Location: product.php?id='.$product->get('id'));
        exit;
    }

    elseif (!empty($_GET['id'])) {
        $product = new Product($kernel, $_GET['id']);
        $filehandler = new FileHandler($kernel);

        if(isset($_GET['return_redirect_id'])) {
            $redirect = Redirect::factory($kernel, 'return');
            if($redirect->get('identifier') == 'product') {
                $append_file = new AppendFile($kernel, 'product', $product->get('id'));
                $array_files = $redirect->getParameter('file_handler_id');
                if(is_array($array_files)) {
                    foreach($array_files AS $file_id) {
                        $append_file->addFile(new FileHandler($kernel, $file_id));
                    }
                }
                
            }
        }

    }

    else {
        trigger_error('Ulovligt', E_USER_ERROR);
    }
}

$page = new Page($kernel);
$page->start(t('product') . ': ' . $product->get('name'));
?>

<div id="colOne">

<div class="box">
    <h2>#<?php e($product->get('number'));  ?> <?php e($product->get('name')); ?></h2>
    <ul class="options">
        <?php if ($product->get('locked') != 1) { ?>
        <li><a href="product_edit.php?id=<?php echo $product->get('id'); ?>"><?php e($translation->get('edit', 'common')); ?></a></li>

        <li><a class="confirm" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo intval($product->get('id')); ?>"><?php echo safeToHtml($translation->get('delete', 'common')); ?></a></li>
        <?php } ?>
        <li><a href="product.php?copy=<?php echo intval($product->get('id')); ?>"><?php echo $translation->get('copy', 'common'); ?></a></li>
        <li><a href="index.php?from_product_id=<?php echo intval($product->get('id')); ?>&amp;use_stored=true"><?php echo safeToHtml($translation->get('close', 'common')); ?></a></li>
    </ul>
    <div><?php echo autoop($product->get('description')); ?></div>
</div>

<table>
    <tr>
        <td><?php e(t('price')); ?></td>
        <td><?php echo safeToHtml(number_format($product->get('price'), 2, ",", ".")); ?> <?php e(t('excl. vat')); ?></td>
    </tr>
    <tr>
        <td><?php e(t('weight')); ?></td>
        <td><?php echo safeToHtml($product->get('weight')); ?> gram</td>
    </tr>
    <tr>
        <td><?php e(t('unit')); ?></td>
        <td>
            <?php
                // getting settings
                $unit_choises  = Product::getUnits();
                echo safeToHtml(t($unit_choises[$product->get('unit_id')]['combined']));
            ?>
        </td>
    </tr>

    <?php if ($kernel->user->hasModuleAccess("webshop")): ?>

    <tr>
        <td><?php e(t('show in webshop')); ?></td>
        <td>
            <?php
                $show_choises = array(0=>"Nej", 1=>"Ja");
                echo safeToHtml($show_choises[$product->get('do_show')]);
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
                echo safeToHtml($vat_choises[$product->get('vat')]);
            ?>
        </td>
    </tr>
    <?php if ($kernel->intranet->hasModuleAccess('stock')): ?>
    <tr>
        <td><?php e(t('stock product')); ?></td><td>
            <?php
                $stock_choises = array(0=>"Nej", 1=>"Ja");
                echo safeToHtml($stock_choises[$product->get('stock')]);
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
                    echo safeToHtml($account->get('number') . ' ' . $account->get('name'));
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
if($kernel->user->hasModuleAccess('invoice')) {
    $debtor_module = $kernel->useModule('debtor');
    $invoice = new Debtor($kernel, 'invoice');
    if($invoice->any('product', $product->get('id'))) {
        ?>
        <ul class="options">
            <li><a href="<?php print($debtor_module->getPath().'list.php?type=invoice&amp;status=-1&amp;product_id='.$product->get('id')); ?>"><?php e(t('invoices with this product')); ?></a></li>
        </ul>
        <?php
    }
}
?>


<div id="related_products" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'related') echo ' fade'; ?>">
    <h2><?php e(t('related products')); ?></h2>
    <?php if ($product->get('locked') == 0) { ?>
        <ul class="button"><li><a href="related_product.php?id=<?php echo $product->get('id'); ?>"><?php e(t('add products')); ?></a></li></ul>
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
        if(count($product->get('pictures')) > 0) {
            foreach($product->get('pictures') AS $appendix) {
                echo '<div class="appendix"><img src="'.$appendix['system-square']['file_uri'].'" />'.$appendix['original']['name'].' <a class="delete" href="product.php?id='.$product->get('id').'&amp;delete_appended_file_id='.$appendix['appended_file_id'].'">Slet</a></div>';
            }
        }
        ?>


        <form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="POST"  enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo intval($product->get('id')); ?>" />
        <input type="hidden" name="detail_id" value="<?php echo intval($product->get('detail_id')); ?>" />

        <?php
        $filehandler = new Filehandler($kernel);
        $filehandler_html = new FileHandlerHTML($filehandler);
        $filehandler_html->printFormUploadTag('pic_id', 'new_append_file', 'choose_file', array('include_submit_button_name' => 'append_file_submit', 'filemanager' => true));

        //$filehandler_html->printFormUploadTag('pic_id', 'new_pic', 'choose_file', array('image_size' => 'small'));'
        ?>
        </form>
    </div>


    <div id="keywords" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'keywords') echo ' fade'; ?>">
      <h2><?php e(t('keywords')); ?></h2>
    <?php if ($product->get('locked') == 0) { $shared_keyword = $kernel->useShared('keyword'); ?>
    <ul class="button"><li><a href="<?php echo $shared_keyword->getPath(); ?>connect.php?product_id=<?php echo $product->get('id'); ?>">Tilknyt nøgleord</a></li></ul>
    <?php } ?>
    <?php
        $keyword = $product->getKeywordAppender();
        $keywords = $keyword->getConnectedKeywords();
        if (is_array($keywords) AND count($keywords) > 0) {
            echo '<ul>';
            foreach ($keywords AS $k) {
                echo '<li>' . safeToHtml($k['keyword']) . '</li>';
            }
            echo '</ul>';
        }
    ?>
  </div>


    <?php
    /* HACK HACK HACK MED AT TJEKKE OM oProduct har objektet */
    if($kernel->user->hasModuleAccess("stock") AND is_object($product->stock)) {

        if(isset($_GET['adaptation']) && $_GET['adaptation'] == 'true') {
            $product->stock->adaptation();
        }
        ?>
        <div id="stock" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'stock') echo ' fade'; ?>">
            <h2><?php e(t('stock')); ?></h2>

            <table>
                <tr>
                    <td><?php e(t('stock status')); ?></td>
                    <td><?php print($product->stock->get("actual_stock")); ?></td>
                </tr>
                <tr>
                    <td><?php e(t('ordered')); ?></td>
                    <td><?php print($product->stock->get("on_order")); ?></td>
                </tr>
                <tr>
                    <td><?php e(t('reserved')); ?></td>
                    <td><?php print($product->stock->get("reserved")); ?> (<?php print($product->stock->get("on_quotation")); ?>)</td>
                </tr>
            </table>
            <!-- hvad bliver følgende brugt til -->
            <div id="stock_regulation" style="display: none ; position: absolute; border: 1px solid #666666; background-color: #CCCCCC; padding: 10px; width: 260px;">
                <?php e(t('requlate with quantity')); ?>: <input type="text" name="regulate_number" size="5" />
                <br /><?php e(t('description')); ?>: <input type="text" name="regulation_description" />
                <br /><input type="submit" name="regulate" value="<?php e(t('save')); ?>" /> <a href="javascript:;" onclick="document.getElementById('stock_regulation').style.display='none';return false"><?php e(t('hide')); ?></a>

            </div>

            <p><a href="stock_regulation.php?product_id=<?php print($product->get('id')); ?>">Regulering</a> <a href="product.php?id=<?php print($product->get('id')); ?>&amp;adaptation=true" class="confirm">Afstem</a></p>

            <p>Sidst afstemt: <?php echo safeToHtml($product->stock->get('dk_adaptation_date_time')); ?></p>

            <?php
            if($kernel->user->hasModuleAccess('procurement')) {
                $kernel->useModule('procurement');

                $procurement = new Procurement($kernel);
                $latest = $procurement->getLatest($product->get('id'), $product->stock->get("actual_stock"));

                if(count($latest) > 0) {
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
                        for($i = 0, $max = count($latest); $i < $max; $i++) {
                            ?>
                            <tr>
                                <td><?php echo safeToHtml($latest[$i]['dk_invoice_date']); ?></td>
                                <td class="amount"><?php print(number_format($latest[$i]['calculated_unit_price'], 2, ",", ".")); ?></td>
                                <td class="amount"><?php safeToHtml(print($latest[$i]['quantity'])); ?></td>
                                <td>
                                    <?php
                                    if(isset($latest[$i]['sum_quantity']) && $latest[$i]['sum_quantity'] >= $product->stock->get("actual_stock") && $is_under_actual) {
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