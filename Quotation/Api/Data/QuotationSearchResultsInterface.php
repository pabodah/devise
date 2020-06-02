<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface QuotationSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getItems();

    /**
     * @param array $items
     * @return QuotationSearchResultsInterface
     */
    public function setItems(array $items);
}
