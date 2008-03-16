<?php
require('../../include_first.php');

$module = $kernel->module('newsletter');

if(isset($_POST['submit'])) {
	$list = new NewsletterList($kernel, $_POST['id']);
	if ($id = $list->save($_POST)) {
		header('Location: index.php?from_id='.$id);
		exit;
	}
	else {
		$value = $_POST;
	}

}
elseif(isset($_GET['id'])) {
	$list = new NewsletterList($kernel, $_GET['id']);
	$value = $list->get();
}
else {
	$list = new NewsletterList($kernel);
	$value = array();

}

$page = new Page($kernel);
$page->start('Rediger liste');
?>

<h1>Rediger liste</h1>

<?php echo $list->error->view(); ?>

<form action="<?php e(basename($_SERVER['PHP_SELF'])); ?>" method="post">
	<fieldset>
	<legend>Om nyhedsbrevet</legend>
	<div class="formrow">
	  <label for="title">Titel</label>
		<input type="text" name="title" value="<?php if (!empty($value['title'])) e($value['title']); ?>" />
  </div>
<?php
/*
	<div class="formrow">
	  <label for="subscribe_option_key">Tilmeldingsmuligheder</label>
		<select name="subscribe_option_key" id="subscribe_option_key">
			<?php
			$newsletter_module = $kernel->getModule('newsletter');

			foreach($newsletter_module->getSetting('subscribe_option') AS $key => $option) {
				?>
				<option value="<?php print($key); ?>" <?php if($value['subscribe_option_key'] == $key) print("selected=\"selected\""); ?> ><?php print($newsletter_module->getTranslation($option)); ?></option>
				<?php
			}
			?>
		</select>
  </div>
	*/
?>
  	<div style="clear: both;">
		<label for="description">Beskrivelse</label><br />
		<textarea name="description" cols="90" rows="10"><?php if(!empty($value['description'])) e($value['description']); ?></textarea>
	</div>
</fieldset>
<fieldset>
	<legend>Afsender af nyhedsbrevet</legend>
	<div class="formrow">
	  <label for="sender_name">Navn på e-mailen</label>
		<input type="text" name="sender_name" value="<?php if (!empty($value['sender_name'])) e($value['sender_name']); ?>" /> (Hvis den er tom, er intranettet afsender)
  </div>
	<div class="formrow">
	  <label for="reply_email">Svar e-mail</label>
		<input type="text" name="reply_email" value="<?php if (!empty($value['reply_email'])) e($value['reply_email']); ?>" /> (Hvis den er tom, er intranettets e-mail-adresse til svar)
  </div>
  </fieldset>
  <fieldset>
  <legend>Øvrige oplysninger</legend>
  <!--
	<div class="formrow">
	  <label for="privacy_policy">Privatlivspolitik</label>
		<input type="text" name="privacy_policy" value="<?php if (!empty($value['privacy_policy'])) e($value['privacy_policy']); ?>" />
  </div>
-->

	<div style="clear: both;">
		<label for="subscribe_message">Tekst i e-mailen hvor man skal bekræfte sin tilmelding</label><br />
		<textarea name="subscribe_message" cols="90" rows="10"><?php if (!empty($value['subscribe_message'])) e($value['subscribe_message']); ?></textarea>
	</div>
	<!--
	<div style="clear: both;">
		<label for="unsubscribe_message">Frameldingsbesked</label><br />
		<textarea name="unsubscribe_message" cols="90" rows="10"><?php if (!empty($value['unsubscribe_message'])) e($value['unsubscribe_message']); ?></textarea>
	</div>
	-->

	<div>
	  <input type="submit" name="submit" value="Gem" class="save" />
		eller
		<a href="index.php">Fortryd</a>
		<input type="hidden" name="id" value="<?php if (!empty($value['id'])) e($value['id']); ?>" />
	</div>

	</fieldset>
</form>


<?php
$page->end();
?>