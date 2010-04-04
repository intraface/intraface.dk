<h1><?php e(t('Payments')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

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
<?php foreach ($payments as $payment): ?>
    <tr>

        <td><?php e($payment['payment_for']); ?> #<?php e($payment['payment_for_id']); ?></td>
        <td><?php e($payment['type']); ?></td>
        <td><?php e($payment['description']); ?></td>
        <td><?php e($payment['dk_payment_date']); ?></td>
        <td><?php e($payment['amount']); ?></td>
        <?php if ($payment['is_stated']): ?>
        <td><?php e(t('Yes')); ?></td>
        <?php else: ?>
        <td><a href="<?php e(url($payment['id'] . '/state')); ?>"><?php e(t('No')); ?></a></td>
        <?php endif; ?>

    </tr>
<?php endforeach; ?>
</table>
