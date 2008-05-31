<?php
/**
 * This filter will be added to the basket to do various actions on the
 * basket such as adding product
 *
 * running_index: number for the running order
 * evaluate_target_key: price, weight, webshop_coupon (later: (array)product_id, customer_id ...)
 * evaluate_method: > < == != [maybe (>= <=) ??]
 * evaluate_value: the number or value e.g. 600 if price.
 * go_to_index_after: makes i possible to jump further in the filter.
 *
 * action_key: no_action, add_product_id, (later: add_order_text, ...?)
 * action_value: id or text
 * action_quantity: number of times the action e.g 10 x product_id
 * action_unit: quantity or percentage.
 *
 * PHP version 5
 *
 * @category Application
 * @package  Intraface_Shop
 * @author   Sune Jensen <sj@sunet.dk>
 * @version @package-version@
 */

require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'MDB2.php';
require_once 'Intraface/Validator.php';

class BasketEvaluation extends Intraface_Standard
{
     public $kernel;
     public $error;
     private $db;
     private $id;
     private $values;
     private $settings;

    /**
     * Constructor
     *
     * @param object $kernel Kernel registry
     * @param int    $id     Id of the BasketEvaluation
     *
     * @return void
     */
    public function __construct($kernel, $id = 0)
    {
        if (!is_object($kernel)) {
            trigger_error("First parameter to BasketEvaluation should be kernel", E_USER_ERROR);
            return false;
        }

        $this->error = new Intraface_Error;
        $this->id = (int)$id;
        $this->kernel = $kernel;

        $this->db = MDB2::singleton(DB_DSN);

        if (PEAR::isError($this->db)) {
            die($this->db->getMessage());
        }

        $this->value['settings'] = array (
            'evaluate_target' => array(
                0 => 'price',
                1 => 'weight',
                2 => 'customer_coupon',
                3 => 'customer_country'),

            'evaluate_method' => array(
                0 => 'equals',
                1 => 'different_from',
                2 => 'at_least',
                3 => 'at_most'),

            'action_action' => array(
                0 => 'no_action',
                1 => 'add_product_id'),

            'action_unit' => array(
                0 => 'pieces',
                1 => 'percentage_of_price_including_vat',
                2 => 'percentage_of_price_exclusive_vat')
        );

        if ($this->id != 0) {
            $this->load();
        }

    }

    /**
     * Loads the evaluation
     *
     * @return boolean
     */
    private function load()
    {
        $result = $this->db->query("SELECT * FROM webshop_basket_evaluation WHERE active = 1 AND intranet_id = ".$this->db->quote($this->kernel->intranet->get('id'), 'integer')." AND id = ".$this->db->quote($this->id, 'integer'));

        if (PEAR::isError($result)) {
            trigger_error($result->getMessage() . $result->getUserInfo(), E_USER_ERROR);
        }

        if ($result->numRows() == 0) {
            trigger_error('Invalid id in BasketEvaluation->load', E_USER_ERROR);
            return false;
        }

        $this->value = array_merge($this->value, $result->fetchRow(MDB2_FETCHMODE_ASSOC));

        $this->value['evaluate_target'] = $this->value['settings']['evaluate_target'][$this->value['evaluate_target_key']];
        $this->value['evaluate_method'] = $this->value['settings']['evaluate_method'][$this->value['evaluate_method_key']];
        $this->value['action_action'] = $this->value['settings']['action_action'][$this->value['action_action_key']];
        $this->value['action_unit'] = $this->value['settings']['action_unit'][$this->value['action_unit_key']];

        return true;
    }

    function validate($input)
    {
        $validator = new Intraface_Validator($this->error);

        $validator->isNumeric($input['running_index'], 'Index is not a valid number');
        $validator->isNumeric($input['evaluate_target_key'], 'Evaluation target is not valid');
        $validator->isNumeric($input['evaluate_method_key'], 'Evaluation method is not valid');
        $validator->isString($input['evaluate_value'], 'Evaluation value is not valid', '', 'allow_empty');
        $validator->isNumeric($input['go_to_index_after'], 'Go to index after is not a valid number');
        $validator->isNumeric($input['action_action_key'], 'Action is not valid');
        $validator->isString($input['action_value'], 'Target is not valid', '', 'allow_empty');
        $validator->isNumeric($input['action_quantity'], 'Action quantity is not a valid number', 'zero_or_greater');
        $validator->isNumeric($input['action_unit_key'], 'Action unit is not valid');

        if ($this->error->isError()) {
            return false;
        }

        return true;

    }

