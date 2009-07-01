<?php
require '../../include_first.php';

$module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');

$redirect = Intraface_Redirect::factory($kernel, 'receive');

if (!empty($_GET['add'])) {

	$add_redirect = Intraface_Redirect::factory($kernel, 'go');
	$url = $add_redirect->setDestination($module->getPath()."contact_edit.php", $module->getPath()."select_contact.php?".$redirect->get('redirect_query_string'));
	$add_redirect->askParameter("contact_id");
	//$add_redirect->setParameter("selected_contact_id", intval($_GET['add']));
	header("Location: ".$url);
	exit;
}

if (!empty($_GET['return_redirect_id'])) {
    $return_redirect = Intraface_Redirect::factory($kernel, 'return');
    if ($return_redirect->getParameter('contact_id') != 0) {
        $redirect->setParameter('contact_id', $return_redirect->getParameter('contact_id'));
        header("Location: ".$redirect->getRedirect('index.php'));
        exit;
    }
}

if (isset($_POST['submit'])) {

	$contact = new Contact($kernel, intval($_POST['selected']));
	if ($contact->get('id') != 0) {
		$redirect->setParameter("contact_id", $contact->get('id'));
		header("Location: ".$redirect->getRedirect('index.php'));
		exit;
	} else {
		$contact->error->set("Du skal vælge en kontakt");
	}
} else {
	$contact = new Contact($kernel);
}

// hente liste med kunder

$keywords = $contact->getKeywordAppender();
$used_keywords = $keywords->getUsedKeywords();

if (isset($_GET['contact_id'])) {
	$contact->getDBQuery()->setCondition("contact.id = ".intval($_GET['contact_id']));
} elseif (isset($_GET['query']) || isset($_GET['keyword_id'])) {

	if (isset($_GET['query'])) {
		$contact->getDBQuery()->setFilter('search', $_GET['query']);
	}

	if (isset($_GET['keyword_id'])) {
		$contact->getDBQuery()->setKeyword($_GET['keyword_id']);
	}
} else {
	$contact->getDBQuery()->useCharacter();
}

$contact->getDBQuery()->defineCharacter('character', 'address.name');
$contact->getDBQuery()->usePaging('paging');
$contact->getDBQuery()->storeResult('use_stored', 'select_contact', 'sublevel');

if (isset($_GET['contact_id']) && intval($_GET['contact_id']) != 0) {
	$contact->getDBQuery()->setExtraUri("&last_contact_id=".intval($_GET['contact_id']));
} elseif (isset($_GET['last_contact_id']) && intval($_GET['last_contact_id']) != 0) {
	$contact->getDBQuery()->setExtraUri("&last_contact_id=".intval($_GET['last_contact_id']));
}


$contacts = $contact->getList();

$page = new Intraface_Page($kernel);
$page->start('Vælg kontakt');
?>
<h1>Vælg kontakt</h1>

<?php echo $contact->error->view(); ?>

<?php if (!$contact->isFilledIn()): ?>

	<p>Der er ikke oprettet nogen kontakter. <a href="select_contact.php?add=1">Opret en kontakt</a>.</p>

<?php else: ?>
    <ul class="options">
        <li><a href="select_contact.php?add=1">Opret kontakt</a></li>
        <?php if (isset($_GET['last_contact_id']) && intval($_GET['last_contact_id']) != 0): ?>
        <li><a href="select_contact.php?contact_id=<?php e($_GET['last_contact_id']); ?>">Vis valgte</a></li>
        <?php endif; ?>

    </ul>

    <form action="select_contact.php" method="get" class="search-filter">
	<fieldset>
		<legend>Søgning</legend>

		<label for="query">Søg efter
			<input name="query" id="query" type="text" value="<?php e($contact->getDBQuery()->getFilter('search')); ?>" />
		</label>

		<?php if (is_array($used_keywords) AND count($used_keywords)): ?>
		<label for="keyword_id">Vis med nøgleord
			<select name="keyword_id" id="keyword_id">
				<option value="">Ingen</option>
				<?php foreach ($used_keywords AS $k) { ?>
					<option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $contact->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
				<?php } ?>
			</select>
		</label>
		<?php endif; ?>

		<span><input type="submit" value="Afsted!" /></span>
	</fieldset>
    </form>

    <?php echo $contact->getDBQuery()->display('character'); ?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
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
    				<td colspan="4"><?php echo $contact->getDBQuery()->display('paging'); ?></td>
    			</tr>
    		</tfoot>
    		<tbody>
    			<?php foreach ($contacts as $c) { ?>
    			<tr>
    				<td>
    					<input type="radio" value="<?php e($c['id']); ?>" name="selected" <?php if ($redirect->getParameter('contact_id') == $c['id']) print("checked=\"checked\""); ?> />
    				</td>
    				<td><?php e($c['number']); ?></td>
    				<td><a href="contact.php?id=<?php e($c['id']); ?>"><?php e($c['name']); ?></a></td>
    				<td><?php e($c['email']); ?></td>
    			</tr>
    			<?php } // end foreach
                ?>
    		</tbody>
    	</table>

    	<input type="submit" name="submit" value="<?php e($translation->get('choose', 'common')); ?>" /> eller <a href="<?php e($redirect->getRedirect("index.php")); ?>">Fortryd</a>
    </form>

<?php endif; ?>
<?php
$page->end();
?>