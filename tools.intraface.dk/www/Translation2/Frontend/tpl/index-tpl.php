<div class="search"><form action="<?php echo $this->url('search'); ?>" method="GET">Søg: <input type="text" name="search" value="" /> <input type="submit" value=" > " /></form></div>

<h1>Translation</h1>


<?php
if(isset($message) && is_array($message) && count($message) > 0) {
    ?>
    <div class="message"><?php echo implode("<br />", $message); ?></div>
    <?php
}
?>

<form action="<?php echo url('./'); ?>" method="POST">

<div class="formrow"><label for="id">Identifier</label><input type="text" name="id" value="<?php if(isset($id)) echo $id; ?>" /> (Simpelt forståeligt engelsk)</div>

<?php
if(isset($exists) && is_array($exists) && count($exists) > 0) {
    echo '<div class="exists">';
    foreach($exists AS $key => $value) {
        echo '<p>'.$key.': '.$value['id'].'</p>';
    }
    echo '</div>';
}
?>

<div class="formrow"><label for="page_id">PageId</label>
    <select name="page_id">
        <?php
        $db->query("SELECT DISTINCT(page_id) FROM core_translation_i18n WHERE page_id != '' ORDER BY page_id");
        while($db->nextRecord()) {
            ?>
            <option value="<?php echo $db->f('page_id'); ?>" <?php if(isset($page_id) && $page_id == $db->f('page_id')) echo 'selected="selected"'; ?> ><?php echo $db->f('page_id'); ?></option>
            <?php
        }
        ?>
    </select>
    Ny: <input type="text" name="new_page_id" value="<?php if(isset($new_page_id)) echo $new_page_id; ?>" /> (Modulnavn)
</div>

<div class="formrow"><label for="dk">DK</label><textarea name="dk"><?php if(isset($dk)) echo $dk; ?></textarea></div>

<?php
if(isset($exists) && is_array($exists) && count($exists) > 0) {
    echo '<div class="exists">';
    foreach($exists AS $key => $value) {
        echo '<p>'.$key.': '.$value['dk'].'</p>';
    }
    echo '</div>';
}
?>

<div class="formrow"><label for="uk">UK</label><textarea name="uk"><?php if(isset($uk)) echo $uk; ?></textarea></div>

<?php
if(isset($exists) && is_array($exists) && count($exists) > 0) {
    echo '<div class="exists">';
    foreach($exists AS $key => $value) {
        echo '<p>'.$key.': '.$value['uk'].'</p>';
    }
    echo '</div>';
}
?>

<?php
if(isset($overwrite) && $overwrite == 1) {
    ?>
    <input type="submit" name="submit" value="  Gem alligevel " /> eller <a href="<?php e(url('./')); ?>">Fortryd</a>
    <input type="hidden" name="overwrite" value="1" />
    <?php
}
else {
    ?>
    <input type="submit" name="submit" value="  Gem  " />
    <input type="hidden" name="overwrite" value="0" />
    <?php
}
?>

</form>

<?php

if(isset($success) && is_array($success)) {
    ?>
    <div class="success">
    <p><strong><?php echo $success['text']; ?></strong></p>
    <p><?php echo $success['page_id'].': '.$success['id']; ?> <a href="index.php?edit_id=<?php echo urlencode($success['id']); ?>&page_id=<?php echo urlencode($success['page_id']); ?>" >Ret</a></p>
    <p>DK: <?php echo $success['dk']; ?></p>
    <p>UK: <?php echo $success['uk']; ?></p>
    </div>
    <?php
}
?>
