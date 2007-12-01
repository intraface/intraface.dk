<?php
class Translation2_Frontend_Controller_Index extends k_Controller
{
    public $map = array('search' => 'Translation2_Frontend_Controller_Search');

    function GET()
    {
        $db = $this->registry->get('db_sql');

        $message = array();
        $id = 0;
        $overwrite = 0;

        if(isset($this->GET['edit_id']) && isset($this->GET['page_id'])) {
            $db->query("SELECT * FROM core_translation_i18n WHERE id = \"".safeToDb(urldecode($this->GET['edit_id']))."\" AND page_id = \"".safeToDb(urldecode($this->GET['page_id']))."\"");
            if($db->nextRecord()) {
                $id = $db->f('id');
                $page_id = $db->f('page_id');
                $dk = $db->f('dk');
                $uk = $db->f('uk');
                $overwrite = 1;
                $message[] = "Du er ved at rette en tekst, det vil �ndre alle steder hvor identifier benyttes.";
            }
            else {
                $message[] = "Kunne ikke finde den søgte post";
            }
        }

        $data = array('message' => $message, 'id' => $id, 'overwrite' => $overwrite, 'db' => $db);

        return $this->render(dirname(__FILE__) . '/../tpl/index-tpl.php', $data);
    }

    function POST()
    {
        $db = $this->registry->get('db_sql');

        $message = array();

        $id = mysql_escape_string(trim($this->POST['id']));
        @$page_id = mysql_escape_string(trim($this->POST['page_id']));
        @$new_page_id = mysql_escape_string(trim($this->POST['new_page_id']));
        $dk = mysql_escape_string(trim($this->POST['dk']));
        $uk = mysql_escape_string(trim($this->POST['uk']));

        if($id == '') {
            $message[] = 'Identifier er ikke udfyldt';
        }

        if($page_id == '' && $new_page_id == '') {
            $message[] = 'Der er ikke angivet et PageId';
        }

        if($dk == '') {
            $message[] = 'DK er ikke udfyldt';
        }

        if($uk == '') {
            $message[] = 'UK er ikke udfyldt';
        }

        if(count($message) == 0) {

            if($new_page_id != '') {
                $page_id = $new_page_id;
            }

            $exists = array();
            $db->query("SELECT * FROM core_translation_i18n WHERE (page_id = \"".$page_id."\" OR page_id = 'common') AND id = \"".$id."\"");
            if($db->numRows() > 0 && $_POST['overwrite'] != '1') {
                $message[] = "Den indtastede identifier eksisterer allerede.<br />Hvis det er under samme page_id vil den blive overskrevet. Hvis det er under 'Common' vil den blive oprettet.";
                $overwrite = 1;

                while($db->nextRecord()) {
                    $exists[$db->f('page_id')]['id'] = $db->f('id');
                    $exists[$db->f('page_id')]['page_id'] = $db->f('page_id');
                    $exists[$db->f('page_id')]['dk'] = $db->f('dk');
                    $exists[$db->f('page_id')]['uk'] = $db->f('uk');
                }
            }

            if(count($message) == 0) {
                $db->query("SELECT id, page_id FROM core_translation_i18n WHERE page_id = \"".$page_id."\" AND id = \"".$id."\"");
                if($db->nextRecord()) {
                    $id = $db->f('id');
                    $db->query("UPDATE core_translation_i18n SET dk = \"".$dk."\", uk = \"".$uk."\" WHERE page_id = \"".$page_id."\" AND id = \"".$id."\"");
                    $success['text'] = "F�lgende translation er opdateret";
                }
                else {
                    $db->query("INSERT INTO core_translation_i18n SET dk = \"".$dk."\", uk = \"".$uk."\", page_id = \"".$page_id."\", id = \"".$id."\"");

                    $success['text'] = "Følgende translation er indsat";
                }
                $success['id'] = $id;
                $success['page_id'] = $page_id;
                $success['dk'] = $dk;
                $success['uk'] = $uk;
                $id = $dk = $uk = $new_page_id =  '';
            }
        }
        throw new k_http_Redirect($this->url());
    }
}

