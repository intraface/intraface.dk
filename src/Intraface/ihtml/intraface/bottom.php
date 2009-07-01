		</div><!-- content-main -->

	</div><!-- pagebody -->


	<ul id="navigation-user" class="clearfix">

		<?php if (isset($this->usermenu)): ?>
		<?php foreach ($this->usermenu as $menuitem) { ?>
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
