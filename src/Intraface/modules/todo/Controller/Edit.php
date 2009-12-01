<?php
class Intraface_modules_todo_Controller_Edit extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('todo');
        $translation = $this->getKernel()->getTranslation('todo');

        if (is_numeric($this->context->name())) {
            $todo = new TodoList($this->getKernel(), $this->context->name());
        } else {
            $todo = new TodoList($this->getKernel());
        }

        $value = $todo->get();
        $value['todo'] = $todo->getUndoneItems();

        $data = array(
            'value' => $value,
            'todo' => $todo,
            'kernel' => $this->getKernel()
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $module = $this->getKernel()->module('todo');
        $translation = $this->getKernel()->getTranslation('todo');
        $todo = new TodoList($this->getKernel(), $_POST['id']);
        if ($todo->save(array(
            'list_name' => $_POST['list_name'],
            'list_description' => $_POST['list_description']
        ))) {

            foreach ($_POST['todo'] AS $key=>$value) {
                if (isset($_POST['item_id'])) {
                    $item_id = $_POST['item_id'];
                    if ($todo->getItem($_POST['item_id'][$key])->save($_POST['todo'][$key], $_POST['responsible_user_id'][$key])) {
                    }
                } else {
                    $item_id = 0;
                }
            }
            return new k_SeeOther($this->url('../' . $_POST['id']));
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}