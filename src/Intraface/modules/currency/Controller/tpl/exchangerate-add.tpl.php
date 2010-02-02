<form action="<?php e(url()); ?>" method="post">
    <fieldset>
        <div class="formrow">
            <label for="rate"><?php e(t('Rate')); ?></label>
            <input type="text" name="rate" id="rate" value="" />
        </div>
    </fieldset>
    <p>
        <input type="submit" value="<?php e(t('save')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </p>
</form>