<?php
require('../../include_first.php');

$module = $kernel->module('product');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['action']) AND $_POST['action'] == 'delete') {
        $deleted = array();
        if (!empty($_POST['selected']) AND is_array($_POST['selected'])) {
            foreach ($_POST['selected'] AS $key=>$id) {
                $product = new Product($kernel, intval($id));
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
            foreach ($undelete AS $key=>$id) {
                $product = new Product($kernel, intval($id));
                if (!$product->undelete()) {
                    // void
                }
            }
        }
    }
}

$product = new Product($kernel);
$product->createDBQuery();
// $characters = $product->getCharacters();
$keywords = $product->getKeywordAppender();

// burde bruge query
if(isset($_GET["search"]) || isset($_GET["keyword_id"])) {
    if(isset($_GET["search"])) {
        $product->dbquery->setFilter("search", $_GET["search"]);
    }

    if(isset($_GET["keyword_id"])) {
        $product->dbquery->setKeyword($_GET["keyword_id"]);
    }
} else {
    $product->dbquery->useCharacter();
}

$product->dbquery->defineCharacter("character", "detail.name");
$product->dbquery->usePaging("paging");
$product->dbquery->storeResult("use_stored", "products", "toplevel");

$products = $product->getList();

$page = new Page($kernel);
$page->start(t('products'));
?>
<h1><?php e(t('products')); ?></h1>

<ul class="options">
    <li><a class="new" href="product_edit.php"><?php e(t('create product')); ?></a></li>
    <?php if (count($products) > 0): ?>
    <li><a href="batch_edit.php?use_stored=true"><?php e(t('edit all products in search')); ?></a></li>
    <?php endif; ?>
</ul>

<?php if (!$product->isFilledIn()): ?>
    <p><?php e(t('no products has been created.')); ?> <a href="product_edit.php"><?php e(t('create product')); ?></a>.</p>
<?php else: ?>

<form action="index.php" method="get" class="search-filter">
    <fieldset>
        <legend><?php e(t('search')); ?></legend>
        <!--
        <label for="filter">Filter
            <select name="filter" id="filter">
                <option>Ingen</option>
                <option value="notpublished" <?php if(isset($_GET['filter']) && $_GET['filter'] == 'notpublished') echo ' selected="selected"'; ?>>Ikke udgivet</option>
                <option value="webshop"<?php if(isset($_GET['filter']) && $_GET['filter'] == 'webshop') echo ' selected="selected"'; ?>>Webshop</option>
                <option value="stock"<?php if(isset($_GET['filter']) && $_GET['filter'] == 'stock') echo ' selected="selected"'; ?>>Lager</option>
            </select>
        </label>
        -->
        <label for="search"><?php e(t('search for')); ?>
            <input name="search" id="search" type="text" value="<?php echo safeToForm($product->dbquery->getFilter("search")); ?>" />
        </label>

        <label for="keyword_id"><?php e(t('show with keywords')); ?>
            <select name="keyword_id" id="keyword_id">
                <option value=""><?php e(t('none')); ?></option>
                <?php foreach ($keywords->getUsedKeywords() AS $k) { ?>
                <option value="<?php echo $k['id']; ?>" <?php if($k['id'] == $product->dbquery->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php echo safeToForm($k['keyword']); ?></option>
                <?php } ?>
            </select>
        </label>
        <span>
            <input type="submit" value="<?php e(t('go')); ?>" />	<input type="reset" value="<?php e(t('reset')); ?>" />
        </span>
    </fieldset>
</form>


<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
<?php if(!empty($deleted)): ?>
        <p class="message"><?php e(t('products has been deleted')); ?>. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="<?php e(t('regret')); ?>" /></p>
<?php endif; ?>

<?php echo $product->dbquery->display('character'); ?>

    <?php if (!is_array($products) OR count($products) == 0): ?>
        <p><?php e(t('no products in search')); ?>.</p>
    <?php else: ?>

    <table summary="Produkter" id="product_table" class="stripe">
        <caption><?php e(t('products')); ?></caption>
        <thead>
            <tr>
                <th></th>
                <th><?php e(t('product number')); ?></th>
                <th><?php e(t('name')); ?></th>
                <th><?php e(t('unit')); ?></th>
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
                    <input type="checkbox" value="<?php echo intval($p['id']); ?>" name="selected[]" />
                </td>

                <td><?php echo safeToHtml($p['number']); ?></td>
                <td><a href="product.php?id=<?php echo $p['id']; ?>"><?php echo safeToHtml($p['name']); ?></a></td>
                <td><?php echo t($p['unit']['combined']); ?></td>
                 <?php if ($kernel->user->hasModuleAccess("webshop")) { ?>
              <td><?php if ($p['do_show'] == 1) e(t('yes')); else e(t('no')); ?></td>
                <?php } ?>
                <?php if ($kernel->user->hasModuleAccess("stock")) { ?>
                    <td>
                        <?php
                        if($p['stock'] == 0) {
                            print("-");
                        }
                        else {
                            //print_r($p['stock_status']);
                              if (!empty($p['stock_status']['for_sale'])) echo safeToHtml($p['stock_status']['for_sale']);
                        }
                        ?>
                    </td>
                <?php } ?>
                <td><?php if ($p['vat'] == 1) e(t('yes')); else e(t('no')); ?></td>
                <td class="amount"><?php echo number_format($p['price'], 2, ",", "."); ?></td>

                <td class="options">
          <?php if ($p['locked'] == 0) { ?>
                  <!-- nedenstående bør sættes på produktsiden - muligheden skal ikke findes her
                    <a href="index.php?lock=<?php echo $p['id']; ?>&amp;use_stored=true"><?php e(t('lock')); ?></a>
                    -->
                    <a class="button edit" href="product_edit.php?id=<?php echo $p['id']; ?>"><?php e(t('edit')); ?></a>
                    <!--<a class="button delete ajaxdelete" title="Dette sletter produktet" id="delete<?php echo intval($p['id']); ?>" href="index.php?use_stored=true&amp;delete=<?php echo intval($p['id']); ?>">Slet</a>--></td>
       <?php } else { ?>
          <a href="index.php?unlock=<?php echo $p['id']; ?>&amp;use_stored=true"><?php e(t('unlock')); ?></a>
       <?php } ?>
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

<?php echo $product->dbquery->display('paging'); ?>

<?php endif; ?>

<?php
$page->end();
?>