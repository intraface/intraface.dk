<h1><?php e('Edit'); ?></h1>

<form action="<?php e(url(null)); ?>" method="post">
    <fieldset>
        <label for="name"><?php e(t('Name')); ?></label>
        <input type="text" name="name" id="name" value="<?php if(!empty($data['name'])) e($data['name']); ?>" />

        <br />

        <label for="identifier"><?php e(t('Identifier')); ?></label>
        <input type="text" name="identifier" id="identifier" value="<?php if(!empty($data['identifier'])) e($data['identifier']); ?>" />

    </fieldset>

    <fieldset>
        <legend><?php e(t('What should be shown in the shop?')); ?></legend>
        <div class="formrow">
        <label><?php e(t('Show')); ?></label>
            <select name="show_online">
            <?php
                 foreach ($settings AS $k=>$v) {
                    echo '<option value="'.$k.'"';
                    if (!empty($data['show_online']) AND $k == $data['show_online']) echo ' selected="selected"';
                    echo '>' . __($v) . '</option>';
                }
            ?>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Order confirmation - including warranty and right of cancellation')); ?></legend>
        <div>
        <div>
        <label for="confirmation_subject"><?php e(t('Subject')); ?></label>
        <input type="text" name="confirmation_subject" size="50" value="<?php  if(!empty($data['confirmation_subject'])) e($data['confirmation_subject']); ?>" />
        </div>
        <label for="confirmation_text"><?php e(t('Text')); ?></label><br />
        <textarea id="confirmation_test" name="confirmation" cols="80" rows="10"><?php  if(!empty($data['confirmation'])) e($data['confirmation']); ?></textarea>
        </div>
        <div>
        <label for="description"><?php e(t('Confirmation greeting')); ?></label><br />
        <textarea name="confirmation_greeting" cols="50" rows="2"><?php  if(!empty($data['confirmation_greeting'])) e($data['confirmation_greeting']); ?></textarea>
        </div>
        <input type="checkbox" name="confirmation_add_contact_url" value="1" <?php if (isset($data['confirmation_add_contact_url']) AND $data['confirmation_add_contact_url'] == 1) echo ' checked="checked"'; ?>/> <label for="add_customer_login_link">Tilf�j logininformation s� kunden kan logge ind i kundelogin.dk</label>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Webshop receipt')); ?></legend>
        <div>
        <label for="webshop_receipt"><?php e(t('text')); ?></label><br />
        <textarea name="receipt" cols="80" rows="10"><?php  if(!empty($data['receipt'])) e($data['receipt']); ?></textarea>
        </div>
    </fieldset>
<!--
    <fieldset>
        <legend><?php e(t('Description')); ?></legend>
        <div>
        <label for="description"><?php e(t('text')); ?></label><br />
        <textarea name="description" cols="80" rows="10"><?php  if(!empty($data['description'])) e($data['description']); ?></textarea>
        </div>
    </fieldset>
-->
    <p>
        <input type="submit" value="<?php e(t('save', 'common')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </p>

</form>