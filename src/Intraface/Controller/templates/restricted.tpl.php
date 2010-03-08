<div id="colOne">

<h1><?php e(t('Dashboard')); ?></h1>


<?php if ($context->getKernel()->setting->get('user', 'homepage.message') == 'view'): ?>
<div class="message">
	<p><?php e(t('Welcome, you are on the dashboard')); ?></p>
	<p><a href="<?php e(url(null, array('message' => 'hide'))); ?>"><?php e(t('Hide message forever')); ?></a></p>
</div>
<?php endif; ?>



<?php if (!empty($_attention_needed)): ?>
    <ul class="message-dependent">

	<?php foreach ($_attention_needed AS $advice): ?>
	   <li>
        <?php if (!empty($advice['link'])): ?>
            <a href="<?php e($advice['link']); ?>">
        <?php endif; ?>
			<?php if (!empty($advice['msg'])) {
				if (isset($advice['no_translation']) && $advice['no_translation'] == true) {
					e($advice['msg']);
				} else {
					e(t($advice['msg'], $advice['module']));
				}
			}
            ?>
			<?php if (!empty($advice['link'])): ?>
                </a>
            <?php endif; ?>

			</li>

		<?php endforeach; ?>
		</ul>

	<?php endif;
?>

<?php if (!empty($_advice)): ?>
    <ul class="advice">
        <?php foreach ($_advice AS $advice): ?>
        <li>
			<?php if (!empty($advice['link'])): ?>
                <a href="<?php e($advice['link']); ?>">
            <?php endif; ?>
			<?php if (!empty($advice['msg'])) e(t($advice['msg'])); ?>
			<?php if (!empty($advice['link'])): ?>
                </a>
			<?php endif; ?>
            </li>

		<?php endforeach; ?>
		</ul>
<?php endif; ?>

<?php
require_once('Intraface/ModuleHandler.php');
// false && which means the link is deactivated until we are going to use it!
if (Intraface_ModuleHandler::exists(MDB2::singleton(DB_DSN), 'modulepackage') && $context->getKernel()->user->hasModuleAccess('modulepackage')): ?>
    <?php
    $module_modulepackage = $context->getKernel()->useModule('modulepackage');
    ?>
    <p><a href="<?php e(url('module/modulepackage')); ?>"><?php e(t('view and change your intraface account')); ?></a></p>
<?php endif; ?>
</div>

<div id="colTwo">
	<img src="<?php e(url('/images/icons/twitter2.jpg')); ?>" height="133" width="200" />
	<?php foreach ($tweets as $tweet): ?>
	<?php $date = new DateTime($tweet['created_at']); ?>
		<p style="clear: both;">
		<img src="<?php e($tweet['profile_image_url']); ?>" style="border:1px solid black; float: left; margin-right: 5px; " />
		<span style=" padding-top: 5px; "><?php e($tweet['text']); ?></span><span>- <?php e($date->format('d-m-Y H:i')); ?> via <a href="http://twitter.com/<?php e($tweet['from_user']); ?>/status/<?php e($tweet['id']); ?>">Twitter</a></span>
		</p>
	<?php endforeach; ?>
</div>

