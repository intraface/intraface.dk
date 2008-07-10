<?php

class Intraface_Doctrine_ErrorRender
{
    
    private $errorstack = array();
    private $translation;
    
    public function __construct($translation = NULL)
    {
        $this->translation = $translation;
    }
    
    public function attachErrorStack($errorstack, $field_alias = array()) 
    {
        $this->errorstack[] = array(
            'errorstack' => $errorstack,
            'field_alias' => $field_alias);
    }
    
    public function view($type = 'html') 
    {
        
        // only html is implemented
        
        $display = '<ul class="formerrors">';
        foreach($this->errorstack AS $errorstack) {
            foreach($errorstack['errorstack'] AS $field_name => $error_codes) {
                $display .= '<li>';
                $display .= $this->translate('There was an error in', 'common').' '; 
                $display .= ( isset($errorstack['field_alias'][$field_name]) ? 
                            $errorstack['field_alias'][$field_name] : 
                            $field_name);
                    
                foreach($error_codes AS $error_code) {
                    $description = $this->getErrorDescription($error_code);
                    $display .= ', '.$this->translate( $description !== NULL ? $description : $error_code, 'common');
                }
                $display .= '.</li>';
            }
        }
        $display .= '</ul>';
        
        return $display;
        
    }
    
    private function getErrorDescription($code) {
        
        $errorcodes = array(
            'notnull' => 'it needs to be filled in',
            'email' => 'it is not a valid email',
            'notblank' => 'it needs to be filled in',
            'nospace' => 'it cannot not contain spaces',
            'past' => 'the date should be before today',
            'future' => 'the date should be after today',
            'minlenth' => 'the field should be at least ? characters long',
            'country' => 'it is not a country',
            'ip' => 'it is not an ip address',
            'htmlcolor' => 'it is not a valid html color',
            'range' => 'the number is not inside the valid range',
            'unique' => 'it should be unique',
            'creditcard' => 'it is not a valid creditcard number',
            'nohtml' => 'you have used a forbidden html tag'
        );
        
        if(isset($errorcodes[$code])) {
            return $errorcodes[$code];
        }
        else {
            return null;
        }
    }
    
    private function translate($text, $page_id = 'common') {
        if(is_callable($this->translation, 'get')) {
            return $this->translation->get($text, $page_id);
        }
        else {
            return $text;
        }
    }
    
    
}

?>
