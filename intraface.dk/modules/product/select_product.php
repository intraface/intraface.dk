<?php
require('../../include_first.php');

$product_module = $kernel->module("product");
$translation = $kernel->getTranslation('product');

// hente liste med produkter - bør hentes med getList!

$redirect = Intraface_Redirect::factory($kernel, 'receive');

if ($redirect->get('id') != 0) {
    $multiple = $redirect->isMultipleParameter('product_id');
    if (isset($_GET['set_quantity']) && (int)$_GET['set_quantity'] == 1) {
        $quantity = 1;
    } else {
        $quantity = 0;
    }
} else {
    trigger_error("Der mangler en gyldig redirect", E_USER_ERROR);
}

if (isset($_GET['add_new'])) {
    $add_redirect = Intraface_Redirect::factory($kernel, 'go');
    $add_redirect->setIdentifier('add_new');
    $url = $add_redirect->setDestination($product_module->getPath().'product_edit.php', $product_module->getPath().'select_product.php?'.$redirect->get('redirect_query_string').'&set_quantity='.$quantity);
    $add_redirect->askParameter('product_id');
    header('location: '.$url);
    exit;
}

if (!empty($_GET['select_variation'])) {
    $variation_redirect = Intraface_Redirect::factory($kernel, 'go');
    $variation_redirect->setIdentifier('select_variation');
    $url = $variation_redirect->setDestination($product_module->getPath().'select_product_variation.php?product_id='.intval($_GET['select_variation']).'&set_quantity='.$quantity, $product_module->getPath().'select_product.php?'.$redirect->get('redirect_query_string').'&set_quantity='.$quantity);
    $type = ($multiple) ? 'multiple' : 'single';
    $variation_redirect->askParameter('product_variation_id', $type);
    header('location: '.$url);
    exit;
}

if (isset($_GET['return_redirect_id'])) {
    $return_redirect = Intraface_Redirect::factory($kernel, 'return');
    if ($return_redirect->getIdentifier() == 'add_new' && $return_redirect->getParameter('product_id') != 0) {
        $redirect->setParameter('product_id', serialize(array('product_id' => intval($return_redirect->getParameter('product_id')), 'product_variation_id' => 0)), 1);
    }
    elseif ($return_redirect->getIdentifier() == 'select_variation') {
        // Returning from variations page and add the variations to the product_id parameter.
        $product_variations = $return_redirect->getParameter('product_variation_id', 'with_extra_value');
        if ($multiple) {
            foreach ($product_variations AS $product_variation) {
                $redirect->removeParameter('product_id', $product_variation['value']);
                if ($quantity) {
                    $redirect->setParameter('product_id', $product_variation['value'], $product_variation['extra_value']);
                } else {
                    $redirect->setParameter('product_id', $product_variation['value']);
                }
            }
        }
        else {
            
            $redirect->removeParameter('product_id', $product_variations['value']);
            if ($quantity) {
                $redirect->setParameter('product_id', $product_variations['value'], $product_variations['extra_value']);
            } else {
                $redirect->setParameter('product_id', $product_variations['value']);
            }
        }
    }
}

if (isset($_POST['submit']) || isset($_POST['submit_close'])) {
    if ($multiple) {
        if (isset($_POST['selected']) && is_array($_POST['selected'])) {
            foreach ($_POST['selected'] AS $selected_id => $selected_value) {
                if ($selected_value != '' && $selected_value != '0') {
                    $select = serialize(array('product_id' => $selected_id, 'product_variation_id' => 0));
                    // Hvis der allerede er gemt en værdi, så starter vi med at fjerne den, så der ikke kommer flere på.
                    $redirect->removeParameter('product_id', $select);
                    if ($quantity) {
                        $redirect->setParameter('product_id', $select, $selected_value);
                    } else {
                        $redirect->setParameter('product_id', $select);
                    }
                }
            }
        }
    } else {
        if (isset($_POST['selected']) && (int)$_POST['selected'] != 0) {
            $select = serialize(array('product_id' => (int)$_POST['selected'], 'product_variation_id' => 0));
            if ($quantity) {
                $redirect->setParameter('product_id', $select, (int)$_POST['quantity']);
            } else {
                $redirect->setParameter('product_id', $select);
            }
        }
    }

    if (isset($_POST['submit_close'])) {
        header('location: '.$redirect->getRedirect('index.php')); // index.php, ja hvor skal man ellers hen hvis der er fejl i redirect
        exit;
    }
}

