<?php
// der skal gøre sådan at man får en bekræftelse på, at e-mailen er sendt, hvis man sender e-mail
require('../../include_first.php');

$module = $kernel->module('todo');
$translation = $kernel->getTranslation('todo');

// Delete
if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $todo = new TodoList($kernel, $_GET['id']);
    $todo->getItem($_GET['delete'])->delete();
    header('Location: todo.php?id='.$_GET['id']);
    exit;
}

if (!empty($_POST)) {
    $todo = new TodoList($kernel, $_POST['id']);

    // new item
    if (!empty($_POST['new_item'])) {
        $todo->getItem()->save($_POST['new_item'], $_POST['responsible_user_id']);
    }

    // Set done
    $todo->setAllItemsUndone();
    if (!empty($_POST['done'])) {

        foreach ($_POST['done'] AS $key=>$value) {
            if ($todo->getItem($_POST['done'][$key])->setDone()) {
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
        $email->Body = $kernel->setting->get('user', 'todo.email.standardtext') . "\n\n" . $kernel->setting->get('intranet', 'todo.publiclist') . '?public_key=' . $todo->get('public_key'). "&intranet_key=".$kernel->intranet->get('private_key')."\n\nMed venlig hilsen\n".$kernel->user->getAddress()->get('name') . "\n" . $kernel->intranet->get('name');

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
    } else {
        header('Location: index.php');
        exit;
    }
} else {
    $todo = new TodoList($kernel, $_REQUEST['id']);
    if (isset($_GET['action']) && $_GET['action'] == "moveup") {
        $todo->getItem($_GET['item_id'])->getPosition(MDB2::singleton(DB_DSN))->moveUp();
    }

    if (isset($_GET['action']) && $_GET['action'] == "movedown") {
        $todo->getItem($_GET['item_id'])->getPosition(MDB2::singleton(DB_DSN))->moveDown();
    }

    $value = $todo->get();
    $value['todo'] = $todo->getAllItems();
}

$page = new Intraface_Page($kernel);
$page->includeJavascript('module', 'todo.js');
$page->start(t('Edit todo'));
?>

<h1><?php e($value['list_name']); ?></h1>

<ul class="options">
    <li><a href="todo_edit.php?id=<?php e($todo->get('id')); ?>"><?php e(t('Edit')); ?></a></li>
</ul>

<p><?php if (isset($value['list_description'])) e($value['list_description']); ?></p>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">



    <input type="hidden" name="id" value="<?php e($value['id']); ?>" />
    <fieldset>
    <?php foreach ($value['todo'] AS $i): ?>
        <div>
            <?php if ($i['status'] == 1 AND empty($headline)) { echo '<h4>'.t('Finished').'</h4>'; $headline = true; } ?>
          <label <?php  if ($i['status'] == 1) echo ' class="completed"'; ?>>
            <input type="checkbox" name="done[]" value="<?php e($i['id']); ?>" <?php if ($i['status'] == 1) echo ' checked="checked"'; ?>/>
          <?php if ($i['responsible_user_id'] > 0) {  $user = new Intraface_User($i['responsible_user_id']); echo '<strong class="responsible">' . $user->getAddress()->get('name') . '</strong>: ';  } ?> <?php e($i['item']); ?>
            </label>

            <?php if ($i['status'] == 0): ?>
          <a href="todo.php?id=<?php e($todo->get('id')); ?>&amp;item_id=<?php e($i['id']); ?>&amp;action=moveup"><?php e(t('Up')); ?></a>
          <a href="todo.php?id=<?php e($todo->get('id')); ?>&amp;item_id=<?php e($i['id']); ?>&amp;action=movedown"><?php e(t('Down')); ?></a>
          <a href="todo.php?id=<?php e($value['id']); ?>&amp;delete=<?php e($i['id']); ?>" class="confirm" title="<?php e(t('This will delete the todo')); ?>"><?php e(t('Remove')); ?></a>
            <?php endif; ?>
      </div>
   <?php endforeach; ?>
  </fieldset>

  <div id="new_item_form" class="hiddenbox">
      <input type="text" name="new_item" size="40" id="new_item" />
          <label id="responsible"><?php e(t('Who is responsible?')); ?></label>
        <select name="responsible_user_id" id="responsible">
          <option value="0"><?php e(t('Choose a responsible person')); ?></option>
          <?php
            $users = $kernel->user->getList();
          foreach ($users AS $user) {
              echo '<option value="'.$user['id'].'"';
            echo '>'.$user['name'].'</option>';
          }
        ?>
        </select>

        <p><input type="submit" value="<?php e(t('Add')); ?>" />

        <!-- det følgende bør sikkert skrives ind med javascript -->
        <?php e(t('or')); ?> <a href="#" onclick="todo.showFormField(document.getElementById('new_item_form'), '<?php e(t('Add item')); ?>')"><?php e(t('Close')); ?></a></p>


      </div>



<?php /* if ($kernel->setting->get('intranet', 'todo.publiclist') != ''): ?>

    <p><a href="todo_email.php?id=<?php e($todo->get('id')); ?>">Send e-mail</a></p>

<?php endif; */ ?>



  <input type="submit" value="<?php e(t('Mark as fixed')); ?>" class="save" /> <?php e(t('or')); ?> <a href="index.php"><?php e(t('Cancel')); ?></a>

</form>


<?php
$page->end();
?>