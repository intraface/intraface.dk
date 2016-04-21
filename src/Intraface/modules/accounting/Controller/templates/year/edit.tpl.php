<?php
$values = $context->getValues();
?>

<h1>Regnskabsår</h1>

<form action="<?php e($context->url(null, array($context->subview()))); ?>" method="post">
    <input type="hidden" name="id" value="<?php if (!empty($values['id'])) {
        e($values['id']);
} ?>" />

    <?php echo $context->getYear()->error->view(); ?>

    <fieldset>
        <legend>Oplysninger om regnskabsåret</legend>

        <div class="formrow">
            <label for="label">Navn</label>
            <input type="text" name="label" id="label" value="<?php if (!empty($values['label'])) {
                e($values['label']);
} ?>" />
        </div>

        <div class="formrow">
            <label for="from_date">Fra dato</label>
            <input type="text" name="from_date" id="from_date" value="<?php if (!empty($values['from_date_dk'])) {
                e($values['from_date_dk']);
} ?>" />
        </div>

        <div class="formrow">
            <label for="to_date">Til dato</label>
            <input type="text" name="to_date" id="to_date" value="<?php if (!empty($values['to_date_dk'])) {
                e($values['to_date_dk']);
} ?>" />
        </div>
        <br /> <!-- Needs to be present for the layout to display properly -->
        <div class="formrow">
            <label for="last_year_id">Sidste års regnskab</label>
            <select name="last_year_id" id="last_year_id">
                    <option value="0">Ingen</option>
                    <?php
                    foreach ($context->getYearGateway()->getList() as $y) :
                        if (!empty($values['id']) and $y['id'] == $values['id']) {
                            continue;
                        }
                        ?>
                        <option value="<?php e($y['id']); ?>"<?php if (!empty($values['last_year_id']) and $y['id'] == $values['last_year_id']) {
                            echo ' selected="selected"';
} ?>><?php e($y['label']); ?></option>
                    <?php                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             endforeach; ?>
            </select>
        </div>

        <div class="formrow">
            <label for="locked">Låst</label>
            <select name="locked" id="locked">
                    <option value="0"<?php if (!empty($values['locked']) and $values['locked'] == '0') {
                        echo ' selected="selected"';
} ?>>Nej</option>
                    <option value="1"<?php if (!empty($values['locked']) and $values['locked'] == '1') {
                        echo ' selected="selected"';
} ?>>Ja</option>
            </select>
        </div>

        <div class="formrow">
            <label for="vat">Moms</label>
            <input type="checkbox" name="vat" id="vat" value="1" <?php if (!empty($values['vat']) and $values['vat'] == 1) {
                echo ' checked="checked"';
}  ?>/>
        </div>

    <div style="clear:both;">
        <input type="submit" value="<?php e(t('Save')); ?>" name="submit" id="submit" />
        <a href="<?php e($context->url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </div>
    </fieldset>
</form>
