<?php
class Intraface_Filehandler_Controller_Viewer extends k_Component
{
    function renderHtml()
    {
        if (empty($_SERVER["QUERY_STRING"])) {
            throw new Exception('no querystring is given!');
            exit;
        }

        $query_parts = explode('/', $_SERVER["QUERY_STRING"]);

        $filehandler = Ilib_Filehandler::factory($this->registry->get('intraface:kernel'), $query_parts[2]);
        if(!is_object($filehandler) || $filehandler->getId() == 0) {
            throw new Exception('Invalid image: '.$_SERVER['QUERY_STRING']);
        }

        settype($query_parts[3], 'string');
        $fileviewer = new Ilib_Filehandler_FileViewer($filehandler, $query_parts[3]);
        throw new k_http_Response(200, $fileviewer->out());
    }
}
