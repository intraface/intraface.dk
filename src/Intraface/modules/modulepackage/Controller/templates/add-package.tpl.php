<h1><?php e(t($add_type).' '.t('package')); ?></h1>

<?php echo $modulepackagemanager->error->view(); ?>

<form action="<?php e(url()); ?>" method="post">

<fieldset>
    <legend><?php e(t('your selected package')); ?></legend>
    <div class="formrow">
        <label for="package"><?php e(t('package')); ?></label>
        <span id="package"><?php e(t($modulepackage->get('plan')).' '.t($modulepackage->get('group'))); ?> <input type="hidden" name="id" value="<?php e($modulepackage->get('id')); ?>" /></span>
    </div>

    <div class="formrow">
        <?php

        ?>
        <label for="price"><?php e(t('price')); ?></label>
        <span id="price"><?php $product = $modulepackageshop->getProduct((int)$modulepackage->get('product_id'));
        if (isset($product['product']['currency']['DKK']['price_incl_vat'])) :
            e('DKK '.$product['product']['currency']['DKK']['price_incl_vat'].' '.t('per').' '.t($product['product']['unit']['singular']));
        else :
            echo 'free!';
        endif; ?></span>
    </div>

    <div class="formrow">
        <label for="modules"><?php e(t('gives you the following modules')); ?></label>
        <span id="modules">
            <?php
            $modules = $modulepackage->get('modules');
            $limiters = array();
            for ($j = 0, $max = count($modules); $j < $max; $j++) {
                if ($j != 0) {
                    e(', ');
                }
                e(t($modules[$j]['module']));
                if (is_array($modules[$j]['limiters']) && count($modules[$j]['limiters']) > 0) {
                    $limiters = array_merge($limiters, $modules[$j]['limiters']);
                }
            }
            ?>
        </span>
    </div>

    <div class="formrow">
        <label for="limiters"><?php e(t('and gives you')); ?></label>
        <span id="limiters">
            <?php
            if (is_array($limiters) && count($limiters) > 0) {
                foreach ($limiters as $limiter) {
                    e(t($limiter['description']).' ');
                    if (isset($limiter['limit_readable'])) {
                        e($limiter['limit_readable']);
                    } else {
                        e($limiter['limit']);
                    }
                }
            } else {
                e(t('no limitations at all, isn\'t that nice!'));
            }
            ?>
        </span>
    </div>
</fieldset>

<?php
$modulepackagemanager->getDBQuery($kernel);
$modulepackagemanager->getDBQuery()->setFilter('status', 'created_and_active');
$modulepackagemanager->getDBQuery()->setFilter('group_id', $modulepackage->get('group_id'));
$modulepackagemanager->getDBQuery()->setFilter('sorting', 'end_date');
$existing_modulepackages = $modulepackagemanager->getList();

// default start date is today
if ($add_type == 'extend' && count($existing_modulepackages) > 0 && isset($existing_modulepackages[count($existing_modulepackages)-1]['dk_start_date'])) {
    $end_date_integer = strtotime($existing_modulepackages[count($existing_modulepackages)-1]['end_date']);
    // the new start day is the day after the last package ends
    $start_date = date('d-m-Y', strtotime('+1 day', $end_date_integer));
} else {
    $start_date = date('d-m-Y');
}

if (is_array($existing_modulepackages) && count($existing_modulepackages) > 0) :
    ?>
    <fieldset>
        <legend><?php e(t('your existing packages')); ?></legend>

        <table class="stripe">
            <thead>
                <tr>
                    <th><?php e(t('package')); ?></th>
                    <th><?php e(t('end date')); ?></th>
                    <?php if ($add_type == 'upgrade') : ?>
                        <th><?php e(t('balance in your favour')); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($existing_modulepackages as $package) : ?>
                    <tr>
                        <td><?php e(t($package['plan']).' '.t($package['group'])); ?></td>
                        <td><?php e($package['dk_end_date']); ?></td>
                        <?php if ($add_type == 'upgrade') : ?>
                            <td></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
             </tbody>
         </table>
            <?php if ($add_type == 'upgrade') : ?>
            <p><?php e(t('your balance will be deducted from your new upgrade price')); ?></p>
            <?php endif; ?>
    </fieldset>
    <?php
endif;
?>

<fieldset>
    <legend><?php e(t('choose periode')); ?></legend>
    <div class="formrow">
        <label for="start_date"><?php e(t('start dato')); ?></label>
        <span id="start_date"><?php e($start_date); ?></span>
    </div>


    <div class="formrow">
        <label for="duration_month"><?php e(t('periode')); ?></label>
        <select name="duration_month" id="duration_month">
            <option value="12"><?php e('1 '.t('year'));
            if (isset($product['price_incl_vat'])) :
                e(' (DKK '.($product['price_incl_vat']*12).')');
            endif; ?></option>
            <option value="24"><?php e('2 '.t('years'));
            if (isset($product['price_incl_vat'])) :
                e(' (DKK '.($product['price_incl_vat']*24).')');
            endif;  ?></option>
        </select>
    </div>

</fieldset>

<fieldset>
    <legend><?php e(t('make sure your address informations are correct')); ?></legend>
    <?php
    $address = $kernel->intranet->address->get();
    ?>
    <div class="formrow">
        <label for="name"><?php e(t('name', 'address')); ?></label>
        <input type="text" name="name" id="name" value="<?php if (isset($address['name'])) {
            e($address["name"]);
} ?>" />
    </div>
    <div class="formrow">
        <label for="address"><?php e(t('address', 'address')); ?></label>
        <textarea name="address" id="address" rows="2"><?php if (isset($address['address'])) {
            e($address["address"]);
} ?></textarea>
    </div>
    <div class="formrow">
        <label for="postcode"><?php e(t('postal code and city', 'address')); ?></label>
        <div>
            <input type="text" name="postcode" id="postcode" value="<?php if (isset($address['postcode'])) {
                e($address["postcode"]);
} ?>" size="4" />
            <input type="text" name="city" id="city" value="<?php if (isset($address['city'])) {
                e($address["city"]);
} ?>" />
        </div>
    </div>
    <div class="formrow">
        <label for="country"><?php e(t('country', 'address')); ?></label>
        <input type="text" name="country" id="country" value="<?php if (isset($address['country'])) {
            e($address["country"]);
} ?>" />
    </div>
    <div class="formrow">
        <label for="cvr"><?php e(t('cvr number', 'address')); ?></label>
        <input type="text" name="cvr" id="cvr" value="<?php if (isset($address['cvr'])) {
            e($address["cvr"]);
} ?>" />
    </div>
    <div class="formrow">
        <label for="email"><?php e(t('e-mail', 'address')); ?></label>
        <input type="text" name="email" id="email" value="<?php if (isset($address['email'])) {
            e($address["email"]);
} ?>" />
    </div>
    <div class="formrow">
        <label for="phone"><?php e(t('phone', 'address')); ?></label>
        <input type="text" name="phone" id="phone" value="<?php if (isset($address['phone'])) {
            e($address["phone"]);
} ?>" />
    </div>
</fieldset>

<fieldset>
    <legend><?php e(t('one final question')); ?></legend>

    <input type="checkbox" name="accept_conditions" value="1" id="accept_conditions" /> <label for="accept_conditions"><?php e(t('i accept the conditions of sales and use!')); ?></label>
</fieldset>

<p>
    <input type="submit" name="submit" value="<?php e(t('yes, i am ready to pay')); ?>" />
    <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
</p>

</form>
