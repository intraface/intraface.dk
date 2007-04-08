		</div><!-- content-main -->

	</div><!-- pagebody -->


	<ul id="navigation-user" class="clearfix">
		
		<?php if(isset($this->usermenu)): ?>
		<?php foreach ($this->usermenu AS $menuitem) { ?>
			<li><a href="<?php echo $menuitem['url']; ?>"><?php echo $menuitem['name']; ?></a></li>
		<?php } ?>
		<?php endif; ?>
	</ul>

	<div id="footer" class="clearfix">
	</div>

	<?php

		if (MDB2_DEBUG) {
		echo '<div style="margin: 1em;"><h2>MDB2-queries</h2>';
		echo '<code>';
		$db = MDB2::singleton(DB_DSN);
		echo str_replace("\n\n\n\n\t", "<br />", $db->getDebugOutput());
		echo '</code></div>';
		}

	?>


</div><!-- container -->



</body>
</html>
<?php //echo timer(true); ?>
