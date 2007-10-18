<?php
/*
 * To display all errors
 */

define('ERROR_LOG', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/log/error.log');
define('ERROR_LOG_UNIQUE', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/log/unique-error.log');
define('PATH_WWW', 'http://');

class Intraface_Tools_ErrorList {

    public function get($show = 'unique') {


        $handle = fopen(ERROR_LOG, "r");
        while (!feof($handle)) {
           $buffer = fgets($handle, 4096);
           if (empty($buffer) OR !is_string($buffer)) continue;
           // $errors[] = unserialize($buffer); if buffer is array.
           $errors[] = $buffer;

        }
        fclose($handle);

        $unique = array();
        $items = array();

        if(!empty($errors)) {
            foreach ($errors AS $error_string) {
                if(!ereg("^([a-zA-Z]{3} [0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}) ([a-zA-Z0-9]+) \[([a-zA-Z0-9]+)\] ([a-zA-Z0-9]+): (.+) in ([][a-zA-Z0-9/\_.-]*) line ([][a-zA-Z0-9]*) \(Request: ([][a-zA-Z0-9/\._-]*)\)", $error_string, $params)) {
                    $error['message'] = 'Unable to parse error line!';
                }

                $error['date'] = (isset($params[1])) ? date(DATE_RFC822, strtotime($params[1])) : date(DATE_RFC822);

                $error['type'] = '';
                // We choose not to use PEAR log identifier [2] and PEAR error type [3] as they are the same so fare
                // if(isset($params[2])) $error['type'] .= $params[2].' ';
                // if(isset($params[3])) $error['type'] .= '['.$params[3].'] ';
                if(isset($params[4])) $error['type'] .= $params[4];

                $error['message'] = (isset($params[5])) ? $params[5] : '[no message]';
                $error['file'] = (isset($params[6])) ? $params[6] : '[not given]';
                $error['line'] = (isset($params[7])) ? $params[7] : '[not given]';
                $error['request'] = (isset($params[8])) ? $params[8] : '[not given]';

                // $input['type'].": ".$input['message']." in ".$input['file']." line ".$input['line']. " (Request: ".$_SERVER['REQUEST_URI'].")";


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
                    'link' => substr(PATH_WWW, 0, strlen(PATH_WWW)-1) . $error['request'],
                    'author' => 'Sikkert Sune :)'
                );

            }
        }

        return $items;
    }

    public function delete() {
        unlink(ERROR_LOG);
        touch(ERROR_LOG);
        @unlink(ERROR_LOG_UNIQUE);
        touch(ERROR_LOG_UNIQUE);
    }

}
?>
