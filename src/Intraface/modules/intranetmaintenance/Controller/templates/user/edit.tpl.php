<?php
$value = $context->getValues();
$address_values = $context->getValues();
?>
<h1><?php e('User'); ?></h1>

<ul>
	<li><a href="<?php e(url('../')); ?>"><?php e(__('Close')); ?></a></li>
</ul>

<?php echo $context->getUser()->error->view(); ?>

<form action="<?php e(url(null, array($context->subview()))); ?>" method="post">
<input type="hidden" value="<?php e($context->method); ?>" name="_method" />

<fieldset>
    <legend>Oplysninger om bruger</legend>
    <div class="formrow">
        <label for="name">E-mail</label>
        <input type="text" name="email" id="email" value="<?php if (isset($value['email'])) e($value["email"]); ?>" />
        <p style="clear:both;">Din e-mail er også dit brugernavn</p>
    </div>
    <div class="formrow">
        <label for="disabled">Deaktiveret</label>
        <input type="checkbox" name="disabled" id="disabled" value="1" <?php if (isset($value['disabled']) && $value["disabled"] == 1) print 'checked="checked"'; ?> />
    </div>

    <div class="formrow">
        <?php
        // hvis en bruger er valgt skal teksten vises, ellers ikke
        if ($context->query('id') != 0) {
            ?>
            <p>Du kan vælge at angive en ny adgangskode.</p>
            <?php
        }
        ?>
        <label for="password">Adgangskode</label>
        <input type="password" name="password" id="password" />
    </div>
    <div class="formrow">
        <label for="confirm_password">Bekræft adgangskode</label>
        <input type="password" name="confirm_password" id="confirm_password" />
    </div>
</fieldset>
<input type="submit" name="submit" value="<?php e(t('Save')); ?>" id="submit-save-password" />
or <a href="<?php e(url('../')); ?>">Cancel</a>

<?php

if ($context->getIntranet()->getId() != 0) {
    ?>
    <fieldset>
        <legend>Adresse oplysninger</legend>
        <div class="formrow">
            <label for="address_name">Navn</label>
            <input type="text" name="address_name" id="address_name" value="<?php if (isset($address_value["name"])) e($address_value["name"]); ?>" />
        </div>
        <div class="formrow">
            <label for="address">Adresse</label>
            <textarea name="address" id="address" rows="2"><?php if (isset($address_value["address"])) e($address_value["address"]); ?></textarea>
        </div>
        <div class="formrow">
            <label for="postcode">Postnr og by</label>
            <input type="text" name="postcode" id="postcode" value="<?php if (isset($address_value["postcode"])) e($address_value["postcode"]); ?>" size="4" />
            <input type="text" name="city" id="city" value="<?php if (isset($address_value["city"])) e($address_value["city"]); ?>" />
        </div>
        <div class="formrow">
            <label for="country">Land</label>
            <input type="text" name="country" id="country" value="<?php if (isset($address_value["country"])) e($address_value["country"]); ?>" />
        </div>
        <div class="formrow">
            <label for="address_email">E-mail</label>
            <input type="text" name="address_email" id="address_email" value="<?php if (isset($address_value["email"])) e($address_value["email"]); ?>" disabled="disabled" />
        </div>
        <div class="formrow">
            <label for="website">Hjemmeside</label>
            <input type="text" name="website" id="website" value="<?php if (isset($address_value["website"])) e($address_value["website"]); ?>" />
        </div>
        <div class="formrow">
            <label for="phone">Telefon</label>
            <input type="text" name="phone" id="phone" value="<?php if (isset($address_value["phone"])) e($address_value["phone"]); ?>" />
        </div>
    </fieldset>
    <input type="submit" name="submit" value="<?php e(t('Save')); ?>" id="submit-save-address" />
    or <a href="<?php e(url('../')); ?>">Cancel</a>
    <?php
}
?>


<input type="hidden" name="id" id="id" value="<?php e($context->getUser()->get("id")); ?>" />
<input type="hidden" name="intranet_id" value="<?php e($context->getIntranet()->get('id')); ?>" />

</form>
