<?php
/**
 * @author Lars Olesen <lars@legestue.net>
 */

require '../../include_first.php';

$module = $kernel->module('product');

$redirect = Intraface_Redirect::factory($kernel, 'receive');
$translation = $kernel->getTranslation('product');

if ($kernel->user->hasModuleAccess('accounting')) {
    $mainAccounting = $kernel->useModule('accounting');
}

$gateway = new Intraface_modules_product_ProductDoctrineGateway(Doctrine_Manager::connection(), $kernel->user);
            

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if(!empty($_POST['id'])) {
        $product = $gateway->findById((int)$_POST['id']);
    }
    else {
        $product = new Intraface_modules_product_ProductDoctrine;
    }
    
    $product->getDetails()->number = $_POST['number'];
    $product->getDetails()->name = $_POST['name'];
    $product->getDetails()->description = $_POST['description'];
    $product->getDetails()->price = new Ilib_Variable_Float($_POST['price'], 'da_dk');
    if(isset($_POST['before_price'])) $product->getDetails()->before_price = new Ilib_Variable_Float($_POST['before_price'], 'da_dk');
    if(isset($_POST['weight'])) $product->getDetails()->weight = new Ilib_Variable_Float($_POST['weight'], 'da_dk');
    if(isset($_POST['unit'])) $product->getDetails()->unit = $_POST['unit'];
    if(isset($_POST['vat'])) $product->getDetails()->vat = $_POST['vat'];
    if(isset($_POST['do_show'])) $product->getDetails()->do_show = $_POST['do_show'];
    if(isset($_POST['state_account_id'])) $product->getDetails()->state_account_id = $_POST['state_account_id'];
    
    if(isset($_POST['has_variation'])) $product->has_variation = $_POST['has_variation'];
    if(isset($_POST['stock'])) $product->stock = $_POST['stock'];
    
    try {
        $product->save();
        
        if ($redirect->get('id') != 0) {
            $redirect->setParameter('product_id', $product->getId());
        }
        header('Location: ' . $redirect->getRedirect('product.php?id='.$product->getId()));
        exit;
    } catch (Doctrine_Validator_Exception $e) {
        $error = new Intraface_Doctrine_ErrorRender($translation);
        $error->attachErrorStack($product->getErrorStack());
        $error->attachErrorStack($product->getDetails()->getErrorStack());
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
        $product = $gateway->findById((int)$_GET['id']);
    } else {
        
    }
}

$page = new Intraface_Page($kernel);
$page->start(t('edit product'));
?>

<h1><?php e(t('edit product')); ?></h1>

<?php /* if ($product->get('locked') == 1) { ?>
    <ul class="formerrors">
      <li>Produktet er låst og kan ikke opdateres. <a href="edit_product.php?unlock=<?php e($product->get('id')); ?>&amp;id=<?php e($product->get('id')); ?>">Lås op</a>.</li>
   </ul>
<?php } */ ?>

