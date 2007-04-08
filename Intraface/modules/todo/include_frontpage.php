<?php
/*

// skla ikke længere bruges
	// midlertidig fil til forsiden
  $db = new DB_Sql;
  $db->query("SELECT todo_list.name, todo_item.item, todo_list.id FROM todo_item 
  	INNER JOIN todo_list ON todo_list.id = todo_item.todo_list_id 
  	WHERE todo_list.intranet_id = " . $kernel->intranet->get('id') . "	
     AND responsible_user_id = " .$kernel->user->get('id'). "
    	AND todo_item.status = 0
     AND todo_item.active=1");
	if ($db->numRows() > 0) {
		echo '<h2>Todo: Du er ansvarlig for</h2>';
    echo '<ul>';
    while ($db->nextRecord()) {
    	echo '<li><strong>'.$db->f('name').'</strong>: '.$db->f('item').' &mdash; <a href="/modules/todo/todo.php?id='.$db->f('id').'">Gå til listen</a></li>';
    }
    echo '</ul>';
  }
  */
?>