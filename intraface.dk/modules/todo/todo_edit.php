<?php
require('../../include_first.php');

$module = $kernel->module('todo');

if (!empty($_POST)) {
	$todo = new TodoList($kernel, $_POST['id']);
	if ($todo->save(array(
		'list_name' => $_POST['list_name'],
		'list_description' => $_POST['list_description']
	))) {

  	foreach ($_POST['todo'] AS $key=>$value) {
			$todo->loadItem($_POST['item_id'][$key]);

	  	if ($todo->item->save($_POST['todo'][$key], $_POST['responsible_user_id'][$key])) {
      }

    }

	 	header('Location: todo.php?id='.$todo->get('id'));
    exit;
  }
}
else {
	$todo = new TodoList($kernel, $_GET['id']);
  $value = $todo->get();
  $value['todo'] = $todo->item->getList("undone");
}

$page = new Page($kernel);
$page->includeJavascript('module', 'todo.js');
$page->start('Ret Todo');
?>
<h1>Ret Todo</h1>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<input type="hidden" name="id" value="<?php echo $value['id']; ?>" />
	<fieldset id="todolist">
  	<h2>Title</h2>
  	<label><input style="font-size: 2em;" type="text" name="list_name" value="<?php echo $value['list_name'] ?>" /></label>

  	<h2>Punkter</h2>
    <?php foreach($value['todo'] AS $i): ?>
		<div>
		<input type="hidden" name="item_id[]" value="<?php echo $i['id']; ?>">
 	  <label>
			<input type="text" style="width:50%;" name="todo[]" value="<?php echo $i['item'] ?>" />
		</label>
    <label>
	    <select name="responsible_user_id[]">
      	<option value="0">Vælg en ansvarlig</option>
      	<?php
        	$users = $kernel->user->getList();
          foreach ($users AS $user) {
          	echo '<option value="'.$user['id'].'"';
            if ($i['responsible_user_id'] == $user['id']) echo ' selected="selected"';
            echo '>'.$user['name'].'</option>';
          }
        ?>
  	  </select>
			<!--   -->
    </label>
		<!-- egentlig bør dette link henvise til en side, der sletter punktet - mærkeligt at jeg ikke kan få behavior til at virke -->
		<a href="#">Fjern</a>
<!--		onclick="this.parentNode.parentNode.removeChild(this.parentNode);" -->
		</div>
    <?php endforeach; ?>
		<div id="readroot">
		<label><input type="text" style="width:50%;" name="todo[]" value="" /></label> <!--<a href="edit_todo.php?id=<?php echo $value['id']; ?>&amp;delete=<?php echo $i['id']; ?>">Slet</a><br />-->
    <label>
	    <select name="responsible_user_id[]">
      	<option value="0">Vælg en ansvarlig</option>
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
    <a href="#" onclick="this.parentNode.parentNode.removeChild(this.parentNode);">Fjern</a>
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

	<p><a href="" onclick="moreFields(); return false;">Flere felter</a></p>

  <h2>Beskrivelse (valgfri)</h2>
  <label><textarea cols="80" rows="4" name="list_description"><?php echo $value['list_description'] ?></textarea></label>

  </fieldset>

	<div>
		<input type="submit" value="Gem listen" class="save" /> eller <a href="todo.php?id=<?php echo $value['id']; ?>">Fortryd</a>
	</div>
</form>


<?php
$page->end();
?>