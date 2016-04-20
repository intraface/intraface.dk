<h1><?php e($title); ?></h1>

<?php echo $context->getError()->view(); ?>

<form action="<?php e(url(null, array($context->subview()))); ?>" method="POST">
<input type="hidden" name="_method" value="<?php e($context->method); ?>" />
<fieldset>
    <legend><?php e(t('Information'))?></legend>

    <div class="formrow">
        <label for="number"><?php e(t('Procurement number'))?></label>
        <input type="text" name="number" id="number" value="<?php if (!empty($values['number'])) {
            e($values["number"]);
} ?>" />
    </div>

    <div class="formrow">
        <label for="description"><?php e(t('Description'))?></label>
        <input type="text" name="description" id="description" value="<?php  if (!empty($values['description'])) {
            e($values["description"]);
} ?>" size="30" />
    </div>

    <div class="formrow">
        <label for="dk_invoice_date"><?php e(t('Invoice date'))?></label>
        <input type="text" name="dk_invoice_date" id="dk_invoice_date" value="<?php  if (!empty($values['dk_invoice_date'])) {
            e($values["dk_invoice_date"]);
} ?>" size="10" onBlur="fillDateFields();" />
    </div>

    <div class="formrow">
        <label for="dk_delivery_date"><?php e(t('Delivery date'))?></label>
        <input type="text" name="dk_delivery_date" id="dk_delivery_date" value="<?php  if (!empty($values['dk_delivery_date'])) {
            e($values["dk_delivery_date"]);
} ?>" size="10" />
    </div>

    <div class="formrow">
        <label for="dk_payment_date"><?php e(t('Payment date'))?></label>
        <input type="text" name="dk_payment_date" id="dk_payment_date" value="<?php  if (!empty($values['dk_payment_date'])) {
            e($values["dk_payment_date"]);
} ?>" size="10" />
    </div>

    <div class="formrow">
        <label for="from_region"><?php e(t('Buy from'))?></label>
        <select name="from_region" id="from_region">
            <?php $from_region = $gateway->getRegionTypes(); ?>
            <?php foreach ($from_region as $key => $region) : ?>
                <option value="<?php e($key); ?>" <?php if (!empty($values["from_region"]) and $values["from_region"] == $key) {
                    print("selected='selected'");
} ?> ><?php e(t($region)); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="formrow">
        <label for="vendor"><?php e(t('Vendor'))?></label>
        <input type="text" name="vendor" id="vendor" value="<?php  if (!empty($values['vendor'])) {
            e($values["vendor"]);
} ?>" size="30" />
    </div>
</fieldset>

<fieldset>
    <legend><?php e(t('Price'))?></legend>

    <div class="formrow">
        <label for="dk_price_items"><?php e(t('Price for items (excl shipment, fees etc.)'))?></label>
        <input type="text" name="dk_price_items" id="dk_price_items" value="<?php if (isset($values['dk_price_items'])) {
            e($values["dk_price_items"]);
} ?>" size="10" /> DKK <?php e(t('(excl vat)')); ?>
    </div>

    <div class="formrow">
        <label for="dk_vat"><?php e(t('Vat'))?></label>
        <input type="text" name="dk_vat" id="vat" value="<?php  if (isset($values['dk_vat'])) {
            e($values["dk_vat"]);
} ?>" size="10" /> DKK
    </div>

    <div class="formrow">
        <label for="dk_price_shipment_etc"><?php e(t('Price for shipment, fees etc.'))?></label>
        <input type="text" name="dk_price_shipment_etc" id="dk_price_shipment_etc" value="<?php  if (isset($values['dk_price_shipment_etc'])) {
            e($values["dk_price_shipment_etc"]);
} ?>" size="10" /> DKK <?php e(t('(excl vat')); ?>
    </div>

</fieldset>

<input type="submit" class="save" name="submit" value="<?php e(t('Save'))?>" />
<a href="<?php e(url(null)); ?>"><?php e(t('Cancel'))?></a>

<input type="hidden" name="id" value="<?php if (isset($values['id'])) {
    e($values['id']);
} ?>" />

</form>