<h1><?php e(t('Content on page').' '.$cmspage->get('title')); ?></h1>

<ul class="options">
	<li><a class="edit" href="<?php e(url(null, array('edit'))); ?>"><?php e(t('edit settings')); ?></a></li>
	<li><a
		href="<?php e(url('../', array('type' => $cmspage->get('type')))); ?>"><?php e(t('close')); ?></a></li>
		<?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
	<li><a
		href="<?php e(url('../../templates/' . $cmspage->get('template_id'))); ?>"><?php e(t('edit template')); ?></a></li>
		<?php endif; ?>
</ul>

<form method="post" action="<?php e(url()); ?>" id="publish-form">
<fieldset class="<?php e($cmspage->getStatus()); ?>"><?php if (!$cmspage->isPublished()): ?>
		<?php e(t('this page is not published')); ?> <input type="submit"
	value="<?php e(t('publish now')); ?>" name="publish" /> <?php else: ?>
		<?php e(t('this page is published')); ?> <input type="submit"
	value="<?php e(t('set as draft')); ?>" name="unpublish" /> <?php endif; ?>
<input type="hidden" value="<?php e($cmspage->get('id')); ?>" name="id" />
</fieldset>
</form>

<br style="clear: both;" />

		<?php if (count($sections) == 0): ?>
<p class="warning"><?php echo e(t('no sections added to the template')); ?>
		<?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?> <a
	href="<?php e(url('../../template/' . $cmspage->get('template_id'))); ?>"><?php e(t('edit template')); ?></a>.
	<?php else: ?> <strong><?php echo e(t('you cannot edit templates')); ?></strong>
	<?php endif; ?></p>
	<?php else: ?>

	<?php
	if (!empty($context->error) AND is_array($context->error) AND array_key_exists($section->get('id'), $context->error)) {
	    echo '<p class="error">'.e(t('error in a section - please see below')).'</p>';
	}
	?>

<form method="post" action="<?php e(url()); ?>"
	enctype="multipart/form-data" id="myform"><?php $test = ''; foreach ($sections AS $section):  ?>
	<?php
	// hvis value section ikke er sat, s� er det en ny post, s� vi henter den bare fra section->get()
	if (empty($value['section'][$section->get('id')])) {
	    $value['section'][$section->get('id')] = $section->get();
	}
	if (!empty($error) AND is_array($error) AND array_key_exists($section->get('id'), $error)) {
	    if (!empty($test) AND $section->get('type') != $test) echo '</fieldset>'; // Udkommenteret af sune, da den gav problemer. </fieldset> udskrives hver gang ny sektion inds�ttes, derfor kan jeg ikke se hvorfor den skal v�re der, og det bet�d at der kom en </fieldset> for meget
	    echo '<p class="error">'.$error[$section->get('id')].$test.$section->get('type').'</p>';
	}

	?> <?php switch($section->get('type')) {
	    case 'shorttext':
	        if (!array_key_exists($section->get('id'), $context->error) AND !empty($test) AND $test != 'shorttext') echo '</fieldset>';
	        if ($test != 'shorttext') echo '<fieldset>';
	        include 'section/shorttext.tpl.php';
	        break;
	    case 'longtext':
	        if (!array_key_exists($section->get('id'), $error) AND !empty($test) AND $test != 'longtext') echo '</fieldset>';
	        if ($test != 'longtext') echo '<fieldset>';
	        include 'section/longtext.tpl.php';
	        break;
	    case 'picture':
	        if (!array_key_exists($section->get('id'), $error) AND !empty($test)) echo '</fieldset>';
	        include 'section/picture.tpl.php';
	        break;
	    case 'mixed':
	        if (!array_key_exists($section->get('id'), $error)) { echo '</fieldset>'; }
	        include 'section/mixed.tpl.php';
	        break;
	        ?> <?php
	}
	$test = $section->get('type');

	?> <?php endforeach; ?>

</fieldset>

<div><input type="submit" value="<?php e(t('save')); ?>" /> <input
	type="submit" name="close" value="<?php e(t('save and close')); ?>" />
<a
	href="<?php e(url('../', array('type' => $cmspage->get('type')))); ?>"><?php e(t('Cancel')); ?></a>
</div>

</form>

	<?php endif; ?>