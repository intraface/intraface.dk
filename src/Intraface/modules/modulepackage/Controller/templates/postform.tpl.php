<h1><?php e(t('Pay your order'))?></h1>

<form action="<?php $url = $form->getAction(); if(substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://'): echo ''; else: echo $url; endif; ?>" method="post" autocomplete="off" id="payment_details">
    <?php echo $form->getHiddenFields(); ?>
    <input type="hidden" name="order_identifier" value="<?php e($_POST['order_identifier']); ?>" />

    <fieldset class="clearfix">
        <legend><span><?php e(t('Card information')); ?></span></legend>
        <div class="formrow">
            <label for="cardnum"><?php e(t('Card number')); ?></label>
            <input type="text" maxlength="16" size="19" name="<?php echo $form->getCardNumberFieldName(); ?>" id="cardnum" />
        </div>
        <div class="formrow">
            <label for="month"><?php e(t('Expire date')); ?></label>

            <select name="<?php echo $form->getExpireMonthFieldName(); ?>" class="s4-select" id="month">
                <?php
                $month_array = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
                foreach($month_array as $month) {
                    echo '<option value="'.$month.'">'.$month.'</option>';
                }
                ?>
            </select>
            <strong class="slash">/</strong>
            <select name="<?php echo $form->getExpireYearFieldName(); ?>" class="s4-select" id="year">
                <?php
                $current_year = date('Y');
                for($i = $current_year; $i < $current_year + 16; $i++) {
                    echo '<option value="'.substr($i, -2).'">'.substr($i, -2).'</option>';
                }
                ?>
            </select>
        </div>
        <div class="formrow">
            <label for="cvd"><?php e(t('Security no.')); ?></label>
            <input type="text" maxlength="3" size="3" name="<?php echo $form->getSecurityNumberFieldName(); ?>" id="cvd" />
        </div>
        <div>
            <input class="godkend" name="pay" type="submit" id="submit" value="<?php e(t('Pay')); ?>" />
        </div>
    </fieldset>
</form>
