<?php
require('../../include_first.php');

$kernel->module('email');
$contact_module = $kernel->useModule('contact');
$email_shared = $kernel->useShared('email');
$translation = $kernel->getTranslation('email');

if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$email = new Email($kernel, $_GET['delete']);
	if (!$email->delete()) {
		trigger_error(__('could not delete e-mail', 'email'), E_USER_ERROR);
	}
}

$email_object = new Email($kernel);
$email_object->getDBQuery()->useCharacter();
$email_object->getDBQuery()->defineCharacter('character', 'email.subject');
$email_object->getDBQuery()->usePaging('paging');
//$email->dbquery->storeResult('use_stored', 'emails', 'toplevel');

$emails = $email_object->getList();
$queue = $email_object->countQueue();

$page = new Intraface_Page($kernel);
$page->start(__('e-mails'));
?>
<h1><?php e(__('e-mails')); ?></h1>

<?php if (count($emails) == 0): ?>

	<p><?php e(__('no e-mails has been sent')); ?></p>

<?php else: ?>

	<?php
		//til test af sentThisHour(). Bør ikke vises når vi er i produktion.
		//echo $email_object->sentThisHour();
	?>

	<?php if ($queue > 0): ?>
		<p><?php e(__('e-mails are in queue - the will be sent soon')); ?></p>
	<?php endif; ?>

	<?php echo $email_object->getDBQuery()->display('character'); ?>

	<table>
	<caption><?php e(__('e-mails')); ?></caption>
	<thead>
	<tr>
		<th><?php e(__('subject')); ?></th>
		<th><?php e(__('contact')); ?></th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($emails AS $email): ?>
	<tr>
		<td><a href="<?php e($email_shared->getPath()); ?>email.php?id=<?php e($email['id']); ?>"><?php e($email['subject']); ?></a></td>
		<td><a href="<?php e($contact_module->getPath()); ?>contact.php?id=<?php e($email['contact_id']); ?>"><?php e($email['contact_name']); ?></a></td>
		<td>
		<?php if (!empty($email['status']) AND $email['status'] != 'sent'): ?>
			<a class="edit" href="<?php e($email_shared->getPath()); ?>edit.php?id=<?php e($email['id']); ?>"><?php e(__('edit', 'common')); ?></a>
			<a class="delete" href="index.php?delete=<?php e($email['id']); ?>"><?php e(__('delete', 'common')); ?></a>
		<?php else: ?>
			<?php e(__($email['status'], 'email')); ?>
		<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>

	<?php echo $email_object->getDBQuery()->display('paging'); ?>

<?php endif; ?>

<?php
$page->end();
?>