<?php if (isset($error)) echo $error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
<fieldset>
    <legend><?php e(t('product information')); ?></legend>
        <input type="hidden" name="id" value="<?php if (isset($product)) e($product->getId()); ?>" />
        <div class="formrow">
            <label for="number"><?php e(t('product number')); ?></label>
            <input type="text" name="number" id="number" value="<?php if (isset($product)): e($product->getDetails()->getNumber()); else: e($gateway->getMaxNumber() + 1); endif; ?>" />
        </div>
        <div class="formrow">
            <label for="name"><?php e(t('name')); ?></label>
            <input type="text" size="50" name="name" id="name" value="<?php if(isset($product)) e($product->getDetails()->getName()); ?>" />
        </div>
        <div class="formrow">
            <label for="description"><?php e(t('description')); ?></label>
            <textarea class="resizable" rows="8" cols="60" name="description" id="description"><?php if (isset($product)) e($product->getDetails()->getDescription()); ?></textarea>
        </div>

        <div class="formrow">
            <label for="unit"><?php e(t('unit type')); ?></label>
            <select name="unit" id="unit">
                <?php foreach (Intraface_modules_product_Product_Details::getUnits() AS $key=>$unit): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($product) && ($units = $product->getDetails()->getUnit()) && $units['singular'] == $unit['singular']) e(' selected="selected"'); ?> ><?php if(!empty($unit['combined'])) e(t($unit['combined'])); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('price information')); ?></legend>
        <div class="formrow">
            <label for="price"><?php e(t('price')); ?></label>
            <input type="text" name="price" id="price" value="<?php if (isset($product)) e($product->getDetails()->getPrice()->getAsLocal('da_dk')); ?>" /> <?php e(t('excl. vat')); ?>
        </div>

        <div class="formrow">
            <label for="vat"><?php e(t('vat')); ?></label>
            <select name="vat" id="vat">
                <?php foreach (array(0 => 'No', 1 => 'Yes') AS $key=>$v): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($product) && ($key == 1 &&  $product->getDetails()->getVatPercent()->getAsIso() > 0 || $key == 0 && $product->getDetails()->getVatPercent()->getAsIso() == 0)) e(' selected="selected"'); ?> ><?php e(t($v, 'common')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </fieldset>

    <?php if ($kernel->user->hasModuleAccess('webshop') || $kernel->user->hasModuleAccess('shop')): ?>
    <fieldset>
        <legend><?php e(t('Information for shop')); ?></legend>

        <div class="formrow">
            <label for="weight"><?php e(t('weight')); ?></label>
            <input type="text" name="weight" id="weight" value="<?php if (isset($product)) e($product->getDetails()->getWeight()->getAsLocal('da_dk')); ?>" /> <?php e(t('grams')); ?>
        </div>
        
        <div class="formrow">
            <label for="before_price"><?php e(t('Before price')); ?></label>
            <input type="text" name="before_price" id="before_price" value="<?php if (isset($product) && $product->getDetails()->getBeforePrice()->getAsIso() != 0) e($product->getDetails()->getBeforePrice()->getAsLocal('da_dk')); ?>" />
        </div>

        <?php if ($kernel->user->hasModuleAccess('shop')): ?>
            <?php if (!isset($product)): ?>
                <div class="formrow">
                    <label for="has_variation"><?php e(t('Product has variations')); ?></label>
                    <select name="has_variation" id="has_variation">
                        <?php foreach (array(0 => 'No', 1 => 'Yes') AS $key=>$v): ?>
                            <option value="<?php e($key); ?>"><?php e(t($v, 'common')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="formrow">
                    <label for="has_variation"><?php e(t('Product has variations')); ?></label>
                    <input type="hidden" name="has_variation" value="<?php e($value['has_variation']); ?>" />
                    <span id="has_variation">
                        <?php
                        if ($product->getDetails()->hasVariation() == 1) {
                            e('Yes', 'common');
                        } else {
                            e('No', 'common');
                        }
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="formrow">
            <label for="do_show"><?php e(t('show in webshop')); ?></label>
            <select name="do_show" id="do_show">
                
                <?php foreach (array(0 => 'No', 1 => 'Yes') AS $key=>$v): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($product) && $product->getDetails()->showInShop() == $key) e('selected="selected"'); ?> ><?php e(t($v, 'common')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>
        
        <!-- her bør være en tidsangivelse -->

    <?php endif; ?>

    <?php if ($kernel->user->hasModuleAccess('stock')): ?>
    <fieldset>
        <legend><?php e(t('stock')); ?></legend>
        <div class="formrow">
            <label for="stock"><?php e(t('stock product')); ?></label>
            <select name="stock" id="stock">
                <?php foreach (array(0 => 'No', 1 => 'Yes') AS $key=>$v): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($product) AND $product->hasStock() == $key) e('selected="selected"'); ?> ><?php e(t($v, 'common')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>
    <?php endif; ?>

    <?php if ($kernel->user->hasModuleAccess('accounting')): ?>
    <?php
        $x = 0;
        $year = new Year($kernel);
        $year->loadActiveYear();

        $account = new Account($year);
        $accounts =  $account->getList('sale');

    ?>
    <fieldset>
        <legend><?php e(t('accounting')); ?></legend>

        <?php if (count($accounts) == 0): ?>
            <p><?php e($translation->get('You will need to create an accounting year and create accounts for that year, to be able to set the account for which this product will be stated.')); ?> <a href="<?php e($mainAccounting->getPath()); ?>"><?php e($translation->get('Create accounting year and accounts')); ?></a></p>
        <?php else: ?>

        <div class="formrow">
            <label for="state_account"><?php e(t('state on account')); ?></label>
            <select id="state_account" name="state_account_id">
                <option value=""><?php e(t('choose...', 'common')); ?></option>
                <?php
                    $x = 0;
                    $optgroup = 0;
                    foreach ($accounts AS $a):
                        if (strtolower($a['type']) == 'sum') continue;

                        if (strtolower($a['type']) == 'headline') {
                            
                            // det er lidt svært at få optgroupperne til at passe, hvis man har flere overskrifter i træk
                            if ($optgroup == 1) echo '</optgroup>';
                            echo '<optgroup label="'.$a['name'].'">';
                            $optgroup = 1;
                            continue;
                        }
                        ?>
                        <option value="<?php e($a['number']); ?>"
                        <?php
                        // @todo er det korrekt at det er number? og måske skal et produkt i virkeligheden snarere
                        // gemmes med nummeret en med id - for så er det noget lettere at opdatere fra år til år
                        if (isset($product) && $product->getDetails()->getStateAccountId() == $a['number']) echo ' selected="selected"';
                        ?>
                        ><?php e($a['name']); ?></option>
                        <?php
                    endforeach;
                    if ($optgroup == 1) echo '</optgroup>';
                ?>
            </select>
        </div>
        <?php endif; ?>
    </fieldset>
    <?php endif; ?>

    <div>
        <?php /* if ($product->get('locked') == 0):  */ ?>
            <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" class="save" /> <?php e(t('or', 'common')); ?>
        <?php /* endif; */ ?>
        <a href="<?php $return = 'index.php'; if (isset($product) && $product->getId() != 0) $return = 'product.php?id='.intval($product->getId()); e($redirect->getRedirect($return)); ?>"><?php e(t('Cancel', 'common')); ?></a>
    </div>

</form>

<?php
$page->end();
?>
