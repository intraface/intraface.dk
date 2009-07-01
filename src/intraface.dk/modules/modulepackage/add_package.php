<?php
require('../../include_first.php');
$module = $kernel->module('modulepackage');
$module->includeFile('Manager.php');
$module->includeFile('ShopExtension.php');
$module->includeFile('ActionStore.php');

$translation = $kernel->getTranslation('modulepackage');

if (!empty($_POST)) {
    $modulepackage = new Intraface_modules_modulepackage_ModulePackage(intval($_POST['id']));
    $modulepackagemanager = new Intraface_modules_modulepackage_Manager($kernel->intranet);
    
    $add_type = $modulepackagemanager->getAddType($modulepackage);
    
    $values = $_POST;
    if (!$kernel->intranet->address->validate($values) || !$kernel->intranet->address->save($values)) {
        // Here we need to know the errors from address, but it does not validate now!
        $modulepackagemanager->error->set('there was an error in your address informations');
        $modulepackagemanager->error->merge($kernel->intranet->address->error->getMessage());
    }
    else {
        if (!isset($_POST['accept_conditions']) || $_POST['accept_conditions'] != '1') {
            $modulepackagemanager->error->set('You need to accept the conditions of sale and use');
        }
        else {
        
            // we are now ready to create the order.
            switch($add_type) {
                case 'add':
                    $action = $modulepackagemanager->add($modulepackage, (int)$_POST['duration_month'].' month');
                    break;
                case 'extend':
                    $action = $modulepackagemanager->extend($modulepackage, (int)$_POST['duration_month'].' month');
                    break;
                case 'upgrade':
                    $action = $modulepackagemanager->upgrade($modulepackage, (int)$_POST['duration_month'].' month');
                    break;
                default:
                    trigger_error('Invalid add_type "'.$add_type.'"', E_USER_ERROR);   
                    exit;   
            }
            
            if (!$modulepackagemanager->error->isError()) {
                $action_store = new Intraface_modules_modulepackage_ActionStore($kernel->intranet->get('id'));
                if ($action->hasAddActionWithProduct()) {
                    
                    $contact = $kernel->intranet->address->get();
                    // The following we do not want to transfer as this can give problems.
                    unset($contact['id']);
                    unset($contact['type']);
                    unset($contact['belong_to_id']);
                    unset($contact['address_id']);
                    
                    // If the intranet address is different from the user it is probably a company.
                    if ($kernel->intranet->address->get('name') != $kernel->user->getAddress()->get('name')) {
                        $contact['contactperson'] = $kernel->user->getAddress()->get('name');
                        $contact['contactemail'] = $kernel->user->getAddress()->get('email');
                        $contact['contactphone'] =  $kernel->user->getAddress()->get('phone');
                    }
                    
                    // We add the contact_id. But notice, despite of the bad naming the contact_id is the contact_id in the intranet_maintenance intranet!
                    $contact['contact_id'] = (int)$kernel->intranet->get('contact_id'); 
                    
                    // we place the order.
                    if (!$action->placeOrder($contact, Intraface_Mail::factory())) {
                        trigger_error("Unable to place the order", E_USER_ERROR);
                        exit;
                    }
                    
                    $total_price = $action->getTotalPrice();
                    
                }
                else {
                    $total_price = 0;
                }
                
                // sets private key to be saved.
                $action->setIntranetPrivateKey($kernel->intranet->get('private_key'));
                    
                if (!$action_store_identifier = $action_store->store($action)) {
                    trigger_error("Unable to store Action!", E_USER_ERROR);
                    exit;
                }
                    
                // TODO: What do we do if the onlinepayment is not running?
                    
                // Notice: Only if the price is more than zero we continue to the payment page, otherwise we contibue to the process page further down.
                if (!empty($action_store_identifier) && $total_price > 0) {
                    header('location: payment.php?action_store_identifier='.$action_store_identifier);
                    exit;
                }
                elseif (!empty($action_store_identifier)) {
                    header('location: process.php?action_store_identifier='.$action_store_identifier);
                    exit;
                }
                else {
                    trigger_error('We did not end up having an action store id!', E_USER_ERROR);
                    exit;
                }
            }
        }
    }
}
elseif (isset($_GET['id'])) {
    $modulepackage = new Intraface_modules_modulepackage_ModulePackage(intval($_GET['id']));
    $modulepackagemanager = new Intraface_modules_modulepackage_Manager($kernel->intranet);
    if ($modulepackage->get('id') == 0) {
        trigger_error("Invalid id", E_USER_ERROR);
        exit;
    }
    
    $add_type = $modulepackagemanager->getAddType($modulepackage);
    
}
else {
    trigger_error("An id is needed!", E_USER_ERROR);
}



