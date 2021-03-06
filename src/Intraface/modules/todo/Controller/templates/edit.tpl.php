
<h1><?php e(t('Edit todo')); ?></h1>

<form action="<?php e(url()); ?>" method="post">
    <input type="hidden" name="id" value="<?php e($todo->get('id')); ?>" />
    <fieldset id="todolist">
      <h2><?php e(t('Edit todo')); ?></h2>
      <label><input style="font-size: 2em;" type="text" name="list_name" value="<?php if (!empty($value['list_name'])) {
            e($value['list_name']);
} ?>" /></label>
      <h2><?php e(t('Items')); ?></h2>
        <?php foreach ($value['todo'] as $i) : ?>
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
            foreach ($users as $user) {
                echo '<option value="'.$user['id'].'"';
                if ($i['responsible_user_id'] == $user['id']) {
                    echo ' selected="selected"';
                }
                echo '>'.$user['name'].'</option>';
            }
            ?>
        </select>
        </label>
        <!-- egentlig b�r dette link henvise til en side, der sletter punktet - m�rkeligt at jeg ikke kan f� behavior til at virke -->
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
            foreach ($users as $user) {
                echo '<option value="'.$user['id'].'"';
                echo '>'.$user['name'].'</option>';
            }
        ?>
        </select>
    </label>
        <!-- b�r skrives ind med javascript eller ogs� skulle man bare knytte den til en slet-funktion, hvilket nok ville v�re ganske kvikt? -->
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
   <label><textarea cols="80" rows="4" name="list_description"><?php if (!empty($value['list_description'])) {
        e($value['list_description']);
} ?></textarea></label>

   </fieldset>

    <div>
        <input type="submit" value="<?php e(t('Save list')); ?>" class="save" id="submit-save" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </div>
</form>