$product = new Product($kernel);
$keywords = $product->getKeywordAppender();

if (isset($_GET["search"]) || isset($_GET["keyword_id"])) {

    if (isset($_GET["search"])) {
        $product->getDBQuery()->setFilter("search", $_GET["search"]);
    }

    if (isset($_GET["keyword_id"])) {
        $product->getDBQuery()->setKeyword($_GET["keyword_id"]);
    }
} else {
    $product->getDBQuery()->useCharacter();
}

$product->getDBQuery()->defineCharacter("character", "detail.name");
$product->getDBQuery()->usePaging("paging");
$product->getDBQuery()->storeResult("use_stored", "select_product", "sublevel");
$product->getDBQuery()->setExtraUri('set_quantity='.$quantity);

$list = $product->getList();
$product_values = $redirect->getParameter('product_id', 'with_extra_value');
$selected_products = array();
if (is_array($product_values)) {
    if ($multiple) {
        foreach ($product_values AS $selection) {
            $selection['value'] = unserialize($selection['value']);
            $selected_products[$selection['value']['product_id']] = $selection['extra_value'];
        }
    }
    else {
        $selected_products[$product_values['value']['product_id']] = $product_values['extra_value'];
    }
}

$page = new Intraface_Page($kernel);
//$page->includeJavascript('module', 'add_related.js');
$page->start(t('select product'));
?>
<h1><?php e(t('select product')); ?></h1>

<?php if ($product->isFilledIn() == 0): ?>
    <p><?php e(t('no products to select.')); ?> <a href="select_product.php?add_new=true&amp;set_quantity=<?php e($quantity); ?>"><?php e(t('create product')); ?></a>.</p>
<?php else: ?>

    <ul class="options">
        <li><a href="select_product.php?add_new=true&amp;set_quantity=<?php e($quantity); ?>">Opret produkt</a></li>
    </ul>

    <form action="<?php echo basename(__FILE__); ?>" method="get">
        <fieldset>
            <legend><?php e(t('search', 'common')); ?></legend>
            <label><?php e(t('search for')); ?>
            <input type="text" value="<?php e($product->getDBQuery()->getFilter("search")); ?>" name="search" id="search" />
        </label>
        <label>
            <?php e(t('show with keywords')); ?>
            <select name="keyword_id" id="keyword_id">
                <option value=""><?php e(t('none', 'common')); ?></option>
                <?php foreach ($keywords->getUsedKeywords() AS $k) { ?>
                <option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $product->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
                <?php } ?>
            </select>
        </label>
        <span><input type="submit" value="<?php e(t('search', 'common')); ?>" class="search" /><input type="hidden" name="set_quantity" value="<?php e($quantity); ?>" /></span>
        </fieldset>
        <br style="clear: both;" />
    </form>

    <?php
    echo $product->getDBQuery()->display('character');
    ?>
    <form action="<?php e($_SERVER['PHP_SELF']); ?>?set_quantity=<?php e($quantity); ?>" method="post">
        <table summary="Produkter" class="stripe">
            <caption><?php e(t('products')); ?></caption>
            <thead>
                <tr>
                    <th><?php if ($multiple && $quantity): e(t('Quantity')); else: echo e(t('Choose')); endif; ?></th>
                    <th><?php e(t('Product number')); ?></th>
                    <th><?php e(t('Name')); ?></th>
                    <th><?php e(t('Unit type')); ?></th>
                    <?php if ($kernel->user->hasModuleAccess('stock')): ?>
                    <th><?php e(t('Stock')); ?>r</th>
                    <?php endif; ?>
                    <th><?php e(t('Vat')); ?></th>
                    <th><?php e(t('Price')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list AS $p): ?>
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
                    <?php if ($kernel->user->hasModuleAccess('stock')): ?>
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

      <?php echo $product->getDBQuery()->display('paging'); ?>
    </form>
<?php endif; ?>
<?php
$page->end();
?>