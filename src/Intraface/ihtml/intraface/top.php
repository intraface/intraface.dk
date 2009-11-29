<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"><html xmlns="http://www.w3.org/1999/xhtml" lang="da">
<head>
	<title><?php e($title); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php if (defined('INTRAFACE_K2')): e('utf-8'); else: e('iso-8859-1'); endif;  ?>" />
	<style type="text/css">
		@import "<?php e(url('/css/stylesheet.php', array('theme' => $this->theme_key, 'fontsize' => $this->fontsize))); ?>";
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

	<?php echo $javascript; ?>
	<link rel="start" href="http://www.intraface.dk/" title="Home" />

    <link href="<?php e(url('/images/favicon.ico')); ?>" rel="icon" />
    <link href="<?php e(url('/images/favicon.ico')); ?>" rel="shortcut icon"/>
    <link rel="shortcut icon" href="<?php e(url('/images/favicon.ico')); ?>" type="image/x-icon" />

</head>

<body id="onlinefaktura-dk"<?php if (!empty($module_name)) echo ' class="' . $module_name . '"'; ?>>

	<div id="container" class="clearfix">

		<?php if (!empty($system_message)) { ?><p id="system_message"><?php e($system_message); ?></p><?php } ?>

		<div id="branding" class="vcard">
			<h1 id="head" class="fn"><?php e($intranet_name); ?></h1>
			<div><span class="street-address"><?php e(str_replace("\n", ', ', $this->kernel->intranet->address->get('address'))); ?></span>
			<?php if ($this->kernel->intranet->address->get('postcode') != "" || $this->kernel->intranet->address->get('city')): ?>
			&bull; <span class="postal-code"><?php e($this->kernel->intranet->address->get('postcode')); ?></span>
			<span class="location"><?php e($this->kernel->intranet->address->get('city')); ?></span>
			<?php endif; ?>
			<?php if ($this->kernel->intranet->address->get('cvr') != ""): ?>
				&bull; <span>CVR <?php e( $this->kernel->intranet->address->get('cvr'));	?> </span>
			<?php endif; ?>
			</div>
		</div>

		<ul id="navigation-site" class="clearfix">
			<?php $first = true; foreach ($this->menu as $menuitem) { ?>
				<li<?php if ($first) echo ' class="first-child"'; ?>><a href="<?php e($menuitem['url']); ?>"><?php e($menuitem['name']); ?></a></li>
			<?php $first = false;} ?>
		</ul>

		<?php if (count($this->submenu) > 0) { ?>
			<ul id="navigation-sub" class="clearfix">
			<?php $first = true; foreach ($this->submenu as $menuitem) { ?>
				<li<?php if ($first) echo ' class="first-child"'; ?>><a href="<?php e($menuitem['url']); ?>"><?php e($menuitem['name']); ?></a></li>
			<?php $first = false; } ?>
			</ul>
		<?php } ?>


	<div id="content" class="clearfix">
		<div id="content-main">
