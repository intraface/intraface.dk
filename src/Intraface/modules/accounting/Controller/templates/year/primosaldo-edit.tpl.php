
<h1>Rediger primosaldo <?php e($year->get('label')); ?></h1>

<ul class="options">
	<li><a href="<?php e(url('../')); ?>"><?php e(t('Back to year')); ?></a></li>
	<li><a href="<?php e(url(null)); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php echo $account->error->view(); ?>

<form method="post" action="<?php e(url(null, array($context->subview()))); ?>">
	<input type="hidden" name="_method" value="put" />
	<fieldset>
		<legend>Oplysninger til primosaldo</legend>
		<table>
			<thead>
			<tr>
				<th>Kontonummer</th>
				<th>Kontonavn</th>
				<th>Debet</th>
				<th>Credit</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($accounts AS $account): ?>
				<tr>
					<td>
						<input type="hidden" name="id[]" id="id<?php e($account['id']); ?>" value="<?php e($account['id']); ?>" />
						<?php e($account['number']); ?>
					</td>
					<td><?php e($account['name']); ?></td>
					<td>
						<input type="text" name="debet[]" id="debet<?php e($account['id']); ?>" value="<?php e(amountToForm($account['primosaldo_debet'])); ?>" />
					</td>
					<td>
						<input type="text" name="credit[]" id="credit<?php e($account['id']); ?>" value="<?php e(amountToForm($account['primosaldo_credit'])); ?>" />
					</td>
				</tr>
				<?php
					$total_debet += $account['primosaldo_debet'];
					$total_credit += $account['primosaldo_credit'];
				?>

			<?php endforeach; ?>
				<tr>
					<td></td>
					<td>
						<strong>Balance</strong>
						<?php
							if ($total_debet != $total_credit) {
								echo '<strong style="color: red;">Balancen stemmer ikke</strong>';
							}
						?>
					</td>
					<td><strong><?php e(amountToOutput($total_debet)); ?></strong></td>
					<td><strong><?php e(amountToOutput($total_credit)); ?></strong></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<div>
		<input type="submit" name="submit" value="<?php e(t('Save')); ?>" class="confirm" />
		<a href="<?php e(url(null)); ?>"><?php e(t('Cancel')); ?></a>
	</div>
</form>