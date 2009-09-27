<h1><?php e(t('Send newsletter')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../', array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
</ul>

<?php echo $context->getLetter()->error->view(); ?>

<form action="<?php e(url(null)); ?>" method="post">
    <input type="hidden" name="id" value="<?php e($context->getLetter()->get('id')); ?>" />

    <fieldset>
        <legend><?php e(t('Subject')); ?></legend>
        <p><?php e($context->getLetter()->get('subject')); ?></p>
    </fieldset>
    <fieldset>
        <legend><?php e(t('Body text')); ?></legend>
        <div>
        <pre><?php e(wordwrap($context->getLetter()->get('text') . "\n\n" . $context->getLetter()->list->get('unsubscribe')), 72); ?></pre>
    </div>
    </fieldset>
  <div>
        <input type="submit" name="submit" value="<?php e(t('Send')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
   </div>
</form>
