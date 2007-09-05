<?php
/*
 * To display all errors
 */
 
class ErrorList {
    
    
    
    public function get($show = 'unique') {
        
        
        $handle = fopen(ERROR_LOG, "r");
        while (!feof($handle)) {
           $buffer = fgets($handle, 4096);
           if (empty($buffer) OR !is_string($buffer)) continue;
           $errors[] = unserialize($buffer);
        }
        fclose($handle);

        $unique = array();
        $items = array();

        if (!empty($errors)) {
            foreach ($errors AS $error) {
        
                if($show == 'unique' && in_array(md5($error['type'].$error['message'].$error['file'].$error['line']), $unique)) {
                    CONTINUE;     
                }
                $unique[] = md5($error['type'].$error['message'].$error['file'].$error['line']);
    
                if($error['file'] == '') {
                    $error['file'] = 'URL: '.$error['request'];
                }
                
                $items[] = array(
                    'title' => $error['type'] . ': ' . $error['message'],
                    'description' => $error['file'] . ' - line ' . $error['line'],
                    'pubDate' => $error['date'], // RFC 822
                    'link' => PATH_WWW . $error['request'],
                    'author' => 'Sikkert Sune :)'
                );
            }
        }
        
        return $items;       
    }
    
    public function delete() {
        unlink(ERROR_LOG);
        touch(ERROR_LOG);
        unlink(ERROR_LOG_UNIQUE);
        touch(ERROR_LOG_UNIQUE);
    }
    
}
?>
