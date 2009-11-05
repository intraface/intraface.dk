<?php
$related_product_ids = $context->getRelatedProductIds();
?>

<h1><?php e(t('Add related products')); ?></h1>
<p>... <?php e(t('to', 'common')); ?> <?php e($context->getProduct()->get('name')); ?></p>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close', 'common')); ?></a></li>
</ul>

<form action="<?php e(url()); ?>" method="get">
    <fieldset>
        <legend><?php e(t('search', 'common')); ?></legend>
        <!--
        <label>Filter
        <select name="filter" id="filter">
            <option>Ingen</option>
            <option value="notpublished" <?php if (!empty($_GET['filter']) AND $_GET['filter'] == 'notpublished') echo ' selected="selected"'; ?>>Ikke udgivet</option>
            <option value="webshop"<?php if (!empty($_GET['filter']) AND $_GET['filter'] == 'webshop') echo ' selected="selected"'; ?>>Webshop</option>
            <option value="stock"<?php if (!empty($_GET['filter']) AND $_GET['filter'] == 'stock') echo ' selected="selected"'; ?>>Lager</option>
        </select>
        </label>
        -->
        <label><?php e(t('search for')); ?>
        <input type="text" value="<?php e($context->getProduct()->getDBQuery()->getFilter("search")); ?>" name="search" id="search" />
    </label>
    <label>
        Vis med nøgleord
        <select name="keyword_id" id="keyword_id">
            <option value=""><?php e(t('none', 'common')); ?></option>
            <?php foreach ($context->getKeywords()->getUsedKeywords() AS $k) { ?>
            <option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $context->getProduct()->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
            <?php } ?>
        </select>
    </label>
    <span>
        <input type="submit" value="<?php e(t('Go', 'common')); ?>" class="search" />
        <input type="hidden" value="<?php e($context->getProduct()->get('id')); ?>" name="id" />
    </span>
    </fieldset>
    <br style="clear: both;" />
</form>

<?php
echo $context->getProduct()->getDBQuery()->display('character');
?>
<form action="<?php e(url()); ?>" method="post">
    <input type="hidden" name="id" value="<?php e($context->getProduct()->get('id')); ?>" id="product_id" />
    <table summary="Produkter" class="stripe">
        <caption><?php e(t('products')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('choose', 'common')); ?></th>
                <th><?php e(t('product number')); ?></th>
                <th><?php e(t('name')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($context->getProducts() as $p) { ?>
            <tr>
                <td>
                    <input type="hidden" name="product[<?php e($p['id']); ?>]" value="<?php e($p['id']); ?>" />
                    <input class="input-relate" id="<?php e($p['id']); ?>" type="checkbox" name="relate[<?php e($p['id']); ?>]" value="relate" <?php if (array_search($p['id'], $related_product_ids) !== false) echo ' checked="checked"'; ?> />
                </td>
                <td><?php e($p['number']); ?></td>
                <td><?php e($p['name']); ?></td>
            </tr>
            <?php } // end foreach ?>
        </tbody>
    </table>
      <p>
          <input type="submit" value="<?php e(t('Save', 'common')); ?>" />
          <input type="submit" value="<?php e(t('Save and close', 'common')); ?>" name="close" />
      </p>

  <?php echo $context->getProduct()->getDBQuery()->display('paging'); ?>
    </form>