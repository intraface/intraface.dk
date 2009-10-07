<?php
/**
 * Doctrine Gateway to DebtorDoctrine
 *
 * Bruges til at holde styr på debtor.
 *
 * @package Intraface_Debtor
 * @author Sune Jensen
 * @see DebtorDoctrine
 */

class Intraface_modules_debtor_DebtorDoctrineGateway
{
    
    /**
     * @var object
     */
    private $user;

    /**
     * 
     * @var object doctrine record table
     */
    private $table;
    
    /**
     * @var integer type
     */
    private $type_key;
    
    /**
     * Constructor
     *
     * @param object  $user                Userobject
     *
     * @return void
     */
    function __construct($doctrine, $user)
    {
        
        $this->user = $user;
        $this->table = $doctrine->getTable('Intraface_modules_debtor_DebtorDoctrine');
        $this->type_key = 3; // invoice
    }

    /**
     * Finds a product with an id
     *
     * @param integer $id product id
     * @return object
     */
    /*
    function findById($id)
    {
        
        die('Not created');
        $collection = $this->table
            ->createQuery()
            ->select('*, details.*')
            ->innerJoin('Intraface_modules_product_ProductDoctrine.details AS details')
            ->addWhere('active = 1')
            ->addWhere('id = ?', $id)
            ->addOrderBy('details.id')
            ->execute();
    
        if ($collection == NULL || $collection->count() != 1) {
            throw new Intraface_Gateway_Exception('Error finding product from id '.$id);
        } else {
            return $collection->getLast();
        }
        
    }*/

