<?php
class Intraface_Filehandler_Controller_Viewer extends k_Component
{
    protected $mdb2;
    protected $fileviewer;
    protected $file;

    function __construct(MDB2_Driver_Common $mdb2)
    {
        $this->mdb2 = $mdb2;
    }

    function dispatch()
    {
        if (empty($_SERVER["QUERY_STRING"])) {
            throw new k_PageNotFound();
        }

        $query_parts = explode('/', $_SERVER["QUERY_STRING"]);

        if (!isset($query_parts[1])) { // public
            throw new k_PageNotFound();
        }

        $auth_adapter = new Intraface_Auth_PublicKeyLogin($this->mdb2, $this->session()->sessionId(), $query_parts[1]);
        $weblogin = $auth_adapter->auth();

        if (!$weblogin) {
            if (isset($query_parts[1])) { // public
                $query = $query_parts[1];
            } else {
                $query = 'query_parts[1] is empty';
            }
            $response = new k_TextResponse('Could not login using the key ' . $query);
            $response->setStatus(403);
            return $response;
        }

        $kernel = new Intraface_Kernel;
        $kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
        $filehandler_shared = $kernel->useModule('filemanager');
        $filehandler_shared->includeFile('FileViewer.php');

        $access_key = $query_parts[2];

        $gateway = new Ilib_Filehandler_Gateway($kernel);
        $this->file = $gateway->findByAccesskey($access_key);

        if (!is_object($this->file) || $this->file->get('id') == 0) {
            throw new k_PageNotFound();
        }

        settype($query_parts[3], 'string');
        $this->fileviewer = new FileViewer($this->file, $query_parts[3]);
        if ($this->fileviewer->needLogin()) {
            $auth = new Intraface_Auth($this->session()->sessionId());
            if (!$auth->hasIdentity()) {
                $response = new k_TextResponse('You are not correctly logged in');
                $response->setStatus(403);
                return $response;
            }

            $user = $auth->getIdentity($this->mdb2);
            $intranet = new Intraface_Intranet($user->getActiveIntranetId());
            if ($intranet->getId() != $kernel->intranet->getId()) {
                $response = new k_TextResponse('You are not logged in to the correct intranet');
                $response->setStatus(403);
                return $response;
            }
        }

        return parent::dispatch();
    }

    function GET()
    {
        $response = new k_HttpResponse(200, $this->fileviewer->fetch());
        $response->setContentType($this->fileviewer->getMimeType());
        $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $this->fileviewer->getLastModified()).' GMT');
        $response->setHeader('Cache-Control', 'private');
        $response->setHeader('Content-Disposition', 'inline;filename='.$this->fileviewer->getFileName());
        $response->setHeader('Pragma', 'cache');
        return $response;
    }
}
