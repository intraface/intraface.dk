<?php
/**
 * @package <SystemMessage>
 * @author  <Sune>
 * @since   1.0
 * @version     1.0
 *
 */
class SharedKeyword extends Intraface_Shared
{
    function __construct()
    {
        $this->shared_name = 'keyword';
        $this->active = 1;

        $this->addPreloadFile('Keyword.php');
    }
}
