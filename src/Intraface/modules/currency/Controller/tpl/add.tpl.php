<form action="<?php e(url(null)); ?>" method="post">
    <fieldset>
        <div class="formrow">
            <label for="type_iso_code"><?php e(t('Currency type')); ?></label>
            <select name="type_iso_code" id="type_iso_code">
            <?php foreach ($currency_types as $type): ?>
                <option value="<?php e($type->getIsoCode()); ?>"
                <?php if (!empty($data['selected_type_iso_code']) AND $type->getIsoCode() == $data['selected_type_iso_code']) echo ' selected="selected"'; ?>
                ><?php e($type->getIsoCode() . ' ' . $type->getDescription()); ?></option>
            <?php endforeach; ?>
            </select>
        </div>



    </fieldset>


    <p>
        <input type="submit" value="<?php e(t('save')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </p>

</form>