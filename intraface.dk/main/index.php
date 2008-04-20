<?php
require('../include_first.php');

$translation = $kernel->getTranslation();

$_attention_needed = array();
$_advice = array();

$last_view = $kernel->setting->get('user', 'homepage.last_view');
$kernel->setting->set('user', 'homepage.last_view', date('Y-m-d H:i:s'));

if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
	$kernel->setting->set('user', 'homepage.message', 'hide');
}

/**
 *  Adds link for id user details is filled in. They are going to be in the top.
 */
if (!$kernel->user->isFilledIn()) {
	$_advice[] = array(
		'msg' => 'all information about you has not been filled in',
		'link' => PATH_WWW . 'main/controlpanel/user_edit.php',
		'module' => 'dashboard'
	);
}

// getting stuff to show on the dashboard
$modules = $kernel->getModules();

for($i = 0, $max = count($modules); $i < $max; $i++) {

	if(!$kernel->intranet->hasModuleAccess(intval($modules[$i]['id']))) {
		continue;
	}
	if(!$kernel->user->hasModuleAccess(intval($modules[$i]['id']))) {
		continue;
	}

	$module = $kernel->useModule($modules[$i]['name']);
	$frontpage_files = $module->getFrontpageFiles();

	if (!is_array($frontpage_files) OR count($frontpage_files) == 0) {
		continue;
	}

	foreach($frontpage_files AS $file) {
		$file = PATH_INCLUDE_MODULE . $module->getName() . '/' .$file;
		if (file_exists($file)) {
			include($file);
		}
	}
}
# hvis der kun er et modul, skal man gå direkte til det - eller skal man?
/*
if ($kernel->user->countModuleAccess() == 1) {
}
*/

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('dashboard', 'dashboard')));
?>
<div id="colOne">

<h1><?php echo safeToHtml($translation->get('dashboard', 'dashboard')); ?></h1>

<?php if ($kernel->setting->get('user', 'homepage.message') == 'view'): ?>
<div class="message">
	<p><?php echo safeToHtml($translation->get('welcome, you are on the dashboard', 'dashboard')); ?></p>
	<p><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?message=hide"><?php echo safeToHtml($translation->get('hide message forever')); ?></a></p>
</div>
<?php endif; ?>


<?php
	if (is_array($_attention_needed) AND count($_attention_needed) > 0):
		echo '<ul class="message-dependent">';

		foreach ($_attention_needed AS $advice):
			echo '	<li>';
			if (!empty($advice['link'])) echo '<a href="'.safeToHtml($advice['link']).'">';
			if (!empty($advice['msg'])) {
				if(isset($advice['no_translation']) && $advice['no_translation'] == true) {
					echo safeToHtml($advice['msg']);
				}
				else {
					echo safeToHtml($translation->get($advice['msg'], $advice['module']));	
				}
			}
			if (!empty($advice['link'])) echo '</a>';
			
			echo '	</li>';

		endforeach;
		echo '</ul>';

	endif;
?>

<?php
	if (is_array($_advice) AND count($_advice) > 0):
		echo '<ul class="advice">';

		foreach ($_advice AS $advice):

			echo '	<li>';
			if (!empty($advice['link'])) echo '<a href="'.safeToHtml($advice['link']).'">';
			if (!empty($advice['msg'])) echo safeToHtml($translation->get($advice['msg'], $advice['module']));
			if (!empty($advice['link'])) echo '</a>';
			echo '	</li>';

		endforeach;
		echo '</ul>';

	endif;
?>

<?php 
require_once('Intraface/ModuleHandler.php');
// false && which means the link is deactivated until we are going to use it!
if(Intraface_ModuleHandler::exists(MDB2::singleton(DB_DSN), 'modulepackage') && $kernel->user->hasModuleAccess('modulepackage')): ?>
    <?php 
    $module_modulepackage = $kernel->useModule('modulepackage');
    ?>
    <p><a href="<?php echo safeToHtml($module_modulepackage->getPath()); ?>"><?php echo safeToHtml($translation->get('view and change your intraface account')); ?></a></p>
<?php endif; ?>
</div>

<div id="colTwo">

<?php

$systemdisturbance = new SystemDisturbance($kernel);
$now = $systemdisturbance->getActual();
$disturbance = $systemdisturbance->getList(true);

if(is_array($now) AND count($now) > 0) {
	if($now['important'] == 1) {
		$class = "warning";
	}
	else {
		$class = "message";
	}
	?>
	<div class="<?php echo $class; ?>">
		<p><?php print(nl2br($now['description'])); ?></p>
		<p>Forventes afsluttet <?php print($now['dk_to_date_time']); ?></p>
	</div>
	<?php
}
?>

<?php
if(is_array($disturbance) AND count($disturbance) > 0) {
	?>
	<div class="box">
	<h2><?php echo safeToHtml($translation->get('future disturbance', 'common')); ?></h2>
	<dl>
		<?php for($i = 0, $max = count($disturbance); $i < $max; $i++) { ?>
			<dt>Fra <?php print($disturbance[$i]['dk_from_date_time']); ?> til <?php print($disturbance[$i]['dk_to_date_time']); ?></dt>
			<dd><?php print(nl2br($disturbance[$i]['description'])); ?></dd>
		<?php } // slut på for ?>
	</dl>
	</div>
	<?php
}
?>


<?php

$systemmessage = $kernel->useShared('systemmessage');
$intranetnews = new IntranetNews($kernel);

$some_days_ago = date('Y-m-d', time() - 7 * 24 * 60 * 60);
$last_view_split = split(' ', $last_view); // vil kun bruge datoen og ikke klokkeslettet


if($last_view_split[0] > $some_days_ago) {
	$news = $intranetnews->getList($some_days_ago.' 23:59:59'); // Så bliver den vist hele dagen.
}
else {
	$news = $intranetnews->getList($last_view_split[0].' 23:59:59');
}

?>

<?php if (count($news) > 0): ?>
<div class="box">

<h2><?php echo safeToHtml($translation->get('news', 'common')); ?></h2>


<dl>
<?php for($i = 0, $max = count($news); $i < $max; $i++) { ?>
	<dt><?php print($news[$i]['dk_date_time'].' af '.$news[$i]['user_name']); ?></dt>
	<dd><?php print('<strong>'.$news[$i]['area'].':</strong> '.nl2br($news[$i]['description'])); ?></dd>
	<?php } // slut på for ?>
</dl>
</div>

<?php endif; ?>

</div>

<?php
$page->end();
?>
