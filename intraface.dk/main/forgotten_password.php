<?php
require('../common.php');
$title = 'Glemt password';

if (!empty($_POST)) {

	if (User::sendForgottenPasswordEmail($_POST['email'])) {
		$msg = '<p>Vi har sendt en e-mail til dig med en ny adgangskode, som du bør gå ind og lave om med det samme.</p>';
	}
	else {
		$msg = '<p>Det gik <strong>ikke</strong> godt. E-mailen kunne ikke sendes. Du kan prøve igen senere.</p>';
	}
}

include(PATH_INCLUDE_IHTML . 'outside/top.php');
?>

<h1><span>Intraface.dk</span></h1>

<?php if (empty($msg)): ?>

<form method="POST" action="<?php echo basename($_SERVER['PHP_SELF']); ?>" id="forgotten_email_form">
	<p>Det er da fjollet, at du har glemt din adgangskode. Skriv din e-mail ind nedenunder, og så sender vi dig en ny.</p>
	<fieldset>
		<label id="email_label">E-mail:</label>
		<input type="text" name="email" id="email"  />
		<input type="submit" name="submit" value="Hjælp!" id="submit" />
	</fieldset>
</form>
<?php else:
	echo $msg;
endif;?>


<?php include(PATH_INCLUDE_IHTML . 'outside/bottom.php'); ?>