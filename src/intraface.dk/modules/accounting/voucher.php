<?php
require '../../include_first.php';

$module_accounting = $kernel->module('accounting');
$kernel->useShared('filehandler');
$translation = $kernel->getTranslation('accounting');


$not_all_stated  = false;

$year = new Year($kernel);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	if (!empty($_GET['return_redirect_id']) AND is_numeric($_GET['return_redirect_id'])) {

		$redirect = Intraface_Redirect::factory($kernel, 'return');
		$selected_file_id = $redirect->getParameter('file_handler_id');

		if ($selected_file_id != 0) {
			$voucher = new Voucher($year, intval($_GET['id']));
			$voucher_file = new VoucherFile($voucher);
			$var['belong_to'] = 'file';
			$var['belong_to_id'] = intval($selected_file_id);
			$voucher_file->save($var);
		}
	}
}

if (!empty($_GET['delete']) AND is_numeric($_GET['delete']) AND !empty($_GET['id']) AND is_numeric($_GET['id'])) {
	$voucher = new Voucher($year, $_GET['id']);
	$post = new Post($voucher, $_GET['delete']);
	if ($post->delete()) {
		header('Location: voucher.php?id='.$voucher->get('id'));
		exit;
	}
}

if (!empty($_GET['delete_file']) AND is_numeric($_GET['delete_file'])) {

	$voucher = new Voucher($year, $_GET['id']);
	$voucher_file = new VoucherFile($voucher, $_GET['delete_file']);
	if ($voucher_file->delete()) {
		header('Location: voucher.php?id='.$voucher->get('id'));
		exit;
	} else {
		trigger_error('Kunne ikke slette filen');
	}
} elseif (!empty($_POST) AND !empty($_POST['state'])) {
	$voucher = new Voucher($year, $_POST['id']);
	$voucher->stateVoucher();
} elseif (!empty($_POST) AND !empty($_FILES)) {
	$voucher = new Voucher($year, $_POST['id']);
	$voucher_file = new VoucherFile($voucher);
	$var['belong_to'] = 'file';

	if (!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
		$redirect = Intraface_Redirect::factory($kernel, 'go');
		$module_filemanager = $kernel->useModule('filemanager');
		$url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $module_accounting->getPath().'voucher.php?id='.$voucher->get('id'));
		// $redirect->setIdentifier('voucher'); // Den er der kun behov for, hvis der er flere redirect med return på samme side  /Sune 06-12-2006
		$redirect->askParameter('file_handler_id');
		header('Location: '.$url);
		exit;
	} elseif (!empty($_FILES['new_file'])) {
		$filehandler = new FileHandler($kernel);
		$filehandler->createUpload();
		$filehandler->upload->setSetting('max_file_size', 2000000);
		if ($id = $filehandler->upload->upload('new_file')) {
			$var['belong_to_id'] = $id;
			if (!$voucher_file->save($var)) {
				$value = $_POST;
			} else {
				header('Location: voucher.php?id='.$voucher->get('id'));
				exit;
			}

		} else {
			$filehandler->error->view();
			$voucher_file->error->set('Kunne ikke uploade filen');
			$voucher_file->error->view();
		}

	}
} elseif(!empty($_POST) AND !empty($_POST['action']) && $_POST['action'] == 'counter_entry' ) {

	$voucher = new Voucher($year, $_POST['id']);
	$posts = $voucher->getPosts();

	foreach($posts as $post) {
		if(isset($_POST['selected']) && is_array($_POST['selected']) && in_array($post['id'], $_POST['selected'])) {
			$new_post = new Post($voucher);
			$new_post->save($post['date'], $post['account_id'], $post['text'].' - '.t('counter entry'), $post['credit'], $post['debet']);
		}
	}
} else {
	$voucher = new Voucher($year, $_GET['id']);
}

$posts = $voucher->getPosts();
$voucher_file = new VoucherFile($voucher);
$voucher_files = $voucher_file->getList();

$page = new Intraface_Page($kernel);
$page->start('Regnskab');
?>

<h1>Bilag #<?php e($voucher->get('number')); ?> på <?php e($year->get('label')); ?></h1>

<ul class="options">
	<li><a class="edit" href="voucher_edit.php?id=<?php e($voucher->get('id')); ?>"><?php e(__('edit', 'common')); ?></a></li>
	<li><a href="vouchers.php"><?php e(__('close', 'common')); ?></a></li>
