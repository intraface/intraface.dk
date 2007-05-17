<?php
/**
 * Sends an email with a link to the subscriber to confirm the subscription.
 *
 * Must have an update method
 *
 * @category Intraface
 * @package  Newsletter
 * @author   Lars Olesen <lars@legestue.net>
 * @version  @package-version@
 */
require_once 'Intraface/shared/email/Email.php';

class Intraface_Module_Newsletter_Observer_OptinMail // must implement an observer pattern
{
    private $list;

    /**
     * Constructor
     *
     * @param object $list Newsletter list
     *
     * @return void
     */
    public function __construct($list)
    {
        $this->list = $list;
    }

    /**
     * Update
     *
     * @param object $subscriber Subscriber object
     *
     * @return boolean
     */
    public function update($subscriber)
    {
        return $this->sendOptInEmail($subscriber);
    }

    /**
     * The subscriber must receive an e-mail so the subscribtion can be confirmed
     * The e-mail should say that the subscription should be confirmed within a week.
     *
     * E-mailen skal indeholde følgende:
     * - url til privacy policy på sitet
     * - en kort beskrivelse af mailinglisten
     * - url som brugeren følger for at bekræfte tilmeldingen
     *
     * - I virkeligheden skal den nok nøjes med lige at logge ind i ens personlige webinterface
     *   hvor man så kan lave bekræftelsen fra. Det skal altså bare være loginkoden fra
     *   den personlige konto, der står der, og så skal nyhedsbreve på forsiden (hvis dette sted
     *   har nogle nyhedsbreve).
     *
     * @see tilføj cleanUp();
     *
     * @param object $subscriber Subscriber object
     *
     * @return boolean
     */
    private function sendOptInEmail($subscriber)
    {
        $subscriber->load();

        $email = new Email($this->list->kernel);
        $data = array(
            'subject' => 'Bekræft tilmelding',
            'body' =>
                $this->list->get('subscribe_message') . "\n\n" .
                "\n\nMed venlig hilsen\n".$this->list->get('sender_name'),
            'contact_id' => $subscriber->get('contact_id'),
            'from_email' => $this->list->get('reply_email'),
            'from_name' => $this->list->get('sender_name'),
            'type_id' => 7, // nyhedsbreve
            'belong_to' => $this->list->get('id')
        );

        if (!$email->save($data)) {
            return false;
        }

        if ($email->send()) {
            $db = new DB_Sql;
            $db->query("UPDATE newsletter_subscriber SET date_optin_email_sent = NOW() WHERE id = " . $this->id);
            return true;
        }

        return false;
    }
}
?>