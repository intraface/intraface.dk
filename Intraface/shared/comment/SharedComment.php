<?php
/**
 *
 * @package <Message>
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 *
 */

class SharedComment extends Shared
{
    function __construct()
    {
        $this->shared_name = 'comment'; // Navn på på mappen med modullet
        $this->active = 1; // Er shared aktivt

        $this->addPreloadFile('Comment.php');

        $this->addSetting('types', array(
            0 => '_invalid_',
            1 => '_invalid_',
            2 => 'product',
            3 => 'cms_page',
            4 => '_invalid_'
        ));
    }
}