<?php
require('../../include_first.php');

$module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');

// settings
if (!empty($_GET['search']) AND in_array($_GET['search'], array('hide', 'view'))) {
	$kernel->setting->set('user', 'contact.search', $_GET['search']);
}

// delete

if (!empty($_POST['action']) AND $_POST['action'] == 'delete') {
	$deleted = array();
	if (!empty($_POST['selected']) AND is_array($_POST['selected'])) {
		foreach ($_POST['selected'] AS $key=>$id) {
			$contact = new Contact($kernel, intval($id));
			if ($contact->delete()) {
				$deleted[] = $id;
			}
		}
	}
}
elseif (!empty($_POST['undelete'])) {

	if (!empty($_POST['deleted']) AND is_string($_POST['deleted'])) {
		$undelete = unserialize(base64_decode($_POST['deleted']));
	}
	else {
		trigger_error('could not undelete', E_USER_ERROR);
	}
	if (!empty($undelete) AND is_array($undelete)) {
		foreach ($undelete AS $key=>$id) {
			$contact = new Contact($kernel, intval($id));
			if (!$contact->undelete()) {
			// void
			}
		}
	}
}


/*
if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$contact = new Contact($kernel, $_GET['delete']);
	$delete = $contact->delete();
}
elseif (!empty($_GET['undelete']) AND is_numeric($_GET['undelete'])) {
	$contact = new Contact($kernel, $_GET['undelete']);
	$contact->undelete();
}
*/



// hente liste med kunder
$contact = new Contact($kernel);
$contact->createDBQuery();
$keywords = $contact->getKeywords();
$used_keywords = $keywords->getUsedKeywords();

if(isset($_GET['query']) || isset($_GET['keyword_id'])) {

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
$contact->dbquery->storeResult('use_stored', 'contact', 'toplevel');

$contacts = $contact->getList();

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('contacts')));
?>
<h1><?php echo safeToHtml($translation->get('contacts')); ?></h1>

<ul class="options">
	<li><a class="new" href="contact_edit.php"><?php echo safeToHtml($translation->get('create contact')); ?></a></li>
	<?php if ($kernel->setting->get('user', 'contact.search') == 'hide' AND count($contacts) > 0): ?>
	<li><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?search=view"><?php echo safeToHtml($translation->get('show search')); ?></a></li>
	<?php endif; ?>
	<li><a class="pdf" href="<?php echo safeToHtml('http://'.NET_HOST.NET_DIRECTORY.'modules/contact/'); /* BAD SOLUTION!!! */ ?>pdf_label.php?use_stored=true" target="_blank"><?php echo safeToHtml($translation->get('print labels')); ?></a></li>
	<li><a class="excel" href="excel.php?use_stored=true"><?php echo safeToHtml($translation->get('excel', 'common')); ?></a></li>
	<li><a href="email_search.php?use_stored=true"><?php echo safeToHtml($translation->get('email to contacts in search')); ?></a></li>

</ul>

<?php if (!$contact->isFilledIn()): ?>

	<p><?php echo safeToHtml($translation->get('no contacts has been created')); ?>. <a href="contact_edit.php"><?php echo safeToHtml($translation->get('create contact')); ?></a>.</p>

<?php else: ?>


<?php if ($kernel->setting->get('user', 'contact.search') == 'view'): ?>

<form action="index.php" method="get" class="search-filter">
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('search')); ?></legend>

		<label for="query"><?php echo safeToHtml($translation->get('search for')); ?>
			<input name="query" id="query" type="text" value="<?php print($contact->dbquery->getFilter('search')); ?>" />
		</label>

		<?php if (is_array($used_keywords) AND count($used_keywords)): ?>
		<label for="keyword_id"><?php echo safeToHtml($translation->get('show with keywords')); ?>
			<select name="keyword_id" id="keyword_id">
				<option value="">Alle</option>
				<?php foreach ($used_keywords AS $k) { ?>
					<option value="<?php echo $k['id']; ?>" <?php if($k['id'] == $contact->dbquery->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php echo safeToHtml($k['keyword']); ?></option>
				<?php } ?>
			</select>
		</label>
		<?php endif; ?>
		<span><input type="submit" value="Afsted!" /></span>
		<!-- <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?search=hide">Skjul søgemulighederne</a>  -->
	</fieldset>
</form>

<?php endif; ?>

<?php echo $contact->dbquery->display('character'); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

	<?php if(!empty($deleted)): ?>
		<p class="message">Du har slettet kontakter. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="Fortryd" /></p>
	<?php endif; ?>

	<table summary="<?php echo safeToHtml($translation->get('contacts')); ?>" class="stripe">
		<caption><?php echo safeToHtml($translation->get('contacts')); ?></caption>
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th><?php echo safeToHtml($translation->get('number')); ?></th>
				<th><?php echo safeToHtml($translation->get('name', 'address')); ?></th>
				<th><?php echo safeToHtml($translation->get('phone', 'address')); ?></th>
				<th><?php echo safeToHtml($translation->get('e-mail', 'address')); ?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5"><?php echo $contact->dbquery->display('paging'); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($contacts AS $c) { ?>
			<tr class="vcard">

				<td>
					<input type="checkbox" value="<?php echo intval($c['id']); ?>" name="selected[]" />
				</td>
				<td><?php echo safeToHtml($c['number']); ?></td>
				<td class="fn"><a href="contact.php?id=<?php echo $c['id']; ?>"><?php echo safeToHtmL($c['name']); ?></a></td>
				<td class="tel"><?php echo safeToHtml($c['phone']); ?></td>
				<td class="email"><?php echo safeToHtml($c['email']); ?></td>
				<td class="options">
					<a class="edit" href="contact_edit.php?id=<?php echo $c['id']; ?>"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a>
					<?php /*
					<a class="delete" href="index.php?delete=<?php echo $c['id']; ?>&amp;use_stored=true"><?php echo safeToHtml($translation->get('delete', 'common')); ?></a> */ ?>
				</td>
			</tr>
			<?php } // end foreach ?>
		</tbody>
	</table>

	<select name="action">
		<option value="">Vælg</option>
		<option value="delete">Slet valgte</option>
	</select>

	<input type="submit" value="Udfør" />
</form>

<?php endif; ?>
<?php
$page->end();
?>