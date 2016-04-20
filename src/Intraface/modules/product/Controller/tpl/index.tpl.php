<h1><?php e(t('products')); ?></h1>

<ul class="options">
    <li><a class="new" href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create')); ?></a></li>
    <?php if (count($context->getProducts()) > 0) : ?>
    <li><a href="<?php e(url('batchedit', array('use_stored' => 'true'))); ?>"><?php e(t('Batch edit search')); ?></a></li>
    <li><a href="<?php e(url('batchprice', array('use_stored' => 'true'))); ?>"><?php e(t('Batch edit prices')); ?></a></li>
    <?php endif; ?>
    <?php if ($context->getKernel()->intranet->hasModuleAccess('shop')) : ?>
        <li><a href="<?php e(url('attributegroups')); ?>"><?php e(t('Edit attributes')); ?></a></li>
    <?php endif; ?>
</ul>

<?php if (!$context->getProduct()->isFilledIn()) : ?>
    <p><?php e(t('No products has been created.')); ?> <a href="<?php e(url(null, array('create'))); ?>"><?php e(t('create product')); ?></a>.</p>
<?php else : ?>

<form action="<?php e(url()); ?>" method="get" class="search-filter">
    <fieldset>
        <legend><?php e(t('Search')); ?></legend>
        <!--
        <label for="filter">Filter
            <select name="filter" id="filter">
                <option>Ingen</option>
                <option value="notpublished" <?php if (isset($_GET['filter']) && $_GET['filter'] == 'notpublished') {
                    echo ' selected="selected"';
} ?>>Ikke udgivet</option>
                <option value="webshop"<?php if (isset($_GET['filter']) && $_GET['filter'] == 'webshop') {
                    echo ' selected="selected"';
} ?>>Webshop</option>
                <option value="stock"<?php if (isset($_GET['filter']) && $_GET['filter'] == 'stock') {
                    echo ' selected="selected"';
} ?>>Lager</option>
            </select>
        </label>
        -->
        <label for="search"><?php e(t('Search for')); ?>
            <input name="search" id="search" type="text" value="<?php e($context->getProduct()->getDBQuery()->getFilter("search")); ?>" />
        </label>

        <label for="keyword_id"><?php e(t('Show with keywords')); ?>
            <select name="keyword_id" id="keyword_id">
                <option value=""><?php e(t('None')); ?></option>
                <?php foreach ($context->getKeywords()->getUsedKeywords() as $k) { ?>
                <option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $context->getProduct()->getDBQuery()->getKeyword(0)) {
                    echo ' selected="selected"';
}; ?>><?php e($k['keyword']); ?></option>
                <?php } ?>
            </select>
        </label>
        <span>
            <input type="submit" value="<?php e(t('Go')); ?>" />    <input type="reset" value="<?php e(t('reset')); ?>" />
        </span>
    </fieldset>
</form>


<form action="<?php e(url()); ?>" method="post">
<input type="hidden" value="put" name="_method" />
<?php if (!empty($deleted)) : ?>
        <p class="message"><?php e(t('products has been deleted')); ?>. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="<?php e(t('Cancel')); ?>" /></p>
<?php endif; ?>

<?php echo $context->getGateway()->getDBQuery()->display('character'); ?>

    <?php if (count($context->getProducts()) == 0) : ?>
        <p><?php e(t('no products in search')); ?>.</p>
    <?php else : ?>

    <table summary="<?php e(t('products')); ?>" id="product_table" class="stripe">
        <caption><?php e(t('products')); ?> (<?php e(t('prices excl. vat')); ?>)</caption>
        <thead>
            <tr>
                <th></th>
                <th>#</th>
                <th><?php e(t('name')); ?></th>
                <th><?php e(t('unit type')); ?></th>
                <?php if ($context->getKernel()->user->hasModuleAccess("webshop")) { ?>
                    <th><?php e(t('published')); ?></th>
                <?php } ?>
                <?php if ($context->getKernel()->user->hasModuleAccess("stock")) { ?>
                    <th><?php e(t('stock status')); ?></th>
                <?php } ?>
                <th><?php e(t('vat')); ?></th>
                <th><?php e(t('price')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($context->getProducts() as $p) { ?>
            <tr>
                <td>
                    <input type="checkbox" id="product-<?php e($p['id']); ?>" value="<?php e($p['id']); ?>" name="selected[]" />
                </td>

                <td><?php e($p['number']); ?></td>
                <td>
                    <?php if ($p['has_variation']) : ?>
                        <img class="variation" src="<?php e(url('/images/icons/silk/table_multiple.png')); ?>" title="<?php e(t('The product has variations')); ?>"/>
                    <?php endif; ?>
                    <a href="<?php e(url($p['id'])); ?>"><?php e($p['name']); ?></a>
                </td>
                <td><?php if ($p['unit']['combined'] != '') {
                    e(t($p['unit']['combined']));
} ?></td>
                <?php if ($context->getKernel()->user->hasModuleAccess("webshop")) : ?>
                    <td><?php if ($p['do_show'] == 1) {
                        e(t('yes'));
} else {
    e(t('no'));
} ?></td>
                <?php endif; ?>
                <?php if ($context->getKernel()->user->hasModuleAccess('stock')) { ?>
                    <td>
                        <?php
                        if ($p['stock'] == 0) {
                            e("-");
                        } elseif ($p['has_variation']) {
                            e('...');
                        } else {
                            if (!empty($p['stock_status']['for_sale'])) {
                                e($p['stock_status']['for_sale']);
                            }
                        }
                        ?>
                    </td>
                <?php } ?>
                <td><?php if ($p['vat'] == 1) {
                    e(t('yes'));
} else {
    e(t('no'));
} ?></td>
                <td class="amount"><?php echo number_format($p['price'], 2, ",", "."); ?></td>

                <td class="options">
            <?php if ($p['locked'] == 0) { ?>
                  <!-- nedenst�ende b�r s�ttes p� produktsiden - muligheden skal ikke findes her
                    <a href="index.php?lock=<?php e($p['id']); ?>&amp;use_stored=true"><?php e(t('lock')); ?></a>
                    -->
                    <a class="button edit" href="<?php e(url($p['id'], array('edit'))); ?>"><?php e(t('edit')); ?></a>
                    <!--<a class="button delete ajaxdelete" title="Dette sletter produktet" id="delete<?php e($p['id']); ?>" href="index.php?use_stored=true&amp;delete=<?php e($p['id']); ?>">Slet</a>-->
        <?php } else { ?>
          <a href="<?php e(url($p['id'], array('unlock' => true))); ?>"><?php e(t('unlock')); ?></a>
        <?php } ?>
       </td>
            </tr>
            <?php } // end foreach ?>
        </tbody>
    </table>
    <select name="action">
        <option value=""><?php e(t('choose...')); ?></option>
        <option value="delete"><?php e(t('delete selected')); ?></option>
    </select>

    <input type="submit" value="<?php e(t('go')); ?>" />
</form>

    <?php endif; ?>

<?php echo $context->getGateway()->getDBQuery()->display('paging'); ?>

<?php endif; ?>
