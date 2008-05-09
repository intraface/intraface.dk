<?php
require('../../include_first.php');

$module = $kernel->module('todo');
$translation = $kernel->getTranslation('todo');

if (!empty($_POST)) {
	$kernel->setting->set('intranet','todo.publiclist', $_POST['publiclist']);
	$kernel->setting->set('user','todo.email.standardtext', $_POST['emailstandardtext']);
	header('Location: index.php');
  exit;
}
else {
	$value['publiclist'] = 	$kernel->setting->get('intranet','todo.publiclist');
	$value['emailstandardtext'] = 	$kernel->setting->get('user','todo.email.standardtext');
}

$page = new Page($kernel);
$page->start('Settings');



?>
<h1>Indstillinger</h1>

<form action="<?php $_SERVER['PHP_SELF']; ?>" method="post">

  <fieldset>
    <label>
			<span class="labelText">Offentlig liste</span>
      <input type="text" size="60" name="publiclist" value="<?php echo $value['publiclist']; ?>" />
    </label>
    <label>
			<span class="labelText">Standardtekst på e-mails</span>
      <textarea name="emailstandardtext" rows="6" cols="80"><?php echo $value['emailstandardtext']; ?></textarea>
    </label>
  </fieldset>

  <input type="submit" value="Gem" />

</form>

<?php
$page->end();
?>