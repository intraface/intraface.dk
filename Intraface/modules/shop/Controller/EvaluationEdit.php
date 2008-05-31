<?php
class Intraface_modules_shop_Controller_EvaluationEdit extends k_Controller
{
    function GET()
    {
        if (!empty($this->GET['delete'])) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), (int)$this->GET['delete']);
            $basketevaluation->delete();
            throw new k_http_Redirect($this->url('../'));
        } elseif (isset($this->GET['id'])) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), (int)$this->GET['id']);
            $value = $basketevaluation->get();
        } else {
			$doctrine = $this->registry->get('doctrine');
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), new Intraface_modules_shop_Shop);
            $value = array();
        }
        $settings = $basketevaluation->get('settings');

        $data = array('basketevaluation' => $basketevaluation, 'value' => $value, 'settings' => $settings);

        return $this->render(dirname(__FILE__) . '/tpl/evaluation.tpl.php', $data);

    }

    function POST()
    {
        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), (int)$this->POST['id']);

        if (!$basketevaluation->save($this->POST->getArrayCopy())) {
            throw new Exception('Could not save values');
        }

        throw new k_http_Redirect($this->url('../'));
    }
}