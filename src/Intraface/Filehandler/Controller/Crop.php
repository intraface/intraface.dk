<?php
class Intraface_Filehandler_Controller_Crop extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();

        $module = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        $filemanager = $this->getFile();
        $instance_type = $this->query('instance_type');

        $img_height = $filemanager->get('height');
        $img_width = $filemanager->get('width');

        $instance = $filemanager->getInstance('system-large');
        $editor_img_uri = $instance->get('file_uri');
        $editor_img_height = $instance->get('height');
        $editor_img_width = $instance->get('width');

        $size_ratio = $editor_img_width / $img_width;

        $instance = $filemanager->getInstance($instance_type);
        $type = $instance->get('instance_properties');

        $params = unserialize($instance->get('crop_parameter'));

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

        if ($type['resize_type'] != 'strict' && $this->query('unlock_ratio') == '1') {
            $unlock_ratio = 1;
        } else {
            $unlock_ratio = 0;
        }

        $size_ratio = doubleval(1/$size_ratio);

        // make sure that it is with a . and not ,
        $size_ratio = str_replace(',', '.', $size_ratio);

        $this->document->setTitle('Crop image: ' . $filemanager->get('file_name'));
        $this->document->addScript('filehandler/cropper/lib/prototype.js');
        $this->document->addScript('filehandler/cropper/lib/scriptaculous.js');
        $this->document->addScript('filehandler/cropper/cropper.js');
        // @todo HACK only way I can get the link to be correct with a comma
        $this->document->addScript('filehandler/crop_image.js.php'.
            '?size_ratio=' . $size_ratio .
            '&max_width=' . round($editor_min_width) .
            '&max_height=' . round($editor_min_height) .
            '&unlock_ratio=' . $unlock_ratio .
            '&x1=' . $params['crop_offset_x'] .
            '&y1=' . $params['crop_offset_y'] .
            '&x2=' . ($params['crop_offset_x'] + $params['crop_width']) .
            '&y2=' . ($params['crop_offset_y'] + $params['crop_height'])
        );

        $data = array('translation' => $translation,
                      'type' => $type,
                      'filemanager' => $filemanager,
                      'img_height' => $img_height,
                      'img_width' => $img_width,
                      'editor_img_height' => $editor_img_height,
                      'editor_img_width' => $editor_img_width,
                      'editor_img_uri' => $editor_img_uri,
                      'unlock_ratio' => $unlock_ratio);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/crop');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $kernel = $this->getKernel();
        $module = $kernel->module('filemanager');

        $filemanager = $this->getFile();
        $instance_type = $this->body('instance_type');

        $validator = new Ilib_Validator($filemanager->error);
        $validator->isNumeric((int)$this->body('width'), 'Invalid width', 'greater_than_zero,integer');
        $validator->isNumeric((int)$this->body('height'), 'Invalid width', 'greater_than_zero,integer');
        $validator->isNumeric((int)$this->body('x'), 'Invalid x', 'zero_or_greater,integer');
        $validator->isNumeric((int)$this->body('y'), 'Invalid y', 'zero_or_greater,integer');

        if (!$filemanager->error->isError()) {
            $instance = $filemanager->getInstance($instance_type);
            $instance->delete();

            $param['crop_width'] = (int)$this->body('width');
            $param['crop_height'] = (int)$this->body('height');
            $param['crop_offset_x'] = (int)$this->body('x');
            $param['crop_offset_y'] = (int)$this->body('y');

            $instance = $filemanager->getInstance($instance_type, $param);

            if (!$filemanager->error->isError()) {
                return new k_SeeOther($this->context->url());
            }
       }
       throw new Exception($filemanager->error->view());
    }

    function getFile()
    {
        return $this->context->getFile();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}