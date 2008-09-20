<?php
class Intraface_Doctrine_Template_Listener_Intranet extends Doctrine_Record_Listener
{
    public function preDqlSelect(Doctrine_Event $event)
    {
        $params = $event->getParams();
        
        $invoker = $event->getInvoker();
        $class   = get_class($invoker);
        
        $field = $params['alias'] . '.intranet_id';
        $query = $event->getQuery();
        
        // if it is not the class from FROM statement, it is left or inner join.
        // then we add the possibility for IS NULL. Actually it is only relevant in
        // left join, but how do we find out which kind of join it is?
        if($query->contains('FROM '.$class)) {
            $query->addWhere($field . '  = ?', array($this->getIntranetId()));
        }
        else {
            $query->addWhere('('.$field.' IS NULL OR '.$field . '  = ?)', array($this->getIntranetId()));
        }
        
        
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
