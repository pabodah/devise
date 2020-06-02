<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model;

use Magento\Framework\Model\AbstractModel;
use Devis\Quotation\Api\Data\QuotationInterface;

class Quotation extends AbstractModel implements QuotationInterface
{
    const CACHE_TAG = 'devis_quotation';

    public function _construct()
    {
        $this->_init("Devis\Quotation\Model\ResourceModel\Quotation");
    }

    /**
     * Get cache identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getProductId()
    {
        return $this->getData(QuotationInterface::PRODUCT_ID);
    }

    public function setProductId($productId)
    {
        return $this->setData(QuotationInterface::PRODUCT_ID, $productId);
    }

    public function getQty()
    {
        return $this->getData(QuotationInterface::QTY);
    }

    public function setQty($qty)
    {
        return $this->setData(QuotationInterface::QTY, $qty);
    }

    public function getProductOptions()
    {
        return $this->getData(QuotationInterface::PRODUCT_OPTIONS);
    }

    public function setProductOptions($productOptions)
    {
        return $this->getData(QuotationInterface::PRODUCT_OPTIONS, $productOptions);
    }

    public function getProductOptionsNames()
    {
        return $this->getData(QuotationInterface::PRODUCT_OPTIONS_NAMES);
    }

    public function setProductOptionsNames($productOptionsNames)
    {
        return $this->getData(QuotationInterface::PRODUCT_OPTIONS_NAMES, $productOptionsNames);
    }

    public function getQuoteId()
    {
        return $this->getData(QuotationInterface::QUOTE_ID);
    }

    public function setQuoteId($quoteId)
    {
        return $this->getData(QuotationInterface::QUOTE_ID, $quoteId);
    }
}
