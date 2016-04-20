<?php
interface Intraface_Observable
{
    public function update($event);
}

class Intraface_Event
{
    protected $caller;
    protected $event;
    protected $params;

    function __construct($caller, $event, $params = array())
    {
        $this->caller = $caller;
        $this->event = $event;
        $this->params = $params;
    }

    function getEvent()
    {
        return $this->event;
    }
}

class Intraface_Dispatcher
{
    protected $events;

    function attach($event, $callable)
    {
        $this->events[$event] = $callable;
    }

    function notify(Intraface_Event $event)
    {
        foreach ($this->events as $e => $callable) {
            if ($event->getEvent() != $e) {
                continue;
            }
            call_user_func($this->events[$e]);
        }

    }
}
