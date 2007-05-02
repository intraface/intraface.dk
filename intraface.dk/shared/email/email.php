<?php
require('../../include_first.php');

$kernel->useShared('email');
$redirect = Redirect::factory($kernel, 'receive');



// hvordan skal denne laves helt præcist?
if (!empty($_POST)) {
	$email = new Email($kernel, $_POST['id']);

	if (!empty($_POST['submit'])) {
		if ($email->send()) {

			$email->load();
			// This status can be used to change status where the email is coming from.
			if($redirect->get('id') != 0) {
				$redirect->setParameter('send_email_status', $email->get('status'));
			}

			/*
			// Moved to reminder.php triggered on return_redirect_id
			switch($email->get('type_id')) {
				case 5: // rykkere
					if (!$kernel->user->hasModuleAccess('debtor') OR !$kernel->user->hasModuleAccess('invoice')) {
						break;
					}

					$kernel->useModule('debtor');
					$kernel->useModule('invoice');
					$reminder = new Reminder($kernel, $email->get('belong_to_id'));
					$reminder->setStatus('sent');

					break;

				default:
					break;
			}
			*/
			header('Location: ' . $redirect->getRedirect('email.php?id='.$email->get('id')));
			exit;

		}
	}
}
else {
  $email = new Email($kernel, (int)$_GET['id']);
  $value = $email->get();
  $contact = $email->getContact();
}

$page = new Page($kernel);
$page->start('Email');
?>
<h1>E-mail</h1>

<?php
if ($email->get('status') == 'sent') {
	echo '<p class="message">E-mailen er sendt.';
	if ($kernel->user->hasModuleAccess('email')) {
		$email_module = $kernel->useModule('email');
		echo ' <a href="'.$email_module->getPath().'">Gå til e-mails</a>.';
	}
	echo '</p>';
}
else { ?>
<ul class="options">
  <li><a href="edit.php?id=<?php echo $email->get('id'); ?>">Rediger</a></li>
</ul>
<?php } ?>

<?php $email->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
	<input type="hidden" value="<?php echo $value['id']; ?>" name="id" />

	<fieldset>
		<pre>Til: <?php echo safeToHtml($contact->address->get('name')." <".$contact->address->get('email').">"); ?></pre>
		<pre><?php echo safeToHtml($value['subject']); ?></pre>
	</fieldset>

	<fieldset>
		<pre><?php echo wordwrap(safeToHtml($value['body']), 75); ?></pre>
	</fieldset>

	<?php if(!$email->isReadyToSend()): ?>
		<?php echo $email->error->view(); /* errors is first set in isReadyToSend, therefor we show the errors here */  ?>
	<?php elseif ($email->get('status') != 'sent'): // 3 er sendt ?>
		<input type="submit" name="submit" value="Send e-mail" class="save" onclick="return confirm('Er du sikker på, at du vil sende en e-mail?');" /> eller
		<a href="<?php echo $redirect->getRedirect('email.php?id='.$email->get('id')); ?>">Fortryd</a>
	<?php endif; ?>
</form>

<?php
$page->end();
?>
