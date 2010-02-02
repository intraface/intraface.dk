<div id="colOne">

<div class="box">

	<h1><?php e(t('reminder')); ?>: <?php e($reminder->get('subject')); ?></h1>

	<ul class="options">
		<li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
		<li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
	</ul>

</div>

<form method="post" action="<?php e(url()); ?>">
	<?php if ($reminder->get('status') == 'created'): ?>
			<input type="submit" value="<?php e(t('mark as seen')); ?>" name="mark_as_seen" class="confirm" title="<?php e(t('This will mark the reminder as seen')); ?>" />
			<input type="submit" value="<?php e(t('cancel')); ?>" name="cancel" class="confirm" title="<?php e(t('This will cancel the reminder')); ?>" />

			<?php e(t('postpone')); ?>:
			<input type="submit" value="<?php e(t('1 day')); ?>" name="postpone_1_day" class="confirm" title="<?php e(t('This will postpone the reminder with 1 day')); ?>" />
			<input type="submit" value="<?php e(t('1 week')); ?>" name="postpone_1_week" class="confirm" title="<?php e(t('This will postpone the reminder with 1 week')); ?>" />
			<input type="submit" value="<?php e(t('1 month')); ?>" name="postpone_1_month" class="confirm" title="<?php e(t('This will postpone the reminder with 1 month')); ?>" />
			<input type="submit" value="<?php e(t('1 year')); ?>" name="postpone_1_year" class="confirm" title="<?php e(t('This will postpone the reminder with 1 year')); ?>" />
			<a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('other')); ?></a>

	<?php endif; ?>
</form>

<?php echo $reminder->error->view(); ?>

<p><?php autohtml($reminder->get('description')); ?></p>

<table>
	<caption><?php e(t('reminder information')); ?></caption>
	<tbody>
	<tr>
		<th><?php e(t('reminder date')); ?></th>
		<td class="date"><?php e($reminder->get('dk_reminder_date')); ?></td>
	</tr>

	<tr>
		<th><?php e(t('status')); ?></th>
		<td><?php e($reminder->get('status')); ?></td>
	</tr>

	<tr>
		<th><?php e(t('created date')); ?></th>
		<td class="date"><?php e($reminder->get('dk_date_created')); ?></td>
	</tr>
    </tbody>
</table>
</div>
<div id="colTwo">


</div>
