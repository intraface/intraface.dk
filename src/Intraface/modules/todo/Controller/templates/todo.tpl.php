
<h1><?php e($value['list_name']); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('edit')); ?>"><?php e(t('Edit')); ?></a></li>
</ul>

<p><?php if (isset($value['list_description'])) {
    e($value['list_description']);
} ?></p>

<form action="<?php e(url()); ?>" method="post">

    <input type="hidden" name="id" value="<?php e($value['id']); ?>" />
    <fieldset>
    <?php foreach ($value['todo'] as $i) : ?>
        <div>
            <?php if ($i['status'] == 1 and empty($headline)) {
                echo '<h4>'.t('Finished').'</h4>';
                $headline = true;
} ?>
          <label <?php  if ($i['status'] == 1) {
                echo ' class="completed"';
} ?>>
            <input type="checkbox" name="done[]" value="<?php e($i['id']); ?>" <?php if ($i['status'] == 1) {
                echo ' checked="checked"';
} ?>/>
            <?php if ($i['responsible_user_id'] > 0) {
                $user = new Intraface_User($i['responsible_user_id']);
                echo '<strong class="responsible">' . $user->getAddress()->get('name') . '</strong>: ';
} ?> <?php e($i['item']); ?>
            </label>

            <?php if ($i['status'] == 0) : ?>
          <a href="<?php e(url(null, array('item_id' => $i['id'], 'action' => 'moveup'))); ?>"><?php e(t('Up')); ?></a>
          <a href="<?php e(url(null, array('item_id' => $i['id'], 'action' => 'movedown'))); ?>"><?php e(t('Down')); ?></a>
          <a href="<?php e(url(null, array('item_id' => $i['id'], 'action' => 'delete'))); ?>" class="confirm" title="<?php e(t('This will delete the todo')); ?>"><?php e(t('Remove')); ?></a>
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
            foreach ($users as $user) {
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



  <input type="submit" value="<?php e(t('Mark as fixed')); ?>" class="save" />
  <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>

</form>
