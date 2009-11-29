<?php
/*if ($context->getProductDoctrine()->getId() > 0) {
    $product = $context->getProductDoctrine();
}*/
?>
<h1><?php e(t('Edit product')); ?></h1>

<?php echo $context->getError()->view(); ?>

<form action="<?php e(url(null, array($context->subview()))); ?>" method="post">
<fieldset>
    <legend><?php e(t('Product information')); ?></legend>
        <div class="formrow">
            <label for="number"><?php e(t('Product number')); ?></label>
            <input type="text" name="number" id="number" value="<?php if (isset($product)): e($product->getDetails()->getNumber()); else: e($context->getGateway()->getMaxNumber() + 1); endif; ?>" />
        </div>
        <div class="formrow">
            <label for="name"><?php e(t('Name')); ?></label>
            <input type="text" size="50" name="name" id="name" value="<?php if(isset($product)) e($product->getDetails()->getTranslation('da')->name); ?>" />
        </div>
        <div class="formrow">
            <label for="description"><?php e(t('Description')); ?></label>
            <textarea class="resizable" rows="8" cols="60" name="description" id="description"><?php if (isset($product)) e($product->getDetails()->getTranslation('da')->description); ?></textarea>
        </div>

        <div class="formrow">
            <label for="unit"><?php e(t('Unit type')); ?></label>
            <select name="unit" id="unit">
                <?php foreach (Intraface_modules_product_Product_Details::getUnits() AS $key=>$unit): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($product) && ($units = $product->getDetails()->getUnit()) && $units['singular'] == $unit['singular']) e(' selected="selected"'); ?> ><?php if(!empty($unit['combined'])) e(t($unit['combined'])); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Price information')); ?></legend>
        <div class="formrow">
            <label for="price"><?php e(t('Price')); ?></label>
            <input type="text" name="price" id="price" value="<?php if (isset($product)) e($product->getDetails()->getPrice()->getAsLocal('da_dk', 2)); ?>" /> <?php e(t('excl. vat')); ?>
        </div>

        <div class="formrow">
            <label for="vat"><?php e(t('Vat')); ?></label>
            <select name="vat" id="vat">
                <?php foreach (array(0 => 'No', 1 => 'Yes') AS $key=>$v): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($product) && ($key == 1 &&  $product->getDetails()->getVatPercent()->getAsIso() > 0 || $key == 0 && $product->getDetails()->getVatPercent()->getAsIso() == 0)) e(' selected="selected"'); ?> ><?php e(t($v, 'common')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </fieldset>

    <?php if ($context->getKernel()->user->hasModuleAccess('webshop') || $context->getKernel()->user->hasModuleAccess('shop')): ?>
    <fieldset>
        <legend><?php e(t('Information for shop')); ?></legend>

        <div class="formrow">
            <label for="weight"><?php e(t('Weight')); ?></label>
            <input type="text" name="weight" id="weight" value="<?php if (isset($product)) e($product->getDetails()->getWeight()->getAsLocal('da_dk')); ?>" /> <?php e(t('grams')); ?>
        </div>

        <div class="formrow">
            <label for="before_price"><?php e(t('Before price')); ?></label>
            <input type="text" name="before_price" id="before_price" value="<?php if (isset($product) && $product->getDetails()->getBeforePrice()->getAsIso() != 0) e($product->getDetails()->getBeforePrice()->getAsLocal('da_dk', 2)); ?>" />
        </div>

        <?php if ($context->getKernel()->user->hasModuleAccess('shop')): ?>
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
                    <input type="hidden" name="has_variation" value="<?php if(isset($product)) e($product->hasVariation()); ?>" />
                    <span id="has_variation">
                        <?php
                        if ($product->hasVariation() == 1) {
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
            <label for="do_show"><?php e(t('Show in webshop')); ?></label>
            <select name="do_show" id="do_show">

                <?php foreach (array(0 => 'No', 1 => 'Yes') AS $key=>$v): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($product) && $product->showInShop() == $key) e('selected="selected"'); ?> ><?php e(t($v, 'common')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>

    <?php endif; ?>

    <?php if ($context->getKernel()->user->hasModuleAccess('stock')): ?>
    <fieldset>
        <legend><?php e(t('Stock')); ?></legend>
        <div class="formrow">
            <label for="stock"><?php e(t('Stock product')); ?></label>
            <select name="stock" id="stock">
                <?php foreach (array(0 => 'No', 1 => 'Yes') AS $key=>$v): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($product) AND $product->hasStock() == $key) e('selected="selected"'); ?> ><?php e(t($v, 'common')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>
    <?php endif; ?>

    <?php if ($context->getKernel()->user->hasModuleAccess('accounting')): ?>
    <?php
        $mainAccounting = $context->getKernel()->useModule('accounting');
        $x = 0;
        $year = new Year($context->getKernel());
        $year->loadActiveYear();

        $account = new Account($year);
        $accounts =  $account->getList('sale');

    ?>
    <fieldset>
        <legend><?php e(t('Accounting')); ?></legend>

        <?php if (count($accounts) == 0): ?>
            <p><?php e(__('You will need to create an accounting year and create accounts for that year, to be able to set the account for which this product will be stated.')); ?> <a href="<?php e($mainAccounting->getPath()); ?>"><?php e(__('Create accounting year and accounts')); ?></a></p>
        <?php else: ?>

        <div class="formrow">
            <label for="state_account"><?php e(t('State on account')); ?></label>
            <select id="state_account" name="state_account_id">
                <option value=""><?php e(t('Choose...', 'common')); ?></option>
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
        <input type="submit" name="submit" value="<?php e(t('Save', 'common')); ?>" class="save" />
        <a href="<?php e(url('.')); ?>"><?php e(t('Cancel', 'common')); ?></a>
    </div>

</form>
