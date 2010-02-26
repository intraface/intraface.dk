<?php
class Intraface_Filehandler_Controller_SelectFile extends Intraface_Filehandler_Controller_Index
{
    public $multiple_choice;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getFileAppender()
    {
    	return $this->context->context->getFileAppender();
    }

    function dispatch()
    {
        $this->multiple_choice = $this->query('multiple_choice');
        $this->url_state->set('multiple_choice', $this->query('multiple_choice'));
        $this->url_state->set('use_stored', 'true');
        $this->url_state->set('images', $this->query('images'));
        return parent::dispatch();
    }

    function postForm()
    {
        $kernel = $this->context->getKernel();
        $module_filemanager = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');
        $gateway = new Ilib_Filehandler_Gateway($this->context->getKernel());
        /*
        if (isset($this->POST['ajax'])) {

            if (!isset($this->POST['redirect_id'])) {
                print('0');
            }

            $options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
            $redirect = new Ilib_Redirect($kernel->getSessionId(), MDB2::facotory(DB_DSN), intval($this->POST['redirect_id']), $options);
            if (isset($this->POST['add_file_id'])) {
                $filemanager = $gateway->getFromId(intval($this->POST['add_file_id']));
                if ($filemanager->get('id') != 0) {
                    $redirect->setParameter("file_handler_id", $filemanager->get('id'));
                    print('1');
                    exit;
                }
            }
            if (isset($this->POST['remove_file_id'])) {
                $redirect->removeParameter('file_handler_id', (int)$this->POST['remove_file_id']);
                print('1');
                exit;
            }
            print('0');
            exit;
        }


        $options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));

        $receive_redirect = Ilib_Redirect::factory($kernel->getSessionId(), MDB2::singleton(DB_DSN), 'receive', $options);

        $multiple_choice = false;
        */
        /*
        if ($receive_redirect->isMultipleParameter('file_handler_id')) {
            $multiple_choice = true;
        } else {
            $multiple_choice = false;
        }
        */
        /*
        if (isset($this->POST['return'])) {
            // Return is when AJAX is active, and then the checked files is already saved and should not be saved again.
            return new k_SeeOther($receive_redirect->getRedirect($this->url()));
        }
        */

        if (method_exists($this->context->context, 'appendFile')) {
            if (is_array($this->body('selected'))) {
                foreach ($this->body('selected') as $file_id) {
                    $file = $this->context->context->appendFile($file_id);
                }
            }
        } else {
            $appender = $this->getFileAppender();
            foreach ($this->body('selected') as $file_id) {
                $file = $gateway->getFromId($file_id);
            	$appender->addFile($file);
            }
        }
        return new k_SeeOther($this->url('../../'));
        /*
        $filemanager = new Ilib_Filehandler_Manager($kernel); // has to be loaded here, while it should be able to set an error just below.

        if (isset($this->POST['submit_close']) || isset($this->POST['submit'])) {
            settype($this->POST['selected'], 'array');
            $selected = $this->POST['selected'];

            $number_of_files = 0;
            foreach($selected as $id) {
                $tmp_f = $gateway->getFromId((int)$id);
                if ($tmp_f->get('id') != 0) {
                    $receive_redirect->setParameter("file_handler_id", $tmp_f->get('id'));
                    $number_of_files++;
                }
            }

            if ($number_of_files == 0) {
                $filemanager->error->set("you have to choose a file");
            } elseif ($multiple_choice == false || isset($this->POST['submit_close'])) {
                return new k_SeeOther($receive_redirect->getRedirect($this->url()));
            }
        }
        */
        /*
        if ($multiple_choice) {
            $selected_files = $receive_redirect->getParameter('file_handler_id');
        } else {
            if (isset($this->GET['selected_file_id'])) {
                $selected_files[] = (int)$this->GET['selected_file_id'];
            } else {
                $selected_files = array();
            }
        }
        */

        /*
        $filemanager->getDBQuery()->defineCharacter('character', 'file_handler.file_name');
        $filemanager->getDBQuery()->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
        $filemanager->getDBQuery()->storeResult("use_stored", "filemanager", "sublevel");

        $files = $filemanager->getList();
        */
    }

