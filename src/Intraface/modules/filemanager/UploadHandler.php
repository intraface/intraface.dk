<?php
/**
 * Upload handler. Klarer upload af b�de billeder og dokumenter.
 *
 * Kan det passe at multiple upload endnu ikke virker? Det beh�ver den s�dan set heller ikke:
 * Vi skal bare have nedenst�ende til at fungere.
 *
 * Desuden skal jeg bruge s�dan en temporaryUpload (en der s�ttes i gang, s�
 * snart man klikker en fil ind i et fileelement (s� den begynder at uploade med det samme).
 * Derved kan vi f� et automatisk preview af temp-filen, s� snart den er oppe p� serveren.
 * Vi f�r dem s� skrevet ind p� siden med en checkbox for at sige, om man vil uploade
 * filerne. Dem der er tjekket importerer vi s� med import-funktionen i stedet for at uploade
 * dem p� ny. P� den m�de kommer upload til at g� knalderhurtigt :) Det hele skal naturligvis
 * laves ud fra et single file-input felt, som det ses p�
 *
 * http://the-stickman.com/web-development/javascript/upload-multiple-files-with-a-single-file-element/
 *
 * Men selve visningen skal foreg� lidt ligesom hos http://www.air4web.com/files/upload/
 *
 * Der underst�ttes dog kun billeder, og vi skal underst�tte det hele, men hvis det er billeder
 * vises en thump. Hvis det er andet vises et ikon.
 *
 * Alternativt kan man bare se selve filnavnet, som det er p� gmail. De uploader ogs� tingene med det samme
 * - og s� har du mulighed for at slette, det der ikke skal sendes med alligevel. Jeg t�nkte
 * at vi gjorde det p� nogenlunde samme m�de. Vi skal bare have lavet en eller anden m�de
 * at f� slettet temporary igen.
 *
 * N�r man s� har uploadet alle sine filer p� en gang, sendes man til en batch editeringsside.
 * Til den mangler vi lige et eller andet med s�gning i filsystemet, f�r vi kan implementere det.
 *
 * Endelig skal du lige give et bud p�, hvordan filehandleren skal spille sammen med de andre
 * moduler, n�r man skal v�lge filer derfra!
 *
 * Der mangler en funktion til at hente alle billederne ud automatisk - og andre filtyper ogs�.
 * Den har man brug for ogs� selvom man ikke har adgang til selve filarkivet. For t�nk sig
 * n�r man er s�dan en der m�ske kun har lov at inds�tte billeder p� en side, der er oploadet
 * til filarkivet.
 *
 * @package Intraface
 * @author: Sune
 * @version: 1.0
 *
 */
class UploadHandler extends Intraface_Standard
{
    /**
     * @var object
     */
    private $file_handler;

    /**
     * @todo
     */
    private $upload_setting;

    /**
     * @var string
     */
    private $upload_path;

    /**
     * @var todo
     */
    private $imported_files;

    /**
     * Constructor
     *
     * @todo this file should rather have a config object. Really it does not use the filehandler for much
     *       then the filehandler->save method should rather accept some kind of file object which this
     *       object could generate?
     *
     * @param object $file_handler A filehandler
     *
     * @return void
     */
    function __construct($file_handler)
    {
        if (!is_object($file_handler)) {
            throw new Exception("UploadHandler needs a filehandler- or filemanagerobject (1)");
        }

        $this->file_handler = $file_handler;

        //$this->upload_path = PATH_UPLOAD.$this->file_handler->kernel->intranet->get('id').'/';
        $this->upload_path = $this->file_handler->getUploadPath();

        $this->upload_setting['max_file_size'] = 500000;
        $this->upload_setting['allow_only_images'] = 0;
        $this->upload_setting['allow_only_documents'] = 0;
        $this->upload_setting['file_accessibility'] = 'intranet';
        $this->upload_setting['add_keyword'] = '';
    }

    /**
     * Benyttes til at s�tte indstillinger for upload
     * Se indstillingenre i Uploadhandler->uploadhandler (init funktionen).
     *
     * @param string $setting @todo
     * @apram string $value   @todo
     *
     * @return void
     */
    function setSetting($setting, $value)
    {
        if (isset($this->upload_setting[$setting])) {
            $this->upload_setting[$setting] = $value;
        } else {
            throw new Exception("Ugyldig setting ".$setting." i UploadHandler->setSetting");
        }
    }

    /**
     * @todo What does this do, and it should probably be rewritten - seems the returns are pretty strange
     *
     * @return boolean
     */
    function multipleUpload()
    {
        $upload = new HTTP_Upload("en");
        $files = $upload->getFiles();
        $return = true;
        foreach ($files as $file) {
            if ($error = $file->isError()) {
                $this->file_handler->error->set($file->getMessage());
                $return = false;
            }
        }

        if ($return === false) {
            return false;
        }

        foreach ($files as $file) {
            if ($this->upload($file) === false) {
                $return = false;
            }
        }

        return $return;
    }

