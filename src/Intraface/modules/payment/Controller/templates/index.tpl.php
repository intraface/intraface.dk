<h1><?php e(t('Payments')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

   <form method="get" action="<?php e(url()); ?>">
    <fieldset>
        <legend><?php e(t('Search')); ?></legend>
        <label><?php e(t('Text')); ?>
            <input type="text" name="text" value="<?php e($gateway->getDBQuery()->getFilter("text")); ?>" />
        </label>
        <label><?php e(t('Status')); ?>
        <select name="status">
            <?php
            $status_list = array(
                '-1' => 'All',
                '-2' => 'Open',
                '0' => 'Created',
                '1' => 'Recieved',
                '3' => 'Canceled'
            );
            ?>
            <?php foreach ($status_list as $status => $text) : ?>
                <option value="<?php e($status); ?>" <?php if ($gateway->getDBQuery()->getFilter("status") == $status) {
                    echo ' selected="selected"';
}?>><?php e(t($text))?></option>
            <?php endforeach; ?>
         </select>
        </label>
        <label><?php e(t('From date')); ?>
            <input type="text" name="from_date" id="date-from" value="<?php e($gateway->getDBQuery()->getFilter("from_date")); ?>" /> <span id="calender"></span>
        </label>
        <label><?php e(t('To date')); ?>
            <input type="text" name="to_date" value="<?php e($gateway->getDBQuery()->getFilter("to_date")); ?>" />
        </label>
        <label><?php e(t('Not stated')); ?>
            <input type="checkbox" name="not_stated" value="1" <?php if ($gateway->getDBQuery()->getFilter("not_stated") == 1) {
                echo ' checked="checked"';
} ?> />
        </label>
        <span>
        <input type="submit" name="search" value="<?php e(t('Find')); ?>" />
        </span>
    </fieldset>
    </form>

<table>
    <caption><?php e(t('Payments')); ?></caption>
    <tr>
        <th><?php e(t('Payment for')); ?></th>
        <th><?php e(t('Type')); ?></th>
        <th><?php e(t('Description')); ?></th>
        <th><?php e(t('Payment date')); ?></th>
        <th><?php e(t('Amount')); ?></th>
        <th><?php e(t('Is stated')); ?></th>
    </tr>
<?php foreach ($payments as $payment) : ?>
    <tr>

        <td><?php e($payment['payment_for']); ?> #<?php e($payment['payment_for_id']); ?></td>
        <td><?php e($payment['type']); ?></td>
        <td><?php e($payment['description']); ?></td>
        <td><?php e($payment['dk_payment_date']); ?></td>
        <td><?php e($payment['amount']); ?></td>
        <?php if ($payment['is_stated']) : ?>
        <td><?php e(t('Yes')); ?></td>
        <?php else : ?>
        <td><a href="<?php e(url($payment['id'] . '/state')); ?>"><?php e(t('No')); ?></a></td>
        <?php endif; ?>

    </tr>
<?php endforeach; ?>
</table>

<?php echo $gateway->getDBQuery()->display('paging'); ?>
