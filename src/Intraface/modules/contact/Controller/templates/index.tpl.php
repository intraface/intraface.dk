<h1><?php e(__('Contacts')); ?></h1>

<ul class="options">
	<li><a class="new" href="<?php e(url(null, array('create'))); ?>"><?php e(__('Create contact')); ?></a></li>
	<?php if ($context->getKernel()->getSetting()->get('user', 'contact.search') == 'hide' AND count($context->getContacts()) > 0): ?>
	<li><a href="<?php e(url(null, array('search' => 'view'))); ?>"><?php e(__('show search')); ?></a></li>
	<?php endif; ?>
	<li><a class="pdf" href="<?php e(url(null . '.pdf', array('use_stored' => 'true'))); ?>" target="_blank"><?php e(__('Pdf-labels')); ?></a></li>
	<li><a class="excel" href="<?php e(url(null . '.xls', array('use_stored' => 'true'))); ?>"><?php e(__('Excel', 'common')); ?></a></li>
	<li><a href="<?php e(url('sendemail', array('use_stored' => true))); ?>"><?php e(__('Email to contacts in search')); ?></a></li>
    <li><a href="<?php e(url('import')); ?>"><?php e(__('Import contacts')); ?></a></li>
</ul>

<?php if (!$context->getContact()->isFilledIn()): ?>

	<p><?php e(__('No contacts has been created')); ?>. <a href="<?php e(url(null, array('create'))); ?>"><?php e(__('Create contact')); ?></a>.</p>

<?php else: ?>


<?php if ($context->getKernel()->getSetting()->get('user', 'contact.search') == 'view'): ?>

<form action="<?php e(url()); ?>" method="get" class="search-filter">
	<fieldset>
		<legend><?php e(__('search', 'common')); ?></legend>

		<label for="query"><?php e(__('search for', 'common')); ?>
			<input name="query" id="query" type="text" value="<?php e($context->getContact()->getDBQuery()->getFilter('search')); ?>" />
		</label>

		<?php if (count($context->getUsedKeywords())): ?>
		<label for="keyword_id"><?php e(__('show with keywords', 'common')); ?>
			<select name="keyword_id" id="keyword_id">
				<option value=""><?php e(t('All')); ?></option>
				<?php foreach ($$context->getUsedKeywords() AS $k) { ?>
					<option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $context->getContact()->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
				<?php } ?>
			</select>
		</label>
		<?php endif; ?>
		<span><input type="submit" value="<?php e(t('go', 'common')); ?>" /></span>
		<!-- <a href="<?php e(url(null, array('search' => 'hide'))); ?>"><?php e(t('Hide search')); ?></a>  -->
	</fieldset>
</form>

<?php endif; ?>

<?php echo $context->getContact()->getDBQuery()->display('character'); ?>

<form action="<?php e(url()); ?>" method="post">
	<input type="hidden" value="put" name="_method" />
	<?php if (!empty($deleted)): ?>
		<p class="message">Du har slettet kontakter. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="Fortryd" /></p>
	<?php endif; ?>

	<table summary="<?php e(__('contacts')); ?>" class="stripe">
		<caption><?php e(__('contacts')); ?></caption>
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th><?php e(__('number')); ?></th>
				<th><?php e(__('name', 'address')); ?></th>
				<th><?php e(__('phone', 'address')); ?></th>
				<th><?php e(__('e-mail', 'address')); ?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5"><?php echo $context->getContact()->getDBQuery()->display('paging'); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($context->getContacts() as $c) { ?>
			<tr class="vcard">

				<td>
					<input type="checkbox" value="<?php e($c['id']); ?>" name="selected[]" />
				</td>
				<td><?php e($c['number']); ?></td>
				<td class="fn"><a href="<?php e(url($c['id'])); ?>"><?php e($c['name']); ?></a></td>
				<td class="tel"><?php e($c['phone']); ?></td>
				<td class="email"><?php e($c['email']); ?></td>
				<td class="options">
					<a class="edit" href="<?php e(url($c['id'], array('edit'))); ?>"><?php e(__('edit', 'common')); ?></a>
					<?php /*
					<a class="delete" href="index.php?delete=<?php e($c['id']); ?>&amp;use_stored=true"><?php e(__('delete', 'common')); ?></a> */ ?>
				</td>
			</tr>
			<?php } // end foreach ?>
		</tbody>
	</table>

	<select name="action">
		<option value=""><?php e(t('Choose')); ?></option>
		<option value="delete"><?php e(t('Delete')); ?></option>
	</select>

	<input type="submit" value="<?php e(t('Execute')); ?>" />

</form>

<?php endif; ?>