    function uploadAsTemporary()
    {

    }

    /**
     * Upload fil
     *
     * @param string $input       er navnt p� inputfeltet eller et http_upload_file object
     * @param string $upload_type har enten v�rdien 'save' (gemmer filen i filehandler og returnerer id), eller 'temporary' (gemmer i filehandler, men med temporary sat) eller 'do_not_save' (flytter filen til tempdir og returnerer fil-sti)
     *
     * @return boolean
     */
    function upload($input, $upload_type = 'save')
    {

        if (!in_array($upload_type, array('save', 'temporary', 'do_not_save'))) {
            throw new Exception("Anden parameter '".$upload_type."' er ikke enten 'save', 'temporary' eller 'do_not_save' i UploadHandler->upload");
        }

        if (is_string($input) && $input != '') {
            $upload = new HTTP_Upload('en');
            $file = $upload->getFiles($input);
            if ($file->isError()) {
                $this->file_handler->error->set($file->getMessage());
                return false;
            }
        } elseif (is_object($input) && strtolower(get_class($input)) == 'http_upload_file') {
            $file = $input;
        } else {
            throw new Exception("Invalid input in FileHandler->upload");
        }

        $prop = $file->getProp(); // returnere et array med oplysninger om filen.

        if (!$file->isValid()) {
            $this->file_handler->error->set($file->getMessage());
            return false;
        }
        if (!isset($prop['ext']) || $prop['ext'] == "") {
            $this->file_handler->error->set("error in file - needs extension");
            return false;
        }

        if ($prop['size'] > $this->upload_setting['max_file_size']) {
            $this->file_handler->error->set("error in file - too big");
        }

        $mime_type = $this->file_handler->_getMimeType($prop['type'], 'mime_type');
        if ($mime_type === false) {
            $this->file_handler->error->set("error in file - not allowed mime_type (".$prop['ext'].", ".$prop['type'].")");
            return false;
        }

        // @todo: we have a problem here because csv files have the same mime type as exe files!

        if ($mime_type['allow_user_upload'] == 0) {
            $this->file_handler->error->set("error in file - you have no permissions");
            return false;
        }

        if ($this->upload_setting['allow_only_images'] == 1 && $mime_type['image'] == 0) {
            $this->file_handler->error->set("error in file - only images are allowed");
            return false;
        }

        if ($this->upload_setting['allow_only_documents'] == 1 && $mime_type['image'] == 1) {
            $this->file_handler->error->set("error in file - only documents are allowed");
            return false;
        }

        if ($this->file_handler->error->isError()) {
            return false;
        }


        if ($upload_type == 'do_not_save') {
            // $tmp_server_file_name = date("U").$this->file_handler->kernel->randomKey(10).".".$mime_type['extension'];
            $tmp_server_file = $this->file_handler->createTemporaryFile($prop['real']);

            $file->setName($tmp_server_file->getFileName());

            /*
             * This is now handled by TemporaryFile
            if (!is_dir($this->file_handler->tempdir_path)) {
                if (!mkdir($this->file_handler->tempdir_path)) {
                    throw new Exception("Kunne ikke oprette mappe i FileHandler->upload");
                }
            }
             */

            $moved = $file->moveTo($tmp_server_file->getFileDir());

            if (PEAR::isError($moved)) {
                throw new Exception("Kunne ikke flytte filen i UploadHandler->upload");
            }

            return array(
                'tmp_file_path' => $tmp_server_file->getFilePath(),
                'tmp_file_name' => $tmp_server_file->getFileName(),
                'file_name' => $prop['real'],
                'image' => $mime_type['image'],
                'icon' => $mime_type['icon']
                );


        } elseif ($upload_type == 'temporary') {
            $id = $this->file_handler->save($prop['tmp_name'], $prop['real'], 'temporary', $mime_type['mime_type']);

            return $id;
        } else {

            # PHP's mime_content_type showed up to be not to liable with png images. Therefor we submit the mime_type from here which is ok.
            $id = $this->file_handler->save($prop['tmp_name'], $prop['real'], 'visible', $mime_type['mime_type']);
            $this->file_handler->update(array('accessibility' => $this->upload_setting['file_accessibility']));

            if ($this->upload_setting['add_keyword'] != '' && strtolower(get_class($this->file_handler)) == 'filemanager') {
                $this->file_handler->load();
                $keyword = $this->file_handler->getKeywords();
                $appender = $this->file_handler->getKeywordAppender();
                $string_appender = new Intraface_Keyword_StringAppender($keyword, $appender);
                $string_appender->addKeywordsByString($this->upload_setting['add_keyword']);
            }

            return $id;
        }
    }