$modulepackageshop = new Intraface_modules_modulepackage_ShopExtension();

$page = new Intraface_Page($kernel);
$page->start($translation->get($add_type).' '.$translation->get('package'));
?>

<h1><?php e($translation->get($add_type).' '.$translation->get('package')); ?></h1>

<?php echo $modulepackagemanager->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

<fieldset>
    <legend><?php e($translation->get('your selected package')); ?></legend>
    <div class="formrow">
        <label for="package"><?php e($translation->get('package')); ?></label>
        <span id="package"><?php e($translation->get($modulepackage->get('plan')).' '.$translation->get($modulepackage->get('group'))); ?> <input type="hidden" name="id" value="<?php e($modulepackage->get('id')); ?>" /></span>
    </div>
    
    <div class="formrow">
        <?php
        
        ?>
        <label for="price"><?php e($translation->get('price')); ?></label>
        <span id="price"><?php $product = $modulepackageshop->getProduct((int)$modulepackage->get('product_id')); if (isset($product['product']['currency']['DKK']['price_incl_vat'])): e('DKK '.$product['product']['currency']['DKK']['price_incl_vat'].' '.$translation->get('per').' '.$translation->get($product['product']['unit']['singular'])); else: echo 'free!'; endif; ?></span>
    </div>
    
    <div class="formrow">
        <label for="modules"><?php e($translation->get('gives you the following modules')); ?></label>
        <span id="modules">
            <?php 
            $modules = $modulepackage->get('modules');
            $limiters = array();
            for ($j = 0, $max = count($modules); $j < $max; $j++) {
                if ($j != 0) {
                    e(', ');
                }
                e($translation->get($modules[$j]['module']));
                if (is_array($modules[$j]['limiters']) && count($modules[$j]['limiters']) > 0) {
                    $limiters = array_merge($limiters, $modules[$j]['limiters']);
                }  
            }
            ?>
        </span>
    </div>
    
    <div class="formrow">
        <label for="limiters"><?php e($translation->get('and gives you')); ?></label>
        <span id="limiters">
            <?php 
            if (is_array($limiters) && count($limiters) > 0) {
                foreach ($limiters AS $limiter) {
                    e($translation->get($limiter['description']).' ');
                    if (isset($limiter['limit_readable'])) {
                        e($limiter['limit_readable']);
                    }
                    else {
                        e($limiter['limit']);
                    }
                }
            }
            else {
                e($translation->get('no limitations at all, isn\'t that nice!'));
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
}
else {
    $start_date = date('d-m-Y');
}

if (is_array($existing_modulepackages) && count($existing_modulepackages) > 0):
    ?>
    <fieldset>
        <legend><?php e($translation->get('your existing packages')); ?></legend>
        
        <table class="stripe">
            <thead>
                <tr>
                    <th><?php e($translation->get('package')); ?></th>
                    <th><?php e($translation->get('end date')); ?></th>
                    <?php if ($add_type == 'upgrade'): ?>
                        <th><?php e($translation->get('balance in your favour')); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($existing_modulepackages AS $package): ?>
                    <tr>    
                        <td><?php e($translation->get($package['plan']).' '.$translation->get($package['group'])); ?></td>
                        <td><?php e($package['dk_end_date']); ?></td>
                        <?php if ($add_type == 'upgrade'): ?>
                            <td></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
             </tbody>
         </table>
         <?php if ($add_type == 'upgrade'): ?>
            <p><?php e($translation->get('your balance will be deducted from your new upgrade price')); ?></p>
         <?php endif; ?>
    </fieldset>
    <?php
