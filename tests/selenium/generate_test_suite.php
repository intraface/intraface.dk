<?php

/**
 * This file automatically generates the testSuite.html file to run all tests!
 */


$write = "<html>" .
        "\r\n\t<body>" .
        "\r\n\t\t<table>" .
        "\r\n\t\t\t<tr>" .
        "\r\n\t\t\t\t<td>Intraface Selenium Test Suite</td>" .
        "\r\n\t\t\t</tr>";

$f = '.';

foreach(scandir( $f ) as $folder ){
    if(!strcmp(substr($folder, 0, 1), '.' )) continue;

    if(is_dir($folder)) {
        foreach(scandir($f.DIRECTORY_SEPARATOR.$folder) as $file) {
            if(!strcmp(substr($file, 0, 1), '.' )) continue;

            // substr($file, 0, 4) == 'test' &&

            if($file == 'generate' && is_dir($f.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$file)) {
                $generate_dir = $f.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$file;

                foreach(scandir($generate_dir) as $generate_file) {
                    if(substr($generate_file, strlen($generate_file) - 5) == '.html') {

                        $generate_file_content = file_get_contents($generate_dir.DIRECTORY_SEPARATOR.$generate_file);
                        $generate_file_content = str_replace('##path_test_root##', realpath(dirname(__FILE__)), $generate_file_content);
                        if(!is_dir($f.DIRECTORY_SEPARATOR.'.tmp')) {
                            mkdir($f.DIRECTORY_SEPARATOR.'.tmp');
                        }
                        if(!is_dir($f.DIRECTORY_SEPARATOR.'.tmp'.DIRECTORY_SEPARATOR.$folder)) {
                            mkdir($f.DIRECTORY_SEPARATOR.'.tmp'.DIRECTORY_SEPARATOR.$folder);
                        }
                        file_put_contents($f.DIRECTORY_SEPARATOR.'.tmp'.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$generate_file, $generate_file_content);
                        $test_name = $folder.':generated:'.substr($generate_file, 0, strlen($generate_file) - 5);

                        $write .= "\r\n\t\t\t<tr>" .
                                  "\r\n\t\t\t\t<td><a href=\"".$f.DIRECTORY_SEPARATOR.'.tmp'.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$generate_file."\">".$test_name."</a></td>" .
                                  "\r\n\t\t\t</tr>";
                    }
                }
            } elseif(substr($file, strlen($file) - 5) == '.html') {

                $test_name = $folder.':'.substr($file, 0, strlen($file) - 5);

                $write .= "\r\n\t\t\t<tr>" .
                          "\r\n\t\t\t\t<td><a href=\"".$folder.DIRECTORY_SEPARATOR.$file."\">".$test_name."</a></td>" .
                          "\r\n\t\t\t</tr>";

            }
        }
    }
}

$write .= "\r\n\t\t</table>" .
          "\r\n\t</body>" .
          "\r\n</html>";

@unlink('testSuite.html');

if (!$handle = fopen('testSuite.html', 'a')) {
    echo "Cannot open file ($filename)";
    exit;
}

// Write $somecontent to our opened file.
if (fwrite($handle, $write) === FALSE) {
    echo "Cannot write to file ($filename)";
    exit;
}

fclose($handle);

echo "Success, wrote content to file testSuite.html\n";

// echo $write;
?>
