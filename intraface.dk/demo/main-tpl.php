<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="da" lang="da">
    <head>
        <title><?php echo $title; ?></title>
        <meta http-equiv="content-type" content="text/html; charset=<?php e($encoding); ?>">
        <link rel="alternate" type="application/rss+xml" title="Alle produkter" href="/demo/shop/rss.php" />

        <style type="text/css">
        <?php foreach ($this->document->styles as $style): ?>
            @import "<?php e($style); ?>";
        <?php endforeach;?>
        </style>
     </head>

     <body>
        <div id="container">
            <?php echo $content; ?>
        </div>
    </body>
</html>