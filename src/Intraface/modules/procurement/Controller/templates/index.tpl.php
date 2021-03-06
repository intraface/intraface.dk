<h1><?php e(t('Procurement'));
if (!empty($contact) and is_object($contact)) {
    e(": ".$contact->address->get('name'));
} ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
    <li><a class="new" href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create')); ?></a></li>
    <?php if (!empty($contact) and is_object($contact)) : ?>
        <li><a href="<?php e(t('../')); ?>"><?php e(t('Go to contact')); ?></a></li>
    <?php endif; ?>
</ul>

<?php if (!$gateway->any()) : ?>
    <p><?php e(t('No procurements has been created')); ?>. <a href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create procurement')); ?></a>.</p>
<?php else : ?>

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

    <table class="stripe">
        <caption><?php e(t('Procurement')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('No.')); ?></th>
                <th><?php e(t('Description')); ?></th>
                <th><?php e(t('From')); ?></th>
                <th><?php e(t('Invoice date')); ?></th>
                <th><?php e(t('Delivery date')); ?></th>
                <th><?php e(t('Payment date')); ?></th>
                <th><?php e(t('Price')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($procurements as $procurement) : ?>
                <tr>
                    <td><?php e($procurement["number"]); ?></td>
                    <td><a href="<?php e(url($procurement["id"])); ?>"><?php e($procurement["description"]); ?></a></td>
                    <td>
                        <?php if ($context->getKernel()->user->hasModuleAccess('contact') && $procurement["contact_id"] != 0) : ?>
                            <?php $ModuleContact = $context->getKernel()->getModule('contact'); ?>
                            <a href="<?php e($ModuleContact->getPath().$procurement["contact_id"]); ?>"><?php e($procurement["contact"]); ?></a>
                        <?php else : ?>
                            <?php e($procurement["vendor"]); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php e($procurement["dk_invoice_date"]); ?></td>
                    <td>
                        <?php if ($procurement["status"] == "recieved" || $procurement["status"] == "canceled") : ?>
                            <?php e(t(ucfirst($procurement["status"]))); ?> <?php e($procurement["dk_delivery_date"]); ?>
                        <?php elseif ($procurement["delivery_date"] != "0000-00-00") : ?>
                            <?php e($procurement["dk_delivery_date"]); ?>
                        <?php else : ?>
                            <?php e(t('Not given')); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($procurement["status"] == "canceled") : ?>
                            <?php e("-"); ?>
                        <?php elseif ($procurement['paid_date'] != '0000-00-00') : ?>
                            <?php e(t('Paid')); ?> <?php e($procurement['dk_paid_date']); ?>
                        <?php elseif ($procurement["payment_date"] != "0000-00-00") : ?>
                            <?php e($procurement["dk_payment_date"]); ?>
                        <?php else : ?>
                            <?php e(t('Not given')); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php e(number_format($procurement["total_price"], 2, ',', '.')); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php echo $gateway->getDBQuery()->display('paging'); ?>

<?php endif; ?>