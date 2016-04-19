<?php
/**
 * Her skal der inds�ttes en r�kke funktioner, som g�r det lettere at inds�tte filer og filuploads forskellige steder
 *
 * Den mangler stadig at blive udviklet!!!
 *
 * @package Intraface
 */


/*

Den skal fungere s�dan nogenlunde s�dan her:


// Ved udskrivning af upload felt
$filehandler = new FileHandler($kernel); Der kan s�ttes id som sidste parameter
$filehandler_html = new FileHandlerHtml($filehandler); // Denne klasse er ikke skrevet
$filehandler_html->getUploadField("userfile", array([indstillinger se herunder]));

# Indstillinger (noget i retning af):
# Dem med * stjerne skal v�re sat som default, og s�tter man ikke indstillingen benyttes den.
#
# type: *full (inklusiv <form>) all_input (med submit knap), only_upload_field (kun upload feltet
# number_of_fields: *1 (Antallet af upload felter. Benyttes der flere kan filehandler->upload->multipleUpload() med fordel benyttes.
# allow_images: *1 (1 eller 0, om man kan uploade billeder)
# allow_documents: *1 (1 eller 0, om man kan uploade dokumenter)

Jeg har ikke helt fundet ud af hvor mange muligheder man skal have n�r man uploader filer.
Har man ikke adgang til FileManager modullet, s� skal man nok ikke have adgang til andet end at uploade en fil, vise et lille billede, hvis det er et billede, samt at slette billedet.
Har man adgang til FileManager modullet, s� skal der ved siden af upload feltet ogs� v�re et link til "Tilf�j eksisterende fil" som (nok med Redirect) skal g� til en filemanager/select_file.php, som skal give mulighed p� samme m�de som contact/select_contact.php at v�lge en eksisterende kontakt.
Har man adgang til FileManager modullet, s� skal man m�ske ogs� have mulgighed for at tilf�je n�gleord med det samme ved upload. Evt. bare med et tekstfelt, hvor man skriver hvilke n�gleord. Det har du jo lavet :-) Det skal m�ske ogs� v�re muligt at afg�re accesssibility (der afg�rer hvem der m� tilg� filerne. Mulighederne er: personal (kun den bruger som uploader den (er ikke implemeteret)), intranet (kun inden for intranettet), public (uden for intranettet, denne skal nok s�ttes automatisk, n�r man uploader fra cms.))
Bem�rk at filerne altid (ogs� n�r accessibility er public) er beskyttet med en access_key (best�ende af intranet public_key og fil access_key), s� det kun er hvis man f�r stien til filen, at den kan hentes. Det vil sige, hvis CMS f.eks. giver stien til billedet.

// Ved upload af fil
$filehandler = new FileHandler($kernel);
$filehandler->loadUpload();
$filehandler->upload->setSetting('accessibility', 'public'); // Der er en r�kke forskellige indstillinger der kan s�ttes. Se shared/filehandler/UploadHandler.php)
if ($filehandler->upload->upload("userfile")) {
    // upload retunere id p� filen. Den kan ogs� efterf�lgende tilg�s med $filehandler->get('id');
    // noget redirect eller noget andet til den rigtige side
}
else {
    echo $filehandler->error->view();
}

// inds�tte billede
$filehandler = new FileHandler($kernel); Der kan s�ttes id som sidste parameter

echo $filehandler->get('file_uri') returnere url'en til filen. (img-tag ser herunder)

$filehandler->loadInstance('small')
echo $filehandler->instance->get('file_uri');
$filehandler->loadInstance('big')
echo $filehandler->instance->get('file_uri');

// Instances er forskellige st�rrelser af et billede. Se st�rrelserne i shared/filehandler/InstanceHandler.

// Udskrivelse af img-tag (er ikke skrevet endnu)
$filehandler_html = new FileHandlerHtml($filehandler); // Denne klasse er ikke skrevet
$filehandler_html->getImgTag('class="image"');

Jeg mangler stadig at lave en import funktion der kan importere flere billeder enten fra ftp mappe eller et andet sted.


Filer:
shared/filehandler/FileHandler.php
shared/filehandler/UplaodHandler.php
shared/filehandler/InstanceHandler.php
shared/filehandler/FileHandlerHTML.php

modules/filemanager/FileManager.php (Klasse nedarver fra FileHandler, og har derved alle de samme funktioner).
intraface_include/file_type.php (indeholder array med filtyperne (jeg benytter ikke tabellen file_type i databasen)).
intradace.dk/main/file/index.php her hentes filerne fra. Den er ikke helt smukt lavet, men det kan komme senere.
*/


