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
                    echo '>' . t($v) . '</option>';
                }
            ?>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Order confirmation - including warranty and right of cancellation')); ?></legend>
        <div>
        <label for="confirmation_text"><?php e(t('Text')); ?></label><br />
        <textarea name="confirmation" cols="80" rows="10"><?php  if(!empty($data['confirmation'])) e($data['confirmation']); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Webshop receipt')); ?></legend>
        <div>
        <label for="webshop_receipt"><?php e(t('text')); ?></label><br />
        <textarea name="receipt" cols="80" rows="10"><?php  if(!empty($data['receipt'])) e($data['receipt']); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Description')); ?></legend>
        <div>
        <label for="description"><?php e(t('text')); ?></label><br />
        <textarea name="description" cols="80" rows="10"><?php  if(!empty($data['description'])) e($data['description']); ?></textarea>
        </div>
    </fieldset>

    <p>
        <input type="submit" value="<?php e(t('save', 'common')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </p>

</form>