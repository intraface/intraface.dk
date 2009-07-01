<?php
/**
 * @package Intraface_CMS
 */
class Intraface_modules_cms_element_Delicious extends CMS_Element
{
    function __construct($section, $id = 0)
    {
        $this->value['type'] = 'delicious';
        parent::__construct($section, $id);
    }

    function load_element()
    {
        $url = $this->parameter->get('url');
        $this->value['url'] = $url;

        $req = new HTTP_Request(
            $this->value['url'],
            array(
                'timeout', 3
            )
        );

        if (PEAR::isError($req->sendRequest())) {
            return 0;
        }

        $xml = $req->getResponseBody();

        $unserializer = new XML_Unserializer();
        $unserializer->unserialize($xml);

        $output = $unserializer->getUnserializedData();

        $this->value['items'] = $output['item'];
    }

    /**
     *
     */
    function validate_element($var)
    {
        $validator = new Intraface_Validator($this->error);
        $validator->isUrl($var['url'], 'error in url');

        if (substr($var['url'], 0, 23) != 'http://del.icio.us/rss/') {
            $this->error->set('error in url - has to be a del.icio.us rss feed');
        }

        if ($this->error->isError()) {
            return false;
        }

        return true;
    }

    function save_element($var)
    {
        $url = parse_url($var['url']);

        // cleans up url
        $var['url'] = $url['scheme'] . '://' . $url['host'] . $url['path'];

        $this->parameter->save('url', $var['url']);

        return true;
    }
}
