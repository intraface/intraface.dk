<?php

die("Denne fil bliver nok ikke nødvendig alligevel!");


require('../../include_first.php');

$module = $kernel->module("newstock");

if (empty($_GET['id']) OR !is_numeric($_GET['id'])) {
    trigger_error("stock_history kræver et id", ERROR);
  exit;
}

// set up product
$oProduct = new Product($kernel, $_GET['id']);

$page = new Intraface_Page($kernel);
$page->start(t('stock history'));
?>

<div id="colOne">

    <fieldset>
        <h2><?php e(t('stock history')); ?></h2>
        <?php e(t('product')); ?> #<?php echo htmlentities($oProduct->get('number'));  ?> <?php e($oProduct->get('name')); ?>
        <ul class="button">
        <li><a href="product.php?id=<?php echo $oProduct->get('id'); ?>"><?php e(t('close')); ?></a></li>
      </ul>
        <div><?php e($product['description']); ?></div>
    </fieldset>


    <table class="stripe">
        <thead>
            <tr>
                <th><?php e(t('date')); ?></th>
                <th><?php e(t('change')); ?></th>
                <th><?php e(t('quantity')); ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tbody>
            <?php


            for($i = 0, $max = count($x); $i < $max; $i++) {
                ?>
                <tr>
                    <td><?php print($procurements[$i]["number"]); ?></td>
                    <td><a href="view.php?id=<?php print($procurements[$i]["id"]); ?>"><?php print($procurements[$i]["description"]); ?></a></td>
                    <td><?php print($procurements[$i]["status"]); ?></td>
                    <td><?php print($procurements[$i]["paid"]); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

</div>



        <div id="stock" class="box">
            <h2><?php e(t('status')); ?></h2>

            <table>
                <tr>
                    <td><?php e(t('on stock')); ?></td>
                    <td><?php print($oProduct->newstock->get("actual_stock")); ?></td>
                </tr>
                <tr>
                    <td><?php e(t('ordered')); ?></td>
                    <td><?php print($oProduct->newstock->get("on_order")); ?></td>
                </tr>
                <tr>
                    <td><?php e(t('reserved')); ?></td>
                    <td><?php print($oProduct->newstock->get("reserved")); ?> (<?php print($oProduct->newstock->get("on_quotation")); ?>)</td>
                </tr>
            </table>

        </div>


</div>
<?php
$page->end();
?>
