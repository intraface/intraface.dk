<h1><?php e(t('Register payment for').' '.t($context->getType()).' #'.$context->getModel()->get('number')); ?></h1>

<?php echo $context->getPayment()->error->view(); ?>

<form method="post" action="<?php e(url()); ?>">
<fieldset>
    <legend><?php e(t('payment')); ?></legend>
    <div class="formrow">
        <label for="payment_date">Dato</label>
        <input type="text" name="payment_date" id="payment_date" value="<?php e(date("d-m-Y")); ?>" />
    </div>

    <div class="formrow">
        <label for="type">Type</label>
        <select name="type" id="type">
            <?php
            $types = $context->getPayment()->getTypes();
            foreach ($types AS $key => $value) {
                ?>
                <option value="<?php e($key); ?>" <?php if ($key == 0) print("selected='selected'"); ?> ><?php e(__($value)); ?></option>
                <?php
            }
            ?>
        </select>
    </div>

    <div class="formrow">
        <label for="amount">Beløb</label>
        <input type="text" name="amount" id="amount" value="<?php e(number_format($context->getModel()->get("arrears"), 2, ",", ".")); ?>" />
    </div>
</fieldset>
<input type="submit" name="payment" value="Registrér" />
<a href="<?php e(url('../')); ?>"><?php e(t('Cancel', 'common')); ?></a>
</form>
