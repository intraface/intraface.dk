<?php
class Intraface_Tools_Controller_ErrorLog extends k_Controller
{
    public $map = array('rss' => 'Intraface_Tools_Controller_ErrorLog_RSS');

    function GET()
    {
        $errorlist = $this->registry->get('errorlist');

            if (!empty($this->GET['action']) AND $this->GET['action'] == 'deletelog') {
                $errorlist->delete();
            }

            $output = '<html><body>';

            $output .= '<h1>Errorlog</h1>';
            $output .= '<p><strong>When you have corrected errors, you have to delete the log.</strong> <a href="'.$this->url('?action=deletelog').'">Delete now</a></p>';

            if(isset($this->GET['show']) && $this->GET['show'] != '') {
                $items = $errorlist->get($this->GET['show']);
            } else {
                $items = $errorlist->get();
            }

            foreach($items AS $item) {
                $output .=  '<p><strong>'.$item['title'].'</strong> '.$item['description'].'<br />'.$item['pubDate'].': <em><a href="'.$item['link'].'">'.$item['link'].'</a></em>';
            }

            $output .=  '</body></html>';

            return $output;
    }

}
