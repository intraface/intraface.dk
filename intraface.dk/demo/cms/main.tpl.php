<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="da">

<head>
    <title><?php echo $this->document->title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->document->encoding; ?>" />
    <style type="text/css"><?php echo $this->document->style; ?></style>
</head>

<body>
    <div id="container">
        <div id="head">
            <div class="wrap">
                <h1>Kim og Mona</h1>
            </div>

            <hr />
            <?php if (!empty($this->document->navigation)): ?>
            <ul id="navigation-toplevel">
            <?php foreach ($this->document->navigation as $navigation): ?>
                <li><a href="<?php e(url($navigation['url_self'])); ?>"><?php e($navigation['navigation_name']); ?></a></li>
            <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <hr />

        <div id="content">

            <div id="submenu">
            <?php if (!empty($this->document->subnavigation)): ?>
            <ul id="navigation-sub">
            <?php foreach ($this->document->subnavigation as $navigation): ?>
                <li><a href="<?php e(url($navigation['url_self'])); ?>"><?php e($navigation['navigation_name']); ?></a></li>
            <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            </div>

            <div class="wrap">
                <?php echo $content; ?>
            </div>

            <div style="clear:both;">&nbsp;</div>
        </div>

    </div>

    <hr />

    <div id="foot">
        &copy; Billeder på siden må ikke gengives eller kopieres uden tilladelse.
    </div>

</body>
</html>