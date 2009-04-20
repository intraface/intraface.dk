<?xml version="1.0"?>
<rss version="2.0">
    <channel>
        <title>Errorlog for intraface.dk</title>
        <link>http://tools.intraface.dk/error/</link>
        <description>This is the error log for intraface.dk</description>
        <language>en-us</language>
        <pubDate><?php e(date("D, d M Y H:i:s T")); ?></pubDate>
        <lastBuildDate><?php e(date("D, d M Y H:i:s T")); ?></lastBuildDate>
        <docs></docs>
        <managingEditor>support@intraface.dk</managingEditor>
        <webMaster>support@intraface.dk</webMaster>
        <?php if(isset($items) && is_array($items)): ?> 
            <?php foreach($items AS $item): ?>
                <item>
                    <title><?php e($item['title']); ?></title>
                    <link><?php e(url('../unique')); ?></link>
                    <description><?php e($item['description'].' ('.$item['link'].')')?></description>
                </item>
            <?php endforeach; ?>    
        <?php endif; ?>            
    </channel>
</rss>