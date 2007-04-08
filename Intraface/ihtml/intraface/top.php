<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"><html xmlns="http://www.w3.org/1999/xhtml" lang="da">
<head>
	<title><?php echo $title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css">
		@import "<?php echo PATH_WWW; ?>css/stylesheet.php?theme=<?php echo $this->theme_key; ?>&amp;fontsize=<?php echo $this->fontsize; ?>";
	</style>
	<link rel="stylesheet" href="<?php echo PATH_WWW; ?>css/print.css" type="text/css" media="print" title="Printvenlig" />

	
	<script type="text/javascript" src="<?php echo PATH_WWW; ?>javascript/yui/yahoo/yahoo-min.js"></script>
	<script type="text/javascript" src="<?php echo PATH_WWW; ?>javascript/yui/event/event-min.js"></script>
	<script type="text/javascript" src="<?php echo PATH_WWW; ?>javascript/yui/dom/dom-min.js"></script>
	<script type="text/javascript" src="<?php echo PATH_WWW; ?>javascript/main.js"></script>
	<script type="text/javascript" src="<?php echo PATH_WWW; ?>javascript/stripe.js"></script>
	<!--[if lt IE 7.]>
	<script defer type="text/javascript" src="<?php echo PATH_WWW; ?>javascript/pngfix.js"></script>
	<![endif]-->

	<?php print($javascript); ?>
	<link rel="start" href="http://www.intraface.dk/" title="Home" />
</head>

<body id="onlinefaktura-dk"<?php if(!empty($module_name)) echo ' class="' . $module_name . '"'; ?>>

	<div id="container" class="clearfix">
	
		<?php if (!empty($system_message)) { ?><p id="system_message"><?php echo $system_message; ?></p><?php } ?>

		<div id="branding" class="vcard">
			<h1 id="head" class="fn"><?php print($intranet_name); ?></h1>
			<div><span class="street-address"><?php echo str_replace("\n", ', ', $this->kernel->intranet->address->get('address')); ?></span>
			<?php if($this->kernel->intranet->address->get('postcode') != "" || $this->kernel->intranet->address->get('city')): ?>
			&bull; <span class="postal-code"><?php echo $this->kernel->intranet->address->get('postcode'); ?></span>  
			<span class="location"><?php echo $this->kernel->intranet->address->get('city'); ?></span>
			<?php endif; ?>
			<?php if($this->kernel->intranet->address->get('cvr') != ""): ?>
				&bull; <span>CVR <?php echo  $this->kernel->intranet->address->get('cvr');	?> </span>
			<?php endif; ?>
			</div>
		</div>
			
		<ul id="navigation-site" class="clearfix">
			<?php $first = true; foreach ($this->menu AS $menuitem) { ?>
				<li<?php if ($first) echo ' class="first-child"'; ?>><a href="<?php echo $menuitem['url']; ?>"><?php echo $menuitem['name']; ?></a></li>
			<?php $first = false;} ?>
		</ul>
		
		<?php if (count($this->submenu) > 0) { ?>
			<ul id="navigation-sub" class="clearfix">
			<?php $first = true; foreach ($this->submenu AS $menuitem) { ?>
				<li<?php if ($first) echo ' class="first-child"'; ?>><a href="<?php echo $menuitem['url']; ?>"><?php echo $menuitem['name']; ?></a></li>
			<?php $first = false; } ?>
			</ul>
		<?php } ?>
		

	<div id="content" class="clearfix">
		<div id="content-main">
