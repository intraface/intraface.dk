<?php
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

$gateway = $dependency->create('Intraface_modules_product_Gateway');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['action']) AND $_POST['action'] == 'delete') {
        $deleted = array();
        if (!empty($_POST['selected']) AND is_array($_POST['selected'])) {
            foreach ($_POST['selected'] as $key=>$id) {
                $product = $gateway->getById(intval($id));
                if ($product->delete()) {
                    $deleted[] = $id;
                }
            }
        }
    } elseif (!empty($_POST['undelete'])) {
        if (!empty($_POST['deleted']) AND is_string($_POST['deleted'])) {
            $undelete = unserialize(base64_decode($_POST['deleted']));
        } else {
            trigger_error('could not undelete', E_USER_ERROR);
        }
        if (!empty($undelete) AND is_array($undelete)) {
            foreach ($undelete as $key=>$id) {
                $product = $gateway->getById(intval($id));
                if (!$product->undelete()) {
                    // void
                }
            }
        }
    }
}

$product = $gateway->getById(0);
// $characters = $product->getCharacters();
$keywords = $product->getKeywordAppender();

// burde bruge query
if (isset($_GET["search"]) || isset($_GET["keyword_id"])) {
    if (isset($_GET["search"])) {
        $gateway->getDBQuery()->setFilter("search", $_GET["search"]);
    }

    if (isset($_GET["keyword_id"])) {
        $gateway->getDBQuery()->setKeyword($_GET["keyword_id"]);
    }
} else {
    $gateway->getDBQuery()->useCharacter();
}

$gateway->getDBQuery()->defineCharacter("character", "detail.name");
$gateway->getDBQuery()->usePaging("paging");
$gateway->getDBQuery()->storeResult("use_stored", "products", "toplevel");

$products = $gateway->getAllProducts();

$page = new Intraface_Page($kernel);
$page->start(t('products'));
?>
<h1><?php e(t('products')); ?></h1>

<ul class="options">
    <li><a class="new" href="product_edit.php"><?php e(t('create product')); ?></a></li>
    <?php if (count($products) > 0): ?>
    <li><a href="batch_edit.php?use_stored=true"><?php e(t('edit all products in search')); ?></a></li>
    <?php endif; ?>
    <?php if ($kernel->intranet->hasModuleAccess('shop')): ?>
        <li><a href="attribute_groups.php"><?php e(t('Edit attributes')); ?></a></li>
    <?php endif; ?>
</ul>

<?php if (!$product->isFilledIn()): ?>
    <p><?php e(t('no products has been created.')); ?> <a href="product_edit.php"><?php e(t('create product')); ?></a>.</p>
<?php else: ?>

<form action="index.php" method="get" class="search-filter">
    <fieldset>
        <legend><?php e(t('search', 'common')); ?></legend>
        <!--
        <label for="filter">Filter
            <select name="filter" id="filter">
                <option>Ingen</option>
                <option value="notpublished" <?php if (isset($_GET['filter']) && $_GET['filter'] == 'notpublished') echo ' selected="selected"'; ?>>Ikke udgivet</option>
                <option value="webshop"<?php if (isset($_GET['filter']) && $_GET['filter'] == 'webshop') echo ' selected="selected"'; ?>>Webshop</option>
                <option value="stock"<?php if (isset($_GET['filter']) && $_GET['filter'] == 'stock') echo ' selected="selected"'; ?>>Lager</option>
            </select>
        </label>
        -->
        <label for="search"><?php e(t('search for', 'common')); ?>
            <input name="search" id="search" type="text" value="<?php e($product->getDBQuery()->getFilter("search")); ?>" />
        </label>

        <label for="keyword_id"><?php e(t('show with keywords', 'common')); ?>
            <select name="keyword_id" id="keyword_id">
                <option value=""><?php e(t('none', 'common')); ?></option>
                <?php foreach ($keywords->getUsedKeywords() AS $k) { ?>
                <option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $product->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
                <?php } ?>
            </select>
        </label>
        <span>
            <input type="submit" value="<?php e(t('go', 'common')); ?>" />	<input type="reset" value="<?php e(t('reset', 'common')); ?>" />
        </span>
    </fieldset>
