<h1>Søgning</h1>

<p><a href="<?php echo url('../'); ?>">Tilbage</a></p>

<div class="search"><form action="<?php echo url('./'); ?>" method="GET">Søg: <input type="text" name="search" value="" /> <input type="submit" value=" > " /></form></div>


<?php
if(isset($this->GET['search']) && $this->GET['search'] != '') {
    $db->query("SELECT * FROM core_translation_i18n WHERE id LIKE \"%".$this->GET['search']."%\"");
    if($db->numRows() > 0) {
        ?>
        <table>
            <caption>Søgeresultat</caption>
            <tr>
                <th>Identifier</th>
                <th>Page id</td>
                <th>DK</th>
                <th>UK</th>
                <th></th>
            </tr>
            <?php
            while($db->nextRecord()) {
                ?>
                <tr>
                    <td><?php echo $db->f('id'); ?></td>
                    <td><?php echo $db->f('page_id'); ?></td>
                    <td><?php echo $db->f('dk'); ?></td>
                    <td><?php echo $db->f('uk'); ?></td>
                    <td><a href="<?php echo url('../'); ?>?edit_id=<?php echo $db->f('id'); ?>&page_id=<?php echo $db->f('page_id'); ?>">Ret</a></td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
}
?>