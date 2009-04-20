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
        $data['items'] = $this->getErrorList();
        try {
            $translation = $this->registry->get('translation_admin');
            $data['has_translation'] = true;
        }
        catch(ReflectionException $e) {
            $data['has_translation'] = false;
        }

        return $this->render('Intraface/Tools/templates/errorlist-tpl.php', $data);
    }
    
    public function POST()
    {
        $errorlist = $this->registry->get('errorlist');
        
        if (!empty($this->POST['deletelog'])) {
            $errorlist->delete();
        }
        
        return $this->GET();
    }
}