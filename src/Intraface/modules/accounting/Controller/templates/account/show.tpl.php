<h1><?php e($context->getAccount()->get('number')); ?>: <?php e($context->getAccount()->get('name')); ?></h1>

<ul class="options">
	<li><a href="<?php e(url(null, array('edit'))); ?>">Ret</a></li>
	<li><a href="<?php e(url('../')); ?>">Luk</a></li>
</ul>

<!-- Følgende bør vises her, men kunne skjules med en indstilling
<table>
	<tr>
		<th rowspan="2">Beskrivelse</th>
		<td rowspan="2"><?php e($context->getAccount()->get('comment')); ?></td>
	</tr>
	<tr>
		<th>Type</th>
		<td><?php e($context->getAccount()->get('type')); ?></td>	</tr>
	<tr>
		<th>Moms</th>
		<td><?php e($context->getAccount()->get('vat')); ?></td>
	</tr>
</table>
-->

<p><?php e(t('vat')); ?>: <?php e(t($context->getAccount()->get('vat'))); ?> <?php if ($context->getAccount()->get('vat') != 'none'): ?><?php e(number_format($context->getAccount()->get('vat_percent'), 2, ',', '.').'%'); ?><?php endif; ?></p>

<?php if (!empty($posts) AND is_array($posts) AND count($posts) > 0) { ?>
	<table>
		<caption>Konti</caption>
		<thead>
			<tr>
					<th>Dato</th>
					<th>Bilag</th>
					<th>Tekst</th>
					<th>Debet</th>
					<th>Kredit</th>
					<th>Saldo</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($posts AS $post) { $saldo = $saldo + $post['debet'] - $post['credit']; ?>
			<tr>
				<td><?php if (isset($post['dk_date'])) e($post['dk_date']); ?></td>
				<td><?php if (isset($post['voucher_id'])): ?><a href="voucher.php?id=<?php e($post['voucher_id']); ?>"><?php e($post['voucher_number']); ?></a><?php endif; ?></td>
				<td><?php e($post['text']); ?></td>
				<td class="amount"><?php e(amountToOutput($post['debet'])); ?></td>
				<td class="amount"><?php e(amountToOutput($post['credit'])); ?></td>
				<td class="amount"><?php e(amountToOutput($saldo)); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

<?php } else { ?>
	<p>Der er endnu ikke bogført nogle poster på denne konto.</p>
<?php } // else ?>