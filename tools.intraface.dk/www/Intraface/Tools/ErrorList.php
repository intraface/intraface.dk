<?php
/*
 * To display all errors
 */
class Intraface_Tools_ErrorList
{
    private $error_log;
    private $error_log_unique;

    function __construct($filename, $unique_filename)
    {
        $this->error_file = $filename;
        if (!file_exists($filename)) {
            throw new Exception('error log not found ' . $filename);
        }
        $this->error_file_unique = $unique_filename;
        if (!file_exists($unique_filename)) {
            touch($unique_filename);
        }
    }

    public function get($show = 'unique')
    {
        $handle = fopen($this->error_file, "r");
        while (!feof($handle)) {
           $buffer = fgets($handle, 4096);
           if (empty($buffer) OR !is_string($buffer)) continue;
           // $errors[] = unserialize($buffer); if buffer is array.
           $errors[] = $buffer;
        }
        fclose($handle);

        $unique = array();
        $items = array();

        if (!empty($errors)) {
            foreach ($errors AS $error_string) {
                if (!ereg("^([a-zA-Z]{3} [0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}) ([a-zA-Z0-9]+) \[([a-zA-Z0-9]+)\] ([a-zA-Z0-9]+): (.+) in ([][a-zA-Z0-9/\_.-]*) line ([][a-zA-Z0-9]*) \(Request: ([][a-zA-Z0-9/\._%+&?-]*)\)", $error_string, $params)) {
                    $error['message'] = 'Unable to parse error line!';
                }

                $error['date'] = (isset($params[1])) ? date(DATE_RFC822, strtotime($params[1])) : date(DATE_RFC822);

                $error['type'] = '';
                // We choose not to use PEAR log identifier [2] and PEAR error type [3] as they are the same so fare
                // if (isset($params[2])) $error['type'] .= $params[2].' ';
                // if (isset($params[3])) $error['type'] .= '['.$params[3].'] ';
                if (isset($params[4])) $error['type'] .= $params[4];

                $error['message'] = (isset($params[5])) ? $params[5] : '[no message]';
                $error['file'] = (isset($params[6])) ? $params[6] : '[not given]';
                $error['line'] = (isset($params[7])) ? $params[7] : '[not given]';
                $error['request'] = (isset($params[8])) ? $params[8] : '[not given]';

                // $input['type'].": ".$input['message']." in ".$input['file']." line ".$input['line']. " (Request: ".$_SERVER['REQUEST_URI'].")";


                if ($show == 'unique' && in_array(md5($error['type'].$error['message'].$error['file'].$error['line']), $unique)) {
                    continue;
                }
                $unique[] = md5($error['type'].$error['message'].$error['file'].$error['line']);

                if ($error['file'] == '') {
                    $error['file'] = 'URL: '.$error['request'];
                }

                $items[] = array(
                    'title' => $error['type'] . ': ' . $error['message'],
                    'description' => $error['file'] . ' - line ' . $error['line'],
                    'pubDate' => $error['date'], // RFC 822
                    'link' => substr(url(null), 0, strlen(url(null))-1) . $error['request'],
                    'author' => 'Sikkert Sune :)'
                );

            }
        }

        return $items;
    }

    public function delete()
    {
        unlink($this->error_file);
        touch($this->error_file);
        unlink($this->error_file_unique);
        touch($this->error_file_unique);
    }

}
