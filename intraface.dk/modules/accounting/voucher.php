<?php
require('../../include_first.php');

$module_accounting = $kernel->module('accounting');
$kernel->useShared('filehandler');
$translation = $kernel->getTranslation('accounting');


$not_all_stated  = false;

$year = new Year($kernel);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	if (!empty($_GET['return_redirect_id']) AND is_numeric($_GET['return_redirect_id'])) {



		$redirect = Redirect::factory($kernel, 'return');
		$selected_file_id = $redirect->getParameter('file_handler_id');

		if($selected_file_id != 0) {
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
	}
	else {
		trigger_error('Kunne ikke slette filen');
	}
}

elseif (!empty($_POST) AND !empty($_POST['state'])) {
	$voucher = new Voucher($year, $_POST['id']);
	$voucher->stateVoucher();
}
elseif (!empty($_POST) AND !empty($_FILES)) {
	$voucher = new Voucher($year, $_POST['id']);
	$voucher_file = new VoucherFile($voucher);
	$var['belong_to'] = 'file';

	if(!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
		$redirect = Redirect::factory($kernel, 'go');
		$module_filemanager = $kernel->useModule('filemanager');
		$url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $module_accounting->getPath().'voucher.php?id='.$voucher->get('id'));
		// $redirect->setIdentifier('voucher'); // Den er der kun behov for, hvis der er flere redirect med return på samme side  /Sune 06-12-2006
		$redirect->askParameter('file_handler_id');
		header('Location: '.$url);
		exit;
	}
	elseif (!empty($_FILES['new_file'])) {
		$filehandler = new FileHandler($kernel);
		$filehandler->loadUpload();
		$filehandler->upload->setSetting('max_file_size', 2000000);
		if($id = $filehandler->upload->upload('new_file')) {
			$var['belong_to_id'] = $id;
			if (!$voucher_file->save($var)) {
				$value = $_POST;
			}
			else {
				header('Location: voucher.php?id='.$voucher->get('id'));
				exit;
			}

		}
		else {
			$filehandler->error->view();
			$voucher_file->error->set('Kunne ikke uploade filen');
			$voucher_file->error->view();
		}

	}


}
else {
	$voucher = new Voucher($year, $_GET['id']);
}

$posts = $voucher->getPosts();
$voucher_file = new VoucherFile($voucher);
$voucher_files = $voucher_file->getList();



$page = new Page($kernel);
$page->start('Regnskab');
?>

<h1>Bilag #<?php echo safeToDb($voucher->get('number')); ?> på <?php echo safeToDb($year->get('label')); ?></h1>

<ul class="options">
	<li><a class="edit" href="voucher_edit.php?id=<?php echo intval($voucher->get('id')); ?>"><?php echo $translation->get('edit', 'common'); ?></a></li>
	<li><a href="vouchers.php"><?php echo $translation->get('close', 'common'); ?></a></li>
</ul>

<p><?php echo $voucher->get('text'); ?></p>

<?php $reference = $voucher->get('reference'); if (!empty($reference)): ?>
	<p><strong>Reference</strong>: <?php echo safeToHtml($voucher->get('reference')); ?></p>
<?php endif; ?>

<?php if (count($posts) == 0): ?>
	<p class="warning">Der er ikke nogen poster på bilaget. <a href="post_edit.php?voucher_id=<?php echo $voucher->get('id'); ?>">Indtast poster</a>.</p>
<?php else: ?>
	<table>
		<caption>Poster</caption>
		<thead>
		<tr>
			<th>Dato</th>
			<th>Tekst</th>
			<th>Konto</th>
			<th>Debet</th>
			<th>Kredit</th>
			<th></th>
		</tr>
		</thead>
	<?php foreach ($posts AS $post): ?>
		<tr>
			<td><?php echo $post['date_dk']; ?></td>
			<td><?php echo $post['text']; ?></td>
			<td><a href="account.php?id=<?php echo intval($post['account_id']); ?>"><?php echo safeToHtml($post['account_name']); ?></a></td>
			<td class="amount"><?php echo amountToOutput($post['debet']); ?></td>
			<td class="amount"><?php echo amountToOutput($post['credit']); ?></td>
			<td class="options">
				<?php if ($post['stated'] == 0): $not_all_stated = true; ?>
				<a class="edit" href="post_edit.php?id=<?php echo $post['id']; ?>">Ret</a>
				<a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo $post['id']; ?>&amp;id=<?php echo $voucher->get('id'); ?>">Slet</a>
				<?php else: ?>
				Bogført
				<?php endif; ?>
			</td>
		</tr>

	<?php endforeach; ?>
	</table>
	<p><a href="post_edit.php?voucher_id=<?php echo $voucher->get('id'); ?>">Indtast poster</a></p>
	<?php if (round($voucher->get('saldo'), 2) <> 0.00): ?>
		<p class="error">Bilaget stemmer ikke. Der er en difference på <?php echo round($voucher->get('saldo'), 2); ?> kroner.</p>
	<?php elseif ($not_all_stated): ?>
	<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
		<input name="id" type="hidden" value="<?php echo $voucher->get('id'); ?>" />
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
		<?php foreach($voucher_files AS $file): ?>
			<tr>
				<td><a target="_blank" href="<?php echo $file['file_uri']; ?>"><?php echo safeToHtml($file['description']); ?></a></td>
				<td class="options">
					<a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete_file=<?php echo $file['id']; ?>&amp;id=<?php echo $voucher->get('id'); ?>">Slet</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>

<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>"  enctype="multipart/form-data">
	<input name="id" type="hidden" value="<?php echo $voucher->get('id'); ?>" />
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
