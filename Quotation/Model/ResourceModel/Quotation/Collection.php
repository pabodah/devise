<?php
/**
 * Copyright (c) Devis
 */
namespace Devis\Quotation\Model\ResourceModel\Quotation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init("Devis\Quotation\Model\Quotation", "Devis\Quotation\Model\ResourceModel\Quotation");
    }
}
