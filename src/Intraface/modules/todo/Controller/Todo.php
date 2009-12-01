<?php
// der skal gøre sådan at man får en bekræftelse på, at e-mailen er sendt, hvis man sender e-mail
class Intraface_modules_todo_Controller_Todo extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'email') {
            return 'Intraface_modules_todo_Controller_Email';
        } elseif ($name == 'edit') {
            return 'Intraface_modules_todo_Controller_Edit';
        }
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('todo');
        $translation = $this->getKernel()->getTranslation('todo');

        $todo = new TodoList($this->getKernel(), $this->name());
        if (!empty($_GET['action']) AND $_GET['action'] == "delete") {
            $todo = new TodoList($this->getKernel(), $this->name());
            $todo->getItem($_GET['item_id'])->delete();
            return new k_SeeOther($this->url());
        } elseif (isset($_GET['action']) && $_GET['action'] == "moveup") {
            $todo->getItem($_GET['item_id'])->getPosition(MDB2::singleton(DB_DSN))->moveUp();
            return new k_SeeOther($this->url());
        } elseif (isset($_GET['action']) && $_GET['action'] == "movedown") {
            $todo->getItem($_GET['item_id'])->getPosition(MDB2::singleton(DB_DSN))->moveDown();
            return new k_SeeOther($this->url());
        }

        $value = $todo->get();
        $value['todo'] = $todo->getAllItems();


        $data = array(
            'value' => $value,
            'kernel' => $this->getKernel()
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/todo');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $todo = new TodoList($this->getKernel(), $_POST['id']);

        // new item
        if (!empty($_POST['new_item'])) {
            $todo->getItem()->save($_POST['new_item'], $_POST['responsible_user_id']);
        }

        // Set done
        $todo->setAllItemsUndone();
        if (!empty($_POST['done'])) {

            foreach ($_POST['done'] AS $key=>$value) {
                if ($todo->getItem($_POST['done'][$key])->setDone()) {
                }
            }
        }
        /*
         if (!empty($_POST['send_list_email'])) {
         $email = new Phpmailer;
         $email->Subject = $todo->get('list_name');
         $email->From = $this->getKernel()->intranet->address->get('email');
         $email->FromName = $this->getKernel()->intranet->get('name');
         $email->addAddress($_POST['send_list_email']);
         $email->Body = $this->getKernel()->setting->get('user', 'todo.email.standardtext') . "\n\n" . $this->getKernel()->setting->get('intranet', 'todo.publiclist') . '?public_key=' . $todo->get('public_key'). "&intranet_key=".$this->getKernel()->intranet->get('private_key')."\n\nMed venlig hilsen\n".$this->getKernel()->user->getAddress()->get('name') . "\n" . $this->getKernel()->intranet->get('name');

         if ($email->Send()) {
         $email_msg = 'E-mailen er sendt';
         }
         else {
         $email_msg = 'E-mailen blev ikke sendt';
         }
         }
         */
        if ($todo->howManyLeft() > 0) {
            return new k_SeeOther($this->url());
        } else {
            return new k_SeeOther($this->url('../'));
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}