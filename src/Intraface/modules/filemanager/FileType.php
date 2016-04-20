<?php
/**
 * Class to control and check file types
 */

/**
 *
 * Special windows cases.
 * 12: image/pjpeg er jpg billeder uploadet i IE
 * 14: image/x-png er jpg billeder uploadet i IE
 * 16: application/octet-stream er csv i windows
 * @todo: csv type applocatio/octet-stream should be checked whether it is a program as this mime_type can also be an exe file
 */

class FileType
{
    protected $types = array(
        0 => array(
            'description' => 'Unknown filetype',
            'mime_type' => '',
            'extension' => '',
            'icon' => '',
            'image' => 0,
            'allow_user_upload' => 0),
        1 => array(
            'description' => 'Zip file',
            'mime_type' => 'application/mac-binhex40',
            'extension' => 'zip',
            'icon' => 'zip.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        2 => array(
            'description' => 'Text document',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'icon' => 'txt.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        3 => array(
            'description' => 'Word document',
            'mime_type' => 'application/msword',
            'extension' => 'doc',
            'icon' => 'doc.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        4 => array(
            'description' => 'Powerpoint presentation',
            'mime_type' => 'application/vnd.ms-powerpoint',
            'extension' => 'ppt',
            'icon' => 'ppt.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        5 => array(
            'description' => 'Excel spreadsheet',
            'mime_type' => 'application/vnd.ms-excel',
            'extension' => 'xls',
            'icon' => 'xls.jpg',
            'image' => 0,
            'allow_user_upload' => 1),

        6 => array(
            'description' => 'GIF image',
            'mime_type' => 'image/gif',
            'extension' => 'gif',
            'icon' => 'gif.jpg',
            'image' => 1,
            'allow_user_upload' => 1),
        7 => array(
            'description' => 'JPEG image',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpeg',
            'icon' => 'jpg.jpg',
            'image' => 1,
            'allow_user_upload' => 1),
        8 => array(
            'description' => 'PNG image',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'icon' => 'png.jpg',
            'image' => 1,
            'allow_user_upload' => 1),
        9 => array(
            'description' => 'PDF document',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'icon' => 'pdf.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        10 => array('description' => 'RTF document',
            'mime_type' => 'application/rtf',
            'extension' => 'rtf',
            'icon' => 'doc.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        11 => array(
            'description' => 'HTML document',
            'mime_type' => 'text/html',
            'extension' => 'html',
            'icon' => 'html.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        12 => array(
            'description' => 'MP3 audio file',
            'mime_type' => 'audio/mpeg',
            'extension' => 'mp3',
            'icon' => 'mp3.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        13 => array(
            'description' => 'JPEG image',
            'mime_type' => 'image/pjpeg',
            'extension' => 'jpeg',
            'icon' => 'jpg.jpg',
            'image' => 1,
            'allow_user_upload' => 1),
        14 => array(
            'description' => 'PNG image',
            'mime_type' => 'image/x-png',
            'extension' => 'png',
            'icon' => 'png.jpg',
            'image' => 1,
            'allow_user_upload' => 1),
        15 => array(
            'description' => 'CSV file',
            'mime_type' => 'text/csv',
            'extension' => 'csv',
            'icon' => 'csv.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        16 => array(
            'description' => 'CSV file',
            'mime_type' => 'application/octet-stream',
            'extension' => 'csv',
            'icon' => 'csv.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        17 => array(
            'description' => 'PDF document',
            'mime_type' => 'application/download',
            'extension' => 'pdf',
            'icon' => 'pdf.jpg',
            'image' => 0,
            'allow_user_upload' => 1),
        );

    /**
     * constructor
     */
    public function __construct()
    {
    }

    /**
     * Returns all the file types.
     */
    public function getList()
    {
        return $this->types;
    }
}
