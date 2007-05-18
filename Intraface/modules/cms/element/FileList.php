<?php
class CMS_FileList extends CMS_Element {

    var $select_methods = array('single_file');

    function __construct(& $section, $id = 0) {
        $this->value['type'] = 'filelist';
        parent::__construct($section, $id);
        $this->section->kernel->useShared('filehandler');
    }

    function load_element() {
        $this->value['filelist_select_method'] = $this->parameter->get('filelist_select_method');
        /*

        if (!$this->parameter->get('chosen_files')) {
            $this->value['chosen_files'] = array();
        }
        else {
            $this->value['chosen_files'] = unserialize($this->parameter->get('chosen_files'));
        }
        $this->value['caption'] = $this->parameter->get('caption');
        $this->value['files'] = array();
        if (!empty($this->value['chosen_files']) AND is_array($this->value['chosen_files']) AND count($this->value['chosen_files']) > 0) {
            $i = 0;
             foreach ($this->value['chosen_files'] AS $file_id) {
                $filehandler = new FileHandler($this->section->kernel, $file_id);
                $file_type = $filehandler->get('file_type');
                $this->value['files'][$i] = $filehandler->get();
                $i++;
            }
        }
        */

        if(false) { // benytter keyword

            // Dette skal lige implementeres, så hvis man har filemanager, og har benyttet nøgleord, så
            // skal array returneres ved hjælp af Filemanager. Vær opmærksom på hvis en bruger der ikke har
            // Filemanager ser elementet, lavet af en der har filemanager. her i vis, skal der nok overrules om
            // brugeren har filemanager.
            $this->value['keyword_id'] = $this->parameter->get('keyword_id');

            $filemanager = new FileManager($this->kernel);
            $filemanager->createDBQuery();
            $filemanager->dbquery->setKeyword($this->value['keyword_id']);
            $files = $filemanager->getList();


        }
        else { // Enkeltfiler
            // print("this->id i gallery: ".$this->id."<br /><br />");
            $shared_filehandler = $this->kernel->useShared('filehandler');
            $shared_filehandler->includeFile('AppendFile.php');
            $append_file = new AppendFile($this->kernel, 'cms_element_filelist', $this->id);
            $files = $append_file->getList();
        }


        $i = 0;
        foreach ($files AS $file) {
            if(isset($file['file_handler_id'])) {
                $id = $file['file_handler_id'];
                $append_file_id = $file['id'];
            }
            else {
                $id = $file['id'];
                $append_file_id = 0;
            }

            $filehandler = new FileHandler($this->kernel, $id);
            $filehandler->createInstance();
            // HACK lille hack - til at undgå at vi får filer med som ikke har beskrivelser (formentlig slettede filer)
            if (!$filehandler->get('description')) continue;
            $this->value['files'][$i] = $filehandler->get();
            $this->value['files'][$i]['append_file_id'] = $append_file_id;
            // $this->value['pictures'][$i]['show_uri'] = $file_uri;

            $i++;
        }

        return 1;

    }

    function validate_element($var) {
        $validator = new Validator($this->error);
        $validator->isString($var['caption'], 'error in caption', '', 'allow_empty');
        if (!empty($var['files']) AND !is_array($var['files'])) {
            $this->error->set('error in files - has to be an array');
        }
        if (!empty($var['filelist_select_method']) AND !in_array($var['filelist_select_method'], $this->select_methods)) {
            $this->error->set('error in filelist_select_method');
        }

        // egentlig bør de enkelte værdier i arrayet også valideres

        if ($this->error->isError()) {
            return 0;
        }
        return 1;
    }

    function save_element($var) {
        $var['caption'] = strip_tags($var['caption']);

        if (!$this->validate_element($var)) return 0;

        $this->parameter->save('caption', $var['caption']);
        $this->parameter->save('chosen_files', serialize($var['files']));
        $this->parameter->save('chosen_files', $var['filelist_select_method']);


        return 1;
    }
}
?>