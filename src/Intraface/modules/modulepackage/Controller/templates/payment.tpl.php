<h1><?php e(t('you are now ready to pay your order')); ?></h1>

<?php if ($context->query('payment_error')): ?>
    <div class="message">
        <?php e(t('an error occured under your online payment. Please try again. If this keeps happening, feel free to contact us.')); ?>
    </div>
<?php endif; ?>


<p><?php e(t('we have registered your order, and you are ready to pay for it.')); ?></p>

<p><strong><?php e(t('your payment is')); ?> DKK <?php e($action->getTotalPrice()); ?></strong></p>

<p><?php e(t('you have 2 options:')); ?></p>

<ul style="padding-left: 30px; list-style: square outside url(<?php e(url('/images/icons/silk/accept.png')); ?>);">
    <li><h2><?php e(t('pay online')); ?></h2>
        <p><?php e(t('you can choose to pay the order with creditcard. this will process your order instantly.')); ?></p>
        <p><?php e(t('the paymend is carried out on a secure connection.')); ?></p>
        <form action="<?php e($form_action); ?>" method="POST">
            <?php echo $payment_prepare->getHiddenFields(); ?>
            <input type="hidden" name="action_store_identifier" value="<?php e($action_store->getIdentifier()); ?>" />
            <input type="submit" name="submit" value="<?php e(t('pay the order now')); ?>" />
       </form>
    </li>
    <li><h2><?php e(t('Pay by bank transfer')); ?></h2>
        <p><?php e(t('You can choose to pay the order by bank transfer.')); ?></p>
        <p><?php e(t('Please notice that your order will first be processed when we have recieved your payment.')); ?></p>
        <p><?php e(t('As we have already registered your order, you do not need to do anymore for now. you will recieve the payment information on your e-mail with the order confirmation.')); ?></p>
        <p><a href="<?php e(url('/')); ?>"><?php e(t('back to the frontpage')); ?></a></p>
    </li>
</ul>

<p><?php e(t('if you have any problems or questions, do not hesitate to contact us.')); ?> <a href="mailto:support@intraface.dk">support@intraface.dk</a></p>
