<?php
/**
 * @package Intraface_CMS
 */
require_once 'Intraface/modules/cms/Element.php';

class CMS_Video extends CMS_Element
{
    public $services = array(
        'revver' => 'revver',
        'google' => 'google',
        'youtube' => 'youtube'
    );

    function __construct(& $section, $id = 0)
    {
        $this->value['type'] = 'video';
        parent::__construct($section, $id);
    }

    function load_element()
    {
        $this->value['service'] = $this->parameter->get('service');
        $this->value['doc_id'] = $this->parameter->get('doc_id');
        switch ($this->get('service')) {
            case 'revver':
                $this->value['player'] = '<embed type="application/x-shockwave-flash" src="http://flash.revver.com/player/1.0/player.swf" pluginspage="http://www.macromedia.com/go/getflashplayer" scale="noScale" salign="TL" bgcolor="#ffffff" flashvars="width=480&height=392&mediaId='.$this->value['doc_id'].'&affiliateId=35722&javascriptContext=true&skinURL=http://flash.revver.com/player/1.0/skins/Default_Raster.swf&skinImgURL=http://flash.revver.com/player/1.0/skins/night_skin.png&actionBarSkinURL=http://flash.revver.com/player/1.0/skins/DefaultNavBarSkin.swf&resizeVideo=True" wmode="transparent" height="392" width="480"></embed>';
            break;
            case 'google':
                $this->value['player']  = '<object type="application/x-shockwave-flash" data="http://video.google.com/googleplayer.swf?docId='.$this->value['doc_id'].'" width="400" height="326" id="VideoPlayback">';
                $this->value['player'] .= '<param name="movie" value="http://video.google.com/googleplayer.swf?docId='.$this->value['doc_id'].'" />';
                $this->value['player'] .= '<param name="allowScriptAcess" value="sameDomain" />';
                $this->value['player'] .= '<param name="quality" value="best" />';
                $this->value['player'] .= '<param name="bgcolor" value="#FFFFFF" />';
                $this->value['player'] .= '<param name="scale" value="noScale" />';
                $this->value['player'] .= '<param name="salign" value="TL" />';
                $this->value['player'] .= '<param name="FlashVars" value="playerMode=embedded" />';
                $this->value['player'] .= '</object>';
            break;
            case 'youtube':
                $this->value['player'] = '<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/'.$this->value['doc_id'].'"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'.$this->value['doc_id'].'" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';
            break;
            default:
                $this->value['player'] = 'Ingen player understøtter denne type';
            break;

        }
    }

    /**
     * Denne skal validere om det er en gyldig del.icio.us-sti - og om det er en
     * rss-fil.
     */
    function validate_element($var)
    {
        $validator = new Intraface_Validator($this->error);
        $validator->isString($var['doc_id'], 'error in doc id');
        $validator->isString($var['service'], 'error in service');
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
        $this->parameter->save('service', $var['service']);
        $this->parameter->save('doc_id', $var['doc_id']);

        return 1;
    }

}