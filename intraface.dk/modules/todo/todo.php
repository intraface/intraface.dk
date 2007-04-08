<?php
// der skal gøre sådan at man får en bekræftelse på, at e-mailen er sendt, hvis man sender e-mail
require('../../include_first.php');
require(PATH_INCLUDE_COMMON . 'tools/Position.php');

$module = $kernel->module('todo');


// Delete
if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$todo = new TodoList($kernel, $_GET['id']);
	$todo->loadItem($_GET['delete']);
	$todo->item->delete();
	header('Location: todo.php?id='.$_GET['id']);
	exit;
}

if (!empty($_POST)) {
	$todo = new TodoList($kernel, $_POST['id']);

	// new item
	if (!empty($_POST['new_item'])) {
		$todo->item->save($_POST['new_item'], $_POST['responsible_user_id']);
	}

	// Set done
	$todo->item->setAllUndone();
	if (!empty($_POST['done'])) {

		foreach ($_POST['done'] AS $key=>$value) {
			$todo->loadItem($_POST['done'][$key]);
			if ($todo->item->setDone()) {
			}
		}
	}
	/*
	if (!empty($_POST['send_list_email'])) {
		$email = new Phpmailer;
		$email->Subject = $todo->get('list_name');
		$email->From = $kernel->intranet->address->get('email');
		$email->FromName = $kernel->intranet->get('name');
		$email->addAddress($_POST['send_list_email']);
		$email->Body = $kernel->setting->get('user', 'todo.email.standardtext') . "\n\n" . $kernel->setting->get('intranet', 'todo.publiclist') . '?public_key=' . $todo->get('public_key'). "&intranet_key=".$kernel->intranet->get('private_key')."\n\nMed venlig hilsen\n".$kernel->user->address->get('name') . "\n" . $kernel->intranet->get('name');

		if ($email->Send()) {
			$email_msg = 'E-mailen er sendt';
		}
		else {
			$email_msg = 'E-mailen blev ikke sendt';
		}
	}
	*/
	if ($todo->howManyLeft() > 0) {
		header('Location: todo.php?id='.$_POST['id']);
		exit;
	}
	else {
		header('Location: index.php');
		exit;
	}
}
else {
	$todo = new TodoList($kernel, $_REQUEST['id']);
	if(isset($_GET['action']) && $_GET['action'] == "moveup") {
		$todo->loadItem($_GET['item_id']);
		$todo->item->MoveUp();
	}

	if(isset($_GET['action']) && $_GET['action'] == "movedown") {
		$todo->loadItem($_GET['item_id']);
		$todo->item->MoveDown();
	}

	$value = $todo->get();
	$value['todo'] = $todo->item->getList();
}

$page = new Page($kernel);
$page->includeJavascript('module', 'todo.js');
$page->start('Ret Todo');
?>

<h1><?php echo $value['list_name'] ?></h1>

<ul class="options">
	<li><a href="todo_edit.php?id=<?php echo $todo->get('id'); ?>">Ret</a></li>
</ul>

<p><?php if (isset($value['list_description'])) echo $value['list_description']; ?></p>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">



	<input type="hidden" name="id" value="<?php echo $value['id']; ?>" />
	<fieldset>
	<?php foreach($value['todo'] AS $i): ?>
		<div>
			<?php if ($i['status'] == 1 AND empty($headline)) { echo '<h4>Afsluttet</h4>'; $headline = true; } ?>
  		<label <?php  if ($i['status'] == 1) echo ' class="completed"'; ?>>
			<input type="checkbox" name="done[]" value="<?php echo $i['id']; ?>" <?php if ($i['status'] == 1) echo ' checked="checked"'; ?>/>
  		<?php if ($i['responsible_user_id'] > 0) {  $user = new User($i['responsible_user_id']); echo '<strong class="responsible">' . $user->address->get('name') . '</strong>: ';  } ?> <?php echo $i['item'] ?>
			</label>

			<?php if ($i['status'] == 0): ?>
	  	<a href="todo.php?id=<?php echo $todo->get('id'); ?>&amp;item_id=<?php echo $i['id']; ?>&amp;action=moveup">Op</a>
  		<a href="todo.php?id=<?php echo $todo->get('id'); ?>&amp;item_id=<?php echo $i['id']; ?>&amp;action=movedown">Ned</a>
  		<a href="todo.php?id=<?php echo $value['id']; ?>&amp;delete=<?php echo $i['id']; ?>" class="confirm" title="Dette sletter punktet!">Slet</a>
			<?php endif; ?>
	  </div>
   <?php endforeach; ?>
  </fieldset>

  <div id="new_item_form" class="hiddenbox">
  	<input type="text" name="new_item" size="40" id="new_item" />
		  <label id="responsible">Hvem er ansvarlig</label>
	    <select name="responsible_user_id" id="responsible">
      	<option value="0">Vælg en ansvarlig</option>
      	<?php
        	$users = $kernel->user->getList();
          foreach ($users AS $user) {
          	echo '<option value="'.$user['id'].'"';
            echo '>'.$user['name'].'</option>';
          }
        ?>
  	  </select>

		<p><input type="submit" value="Tilføj" />

		<!-- det følgende bør sikkert skrives ind med javascript -->
		eller <a href="#" onclick="todo.showFormField(document.getElementById('new_item_form'), 'Tilføj punkt')">Luk</a></p>


      </div>



<?php if ($kernel->setting->get('intranet', 'todo.publiclist') != ''): ?>

	<p><a href="todo_email.php?id=<?php echo $todo->get('id'); ?>">Send e-mail</a></p>

<?php endif; ?>



  <input type="submit" value="Marker som lavet" class="save" /> eller <a href="index.php">Fortryd</a>

</form>


<?php
$page->end();
?>