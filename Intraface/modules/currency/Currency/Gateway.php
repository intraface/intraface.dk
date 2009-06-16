<?php

class Intraface_modules_currency_Currency_Gateway
{
    private $table;

    public function __construct($doctrine)
    {
        $this->table = $doctrine->getTable('Intraface_modules_currency_Currency');
    }

    /**
     * Finds all currencies
     *
     * @return object collection
     */
    public function findAll()
    {
        $query = $this->table->createQuery('currency');
        $collection = $query->select('currency.*, product_price_exchange_rate.*, payment_exchange_rate.*')
                ->leftJoin('currency.product_price_exchange_rate AS product_price_exchange_rate')
                ->leftJoin('currency.payment_exchange_rate AS payment_exchange_rate')
                ->orderBy('type_key, product_price_exchange_rate.id, payment_exchange_rate.id')
                ->execute();
        $query->free(true);

        if (!$collection || $collection->count() == 0) {
            throw new Intraface_Gateway_Exception('Unable to find currency');
        }
        return $collection;
    }

    /**
     * Finds all currencies with defined exchange rates
     *
     * @return object collection
     */
    public function findAllWithExchangeRate()
    {
        $query = $this->table->createQuery('currency');
        $collection = $query->select('currency.*, product_price_exchange_rate.*, payment_exchange_rate.*')
                ->innerJoin('currency.product_price_exchange_rate AS product_price_exchange_rate')
                ->innerJoin('currency.payment_exchange_rate AS payment_exchange_rate')
                ->orderBy('type_key, product_price_exchange_rate.id, payment_exchange_rate.id')
                ->execute();
        $query->free(true);

        if (!$collection || $collection->count() == 0) {
            throw new Intraface_Gateway_Exception('Unable to find currency');
        }
        return $collection;
    }

    public function findById($id)
    {
        $query = $this->table->createQuery('currency');
        $query = $query->select('currency.*, product_price_exchange_rate.*, payment_exchange_rate.*')
                ->leftJoin('currency.product_price_exchange_rate AS product_price_exchange_rate')
                ->leftJoin('currency.payment_exchange_rate AS payment_exchange_rate')
                ->addWhere('currency.id = ?', $id)
                ->orderBy('product_price_exchange_rate.id, payment_exchange_rate.id');
        $collection = $query->execute();
        $query->free(true);

        if (!$collection || $collection->count() != 1) {
            throw new Intraface_Gateway_Exception('Unable to find currency '.$id);
        }
        return $collection->getFirst();
    }

    public function findByIsoCode($code)
    {
        $type_gateway = new Intraface_modules_currency_Currency_Type;
        $key = $type_gateway->getByIsoCode($code)->getKey();

        $query = $this->table->createQuery('currency');
        $query = $query->select('currency.*, product_price_exchange_rate.*, payment_exchange_rate.*')
                ->leftJoin('currency.product_price_exchange_rate AS product_price_exchange_rate')
                ->leftJoin('currency.payment_exchange_rate AS payment_exchange_rate')
                ->addWhere('currency.type_key = ?', $key)
                ->orderBy('product_price_exchange_rate.id, payment_exchange_rate.id');

        $collection = $query->execute();
        $query->free(true);

        if (!$collection || $collection->count() != 1) {
            throw new Intraface_Gateway_Exception('Unable to find currency with type key '.$key);
        }
        return $collection->getFirst();
    }
}
