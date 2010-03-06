<?php
class Intraface_Filehandler_Controller_Viewer extends k_Component
{
    protected $mdb2;

    function __construct(MDB2_Driver_Common $mdb2)
    {
        $this->mdb2 = $mdb2;
    }

    function renderHtml()
    {
        if (empty($_SERVER["QUERY_STRING"])) {
            throw new Exception('no querystring is given!');
        }

        $query_parts = explode('/', $_SERVER["QUERY_STRING"]);

        if (!isset($query_parts[1])) { // private key
            return new k_PageNotFound();
        }

        $auth_adapter = new Intraface_Auth_PublicKeyLogin($this->mdb2, $this->session()->sessionId(), $query_parts[1]);
        $weblogin = $auth_adapter->auth();

        if (!$weblogin) {
            if (isset($query_parts[1])) { // private key
                $query = $query_parts[1];
            } else {
                $query = 'query_parts[1] is empty';
            }
            throw new Exception('Error logging in to intranet with public key '.$query);
        }

        $kernel = new Intraface_Kernel;
        $kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
        $filehandler_shared = $kernel->useShared('filehandler');
        $filehandler_shared->includeFile('FileViewer.php');

        $filehandler = FileHandler::factory($kernel, $query_parts[2]);

        if (!is_object($filehandler) || $filehandler->get('id') == 0) {
            return new k_PageNotFound();
        }

        settype($query_parts[3], 'string');
        $fileviewer = new FileViewer($filehandler, $query_parts[3]);

        if ($fileviewer->needLogin()) {
            // session_start();
            $auth = new Intraface_Auth($this->session()->sessionId());
            if (!$auth->hasIdentity()) {
                throw new Exception('You need to be logged in to view the file');
            }

            $user = $auth->getIdentity($this->mdb2);
            $intranet = new Intraface_Intranet($user->getActiveIntranetId());
            if ($intranet->getId() != $kernel->intranet->getId()) {
                throw new Exception('You where not logged into the correct intranet to view the file');
            }
        }
        
        /**
         * TODO: This generates an error as $fileviewer->out() outputs file and returns number of bytes.
         * k_httpResponse expects a string. But if there is not a k_httpResponse, then an error is triggered
         * of sending headers after output (?).
         */
        return new k_HttpResponse(200, $fileviewer->out());
    }
}