    function renderHtml()
    {
        $kernel = $this->context->getKernel();
        $module_filemanager = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        if ($this->query('delete')) {
            $appender = $this->getFileAppender();
            $appender->delete((int)$this->query('delete'));
            return new k_SeeOther($this->url('../../'));
        } elseif ($this->query('moveup')) {
            $appender = $this->getFileAppender();
            $file = $appender->findById($this->query('moveup'));
            try {
                $file->moveUp();
            } catch (Exception $e) {
            }
            return new k_SeeOther($this->url('../../'));
        } elseif ($this->query('movedown')) {
            $appender = $this->getFileAppender();
            $file = $appender->findById(intval($_GET['movedown']));
            try {
                $file->moveDown();
            } catch (Exception $e) {
            }
            return new k_SeeOther($this->url('../../'));
        }

        //$multiple_choice = ;
        $multiple_choice = $this->query('multiple');
        $selected_files = array();

        /*
        $options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
        $receive_redirect = Ilib_Redirect::factory($kernel->getSessionId(), MDB2::singleton(DB_DSN), 'receive', $options);
        if ($receive_redirect->isMultipleParameter('file_handler_id')) {
            $multiple_choice = true;
        } else {
            $multiple_choice = false;
        }
        */

        $filemanager = new Ilib_Filehandler_Manager($kernel); // has to be loaded here, while it should be able to set an error just below.

        /*
        if (isset($this->GET['upload'])) {
            $options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
            $upload_redirect = Ilib_Redirect::factory($kernel->getSessionId(), MDB2::singleton(DB_DSN), 'go', $options);

            if ($this->GET['upload'] == 'multiple') {
                $url = $upload_redirect->setDestination($module_filemanager->getPath().'upload_multiple.php', $module_filemanager->getPath().'select_file.php?redirect_id='.$receive_redirect->get('id').'&filtration=1');
            } else {
                $url = $upload_redirect->setDestination($module_filemanager->getPath().'upload.php', $module_filemanager->getPath().'select_file.php?redirect_id='.$receive_redirect->get('id').'&filtration=1');
            }
            return new k_SeeOther($url);
        }

        if ($multiple_choice) {
            $selected_files = $receive_redirect->getParameter('file_handler_id');
        } else {
            if (isset($this->GET['selected_file_id'])) {
                $selected_files[] = (int)$this->GET['selected_file_id'];
            } else {
                $selected_files = array();
            }
        }
        */
        if ($this->query('images')) {
            $filemanager->getDBQuery()->setFilter('images', 1);
        }

        if ($this->query("text") != "") {
            $filemanager->getDBQuery()->setFilter("text", $this->query("text"));
        }

        if (intval($this->query("filtration")) != 0) {
            // Kun for at filtration igen vises i s�geboksen
            $filemanager->getDBQuery()->setFilter("filtration", $this->query("filtration"));
            switch($this->query("filtration")) {
                case 1:
                    $filemanager->getDBQuery()->setFilter("uploaded_from_date", date("d-m-Y")." 00:00");
                    break;
                case 2:
                    $filemanager->getDBQuery()->setFilter("uploaded_from_date", date("d-m-Y", time()-60*60*24)." 00:00");
                    $filemanager->getDBQuery()->setFilter("uploaded_to_date", date("d-m-Y", time()-60*60*24)." 23:59");
                    break;
                case 3:
                    $filemanager->getDBQuery()->setFilter("uploaded_from_date", date("d-m-Y", time()-60*60*24*7)." 00:00");
                    break;
                case 4:
                    $filemanager->getDBQuery()->setFilter("edited_from_date", date("d-m-Y")." 00:00");
                    break;
                case 5:
                    $filemanager->getDBQuery()->setFilter("edited_from_date", date("d-m-Y", time()-60*60*24)." 00:00");
                    $filemanager->getDBQuery()->setFilter("edited_to_date", date("d-m-Y", time()-60*60*24)." 23:59");
                    break;
                default:
                    // Probaly 0, so nothing happens
            }
        }

        if (is_array($this->query('keyword')) && count($this->query('keyword')) > 0) {
            $filemanager->getDBQuery()->setKeyword($this->query('keyword'));
        }

        if ($this->query('character')) {
            $filemanager->getDBQuery()->useCharacter();
        }

        if ($this->query('search')) {
            $filemanager->getDBQuery()->setSorting('file_handler.date_created DESC');
        }


        $filemanager->getDBQuery()->defineCharacter('character', 'file_handler.file_name');
        $filemanager->getDBQuery()->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
        $filemanager->getDBQuery()->storeResult("use_stored", "filemanager_select", "sublevel");
        $filemanager->getDBQuery()->setUri($this->url());

        $files = $filemanager->getList();

        $this->document->addScript('scripts/select_file.js');
        $this->document->addScript('yui/connection/connection-min.js');
        $this->document->addScript('ckeditor/ckeditor.js');

        $this->document->setTitle('Files');

        $data = array('filemanager' => $filemanager,
                      'multiple_choice' => $multiple_choice,
                      //'receive_redirect' => $receive_redirect,
                      'files' => $files,
                      'selected_files' =>  $selected_files
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/selectfile');
        return $tpl->render($this, $data);
    }
}
