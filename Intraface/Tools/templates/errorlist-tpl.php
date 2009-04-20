<h1>Errorlog</h1>

<p><form method="post" action="<?php e(url('.')); ?>"><strong>When you have corrected errors, you have to delete the log.</strong> <input type="submit" name="deletelog" value="Delete now" /></form></p>


<?php if(isset($items) && is_array($items)): ?> 
    <?php foreach($items AS $item): ?>
        <p>
            <strong><?php e($item['title']); ?></strong> 
            <?php e($item['description']); ?><br />
            <?php e($item['pubDate']); ?>: <em><a href="<?php e($item['link']); ?>"><?php e($item['link']); ?></a></em>
        <?php if($has_translation && ereg("^Translation2: Missing translation for \"([a-zA-ZæøåÆØÅ _-]+)\" on pageID: \"([a-zA-ZæøåÆØÅ _-]+)\"", $item['title'], $params)): ?>
            <a href="<?php e(url('/translation', array('edit_id' => $params[1], 'page_id' => $params[2]))); ?>">Add translation</a>
        <?php endif; ?>
        </p>
    <?php endforeach; ?>
<?php endif; ?>