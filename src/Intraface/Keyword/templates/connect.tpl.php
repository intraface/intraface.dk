<h1><?php e(__('Add keywords to') . ' ' . $object->get('name')); ?></h1>

<?php echo $keyword->error->view(); ?>

<form action="<?php e(url('./')); ?>" method="post">
    <?php if (is_array($keywords) AND count($keywords) > 0): ?>
    <fieldset>
        <legend><?php e(__('choose keywords')); ?></legend>
        <?php
            $i = 0;
            foreach ($keywords as $k) {
                print '<input type="checkbox" name="keyword[]" id="k'.$k['id'].'" value="'.$k['id'].'"';
                if (in_array($k['id'], $checked)) {
                    print ' checked="checked" ';
                }
                print ' />';
                print ' <label for="k'.$k["id"].'">' . htmlentities($k['keyword']) . ' (#'.$k["id"].')</a></label> - <a href="'.$this->url('./', array('delete' => $k["id"])).'" class="confirm">' .__('Delete', 'common'). '</a><br />'. "\n";
        }
        ?>
    </fieldset>
        <div style="clear: both; margin-top: 1em; width:100%;">
            <input type="submit" value="<?php e(__('Choose')); ?>" name="submit" class="save" /> <input type="submit" value="<?php e(__('Choose and close')); ?>" name="close" class="save" />
        </div>

    <?php endif; ?>
    <fieldset>
        <legend><?php e(__('Create keyword')); ?></legend>
        <p><?php e(__('Separate keywords by comma')); ?></samp></p>
        <label for="keyword"><?php e(__('Keywords')); ?></label>
        <input type="text" name="keywords" id="keyword" value="<?php // echo $keyword_string; ?>" />
        <input type="submit" value="<?php e(__('Save', 'common')); ?>" name="submit" name="close" />
    </fieldset>
</form>