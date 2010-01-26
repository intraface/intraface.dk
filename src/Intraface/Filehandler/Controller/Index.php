<?php
class Intraface_Filehandler_Controller_Index extends k_Component
{
    protected $kernel_gateway;
    protected $user_gateway;
    protected $template;

    function __construct(k_TemplateFactory $template, Intraface_KernelGateway $kernel_gateway, Intraface_UserGateway $user_gateway)
    {
        $this->kernel_gateway = $kernel_gateway;
        $this->user_gateway = $user_gateway;
        $this->template = $template;
    }

    function getKernel()
    {
        return $this->kernel_gateway->findByUserobject($this->user_gateway->findByUsername($this->identity()->user()));
    }

    public function renderHtml()
    {
        $gateway = new Ilib_Filehandler_Gateway($this->getKernel());

        if (is_numeric($this->query('delete'))) {
            $filehandler = $gateway->getFromId($this->query('delete'));
            if (!$filemanager->delete()) {
                throw new Exception($this->__('Could not delete file'));
            }
        } elseif (is_numeric($this->query('undelete'))) {
            $filehandler = $gateway->getFromId($this->query('delete'));
            if (!$filemanager->undelete()) {
                throw new Exception($this->__('Could not undelete file'));
            }
        }

        if ($this->query('search')) {

            if ($this->query('text') != '') {
                $gateway->getDBQuery()->setFilter('text', $this->query('text'));
            }

            if (intval($this->query('filtration')) != 0) {
                $gateway->getDBQuery()->setFilter('filtration', $this->query('filtration'));

                switch($this->query('filtration')) {
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

            if (is_array($this->query('keyword')) && count($this->query('keyword')) > 0) {
                $gateway->getDBQuery()->setKeyword($this->query('keyword'));
            }

        } elseif ($this->query('character')) {
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

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/index');
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
}