endif;
?>

<fieldset>
    <legend><?php e($translation->get('choose periode')); ?></legend>
    <div class="formrow">
        <label for="start_date"><?php e($translation->get('start dato')); ?></label>
        <span id="start_date"><?php e($start_date); ?></span>
    </div>
    
    
    <div class="formrow">
        <label for="duration_month"><?php e($translation->get('periode')); ?></label>
        <select name="duration_month" id="duration_month">
            <option value="12"><?php e('1 '.$translation->get('year')); if (isset($product['price_incl_vat'])): e(' (DKK '.($product['price_incl_vat']*12).')'); endif; ?></option>
            <option value="24"><?php e('2 '.$translation->get('years')); if (isset($product['price_incl_vat'])): e(' (DKK '.($product['price_incl_vat']*24).')'); endif;  ?></option>
        </select>    
    </div>
    
</fieldset>

<fieldset>
    <legend><?php e($translation->get('make sure your address informations are correct')); ?></legend>
    <?php
    $address = $kernel->intranet->address->get();
    ?>
    <div class="formrow">
        <label for="name"><?php e($translation->get('name', 'address')); ?></label>
        <input type="text" name="name" id="name" value="<?php if (isset($address['name'])) e($address["name"]); ?>" />
    </div>
    <div class="formrow">
        <label for="address"><?php e($translation->get('address', 'address')); ?></label>
        <textarea name="address" id="address" rows="2"><?php if (isset($address['address'])) e($address["address"]); ?></textarea>
    </div>
    <div class="formrow">
        <label for="postcode"><?php e($translation->get('postal code and city', 'address')); ?></label>
        <div>
            <input type="text" name="postcode" id="postcode" value="<?php if (isset($address['postcode'])) e($address["postcode"]); ?>" size="4" />
            <input type="text" name="city" id="city" value="<?php if (isset($address['city'])) e($address["city"]); ?>" />
        </div>
    </div>
    <div class="formrow">
        <label for="country"><?php e($translation->get('country', 'address')); ?></label>
        <input type="text" name="country" id="country" value="<?php if (isset($address['country'])) e($address["country"]); ?>" />
    </div>
    <div class="formrow">
        <label for="cvr"><?php e($translation->get('cvr number', 'address')); ?></label>
        <input type="text" name="cvr" id="cvr" value="<?php if (isset($address['cvr'])) e($address["cvr"]); ?>" />
    </div>
    <div class="formrow">
        <label for="email"><?php e($translation->get('e-mail', 'address')); ?></label>
        <input type="text" name="email" id="email" value="<?php if (isset($address['email'])) e($address["email"]); ?>" />
    </div>
    <div class="formrow">
        <label for="phone"><?php e($translation->get('phone', 'address')); ?></label>
        <input type="text" name="phone" id="phone" value="<?php if (isset($address['phone'])) e($address["phone"]); ?>" />
    </div>
</fieldset>

<fieldset>
    <legend><?php e($translation->get('one final question')); ?></legend>
    
    <input type="checkbox" name="accept_conditions" value="1" id="accept_conditions" /> <label for="accept_conditions"><?php e($translation->get('i accept the conditions of sales and use!')); ?></label>
</fieldset>

<p>
    <input type="submit" name="submit" value="<?php e($translation->get('yes, i am ready to pay')); ?>" />
    <?php e($translation->get('or', 'common')); ?>
    <a href="index.php"><?php e($translation->get('Cancel', 'common')); ?></a>
</p>

</form>

<?php
$page->end();
?>