</form>


<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
<?php if (!empty($deleted)): ?>
        <p class="message"><?php e(t('products has been deleted')); ?>. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="<?php e(t('Cancel', 'common')); ?>" /></p>
<?php endif; ?>

<?php echo $gateway->getDBQuery()->display('character'); ?>

    <?php if (!is_array($products) OR count($products) == 0): ?>
        <p><?php e(t('no products in search')); ?>.</p>
    <?php else: ?>

    <table summary="Produkter" id="product_table" class="stripe">
        <caption><?php e(t('products')); ?></caption>
        <thead>
            <tr>
                <th></th>
                <th>#</th>
                <th><?php e(t('name')); ?></th>
                <th><?php e(t('unit type')); ?></th>
                <?php if ($kernel->user->hasModuleAccess("webshop")) { ?>
                    <th><?php e(t('published')); ?></th>
                <?php } ?>
                <?php if ($kernel->user->hasModuleAccess("stock")) { ?>
                    <th><?php e(t('stock status')); ?></th>
                <?php } ?>
                <th><?php e(t('vat')); ?></th>
                <th><?php e(t('price')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="8">
                    <?php e(t('prices excl. vat')); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php foreach ($products AS $p) { ?>
            <tr>
                <td>
                    <input type="checkbox" id="product-<?php e($p['id']); ?>" value="<?php e($p['id']); ?>" name="selected[]" />
                </td>

                <td><?php e($p['number']); ?></td>
                <td><?php if ($p['has_variation']) echo '<img class="variation" src="/images/icons/silk/table_multiple.png" title="'.t('The product has variations').'"/> '; ?><a href="product.php?id=<?php e($p['id']); ?>"><?php e($p['name']); ?></a></td>
                <td><?php e(t($p['unit']['combined'])); ?></td>
                 <?php if ($kernel->user->hasModuleAccess("webshop")) { ?>
              <td><?php if ($p['do_show'] == 1) e(t('yes', 'common')); else e(t('no', 'common')); ?></td>
                <?php } ?>
                <?php if ($kernel->user->hasModuleAccess("stock")) { ?>
                    <td>
                        <?php
                        if ($p['stock'] == 0) {
                            e("-");
                        } elseif ($p['has_variation']) {
                            e('...');
                        } else {
                            if (!empty($p['stock_status']['for_sale'])) e($p['stock_status']['for_sale']);
                        }
                        ?>
                    </td>
                <?php } ?>
                <td><?php if ($p['vat'] == 1) e(t('yes', 'common')); else e(t('no', 'common')); ?></td>
                <td class="amount"><?php echo number_format($p['price'], 2, ",", "."); ?></td>

                <td class="options">
          <?php if ($p['locked'] == 0) { ?>
                  <!-- nedenstående bør sættes på produktsiden - muligheden skal ikke findes her
                    <a href="index.php?lock=<?php e($p['id']); ?>&amp;use_stored=true"><?php e(t('lock', 'common')); ?></a>
                    -->
                    <a class="button edit" href="product_edit.php?id=<?php e($p['id']); ?>"><?php e(t('edit', 'common')); ?></a>
                    <!--<a class="button delete ajaxdelete" title="Dette sletter produktet" id="delete<?php e($p['id']); ?>" href="index.php?use_stored=true&amp;delete=<?php e($p['id']); ?>">Slet</a>--></td>
       <?php } else { ?>
          <a href="index.php?unlock=<?php e($p['id']); ?>&amp;use_stored=true"><?php e(t('unlock', 'common')); ?></a>
       <?php } ?>
            </tr>
            <?php } // end foreach ?>
        </tbody>
    </table>
    <select name="action">
        <option value=""><?php e(t('choose...', 'common')); ?></option>
        <option value="delete"><?php e(t('delete selected', 'common')); ?></option>
    </select>

    <input type="submit" value="<?php e(t('go', 'common')); ?>" />
</form>

    <?php endif; ?>

<?php echo $gateway->getDBQuery()->display('paging'); ?>

<?php endif; ?>

<?php
$page->end();
?>