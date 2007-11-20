<?php
require('../../include_first.php');
require('Intraface/ModulePackage.php');
require('Intraface/ModulePackage/Manager.php');
require('Intraface/ModulePackage/ShopExtension.php');
require('Intraface/ModulePackage/ActionStore.php');

$translation = $kernel->getTranslation('modulepackage');

if(!empty($_POST)) {
    $modulepackage = new Intraface_ModulePackage(intval($_POST['id']));
    $modulepackagemanager = new Intraface_ModulePackage_Manager($kernel->intranet);
    
    $add_type = $modulepackagemanager->getAddType($modulepackage);
    
    $values = $_POST;
    if(!$kernel->intranet->address->save($values)) {
        // Here we need to know the errors from address, but it does not validate now!
        $modulepackagemanager->error->set('there was an error in your address informations');
    }
    else {
        if(!isset($_POST['accept_conditions']) || $_POST['accept_conditions'] != '1') {
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
            
            if(!$modulepackagemanager->error->isError()) {
                $action_store = new Intraface_ModulePackage_ActionStore($kernel->intranet->get('id'));
                if($action->hasAddActionWithProduct()) {
                    
                    $contact = $kernel->intranet->address->get();
                    // The following we do not want to transfer as this can give problems.
                    unset($contact['id']);
                    unset($contact['type']);
                    unset($contact['belong_to_id']);
                    unset($contact['address_id']);
                    
                    // If the intranet address is different from the user it is probably a company.
                    if($kernel->intranet->address->get('name') != $kernel->user->address->get('name')) {
                        $contact['contactperson'] = $kernel->user->address->get('name');
                        $contact['contactemail'] = $kernel->user->address->get('email');
                        $contact['contactphone'] =  $kernel->user->address->get('phone');
                    }
                    
                    // We add the contact_id. But notice, despite of the bad naming the contact_id is the contact_id in the intranet_maintenance intranet!
                    $contact['contact_id'] = (int)$kernel->intranet->get('contact_id'); 
                    
                    // we place the order.
                    if(!$action->placeOrder($contact)) {
                        trigger_error("Unable to place the order", E_USER_ERROR);
                        exit;
                    }
                    $order_id = $action->getOrderId();
                    $total_price = $action->getTotalPrice();
                }
                else {
                    $total_price = 0;
                }
                    
                if(!$action_store_id = $action_store->store($action)) {
                    trigger_error("Unable to store Action!", E_USER_ERROR);
                    exit;
                }
                    
                // TODO: What do we do if the onlinepayment is not running?
                    
                // Notice: Only if the price is more than zero we continue to the payment page, otherwise we contibue to the process page further down.
                if(isset($action_store_id) && $action_store_id > 0 && $total_price > 0) {
                    header('location: payment.php?action_store_id='.$action_store_id);
                    exit;
                }
                elseif(isset($action_store_id) && $action_store_id > 0) {
                    header('location: process.php?action_store_id='.intval($action_store_id));
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
elseif(isset($_GET['id'])) {
    $modulepackage = new Intraface_ModulePackage(intval($_GET['id']));
    $modulepackagemanager = new Intraface_ModulePackage_Manager($kernel->intranet);
    if($modulepackage->get('id') == 0) {
        trigger_error("Invalid id", E_USER_ERROR);
        exit;
    }
    
    $add_type = $modulepackagemanager->getAddType($modulepackage);
    
}
else {
    trigger_error("An id is needed!", E_USER_ERROR);
}



$modulepackageshop = new Intraface_ModulePackage_ShopExtension();

$page = new Page($kernel);
$page->start(safeToHtml($translation->get($add_type).' '.$translation->get('package')));
?>

<h1><?php echo safeToHtml($translation->get($add_type).' '.$translation->get('package')); ?></h1>

<?php $modulepackagemanager->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

<fieldset>
    <legend><?php echo safeToHtml($translation->get('your selected package')); ?></legend>
    <div class="formrow">
        <label for="package"><?php echo safeToHtml($translation->get('package')); ?></label>
        <span id="package"><?php echo safeToHtml($translation->get($modulepackage->get('plan')).' '.$translation->get($modulepackage->get('group'))); ?> <input type="hidden" name="id" value="<?php echo intval($modulepackage->get('id')); ?>" /></span>
    </div>
    
    <div class="formrow">
        <?php
        
        ?>
        <label for="price"><?php echo safeToHtml($translation->get('price')); ?></label>
        <span id="price"><?php $product = $modulepackageshop->getProduct((int)$modulepackage->get('product_id')); if(isset($product['price_incl_vat'])): echo safeToHtml('DKK '.$product['price_incl_vat']).' '.$translation->get('per').' '.$translation->get($product['unit_declensions']['singular']); else: echo 'free!'; endif; ?></span>
    </div>
    
    <div class="formrow">
        <label for="modules"><?php echo safeToHtml($translation->get('gives you the following modules')); ?></label>
        <span id="modules">
            <?php 
            $modules = $modulepackage->get('modules');
            $limiters = array();
            for($j = 0, $max = count($modules); $j < $max; $j++) {
                if($j != 0) {
                    echo ', ';
                }
                echo $translation->get($modules[$j]['module']);
                if(is_array($modules[$j]['limiters']) && count($modules[$j]['limiters']) > 0) {
                    $limiters = array_merge($limiters, $modules[$j]['limiters']);
                }  
            }
            ?>
        </span>
    </div>
    
    <div class="formrow">
        <label for="limiters"><?php echo safeToHtml($translation->get('and gives you')); ?></label>
        <span id="limiters">
            <?php 
            if(is_array($limiters) && count($limiters) > 0) {
                foreach($limiters AS $limiter) {
                    echo safeToHtml($translation->get($limiter['description']).' ');
                    if(isset($limiter['limit_readable'])) {
                        echo safeToHtml($limiter['limit_readable']);
                    }
                    else {
                        echo safeToHtml($limiter['limit']);
                    }
                }
            }
            else {
                echo safeToHtml($translation->get('no limitations at all, isn\'t that nice!'));
            }
            ?>
        </span>
    </div>
</fieldset>

<?php
$modulepackagemanager->createDBQuery($kernel);
$modulepackagemanager->dbquery->setFilter('status', 'created_and_active');
$modulepackagemanager->dbquery->setFilter('group_id', $modulepackage->get('group_id'));
$modulepackagemanager->dbquery->setFilter('sorting', 'end_date');
$existing_modulepackages = $modulepackagemanager->getList();

// default start date is today
if($add_type == 'extend' && count($existing_modulepackages) > 0 && isset($existing_modulepackages[count($existing_modulepackages)-1]['dk_start_date'])) {
    $end_date_integer = strtotime($existing_modulepackages[count($existing_modulepackages)-1]['end_date']);
    // the new start day is the day after the last package ends
    $start_date = date('d-m-Y', strtotime('+1 day', $end_date_integer));
}
else {
    $start_date = date('d-m-Y');
}

if(is_array($existing_modulepackages) && count($existing_modulepackages) > 0):
    ?>
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('your existing packages')); ?></legend>
        
        <table class="stripe">
            <thead>
                <tr>
                    <th><?php echo safeToHtml($translation->get('package')); ?></th>
                    <th><?php echo safeToHtml($translation->get('end date')); ?></th>
                    <?php if($add_type == 'upgrade'): ?>
                        <th><?php echo safeToHtml($translation->get('balance in your favour')); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($existing_modulepackages AS $package): ?>
                    <tr>    
                        <td><?php echo safeToHtml($translation->get($package['plan']).' '.$translation->get($package['group'])); ?></td>
                        <td><?php echo safeToHtml($package['dk_end_date']); ?></td>
                        <?php if($add_type == 'upgrade'): ?>
                            <td></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
             </tbody>
         </table>
         <?php if($add_type == 'upgrade'): ?>
            <p><?php echo safeToHtml($translation->get('your balance will be deducted from your new upgrade price')); ?></p>
         <?php endif; ?>
    </fieldset>
    <?php
endif;
?>

<fieldset>
    <legend><?php echo safeToHtml($translation->get('choose periode')); ?></legend>
    <div class="formrow">
        <label for="start_date"><?php echo safeToHtml($translation->get('start dato')); ?></label>
        <span id="start_date"><?php echo $start_date; ?></span>
    </div>
    
    
    <div class="formrow">
        <label for="duration_month"><?php echo safeToHtml($translation->get('periode')); ?></label>
        <select name="duration_month" id="duration_month">
            <option value="12"><?php echo safeToHtml('1 '.$translation->get('year')); if(isset($product['price_incl_vat'])): echo safeToHtml(' (DKK '.($product['price_incl_vat']*12).')'); endif; ?></option>
            <option value="24"><?php echo safeToHtml('2 '.$translation->get('years')); if(isset($product['price_incl_vat'])): echo safeToHtml(' (DKK '.($product['price_incl_vat']*24).')'); endif;  ?></option>
        </select>    
    </div>
    
</fieldset>

<fieldset>
    <legend><?php echo safeToHtml($translation->get('make sure your address informations are correct')); ?></legend>
    <?php
    $address = $kernel->intranet->address->get();
    ?>
    <div class="formrow">
        <label for="name"><?php echo safeToHtml($translation->get('name', 'address')); ?></label>
        <input type="text" name="name" id="name" value="<?php if(isset($address['name'])) echo safeToHtml($address["name"]); ?>" />
    </div>
    <div class="formrow">
        <label for="address"><?php echo safeToHtml($translation->get('address', 'address')); ?></label>
        <textarea name="address" id="address" rows="2"><?php if(isset($address['address'])) echo safeToHtml($address["address"]); ?></textarea>
    </div>
    <div class="formrow">
        <label for="postcode"><?php echo safeToHtml($translation->get('postal code and city', 'address')); ?></label>
        <div>
            <input type="text" name="postcode" id="postcode" value="<?php if(isset($address['postcode'])) echo safeToHtml($address["postcode"]); ?>" size="4" />
            <input type="text" name="city" id="city" value="<?php if(isset($address['city'])) echo safeToHtml($address["city"]); ?>" />
        </div>
    </div>
    <div class="formrow">
        <label for="country"><?php echo safeToHtml($translation->get('country', 'address')); ?></label>
        <input type="text" name="country" id="country" value="<?php if(isset($address['country'])) echo safeToHtml($address["country"]); ?>" />
    </div>
    <div class="formrow">
        <label for="cvr"><?php echo safeToHtml($translation->get('cvr number', 'address')); ?></label>
        <input type="text" name="cvr" id="cvr" value="<?php if(isset($address['cvr'])) echo safeToHtml($address["cvr"]); ?>" />
    </div>
    <div class="formrow">
        <label for="email"><?php echo safeToHtml($translation->get('e-mail', 'address')); ?></label>
        <input type="text" name="email" id="email" value="<?php if(isset($address['email'])) echo safeToHtml($address["email"]); ?>" />
    </div>
    <div class="formrow">
        <label for="phone"><?php echo safeToHtml($translation->get('phone', 'address')); ?></label>
        <input type="text" name="phone" id="phone" value="<?php if(isset($address['phone'])) echo safeToHtml($address["phone"]); ?>" />
    </div>
</fieldset>

<fieldset>
    <legend><?php echo safeToHtml($translation->get('one final question')); ?></legend>
    
    <input type="checkbox" name="accept_conditions" value="1" id="accept_conditions" /> <label for="accept_conditions"><?php echo safeToHtml($translation->get('i accept the conditions of sales and use!')); ?></label>
</fieldset>

<p>
    <input type="submit" name="submit" value="<?php echo safeToHtml($translation->get('yes, i am ready to pay')); ?>" />
    <?php echo safeToHtml($translation->get('or', 'common')); ?>
    <a href="index.php"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a>
</p>

</form>

<?php
$page->end();
?>
