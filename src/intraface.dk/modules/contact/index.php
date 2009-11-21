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
} elseif (!empty($_POST['undelete'])) {

	if (!empty($_POST['deleted']) AND is_string($_POST['deleted'])) {
		$undelete = unserialize(base64_decode($_POST['deleted']));
	} else {
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

if (!empty($_GET['import'])) {
    $redirect = Intraface_Redirect::go($kernel);
    $shared_fileimport = $kernel->useShared('fileimport');
    $url = $redirect->setDestination($shared_fileimport->getPath().'index.php', $module->getPath().'import.php');
    $redirect->askParameter('session_variable_name');
    header('location: '.$url);
    exit;

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
$keywords = $contact->getKeywordAppender();
$used_keywords = $keywords->getUsedKeywords();

if (isset($_GET['query']) || isset($_GET['keyword_id'])) {

	if (isset($_GET['query'])) {
		$contact->getDBQuery()->setFilter('search', $_GET['query']);
	}

	if (isset($_GET['keyword_id'])) {
		$contact->getDBQuery()->setKeyword($_GET['keyword_id']);
	}
}
else {
	$contact->getDBQuery()->useCharacter();
}

$contact->getDBQuery()->defineCharacter('character', 'address.name');
$contact->getDBQuery()->usePaging('paging');
$contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');

$contacts = $contact->getList();

$page = new Intraface_Page($kernel);
$page->start(__('contacts'));
?>
<h1><?php e(__('contacts')); ?></h1>

<ul class="options">
	<li><a class="new" href="contact_edit.php"><?php e(__('create contact')); ?></a></li>
	<?php if ($kernel->setting->get('user', 'contact.search') == 'hide' AND count($contacts) > 0): ?>
	<li><a href="<?php e($_SERVER['PHP_SELF']); ?>?search=view"><?php e(__('show search')); ?></a></li>
	<?php endif; ?>
	<li><a class="pdf" href="<?php e('http://'.NET_HOST.NET_DIRECTORY.'modules/contact/'); /* BAD SOLUTION!!! */ ?>pdf_label.php?use_stored=true" target="_blank"><?php e(__('print labels')); ?></a></li>
	<li><a class="excel" href="excel.php?use_stored=true"><?php e(__('excel', 'common')); ?></a></li>
	<li><a href="email_search.php?use_stored=true"><?php e(__('email to contacts in search')); ?></a></li>
    <li><a href="index.php?import=true"><?php e(__('import contacts')); ?></a></li>
</ul>

<?php if (!$contact->isFilledIn()): ?>

	<p><?php e(__('no contacts has been created')); ?>. <a href="contact_edit.php"><?php e(__('create contact')); ?></a>.</p>

<?php else: ?>


<?php if ($kernel->setting->get('user', 'contact.search') == 'view'): ?>

<form action="index.php" method="get" class="search-filter">
	<fieldset>
		<legend><?php e(__('search', 'common')); ?></legend>

		<label for="query"><?php e(__('search for', 'common')); ?>
			<input name="query" id="query" type="text" value="<?php e($contact->getDBQuery()->getFilter('search')); ?>" />
		</label>

		<?php if (is_array($used_keywords) AND count($used_keywords)): ?>
		<label for="keyword_id"><?php e(__('show with keywords', 'common')); ?>
			<select name="keyword_id" id="keyword_id">
				<option value="">Alle</option>
				<?php foreach ($used_keywords AS $k) { ?>
					<option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $contact->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
				<?php } ?>
			</select>
		</label>
		<?php endif; ?>
		<span><input type="submit" value="<?php e(t('go', 'common')); ?>" /></span>
		<!-- <a href="<?php e($_SERVER['PHP_SELF']); ?>?search=hide">Skjul søgemulighederne</a>  -->
	</fieldset>
</form>

<?php endif; ?>

<?php echo $contact->getDBQuery()->display('character'); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

	<?php if (!empty($deleted)): ?>
		<p class="message">Du har slettet kontakter. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="Fortryd" /></p>
	<?php endif; ?>

	<table summary="<?php e(__('contacts')); ?>" class="stripe">
		<caption><?php e(__('contacts')); ?></caption>
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th><?php e(__('number')); ?></th>
				<th><?php e(__('name', 'address')); ?></th>
				<th><?php e(__('phone', 'address')); ?></th>
				<th><?php e(__('e-mail', 'address')); ?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5"><?php echo $contact->getDBQuery()->display('paging'); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($contacts as $c) { ?>
			<tr class="vcard">

				<td>
					<input type="checkbox" value="<?php e($c['id']); ?>" name="selected[]" />
				</td>
				<td><?php e($c['number']); ?></td>
				<td class="fn"><a href="contact.php?id=<?php e($c['id']); ?>"><?php e($c['name']); ?></a></td>
				<td class="tel"><?php e($c['phone']); ?></td>
				<td class="email"><?php e($c['email']); ?></td>
				<td class="options">
					<a class="edit" href="contact_edit.php?id=<?php e($c['id']); ?>"><?php e(__('edit', 'common')); ?></a>
					<?php /*
					<a class="delete" href="index.php?delete=<?php e($c['id']); ?>&amp;use_stored=true"><?php e(__('delete', 'common')); ?></a> */ ?>
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