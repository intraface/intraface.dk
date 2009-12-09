<?php
class Intraface_modules_todo_Controller_Email extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->getKernel()->module('todo');
        $translation = $this->getKernel()->getTranslation('todo');
        $this->getKernel()->useModule('contact');
        $this->getKernel()->useShared('email');

        $todo = new TodoList($this->getKernel(), $this->context->name());
        $value['id'] = $todo->get('id');
        $value['subject'] = 'Todoliste';
        $value['body'] = $this->getKernel()->setting->get('user','todo.email.standardtext') . "\n\nMed venlig hilsen\n".$this->getKernel()->user->getAddress()->get('name') . "\n" . $this->getKernel()->intranet->get('name');

        $contacts = $todo->getContacts();

        $contact = new Contact($this->getKernel());
        $keywords = $contact->getKeywords();
        $contact->getDBQuery()->setKeyword('todo');
        $contact_list = $contact->getList();

        $data = array(
            'value' => $value
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/email');
        return $tpl->render($this, $data);
    }

    function postForm()
    {

        $this->getKernel()->module('todo');
        $this->getKernel()->useModule('contact');
        $this->getKernel()->useShared('email');

        $todo = new TodoList($this->getKernel(), $_POST['id']);

        $_POST['contact'] = array_merge($_POST['contact'], $_POST['new_contact']);

        foreach ($_POST['contact'] AS $key=>$value) {

            if (!empty($_POST['contact'][$key])) {

                $todo->addContact($_POST['contact'][$key]);

                $contact = new Contact($this->getKernel(), $_POST['contact'][$key]);

                $email = new Email($this->getKernel());
                $var = array(
                    'body' => $_POST['body'] . "\n\n" . $contact->getLoginUrl(),
                    'subject' => $_POST['subject'],
                    'contact_id' => $contact->get('id'),
                    'type_id' => 6, // type_id 6 er todo
                	'belong_to' => $todo->get('id')
                );

                if ($id = $email->save($var)) {
                    $email->send(Intraface_Mail::factory());
                    return new k_SeeOther($this->url('../'));
                } else {
                    return new k_SeeOther($this->url());
                }
            }
        }
        return $this->render();
    }
}