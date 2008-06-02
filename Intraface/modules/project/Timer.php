<?php
class Intraface_Project_Timer
{
    /**
     * @var object
     */
    private $session;

    function __construct($session)
    {
        $this->session = $session;
    }

    function getMicrotime()
    {
        $tmp = explode(" ",microtime());
        $rtime = (double)$tmp[0] + (double)$tmp[1];
        return $rtime;
    }

    function start()
    {
        $this->session->set('timer.start', $this->getMicrotime());
    }

    function stop()
    {
        $this->session->set('timer.stop', $this->getMicrotime());
    }

    function getTime()
    {
        return round(($this->session->get('timer.stop') - $this->session->get('timer.start')));
    }
}


class Session
{
    private $session = array();

    function __construct()
    {
        session_start();
    }

    function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    function get($key)
    {
        return $_SESSION[$key];
    }
}

$timer = new Intraface_Project_Timer(new Session);

if (isset($_GET['stop'])) {
    $timer->stop();
    echo $timer->getTime();
}

$timer->start();