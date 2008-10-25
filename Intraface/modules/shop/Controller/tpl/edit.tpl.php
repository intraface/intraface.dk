<h1><?php e('Edit'); ?></h1>

<form action="<?php e(url(null)); ?>" method="post">
    <fieldset>
        <div class="formrow">
            <label for="name"><?php e(t('Name')); ?></label>
            <input type="text" name="name" id="name" value="<?php if (!empty($data['name'])) e($data['name']); ?>" />
        </div>

        <div class="formrow">
            <label for="identifier"><?php e(t('Identifier')); ?></label>
            <input type="text" name="identifier" id="identifier" value="<?php if (!empty($data['identifier'])) e($data['identifier']); ?>" />
        </div>

        <div class="formrow">
            <label for="terms-of-trade"><?php e(t('Terms of trade')); ?></label>
            <input type="text" name="terms_of_trade_url" id="terms-of-trade" value="<?php if (!empty($data['terms_of_trade_url'])) e($data['terms_of_trade_url']); ?>" />
        </div>
    </fieldset>

    <?php if (!empty($currencies)): ?>
        <fieldset>
            <legend><?php e(t('Currency')); ?></legend>
            <div class="formrow">
                <label for="default_currency_id"><?php e(t('Default currency')); ?></label>
                <select name="default_currency_id" id="default_currency_id">
                    <option value="0" >DKK (<?php e(t('standard', 'common')); ?>)</option>
                    <?php
                    foreach ($currencies AS $currency) { ?>
                        <option value="<?php e($currency->getId()); ?>"
                        <?php if (!empty($data['default_currency_id']) AND $currency->getId() == $data['default_currency_id']) echo ' selected="selected"'; ?>
                        ><?php e($currency->getType()->getIsoCode().' '.$currency->getType()->getDescription()); ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </fieldset>
    <?php endif; ?>

    <fieldset>
        <legend><?php e(t('What should be shown in the shop?')); ?></legend>
        <div class="formrow">
        <label><?php e(t('Show')); ?></label>
            <select name="show_online">
            <?php
                 foreach ($settings as $k=>$v) { ?>
                    <option value="<?php e($k); ?>"
                    <?php if (!empty($data['show_online']) AND $k == $data['show_online']) echo ' selected="selected"'; ?>
                    ><?php e(__($v)); ?></option>
                <?php }
            ?>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Order confirmation - including warranty and right of cancellation')); ?></legend>
        <div>
        <input type="checkbox" name="send_confirmation" value="1" <?php if (isset($data['send_confirmation']) AND $data['send_confirmation'] == 1) echo ' checked="checked"'; ?>/> <label for="send_confirmation"><?php e(t('Send e-mail confirmation when order is placed')); ?></label>
        </div>

        <div>
        <label for="confirmation_subject"><?php e(t('Subject')); ?></label>
        <input type="text" name="confirmation_subject" size="50" value="<?php  if (!empty($data['confirmation_subject'])) e($data['confirmation_subject']); ?>" />
        </div>
        <div>
        <label for="confirmation_text"><?php e(t('Text')); ?></label><br />
        <textarea id="confirmation_test" name="confirmation" cols="80" rows="10"><?php  if (!empty($data['confirmation'])) e($data['confirmation']); ?></textarea>
        </div>
        <div>
        <label for="description"><?php e(t('Confirmation greeting')); ?></label><br />
        <textarea name="confirmation_greeting" cols="50" rows="2"><?php  if (!empty($data['confirmation_greeting'])) e($data['confirmation_greeting']); ?></textarea>
        </div>
        <div>
        <input type="checkbox" name="confirmation_add_contact_url" value="1" <?php if (isset($data['confirmation_add_contact_url']) AND $data['confirmation_add_contact_url'] == 1) echo ' checked="checked"'; ?>/> <label for="add_customer_login_link"><?php e(t('Add login information so the customer can login to kundelogin.dk')); ?></label>
        </div>
    </fieldset>

 <fieldset>
        <legend><?php e(t('Include payment information in the order confirmation')); ?></legend>
        <div>
        <label for="payment_link"><?php e(t('Payment link')); ?></label>
        <input type="text" name="payment_link" size="50" value="<?php  if (!empty($data['payment_link'])) e($data['payment_link']); ?>" />
        </div>
        <div>
        <input type="checkbox" name="payment_link_add" value="1" <?php if (isset($data['payment_link_add']) AND $data['payment_link_add'] == 1) echo ' checked="checked"'; ?>/> <label for="payment_link_add"><?php e(t('Add payment information')); ?></label>
        </div>
    </fieldset>


    <fieldset>
        <legend><?php e(t('Webshop receipt')); ?></legend>
        <div>
        <label for="webshop_receipt"><?php e(t('text')); ?></label><br />
        <textarea name="receipt" cols="80" rows="10"><?php  if (!empty($data['receipt'])) e($data['receipt']); ?></textarea>
        </div>
    </fieldset>
<!--
    <fieldset>
        <legend><?php e(t('Description')); ?></legend>
        <div>
        <label for="description"><?php e(t('text')); ?></label><br />
        <textarea name="description" cols="80" rows="10"><?php  if (!empty($data['description'])) e($data['description']); ?></textarea>
        </div>
    </fieldset>
-->
    <p>
        <input type="submit" value="<?php e(t('save', 'common')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </p>

</form>