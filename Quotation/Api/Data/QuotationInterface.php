<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Api\Data;

interface QuotationInterface
{
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const QTY  = 'qty';
    const PRODUCT_OPTIONS = 'product_options';
    const PRODUCT_OPTIONS_NAMES = 'product_options_names';
    const QUOTE_ID = 'quote_id';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     * @return mixed
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getProductId();

    /**
     * @param $productId
     * @return mixed
     */
    public function setProductId($productId);

    /**
     * @return mixed
     */
    public function getQty();

    /**
     * @param $qty
     * @return mixed
     */
    public function setQty($qty);

    /**
     * @return mixed
     */
    public function getProductOptions();

    /**
     * @param $productOptions
     * @return mixed
     */
    public function setProductOptions($productOptions);

    /**
     * @return mixed
     */
    public function getProductOptionsNames();

    /**
     * @param $productOptionsNames
     * @return mixed
     */
    public function setProductOptionsNames($productOptionsNames);

    /**
     * @return mixed
     */
    public function getQuoteId();

    /**
     * @param $quoteId
     * @return mixed
     */
    public function setQuoteId($quoteId);
}
