<?php
class Intraface_modules_accounting_Controller_Voucher_Show extends k_Component
{
    protected $template;
    protected $voucher;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        if ($this->getVoucher()->get('id') == 0) {
            throw new k_PageNotFound();
        }
        return parent::dispatch();
    }

    protected function map($name)
    {
        if ($name == 'post') {
            return 'Intraface_modules_accounting_Controller_Post_Index';
        } elseif ($name == 'filehandler') {
            return 'Intraface_Filehandler_Controller_Index';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->module('accounting');

        if (is_numeric($this->query('delete_file'))) {
            $voucher_file = new VoucherFile($this->getVoucher(), $this->query('delete_file'));
            if ($voucher_file->delete()) {
                return new k_SeeOther($this->url(null, array('flare' => 'File has been removed')));
            } else {
                throw new Exception('Kunne ikke slette filen');
            }
        }

        $posts = $this->getVoucher()->getPosts();

        $voucher_file = new VoucherFile($this->getVoucher());
        $voucher_files = $voucher_file->getList();

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/voucher/show');
        return $smarty->render($this);
    }

    function renderHtmlEdit()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/voucher/edit');
        return $smarty->render($this);
    }

    function postMultipart()
    {
        $this->getKernel()->useModule('filemanager');
        $voucher_file = new VoucherFile($this->getVoucher());
        $var['belong_to'] = 'file';

        if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
            return new k_SeeOther($this->url('filehandler/selectfile'));
        } elseif (!empty($_FILES['new_file'])) {
            $filehandler = new FileHandler($this->getKernel());
            $filehandler->createUpload();
            $filehandler->upload->setSetting('max_file_size', 2000000);
            if ($id = $filehandler->upload->upload('new_file')) {
                $var['belong_to_id'] = $id;
                if (!$voucher_file->save($var)) {
                    $value = $_POST;
                } else {
                    return new k_SeeOther($this->url());
                }
            } else {
                $filehandler->error->view();
                $voucher_file->error->set('Kunne ikke uploade filen');
                $voucher_file->error->view();
            }
        }
        return $this->render();
    }

    function postForm()
    {
        if ($this->body('state')) {
            $this->getVoucher()->stateVoucher();
            return new k_SeeOther($this->url());
        } elseif ($this->body('action') == 'counter_entry') {
            $posts = $this->getVoucher()->getPosts();

            foreach ($posts as $post) {
                if (is_array($_POST['selected']) && in_array($post['id'], $_POST['selected'])) {
                    $new_post = new Post($this->getVoucher());
                    $new_post->save($post['date'], $post['account_id'], $post['text'].' - '.$this->t('counter entry'), $post['credit'], $post['debet']);
                }
            }

            return new k_SeeOther($this->url());
        }

        if ($this->getVoucher()->save($_POST)) {
            return new k_SeeOther($this->url(null));
        }
        return $this->render();
    }

    function appendFile($selected_file_id)
    {
        $voucher_file = new VoucherFile($this->getVoucher());
        $var['belong_to'] = 'file';
        $var['belong_to_id'] = intval($selected_file_id);
        $voucher_file->save($var);
        return true;
    }

    function getFiles()
    {
        $voucher_file = new VoucherFile($this->getVoucher());
        return $voucher_files = $voucher_file->getList();
    }

    function getVoucherFile()
    {
        return $voucher_file = new VoucherFile($this->getVoucher());
    }

    function getVoucher()
    {
        if (is_object($this->voucher)) {
            return $this->voucher;
        }
        return ($this->voucher = new Voucher($this->getYear(), $this->name()));
    }

    function getPosts()
    {
        return $posts = $this->getVoucher()->getPosts();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getFilehandlerHtml()
    {
        require_once 'Intraface/modules/filemanager/FileHandler.php';
        require_once 'Intraface/modules/filemanager/FileHandlerHTML.php';
        $filehandler = new FileHandler($this->getKernel());
        return $filehandler_html = new FileHandlerHTML($filehandler);
    }

    function getYear()
    {
        return $this->context->getYear();
    }

    function getValues()
    {
        return $this->getVoucher()->get();
    }

    function getYears()
    {
        return $this->getYear()->getList();
    }

    function getAccount($id = 0)
    {
        return new Account($this->getYear(), $id);
    }

    function getVatPeriod()
    {
        return new VatPeriod($$this->getYear());
    }

    function getYearGateway()
    {
        $gateway = $this->context->getYearGateway();
        return $gateway;
    }
}
