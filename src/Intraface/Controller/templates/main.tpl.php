<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"><html xmlns="http://www.w3.org/1999/xhtml" lang="da">
<head>
	<title><?php e($context->document()->title()); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">
		@import "<?php e(url('/css/stylesheet.php', array('theme' => $context->getThemeKey(), 'fontsize' => $context->getFontSize()))); ?>";
	</style>
	<link rel="stylesheet" href="<?php e(url('/css/print.css')); ?>" type="text/css" media="print" title="Printvenlig" />

	<script type="text/javascript" src="<?php e(url('/javascript/yui/yahoo/yahoo-min.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/javascript/yui/event/event-min.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/javascript/yui/dom/dom-min.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/javascript/main.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/javascript/stripe.js')); ?>"></script>
	<!--[if lt IE 7.]>
	<script defer type="text/javascript" src="<?php e(url('/javascript/pngfix.js')); ?>"></script>
	<![endif]-->

	<?php foreach ($context->document()->scripts() as $url): ?>
	<script type="text/javascript" src="<?php e(url('/javascript/' . $url)); ?>"></script>
	<?php endforeach; ?>

	<link rel="start" href="<?php e(url('.')); ?>" title="Home" />

    <link href="<?php e(url('/images/favicon.ico')); ?>" rel="icon" />
    <link href="<?php e(url('/images/favicon.ico')); ?>" rel="shortcut icon"/>
    <link rel="shortcut icon" href="<?php e(url('/images/favicon.ico')); ?>" type="image/x-icon" />

</head>

<body id="onlinefaktura-dk"<?php if (!empty($module_name)) echo ' class="' . $module_name . '"'; ?>>

	<div id="container" class="clearfix">

		<?php if (!empty($system_message)) { ?><p id="system_message"><?php e($system_message); ?></p><?php } ?>

		<div id="branding" class="vcard">
			<h1 id="head" class="fn"><?php e($context->getIntranetName()); ?></h1>
			<div><span class="street-address"><?php e(str_replace("\n", ', ', $context->getKernel()->intranet->address->get('address'))); ?></span>
			<?php if ($context->getKernel()->intranet->address->get('postcode') != "" || $context->getKernel()->intranet->address->get('city')): ?>
			&bull; <span class="postal-code"><?php e($context->getKernel()->intranet->address->get('postcode')); ?></span>
			<span class="location"><?php e($context->getKernel()->intranet->address->get('city')); ?></span>
			<?php endif; ?>
			<?php if ($context->getKernel()->intranet->address->get('cvr') != ""): ?>
				&bull; <span>CVR <?php e( $context->getKernel()->intranet->address->get('cvr'));	?> </span>
			<?php endif; ?>
			</div>
		</div>

		<ul id="navigation-site" class="clearfix">
			<?php $first = true; foreach ($context->getMenu() as $menuitem) { ?>
				<li<?php if ($first) echo ' class="first-child"'; ?>><a href="<?php e($menuitem['url']); ?>"><?php e($menuitem['name']); ?></a></li>
			<?php $first = false;} ?>
		</ul>

		<?php if (count($context->getSubMenu()) > 0) { ?>
			<ul id="navigation-sub" class="clearfix">
			<?php $first = true; foreach ($context->getSubMenu() as $menuitem) { ?>
				<li<?php if ($first) echo ' class="first-child"'; ?>><a href="<?php e($menuitem['url']); ?>"><?php e($menuitem['name']); ?></a></li>
			<?php $first = false; } ?>
			</ul>
		<?php } ?>


	<div id="content" class="clearfix">
		<div id="content-main">
<?php echo $content; ?>
		</div><!-- content-main -->

	</div><!-- pagebody -->


	<ul id="navigation-user" class="clearfix">

		<?php if (count($context->getUserMenu()) > 0): ?>
		<?php foreach ($context->getUserMenu() as $menuitem) { ?>
			<li><a href="<?php e($menuitem['url']); ?>"><?php e($menuitem['name']); ?></a></li>
		<?php } ?>
		<?php endif; ?>
	</ul>

	<div id="footer" class="clearfix">
	</div>

	<?php if (defined('MDB2_DEBUG') AND MDB2_DEBUG) { ?>
		<div style="margin: 1em;"><h2>MDB2-queries</h2>
		<code>
        <?php
		$db = MDB2::singleton(DB_DSN);
		echo str_replace("\n\n\n\n\t", "<br />", $db->getDebugOutput());
        ?>
		</code></div>
        <?php
		}
	?>
</div><!-- container -->



</body>
</html>