    /**
     * Saves and validates the evaluation
     *
     * @param struct $input Values to save
     *
     * @return boolean
     */
    public function save($input)
    {
        settype($input['evaluate_value'], 'string');
        settype($input['action_value'], 'string');
        settype($input['action_quantity'], 'integer');
        settype($input['evaluate_value_case_sensitive'], 'integer');

        if (!$this->validate($input)) {
            return false;
        }

        $sql = "running_index = ".$this->db->quote($input['running_index'], 'integer').", " .
                 "evaluate_target_key = ".$this->db->quote($input['evaluate_target_key'], 'integer').", " .
                 "evaluate_method_key = ".$this->db->quote($input['evaluate_method_key'], 'integer').", " .
                 "evaluate_value = ".$this->db->quote($input['evaluate_value'], 'text').", " .
                 "evaluate_value_case_sensitive = ".$this->db->quote($input['evaluate_value_case_sensitive'], 'integer').", " .
                 "go_to_index_after = ".$this->db->quote($input['go_to_index_after'], 'integer').", " .
                 "action_action_key = ".$this->db->quote($input['action_action_key'], 'integer').", " .
                 "action_value = ".$this->db->quote($input['action_value'], 'text').", " .
                 "action_quantity = ".$this->db->quote($input['action_quantity'], 'integer').", " .
                 "action_unit_key = ".$this->db->quote($input['action_unit_key'], 'integer');

        if ($this->id != 0) {
            $result = $this->db->exec("UPDATE webshop_basket_evaluation SET ".$sql." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);

            if (PEAR::isError($result)) {
                trigger_error($result->getMessage() . $result->getUserInfo(), E_USER_ERROR);
                return false;
            }

        } else {
            $result = $this->db->query("INSERT INTO webshop_basket_evaluation SET ".$sql.", intranet_id = ".$this->kernel->intranet->get('id').", id = ".$this->id);

            if (PEAR::isError($result)) {
                trigger_error($result->getMessage() . $result->getUserInfo(), E_USER_ERROR);
                return false;
            }

            $this->id = $this->db->lastInsertID();
            if (PEAR::isError($this->id)) {
                trigger_error($result->getMessage() . $result->getUserInfo(), E_USER_ERROR);
            }
        }

        return true;
    }

    /**
     * Deletes the evaluation
     *
     * @return boolean
     */
    public function delete()
    {
        $result = $this->db->exec("UPDATE webshop_basket_evaluation SET active = 0 WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage() . $result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * Gets a list with evaluations
     *
     * @return boolean
     */
    public function getList()
    {
        $result = $this->db->query("SELECT * FROM webshop_basket_evaluation WHERE active = 1 AND intranet_id = ".$this->kernel->intranet->get('id').' ORDER BY running_index');

        if (PEAR::isError($this->id)) {
            trigger_error($result->getMessage() . $result->getUserInfo(), E_USER_ERROR);
        }

        $i = 0;
        $evaluation = array();

        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $evaluation[$i] = $row;
            $evaluation[$i]['evaluate_target'] = $this->value['settings']['evaluate_target'][$row['evaluate_target_key']];
            $evaluation[$i]['evaluate_method'] = $this->value['settings']['evaluate_method'][$row['evaluate_method_key']];
            $evaluation[$i]['action_action'] = $this->value['settings']['action_action'][$row['action_action_key']];
            $evaluation[$i]['action_unit'] = $this->value['settings']['action_unit'][$row['action_unit_key']];

            $i++;
        }

        return $evaluation;
    }

    /**
     * Runs all evaluations
     *
     * @param object $basket   A basket object
     * @param array  $customer What is this for
     *
     * @return boolean
     */
    public function run($basket, $customer = array())
    {
        $evaluations = $this->getList();
        $go_to_index = 0;

        $basket->removeEvaluationProducts();

        foreach ($evaluations AS $evaluation) {
            // If have been requested to move to a higher index, we make sure we do that
            if ($go_to_index > $evaluation['running_index']) {
                continue;
            }

            $evaluation_result = false;

            switch($evaluation['evaluate_target']) {
                case 'price':
                    $evaluate = (double)$basket->getTotalPrice();
                    settype($evaluation['evaluate_value'], 'double');
                    break;
                case 'weight':
                    $evaluate = (int)$basket->getTotalWeight();
                    settype($evaluation['evaluate_value'], 'integer');
                    break;
                case 'customer_coupon':
                    settype($customer['customer_coupon'], 'string');
                    if($evaluation['evaluate_value_case_sensitive'] != 1) {
                        $evaluate = strtolower(trim($customer['customer_coupon']));
                        $evaluation['evaluate_value'] = strtolower($evaluation['evaluate_value']);
                    } else {
                        $evaluate = trim($customer['customer_coupon']);
                    }
                    // coupons can only be evaluated as 'equals' or 'different from'
                    if ($evaluation['evaluate_method'] != 'equals' && $evaluation['evaluate_method'] != 'different_from') {
                        $evaluation['evaluate_method'] = 'different_from';
                    }
                    break;
                case 'customer_country':
                    settype($customer['country'], 'string');
                    if($evaluation['evaluate_value_case_sensitive'] != 1) {
                        $evaluate = strtolower(trim($customer['country']));
                        $evaluation['evaluate_value'] = strtolower($evaluation['evaluate_value']);
                    } else {
                        $evaluate = trim($customer['country']);
                    }
                    // country can only be evaluated as 'equals' or 'different from'
                    if ($evaluation['evaluate_method'] != 'equals' && $evaluation['evaluate_method'] != 'different_from') {
                        $evaluation['evaluate_method'] = 'different_from';
                    }
                    break;
                default:
                    trigger_error("Invalid evaluation_target in BasketEvaluation->run", E_USER_ERROR);
                    return false;
            }

            switch($evaluation['evaluate_method']) {
                case 'equals':
                    if ($evaluate == $evaluation['evaluate_value']) {
                        $evaluation_result = true;
                    }
                    break;
                case 'different_from':
                    if ($evaluate != $evaluation['evaluate_value']) {
                        $evaluation_result = true;
                    }
                    break;
                case 'at_least':
                    if ($evaluate >= $evaluation['evaluate_value']) {
                        $evaluation_result = true;
                    }
                    break;
                case 'at_most':
                    if ($evaluate <= $evaluation['evaluate_value']) {
                        $evaluation_result = true;
                    }
                    break;
                default:
                    trigger_error("Invalid evaluation_method in BasketEvaluation->run", E_USER_ERROR);
                    return false;
            }

            if ($evaluation_result) {
                $go_to_index = $evaluation['go_to_index_after'];

                switch($evaluation['action_unit']) {
                    case 'pieces':
                        $quantity = $evaluation['action_quantity'];
                        break;
                    case 'percentage_of_price_including_vat':
                        $quantity = round(($evaluation['action_quantity']/100)*$basket->getTotalPrice());
                        break;
                    case 'percentage_of_price_exclusive_vat':
                        $quantity = round(($evaluation['action_quantity']/100)*$basket->getTotalPrice('exclusive_vat'));
                        break;
                    default:
                        trigger_error("Invalid action_unit in BasketEvaluation->run", E_USER_ERROR);
                        return false;
                }

                switch($evaluation['action_action']) {
                    case 'no_action':
                        // fine nothing is done
                        break;
                    case 'add_product_id':
                        if (!$basket->change($evaluation['action_value'], $quantity, '', 0, 1)) { // 1: it is basketevaluation
                            $this->error->set('Could not add product - invalid id or out of stock');
                        }
                        break;
                    default:
                        trigger_error("Invalid action_action in BasketEvaluation->run", E_USER_ERROR);
                        return false;
                }
            }
        }
        return true;
    }

    function getId()
    {
        return $this->id;
    }
}