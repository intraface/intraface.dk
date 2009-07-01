<?php
require('../../include_first.php');

$module = $kernel->module('todo');
$translation = $kernel->getTranslation('todo');

if (!empty($_POST)) {
    $todo = new TodoList($kernel, $_POST['id']);
    if ($todo->save(array(
        'list_name' => $_POST['list_name'],
        'list_description' => $_POST['list_description']
    ))) {

    foreach ($_POST['todo'] AS $key=>$value) {
        if (isset($_POST['item_id'])) {
            $item_id = $_POST['item_id'];    
            if ($todo->getItem($_POST['item_id'][$key])->save($_POST['todo'][$key], $_POST['responsible_user_id'][$key])) {
            }
        } else {
            $item_id = 0;
        }
    }
    header('Location: todo.php?id='.$todo->get('id'));
    exit;
  }
} else {
    if (!empty($_GET['id'])) {
        $todo = new TodoList($kernel, $_GET['id']);
    } else {
        $todo = new TodoList($kernel);
    }

    $value = $todo->get();
    $value['todo'] = $todo->getUndoneItems();
}

$page = new Intraface_Page($kernel);
$page->includeJavascript('module', 'todo.js');
$page->start(t('Edit todo'));
?>
<h1><?php e(t('Edit todo')); ?></h1>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" name="id" value="<?php e($todo->get('id')); ?>" />
    <fieldset id="todolist">
      <h2><?php e(t('Edit todo')); ?></h2>
      <label><input style="font-size: 2em;" type="text" name="list_name" value="<?php if (!empty($value['list_name'])) e($value['list_name']); ?>" /></label>
      <h2><?php e(t('Items')); ?></h2>
        <?php foreach ($value['todo'] AS $i): ?>
        <div>
        <input type="hidden" name="item_id[]" value="<?php e($i['id']); ?>">
        <label>
            <input type="text" style="width:50%;" name="todo[]" value="<?php e($i['item']); ?>" />
        </label>
        <label>
        <select name="responsible_user_id[]">
            <option value="0"><?php e(t('Who is responsible?')); ?></option>
            <?php
                $users = $kernel->user->getList();
                foreach ($users AS $user) {
                    echo '<option value="'.$user['id'].'"';
                    if ($i['responsible_user_id'] == $user['id']) {
                        echo ' selected="selected"';   
                    }
                    echo '>'.$user['name'].'</option>';
                }
            ?>
        </select>
        </label>
        <!-- egentlig bør dette link henvise til en side, der sletter punktet - mærkeligt at jeg ikke kan få behavior til at virke -->
        <a href="#"><?php e(t('Remove')); ?></a>
        <!-- onclick="this.parentNode.parentNode.removeChild(this.parentNode);" -->
        </div>
    <?php endforeach; ?>
        <div id="readroot">
        <label><input type="text" style="width:50%;" name="todo[]" value="" /></label>
    <label>
        <select name="responsible_user_id[]">
          <option value="0"><?php e(t('Who is responsible?')); ?></option>
          <?php
            $users = $kernel->user->getList();
          foreach ($users AS $user) {
              echo '<option value="'.$user['id'].'"';
            echo '>'.$user['name'].'</option>';
          }
        ?>
        </select>
    </label>
        <!-- bør skrives ind med javascript eller også skulle man bare knytte den til en slet-funktion, hvilket nok ville være ganske kvikt? -->
            <a href="#" onclick="this.parentNode.parentNode.removeChild(this.parentNode);"><?php e(t('Remove')); ?></a>
        </div>

        <span id="writeroot"></span>

<script type="text/javascript">
document.getElementById("readroot").style.display = "none";

var counter = 0;

function moreFields() {
    counter++;
    var newFields = document.getElementById('readroot').cloneNode(true);
    newFields.id = '';
    newFields.style.display = 'block';
    var newField = newFields.childNodes;
    for (var i=0;i<newField.length;i++)
    {
        var theName = newField[i].name
        if (theName)
            newField[i].name = theName + counter;
    }
    var insertHere = document.getElementById('writeroot');
    insertHere.parentNode.insertBefore(newFields,insertHere);
}
</script>

    <p><a href="" onclick="moreFields(); return false;"><?php e(t('More fields')); ?></a></p>

   <h2><?php e(t('Description (optional)')); ?></h2>
   <label><textarea cols="80" rows="4" name="list_description"><?php if (!empty($value['list_description'])) e($value['list_description']); ?></textarea></label>

   </fieldset>

    <div>
        <input type="submit" value="<?php e(t('Save list')); ?>" class="save" id="submit-save" /> <?php e(t('or')); ?> <a href="todo.php?id=<?php e($todo->get('id')); ?>"><?php e(t('Cancel')); ?></a>
    </div>
</form>


<?php
$page->end();
?>