<h1><?php e(t('Choose contact')); ?></h1>

<?php echo $context->getContact()->error->view(); ?>

<?php if (!$context->getContact()->isFilledIn()): ?>

	<p><?php e(t('No contacts has been created')); ?>. <a href="<?php e(url(null, array('add' => 1))); ?>"><?php e(t('Create contact')); ?></a>.</p>

<?php else: ?>
    <ul class="options">
        <li><a href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create contact')); ?></a></li>
        <?php if (isset($_GET['last_contact_id']) && intval($_GET['last_contact_id']) != 0): ?>
        <li><a href="<?php e(url(null, array('contact_id' => $_GET['last_contact_id']))); ?>"><?php e(t('Show chosen')); ?></a></li>
        <?php endif; ?>

    </ul>

    <form action="<?php e(url(null)); ?>" method="get" class="search-filter">
	<fieldset>
		<legend><?php e(t('Search')); ?></legend>

		<label for="query"><?php e(t('Search for')); ?>
			<input name="query" id="query" type="text" value="<?php e($context->getContact()->getDBQuery()->getFilter('search')); ?>" />
		</label>

		<?php if (count($context->getUsedKeywords()) > 0): ?>
		<label for="keyword_id"><?php e(t('Show with keywords')); ?>
			<select name="keyword_id" id="keyword_id">
				<option value=""><?php e(t('None')); ?></option>
				<?php foreach ($used_keywords AS $k) { ?>
					<option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $context->getContact()->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
				<?php } ?>
			</select>
		</label>
		<?php endif; ?>

		<span><input type="submit" value="<?php e(t('Search')); ?>" /></span>
	</fieldset>
    </form>

    <?php echo $context->getContact()->getDBQuery()->display('character'); ?>

    <form action="<?php e(url()); ?>" method="post">
    	<input type="hidden" name="_method" value="put" />
    	<table summary="Kontakter" class="stripe">
    		<caption><?php e(t('Contacts')); ?></caption>
    		<thead>
    			<tr>
    				<th>&nbsp;</th>
    				<th><?php e(t('No.')); ?></th>
    				<th><?php e(t('Name')); ?></th>
    				<th><?php e(t('Email')); ?></th>
    			</tr>
    		</thead>
    		<tfoot>
    			<tr>
    				<td colspan="4"><?php echo $context->getContact()->getDBQuery()->display('paging'); ?></td>
    			</tr>
    		</tfoot>
    		<tbody>
    			<?php foreach ($context->getContacts() as $c) { ?>
    			<tr>
    				<td>
    					<input id="contact_<?php e($c['id']); ?>" type="radio" value="<?php e($c['id']); ?>" name="selected" />
    				</td>
    				<td><?php e($c['number']); ?></td>
    				<td><?php e($c['name']); ?></td>
    				<td><?php e($c['email']); ?></td>
    			</tr>
    			<?php } // end foreach
                ?>
    		</tbody>
    	</table>

    	<input type="submit" name="submit" value="<?php e(__('Choose')); ?>" />
    	<a href="<?php e(url('../../')); ?>"><?php e(t('Cancel')); ?></a>
    </form>

<?php endif; ?>
