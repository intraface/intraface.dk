<h1><?php e(t('Stock')); ?></h1>


<?php if (count($context->getStock()) > 0) { ?>

<?php
echo '<div style="text-align: center; margin: 1em">- ';
foreach ($stock->getCharacters() AS $c) {
    echo '<a href="?c='.$c.'">'.strtolower($c).'</a> - ';
}
echo '</div>';


?>

<?php if (count($$context->getStock()) > 100) { echo '<p>Der vises kun 100 poster ad gangen. Lav nogle søgekriterier.</p>'; } ?>

<form action="<?e(url()); ?>" method="post">
    <table summary="Produkter">
        <thead>
            <tr>
                <th><?php e(t('name')); ?></th>
                <th><?php e(t('quantity')); ?></th>
                <th><?php e(t('reservered on invoices')); ?></th>
                <th><?php e(t('reserved on shop')); ?></th>
                <th><?php e(t('on stock')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($$context->getStock() AS $product) { ?>
            <tr>
                <td><?php e($product['name']); ?></td>
                <td>
              <input type="text" name="quantity[]" value="<?php e($product['quantity']); ?>" />
              <input type="hidden" name="id[]" value="<?php e($product['id']); ?>" />
          </td>
                <td><?php e($product['invotice_reserved']); ?></td>
                <td><?php e($product['webshop_reserved']); ?></td>
                <td><?php e($product['actual_stock']); ?></td>
            </tr>
            <?php } // end foreach ?>
        </tbody>
    </table>
  <div>
      <input type="submit" name="submit" value="<?php e(t('update stock')); ?>" class="save" />
  </div>
</form>
<?php
}
else {
?>
    <p><?php e(t('no products has been created on stock')); ?></p>
<?php
}
