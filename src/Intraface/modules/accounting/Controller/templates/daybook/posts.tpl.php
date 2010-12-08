<table class="stripe">
<caption><?php e(t('Posts in the daybook')); ?></caption>
<thead>
    <tr>
        <th><?php e(t('Date')); ?></th>
        <th><?php e(t('Voucher')); ?></th>
        <th><?php e(t('Text')); ?></th>
        <th><?php e(t('Account')); ?></th>
        <th><?php e(t('Debet')); ?></th>
        <th><?php e(t('Credit')); ?></th>
        <th><?php e(t('Reference')); ?></th>
        <th>&nbsp;</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($context->getPostsInDraft() as $p): ?>
    <tr>
        <td><?php e($p['date_dk']); ?></td>
        <td><a href="<?php e(url('../voucher/' . $p['voucher_id'])); ?>"><?php e($p['voucher_number']); ?></a></td>
        <td><?php e($p['text']); ?></td>
        <td><a href="<?php e(url('../account/' . $p['account_id'])); ?>"><?php e($p['account_number']); ?> <?php e($p['account_name']); ?></a></td>
        <td class="amount"><?php e(amountToOutput($p['debet'])); ?></td>
        <td class="amount"><?php e(amountToOutput($p['credit'])); ?></td>
        <td><?php if (!empty($p['reference'])) e($p['reference']); ?></td>
        <td><a href="<?php e(url('../voucher/' . $p['voucher_id'])); ?>"><?php e(t('See voucher')); ?></a></td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>

<?php if (round($context->getPost()->get('list_saldo'), 2) == 0.00): // this is a hack - can be removed when the database uses mindste enhed ?>
    <p class="advice"><a href="<?php e(url('state')); ?>">Bogfør posterne</a></p>
<?php else: ?>
    <p class="error">Kassekladden stemmer ikke. Der er en difference på <?php e(amountToOutput($post->get('list_saldo'))); ?>.</p>
<?php endif; ?>
