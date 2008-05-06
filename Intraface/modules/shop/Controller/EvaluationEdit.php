<?php
class Intraface_modules_shop_Controller_EvaluationEdit extends k_Controller
{
    function GET()
    {
        if ($this->GET['delete']) {
            $basketevaluation = new Intraface_modules_webshop_BasketEvaluation($this->registry->get('intranet'), (int)$this->GET['delete']);
            $basketevaluation->delete();
            throw new k_http_Redirect($this->url('../'));
        } elseif (isset($this->GET['id'])) {
            $basketevaluation = new Intraface_modules_webshop_BasketEvaluation($this->registry->get('intranet'), (int)$this->GET['id']);
            $value = $basketevaluation->get();
        } else {
            $basketevaluation = new Intraface_modules_webshop_BasketEvaluation($this->registry->get('intranet'));
            $value = array();
        }
        $settings = $basketevaluation->get('settings');

        $data = array('basketevaluation' => $basketevaluation, 'value' => $value, 'settings' => $settings);

        return $this->render(dirname(__FILE__) . '/tpl/evaluation.tpl.php', $data);

    }

    function POST()
    {
        $basketevaluation = new Intraface_modules_webshop_BasketEvaluation($this->registry->get('kernel'), (int)$this->POST['id']);

        if (!$basketevaluation->save($this->POST->getArrayCopy())) {
            throw new Exception('Could not save values');
        }

        throw new k_http_Redirect($this->url('../'));
    }
}