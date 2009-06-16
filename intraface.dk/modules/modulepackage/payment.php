<?php
require('../../include_first.php');
$module = $kernel->module('modulepackage');
$module->includeFile('Action.php');
$module->includeFile('ActionStore.php');
$module->includeFile('ShopExtension.php');

$translation = $kernel->getTranslation('modulepackage');

$action_store = new Intraface_modules_modulepackage_ActionStore($kernel->intranet->get('id'));
$action = $action_store->restore((int)$_GET['action_store_id']);

if (!is_object($action)) {
    trigger_error("Problem restoring action from order_id ".$_GET['action_store_id'], E_USER_ERROR);
    exit;
}

$shop = new Intraface_modules_modulepackage_ShopExtension();
$order = $shop->getOrderDetails($action->getOrderIdentifier());

$page = new Intraface_Page($kernel);
$page->start($translation->get('you are now ready to pay your order'));
?>
<h1><?php e($translation->get('you are now ready to pay your order')); ?></h1>

<?php if (!empty($_GET['payment_error'])): ?>
    <div class="message">
        <?php e($translation->get('an error occured under your online payment. Please try again. If this keeps happening, feel free to contact us.')); ?>
    </div>
<?php endif; ?>   


<p><?php e($translation->get('we have registered your order, and you are ready to pay for it.')); ?></p>

<p><strong><?php e($translation->get('your payment is')); ?> DKK <?php e($action->getTotalPrice()); ?></strong></p>

<p><?php e($translation->get('you have 2 options:')); ?></p>

<ul style="padding-left: 30px; list-style: square outside url(/images/icons/silk/accept.png);">
    <li><h2><?php e($translation->get('pay online')); ?></h2>
        <p><?php e($translation->get('you can choose to pay the order with creditcard. this will process your order instantly.')); ?></p>
        <p><?php e($translation->get('the paymend is carried out on a secure connection.')); ?></p>
        
        <?php
        $lang = $translation->getLang(); 
        $language = (isset($lang) && $lang == 'dansk') ? 'da' : 'en';
        
        $payment_provider = 'Ilib_Payment_Authorize_Provider_'.INTRAFACE_ONLINEPAYMENT_PROVIDER;
        $payment_authorize = new $payment_provider(INTRAFACE_ONLINEPAYMENT_MERCHANT, INTRAFACE_ONLINEPAYMENT_MD5SECRET);
        $payment_prepare = $payment_authorize->getPrepare(
            $action->getOrderIdentifier(), 
            $order['id'],
            $order['arrears'][$order['default_currency']], 
            $order['default_currency'],
            $language,
            NET_SCHEME.NET_HOST.NET_DIRECTORY.'modules/modulepackage/index.php?status=success',
            NET_SCHEME.NET_HOST.NET_DIRECTORY.'modules/modulepackage/payment.php?action_store_id='.$action_store->getId().'&payment_error=true',
            NET_SCHEME.NET_HOST.NET_DIRECTORY.'modules/modulepackage/process.php',
            NET_SCHEME.NET_HOST.NET_DIRECTORY.'payment/html/cci.php?language='.$language,
            $_GET,
            $_POST
        );
        
        /*
        $optional = array(
            'action_store_id' => $action_store->getId(),
            'intranet_public_key' => $kernel->intranet->get('public_key')
            );
        
        $payment_prepare->setOptionalValues($optional);
        */
        
        ?>
        <form action="<?php if(!strpos($payment_prepare->getAction(), '/')):  e($payment_prepare->getAction().'/index.php'); else: e($payment_prepare->getAction()); endif; ?>" method="POST">
        
        <?php echo $payment_prepare->getHiddenFields(); ?>
        <input type="hidden" name="action_store_id" value="<?php e($action_store->getId()); ?>" />
        
        <input type="submit" name="submit" value="<?php e($translation->get('pay the order now')); ?>" />
        
       </form>
    </li>
    <li><h2><?php e($translation->get('Pay by bank transfer')); ?></h2>
        <p><?php e($translation->get('You can choose to pay the order by bank transfer.')); ?></p>
        <p><?php e($translation->get('Please notice that your order will first be processed when we have recieved your payment.')); ?></p>
        <p><?php e($translation->get('As we have already registered your order, you do not need to do anymore for now. you will recieve the payment information on your e-mail with the order confirmation.')); ?></p>
        <p><a href=""><?php e($translation->get('back to the frontpage')); ?></a></p>
    </li>
</ul>

<p><?php e($translation->get('if you have any problems or questions, do not hesitate to contact us.')); ?> <a href="mailto:support@intraface.dk">support@intraface.dk</a></p>

<?php
$page->end();
?>