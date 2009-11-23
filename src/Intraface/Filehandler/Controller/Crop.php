<?php
class Intraface_Filehandler_Controller_Crop extends k_Component
{
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();
        $gateway = new Ilib_Filehandler_Gateway($this->getKernel());

        $module = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        $filemanager = $gateway->getFromId($this->context->name());
        $instance_type = $this->GET['instance_type'];

        $img_height = $filemanager->get('height');
        $img_width = $filemanager->get('width');

        $filemanager->createInstance('system-large');
        $editor_img_uri = $filemanager->instance->get('file_uri');
        $editor_img_height = $filemanager->instance->get('height');
        $editor_img_width = $filemanager->instance->get('width');

        $size_ratio = $editor_img_width / $img_width;

        $filemanager->createInstance($instance_type);
        $type = $filemanager->instance->get('instance_properties');

        $params = unserialize($filemanager->instance->get('crop_parameter'));

        $editor_min_width = $type['max_width'] * $size_ratio;
        $editor_min_height = $type['max_height'] * $size_ratio;

        if ($editor_min_width > $editor_img_width) {
            $editor_min_width = $editor_img_width;
            $editor_min_height = ($editor_img_width/$editor_min_width)*$editor_min_height;
        }

        if ($editor_min_height > $editor_img_height) {
            $editor_min_height = $editor_img_height;
            $editor_min_width = ($editor_img_height/$editor_min_height)*$editor_min_width;
        }

        if ($type['resize_type'] != 'strict' && !empty($this->GET['unlock_ratio'])) {
            $unlock_ratio = 1;
        } else {
            $unlock_ratio = 0;
        }

        $size_ratio = doubleval(1/$size_ratio);

        // make sure that it is with a . and not ,
        $size_ratio = str_replace(',', '.', $size_ratio);

        $this->document->setTitle('Crop image: ' . $filemanager->get('file_name'));
        $this->document->addScript($this->url('/javascript/cropper/lib/prototype.js'));
        // @todo HACK only way I can get the link to be correct with a comma
        $this->document->addScript($this->url('/javascript/cropper/lib/scriptaculous.js') . '?load=builder,dragdrop');
        $this->document->addScript($this->url('/javascript/cropper/cropper.js'));
        $this->document->addScript($this->url('/javascript/crop_image.js.php',
            array(
                  'size_ratio' => $size_ratio,
                  'max_width' => round($editor_min_width),
                  'max_height' => round($editor_min_height),
                  'unlock_ratio' => $unlock_ratio,
                  'x1' => $params['crop_offset_x'],
                  'y1' => $params['crop_offset_y'],
                  'x2' => $params['crop_offset_x'] + $params['crop_width'],
                  'y2' => $params['crop_offset_y'] + $params['crop_height'])));

        $data = array('translation' => $translation,
                      'type' => $type,
                      'filemanager' => $filemanager,
                      'img_height' => $img_height,
                      'img_width' => $img_width,
                      'editor_img_height' => $editor_img_height,
                      'editor_img_width' => $editor_img_width,
                      'editor_img_uri' => $editor_img_uri,
                      'unlock_ratio' => $unlock_ratio);

        $tpl = new k_Template(dirname(__FILE__) . '/../templates/crop.tpl.php');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $kernel = $this->getKernel();
        $gateway = new Ilib_Filehandler_Gateway($this->getKernel());

        $module = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        $filemanager = $gateway->getFromId($this->context->name());
        $instance_type = $this->POST['instance_type'];

        $validator = new Ilib_Validator($filemanager->error);
        //$validator->isNumeric((int)$this->POST['width'], 'invalid width', 'greater_than_zero,integer');
        //$validator->isNumeric((int)$this->POST['height'], 'invalid width', 'greater_than_zero,integer');
        //$validator->isNumeric((int)$this->POST['x'], 'invalid width', 'zero_or_greater,integer');
        //$validator->isNumeric((int)$this->POST['y'], 'invalid width', 'zero_or_greater,integer');

        if (!$filemanager->error->isError()) {
            $filemanager->createInstance($instance_type);
            $filemanager->instance->delete();

            $param['crop_width'] = (int)$this->POST['width'];
            $param['crop_height'] = (int)$this->POST['height'];
            $param['crop_offset_x'] = (int)$this->POST['x'];
            $param['crop_offset_y'] = (int)$this->POST['y'];

            $filemanager->createInstance($instance_type, $param);

            if (!$filemanager->error->isError()) {
                return new k_SeeOther($this->context->url());
            }
       }
       throw new Exception($filemanager->error->view());

    }

    function t($phrase)
    {
        return $phrase;
    }
}