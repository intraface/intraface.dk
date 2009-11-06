<h1>Bilag #<?php e($context->getVoucher()->get('number')); ?> p� <?php e($context->getYear()->get('label')); ?></h1>

<ul class="options">
	<li><a class="edit" href="<?php e(url(null, array('edit'))); ?>"><?php e(__('Edit')); ?></a></li>
	<li><a href="<?php e(url('../')); ?>"><?php e(__('Close')); ?></a></li>
</ul>

<p><?php e($context->getVoucher()->get('text')); ?></p>

<?php $reference = $context->getVoucher()->get('reference'); if (!empty($reference)): ?>
	<p><strong>Reference</strong>: <?php e($context->getVoucher()->get('reference')); ?></p>
<?php endif; ?>

<?php if (count($context->getPosts()) == 0): ?>
	<p class="warning">Der er ikke nogen poster p� bilaget. <a href="post_edit.php?voucher_id=<?php e($context->getVoucher()->get('id')); ?>">Indtast poster</a>.</p>
<?php else: ?>
	<form action="<?php e(url(null)); ?>" method="post">
	<table>
		<caption>Poster</caption>
		<thead>
		<tr>
			<th></th>
			<th>Dato</th>
			<th>Tekst</th>
			<th>Konto</th>
			<th>Debet</th>
			<th>Kredit</th>
			<th></th>
		</tr>
		</thead>
	<?php foreach ($context->getPosts() as $post): ?>
		<tr>
			<td><input type="checkbox" name="selected[]" value="<?php e($post['id']); ?>" /></td>
			<td><?php e($post['date_dk']); ?></td>
			<td><?php e($post['text']); ?></td>
			<td><a href="account.php?id=<?php e($post['account_id']); ?>"><?php e($post['account_name']); ?></a></td>
			<td class="amount"><?php e(amountToOutput($post['debet'])); ?></td>
			<td class="amount"><?php e(amountToOutput($post['credit'])); ?></td>
			<td class="options">
				<?php if ($post['stated'] == 0): $not_all_stated = true; ?>
				<a class="edit" href="<?php e(url('post/'. $post['id'] . '/edit')); ?>">Ret</a>
				<a class="delete" href="<?php e(url('post/'.$post['id'], array('delete'))); ?>">Slet</a>
				<?php else: ?>
				Bogf�rt
				<?php endif; ?>
			</td>
		</tr>

	<?php endforeach; ?>
	</table>

	<select name="action">
       <option value=""><?php e(t('Choose...', 'common'))?></option>
       <option value="counter_entry"><?php e(t('Create counter entry'))?></option>
    </select>
    <input name="id" type="hidden" value="<?php e($context->getVoucher()->get('id')); ?>" />
    <input type="submit" value="<?php e(t('go', 'common')); ?>" />

    </form>

	<p><a href="<?php e(url('post', array('create'))); ?>">Indtast poster</a></p>

	<?php if (round($context->getVoucher()->get('saldo'), 2) <> 0.00): ?>
		<p class="error">Bilaget stemmer ikke. Der er en difference p� <?php e(round($context->getVoucher()->get('saldo'), 2)); ?> kroner.</p>
	<?php elseif (isset($not_all_stated)): ?>
	<form action="<?php e(url(null)); ?>" method="post">
		<input name="id" type="hidden" value="<?php e($context->getVoucher()->get('id')); ?>" />
		<fieldset>
			<legend>Bogf�r bilaget</legend>
			<input type="submit" name="state" value="Bogf�r" />
		</fieldset>
	</form>
	<?php endif; ?>
<?php endif; ?>


<?php if (count($context->getFiles()) > 0): ?>

	<table>
		<caption>Filer</caption>

		<thead>
		<tr>
			<th>Filnavn</th>
			<th></th>

		</tr>
		</thead>
		<tbody>
		<?php foreach ($context->getFiles() as $file): ?>
			<tr>
				<td><a target="_blank" href="<?php e($file['file_uri']); ?>"><?php e($file['description']); ?></a></td>
				<td class="options">
					<a class="delete" href="<?php e(url(null)); ?>?delete_file=<?php e($file['id']); ?>&amp;id=<?php e($context->getVoucher()->get('id')); ?>">Slet</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>

<form method="post" action="<?php e(url(null)); ?>"  enctype="multipart/form-data">
	<input name="id" type="hidden" value="<?php e($context->getVoucher()->get('id')); ?>" />
	<fieldset>
		<legend>Upload fil til bilaget</legend>
        <?php
        $context->getVoucherFile()->error->view();
        $context->getFilehandlerHtml()->printFormUploadTag('file_id', 'new_file', 'choose_file');
        ?>
	</fieldset>
	<p><input type="submit" value="Upload" /></p>
</form>
