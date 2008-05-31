<?php
require('../../include_first.php');

$kernel->module('comment');
$kernel->useShared('comment');
$translation = $kernel->getTranslation('comment');


if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$comment = Comment::factory('id', $kernel, $_GET['delete']);
	$comment->delete();
}
elseif (!empty($_GET['approve'])) {
	$comment = Comment::approve($_GET['approve']);
}

/*
$email_object = new Email($kernel);
$email_object->dbquery->useCharacter();
$email_object->dbquery->defineCharacter('character', 'email.subject');
$email_object->dbquery->usePaging('paging');
//$email->dbquery->storeResult('use_stored', 'emails', 'toplevel');

$emails = $email_object->getList();
$queue = $email_object->countQueue();
*/

$comments = Comment::getList('all', $kernel);

$page = new Intraface_Page($kernel);
$page->start('Kommentarer');
?>
<h1>Kommentarer</h1>

<?php if (count($comments) == 0): ?>

	<p>Du har ikke sendt nogen kommentarer.</p>

<?php else: ?>

	<?php // echo $email_object->dbquery->display('character'); ?>

<table>
<caption>Kommentarer</caption>
	<thead>
	<tr>
		<th></th>
		<th>Overskrift</th>
		<th>Kontakt</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
<?php foreach($comments AS $comment): ?>
	<tr>
		<td><img src="<?php echo $comment['gravatar_url']; ?>" height="<?php echo $comment['gravatar_size']; ?>" width="<?php echo $comment['gravatar_size']; ?>" /></td>
		<td><?php echo safeToHtml($comment['headline']); ?></td>
		<td><a href="/modules/contact/contact.php?id=<?php echo safeToHtml($comment['contact_id']); ?>"><?php echo safeToHtml($comment['contact_name']); ?></a></td>
		<td class="options">
			<?php if ($comment['approved'] == 0): ?>
			<a class="approve" href="index.php?approve=<?php echo safeToHtml($comment['code']); ?>">Godkend</a>
			<?php endif; ?>
			<a class="delete" href="index.php?delete=<?php echo safeToHtml($comment['id']); ?>">Slet</a>
		</td>
	</tr>
<?php endforeach; ?>
	</body>
</table>

	<?php // echo $email_object->dbquery->display('paging'); ?></td>

<?php endif; ?>

<?php
$page->end();
?>