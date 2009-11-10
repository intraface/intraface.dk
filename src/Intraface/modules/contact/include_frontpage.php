<?php
/**
 * @package Intraface_Contact
 */

require_once dirname(__FILE__) . '/ContactReminder.php';
$contact_module = $kernel->useModule('contact');

$contact = new Contact($kernel);

if (!$contact->isFilledIn()):
    $_advice[] = array(
        'msg' => 'you can create contacts in the contact module',
        'link' => $contact_module->getPath(),
        'module' => $contact_module->getName()
    );
endif;

$reminders = ContactReminder::upcomingReminders($kernel);
foreach ($reminders AS $reminder) {
    if (strtotime($reminder['reminder_date']) > time()) {
        $text = 'Upcoming';
    } else {
        $text = 'URGENT!';
    }
    $_attention_needed[] = array(
        'module' => $contact_module->getName(),
        'link' => 'restricted/module/contact/'.$reminder['contact_id'] . '/memo/' . $reminder['id'],
        'msg' => $text.' ('.$reminder['dk_reminder_date'].'): '.$reminder['contact_name'].':  '.$reminder['subject'].'.',
        'no_translation' => true
    );

}