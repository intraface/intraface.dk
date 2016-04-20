<?php
class Intraface_modules_email_Controller_Email extends k_Component
{
    protected $email;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->getKernel()->useShared('email');
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
        $email = $this->getEmail();
        $value = $email->get();
        $contact = $email->getContact();

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/show');
        $data = array(
            'contact' => $contact,
            'value' => $value,
            'email' => $email,
            'kernel' => $this->getKernel(),
            'redirect' => $redirect
        );
        return $tpl->render($this, $data);
    }

    function renderPdf()
    {
        $pdf = new Document_Cezpdf;

        $size = 12; // font size

        // udskriv adressehoved
        $text = $this->getEmail()->getContact()->address->get('name') .
            "\n" . $this->getEmail()->getContact()->address->get('address') .
            "\n" . $this->getEmail()->getContact()->address->get('postcode') .
            "  " . $this->getEmail()->getContact()->address->get('city') . "\n\n\n";

        $pdf->ezText($text, $size);

        // uskriv subject
        $pdf->ezText($this->getEmail()->get('subject') . "\n\n", $size);

        // udskriv body
        $pdf->ezText($this->getEmail()->get('body') . "\n", $size);

        // udskriv greeting

        return $pdf->output('stream');
    }

    function putForm()
    {
        $this->getKernel()->useShared('email');
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
        $email = $this->getEmail();

        if (!empty($_POST['submit'])) {
            if ($email->queue()) {
                $email->load();
                // This status can be used to change status where the email is coming from.
                if ($redirect->get('id') != 0) {
                    $redirect->setParameter('send_email_status', $email->get('status'));
                }

                /*
                 // Moved to reminder.php triggered on return_redirect_id
                 switch($email->get('type_id')) {
                 case 5: // rykkere
                 if (!$this->getKernel()->user->hasModuleAccess('debtor') OR !$this->getKernel()->user->hasModuleAccess('invoice')) {
                 break;
                 }

                 $this->getKernel()->useModule('debtor');
                 $this->getKernel()->useModule('invoice');
                 $reminder = new Reminder($this->getKernel(), $email->get('belong_to_id'));
                 $reminder->setStatus('sent');

                 break;

                 default:
                 break;
                 }
                 */
                return new k_SeeOther($redirect->getRedirect($this->url()));
            }
        }
        return $this->render();
    }

    function renderHtmlEdit()
    {
        $email = new Email($this->getKernel(), $this->name());
        if ($this->body()) {
            $value = $this->body();
        } else {
            $value = $email->get();
        }

        $contact = $email->getContact();
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/edit');
        $data = array(
            'contact' => $contact,
            'value' => $value,
            'email' => $email,
            'kernel' => $this->getKernel(),
            'redirect' => $redirect
        );
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        $email = $this->getEmail();

        if ($this->getKernel()->user->hasModuleAccess('email')) {
            $email_module = $this->getKernel()->useModule('email');
            $standard_location = $this->url('../');
        } else {
            $standard_location = '/core/restricted/';
        }

        if (isset($_POST['save']) || isset($_POST['send'])) {
            if (isset($_POST['add_contact_login_url'])) {
                $contact = $email->getContact();
                $_POST['body'] .= "\n\nLogin: ".$contact->getLoginUrl();
            }

            if ($id = $email->save($_POST)) {
                if (isset($_POST['send']) && $_POST['send'] != '' && $email->isReadyToSend()) {
                    $email->queue();
                    $email->load();
                    if ($redirect->get('id') != 0) {
                        $redirect->setParameter('send_email_status', $email->get('status'));
                    }
                    return new k_SeeOther($redirect->getRedirect($standard_location));
                }

                return new k_SeeOther($redirect->getRedirect($standard_location));
            } else {
                $value = $_POST;
            }
        } elseif (isset($_POST['delete'])) {
            $email->delete();
            // hmm maybe not the best redirect, but what else?
            return new k_SeeOther($redirect->getRedirect($standard_location));
        } else {
            throw new Exception("Invalid action to perform on email");
        }
        return $this->render();
    }

    function renderHtmlDelete()
    {
        $this->DELETE();
        return new k_SeeOther($this->url('../'));
    }

    function DELETE()
    {
        if (!$this->getEmail()->delete()) {
            throw new Exception($this->t('could not delete e-mail', 'email'));
        }
        return $this->context->url();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getEmail()
    {
        if (is_object($this->email)) {
            return $this->email;
        }

        return ($this->email = $this->context->getGateway()->findById($this->name()));
    }
}
