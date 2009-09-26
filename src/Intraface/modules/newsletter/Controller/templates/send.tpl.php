<h1>Send nyhedsbrev</h1>

<ul class="options">
    <li><a href="<?php e(url('../', array('edit'))); ?>">Ret</a></li>
</ul>

<?php echo $context->getLetter()->error->view(); ?>

<form action="<?php e(url(null)); ?>" method="post">
    <input type="hidden" name="id" value="<?php e($context->getLetter()->get('id')); ?>" />

    <fieldset>
        <legend>Emne</legend>
        <p><?php e($context->getLetter()->get('subject')); ?></p>
    </fieldset>
    <fieldset>
        <legend>Tekst</legend>
        <div>
        <pre><?php e(wordwrap($context->getLetter()->get('text') . "\n\n" . $context->getLetter()->list->get('unsubscribe')), 72); ?></pre>
    </div>
    </fieldset>
  <div>
        <input type="submit" name="submit" value="Send" />
        <a href="<?php e(url('../')); ?>">Fortryd</a>
   </div>
</form>
