<?php
class Intraface_modules_contact_Controller_Memo extends k_Component
{
    protected $template;
    protected $memo;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/memo');
        return $smarty->render($this, array('reminder' => $this->getMemo()));
    }

    function renderHtmlEdit()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/memo-edit');
        return $smarty->render($this, array('reminder' => $this->getMemo()));
    }

    function postForm()
    {
    	if ($this->body('mark_as_seen')) {
    		$this->getMemo()->setStatus('seen');
    	} elseif ($this->body('cancel')) {
    		$this->getMemo()->setStatus('cancelled');
    	} elseif ($this->body('postpone_1_day')) {
    		$date = new Date($this->getMemo()->get('reminder_date'));
    		$next_day = $date->getNextDay();
    		$this->getMemo()->postponeUntil($next_day->getDate());
    	} elseif ($this->body('postpone_1_week')) {
    		$date = new Date($this->getMemo()->get('reminder_date'));
    		$date_span = new Date_Span();
    		$date_span->setFromDays(7);
    		$date->addSpan($date_span);
    		$this->getMemo()->postponeUntil($date->getDate());
    	} elseif ($this->body('postpone_1_month')) {
    		$date = new Date($this->getMemo()->get('reminder_date'));
    		$date_span = new Date_Span();
            $date_calc = new Date_Calc();
    		$date_parts = explode('-', $this->getMemo()->get('reminder_date'));
            $date_span->setFromDays($date_calc->daysInMonth($date_parts[1], $date_parts[0]));
    		$date->addSpan($date_span);
    		$this->getMemo()->postponeUntil($date->getDate());
    	} elseif ($this->body('postpone_1_year')) {
    		$date = new Date($this->getMemo()->get('reminder_date'));
    		$date_span = new Date_Span();
    		$date_span->setFromDays(365); // does not take account of leap year
    		$date->addSpan($date_span);
    		$this->getMemo()->postponeUntil($date->getDate());
    	} else {
        	if ($id = $this->getMemo()->update($_POST)) {
        		return new k_SeeOther($this->url('../'));
        	}
        	$value = $_POST;
    	}

    	return $this->render();
    }

    function getMemo()
    {
        if (is_object($this->memo)) {
            return $this->memo;
        }
        return $this->memo = ContactReminder::factory($this->context->getKernel(), (int)$this->name());
    }
}