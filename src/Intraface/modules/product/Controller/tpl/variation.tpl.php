<div id="colOne">

    <div class="box">
        <h2><?php e(t('Product')); ?> #<?php e($product->get('number'));  ?> <?php e($product->get('name')); ?></h2>
        <h2><?php e(t('Variation')); ?> #<?php e($variation->getNumber());  ?> <?php e($variation->getName()); ?></h2>
        <ul class="options">
            <li><a href="product.php?id=<?php e($product->get('id')); ?>"><?php e(__('close', 'common')); ?></a></li>
        </ul>
    </div>

    <table>
        <tr>
            <td><?php e(t('Price')); ?></td>
            <td><?php e($variation->getDetail()->getPrice($product)->getAsLocal('da_dk', 2)); ?> <?php e(t('excl. vat')); ?></td>
        </tr>
        <tr>
            <td><?php e(t('Weight')); ?></td>
            <td><?php e($product->get('weight') + $variation->getDetail()->getWeightDifference()); ?> gram</td>
        </tr>
    </table>

    <?php
    if ($kernel->user->hasModuleAccess('invoice')) {
        $debtor_module = $kernel->useModule('debtor');
        $invoice = new Debtor($kernel, 'invoice');
        if ($invoice->any('product', $product->get('id'), $variation->getId())) {
            ?>
            <ul class="options">
                <li><a href="<?php e($debtor_module->getPath().'invoice/list?type=invoice&status=-1&product_id='.$product->get('id').'&product_variation_id='.$variation->getId()); ?>"><?php e(t('invoices with this product')); ?></a></li>
            </ul>
            <?php
        }
    }
    ?>
</div>

<div id="colTwo">

    <?php /*
    // Can be implemented on request
    <div class="box">
        <?php
        //$appendix_list = $append_file->getList();
        $product->getPictures();
        if (count($product->get('pictures')) > 0) {
            foreach ($product->get('pictures') AS $appendix) {
                echo '<div class="appendix"><img src="'.$appendix['system-square']['file_uri'].'" />'.$appendix['original']['name'].' <a class="delete" href="product.php?id='.$product->get('id').'&amp;delete_appended_file_id='.$appendix['appended_file_id'].'">Slet</a></div>';
            }
        }
        ?>


        <form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="POST"  enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php e($product->get('id')); ?>" />
        <input type="hidden" name="detail_id" value="<?php e($product->get('detail_id')); ?>" />

        <?php
        $filehandler = new Filehandler($kernel);
        $filehandler_html = new FileHandlerHTML($filehandler);
        $filehandler_html->printFormUploadTag('pic_id', 'new_append_file', 'choose_file', array('include_submit_button_name' => 'append_file_submit', 'filemanager' => true));

        //$filehandler_html->printFormUploadTag('pic_id', 'new_pic', 'choose_file', array('image_size' => 'small'));'
        ?>
        </form>
    </div>
    */ ?>

    <?php
    if ($kernel->user->hasModuleAccess("stock") AND $product->get('stock')) {

        $stock = $variation->getStock($product);
        if (isset($_GET['adaptation']) && $_GET['adaptation'] == 'true') {
            $stock->adaptation();
        }
        ?>
        <div id="stock" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'stock') echo ' fade'; ?>">
            <h2><?php e(t('stock')); ?></h2>

            <table>
                <tr>
                    <td><?php e(t('stock status')); ?></td>
                    <td><?php e($stock->get("actual_stock")); ?></td>
                </tr>
                <tr>
                    <td><?php e(t('ordered')); ?></td>
                    <td><?php e($stock->get("on_order")); ?></td>
                </tr>
                <tr>
                    <td><?php e(t('reserved')); ?></td>
                    <td><?php e($stock->get("reserved")); ?> (<?php e($stock->get("on_quotation")); ?>)</td>
                </tr>
            </table>
            <ul class="options">
                <li><a href="<?php e(url('regulate')); ?>"><?php e(t('Regulate')); ?></a></li>
                <li><a href="<?php e(url($product->get('id'), array('adaptation'=>'true'))); ?>" class="confirm">Afstem</a></li>
            </ul>

            <p>Sidst afstemt: <?php e($stock->get('dk_adaptation_date_time')); ?></p>

            <?php
            if ($kernel->user->hasModuleAccess('procurement')) {
                $kernel->useModule('procurement');

                $procurement = new Procurement($kernel);
                $latest = $procurement->getLatest($product->get('id'), $stock->get("actual_stock"));

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
                                    if (isset($latest[$i]['sum_quantity']) && $latest[$i]['sum_quantity'] >= $stock->get("actual_stock") && $is_under_actual) {
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