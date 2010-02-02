<h1><?php e(t('register depreciation for').' '.t($context->getType()).' #'.$context->getModel()->get('number')); ?></h1>

<div class="message-dependent">
    <?php e(t('if the reason why you have not recieved the full amount of money is because the customer has returned parts of the sold goods, or you have agreed to reduce the price, you should rather send a credit note, to avoid paying the vat. depreciation should be used when you are not able to collect the money that you expected to get.')); ?>
</div>

<?php echo $context->getDepreciation()->error->view(); ?>

<form method="post" action="<?php e(url()); ?>">
<fieldset>
    <legend><?php e(t('depreciation')); ?></legend>

    <input type="hidden" name="id" value="<?php e($context->getModel()->get('id')); ?>" />
    <div class="formrow">
        <label for="payment_date">Dato</label>
        <input type="text" name="payment_date" id="payment_date" value="<?php e(date("d-m-Y")); ?>" />
    </div>

    <div class="formrow">
        <label for="amount"><?php e(t('Amount')); ?></label>
        <input type="text" name="amount" id="amount" value="<?php e(number_format($context->getModel()->get("arrears"), 2, ",", ".")); ?>" />
    </div>
</fieldset>
<input type="submit" name="depreciation" value="<?php e(t('Register')); ?>" />
    <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
</form>
