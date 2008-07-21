<?php
class Intraface_Doctrine_Template_Listener_Intranet extends Doctrine_Record_Listener
{
    public function preDqlSelect(Doctrine_Event $event)
    {
        $params = $event->getParams();
        $field = $params['alias'] . '.intranet_id';
        $query = $event->getQuery();
        $query->addWhere($field . ' = ?', array($this->getIntranetId()));
    }
    
    public function preInsert(Doctrine_Event $event)
    {
        $event->getInvoker()->intranet_id = $this->getIntranetId();
    }
    
    public function preUpdate(Doctrine_Event $event)
    {
        if($event->getInvoker()->intranet_id != $this->getIntranetId()) {
            throw new Exception('You are trying to update a record with another intranet_id than the present one ('.$event->getInvoker()->intranet_id.'/'.$this->getIntranetId().')');
        }
        
    }
    
    public function preDqlDelete(Doctrine_Event $event)
    {
        // print('preDqlDelete, intranet_id:'.$this->getIntranetId());
        $params = $event->getParams();
        $field = $params['alias'] . '.intranet_id';
        $query = $event->getQuery();
        $query->addWhere($field . ' = ?', array($this->getIntranetId()));
    }
    
    private function getIntranetId()
    {
        return Intraface_Doctrine_Intranet::singleton()->getId();
    }   
}
