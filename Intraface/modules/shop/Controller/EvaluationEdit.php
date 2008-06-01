<?php
class Intraface_modules_shop_Controller_EvaluationEdit extends k_Controller
{
    
	function getShop()
	{
		$doctrine = $this->registry->get('doctrine');
		return Doctrine::getTable('Intraface_modules_shop_Shop')->findOneById($this->context->name);
	}
	
    function GET()
    {
        if (!empty($this->GET['delete'])) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop(), (int)$this->GET['delete']);
            $basketevaluation->delete();
            throw new k_http_Redirect($this->url('../'));
        } elseif (isset($this->GET['id'])) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop(), (int)$this->GET['id']);
            $value = $basketevaluation->get();
        } else {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop());
            $value = array();
        }
        $settings = $basketevaluation->get('settings');

        $data = array('basketevaluation' => $basketevaluation, 'value' => $value, 'settings' => $settings);

        return $this->render(dirname(__FILE__) . '/tpl/evaluation.tpl.php', $data);

    }

    function POST()
    {
        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop(), (int)$this->POST['id']);

        if (!$basketevaluation->save($this->POST->getArrayCopy())) {
            throw new Exception('Could not save values');
        }

        throw new k_http_Redirect($this->url('../'));
    }
}