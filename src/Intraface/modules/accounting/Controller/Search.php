<?php
class Intraface_modules_accounting_Controller_Search extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->context->getKernel()->module('accounting');
        $translation = $this->context->getKernel()->getTranslation('accounting');

        // set year
        $year = new Year($this->context->getKernel());
        $year->checkYear();

        $error = new Intraface_Error;

        // @todo this has to be made much better
        if (!empty($_GET)) {

        	$search_terms = array('bilag');

        	if (!empty($_GET['search'])) {
        		$search_string = $_GET['search'];
        		$search = explode(':', $_GET['search']);
                if (empty($search[0]) OR empty($search[1])) {
                    $error->set('Not a valid search');
                } else {

            		$search_term = $search[0];
            		$search_real = $search[1];
            		if (strpos($search[1], '-')) {
            			$search_real = explode('-', $search_real);
            		} else {
                        $error->set('Not a valid search');
            		}

                    if (!$error->isError()) {
                		$search_term = strtolower($search_term);

                		switch ($search_term) {
                			case 'bilag':
                				$db = new DB_Sql;
                				$db->query("SELECT * FROM accounting_voucher WHERE number >= " . $search_real[0] . " AND number <= " . $search_real[1] . " AND intranet_id = " . $year->kernel->intranet->get('id') . " AND year_id = " . $year->get('id'));
                				//$i++;
                				$posts = array();
                				while ($db->nextRecord()) {
                					$voucher = new Voucher($year, $db->f('id'));
                					$posts = array_merge($voucher->getPosts(), $posts);
                					//$i++;
                				}
                				break;
                			default:
                					$error->set('Not a valid search');
                				break;
                		}
                    }
                }
        	} else {
                $error->set('Not a valid search');
        	}



        }

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/search');
        return $tpl->render($this, array('error' => $error, 'posts' => $posts));
    }
}
