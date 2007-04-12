<?php
require('../../include_first.php');

$kernel->module('email');
$contact_module = $kernel->useModule('contact');
$email_shared = $kernel->useShared('email');
$translation = $kernel->getTranslation('email');

if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$email = new Email($kernel, $_GET['delete']);
	if (!$email->delete()) {
		trigger_error($translation->get('could not delete e-mail', 'email'), E_USER_ERROR);
	}
}

$email_object = new Email($kernel);
$email_object->createDBQuery();
$email_object->dbquery->useCharacter();
$email_object->dbquery->defineCharacter('character', 'email.subject');
$email_object->dbquery->usePaging('paging');
//$email->dbquery->storeResult('use_stored', 'emails', 'toplevel');

$emails = $email_object->getList();
$queue = $email_object->countQueue();

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('e-mails')));
?>
<h1><?php echo safeToHtml($translation->get('e-mails')); ?></h1>

<?php if (count($emails) == 0): ?>

	<p><?php echo safeToHtml($translation->get('no e-mails has been sent')); ?></p>

<?php else: ?>

	<?php
		//til test af sentThisHour(). Bør ikke vises når vi er i produktion.
		//echo $email_object->sentThisHour();
	?>

	<?php if ($queue > 0): ?>
		<p><?php echo safeToHtml($translation->get('e-mails are in queue - the will be sent soon')); ?></p>
	<?php endif; ?>

	<?php echo $email_object->dbquery->display('character'); ?>

	<table>
	<caption><?php echo safeToHtml($translation->get('e-mails')); ?></caption>
	<thead>
	<tr>
		<th><?php echo safeToHtml($translation->get('subject')); ?></th>
		<th><?php echo safeToHtml($translation->get('contact')); ?></th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($emails AS $email): ?>
	<tr>
		<td><a href="<?php echo $email_shared->getPath(); ?>email.php?id=<?php echo $email['id']; ?>"><?php echo safeToHtml($email['subject']); ?></a></td>
		<td><a href="<?php echo $contact_module->getPath(); ?>contact.php?id=<?php echo $email['contact_id']; ?>"><?php echo safeToHtml($email['contact_name']); ?></a></td>
		<td>
		<?php if (!empty($email['status']) AND $email['status'] != 'sent'): ?>
			<a class="edit" href="<?php echo $email_shared->getPath(); ?>edit.php?id=<?php echo $email['id']; ?>"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a>
			<a class="delete" href="index.php?delete=<?php echo $email['id']; ?>"><?php echo safeToHtml($translation->get('delete', 'common')); ?></a>
		<?php else: ?>
			<?php echo safeToHtml($translation->get($email['status'], 'email')); ?>
		<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>

	<?php echo $email_object->dbquery->display('paging'); ?>

<?php endif; ?>

<?php
$page->end();
?>