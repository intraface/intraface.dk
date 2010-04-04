<h1><?php e(t('State')); ?></h1>

<ul class="options">
	<li><a href="<?php e(url('../../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php if (count($context->getYears()) == 0): ?>

	<p><?php e(t('You have to create a year, you can state in')); ?>. <a href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create year')); ?></a>.</p>

<?php else: ?>

<form action="<?php e(url(null)); ?>" method="post">
	<input type="hidden" name="_method" value="put" />

	<p><?php e(t('You have to choose the year, you want the invoice stated in.')); ?>

	<label for=""><?php e(t('Year')); ?>
	<select name="year_id">
		<option value=""><?php e(t('Choose')); ?></option>
		<?php foreach ($context->getYears() as $year): ?>
			<option value="<?php e($year['id']); ?>" <?php if ($context->getYear()->getId() == $year['id']) echo 'selected="selected"'; ?>><?php e($year['label']); ?></option>
		<?php endforeach; ?>
	</select>
	</label>

	<input type="submit" value="<?php e(t('Choose')); ?>" />
</form>

<?php endif; ?>
