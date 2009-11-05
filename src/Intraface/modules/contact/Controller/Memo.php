<?php
class Intraface_modules_contact_Controller_Memo extends k_Component
{
    function renderHtml()
    {
        $contact_module = $this->context->getKernel()->module('contact');
        $translation = $this->context->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

    	$reminder = ContactReminder::factory($this->context->getKernel(), (int)$this->name());
        $contact = $reminder->contact;

        $smarty = new k_Template(dirname(__FILE__) . '/templates/memo.tpl.php');
        return $smarty->render($this, array('reminder' => $reminder));

    }

    function renderHtmlEdit()
    {
        $contact_module = $this->context->getKernel()->module('contact');
        $translation = $this->context->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

    	$reminder = ContactReminder::factory($this->context->getKernel(), (int)$this->name());
        $contact = $reminder->contact;

        $smarty = new k_Template(dirname(__FILE__) . '/templates/memo-edit.tpl.php');
        return $smarty->render($this, array('reminder' => $reminder));

    }

    function postForm()
    {
        $contact_module = $this->context->getKernel()->module('contact');
        $translation = $this->context->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

    	$reminder = ContactReminder::factory($this->context->getKernel(), (int)$this->name());

    	if (isset($_POST['mark_as_seen'])) {
    		$reminder->setStatus('seen');
    	} elseif (isset($_POST['cancel'])) {
    		$reminder->setStatus('cancelled');
    	} elseif (isset($_POST['postpone_1_day'])) {
    		$date = new Date($reminder->get('reminder_date'));
    		$next_day = $date->getNextDay();
    		$reminder->postponeUntil($next_day->getDate());
    	} elseif (isset($_POST['postpone_1_week'])) {
    		$date = new Date($reminder->get('reminder_date'));
    		$date_span = new Date_Span();
    		$date_span->setFromDays(7);
    		$date->addSpan($date_span);
    		$reminder->postponeUntil($date->getDate());
    	} elseif (isset($_POST['postpone_1_month'])) {
    		$date = new Date($reminder->get('reminder_date'));
    		$date_span = new Date_Span();
            $date_calc = new Date_Calc();
    		$date_parts = explode('-', $reminder->get('reminder_date'));
            $date_span->setFromDays($date_calc->daysInMonth($date_parts[1], $date_parts[0]));
    		$date->addSpan($date_span);
    		$reminder->postponeUntil($date->getDate());
    	} elseif (isset($_POST['postpone_1_year'])) {
    		$date = new Date($reminder->get('reminder_date'));
    		$date_span = new Date_Span();
    		$date_span->setFromDays(365); // does not take account of leap year
    		$date->addSpan($date_span);
    		$reminder->postponeUntil($date->getDate());
    	} else {
        	// for a new contact we want to check if similar contacts alreade exists
        	if (empty($_POST['id'])) {
        		$contact = new Contact($this->context->getKernel(), (int)$this->context->context->name());
        		$reminder = new ContactReminder($contact);

        	} else {
        		$reminder = ContactReminder::factory($this->context->getKernel(), (int)$this->name());
        		$contact = $reminder->contact;
        	}

        	if ($id = $reminder->update($_POST)) {
        		return new k_SeeOther($this->url('../'));
        	}

        	$value = $_POST;

    	}


    	return $this->render();
    }

    function t($phrase)
    {
        return $phrase;
    }
}