    /**
     * Finds all products
     *
     * Hvis den er fra webshop bør den faktisk opsamle oplysninger om søgningen
     * så man kan se, hvad folk er interesseret i.
     * Søgemaskinen skal være tolerant for stavefejl
     *
     * @param object $search 
     *
     * @return object collection containing products
     */
    public function findByDbQuerySearch($dbquery = NULL)
    {
        
        
        $query = $this->table
            ->createQuery()
            ->select('due_date, status, number, description, this_date, date_sent, contact_id
                item.id, item.product_detail_id, item.product_variation_id, item.product_variation_detail_id, item.quantity,
                item_product.id, item_product.has_variation, 
                item_product_details.id, item_product_details.number, item_product_details.name, item_product_details.description, item_product_details.price, item_product_details.vat,
                item_product_variation.id, item_product_variation.number,
                item_product_variation_detail.id, item_product_variation_detail.price_difference, item_product_variation_detail.weight_difference')
            ->leftJoin('Intraface_modules_debtor_DebtorDoctrine.item item')
            ->innerJoin('item.product item_product')
            ->innerJoin('item_product.details item_product_details WITH item.product_detail_id = item_product_details.id')
            ->leftJoin('item_product.variation item_product_variation WITH item.product_variation_id = item_product_variation.id')
            ->innerJoin('item_product_variation.detail item_product_variation_detail')
            ->addWhere('active = 1')
            ->addWhere('item.active = 1')
            ->addWhere('type = ?', $this->type_key);
            
        
        if ($dbquery->checkFilter("contact_id")) {
            $query = $query->addWhere("contact_id = ?",intval($dbquery->getFilter("contact_id")));
        }

        if ($dbquery->checkFilter("text")) {
            $query = $query->addWhere('description LIKE ? OR girocode = ? OR number = ?', array('%'.$dbquery->getFilter("text").'%', $dbquery->getFilter("text"), $dbquery->getFilter("text"))); 
            //  OR contact_address.name LIKE \"%".$dbquery->getFilter("text")."%\")

        }

        /* To be implemented
        if ($dbquery->checkFilter("product_id")) {
            $dbquery->setCondition("debtor_item.product_id = ".$dbquery->getFilter('product_id'));
            if ($dbquery->checkFilter("product_variation_id")) {
                $dbquery->setCondition("debtor_item.product_variation_id = ".$dbquery->getFilter('product_variation_id'));
            } else {
                $dbquery->setCondition("debtor_item.product_variation_id = 0");
            }
        } */

        if ($dbquery->checkFilter("date_field")) {
            if (in_array($dbquery->getFilter("date_field"), array('this_date', 'date_created', 'date_sent', 'date_executed', 'data_cancelled'))) {
                $date_field = $dbquery->getFilter("date_field");
            } else {
                $this->error->set("Ugyldigt datointerval felt");
            }
        } else {
            $date_field = 'this_date';
        }

        if ($dbquery->checkFilter("from_date")) {
            $date = new Intraface_Date($dbquery->getFilter("from_date"));
            if ($date->convert2db()) {
                $query = $query->addWhere($date_field." >= ?", $date->get());
            } else {
                $this->error->set("Fra dato er ikke gyldig");
            }
        }

        // Poster med fakturadato før slutdato.
        if ($dbquery->checkFilter("to_date")) {
            $date = new Intraface_Date($dbquery->getFilter("to_date"));
            if ($date->convert2db()) {
                $query = $query->addWhere($date_field." <= ? ", $date->get());
            } else {
                $this->error->set("Til dato er ikke gyldig");
            }
        }
        // alle ikke bogførte skal findes
        if ($dbquery->checkFilter("not_stated")) {
            $query = $query->addWhere("voucher_id = 0");
        }

        if ($dbquery->checkFilter("status")) {
            if ($dbquery->getFilter("status") == "-1") {
                // Behøves ikke, den tager alle.
                // $query = $query->addWhere("status >= 0");

            } elseif ($dbquery->getFilter("status") == "-2") {
                // Not executed = åbne
                if ($dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($dbquery->getFilter("to_date"));
                    if ($date->convert2db()) {
                        // Poster der er executed eller cancelled efter dato, og sikring at executed stadig er det, da faktura kan sættes tilbage.
                        $query = $query->addWhere("(date_executed >= \"".$date->get()."\" AND status = 2) OR (date_cancelled >= \"".$date->get()."\") OR status < 2");
                    }
                } else {
                    // Hvis der ikke er nogen dato så tager vi alle dem som på nuværende tidspunkt har status under
                    $query = $query->addWhere("status < 2");
                }

            } elseif ($dbquery->getFilter("status") == "-3") {
                //  Afskrevne. Vi tager først alle sendte og executed.

                if ($this->get("type") != "invoice") {
                    trigger_error("Afskrevne kan kun benyttes ved faktura", E_USER_ERROR);
                }
                die('functionality not implemented yet! contact Intraface');

                $dbquery->setJoin("INNER", "invoice_payment", "invoice_payment.payment_for_id = debtor.id", "invoice_payment.intranet_id = ".$this->kernel->intranet->get("id")." AND invoice_payment.payment_for = 1");
                $query = $query->addWhere("invoice_payment.type = -1");

                if ($dbquery->checkFilter("to_date")) {
                    $date = new Intraface_Date($dbquery->getFilter("to_date"));
                    if ($date->convert2db()) {
                        // alle som er sendte på datoen og som ikke er cancelled
                        $query = $query->addWhere("debtor.date_sent <= '".$date->get()."' AND debtor.status != 3");
                        $query = $query->addWhere("invoice_payment.payment_date <= '".$date->get()."'");
                    }
                } else {
                    // Hvis der ikke er nogen dato så tager vi alle dem som på nuværende tidspunkt har status under
                    $query = $query->addWhere("status = 1 OR status = 2");
                }
            } else {

                $query = $query->addWhere("status = ?", intval($dbquery->getFilter("status")));

            }
        }

        switch ($dbquery->getFilter("sorting")) {
            case 1:
                $query =  $query->addOrderBy('number ASC, item.position');
                break;
            case 2:
                $query =  $query->addOrderBy('contact.number ASC, item.position');
                break;
            case 3:
                $query =  $query->addOrderBy('contact_address.name ASC, item.position');
                break;
            default:
                $query =  $query->addOrderBy('number DESC, item.position');
        }

        
        // $query = $query->getSql(); die($query);

        $collection = $query->execute();
    
        return $collection;
    }
    
    
    /*
    public function getMaxNumber()
    {
        $collection = $this->table
            ->createQuery()
            ->select('id, details.number')
            ->innerJoin('Intraface_modules_product_ProductDoctrine.details AS details')
            ->addWhere('Intraface_modules_product_ProductDoctrine.active = 0 OR Intraface_modules_product_ProductDoctrine.active = 1')
            ->orderBy('details.number')
            ->execute();
    
        if ($collection == NULL || $collection->count() == 0) {
            return 0;
        } else {
            return $collection->getLast()->getDetails()->getNumber();
        }
    }*/
    
    
}
