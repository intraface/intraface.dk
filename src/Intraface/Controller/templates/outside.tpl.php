<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="da" xml:lang="da">
        <head>
	<title><?php e($this->document()->title()); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">
		@import "<?php e(url('/css/outside_system.css')); ?>";
	</style>
	<script type="text/javascript" src="<?php e(url('/javascript/yui/yahoo/yahoo.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/javascript/yui/event/event.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/javascript/yui/connection/connection.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/javascript/yui/dom/dom.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/javascript/focusField.js')); ?>"></script>


	<script type="text/javascript">
		function init() {
			var form = YAHOO.util.Dom.get("submit");
			if (!form) return;

			var inputField = YAHOO.util.Dom.get("email");
			if (inputField) {
				focusField(inputField);
			}


			var endPoint = YAHOO.util.Dom.getXY('container');

			var attributes = {
				points: {
					to: endPoint,
					control: [ [100, 800], [-100, 200] ]
				},
				width: { by: 100 },
				height: { by: 100 }
			}

			var clickhandler = function(e) {
				form.value = "Vi prøver";
				// form.disabled = true;
				return true;
			}

			YAHOO.util.Event.addListener(form, "click", clickhandler);

		}

		YAHOO.util.Event.addListener(window, "load", init);

	</script>

    <link href="<?php e(url('/images/favicon.ico')); ?>" rel="icon" />
    <link href="<?php e(url('/images/favicon.ico')); ?>" rel="shortcut icon"/>
    <link rel="shortcut icon" href="<?php e(url('/images/favicon.ico')); ?>" type="image/x-icon" />

</head>

<body>
  <div id="container">
  	<?php echo $content; ?>
  </div>

</body>
</html>