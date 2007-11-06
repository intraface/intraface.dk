<?php
require('../../include_first.php');

$module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');

$redirect = Redirect::factory($kernel, 'receive');

if(isset($_GET['add'])) {

	$add_redirect = Redirect::factory($kernel, 'go');
	$url = $add_redirect->setDestination($module->getPath()."contact_edit.php", $module->getPath()."select_contact.php?".$redirect->get('redirect_query_string'));
	$add_redirect->askParameter("contact_id");
	//$add_redirect->setParameter("selected_contact_id", intval($_GET['add']));
	header("Location: ".$url);
	exit;
}


if(isset($_POST['submit'])) {

	$contact = new Contact($kernel, intval($_POST['selected']));
	if($contact->get('id') != 0) {
		$redirect->setParameter("contact_id", $contact->get('id'));
		header("Location: ".$redirect->getRedirect('index.php'));
		exit;
	}
	else {
		$contact->error->set("Du skal vælge en kontakt");
	}
}
else {
	$contact = new Contact($kernel);
}

// hente liste med kunder

$keywords = $contact->getKeywordAppender();
$used_keywords = $keywords->getUsedKeywords();
$contact->createDBQuery();

if(isset($_GET['contact_id'])) {
	$contact->dbquery->setCondition("contact.id = ".intval($_GET['contact_id']));
}
elseif(isset($_GET['query']) || isset($_GET['keyword_id'])) {

	if(isset($_GET['query'])) {
		$contact->dbquery->setFilter('search', $_GET['query']);
	}

	if(isset($_GET['keyword_id'])) {
		$contact->dbquery->setKeyword($_GET['keyword_id']);
	}
}
else {
	$contact->dbquery->useCharacter();
}

$contact->dbquery->defineCharacter('character', 'address.name');
$contact->dbquery->usePaging('paging');
$contact->dbquery->storeResult('use_stored', 'select_contact', 'sublevel');

if(isset($_GET['contact_id']) && intval($_GET['contact_id']) != 0) {
	$contact->dbquery->setExtraUri("&last_contact_id=".intval($_GET['contact_id']));
}
elseif(isset($_GET['last_contact_id']) && intval($_GET['last_contact_id']) != 0) {
	$contact->dbquery->setExtraUri("&last_contact_id=".intval($_GET['last_contact_id']));
}


$contacts = $contact->getList();

$page = new Page($kernel);
$page->start('Vælg kontakt');
?>
<h1>Vælg kontakt</h1>

<?php echo $contact->error->view(); ?>

<ul class="options">
	<li><a href="select_contact.php?add=1">Opret kontakt</a></li>
	<?php if(isset($_GET['last_contact_id']) && intval($_GET['last_contact_id']) != 0): ?>
	<li><a href="select_contact.php?contact_id=<?php print(intval($_GET['last_contact_id'])); ?>">Vis valgte</a></li>
	<?php endif; ?>

</ul>

<?php if (!$contact->isFilledIn()): ?>

	<p>Der er ikke oprettet nogen kontakter. <a href="contact_edit.php">Opret en kontakt</a>.</p>

<?php else: ?>

<form action="select_contact.php" method="get" class="search-filter">
	<fieldset>
		<legend>Søgning</legend>

		<label for="query">Søg efter
			<input name="query" id="query" type="text" value="<?php print($contact->dbquery->getFilter('search')); ?>" />
		</label>

		<?php if (is_array($used_keywords) AND count($used_keywords)): ?>
		<label for="keyword_id">Vis med nøgleord
			<select name="keyword_id" id="keyword_id">
				<option value="">Ingen</option>
				<?php foreach ($used_keywords AS $k) { ?>
					<option value="<?php echo $k['id']; ?>" <?php if($k['id'] == $contact->dbquery->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php echo $k['keyword']; ?></option>
				<?php } ?>
			</select>
		</label>
		<?php endif; ?>

		<span><input type="submit" value="Afsted!" /></span>
	</fieldset>
</form>

<?php echo $contact->dbquery->display('character'); ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<table summary="Kontakter" class="stripe">
		<caption>Kontakter</caption>
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>Nr.</th>
				<th>Navn</th>
				<th>E-mail</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="4"><?php echo $contact->dbquery->display('paging'); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($contacts AS $c) { ?>
			<tr>
				<td>
					<input type="radio" value="<?php echo $c['id']; ?>" name="selected" <?php if((isset($_GET['contact_id']) && $_GET['contact_id'] == $c['id']) || (isset($_GET['last_contact_id']) && $_GET['last_contact_id'] == $c['id'])) print("checked=\"checked\""); ?> />
				</td>
				<td><?php echo $c['number']; ?></td>
				<td><a href="contact.php?id=<?php echo $c['id']; ?>"><?php echo $c['name']; ?></a></td>
				<td><?php echo $c['email']; ?></td>
			</tr>
			<?php } // end foreach ?>
		</tbody>
	</table>

	<input type="submit" name="submit" value="<?php echo $translation->get('choose', 'common'); ?>" /> eller <a href="<?php print($redirect->getRedirect("index.php")); ?>">Fortryd</a>
</form>

<?php endif; ?>
<?php
$page->end();
?>