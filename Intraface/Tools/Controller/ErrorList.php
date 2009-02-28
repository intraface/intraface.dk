<?php
class Intraface_Tools_Controller_ErrorList extends k_Controller
{
    public $map = array(
        'rss' => 'Intraface_Tools_Controller_ErrorList_RSS',
        'all' => 'Intraface_Tools_Controller_ErrorList_All',
        'unique' => 'Intraface_Tools_Controller_ErrorList_Unique'
    );


    function getErrorList() 
    {
        
    }
    
    public function GET()
    {
        $data = array('items' => $this->getErrorList());

        return $this->render('Intraface/Tools/templates/errorlist-tpl.php', $data);
    }
    
    public function POST()
    {
        $errorlist = $this->registry->get('errorlist');
        
        if (!empty($this->POAT['deletelog'])) {
            $errorlist->delete();
        }
        
        return $this->GET();
    }
}