<table class="stripe">
<caption>Poster i kassekladden</caption>
<thead>
    <tr>
        <th>Dato</th>
        <th>Bilag</th>
        <th>Tekst</th>
        <th>Konto</th>
        <th>Debet</th>
        <th>Kredit</th>
        <th>Reference</th>
        <th>&nbsp;</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($context->getPostsInDraft() as $p): ?>
    <tr>
        <td><?php e($p['date_dk']); ?></td>
        <td><a href="<?php e(url('../voucher/' . $p['voucher_id'])); ?>"><?php e($p['voucher_number']); ?></a></td>
        <td><?php e($p['text']); ?></td>
        <td><a href="<?php e(url('../account/' . $p['account_id'])); ?>"><?php e($p['account_name']); ?></a></td>
        <td class="amount"><?php e(amountToOutput($p['debet'])); ?></td>
        <td class="amount"><?php e(amountToOutput($p['credit'])); ?></td>
        <td><?php if (!empty($p['reference'])) e($p['reference']); ?></td>
        <td><a href="<?php e(url('../voucher/' . $p['voucher_id'])); ?>">Se bilag</a></td>
    </tr>
    <?php endforeach; ?>
</tbody>
</table>

<?php if (round($context->getPost()->get('list_saldo'), 2) == 0.00): // this is a hack - can be removed when the database uses mindste enhed ?>
    <p class="advice"><a href="<?php e(url('state')); ?>">Bogfør posterne</a></p>
<?php else: ?>
    <p class="error">Kassekladden stemmer ikke. Der er en difference på <?php e(amountToOutput($post->get('list_saldo'))); ?>.</p>
<?php endif; ?>
