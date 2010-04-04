<h1><?php e(t('Register payment for').' '.t($context->getType()).' #'.$context->getModel()->get('number')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
    <?php if ($context->getKernel()->user->hasModuleAccess('accounting')): ?>
    <li><a href="<?php e(url('state')); ?>"><?php e(t('State')); ?></a></li>
    <?php endif; ?>
</ul>

<?php echo $context->getPayment()->error->view(); ?>

<form method="post" action="<?php e(url()); ?>">
<fieldset>
    <legend><?php e(t('payment')); ?></legend>
    <div class="formrow">
        <label for="payment_date"><?php e(t('Date')); ?></label>
        <input type="text" name="payment_date" id="payment_date" value="<?php e(date("d-m-Y")); ?>" />
    </div>

    <div class="formrow">
        <label for="type"><?php e(t('Type')); ?></label>
        <select name="type" id="type">
            <?php
            $types = $context->getPayment()->getTypes();
            foreach ($types AS $key => $value) {
                ?>
                <option value="<?php e($key); ?>" <?php if ($key == 0) print("selected='selected'"); ?> ><?php e(t($value)); ?></option>
                <?php
            }
            ?>
        </select>
    </div>

    <div class="formrow">
        <label for="amount"><?php e(t('Amount')); ?></label>
        <input type="text" name="amount" id="amount" value="<?php e(number_format($context->getModel()->get("arrears"), 2, ",", ".")); ?>" />
    </div>
</fieldset>
<input type="submit" name="payment" value="<?php e(t('Register')); ?>" />
<a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
</form>
