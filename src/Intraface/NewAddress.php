<?php

/**
 * Styrer adresser til intranet, bruger, kunde og kontaktperson
 *
 * Klassen kan styrer flere forskellige typer af adresser. B�de for intranettet, brugere, kunder og kontaktpersoner.
 * Beskrivelsen af hvilke og med hvilket navn er beskrevet l�ngere nede.
 *
 * TODO Skal vi programmere intranet_id ind i klassen? Det kr�ver at den f�r Kernel.
 *
 * @version 001
 * @author Sune
 */

class NewAddress extends Intraface_Standard {

    var $kernel;
    var $type;
    var $id;
    var $value = array();

    /*
    var $user;

    var $address_id;
    var $fields;
    var $old_address_id;
    */

    function address(&$kernel, $id = 0) {
        this::__construct($kernel, $id);

    }


    function __construct($kernel, $id = 0) {

        $this->kernel = &$kernel;
        $this->id = $id;

        $this->fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean');


    }

    function factory($object, $sub) {


    /*
        $object_name =
    */



    }










    /**
     * Init: loader klassen
     *
     * Her er angivet de typer af adresser den kan h�ndtere med arrayet address_type[].
     * $this-fields er felter i tabellen (db) som overf�res til array og omvendt. M�ske disse
     * engang skal differencieres, s� man angvier hvad feltet i tabellen skal svare til navnet i arrayet.
     * Klassen loader ogs� adressens felter
     *
     * @param	(string)$type	er typen p� adressen. Skal svare til en af dem i $address_type
     * @param	(int)$id BEM�RK id p� intranettet, brugeren, kunde eller kontaktperson. Ikke id p� adressen. Det klare klassen selv.
     * @param	(int)$address_id	Denne bruges kun, i det tilf�lde, hvor man skal finde en gammel adresse. S� angiver man id p� adressen.
     * @return	(int)	Returnere 0 hvis adressen ikke er sat. Returnere id p� adressen hvis det er.
     */
    function _old_Address($type, $id, $old_address_id = 0) {

        $this->db = new DB_Sql;
        $this->id = (int)$id;
        $this->old_address_id = (int)$old_address_id;

        $address_type[1] = 'intranet';
        $address_type[2] = 'user';
        $address_type[3] = 'contact';
        $address_type[4] = 'contact_delivery';
        $address_type[5] = 'contact_invoice';
        $address_type[6] = 'contactperson';

        // $this->fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'contactname', 'ean');
        $this->fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean');

        if ($i = array_search($type, $address_type)) {
            $this->type = $i;
        }
        else {
            throw new Exception('Ugyldig address type');
        }

        return($this->address_id = $this->load());
    }

    /**
     * Private: Loader data ind i array
     */
    function load() {
        if ($this->old_address_id != 0) {
            $sql = "id = ".$this->old_address_id;
        }
        else {
            $sql = "type = ".$this->type." AND belong_to_id = ".$this->id." AND active = 1";
        }

        $this->db->query("SELECT * FROM address WHERE ".$sql);
        if ($this->db->numRows() > 1) {
            throw new Exception('Der er mere end 1 aktiv adresse', FATAL);
        }
        elseif ($this->db->nextRecord()) {
            $this->value['address_id'] = $this->db->f('id');
            for ($i = 0, $max = count($this->fields); $i<$max; $i++) {
                $this->value[$this->fields[$i]] = $this->db->f($this->fields[$i]);
            }
            return $this->db->f('id');
        }
        else {
            return 0;
        }
    }

    /**
     * Public: Denne funktion gemmer data. At gemme data vil sige, at den gamle adresse gemmes, men den nye aktiveres.
     *
     * @param	(array)$array_var	et array med felter med adressen. Se felterne i init funktionen: $this->fields
     * $return	(int)	Returnere 1 hvis arrayet er gemt, 0 hvis det ikke er. Man kan ikke gemme p� en old_address.
     */
    function save($array_var) {

        $db = new DB_sql;
        if ($this->old_address_id != 0) {
            return 0;
        }
        elseif ($this->id == 0) {
            throw new Exception('Address:save(): Id kan ikke v�re 0 n�r du fors�ger at gemme adresse', FATAL);
        }
        elseif (count($array_var) > 0) {

            $db->query("SELECT * FROM address WHERE id = ".$this->address_id);
            if ($db->nextRecord()) {
                $do_update = 0;
                for ($i = 0, $max = count($this->fields), $sql=''; $i<$max; $i++) {
                    if (array_key_exists($this->fields[$i], $array_var) AND isset($array_var[$this->fields[$i]])) {
                        $sql .= $this->fields[$i]." = '".$array_var[$this->fields[$i]]."', ";
                        if ($db->f($this->fields[$i]) != $array_var[$this->fields[$i]]) {
                            $do_update = 1;
                        }
                    }
                    else {
                        // $sql .= $this->fields[$i]." = '', ";
                    }

                }
            }
            else {
                // Kun hvis der rent faktisk gemmes nogle v�rdier opdaterer vi
                $do_update = 0;
                for ($i = 0, $max = count($this->fields), $sql = ''; $i<$max; $i++) {
                    if (array_key_exists($this->fields[$i], $array_var) AND isset($array_var[$this->fields[$i]])) {
                        $sql .= $this->fields[$i]." = '".$array_var[$this->fields[$i]]."', ";
                        $do_update = 1;
                    }
                    else {
                        // $sql .= $this->fields[$i]." = \"\", ";
                    }
                }
            }

            if ($do_update == 0) {
                // Hmmmmm, der er slet ikke nogen felter der er �ndret! S� gemmer vi ikke, men siger at det gik godt :-)
                return 1;
            }
            else {
                $this->db->query("UPDATE address SET active = 0 WHERE type = ".$this->type." AND belong_to_id = ".$this->id);
                $this->db->query("INSERT INTO address SET ".$sql." type = ".$this->type.", belong_to_id = ".$this->id.", active = 1, changed_date = NOW()");
                $this->adress_id = $this->db->insertedId();
                $this->load();
                return 1;
            }
        }
        else {
            // Der var slet ikke noget indhold i arrayet, s� vi lader v�re at opdatere, men siger, at vi gjorde.
            return 1;
        }
    }

    /**
     * Public: Opdatere en adresse.
     *
     * Denne funktion overskriver den nuv�rende adresse. Benyt som udagangspunkt ikke denne, da historikken p� adresser skal gemmes.
     *
     * @param	(array)$array_var	et array med felter med adressen. Se felterne i init funktionen: $this->fields
     * $return	(int)	Returnere 1 hvis arrayet er gemt, 0 hvis det ikke er. Man kan ikke gemme p� en old_address.
     */
    function update($array_var) {
        if ($this->old_address_id != 0) {
            return 0;
        }
        elseif ($this->address_id == 0) {
            $this->save($array_var);
        }
        elseif ($this->id == 0) {
            throw new Exception("Id kan ikke v�re 0 n�r du fors�ger at gemme adresse", FATAL);
        }
        else {
            for ($i = 0, $max = count($this->fields), $sql = ''; $i<$max; $i++) {
                if (isset($array_var[$this->fields[$i]])) {
                    $sql .= $this->fields[$i]." = \"".$array_var[$this->fields[$i]]."\", ";
                }
                else {
                    $sql .= $this->fields[$i]." = \"\", ";
                }
            }

            $this->db->query("UPDATE address SET ".$sql." changed_date = NOW() WHERE id = ".$this->address_id);
            $this->load();
            return 1;
        }
    }

}
?>
