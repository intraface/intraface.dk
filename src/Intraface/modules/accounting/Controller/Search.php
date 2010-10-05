<?php
class Intraface_modules_accounting_Controller_Search extends k_Component
{
    protected $template;
    protected $db_sql;

    function __construct(k_TemplateFactory $template, DB_Sql $db)
    {
        $this->template = $template;
        $this->db_sql = $db;
    }

    function renderHtml()
    {
        $module = $this->context->getKernel()->module('accounting');

        $error = new Intraface_Error;

        $search_terms = array('bilag', 'voucher');

        if ($this->query('voucher_id')) {
            $gateway = new Intraface_modules_accounting_YearGateway($this->getKernel());

            if ($year = $gateway->findByVoucherId($this->query('voucher_id'))) {
                return new k_SeeOther($this->url('../year/' . $year->get('id') . '/voucher/' . $this->query('voucher_id')));
            }
            throw new k_PageNotFound();
        } elseif ($this->query('search')) {
            // set year
            $year = new Year($this->context->getKernel());
            $year->checkYear();

            $search = explode(':', $this->query('search'));
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
                            // fall through
                        case 'voucher':
                            $db = $this->db_sql;
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
        }

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/search');
        return $tpl->render($this, array('error' => $error, 'posts' => $posts));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
