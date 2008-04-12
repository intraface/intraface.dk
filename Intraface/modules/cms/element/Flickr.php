<?php
/**
 * Hastigheden er alt for lav.
 * Vi skal have fundet en bedre måde at gøre det på.
 *
 * I øvrigt skal det måske være muligt at vælge sine billeder ud fra tags
 * i stedet for albums også?
 *
 * På sigt skal man måske endda kunne reorganisere billederne herfra!
 *
 * @package Intraface_CMS
 */
// use this http://www.airtightinteractive.com/simpleviewer/auto_server_instruct.html

require_once 'phpFlickr/phpFlickr.php';
require_once 'Intraface/modules/cms/Element.php';

class CMS_Flickr extends CMS_Element
{
    public $allowed_sizes = array(
        'square'    => 'Små firkanter',
        'thumbnail' => 'Thumbnail',
        'small'     => 'Små',
        'medium'    => 'Medium'
    );

    public $services = array(
        '23'     => '23hq',
        'flickr' => 'flickr'
    );

    function __construct($section, $id = 0)
    {
        $this->value['type'] = 'flickr';
        parent::__construct($section, $id);
    }

    // tror måske bare jeg skal returnere links med billeder og så
    // lave et eller andet end den her pictobrowser
    function load_element()
    {
        //$this->value['user'] = $this->parameter->get('user');
        //$this->parameter->save('api_key', $var['api_key']);
        //$this->value['tags'] = $this->parameter->get('tags');
        $this->value['photoset_id'] = $this->parameter->get('photoset_id');
        $this->value['size']        = $this->parameter->get('size');
        $this->value['service']     = $this->parameter->get('service');

        if (empty($this->value['service'])) {
            $this->parameter->save('service', 'flickr');
            $this->value['service'] = $this->parameter->get('service');
        }

        $this->value['set'] = array();


        // @todo / hack
        // det virker som om den bliver startet vel mange gange den her
        // dette er nødvendig for at få det hele gemt.
        // de mange starter er nok også grunden til at det går lidt langsomt
        // hvis man fx skriver en værdi ud her, kommer den frem flere gange.
        /*
        if (empty($this->value['service'])) {
            return;
        }
        */

        $f = new phpFlickr($this->kernel->setting->get('system', 'flickr.api_key'), NULL, false);
        $f->setService($this->value['service']);
        $f->enableCache('db', DB_DSN);

        // her skal nok lige være lidt fejlhåndtering på servicen
        // hvis det virker, så forstætter vi bare, ellers skal vi fail gracefully
        /*
         * @todo nu virker 23hq vist ikke laengere. det skal vi lige have ordnet.
         *
        if ($f->getErrorCode() == 0) {
            $flickr_photos = $f->photosets_getPhotos($this->value['photoset_id'], 'owner_name');

            //$photos_url = $f->urls_getUserPhotos($flickr_photos['owner']);
            $this->value['set']['info'] = $f->photosets_getInfo($this->value['photoset_id']);

            if ($this->value['service'] == 'flickr') {
                $this->value['set']['url'] = 'http://www.flickr.com/photos/'.$flickr_photos['owner'].'/sets/'.$this->value['photoset_id'] . '/';
            } elseif ($this->value['service'] == '23') {
                // HACK et lille hack - $flickr_photos['photo'][0]['ownername'] indeholder kun det rigtige ownername hvis man både har album og billeder
                $this->value['set']['url'] = 'http://23hq.com/'.$flickr_photos['photo'][0]['ownername'].'/album/'.$this->value['photoset_id'];
            }
            $this->value['pictures'] = array();

            $photos = $flickr_photos['photo'];

            $i = 0;
        }
        */

        if ($this->value['service'] == 'flickr') {
            $this->value['pictobrowser'] = '
                <object codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="500" height="580" id="photo_browser02" align="middle">
                    <param name="FlashVars" VALUE="currentSet='.$this->value['photoset_id'].'&UserName=lsolesen"></param>
                    <param name="movie" value="http://www.db798.com/work/photo_browser/photo_browser.swf"></param>
                    <param name="loop" value="false"></param>
                    <param name="quality" value="best"></param>
                    <param name="scale" value="noscale"></param>
                    <param name="bgcolor" value="#000000"></param>
                    <embed src="http://www.db798.com/work/photo_browser/photo_browser.swf" FlashVars="currentSet='.$this->value['photoset_id'].'&UserName=lsolesen" loop="false" quality="best" scale="noscale" bgcolor="#000000" width="500" height="580" name="photo_browser" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>
                </object>';
        } else {
            $this->value['pictobrowser'] = '';
        }
    }

    function validate_element($var)
    {
        $validator = new Validator($this->error);
        $validator->isNumeric($var['photoset_id'], 'error in photoset id');
        $validator->isString($var['size'], 'error in size', '', 'allow_empty');
        /*
        if (!array_key_exists($var['size'], $this->allowed_sizes)) {
            $this->error->set('Størrelsen er ikke gyldig');
        }
        */
        $validator->isString($var['service'], 'service');

        if (!array_key_exists($var['service'], $this->services)) {
            $this->error->set('error in service');
        }

        if ($this->error->isError()) {
            return 0;
        }

        return 1;
    }

    function save_element($var)
    {
        $this->parameter->save('photoset_id', $var['photoset_id']);
        $this->parameter->save('user', $var['user']);
        //$this->parameter->save('api_key', $var['api_key']);
        //$this->parameter->save('tags', $var['tags']);
        $this->parameter->save('size', $var['size']);
        $this->parameter->save('service', $var['service']);
    }
}