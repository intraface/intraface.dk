<?php
$quantity = $context->quantity;
$multiple = $context->multiple;
?>

<h1><?php e(t('select product')); ?></h1>

<?php if ($context->getProduct()->isFilledIn() == 0): ?>
    <p><?php e(t('no products to select.')); ?> <a href="select_product.php?add_new=true&amp;set_quantity=<?php e($quantity); ?>"><?php e(t('create product')); ?></a>.</p>
<?php else: ?>

    <ul class="options">
        <li><a href="select_product.php?add_new=true&amp;set_quantity=<?php e($quantity); ?>">Opret produkt</a></li>
    </ul>

    <form action="<?php e(url()); ?>" method="get">
        <fieldset>
            <legend><?php e(t('search', 'common')); ?></legend>
            <label><?php e(t('search for')); ?>
            <input type="text" value="<?php e($context->getProduct()->getDBQuery()->getFilter("search")); ?>" name="search" id="search" />
        </label>
        <label>
            <?php e(t('show with keywords')); ?>
            <select name="keyword_id" id="keyword_id">
                <option value=""><?php e(t('none', 'common')); ?></option>
                <?php foreach ($context->getKeywords()->getUsedKeywords() AS $k) { ?>
                <option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $context->getProduct()->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
                <?php } ?>
            </select>
        </label>
        <span><input type="submit" value="<?php e(t('search', 'common')); ?>" class="search" /><input type="hidden" name="set_quantity" value="<?php e($quantity); ?>" /></span>
        </fieldset>
        <br style="clear: both;" />
    </form>

    <?php
    echo $context->getProduct()->getDBQuery()->display('character');
    ?>
    <form action="<?php e(url()); ?>?set_quantity=<?php e($quantity); ?>&amp;use_stored=true" method="post">
        <table summary="Produkter" class="stripe">
            <caption><?php e(t('products')); ?></caption>
            <thead>
                <tr>
                    <th><?php if ($multiple && $quantity): e(t('Quantity')); else: echo e(t('Choose')); endif; ?></th>
                    <th><?php e(t('Product number')); ?></th>
                    <th><?php e(t('Name')); ?></th>
                    <th><?php e(t('Unit type')); ?></th>
                    <?php if ($context->getKernel()->user->hasModuleAccess('stock')): ?>
                    <th><?php e(t('Stock')); ?></th>
                    <?php endif; ?>
                    <th><?php e(t('Vat')); ?></th>
                    <th><?php e(t('Price')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($context->getProducts() AS $p): ?>
                <tr>
                    <td>
                        <?php if ($p['has_variation']): ?>
                            <a href="select_product.php?select_variation=<?php e($p['id']);?>&amp;set_quantity=<?php e($quantity); ?>" /><?php echo '<img class="variation" src="/images/icons/silk/table_multiple.png" title="'.t("See the product's variations").'"/> '; ?></a>
                        <?php elseif ($multiple && $quantity): ?>
                            <input id="<?php e($p['id']); ?>" type="text" name="selected[<?php e($p['id']); ?>]" value="<?php if (isset($selected_products[$p['id']])): e($selected_products[$p['id']]); else: e('0'); endif; ?>" size="2" />
                        <?php elseif ($multiple && !$quantity): ?>
                            <input id="<?php e($p['id']); ?>" type="checkbox" name="selected[<?php e($p['id']); ?>]" value="1" <?php if (array_key_exists($p['id'], $selected_products)) echo ' checked="checked"'; ?> />
                        <?php elseif (!$multiple): ?>
                            <input id="<?php e($p['id']); ?>" type="radio" name="selected" value="<?php e($p['id']); ?>" <?php if (array_key_exists($p['id'], $selected_products)) echo ' checked="checked"'; ?> />
                        <?php endif; ?>
                    </td>
                    <td><?php e($p['number']); ?></td>
                    <td><?php e($p['name']); ?></td>
                    <td><?php if (!empty($p['unit']['combined'])) e(t($p['unit']['combined'])); ?></td>
                    <?php if ($context->getKernel()->user->hasModuleAccess('stock')): ?>
                        <td>
                            <?php if ($p['stock'] == 0): e("-"); elseif ($p['has_variation']): e('...'); elseif (isset($p['stock_status']['for_sale'])): e($p['stock_status']['for_sale']); else: echo 0; endif; ?></td>
                    <?php endif; ?>
                    <td><?php if ($p['vat'] == 1) e('yes'); else e('no'); ?></td>
                  <td class="amount"><?php e(number_format($p['price'], 2, ",", ".")); ?></td>
                </tr>
                <?php  endforeach; ?>
            </tbody>
        </table>
      <p>
        <?php if (!$multiple && $quantity): ?>
            <?php e(t('quantity')); ?>: <input type="text" name="quantity" value="1" />
        <?php endif; ?>
        <?php if ($multiple): ?>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" />
        <?php endif; ?>
        <input type="submit" name="submit_close" value="<?php e(t('save and close', 'common')); ?>" /></p>

      <?php echo $context->getProduct()->getDBQuery()->display('paging'); ?>
    </form>
<?php endif; ?>