</ul>

<p><?php e($voucher->get('text')); ?></p>

<?php $reference = $voucher->get('reference'); if (!empty($reference)): ?>
	<p><strong>Reference</strong>: <?php e($voucher->get('reference')); ?></p>
<?php endif; ?>

<?php if (count($posts) == 0): ?>
	<p class="warning">Der er ikke nogen poster på bilaget. <a href="post_edit.php?voucher_id=<?php e($voucher->get('id')); ?>">Indtast poster</a>.</p>
<?php else: ?>
	<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
	<table>
		<caption>Poster</caption>
		<thead>
		<tr>
			<th></th>
			<th>Dato</th>
			<th>Tekst</th>
			<th>Konto</th>
			<th>Debet</th>
			<th>Kredit</th>
			<th></th>
		</tr>
		</thead>
	<?php foreach ($posts as $post): ?>
		<tr>
			<td><input type="checkbox" name="selected[]" value="<?php e($post['id']); ?>" /></td>
			<td><?php e($post['date_dk']); ?></td>
			<td><?php e($post['text']); ?></td>
			<td><a href="account.php?id=<?php e($post['account_id']); ?>"><?php e($post['account_name']); ?></a></td>
			<td class="amount"><?php e(amountToOutput($post['debet'])); ?></td>
			<td class="amount"><?php e(amountToOutput($post['credit'])); ?></td>
			<td class="options">
				<?php if ($post['stated'] == 0): $not_all_stated = true; ?>
				<a class="edit" href="post_edit.php?id=<?php e($post['id']); ?>">Ret</a>
				<a class="delete" href="<?php e($_SERVER['PHP_SELF']); ?>?delete=<?php e($post['id']); ?>&amp;id=<?php e($voucher->get('id')); ?>">Slet</a>
				<?php else: ?>
				Bogført
				<?php endif; ?>
			</td>
		</tr>

	<?php endforeach; ?>
	</table>

	<select name="action">
       <option value=""><?php e(t('Choose...', 'common'))?></option>
       <option value="counter_entry"><?php e(t('Create counter entry'))?></option>
    </select>
    <input name="id" type="hidden" value="<?php e($voucher->get('id')); ?>" />
    <input type="submit" value="<?php e(t('go', 'common')); ?>" />

    </form>

	<p><a href="post_edit.php?voucher_id=<?php e($voucher->get('id')); ?>">Indtast poster</a></p>
	<?php if (round($voucher->get('saldo'), 2) <> 0.00): ?>
		<p class="error">Bilaget stemmer ikke. Der er en difference på <?php e(round($voucher->get('saldo'), 2)); ?> kroner.</p>
	<?php elseif ($not_all_stated): ?>
	<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
		<input name="id" type="hidden" value="<?php e($voucher->get('id')); ?>" />
		<fieldset>
			<legend>Bogfør bilaget</legend>
			<input type="submit" name="state" value="Bogfør" />
		</fieldset>
	</form>
	<?php endif; ?>
<?php endif; ?>


<?php if (is_array($voucher_files) AND count($voucher_files) > 0): ?>

	<table>
		<caption>Filer</caption>

		<thead>
		<tr>
			<th>Filnavn</th>
			<th></th>

		</tr>
		</thead>
		<tbody>
		<?php foreach ($voucher_files as $file): ?>
			<tr>
				<td><a target="_blank" href="<?php e($file['file_uri']); ?>"><?php e($file['description']); ?></a></td>
				<td class="options">
					<a class="delete" href="<?php e($_SERVER['PHP_SELF']); ?>?delete_file=<?php e($file['id']); ?>&amp;id=<?php e($voucher->get('id')); ?>">Slet</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>

<form method="post" action="<?php e($_SERVER['PHP_SELF']); ?>"  enctype="multipart/form-data">
	<input name="id" type="hidden" value="<?php e($voucher->get('id')); ?>" />
	<fieldset>
		<legend>Upload fil til bilaget</legend>
        <?php
        $voucher_file->error->view();

        $filehandler = new FileHandler($kernel);
        $filehandler_html = new FileHandlerHTML($filehandler);
        $filehandler_html->printFormUploadTag('file_id', 'new_file', 'choose_file');
        ?>
	</fieldset>
	<p><input type="submit" value="Upload" /></p>
</form>

<?php
$page->end();
?>
