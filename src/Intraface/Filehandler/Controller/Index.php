<?php
class Intraface_Filehandler_Controller_Index extends k_Component
{
    protected $kernel_gateway;
    protected $user_gateway;

    function __construct(Intraface_KernelGateway $kernel_gateway, Intraface_UserGateway $user_gateway)
    {
        $this->kernel_gateway = $kernel_gateway;
        $this->user_gateway = $user_gateway;
    }

    function getKernel()
    {
        return $this->kernel_gateway->findByUserobject($this->user_gateway->findByUsername($this->identity()->user()));
    }

    public function renderHtml()
    {
        $gateway = new Ilib_Filehandler_Gateway($this->getKernel());

        if (!empty($this->GET['delete']) AND is_numeric($this->GET['delete'])) {
            $filehandler = $gateway->getFromId($this->GET['delete']);
            if (!$filemanager->delete()) {
                throw new Exception($this->__('Could not delete file'));
            }
        } elseif (!empty($this->GET['undelete']) AND is_numeric($this->GET['undelete'])) {
            $filehandler = $gateway->getFromId($this->GET['delete']);
            if (!$filemanager->undelete()) {
                throw new Exception($this->__('Could not undelete file'));
            }
        }

        if (isset($this->GET['search'])) {

            if (isset($this->GET['text']) && $this->GET['text'] != '') {
                $gateway->getDBQuery()->setFilter('text', $this->GET['text']);
            }

            if (isset($this->GET['filtration']) && intval($this->GET['filtration']) != 0) {
                $gateway->getDBQuery()->setFilter('filtration', $this->GET['filtration']);

                switch($this->GET['filtration']) {
                    case 1:
                        $gateway->getDBQuery()->setFilter('uploaded_from_date', date('d-m-Y').' 00:00');
                        break;
                    case 2:
                        $gateway->getDBQuery()->setFilter('uploaded_from_date', date('d-m-Y', time()-60*60*24).' 00:00');
                        $gateway->getDBQuery()->setFilter('uploaded_to_date', date('d-m-Y', time()-60*60*24).' 23:59');
                        break;
                    case 3:
                        $gateway->getDBQuery()->setFilter('uploaded_from_date', date('d-m-Y', time()-60*60*24*7).' 00:00');
                        break;
                    case 4:
                        $gateway->getDBQuery()->setFilter('edited_from_date', date('d-m-Y').' 00:00');
                        break;
                    case 5:
                        $gateway->getDBQuery()->setFilter('edited_from_date', date('d-m-Y', time()-60*60*24).' 00:00');
                        $gateway->getDBQuery()->setFilter('edited_to_date', date('d-m-Y', time()-60*60*24).' 23:59');
                        break;
                    case 6:
                        $gateway->getDBQuery()->setFilter('accessibility', 'public');
                        break;
                    case 7:
                        $gateway->getDBQuery()->setFilter('accessibility', 'intranet');
                        break;
                    default:
                        // Probably 0, so nothing happens
                        break;
                }
            }

            if (isset($this->GET['keyword']) && is_array($this->GET['keyword']) && count($this->GET['keyword']) > 0) {
                $gateway->getDBQuery()->setKeyword($this->GET['keyword']);
            }

        } elseif (isset($this->GET['character'])) {
            $gateway->getDBQuery()->useCharacter();
        } else {
            $gateway->getDBQuery()->setSorting('file_handler.date_created DESC');
        }
        $gateway->getDBQuery()->defineCharacter('character', 'file_handler.file_name');
        $gateway->getDBQuery()->usePaging('paging', $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $gateway->getDBQuery()->storeResult('use_stored', 'filemanager', 'toplevel');
        $gateway->getDBQuery()->setUri($this->url());

        $files = $gateway->getList();

        $data = array('files' => $files,
                      'filemanager' => $gateway);

        $tpl = new k_Template(dirname(__FILE__) . '/../templates/index.tpl.php');
        return $tpl->render($this, $data);
    }

    protected function map($name)
    {
        if ($name == 'batchedit') {
            return 'Intraface_Filehandler_Controller_Batchedit';
        } elseif ($name == 'uploadmultiple') {
            return 'Intraface_Filehandler_Controller_UploadMultiple';
        } elseif ($name == 'uploadscript') {
            return 'Intraface_Filehandler_Controller_UploadScript';
        } elseif ($name == 'upload') {
            return 'Intraface_Filehandler_Controller_Upload';
        } elseif ($name == 'sizes') {
            return 'Intraface_Filehandler_Controller_Sizes';
        } elseif ($name == 'selectfile') {
            return 'Intraface_Filehandler_Controller_SelectFile';
        }
        return 'Intraface_Filehandler_Controller_Show';
    }

    function t($phrase)
    {
        return $phrase;
    }
}