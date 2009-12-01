<?php
class Intraface_modules_todo_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_todo_Controller_Todo';
        } elseif ($name == 'setting') {
            return 'Intraface_modules_todo_Controller_Setting';
        } elseif ($name == 'create') {
            return 'Intraface_modules_todo_Controller_Edit';
        }
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('todo');
        $translation = $this->getKernel()->getTranslation('todo');

        $todo = new TodoList($this->getKernel());

        $todo_list = $todo->getList();
        $todo_done = $todo->getList('done');

        $data = array(
            'todo_list' => $todo_list,
            'todo_done' => $todo_done
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}