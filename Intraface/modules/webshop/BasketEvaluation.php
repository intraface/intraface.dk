<?php
/**
 * This filter will be added to the basket to do various actions on the
 * basket such as adding product
 *
 * filter_index: number for the running order
 * evaluate_key: price, weight, webshop_coupon (later: (array)product_id, customer_id ...)
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
 * @package Webshop
 * @author  Sune Jensen <sj@sunet.dk>
 * @version @package-version@
 */

require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'MDB2.php';

class BasketEvaluation extends Standard
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
    function __construct($kernel, $id = 0)
    {
        if (!is_object($kernel)) {
            trigger_error("First parameter to BasketEvaluation should be kernel", E_USER_ERROR);
            return false;
        }

        $this->error = new Error;
        $this->id = (int)$id;
        $this->kernel = $kernel;

        $this->db = MDB2::factory(DB_DSN);

        if (PEAR::isError($this->db)) {
            die($this->db->getMessage());
        }

        $this->value['settings'] = array (
            'evaluate_target' => array(
                0 => 'price',
                1 => 'weight',
                2 => 'customer_coupon'),

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
                1 => 'percentage_of_price')
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
    function load()
    {

        $result = $this->db->query("SELECT * FROM webshop_basket_evaluation WHERE active = 1 AND intranet_id = ".$this->db->quote($this->kernel->intranet->get('id'), 'integer')." AND id = ".$this->db->quote($this->id, 'integer'));

        if (PEAR::isError($this->id)) {
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


    /**
     * Saves and validates the evaluation
     *
     * @param struct $input Values to save
     *
     * @return boolean
     */
    function save($input)
    {

        // $input = safeToDb($input);
        $validator = new Validator($this->error);

        $validator->isNumeric($input['running_index'], 'Index is not a valid number');
        $validator->isNumeric($input['evaluate_target_key'], 'Evaluation target is not valid');
        $validator->isNumeric($input['evaluate_method_key'], 'Evaluation method is not valid');
        $validator->isString($input['evaluate_value'], 'Evaluation value is not valid', '', '');
        $validator->isNumeric($input['go_to_index_after'], 'Go to index after is not a valid number');

        $validator->isNumeric($input['action_action_key'], 'Action is not valid');
        $validator->isString($input['action_value'], 'Target is not valid', '', '');
        $validator->isNumeric($input['action_quantity'], 'Action quantity is not a valid number');
        $validator->isNumeric($input['action_unit_key'], 'Action unit is not valid');

        if ($this->error->isError()) {
            return false;
        }


        $sql = "running_index = ".$this->db->quote($input['running_index'], 'integer').", " .
                 "evaluate_target_key = ".$this->db->quote($input['evaluate_target_key'], 'integer').", " .
                 "evaluate_method_key = ".$this->db->quote($input['evaluate_method_key'], 'integer').", " .
                 "evaluate_value = ".$this->db->quote($input['evaluate_value'], 'text').", " .
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
    function delete()
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
    function getList()
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
    function run($basket, $customer = array())
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
                    $evaluate = $basket->getTotalPrice();
                    break;
                case 'weight':
                    $evaluate = $basket->getTotalWeight();
                    break;
                case 'customer_coupon':
                    $evaluate = $customer['coupon'];
                    // coupons can only be evaluated as 'equals' or 'different from'
                    if ($evaluation['evaluate_method_key'] != 0 || $evaluation['evaluate_method_key'] != 1) {
                        $evaluation['evaluate_method_key'] = 1;
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
                    case 'percentage_of_price':
                        $quantity = round(($evaluation['action_quantity']/100)*$basket->getTotalPrice());
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
                        if (!$basket->change($evaluation['action_value'], $quantity, '', 1)) { // 1: it is basketevaluation
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
}
?>