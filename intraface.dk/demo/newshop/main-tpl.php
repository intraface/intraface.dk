<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="da" lang="da">
    <head>
        <title><?php echo $title; ?></title>
        <meta http-equiv="content-type" content="text/html; charset=<?php e($encoding); ?>">
        <link rel="alternate" type="application/rss+xml" title="Alle produkter" href="/demo/shop/rss.php" />

        <style type="text/css">
            @import "<?php e(url('/shop.css.php')); ?>";
        </style>
     </head>

     <body style="background-color: #CC0000;">

     <div style="background-color: white; margin: 20px auto; border: 4px solid #666666; width: 600px; padding: 20px;">

        <?php echo $content; ?>
    <div style="clear:both;"></<div>
    </div>
    </body>
</html>
