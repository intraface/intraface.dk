<form action="<?php e(url(null)); ?>" method="post">
    <fieldset>
        <div class="formrow">
            <label for="type_iso_code"><?php e(t('Currency type')); ?></label>
            <select name="type_iso_code" id="type_iso_code">
            <?php
                 foreach ($currency_types AS $type) {
                    echo '<option value="'.$type->getIsoCode().'"';
                    if (!empty($data['selected_type_iso_code']) AND $type->getIsoCode() == $data['selected_type_iso_code']) echo ' selected="selected"';
                    echo '>' . $type->getIsoCode() . ' ' . $type->getDescription() . '</option>';
                }
            ?>
            </select>
        </div>
        
        
        
    </fieldset>

    
    <p>
        <input type="submit" value="<?php e(t('save', 'common')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </p>

</form>