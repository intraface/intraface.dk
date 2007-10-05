<?php
require('../../include_first.php');

$translation = $kernel->getTranslation('modulepackage');

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('you are now ready to pay your order')));
?>
<h1><?php echo safeToHtml($translation->get('you are now ready to pay your order')); ?></h1>

<p><?php echo safeToHtml($translation->get('we have registered your order, and you are ready to pay for it.')); ?></p>

<?php
// TODO: The price and order id should be collected from the action store id instead.
// TODO: Here the price should be shown also
?>

<p><?php echo safeToHtml($translation->get('you have 2 options:')); ?></p>

<ul>
    <li><h2><?php echo safeToHtml($translation->get('pay online')); ?></h2>
        <p><?php echo safeToHtml($translation->get('you can choose to pay the order with creditcard. this will process your order instantly.')); ?></p>
        <p><?php echo safeToHtml($translation->get('the paymend is carried out on a secure connection.')); ?></p>
        <form action="process.php" method="POST"> <!-- https://secure.quickpay.dk/quickpay.php -->
        <input type="submit" name="submit" value="<?php echo safeToHtml($translation->get('pay the order now')); ?>" />

        <?php
        $lang = $translation->getLang(); 
        $onlinepayment_language = (isset($lang) && $lang == 'dansk') ? 'da' : 'en';
        $onlinepayment_autocapture = "0";
        $onlinepayment_ordernum = $_GET['order_id']; // needs to be 4 digit
        $onlinepayment_amount = $_GET['amount'];
        $onlinepayment_currency = "DKK";
        $onlinepayment_merchant = "29991634";
        $onlinepayment_okpage = NET_SCHEME.NET_HOST.NET_DIRECTORY.'main/account/confirmation.php';
        $onlinepayment_errorpage = NET_SCHEME.NET_HOST.NET_DIRECTORY.'main/account/payment.php';
        $onlinepayment_resultpage = NET_SCHEME.NET_HOST.NET_DIRECTORY.'main/account/process.php';
        $onlinepayment_ccipage = "";
        $onlinepayment_md5secret = "intrafaceonlinepaymentmd5";
        $onlinepayment_md5check = md5($onlinepayment_language.
                $onlinepayment_autocapture.
                $onlinepayment_ordernum.
                $onlinepayment_amount.
                $onlinepayment_currency.
                $onlinepayment_merchant.
                $onlinepayment_okpage.
                $onlinepayment_errorpage.
                $onlinepayment_resultpage.
                $onlinepayment_ccipage.
                $onlinepayment_md5secret);
        
        ?>
        <input type="hidden" name="language" value="<?php echo safeTohtml($onlinepayment_language); ?>" />
        <input type="hidden" name="autocapture" value="<?php echo safeTohtml($onlinepayment_autocapture); ?>" />
        <input type="hidden" name="ordernum" value="<?php echo safeTohtml($onlinepayment_ordernum); ?>" />
        <input type="hidden" name="amount" value="<?php echo safeTohtml($onlinepayment_amount); ?>" />
        <input type="hidden" name="currency" value="<?php echo safeTohtml($onlinepayment_currency); ?>" />
        <input type="hidden" name="merchant" value="<?php echo safeTohtml($onlinepayment_merchant); ?>" />
        <input type="hidden" name="okpage" value="<?php echo safeTohtml($onlinepayment_okpage); ?>" />
        <input type="hidden" name="errorpage" value="<?php echo safeTohtml($onlinepayment_errorpage); ?>" />
        <input type="hidden" name="resultpage" value="<?php echo safeTohtml($onlinepayment_resultpage); ?>" />
        <input type="hidden" name="ccipage" value="<?php echo safeTohtml($onlinepayment_ccipage); ?>" />
        <input type="hidden" name="md5checkV2" value="<?php echo safeTohtml($onlinepayment_md5check); ?>" />
        </form>
    </li>
    <li><h2><?php echo safeToHtml($translation->get('pay by bank transfer')); ?></h2>
        <p><?php echo safeToHtml($translation->get('you can choose to pay the order by bank transfer.')); ?></p>
        <p><?php echo safeToHtml($translation->get('please notice taht your order will first be processed when we have recieved your payment.')); ?></p>
        <p><?php echo safeToHtml($translation->get('as we have already registered your order, you do not need to do anymore for now. you will recieve the payment information on your e-mail with the order confirmation.')); ?></p>
        <p><a href=""><?php echo safeToHtml($translation->get('back to the frontpage')); ?></a></p>
    </li>
</ul>

<p><?php echo safeToHtml($translation->get('if you have any problems or questions, do not hesitate to contact us.')); ?> <a href="mailto:support@intraface.dk">support@intraface.dk</a></p>

<?php
$page->end();
?>