class FileHandlerHTML
{
    private $file_handler;

    function __construct($file_handler)
    {
        $this->file_handler = $file_handler;
    }


    /**
    * Denne funktion skal printe et upload field.
    *
    * Det skal p� en eller anden m�de v�re muligt at s�tte f�lgende parameter:
    * @param checkbox_name: navnet p� den checkboks, som inds�ttes, n�r der er uploadet et billede
    * @param upload_field_name: navnet p� type="file" input-felttet.
    * @param submit_name: navnet p� submit-knappen, som f�rer til "V�lg fra filarkiv"
    * @param options: array:
    *       image_attr (mulighed for at s�tte attributer p� img-tag);
    *           field_description: (det som st�r foran upload feltet, standard 'Fil')
    *           image_size: (st�rrelsen p� billedet. Standard original
    *           type: full/only_upload
    *           include_submit_button_name: hvis sat bliver der indsat en "Upload" knap efter fil-felt, men strengen som navn
    */

    function printFormUploadTag($checkbox_name, $upload_field_name, $submit_name, $options = array())
    {
        $pre_options = array(
            'image_attr' => '',
            'field_description' => 'choose file',
            'image_size' => '',
            'type' => 'full',
            'include_submit_button_name' => '',
            'filemanager' => true
        );

        $options = array_merge($pre_options, $options);

        echo $this->file_handler->error->view();

        if ($this->file_handler->get('id') != 0 && $options['type'] != 'only_upload') {
            $file_id = $this->file_handler->get('id');
            if ($options['image_size'] != '') {
                $this->file_handler->createInstance($options['image_size']);
                $file = $this->file_handler->instance;
            } else {
                $file = $this->file_handler;
            }
            echo '<div>';
            echo '<img src="'.$file->get('file_uri').'" style="width: '.$file->get('width').'px; height: '.$file->get('height').'px;" />';
            echo '<br /><input type="checkbox" name="'.$checkbox_name.'" value="'.$file_id.'" checked="checked" /> '.$this->file_handler->get('file_name');
            echo '</div>';
        }

        echo '<div class="formrow">';
        echo '<label for="'.$upload_field_name.'">'.t($options['field_description'], 'filehandler').'</label>';
        echo '<input name="'.$upload_field_name.'" type="file" id="'.$upload_field_name.'" />';
        if ($options['include_submit_button_name'] != '') {
            echo ' <input type="submit" name="'.$options['include_submit_button_name'].'" value="'.t('upload', 'filehandler') . '" /> <br />';
        }
        if ($this->file_handler->kernel->user->hasModuleAccess('filemanager') and $options['filemanager'] === true) {
            echo ' &nbsp; '.t('or').' &nbsp; <input type="submit" name="'.$submit_name.'" value="'.t('choose from filemanager', 'filehandler').'" />';
        }
        echo '</div>';
    }

    function showFile($delete_link, $options = array())
    {
        $pre_options = array(
            'image_size' => 'icon',
            'force_document_span_size' => '',
            'div_style' => '');


        $options = array_merge($pre_options, $options);

        if ($this->file_handler->get('is_image') == 1 && $options['image_size'] != 'icon') {
            $this->file_handler->createInstance('small');

            if ($options['div_style'] == '') {
                $options['div_style'] = 'height: '.($this->file_handler->instance->get('height')+10).'px;';
            }
            echo '<div class="show_file" style="'.$options['div_style'].'"><img src="'.$this->file_handler->instance->get('file_uri').'" style="width: '.$this->file_handler->instance->get('width').'px; height: '.$this->file_handler->instance->get('height').'px" /> '.$this->file_handler->get('file_name');
            if ($delete_link != '') {
                echo ' <a class="delete" href="'.$delete_link.'">'.t('delete').'</a>';
            }
            echo '</div>';
        } else {
            if ($options['div_style'] == '') {
                $options['div_style'] = 'height: 85px;';
            }
            echo '<div class="show_file" style="'.$options['div_style'].'";><img src="'.$this->file_handler->get('icon_uri').'" style="width: 75px; height: 75px; float: left;" /> '.$this->file_handler->get('file_name');
            if ($delete_link != '') {
                echo ' <a class="delete" href="'.$delete_link.'">'.t('delete').'</a>';
            }
            echo '</div>';
        }
    }
}
