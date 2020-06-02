<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Api;

interface QuotationRepositoryInterface
{
    /**
     * @param $quoteId
     * @return mixed
     */
    public function get($quoteId);

    /**
     * @param Data\QuotationInterface $quote
     * @return mixed
     */
    public function saveQuotation(\Devis\Quotation\Api\Data\QuotationInterface $quote);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getQuotationList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param $quoteId
     * @param array $sharedStoreIds
     * @return mixed
     */
    public function deleteQuotation($quoteId, array $sharedStoreIds);

    /**
     * @param $quoteId
     * @return mixed
     */
    public function getItems($quoteId);
}