    /**
     * @todo Checks whether WHICH FIELD is an upload field
     *
     * @param string $field @todo
     *
     * @return boolean
     */
    function isUploadFile($field)
    {
        if (isset($_FILES) && isset($_FILES[$field]) && $_FILES[$field]['tmp_name'] != '' && $_FILES[$field]['error'] == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get files
     *
     * @return array
     */
    function getFiles()
    {
        $upload = new HTTP_Upload('en');
        return $upload->getFiles();
    }


    /**
     * @todo B�r returnere nogle id'er
     * This should not be in this class
     *
     * @return boolean
     */
    function import($dir)
    {

        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                print($file);
                if ($file == ".." || $file == "." || is_dir($file)) {
                    CONTINUE;
                }

                $ext = substr($file, strrpos($file, ".")+1);

                if (strlen($ext) < 3 || strlen($ext) > 4) {
                    $this->file_handler->error->set("Filen \"".$file."\" har ikke en gyldig endelse, f.eks. .pdf");
                    print("Filen \"".$file."\" har ikke en gyldig endelse, f.eks. .pdf<br />");
                    CONTINUE;
                }

                $file_size = filesize($dir.$file);
                if ($file_size > $this->upload_setting['max_file_size']) {
                    $this->file_handler->error->set("Filen \"".$file."\" er st�rre end de tilladte ".$this->upload_setting['max_file_size']." Byte");
                    print("Filen \"".$file."\" er st�rre end de tilladte ".$this->upload_setting['max_file_size']." Byte<br/>");
                    CONTINUE;
                }

                $mime_type = $this->file_handler->_getMimeType(mime_content_type($dir.$file), 'mime_type');
                if ($mime_type === false) {
                    $this->file_handler->error->set("Filen \"".$file."\" er ikke en gyldig filtype (Det er typen: ".mime_content_type($dir.'/'.$file).")");
                    print("Filen \"".$file."\" er ikke en gyldig filtype (Det er typen: ".mime_content_type($dir.'/'.$file).")<br />");
                    CONTINUE;
                }

                if ($mime_type['allow_user_upload'] == 0) {
                    $this->file_handler->error->set("Filen \"".$file."\" af typen \"".$mime_type['description']."\" kan ikke uploades");
                    print("Filen \"".$file."\" af typen \"".$mime_type['description']."\" kan ikke uploades<br />");
                    CONTINUE;
                }

                if ($this->upload_setting['allow_only_images'] == 1 && $mime_type['image'] == 0) {
                    $this->file_handler->error->set("Filen \"".$file."\" er ikke et billede. Du kan kun uploade billeder!");
                    print("Filen \"".$file."\" er ikke et billede. Du kan kun uploade billeder!<br />");
                    CONTINUE;
                }

                if ($this->upload_setting['allow_only_documents'] == 1 && $mime_type['image'] == 1) {
                    $this->file_handler->error->set("Filen \"".$file."\" er ikke et dokument. Du kan kun uploade dokumenter!");
                    print("Filen \"".$file."\" er ikke et dokument. Du kan kun uploade dokumenter!");
                    CONTINUE;
                }
                /*
                print_r(array('file_name' => $file, 'file_size' => $file_size, 'file_type' => $mime_type['mime_type'], 'accessibility' => $this->upload_setting['file_accessibility']));
                die();
                */

                $file_handler = new FileHandler($this->file_handler->kernel);

                $id = $file_handler->update(array('file_name' => $file, 'file_size' => $file_size, 'file_type' => $mime_type['mime_type'], 'accessibility' => $this->upload_setting['file_accessibility']));

                $imported_files[] = $id;

                if ($this->upload_setting['add_keyword'] != '') {
                    $file_handler->load(); // Der skal lige loades, s� id kan hentes.
                    $file_handler->kernel->useShared('keyword');

                    $keyword = new Keyword($file_handler);
                    $keyword->addKeywordsByString($this->upload_setting['add_keyword']);
                }

                // Vi putter vores egen extension p�, det er mere sikkert.
                $server_file_name = $id.".".$mime_type['extension'];
                $file_handler->update(array('server_file_name' => $server_file_name));

                if (!is_dir($this->upload_path)) {
                    if (!mkdir($this->upload_path, 0755)) {
                        throw new Exception("Kunne ikke oprette mappe i FileHandler->upload");
                    }
                }

                if (!rename($dir.$file, $this->upload_path.$server_file_name)) {
                    $this->file_handler->error->set('Der opstod en fejl under flytningen af filen '.$file);
                    print('Der opstod en fejl under flytningen af filen '.$file.'<br />');
                    $file_handler->delete();
                    CONTINUE;
                }

                if (!chmod($this->upload_path.$server_file_name, 0644)) {
                    // please do not stop executing here
                    throw new Exception("Unable to chmod file '".$this->upload_path.$server_file_name."'");
                }

                //print("SUCCESS: ".$file."<br />");
            }
        }

        closedir($handle);

        return true;
    }
}
?>